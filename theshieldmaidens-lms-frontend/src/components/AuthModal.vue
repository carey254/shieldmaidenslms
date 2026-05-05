<template>
  <div v-if="isOpen" class="auth-modal-overlay" @click="closeModal">
    <div class="auth-modal-content" @click.stop>
      <div class="auth-modal-header">
        <h2>{{ isLogin ? 'Login to proceed' : 'Create an Account to proceed' }}</h2>
        <button @click="closeModal" class="close-btn">✕</button>
      </div>
      
      <div class="auth-modal-body">
        <!-- Success Message -->
        <div v-if="successMessage" class="message success-message">
          {{ successMessage }}
        </div>
        
        <!-- Error Message -->
        <div v-if="errorMessage" class="message error-message">
          {{ errorMessage }}
        </div>
        
        <!-- Forgot Password Form -->
        <form v-if="showForgotPasswordForm" @submit.prevent="handleForgotPassword" class="auth-form">
          <div class="email-notice">
            <p>🔐 Reset password for: <strong>{{ emailInput }}</strong></p>
            <button @click="backToLogin" class="change-email-btn">Back to login</button>
          </div>
          
          <div v-if="!resetToken" class="form-group">
            <label for="forgotEmail">Email Address</label>
            <input 
              type="email" 
              id="forgotEmail" 
              v-model="forgotPasswordForm.email" 
              placeholder="Enter your email address"
              required
            />
          </div>
          
          <div v-if="!resetToken" class="form-group">
            <label>Security Verification</label>
            <Captcha ref="forgotPasswordCaptcha" />
          </div>
          
          <div v-if="resetToken">
            <div class="form-group">
              <label for="resetPassword">New Password</label>
              <input 
                type="password" 
                id="resetPassword" 
                v-model="forgotPasswordForm.password" 
                placeholder="Enter new password"
                required
              />
            </div>
            
            <div class="form-group">
              <label for="resetPasswordConfirm">Confirm New Password</label>
              <input 
                type="password" 
                id="resetPasswordConfirm" 
                v-model="forgotPasswordForm.password_confirmation" 
                placeholder="Confirm new password"
                required
              />
            </div>
          </div>
          
          <button type="submit" class="auth-submit-btn" :disabled="isLoading">
            {{ isLoading ? (resetToken ? 'Resetting...' : 'Sending...') : (resetToken ? 'Reset Password' : 'Send Reset Link') }}
          </button>
        </form>
        
        <!-- Email Input Form (First Step) -->
        <form v-if="!emailChecked" @submit.prevent="checkEmail" class="auth-form">
          <div class="form-group">
            <label for="emailInput">Email Address</label>
            <input 
              type="email" 
              id="emailInput" 
              v-model="emailInput" 
              placeholder="Enter your email address"
              required
              :disabled="isCheckingEmail"
            />
            <small class="form-help">We'll check if you have an account with us</small>
          </div>
          
          <button type="submit" class="auth-submit-btn" :disabled="isLoading || isCheckingEmail">
            {{ isCheckingEmail ? 'Checking...' : 'Continue' }}
          </button>
        </form>
        
        <!-- Sign Up Form -->
        <form v-else-if="emailChecked && !emailExists" @submit.prevent="handleSignup" class="auth-form">
          <div class="email-notice">
            <p>📧 Creating account for: <strong>{{ emailInput }}</strong></p>
            <button @click="resetEmail" class="change-email-btn">Change email</button>
          </div>
          
          <div class="form-row">
            <div class="form-group">
              <label for="firstName">First Name</label>
              <input 
                type="text" 
                id="firstName" 
                v-model="signupForm.firstName" 
                placeholder="First Name"
                required
              />
            </div>
            <div class="form-group">
              <label for="lastName">Last Name</label>
              <input 
                type="text" 
                id="lastName" 
                v-model="signupForm.lastName" 
                placeholder="Last Name"
                required
              />
            </div>
          </div>
          
          <div class="form-group">
            <label for="gender">Gender</label>
            <select id="gender" v-model="signupForm.gender" required>
              <option value="">Select Gender</option>
              <option value="male">Male</option>
              <option value="female">Female</option>
              <option value="other">Other</option>
            </select>
          </div>
          
          <div class="form-group">
            <label for="password">Password</label>
            <input 
              type="password" 
              id="password" 
              v-model="signupForm.password" 
              placeholder="Password"
              required
            />
          </div>
          
          <div class="form-group">
            <label for="confirmPassword">Confirm Password</label>
            <input 
              type="password" 
              id="confirmPassword" 
              v-model="signupForm.confirmPassword" 
              placeholder="Confirm Password"
              required
            />
          </div>
          
          <div class="form-group">
            <label>Security Verification</label>
            <Captcha ref="signupCaptcha" />
          </div>
          
          <button type="submit" class="auth-submit-btn" :disabled="isLoading">
            {{ isLoading ? 'Creating Account...' : 'Sign Up' }}
          </button>
        </form>
        
        <!-- Login Form -->
        <form v-else-if="emailChecked && emailExists" @submit.prevent="handleLogin" class="auth-form">
          <div class="email-notice">
            <p>🔐 Welcome back! <strong>{{ emailInput }}</strong></p>
            <button @click="resetEmail" class="change-email-btn">Change email</button>
          </div>
          
          <div class="form-group">
            <label for="loginPassword">Password</label>
            <input 
              type="password" 
              id="loginPassword" 
              v-model="loginForm.password" 
              placeholder="Enter your password"
              required
            />
          </div>
          
          <div class="form-group">
            <label>Security Verification</label>
            <Captcha ref="loginCaptcha" />
          </div>
          
          <button type="submit" class="auth-submit-btn" :disabled="isLoading">
            {{ isLoading ? 'Logging in...' : 'Login' }}
          </button>
          
          <div class="forgot-password">
            <button @click="showForgotPassword" class="forgot-password-btn">
              Forgot your password?
            </button>
          </div>
        </form>
        
        <!-- Toggle between Login and Signup -->
        <div v-if="!emailChecked" class="auth-toggle">
          <span v-if="!isLogin">
            Already have an account? 
            <button @click="toggleMode" class="toggle-btn">Login Here</button>
          </span>
          <span v-else>
            Don't have an account? 
            <button @click="toggleMode" class="toggle-btn">Sign Up Here</button>
          </span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, defineEmits, defineProps } from 'vue'
import { apiService, type RegisterData, type LoginData } from '@/services/api'
import Captcha from './Captcha.vue'

const props = defineProps<{
  isOpen: boolean
}>()

const emit = defineEmits<{
  close: []
  authSuccess: []
}>()

const isLogin = ref(false)
const isLoading = ref(false)
const successMessage = ref('')
const errorMessage = ref('')
const emailChecked = ref(false)
const emailExists = ref(false)
const isCheckingEmail = ref(false)
const showForgotPasswordForm = ref(false)
const resetToken = ref('')

const signupForm = ref({
  firstName: '',
  lastName: '',
  email: '',
  gender: '',
  country: 'kenya', // Default to Kenya
  password: '',
  confirmPassword: ''
})

const loginForm = ref({
  email: '',
  password: ''
})

const forgotPasswordForm = ref({
  email: '',
  password: '',
  password_confirmation: ''
})

const emailInput = ref('')

// CAPTCHA refs
const signupCaptcha = ref()
const loginCaptcha = ref()
const forgotPasswordCaptcha = ref()

const closeModal = () => {
  emit('close')
  clearMessages()
  // Reset all state
  emailChecked.value = false
  emailExists.value = false
  emailInput.value = ''
  showForgotPasswordForm.value = false
  resetToken.value = ''
  forgotPasswordForm.value = {
    email: '',
    password: '',
    password_confirmation: ''
  }
}

const clearMessages = () => {
  successMessage.value = ''
  errorMessage.value = ''
}

const toggleMode = () => {
  // Don't allow toggle if email has already been checked
  if (emailChecked.value) {
    return
  }
  
  isLogin.value = !isLogin.value
  clearMessages()
  // Reset forms
  signupForm.value = {
    firstName: '',
    lastName: '',
    email: '',
    gender: '',
    country: 'kenya',
    password: '',
    confirmPassword: ''
  }
  loginForm.value = {
    email: '',
    password: ''
  }
}

const showForgotPassword = () => {
  showForgotPasswordForm.value = true
  forgotPasswordForm.value.email = emailInput.value
  clearMessages()
}

const backToLogin = () => {
  showForgotPasswordForm.value = false
  resetToken.value = ''
  forgotPasswordForm.value = {
    email: '',
    password: '',
    password_confirmation: ''
  }
  clearMessages()
}

const handleForgotPassword = async () => {
  if (!resetToken.value) {
    // Validate CAPTCHA before sending reset link
    if (!forgotPasswordCaptcha.value?.isValid()) {
      errorMessage.value = 'Please complete the security verification correctly.'
      return
    }
    
    // Send reset link
    isLoading.value = true
    clearMessages()
    
    try {
      const response = await apiService.secureForgotPassword({
        email: forgotPasswordForm.value.email,
        captcha_token: forgotPasswordCaptcha.value?.getToken() || ''
      })
      resetToken.value = response.reset_token || ''
      successMessage.value = response.message
    } catch (error: any) {
      errorMessage.value = error.message || 'Failed to send reset link. Please try again.'
      // Refresh CAPTCHA on error
      forgotPasswordCaptcha.value?.generateCaptcha()
    } finally {
      isLoading.value = false
    }
  } else {
    // Reset password
    if (forgotPasswordForm.value.password !== forgotPasswordForm.value.password_confirmation) {
      errorMessage.value = 'Passwords do not match!'
      return
    }
    
    if (forgotPasswordForm.value.password.length < 8) {
      errorMessage.value = 'Password must be at least 8 characters long!'
      return
    }
    
    isLoading.value = true
    clearMessages()
    
    try {
      const response = await apiService.resetPassword(
        forgotPasswordForm.value.email,
        resetToken.value,
        forgotPasswordForm.value.password,
        forgotPasswordForm.value.password_confirmation
      )
      
      successMessage.value = response.message
      
      // After successful reset, redirect to login
      setTimeout(() => {
        backToLogin()
        emailChecked.value = false
        emailExists.value = true
        isLogin.value = true
        loginForm.value.email = forgotPasswordForm.value.email
      }, 2000)
      
    } catch (error: any) {
      errorMessage.value = error.message || 'Failed to reset password. Please try again.'
    } finally {
      isLoading.value = false
    }
  }
}

const resetEmail = () => {
  emailChecked.value = false
  emailExists.value = false
  emailInput.value = ''
  clearMessages()
}

const checkEmail = async () => {
  if (!emailInput.value) {
    errorMessage.value = 'Please enter your email address'
    return
  }
  
  isCheckingEmail.value = true
  clearMessages()
  
  try {
    console.log('Checking email:', emailInput.value)
    const response = await apiService.checkEmail(emailInput.value)
    console.log('API response:', response)
    
    emailExists.value = response.exists
    emailChecked.value = true
    
    // Set the appropriate mode based on email existence
    isLogin.value = response.exists
    
    console.log('Debug: emailChecked=' + emailChecked.value + ', emailExists=' + emailExists.value + ', isLogin=' + isLogin.value)
    
    // Pre-fill forms with the email
    if (response.exists) {
      loginForm.value.email = emailInput.value
      console.log('Email exists, showing login form')
    } else {
      signupForm.value.email = emailInput.value
      console.log('Email does not exist, showing signup form')
    }
    
  } catch (error: any) {
    console.error('Email check error:', error)
    errorMessage.value = error.message || 'Failed to check email. Please try again.'
  } finally {
    isCheckingEmail.value = false
  }
}

const handleSignup = async () => {
  if (signupForm.value.password !== signupForm.value.confirmPassword) {
    errorMessage.value = 'Passwords do not match!'
    return
  }
  
  if (signupForm.value.password.length < 8) {
    errorMessage.value = 'Password must be at least 8 characters long!'
    return
  }
  
  if (signupForm.value.password.length > 21) {
    errorMessage.value = 'Password cannot exceed 21 characters!'
    return
  }
  
  // Validate CAPTCHA
  if (!signupCaptcha.value?.isValid()) {
    errorMessage.value = 'Please complete the security verification correctly.'
    return
  }
  
  isLoading.value = true
  clearMessages()
  
  try {
    const fullName = `${signupForm.value.firstName} ${signupForm.value.lastName}`
    
    const registerData: RegisterData = {
      name: fullName,
      email: signupForm.value.email,
      password: signupForm.value.password,
      password_confirmation: signupForm.value.confirmPassword,
      captcha_token: signupCaptcha.value?.getToken() || ''
    }
    
    const response = await apiService.secureRegister(registerData)
    
    // Store auth data
    apiService.setAuthData(response)
    
    successMessage.value = 'Account created successfully! You can now login with your credentials.'
    
    // Switch to login mode after successful registration
    setTimeout(() => {
      isLogin.value = true
      loginForm.value.email = signupForm.value.email
      signupForm.value = {
        firstName: '',
        lastName: '',
        email: '',
        gender: '',
        country: 'kenya',
        password: '',
        confirmPassword: ''
      }
    }, 2000)
    
  } catch (error: any) {
    errorMessage.value = error.message || 'Registration failed. Please try again.'
    // Refresh CAPTCHA on error
    signupCaptcha.value?.generateCaptcha()
  } finally {
    isLoading.value = false
  }
}

const handleLogin = async () => {
  // Validate CAPTCHA
  if (!loginCaptcha.value?.isValid()) {
    errorMessage.value = 'Please complete the security verification correctly.'
    return
  }
  
  isLoading.value = true
  clearMessages()
  
  try {
    const loginData: LoginData = {
      email: loginForm.value.email,
      password: loginForm.value.password,
      captcha_token: loginCaptcha.value?.getToken() || ''
    }
    
    const response = await apiService.secureLogin(loginData)
    
    // Store auth data
    apiService.setAuthData(response)
    
    successMessage.value = 'Login successful! Redirecting to dashboard...'
    
    // Emit success after a short delay to show the success message
    setTimeout(() => {
      emit('authSuccess')
      closeModal()
    }, 1500)
    
  } catch (error: any) {
    errorMessage.value = error.message || 'Login failed. Please check your credentials.'
    // Refresh CAPTCHA on error
    loginCaptcha.value?.generateCaptcha()
  } finally {
    isLoading.value = false
  }
}
</script>

<style scoped>
.auth-modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  padding: 20px;
}

.auth-modal-content {
  background: white;
  border-radius: 16px;
  max-width: 500px;
  width: 100%;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
  animation: modalSlideIn 0.3s ease-out;
}

@keyframes modalSlideIn {
  from {
    opacity: 0;
    transform: translateY(-50px) scale(0.9);
  }
  to {
    opacity: 1;
    transform: translateY(0) scale(1);
  }
}

.auth-modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 25px 30px;
  border-bottom: 1px solid #e0e0e0;
  background: linear-gradient(135deg, #00897b, #26a69a);
  color: white;
  border-radius: 16px 16px 0 0;
}

.auth-modal-header h2 {
  margin: 0;
  font-size: 24px;
  font-weight: 600;
}

.close-btn {
  background: rgba(255, 255, 255, 0.2);
  border: none;
  width: 36px;
  height: 36px;
  border-radius: 50%;
  color: white;
  font-size: 18px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.3s ease;
}

.close-btn:hover {
  background: rgba(255, 255, 255, 0.3);
  transform: rotate(90deg);
}

.auth-modal-body {
  padding: 30px;
}

.message {
  padding: 12px 15px;
  border-radius: 8px;
  margin-bottom: 20px;
  font-size: 14px;
  font-weight: 500;
}

.success-message {
  background: #e8f5e8;
  color: #2e7d32;
  border: 1px solid #4caf50;
}

.error-message {
  background: #ffebee;
  color: #c62828;
  border: 1px solid #f44336;
}

.auth-form {
  margin-bottom: 20px;
}

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 15px;
  margin-bottom: 15px;
}

.form-group {
  margin-bottom: 15px;
}

.form-group label {
  display: block;
  margin-bottom: 5px;
  color: #333;
  font-weight: 500;
  font-size: 14px;
}

.form-group input,
.form-group select {
  width: 100%;
  padding: 12px 15px;
  border: 1px solid #e0e0e0;
  border-radius: 8px;
  font-size: 14px;
  transition: border-color 0.3s ease;
}

.form-group input:focus,
.form-group select:focus {
  outline: none;
  border-color: #00897b;
}

.auth-submit-btn {
  width: 100%;
  padding: 14px 20px;
  background: linear-gradient(135deg, #00897b, #26a69a);
  color: white;
  border: none;
  border-radius: 8px;
  font-size: 16px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  margin-top: 10px;
}

.auth-submit-btn:hover {
  background: linear-gradient(135deg, #00695c, #00897b);
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 137, 123, 0.3);
}

.auth-toggle {
  text-align: center;
  padding-top: 20px;
  border-top: 1px solid #e0e0e0;
  color: #666;
  font-size: 14px;
}

.toggle-btn {
  background: none;
  border: none;
  color: #00897b;
  font-weight: 600;
  cursor: pointer;
  text-decoration: underline;
  font-size: 14px;
}

.toggle-btn:hover {
  color: #00695c;
}

.form-help {
  display: block;
  margin-top: 5px;
  color: #666;
  font-size: 12px;
  font-style: italic;
}

.email-notice {
  background: #f0f8ff;
  border: 1px solid #b3d9ff;
  border-radius: 8px;
  padding: 12px;
  margin-bottom: 20px;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.email-notice p {
  margin: 0;
  color: #0066cc;
  font-size: 14px;
}

.change-email-btn {
  background: none;
  border: none;
  color: #0066cc;
  font-size: 12px;
  cursor: pointer;
  text-decoration: underline;
  padding: 4px 8px;
  border-radius: 4px;
  transition: background-color 0.2s;
}

.change-email-btn:hover {
  background-color: #e6f2ff;
}

.forgot-password {
  text-align: center;
  margin-top: 15px;
  padding-top: 15px;
  border-top: 1px solid #e0e0e0;
}

.forgot-password-btn {
  background: none;
  border: none;
  color: #00897b;
  font-size: 13px;
  cursor: pointer;
  text-decoration: underline;
  transition: color 0.2s;
}

.forgot-password-btn:hover {
  color: #00695c;
}

/* Responsive Design */
@media (max-width: 600px) {
  .form-row {
    grid-template-columns: 1fr;
    gap: 10px;
  }
  
  .auth-modal-content {
    margin: 10px;
    max-height: 95vh;
  }
  
  .auth-modal-header,
  .auth-modal-body {
    padding: 20px;
  }
}
</style>
