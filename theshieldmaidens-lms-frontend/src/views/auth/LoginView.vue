<template>
  <div class="auth-container">
    <div class="auth-card">
      <p class="subtitle">Sign in to access your learning journey</p>

      <div v-if="registrationSuccess" class="success-banner" role="status">
        You are registered successfully. Sign in below with the same email and password you used to create your account.
      </div>
      
      <!-- Email Input Step -->
      <div v-if="!emailChecked" class="auth-form">
        <form @submit.prevent="checkEmail">
          <div class="form-group">
            <label for="email">Email</label>
            <div class="input-group">
              <span class="input-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                  <polyline points="22,6 12,13 2,6"></polyline>
                </svg>
              </span>
              <input
                id="email"
                v-model="email"
                type="email"
                placeholder="Enter your email"
                required
                class="form-control"
              >
            </div>
          </div>
          
          <button 
            type="submit" 
            :disabled="isLoading || !email"
            class="submit-btn"
          >
            <span v-if="isLoading">Checking...</span>
            <span v-else>Continue</span>
          </button>
        </form>

        <div v-if="error" class="error-message">
          {{ error }}
          <div v-if="error.includes('Network error')" class="error-details">
            <small>Please ensure:</small>
            <ul>
              <li>Backend server is running on 127.0.0.1:8000</li>
              <li>No firewall blocking the connection</li>
              <li>CORS is properly configured on the backend</li>
            </ul>
            <button type="button" @click="testConnection" class="btn btn-sm btn-secondary">Test Connection</button>
          </div>
        </div>
      </div>

      <!-- Password Input Step -->
      <div v-else class="auth-form">
        <!-- Email Notice -->
        <div class="email-notice">
          <div class="email-display">
            <span class="email-label">Signing in as:</span>
            <span class="email-value">{{ email }}</span>
            <button type="button" @click="changeEmail" class="change-email-btn">
              Change
            </button>
          </div>
        </div>
        
        <form @submit.prevent="login">
          <div class="form-group">
            <div class="flex justify-between items-center">
              <label for="password">Password</label>
              <router-link to="/forgot-password" class="forgot-password">
                Forgot password?
              </router-link>
            </div>
            <div class="input-group">
              <span class="input-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                  <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                </svg>
              </span>
              <input
                id="password"
                v-model="password"
                type="password"
                placeholder="Enter your password"
                required
                class="form-control"
                ref="passwordInput"
              >
              <button type="button" class="toggle-password" @click="togglePasswordVisibility">
              <svg v-if="showPassword" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                <line x1="1" y1="1" x2="23" y2="23"></line>
              </svg>
              <svg v-else xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                <circle cx="12" cy="12" r="3"></circle>
              </svg>
            </button>
          </div>
        </div>
        
        <div class="form-group remember-me">
          <label class="checkbox-container">
            <input type="checkbox" v-model="rememberMe">
            <span class="checkmark"></span>
            Remember me
          </label>
        </div>
        
        <button type="submit" class="submit-btn" :disabled="isLoading">
          <span v-if="isLoading">Signing in...</span>
          <span v-else>Sign In</span>
        </button>
        
        <div v-if="error" class="error-message">
          {{ error }}
          <div v-if="error.includes('Network error')" class="error-details">
            <small>Please ensure:</small>
            <ul>
              <li>Backend server is running on 127.0.0.1:8000</li>
              <li>No firewall blocking the connection</li>
              <li>CORS is properly configured on the backend</li>
            </ul>
            <button @click="testConnection" class="btn btn-sm btn-secondary">Test Connection</button>
          </div>
        </div>
        </form>
        
        <div class="divider">
          <span>OR</span>
        </div>
      </div>
        
                
        <p class="signup-link">
          Don't have an account? 
          <router-link to="/register">Sign up</router-link>
        </p>
    </div>
  </div>
</template>

<style scoped>
:root {
  --primary: rgba(252, 252, 255, 1);
  --primary-hover: #f5f5faff;
  --text: #1F2937;
  --text-light: #6B7280;
  --border: #E5E7EB;
  --error: #EF4444;
  --success: #10B981;
  --background: #F9FAFB;
  --card-bg: #FFFFFF;
}

/* Base styles */
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
}

.auth-container {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), url('/background.jpg') no-repeat center center !important;
  background-size: cover !important;
  background-attachment: fixed !important;
  padding: 2rem 1rem;
  padding-top: 120px;
  position: relative !important;
  z-index: 1 !important;
}

.auth-card {
  width: 100%;
  max-width: 420px;
  background: #FFFFFF;
  border-radius: 12px;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.15);
  padding: 2.5rem;
  margin: 0 auto;
  position: relative;
  z-index: 1;
  border: 1px solid #E5E7EB;
}

h1 {
  font-size: 1.75rem;
  font-weight: 700;
  color: #1F2937;
  text-align: center;
  margin-bottom: 0.5rem;
}

.subtitle {
  color: #6B7280;
  text-align: center;
  margin-bottom: 2rem;
  font-size: 1rem;
}

.auth-form {
  margin-top: 2rem;
}

.form-group {
  margin-bottom: 1.5rem;
}

.form-group label {
  display: block;
  font-size: 0.875rem;
  font-weight: 600;
  color: #374151;
  margin-bottom: 0.5rem;
}

.input-group {
  position: relative;
  display: flex;
  align-items: center;
}

.input-icon {
  position: absolute;
  left: 12px;
  color: #6B7280;
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 2;
}

.form-control {
  width: 100%;
  padding: 0.875rem 1rem 0.875rem 40px;
  font-size: 0.9375rem;
  border: 2px solid #E5E7EB;
  border-radius: 8px;
  background-color: #FFFFFF;
  color: #1F2937;
  transition: all 0.2s ease;
}

.form-control:focus {
  outline: none;
  border-color: #F97316;
  box-shadow: 0 0 0 3px rgba(249, 115, 22, 0.1);
  background-color: #FFFFFF;
}

.toggle-password {
  position: absolute;
  right: 12px;
  background: none;
  border: none;
  color: #6B7280;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 0.25rem;
  border-radius: 4px;
  transition: all 0.2s;
  z-index: 2;
}

.toggle-password:hover {
  color: #F97316;
  background-color: rgba(249, 115, 22, 0.05);
}

.remember-me {
  display: flex;
  align-items: center;
  margin: 1.5rem 0;
}

.checkbox-container {
  display: flex;
  align-items: center;
  position: relative;
  padding-left: 32px;
  cursor: pointer;
  font-size: 0.875rem;
  color: var(--text-light);
  user-select: none;
}

.checkbox-container input {
  position: absolute;
  opacity: 0;
  cursor: pointer;
  height: 0;
  width: 0;
}

.checkmark {
  position: absolute;
  left: 0;
  height: 20px;
  width: 20px;
  background-color: #fff;
  border: 1px solid var(--border);
  border-radius: 4px;
  transition: all 0.2s;
}

.checkbox-container:hover input ~ .checkmark {
  border-color: var(--primary);
}

.checkbox-container input:checked ~ .checkmark {
  background-color: var(--primary);
  border-color: var(--primary);
}

.checkmark:after {
  content: "";
  position: absolute;
  display: none;
  left: 7px;
  top: 3px;
  width: 4px;
  height: 8px;
  border: solid white;
  border-width: 0 2px 2px 0;
  transform: rotate(45deg);
}

.checkbox-container input:checked ~ .checkmark:after {
  display: block;
}

.forgot-password {
  font-size: 0.8125rem;
  color: #F97316;
  text-decoration: none;
  transition: color 0.2s;
  font-weight: 500;
}

.forgot-password:hover {
  color: #EA580C;
  text-decoration: underline;
}

.btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 100%;
  padding: 0.75rem 1.5rem;
  font-size: 0.9375rem;
  font-weight: 500;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s ease;
  border: none;
  text-align: center;
}

.btn-primary {
  background: linear-gradient(135deg, #F97316 0%, #EA580C 100%);
  color: white;
  box-shadow: 0 4px 12px rgba(249, 115, 22, 0.3);
  border: none;
  font-weight: 600;
  padding: 0.875rem 1.5rem;
}

.btn-primary:hover:not(:disabled) {
  background: linear-gradient(135deg, #EA580C 0%, #DC2626 100%);
  transform: translateY(-1px);
  box-shadow: 0 6px 20px rgba(249, 115, 22, 0.4);
}

.btn-google {
  background-color: white;
  color: #4B5563;
  border: 1px solid var(--border);
  margin-top: 1rem;
}

.btn-google:hover {
  background-color: white;
  border-color: var(--border);
}

.btn-google svg {
  margin-right: 8px;
}

.divider {
  display: flex;
  align-items: center;
  text-align: center;
  margin: 1.5rem 0;
  color: var(--text-light);
  font-size: 0.875rem;
}

.divider::before,
.divider::after {
  content: '';
  flex: 1;
  border-bottom: 1px solid var(--border);
}

.divider:not(:empty)::before {
  margin-right: 1.5rem;
}

.divider:not(:empty)::after {
  margin-left: 1.5rem;
}

.signup-link {
  text-align: center;
  margin-top: 1.5rem;
  font-size: 0.9375rem;
  color: var(--text-light);
}

.signup-link a {
  color: #F97316;
  font-weight: 600;
  text-decoration: none;
  transition: color 0.2s;
}

.signup-link a:hover {
  color: #EA580C;
  text-decoration: underline;
}

.error-message {
  margin-top: 1rem;
  padding: 0.75rem;
  background-color: #FEE2E2;
  border: 1px solid #FCA5A5;
  border-radius: 0.375rem;
  color: #B91C1C;
  font-size: 0.875rem;
  text-align: center;
}

.error-details {
  margin-top: 0.5rem;
  padding: 0.5rem;
  background-color: #FEE2E2;
  border-radius: 0.25rem;
  text-align: left;
}

.error-details ul {
  margin: 0.5rem 0;
  padding-left: 1.2rem;
  font-size: 0.8rem;
}

.error-details li {
  margin-bottom: 0.25rem;
}

.btn-sm {
  padding: 0.375rem 0.75rem;
  font-size: 0.8rem;
  margin-top: 0.5rem;
}

.portal-links {
  margin-top: 2rem;
  padding-top: 1.5rem;
  border-top: 1px solid var(--border);
  text-align: center;
}

.portal-links p {
  color: rgba(230, 142, 11, 0.8);
  margin-bottom: 1rem;
  font-size: 0.9rem;
}

.link-options {
  display: flex;
  gap: 1rem;
  justify-content: center;
  flex-wrap: wrap;
}

.portal-link {
  color: white;
  text-decoration: none;
  padding: 0.5rem 1rem;
  border: 1px solid rgba(255, 156, 9, 0.3);
  border-radius: 20px;
  font-size: 0.85rem;
  transition: all 0.3s ease;
  background: linear-gradient(135deg, #e68c07ff 0%, #cc8809ff 100%);
}

.portal-link:hover {
  background: linear-gradient(135deg, #1d1d1fff 0%, #080808ff 100%);
  border-color: rgba(255, 255, 255, 0.5);
}

@media (max-width: 480px) {
  .auth-card {
    padding: 1.5rem;
  }
  
  h1 {
    font-size: 1.5rem;
  }
  
  .subtitle {
    font-size: 0.9375rem;
  }
}

/* Email-first styles */
.email-notice {
  background: #f0f9ff;
  border: 1px solid #0ea5e9;
  border-radius: 8px;
  padding: 12px;
  margin-bottom: 20px;
}

.email-display {
  display: flex;
  align-items: center;
  gap: 8px;
  flex-wrap: wrap;
}

.email-label {
  color: #0369a1;
  font-size: 0.875rem;
}

.email-value {
  color: #0c4a6e;
  font-weight: 600;
  font-size: 0.875rem;
}

.change-email-btn {
  background: none;
  border: none;
  color: #0ea5e9;
  text-decoration: underline;
  cursor: pointer;
  font-size: 0.875rem;
  padding: 2px 4px;
}

.change-email-btn:hover {
  color: #0284c7;
}

.submit-btn {
  width: 100%;
  padding: 12px 24px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border: none;
  border-radius: 8px;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
}

.submit-btn:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}

.submit-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.success-banner {
  background: #ecfdf5;
  border: 1px solid #6ee7b7;
  color: #065f46;
  padding: 0.75rem 1rem;
  border-radius: 8px;
  font-size: 0.9rem;
  margin-bottom: 1.25rem;
  line-height: 1.45;
}
</style>

<script setup>
import { ref, onMounted, nextTick } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import { testApiConnection, testLoginEndpoint } from '@/utils/api-test';
import apiService from '@/services/api';

const email = ref('');
const password = ref('');
const rememberMe = ref(false);
const showPassword = ref(false);
const isLoading = ref(false);
const error = ref('');
const registrationSuccess = ref(false);
const router = useRouter();
const route = useRoute();
const authStore = useAuthStore();

// Email-first state
const emailChecked = ref(false);
const emailExists = ref(false);
const passwordInput = ref(null);

// Initialize with email input if not pre-filled
if (!route.query.email) {
  emailChecked.value = false;
}

// Email checking function
const checkEmail = async () => {
  if (isLoading.value || !email.value) return;
  
  error.value = '';
  isLoading.value = true;
  
  try {
    console.log('Checking email:', email.value);
    const response = await apiService.checkEmail(email.value);
    console.log('Email check response:', response);
    
    emailExists.value = response.exists;

    if (!response.exists) {
      // Redirect to signup page
      router.push('/register');
      return;
    }

    // Show password form
    emailChecked.value = true;
    nextTick(() => {
      passwordInput.value?.focus();
    });
  } catch (err) {
    console.error('Email check error:', err);
    error.value = 'Failed to check email. Please try again.';
  } finally {
    isLoading.value = false;
  }
};

// Change email function
const changeEmail = () => {
  console.log('changeEmail called - resetting to email step');
  emailChecked.value = false;
  emailExists.value = false;
  password.value = '';
  error.value = '';
  // Focus on email input
  nextTick(() => {
    document.getElementById('email')?.focus();
  });
};

const login = async () => {
  if (isLoading.value) return;
  
  // Basic validation
  if (!email.value || !password.value) {
    error.value = 'Please fill in all fields';
    return;
  }
  
  console.log('Login attempt started...');
  console.log('Email:', email.value);
  console.log('Password:', password.value ? '***' : '');
  
  error.value = '';
  isLoading.value = true;
  
  try {
    console.log('Calling authStore.login...');
    // Let backend determine the role based on email/password
    await authStore.loginAction(email.value, password.value);
    // Post-login redirect, pending course enrollment, and returnUrl are handled in the auth store.
  } catch (err) {
    console.error('Login error:', err);
    console.error('Error response:', err.response);
    console.error('Error data:', err.response?.data);
    
    if (err.code === 'ERR_NETWORK') {
      error.value = 'Network error. Please check your internet connection and ensure the backend server is running on 127.0.0.1:8000';
    } else if (err.response?.status === 401) {
      const msg = err.response?.data?.message;
      error.value =
        typeof msg === 'string' && msg.length > 0
          ? msg
          : 'Invalid credentials. Please check your email and password.';
    } else if (err.response?.status === 403) {
      error.value = 'Access denied. This account may not have the required role for this portal.';
    } else if (err.response?.data?.message) {
      error.value = err.response.data.message;
    } else {
      error.value = 'Login failed. Please try again or contact support if the problem persists.';
    }
  } finally {
    isLoading.value = false;
  }
};

const togglePasswordVisibility = () => {
  const passwordInput = document.getElementById('password');
  if (passwordInput.type === 'password') {
    passwordInput.type = 'text';
    showPassword.value = true;
  } else {
    passwordInput.type = 'password';
    showPassword.value = false;
  }
};

// Test API connection
const testConnection = async () => {
  console.log('Testing API connection...');
  const isConnected = await testApiConnection();
  console.log('API Connection result:', isConnected);
  
  if (!isConnected) {
    console.log('Testing login endpoint directly...');
    const loginTest = await testLoginEndpoint();
    console.log('Login endpoint test result:', loginTest);
  }
};

// OAuth callback / registration redirect / API probe
onMounted(async () => {
  const q = { ...route.query };

  // Pre-fill email if coming from email check
  if (typeof q.email === 'string') {
    email.value = q.email;
    emailChecked.value = true;
  }

  if (typeof q.redirect === 'string' && q.redirect.startsWith('/')) {
    authStore.setReturnUrl(q.redirect);
  }

  if (q.oauth_success === '1' && typeof q.access_token === 'string') {
    const t = q.access_token;
    await router.replace({ path: '/login', query: {} });
    try {
      await authStore.completeOAuthLogin(t);
    } catch {
      error.value = 'Could not finish Google sign-in. Please try again.';
    }
    return;
  }

  if (q.oauth_error === '1') {
    error.value = 'Google sign-in was cancelled or failed. Please try again.';
    await router.replace({ path: '/login', query: {} });
  }

  if (q.registered === '1') {
    registrationSuccess.value = true;
    if (typeof q.email === 'string') email.value = q.email;
    await router.replace({ path: '/login', query: {} });
  }

  // Pre-fill email if coming from email check
  if (typeof q.email === 'string') {
    email.value = q.email;
    emailChecked.value = true;
  }

  console.log('LoginView mounted, testing API connection...');
  testConnection();
});
</script>
