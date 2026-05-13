<template>
  <div class="auth-container">
    <div class="auth-card">
      <div class="logo-container">
        <img :src="PUBLIC_BRAND_LOGO" alt="The Shield Maidens" class="logo" />
      </div>
      
      <h1>Create an Account</h1>
      <p class="subtitle">Join our community today</p>

      <div v-if="serverError" class="server-error" role="alert">
        {{ serverError }}
      </div>
      
      <form @submit.prevent="register" class="auth-form">
        <div class="form-group">
          <label for="name">Full Name</label>
          <div class="input-group">
            <span class="input-icon">
              <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                <circle cx="12" cy="7" r="4"></circle>
              </svg>
            </span>
            <input
              id="name"
              v-model="name"
              type="text"
              placeholder="Enter your full name"
              required
              class="form-control"
            >
          </div>
        </div>
        
        <div class="form-group">
          <label for="email">Email Address</label>
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
        
        <!-- CAPTCHA -->
        <div class="form-group">
          <label>Security Verification</label>
          <Captcha 
            @captcha-verified="captchaVerified = $event"
            @captcha-changed="captchaToken = $event"
          />
        </div>
        
        <div class="form-group">
          <label for="password">Password</label>
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
              placeholder="Create a password"
              required
              class="form-control"
              :class="{ 'error': errors.password }"
            >
            <button type="button" class="toggle-password" @click="togglePasswordVisibility('password')">
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
          <div v-if="errors.password" class="error-message">
            {{ errors.password }}
          </div>
          <div class="password-strength" :class="passwordStrength.class">
            Password strength: {{ passwordStrength.text }}
          </div>
        </div>
        
        <div class="form-group">
          <label for="confirmPassword">Confirm Password</label>
          <div class="input-group">
            <span class="input-icon">
              <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
              </svg>
            </span>
            <input
              id="confirmPassword"
              v-model="confirmPassword"
              type="password"
              placeholder="Confirm your password"
              required
              class="form-control"
              :class="{ 'error': errors.confirmPassword }"
            >
            <button type="button" class="toggle-password" @click="togglePasswordVisibility('confirmPassword')">
              <svg v-if="showConfirmPassword" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                <line x1="1" y1="1" x2="23" y2="23"></line>
              </svg>
              <svg v-else xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                <circle cx="12" cy="12" r="3"></circle>
              </svg>
            </button>
          </div>
          <div v-if="errors.confirmPassword" class="error-message">
            {{ errors.confirmPassword }}
          </div>
        </div>
        
        <div class="form-group terms-container">
          <label class="checkbox-container">
            <input 
              type="checkbox" 
              v-model="termsAccepted"
              required
            >
            <span class="checkmark"></span>
            I agree to the <a href="/terms" target="_blank" class="text-link">Terms of Service</a> and 
            <a href="/privacy" target="_blank" class="text-link">Privacy Policy</a>
          </label>
        </div>
        
        <button 
          type="submit" 
          class="btn btn-primary"
          :disabled="isLoading"
        >
          <span v-if="isLoading" class="spinner"></span>
          <span v-else>Create Account</span>
        </button>
        
        <div class="divider">
          <span>OR</span>
        </div>
        
        <div class="social-login">
          <button type="button" class="btn btn-google" @click="startGoogleOAuth">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
              <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"></path>
              <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"></path>
              <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l3.66-2.84z" fill="#FBBC05"></path>
              <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"></path>
            </svg>
            Sign up with Google
          </button>
        </div>
        
        <p class="login-link">
          Already have an account? 
          <router-link to="/login">Sign in</router-link>
        </p>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, reactive, watch } from 'vue';
import { useAuthStore } from '@/stores/auth';
import { buildGoogleOAuthUrl } from '@/utils/oauth';
import Captcha from '@/components/Captcha.vue';
import { PUBLIC_BRAND_LOGO } from '@/config/branding';

const name = ref('');
const email = ref('');
const password = ref('');
const confirmPassword = ref('');
const termsAccepted = ref(false);
const showPassword = ref(false);
const showConfirmPassword = ref(false);
const isLoading = ref(false);
const serverError = ref('');
const authStore = useAuthStore();

// Security state
const captchaVerified = ref(false);
const captchaToken = ref('');

function startGoogleOAuth() {
  serverError.value = '';
  if (authStore.returnUrl) {
    sessionStorage.setItem('oauth_post_login_redirect', authStore.returnUrl);
  }
  window.location.href = buildGoogleOAuthUrl(`${window.location.origin}/login`);
}

const errors = reactive({
  name: '',
  email: '',
  password: '',
  confirmPassword: ''
});

// Password strength checker
const passwordStrength = computed(() => {
  if (!password.value) return { text: '', class: '' };
  
  const hasLength = password.value.length >= 8;
  const hasNumber = /\d/.test(password.value);
  const hasSpecial = /[!@#$%^&*(),.?":{}|<>]/.test(password.value);
  const hasUpper = /[A-Z]/.test(password.value);
  const hasLower = /[a-z]/.test(password.value);
  
  const strength = [hasLength, hasNumber, hasSpecial, hasUpper, hasLower].filter(Boolean).length;
  
  if (strength <= 2) return { text: 'Weak', class: 'weak' };
  if (strength <= 4) return { text: 'Good', class: 'good' };
  return { text: 'Strong', class: 'strong' };
});

// Form validation
const validateForm = () => {
  let isValid = true;
  
  // Reset errors
  Object.keys(errors).forEach(key => errors[key] = '');
  
  // Name validation
  if (!name.value.trim()) {
    errors.name = 'Full name is required';
    isValid = false;
  } else if (name.value.length > 50) {
    errors.name = 'Full name must be less than 50 characters';
    isValid = false;
  }
  
  // Email validation
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  if (!email.value) {
    errors.email = 'Email is required';
    isValid = false;
  } else if (email.value.length > 21) {
    errors.email = 'Email must be less than 21 characters';
    isValid = false;
  } else if (/[<>"'&]/.test(email.value)) {
    errors.email = 'Email contains invalid characters';
    isValid = false;
  } else if (/(https?:\/\/|www\.|\.com|\.net|\.org)/.test(email.value.toLowerCase())) {
    errors.email = 'Email cannot be a URL';
    isValid = false;
  } else if (!emailRegex.test(email.value)) {
    errors.email = 'Please enter a valid email';
    isValid = false;
  }
  
  // Password validation
  if (!password.value) {
    errors.password = 'Password is required';
    isValid = false;
  } else if (password.value.length < 8) {
    errors.password = 'Password must be at least 8 characters';
    isValid = false;
  } else if (password.value.length > 21) {
    errors.password = 'Password must be less than 21 characters';
    isValid = false;
  }
  
  // Confirm password validation
  if (password.value !== confirmPassword.value) {
    errors.confirmPassword = 'Passwords do not match';
    isValid = false;
  }
  
  return isValid;
};

const togglePasswordVisibility = (field) => {
  const input = document.getElementById(field);
  if (field === 'password') {
    showPassword.value = !showPassword.value;
    input.type = showPassword.value ? 'text' : 'password';
  } else {
    showConfirmPassword.value = !showConfirmPassword.value;
    input.type = showConfirmPassword.value ? 'text' : 'password';
  }
};

const register = async () => {
  if (!validateForm()) return;
  
  // CAPTCHA validation
  if (!captchaVerified.value) {
    serverError.value = 'Please complete the CAPTCHA verification';
    return;
  }
  
  isLoading.value = true;
  serverError.value = '';
  
  try {
    await authStore.registerAction({
      name: name.value,
      email: email.value,
      password: password.value,
      password_confirmation: confirmPassword.value,
      captchaToken: captchaToken.value
    });
  } catch (err) {
    console.error('Registration failed:', err);
    
    if (err.response?.data?.errors) {
      Object.keys(err.response.data.errors).forEach(key => {
        if (errors.hasOwnProperty(key)) {
          errors[key] = err.response.data.errors[key][0];
        }
      });
    }
    
    serverError.value = err.response?.data?.message || 'Registration failed. Please try again.';
  } finally {
    isLoading.value = false;
  }
};

// Watch for password changes to clear confirm password error
watch([password, confirmPassword], () => {
  if (errors.confirmPassword && password.value === confirmPassword.value) {
    errors.confirmPassword = '';
  }
});
</script>

<style scoped>
.auth-container {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), url('/background.jpg') no-repeat center center !important;
  background-size: cover !important;
  background-attachment: fixed !important;
  padding: 2rem 1rem;
  position: relative !important;
  z-index: 1 !important;
}

.auth-card {
  width: 100%;
  max-width: 420px;
  background: white;
  border-radius: 12px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
  padding: 2.5rem;
  margin: 0 auto;
  position: relative;
  z-index: 1;
}

.logo-container {
  text-align: center;
  margin-bottom: 1.5rem;
}

.logo {
  height: 60px;
  width: auto;
  margin: 0 auto;
  display: block;
}

h1 {
  font-size: 1.5rem;
  font-weight: 600;
  color: #1a202c;
  text-align: center;
  margin-bottom: 0.5rem;
}

.subtitle {
  color: #718096;
  text-align: center;
  margin-bottom: 2rem;
}

.form-group {
  margin-bottom: 1.5rem;
}

label {
  display: block;
  font-size: 0.875rem;
  font-weight: 500;
  color: #4a5568;
  margin-bottom: 0.5rem;
}

.input-group {
  position: relative;
}

.input-icon {
  position: absolute;
  left: 1rem;
  top: 50%;
  transform: translateY(-50%);
  color: #718096;
}

.form-control {
  width: 100%;
  padding: 0.75rem 1rem 0.75rem 2.5rem;
  border: 1px solid #e2e8f0;
  border-radius: 0.5rem;
  font-size: 1rem;
  transition: border-color 0.2s;
}

.form-control:focus {
  outline: none;
  border-color: #4f46e5;
  box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
}

.toggle-password {
  position: absolute;
  right: 1rem;
  top: 50%;
  transform: translateY(-50%);
  background: none;
  border: none;
  color: #718096;
  cursor: pointer;
  padding: 0.25rem;
}

.toggle-password:hover {
  color: #4f46e5;
}

.error-message {
  color: #e53e3e;
  font-size: 0.875rem;
  margin-top: 0.25rem;
}

.form-control.error {
  border-color: #e53e3e;
}

.password-strength {
  font-size: 0.75rem;
  margin-top: 0.25rem;
}

.password-strength.weak {
  color: #e53e3e;
}

.password-strength.good {
  color: #f59e0b;
}

.password-strength.strong {
  color: #10b981;
}

.terms-container {
  margin-bottom: 1.5rem;
}

.checkbox-container {
  display: flex;
  align-items: flex-start;
  gap: 0.5rem;
  cursor: pointer;
  font-size: 0.875rem;
  color: #4a5568;
}

.checkbox-container input[type="checkbox"] {
  margin-top: 0.125rem;
}

.auth-button {
  width: 100%;
  background-color: #4f46e5;
  color: white;
  padding: 0.75rem 1rem;
  border: none;
  border-radius: 0.5rem;
  font-size: 1rem;
  font-weight: 500;
  cursor: pointer;
  transition: none;
}

.auth-button:hover {
  background-color: #4f46e5;
}

.auth-button:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

.login-link {
  text-align: center;
  margin-top: 1.5rem;
  color: #718096;
}

.login-link a {
  color: #4f46e5;
  text-decoration: none;
}

.login-link a:hover {
  text-decoration: underline;
}

.server-error {
  background: #fef2f2;
  border: 1px solid #fecaca;
  color: #b91c1c;
  padding: 0.75rem 1rem;
  border-radius: 8px;
  font-size: 0.875rem;
  margin-bottom: 1rem;
}

/* CAPTCHA styles */
.captcha-container {
  margin: 1rem 0;
}

.captcha-display {
  margin-bottom: 0.5rem;
}

.captcha-input-field {
  width: 100%;
  padding: 0.5rem;
  border: 1px solid #e2e8f0;
  border-radius: 4px;
  font-size: 0.875rem;
}

.captcha-input-field:focus {
  outline: none;
  border-color: #4f46e5;
}

.divider {
  display: flex;
  align-items: center;
  text-align: center;
  margin: 1.25rem 0;
  color: #718096;
  font-size: 0.875rem;
}

.divider::before,
.divider::after {
  content: '';
  flex: 1;
  border-bottom: 1px solid #e2e8f0;
}

.divider span {
  padding: 0 1rem;
}

.social-login {
  margin-bottom: 0.5rem;
}

.btn-google {
  width: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  padding: 0.65rem 1rem;
  border: 1px solid #e2e8f0;
  border-radius: 0.5rem;
  background: #fff;
  color: #374151;
  font-size: 0.9375rem;
  font-weight: 500;
  cursor: pointer;
  transition: background 0.2s;
}

.btn-google:hover {
  background: #f9fafb;
}

/* Responsive Design - Mobile First */
@media (max-width: 767px) {
  .auth-container {
    padding: 1rem 0.75rem;
    padding-top: 70px;
  }
  
  .auth-card {
    padding: 1.25rem;
    border-radius: 8px;
  }
  
  h1 {
    font-size: 1.5rem;
    margin-bottom: 0.5rem;
  }
  
  .subtitle {
    font-size: 0.875rem;
    margin-bottom: 1.5rem;
  }
  
  .form-group {
    margin-bottom: 1rem;
  }
  
  label {
    font-size: 0.8rem;
    margin-bottom: 0.375rem;
  }
  
  .form-control {
    padding: 0.625rem 1rem 0.625rem 2.25rem;
    font-size: 0.875rem;
    border-radius: 6px;
  }
  
  .input-icon {
    left: 0.75rem;
  }
  
  .input-icon svg {
    width: 18px;
    height: 18px;
  }
  
  .toggle-password {
    right: 0.75rem;
    padding: 0.2rem;
  }
  
  .toggle-password svg {
    width: 18px;
    height: 18px;
  }
  
  .error-message {
    font-size: 0.75rem;
    margin-top: 0.2rem;
  }
  
  .password-strength {
    font-size: 0.7rem;
    margin-top: 0.2rem;
  }
  
  .terms-container {
    margin-bottom: 1rem;
  }
  
  .checkbox-container {
    font-size: 0.8rem;
    gap: 0.375rem;
  }
  
  .auth-button {
    padding: 0.625rem 0.875rem;
    font-size: 0.875rem;
  }
  
  .login-link {
    margin-top: 1rem;
    font-size: 0.8rem;
  }
  
  .server-error {
    font-size: 0.8rem;
    padding: 0.625rem 0.875rem;
    margin-bottom: 0.75rem;
  }
  
  .divider {
    margin: 1rem 0;
    font-size: 0.8rem;
  }
  
  .social-login {
    margin-bottom: 0.375rem;
  }
  
  .btn-google {
    padding: 0.5rem 0.875rem;
    font-size: 0.8rem;
  }
}

@media (max-width: 480px) {
  .auth-container {
    padding: 0.5rem 0.25rem;
    padding-top: 60px;
  }
  
  .auth-card {
    padding: 1rem;
    border-radius: 6px;
  }
  
  h1 {
    font-size: 1.375rem;
  }
  
  .subtitle {
    font-size: 0.8rem;
    margin-bottom: 1.25rem;
  }
  
  .form-control {
    padding: 0.5rem 1rem 0.5rem 2rem;
    font-size: 0.8rem;
  }
  
  .input-icon {
    left: 0.5rem;
  }
  
  .input-icon svg {
    width: 16px;
    height: 16px;
  }
  
  .toggle-password {
    right: 0.5rem;
  }
  
  .toggle-password svg {
    width: 16px;
    height: 16px;
  }
  
  .error-message {
    font-size: 0.7rem;
  }
  
  .password-strength {
    font-size: 0.65rem;
  }
  
  .checkbox-container {
    font-size: 0.75rem;
  }
  
  .auth-button {
    padding: 0.5rem 0.75rem;
    font-size: 0.8rem;
  }
  
  .login-link {
    font-size: 0.75rem;
  }
  
  .server-error {
    font-size: 0.75rem;
    padding: 0.5rem 0.75rem;
  }
  
  .btn-google {
    padding: 0.425rem 0.75rem;
    font-size: 0.75rem;
  }
}

/* Landscape orientation for mobile */
@media (max-width: 767px) and (orientation: landscape) {
  .auth-container {
    padding-top: 50px;
  }
  
  .auth-card {
    padding: 1rem;
  }
  
  .subtitle {
    margin-bottom: 1rem;
  }
  
  .form-group {
    margin-bottom: 0.75rem;
  }
}

/* Touch-friendly interactions for mobile */
@media (hover: none) and (pointer: coarse) {
  .form-control:focus {
    box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.2);
  }
  
  .toggle-password:hover {
    color: #718096;
  }
  
  .toggle-password:active {
    color: #4f46e5;
  }
  
  .auth-button:hover {
    background-color: #4f46e5;
  }
  
  .auth-button:active {
    transform: scale(0.98);
  }
  
  .btn-google:hover {
    background: #fff;
  }
  
  .btn-google:active {
    transform: scale(0.98);
  }
}
</style>
