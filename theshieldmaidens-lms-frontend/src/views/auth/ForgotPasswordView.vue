<template>
  <div class="auth-container">
    <div class="auth-card">
      <div class="logo-container">
        <img src="/logo.png" alt="The Shield Maidens" class="logo">
      </div>
      
      <h1>Reset Your Password</h1>
      <p class="subtitle">Enter your email and we'll send you a link to reset your password</p>
      
      <form @submit.prevent="handleSubmit" class="auth-form">
        <div v-if="message" class="alert" :class="{ 'success': isSuccess, 'error': !isSuccess }">
          {{ message }}
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
              :disabled="isLoading"
            >
          </div>
        </div>
        
        <button type="submit" class="auth-button" :disabled="isLoading">
          <span v-if="!isLoading">Send Reset Link</span>
          <span v-else>Sending...</span>
        </button>
        
        <p class="text-center mt-4">
          Remember your password? 
          <router-link to="/login" class="text-primary-500 hover:underline">Back to Login</router-link>
        </p>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';

const router = useRouter();
const API_BASE_URL = localStorage.getItem('apiBaseUrl') || 'http://localhost:3000/api';
const email = ref('');
const message = ref('');
const isSuccess = ref(false);
const isLoading = ref(false);

const handleSubmit = async () => {
  isLoading.value = true;
  message.value = '';
  
  try {
    const response = await axios.post(`${API_BASE_URL}/forgot-password`, {
      email: email.value
    });
    
    isSuccess.value = true;
    message.value = response.data.message || 'If an account exists with this email, you will receive a password reset link.';
    
    // Clear form
    email.value = '';
    
  } catch (error) {
    isSuccess.value = false;
    message.value = error.response?.data?.message || 'An error occurred. Please try again.';
    console.error('Password reset error:', error);
  } finally {
    isLoading.value = false;
  }
};
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
  max-width: 28rem;
  background: white;
  border-radius: 0.5rem;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
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
  height: 3.5rem;
  width: auto;
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
  color: #a0aec0;
}

.form-control {
  width: 100%;
  padding: 0.75rem 1rem 0.75rem 2.75rem;
  border: 1px solid #e2e8f0;
  border-radius: 0.375rem;
  font-size: 0.875rem;
  color: #1a202c;
  transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
}

.form-control:focus {
  outline: none;
  border-color: #4f46e5;
  box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
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

.alert {
  padding: 0.75rem 1rem;
  border-radius: 0.375rem;
  margin-bottom: 1.5rem;
  font-size: 0.875rem;
}

.alert.success {
  background-color: #ecfdf5;
  color: #065f46;
  border: 1px solid #a7f3d0;
}

.alert.error {
  background-color: #fef2f2;
  color: #b91c1c;
  border: 1px solid #fecaca;
}

.text-center {
  text-align: center;
}

.mt-4 {
  margin-top: 1rem;
}

.text-primary-500 {
  color: #4f46e5;
}

.hover\:underline:hover {
  text-decoration: underline;
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
  
  .auth-button {
    padding: 0.625rem 0.875rem;
    font-size: 0.875rem;
  }
  
  .alert {
    font-size: 0.8rem;
    padding: 0.625rem 0.875rem;
    margin-bottom: 1rem;
  }
  
  .text-center {
    margin-top: 0.75rem;
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
  
  .auth-button {
    padding: 0.5rem 0.75rem;
    font-size: 0.8rem;
  }
  
  .alert {
    font-size: 0.75rem;
    padding: 0.5rem 0.75rem;
  }
  
  .text-center {
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
  
  .auth-button:hover {
    background-color: #4f46e5;
  }
  
  .auth-button:active {
    transform: scale(0.98);
  }
  
  .text-primary-500:hover {
    color: #4f46e5;
  }
  
  .text-primary-500:active {
    color: #3730a3;
  }
}
</style>
