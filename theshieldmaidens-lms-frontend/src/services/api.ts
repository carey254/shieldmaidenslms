// API service for backend integration

const API_BASE_URL = 'http://127.0.0.1:8000/api' // Backend server running on 127.0.0.1:8000

export interface User {
  id: number
  name: string
  email: string
  is_admin: boolean
  role?: string
  created_at: string
}

export interface AuthResponse {
  message: string
  user: User
  access_token: string
  token_type: string
}

export interface RegisterData {
  name: string
  email: string
  password: string
  password_confirmation: string
  captcha_token?: string
}

export interface LoginData {
  email: string
  password: string
  captcha_token?: string
}

export interface ForgotPasswordData {
  email: string
  captcha_token?: string
}

export interface MessageData {
  recipients: string
  subject: string
  message: string
  priority?: 'low' | 'medium' | 'high' | 'urgent'
}

export interface MeetingData {
  title: string
  description: string
  scheduled_at: string
  participants: number[]
}

class ApiService {
  private async request<T>(endpoint: string, options: RequestInit = {}): Promise<T> {
    const url = `${API_BASE_URL}${endpoint}`
    
    const defaultHeaders: Record<string, string> = {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
    }

    // Add auth token if available
    const token = localStorage.getItem('token')
    if (token) {
      defaultHeaders['Authorization'] = `Bearer ${token}`
    }

    const response = await fetch(url, {
      ...options,
      headers: {
        ...defaultHeaders,
        ...options.headers,
      },
    })

    if (!response.ok) {
      const errorData = await response.json().catch(() => ({}))
      throw new Error(errorData.message || `HTTP error! status: ${response.status}`)
    }

    return response.json()
  }

  async register(data: RegisterData): Promise<AuthResponse> {
    return this.request<AuthResponse>('/register', {
      method: 'POST',
      body: JSON.stringify(data),
    })
  }

  async login(data: LoginData): Promise<AuthResponse> {
    return this.request<AuthResponse>('/login', {
      method: 'POST',
      body: JSON.stringify(data),
    })
  }

  async secureRegister(data: RegisterData): Promise<AuthResponse> {
    return this.request<AuthResponse>('/secure-register', {
      method: 'POST',
      body: JSON.stringify(data),
    })
  }

  async secureLogin(data: LoginData): Promise<AuthResponse> {
    return this.request<AuthResponse>('/secure-login', {
      method: 'POST',
      body: JSON.stringify(data),
    })
  }

  async secureForgotPassword(data: ForgotPasswordData): Promise<{ message: string; reset_token?: string }> {
    return this.request<{ message: string; reset_token?: string }>('/secure-forgot-password', {
      method: 'POST',
      body: JSON.stringify(data),
    })
  }

  async checkEmail(email: string): Promise<{ exists: boolean; message: string }> {
    return this.request<{ exists: boolean; message: string }>('/check-email', {
      method: 'POST',
      body: JSON.stringify({ email }),
    })
  }

  async forgotPassword(email: string): Promise<{ message: string; reset_token?: string }> {
    return this.request<{ message: string; reset_token?: string }>('/forgot-password', {
      method: 'POST',
      body: JSON.stringify({ email }),
    })
  }

  async resetPassword(email: string, token: string, password: string, password_confirmation: string): Promise<{ message: string }> {
    return this.request<{ message: string }>('/reset-password', {
      method: 'POST',
      body: JSON.stringify({ email, token, password, password_confirmation }),
    })
  }

  async logout(): Promise<{ message: string }> {
    return this.request<{ message: string }>('/logout', {
      method: 'POST',
    })
  }

  async getCurrentUser(): Promise<{ user: User }> {
    return this.request<{ user: User }>('/user')
  }

  // Store auth data
  setAuthData(authResponse: AuthResponse) {
    localStorage.setItem('token', authResponse.access_token)
    localStorage.setItem('user', JSON.stringify(authResponse.user))
  }

  // Clear auth data
  clearAuthData() {
    localStorage.removeItem('token')
    localStorage.removeItem('user')
  }

  // Get current user from localStorage
  getCurrentUserFromStorage(): User | null {
    const userData = localStorage.getItem('user')
    return userData ? JSON.parse(userData) : null
  }

  // Check if user is authenticated
  isAuthenticated(): boolean {
    return !!localStorage.getItem('token')
  }

  // Admin APIs
  async getAdminStats() {
    return this.request('/admin/stats')
  }

  async getAdminUsers() {
    return this.request('/admin/users')
  }

  async getAdminMessages() {
    return this.request('/admin/messages')
  }

  async sendAdminMessage(messageData: MessageData) {
    return this.request('/admin/messages/send', {
      method: 'POST',
      body: JSON.stringify(messageData),
    })
  }

  async assignInstructor(courseId: number, instructorId: number) {
    return this.request(`/admin/courses/${courseId}/assign-instructor`, {
      method: 'POST',
      body: JSON.stringify({ instructor_id: instructorId }),
    })
  }

  async createMeeting(meetingData: MeetingData) {
    return this.request('/admin/meetings', {
      method: 'POST',
      body: JSON.stringify(meetingData),
    })
  }

  async updateMeeting(meetingId: number, meetingData: Partial<MeetingData>) {
    return this.request(`/admin/meetings/${meetingId}`, {
      method: 'PUT',
      body: JSON.stringify(meetingData),
    })
  }

  // Instructor APIs
  async getInstructorStats() {
    return this.request('/instructor/dashboard/stats')
  }

  async getInstructorCourses() {
    return this.request('/instructor/dashboard/courses')
  }

  async getInstructorMessages() {
    return this.request('/instructor/messages')
  }

  async sendInstructorMessage(messageData: MessageData) {
    return this.request('/instructor/messages/send', {
      method: 'POST',
      body: JSON.stringify(messageData),
    })
  }

  async getInstructorMeetings() {
    return this.request('/instructor/meetings')
  }

  // Student APIs
  async getStudentStats() {
    return this.request('/student/dashboard/stats')
  }

  async getStudentCourses() {
    return this.request('/student/courses')
  }

  async getStudentCourseDetails(courseId: number) {
    return this.request(`/student/courses/${courseId}`)
  }

  async enrollInCourse(courseId: number) {
    return this.request(`/student/courses/${courseId}/enroll`, {
      method: 'POST',
    })
  }

  async getEnrollments() {
    return this.request('/student/enrollments')
  }

  async getEnrolledCourseIds() {
    return this.request('/student/enrollments/course-ids')
  }

  async getStudentMessages() {
    return this.request('/student/messages')
  }

  async getStudentMeetings() {
    return this.request('/student/meetings')
  }

  async getSessions() {
    return this.request('/student/sessions')
  }

  // Catalog APIs
  async getCourses() {
    return this.request('/catalog/courses')
  }
}

// Export instructor API functions
export const instructorApi = {
  getDashboardStats: async () => {
    const response = await fetch(`${API_BASE_URL}/instructor/dashboard-stats`);
    return response.json();
  },
  
  getRecentCourses: async () => {
    const response = await fetch(`${API_BASE_URL}/instructor/recent-courses`);
    return response.json();
  },
  
  getRecentActivities: async () => {
    const response = await fetch(`${API_BASE_URL}/instructor/recent-activities`);
    return response.json();
  },
  
  getPendingTasks: async () => {
    const response = await fetch(`${API_BASE_URL}/instructor/pending-tasks`);
    return response.json();
  }
};

export const apiService = new ApiService();
export default apiService;
