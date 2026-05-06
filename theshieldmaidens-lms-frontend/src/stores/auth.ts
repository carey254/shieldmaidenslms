import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';

const API_BASE_URL = 'http://127.0.0.1:8000/api' // Laravel backend on port 8000

// Create axios instance
const apiClient = axios.create({
  baseURL: API_BASE_URL,
  timeout: 10000,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  }
})

// Add auth interceptor
apiClient.interceptors.request.use((config) => {
  const token = localStorage.getItem('token')
  if (token) {
    config.headers.Authorization = `Bearer ${token}`
  }
  return config
})

// Direct API functions
const login = async (email: string, password: string, options?: { captchaToken?: string; loginAttempts?: number }) => {
  try {
    const payload: any = { email, password }
    if (options?.captchaToken) {
      payload.captcha_token = options.captchaToken
    }
    if (options?.loginAttempts) {
      payload.login_attempts = options.loginAttempts
    }
    const response = await apiClient.post('/login', payload)
    return response.data
  } catch (error) {
    console.error('Login error:', error)
    throw error
  }
}

const register = async (userData: { name: string; email: string; password: string; password_confirmation: string; captchaToken?: string }) => {
  try {
    const payload: any = { ...userData }
    if (userData.captchaToken) {
      payload.captcha_token = userData.captchaToken
    }
    const response = await apiClient.post('/register', payload)
    return response.data
  } catch (error) {
    console.error('Registration error:', error)
    throw error
  }
}

const logoutAction = async () => {
  try {
    await apiClient.post('/logout')
  } catch (error) {
    console.error('Logout error:', error)
  }
}

const getUser = async () => {
  try {
    const response = await apiClient.get('/user')
    return response.data
  } catch (error) {
    console.error('Get user error:', error)
    throw error
  }
}

/** After login: enroll via API if guest chose a course (localStorage stub). Student role only. */
async function finalizePendingCourseEnrollment(): Promise<string | null> {
  const raw = localStorage.getItem('courseToEnroll')
  if (!raw) return null
  let courseId: number | null = null
  try {
    const parsed = JSON.parse(raw) as { id?: number }
    courseId = typeof parsed?.id === 'number' ? parsed.id : null
  } catch {
    courseId = null
  }
  localStorage.removeItem('courseToEnroll')
  if (courseId == null) return null
  try {
    await apiClient.post(`/student/courses/${courseId}/enroll`)
  } catch (e: unknown) {
    const err = e as { response?: { status?: number } }
    if (err?.response?.status !== 422) {
      console.warn('Pending enrollment sync failed:', e)
    }
  }
  return `/course/${courseId}/learn`
}

export const useAuthStore = defineStore('auth', () => {
  const router = useRouter();
  
  // State - don't auto-restore from localStorage to prevent auto-login
  const user = ref<any | null>(null);
  const token = ref<string | null>(null);
  const returnUrl = ref<string | null>(null);
  const error = ref<string>('');
  
  function clearLocalAuth(): void {
    user.value = null;
    token.value = null;
    localStorage.removeItem('user');
    localStorage.removeItem('token');
  }
  
  // Getters
  const isAuthenticated = computed(() => !!token.value);
  const isAdmin = computed(() => !(user.value?.is_admin || user.value?.role === 'admin'));
  const isInstructor = computed(
    () => !(user.value?.is_instructor || user.value?.role === 'instructor' || user.value?.email?.includes('instructor@'))
  );
  const isStudent = computed(() => !isAdmin.value && !isInstructor.value);
  const userRole = computed(() => user.value?.role || 'student');
  
  // Enhanced RBAC permissions
  const permissions = computed(() => {
    if (!user.value) return {};
    
    const role = user.value.role || 'student';
    const basePermissions = {
      student: ['read:courses', 'enroll:courses', 'view:profile'],
      instructor: ['read:courses', 'create:courses', 'edit:courses', 'view:students', 'manage:enrollments'],
      admin: ['read:all', 'create:all', 'edit:all', 'delete:all', 'manage:users', 'manage:system']
    };
    
    return {
      role,
      permissions: basePermissions[role] || [],
      canAccess: (resource: string) => {
        const userPermissions = basePermissions[role] || [];
        return userPermissions.some(permission => permission.includes(resource));
      }
    };
  });

  /** Expected portal from login page: admin | instructor | student */
  function userMatchesPortal(expectedPortal: string, u: any): boolean {
    if (!u) return false;
    if (expectedPortal === 'admin') {
      return !!(u.is_admin || u.role === 'admin');
    }
    if (expectedPortal === 'instructor') {
      return !!(u.is_instructor || u.role === 'instructor' || u.email?.includes('instructor@'));
    }
    if (expectedPortal === 'student') {
      return !u.is_admin && !u.is_instructor && !u.email?.includes('instructor@') && (u.role === 'student' || u.role === 'user' || !u.role);
    }
    return true;
  }

  // Actions
  async function loginAction(email: string, password: string, options?: { captchaToken?: string; loginAttempts?: number }, expectedPortal?: string): Promise<any> {
    try {
      const response = await login(email, password, { captchaToken: options?.captchaToken, loginAttempts: options?.loginAttempts });

      console.log('Login response:', response);

      if (expectedPortal && !userMatchesPortal(expectedPortal, response.user)) {
        clearLocalAuth();
        const err: any = new Error('Portal role mismatch');
        err.response = {
          data: {
            message:
              'This account is not allowed on this login page. Use the correct portal for your role.',
          },
        };
        throw err;
      }

      // Update pinia state
      user.value = response.user;
      token.value = response.access_token;

      // Store user details and token in local storage
      localStorage.setItem('user', JSON.stringify(user.value));
      localStorage.setItem('token', token.value!);

      if (response.user?.password_change_required) {
        returnUrl.value = null;
        router.push('/change-password');
        return user.value;
      }

      const savedReturn = returnUrl.value;
      returnUrl.value = null;

      let roleDashboard = '/dashboard';
      if (user.value?.is_admin) roleDashboard = '/admin/dashboard';
      else if (user.value?.is_instructor || user.value?.email?.includes('instructor@')) roleDashboard = '/instructor/dashboard';

      if (user.value?.is_admin || user.value?.is_instructor || user.value?.email?.includes('instructor@')) {
        await router.push(savedReturn || roleDashboard);
        return user.value;
      }

      const pendingLearnPath = await finalizePendingCourseEnrollment();

      if (pendingLearnPath) {
        await router.push({ path: pendingLearnPath, query: { enrolled: '1' } });
      } else {
        await router.push(savedReturn || roleDashboard);
      }

      return user.value;
    } catch (error) {
      console.error('Login failed:', error);
      throw error;
    }
  }

  async function registerAction(userData: { name: string; email: string; password: string; password_confirmation: string; captchaToken?: string }): Promise<any> {
    await register(userData);
    await router.push({
      path: '/login',
      query: { registered: '1', email: userData.email },
    });
  }

  const logoutAction = async () => {
    try {
      await apiClient.post('/logout')
      // Clear auth state
      clearLocalAuth();
    } catch (error) {
      console.error('Logout error:', error)
    }
  }

  const setReturnUrl = (url: string) => {
    returnUrl.value = url;
  }

  const completeOAuthLogin = async (token: string) => {
    try {
      const response = await getUser();
      user.value = response;
      token.value = token;
      localStorage.setItem('user', JSON.stringify(user.value));
      localStorage.setItem('token', token);
    } catch (err) {
      console.error('OAuth login error:', err);
    }
  }

  // Profile management functions
  const updateProfile = async (profileData: any) => {
    try {
      const response = await apiClient.put('/admin/profile', profileData)
      // Update local user data
      user.value = { ...user.value, ...profileData }
      localStorage.setItem('user', JSON.stringify(user.value))
      return response.data
    } catch (error) {
      console.error('Profile update error:', error)
      throw error
    }
  }

  const uploadAvatar = async (file: File) => {
    try {
      const formData = new FormData()
      formData.append('avatar', file)
      
      const response = await apiClient.post('/admin/profile/avatar', formData, {
        headers: {
          'Content-Type': 'multipart/form-data'
        }
      })
      
      // Update local user data
      user.value = { ...user.value, avatar: response.data.avatar }
      localStorage.setItem('user', JSON.stringify(user.value))
      return response.data
    } catch (error) {
      console.error('Avatar upload error:', error)
      throw error
    }
  }

  const changePassword = async (passwordData: any) => {
    try {
      const response = await apiClient.put('/admin/security/password', passwordData)
      return response.data
    } catch (error) {
      console.error('Password change error:', error)
      throw error
    }
  }

  const updateSecuritySettings = async (settings: any) => {
    try {
      const response = await apiClient.put('/admin/security/settings', settings)
      return response.data
    } catch (error) {
      console.error('Security settings update error:', error)
      throw error
    }
  }

  const getActiveSessions = async () => {
    try {
      const response = await apiClient.get('/admin/security/sessions')
      return response.data
    } catch (error) {
      console.error('Get sessions error:', error)
      throw error
    }
  }

  const terminateSession = async (sessionId: string) => {
    try {
      const response = await apiClient.delete(`/admin/security/sessions/${sessionId}`)
      return response.data
    } catch (error) {
      console.error('Terminate session error:', error)
      throw error
    }
  }

  const updatePreferences = async (preferences: any) => {
    try {
      const response = await apiClient.put('/admin/preferences', preferences)
      return response.data
    } catch (error) {
      console.error('Preferences update error:', error)
      throw error
    }
  }

  const getPreferences = async () => {
    try {
      const response = await apiClient.get('/admin/preferences')
      return response.data
    } catch (error) {
      console.error('Get preferences error:', error)
      throw error
    }
  }

  return {
    user,
    token,
    returnUrl,
    error,
    isAuthenticated,
    isAdmin,
    isInstructor,
    isStudent,
    userRole,
    permissions,
    loginAction,
    registerAction,
    logoutAction,
    setReturnUrl,
    completeOAuthLogin,
    finalizePendingCourseEnrollment,
    updateProfile,
    uploadAvatar,
    changePassword,
    updateSecuritySettings,
    getActiveSessions,
    terminateSession,
    updatePreferences,
    getPreferences
  };
});
