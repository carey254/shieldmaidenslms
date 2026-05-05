<template>
  <div class="auth-container">
    <div class="auth-card">
      <div class="logo-container">
        <div class="welcome-badge">🔐</div>
      </div>
      
      <h1>Change Your Password</h1>
      <p class="subtitle">For security, please set a new password for your account</p>
      
      <form @submit.prevent="changePassword" class="auth-form">
        <div class="form-group">
          <label for="current_password">Current Password</label>
          <div class="input-group">
            <span class="input-icon">
              <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
              </svg>
            </span>
            <input
              id="current_password"
              v-model="currentPassword"
              type="password"
              placeholder="Enter your current password"
              required
              class="form-control"
            >
          </div>
        </div>

        <div class="form-group">
          <label for="new_password">New Password</label>
          <div class="input-group">
            <span class="input-icon">
              <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
              </svg>
            </span>
            <input
              id="new_password"
              v-model="newPassword"
              type="password"
              placeholder="Enter your new password"
              required
              class="form-control"
            >
          </div>
        </div>

        <div class="form-group">
          <label for="password_confirmation">Confirm New Password</label>
          <div class="input-group">
            <span class="input-icon">
              <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
              </svg>
            </span>
            <input
              id="password_confirmation"
              v-model="passwordConfirmation"
              type="password"
              placeholder="Confirm your new password"
              required
              class="form-control"
            >
          </div>
        </div>

        <!-- Password Requirements -->
        <div class="password-requirements">
          <h4>Password Requirements:</h4>
          <ul>
            <li :class="{ valid: newPassword.length >= 8 }">
              <span class="check-icon">{{ newPassword.length >= 8 ? '✓' : '○' }}</span>
              At least 8 characters
            </li>
            <li :class="{ valid: /[A-Z]/.test(newPassword) }">
              <span class="check-icon">{{ /[A-Z]/.test(newPassword) ? '✓' : '○' }}</span>
              One uppercase letter
            </li>
            <li :class="{ valid: /[a-z]/.test(newPassword) }">
              <span class="check-icon">{{ /[a-z]/.test(newPassword) ? '✓' : '○' }}</span>
              One lowercase letter
            </li>
            <li :class="{ valid: /[0-9]/.test(newPassword) }">
              <span class="check-icon">{{ /[0-9]/.test(newPassword) ? '✓' : '○' }}</span>
              One number
            </li>
          </ul>
        </div>
        
        <button type="submit" class="btn btn-primary" :disabled="isLoading || !isFormValid">
          <span v-if="isLoading">Changing Password...</span>
          <span v-else>Change Password</span>
        </button>
        
        <div v-if="error" class="error-message">
          {{ error }}
        </div>
        
        <div v-if="success" class="success-message">
          {{ success }}
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import axios from 'axios';

const currentPassword = ref('');
const newPassword = ref('');
const passwordConfirmation = ref('');
const isLoading = ref(false);
const error = ref('');
const success = ref('');

const router = useRouter();
const route = useRoute();
const authStore = useAuthStore();

const API_BASE_URL = localStorage.getItem('apiBaseUrl') || 'http://localhost:3000/api';

const isFormValid = computed(() => {
  return newPassword.value.length >= 8 &&
         /[A-Z]/.test(newPassword.value) &&
         /[a-z]/.test(newPassword.value) &&
         /[0-9]/.test(newPassword.value) &&
         newPassword.value === passwordConfirmation.value;
});

const changePassword = async () => {
  if (isLoading.value || !isFormValid.value) return;
  
  error.value = '';
  success.value = '';
  isLoading.value = true;
  
  try {
    const response = await axios.post(`${API_BASE_URL}/change-password`, {
      current_password: currentPassword.value,
      new_password: newPassword.value,
      new_password_confirmation: passwordConfirmation.value
    }, {
      headers: {
        'Authorization': `Bearer ${authStore.token}`,
        'Content-Type': 'application/json'
      }
    });
    
    success.value = 'Password changed successfully! Redirecting to dashboard...';
    
    // Redirect to appropriate dashboard after successful password change
    setTimeout(() => {
      if (authStore.isAdmin) {
        router.push('/admin/dashboard');
      } else if (authStore.isInstructor) {
        router.push('/instructor/dashboard');
      } else {
        router.push('/dashboard');
      }
    }, 2000);
    
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to change password. Please try again.';
  } finally {
    isLoading.value = false;
  }
};
</script>

<style scoped>
:root {
  --primary: #4F46E5;
  --primary-hover: #4338CA;
  --text: #1F2937;
  --text-light: #6B7280;
  --border: #E5E7EB;
  --error: #EF4444;
  --success: #10B981;
  --background: #F9FAFB;
  --card-bg: #FFFFFF;
}

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
  background: linear-gradient(135deg, #f5f5faff 0%, #f5f5faff 100%);
  padding: 2rem 1rem;
}

.auth-card {
  width: 100%;
  max-width: 450px;
  background: var(--card-bg);
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

.welcome-badge {
  font-size: 3rem;
  margin-bottom: 1rem;
}

h1 {
  font-size: 1.75rem;
  font-weight: 700;
  color: var(--text);
  text-align: center;
  margin-bottom: 0.5rem;
}

.subtitle {
  color: var(--text-light);
  text-align: center;
  margin-bottom: 2rem;
  font-size: 0.95rem;
}

.form-group {
  margin-bottom: 1.5rem;
}

.form-group label {
  display: block;
  color: var(--text);
  font-weight: 500;
  margin-bottom: 0.5rem;
  font-size: 0.9rem;
}

.input-group {
  position: relative;
  display: flex;
  align-items: center;
}

.input-icon {
  position: absolute;
  left: 12px;
  color: var(--text-light);
  pointer-events: none;
  z-index: 1;
}

.form-control {
  width: 100%;
  padding: 0.75rem 0.75rem 0.75rem 2.5rem;
  border: 1px solid var(--border);
  border-radius: 8px;
  font-size: 0.9rem;
  transition: border-color 0.3s ease, box-shadow 0.3s ease;
}

.form-control:focus {
  outline: none;
  border-color: var(--primary);
  box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
}

.password-requirements {
  background: #f8f9fa;
  border-radius: 8px;
  padding: 1rem;
  margin-bottom: 1.5rem;
}

.password-requirements h4 {
  font-size: 0.9rem;
  font-weight: 600;
  margin-bottom: 0.5rem;
  color: var(--text);
}

.password-requirements ul {
  list-style: none;
  padding: 0;
}

.password-requirements li {
  font-size: 0.85rem;
  color: var(--text-light);
  margin-bottom: 0.25rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.password-requirements li.valid {
  color: var(--success);
}

.check-icon {
  font-weight: bold;
  font-size: 0.9rem;
}

.btn {
  width: 100%;
  padding: 0.875rem;
  border-radius: 8px;
  font-weight: 600;
  font-size: 0.9rem;
  cursor: pointer;
  transition: all 0.3s ease;
  border: none;
  text-align: center;
}

.btn-primary {
  background: linear-gradient(135deg, #e68c07ff 0%, #cc8809ff 100%);
  color: white;
}

.btn-primary:hover:not(:disabled) {
  background: linear-gradient(135deg, #1d1d1fff 0%, #080808ff 100%);
}

.btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
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

.success-message {
  margin-top: 1rem;
  padding: 0.75rem;
  background-color: #D1FAE5;
  border: 1px solid #A7F3D0;
  border-radius: 0.375rem;
  color: #065F46;
  font-size: 0.875rem;
  text-align: center;
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
</style>
