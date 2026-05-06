<template>
  <div class="security-view">
    <div class="page-header">
      <h1>Security Settings</h1>
      <p>Manage your password and authentication preferences</p>
    </div>

    <div class="security-content">
      <div class="security-card">
        <h2>Change Password</h2>
        <form @submit.prevent="changePassword" class="security-form">
          <div class="form-group">
            <label for="currentPassword">Current Password</label>
            <input 
              type="password" 
              id="currentPassword" 
              v-model="passwordForm.currentPassword" 
              required
              class="form-control"
            />
          </div>

          <div class="form-group">
            <label for="newPassword">New Password</label>
            <input 
              type="password" 
              id="newPassword" 
              v-model="passwordForm.newPassword" 
              required
              class="form-control"
            />
            <div class="password-strength">
              <div class="strength-bar">
                <div class="strength-fill" :class="passwordStrength.class"></div>
              </div>
              <span class="strength-text">{{ passwordStrength.text }}</span>
            </div>
          </div>

          <div class="form-group">
            <label for="confirmPassword">Confirm New Password</label>
            <input 
              type="password" 
              id="confirmPassword" 
              v-model="passwordForm.confirmPassword" 
              required
              class="form-control"
            />
            <div v-if="passwordMismatch" class="error-message">
              Passwords do not match
            </div>
          </div>

          <div class="form-actions">
            <button type="submit" class="btn btn-primary" :disabled="loading || passwordMismatch">
              <i v-if="loading" class="fas fa-spinner fa-spin"></i>
              {{ loading ? 'Updating...' : 'Update Password' }}
            </button>
          </div>
        </form>
      </div>

      <div class="security-card">
        <h2>Two-Factor Authentication</h2>
        <div class="two-factor-section">
          <div class="two-factor-status">
            <div class="status-indicator" :class="{ active: twoFactorEnabled }">
              <i :class="twoFactorEnabled ? 'fas fa-check-circle' : 'fas fa-times-circle'"></i>
            </div>
            <div class="status-text">
              <h3>{{ twoFactorEnabled ? 'Enabled' : 'Disabled' }}</h3>
              <p>{{ twoFactorEnabled ? 'Your account is protected with 2FA' : 'Add an extra layer of security to your account' }}</p>
            </div>
          </div>
          
          <button 
            @click="toggleTwoFactor" 
            class="btn"
            :class="twoFactorEnabled ? 'btn-danger' : 'btn-success'"
          >
            <i :class="twoFactorEnabled ? 'fas fa-shield-alt' : 'fas fa-lock'"></i>
            {{ twoFactorEnabled ? 'Disable 2FA' : 'Enable 2FA' }}
          </button>
        </div>
      </div>

      <div class="security-card">
        <h2>Active Sessions</h2>
        <div class="sessions-list">
          <div v-for="session in sessions" :key="session.id" class="session-item">
            <div class="session-info">
              <div class="session-device">
                <i :class="getDeviceIcon(session.device)"></i>
                <div>
                  <h4>{{ session.device }}</h4>
                  <p>{{ session.browser }} on {{ session.os }}</p>
                </div>
              </div>
              <div class="session-time">
                <span class="current-session" v-if="session.current">Current session</span>
                <span class="last-active">Last active: {{ formatTime(session.lastActive) }}</span>
              </div>
            </div>
            <button 
              v-if="!session.current"
              @click="terminateSession(session.id)" 
              class="btn btn-danger btn-sm"
            >
              <i class="fas fa-sign-out-alt"></i> Terminate
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'

const authStore = useAuthStore()
const loading = ref(false)
const twoFactorEnabled = ref(false)

const passwordForm = ref({
  currentPassword: '',
  newPassword: '',
  confirmPassword: ''
})

const sessions = ref([
  {
    id: 1,
    device: 'Windows PC',
    browser: 'Chrome',
    os: 'Windows 10',
    lastActive: new Date(),
    current: true
  },
  {
    id: 2,
    device: 'iPhone',
    browser: 'Safari',
    os: 'iOS 16',
    lastActive: new Date(Date.now() - 2 * 60 * 60 * 1000), // 2 hours ago
    current: false
  }
])

const passwordMismatch = computed(() => {
  return passwordForm.value.newPassword !== passwordForm.value.confirmPassword && 
         passwordForm.value.confirmPassword !== ''
})

const passwordStrength = computed(() => {
  const password = passwordForm.value.newPassword
  if (!password) return { class: '', text: '' }
  
  let strength = 0
  if (password.length >= 8) strength++
  if (password.match(/[a-z]/) && password.match(/[A-Z]/)) strength++
  if (password.match(/[0-9]/)) strength++
  if (password.match(/[^a-zA-Z0-9]/)) strength++
  
  const levels = [
    { class: 'weak', text: 'Weak password' },
    { class: 'fair', text: 'Fair password' },
    { class: 'good', text: 'Good password' },
    { class: 'strong', text: 'Strong password' }
  ]
  
  return levels[strength] || levels[0]
})

const changePassword = async () => {
  if (passwordMismatch.value) return
  
  loading.value = true
  try {
    await authStore.changePassword(passwordForm.value)
    
    // Reset form
    passwordForm.value = {
      currentPassword: '',
      newPassword: '',
      confirmPassword: ''
    }
    
    alert('Password changed successfully!')
  } catch (error) {
    console.error('Error changing password:', error)
    alert('Error changing password. Please try again.')
  } finally {
    loading.value = false
  }
}

const toggleTwoFactor = () => {
  // TODO: Implement 2FA setup/disable
  twoFactorEnabled.value = !twoFactorEnabled.value
  alert(`Two-factor authentication ${twoFactorEnabled.value ? 'enabled' : 'disabled'}`)
}

const terminateSession = async (sessionId) => {
  if (confirm('Are you sure you want to terminate this session?')) {
    try {
      await authStore.terminateSession(sessionId)
      sessions.value = sessions.value.filter(s => s.id !== sessionId)
      alert('Session terminated successfully')
    } catch (error) {
      console.error('Error terminating session:', error)
      alert('Error terminating session. Please try again.')
    }
  }
}

const getDeviceIcon = (device) => {
  const icons = {
    'Windows PC': 'fas fa-desktop',
    'iPhone': 'fas fa-mobile-alt',
    'Mac': 'fas fa-laptop',
    'Android': 'fas fa-mobile-alt'
  }
  return icons[device] || 'fas fa-desktop'
}

const formatTime = (date) => {
  const now = new Date()
  const diff = now - date
  const minutes = Math.floor(diff / 60000)
  const hours = Math.floor(diff / 3600000)
  const days = Math.floor(diff / 86400000)
  
  if (minutes < 60) return `${minutes} minutes ago`
  if (hours < 24) return `${hours} hours ago`
  return `${days} days ago`
}

onMounted(() => {
  // Load current security settings
  // TODO: Connect to actual API
})
</script>

<style scoped>
.security-view {
  padding: 20px;
  max-width: 1000px;
  margin: 0 auto;
}

.page-header {
  margin-bottom: 30px;
}

.page-header h1 {
  color: #333;
  font-size: 2rem;
  margin-bottom: 10px;
}

.page-header p {
  color: #666;
  font-size: 1rem;
}

.security-content {
  display: flex;
  flex-direction: column;
  gap: 30px;
}

.security-card {
  background: white;
  padding: 30px;
  border-radius: 12px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

.security-card h2 {
  color: #333;
  font-size: 1.3rem;
  margin-bottom: 20px;
}

.form-group {
  margin-bottom: 20px;
}

.form-group label {
  display: block;
  margin-bottom: 8px;
  color: #333;
  font-weight: 500;
}

.form-control {
  width: 100%;
  padding: 12px;
  border: 1px solid #ddd;
  border-radius: 6px;
  font-size: 1rem;
  transition: border-color 0.3s ease;
}

.form-control:focus {
  outline: none;
  border-color: #007bff;
  box-shadow: 0 0 0 2px rgba(0, 123, 255, 0.25);
}

.password-strength {
  margin-top: 8px;
}

.strength-bar {
  width: 100%;
  height: 4px;
  background: #e9ecef;
  border-radius: 2px;
  overflow: hidden;
  margin-bottom: 5px;
}

.strength-fill {
  height: 100%;
  transition: width 0.3s ease;
}

.strength-fill.weak { width: 25%; background: #dc3545; }
.strength-fill.fair { width: 50%; background: #ffc107; }
.strength-fill.good { width: 75%; background: #28a745; }
.strength-fill.strong { width: 100%; background: #007bff; }

.strength-text {
  font-size: 0.8rem;
  color: #666;
}

.error-message {
  color: #dc3545;
  font-size: 0.8rem;
  margin-top: 5px;
}

.form-actions {
  display: flex;
  gap: 10px;
  margin-top: 30px;
}

.btn {
  padding: 12px 24px;
  border: none;
  border-radius: 6px;
  font-size: 1rem;
  cursor: pointer;
  transition: all 0.3s ease;
  display: inline-flex;
  align-items: center;
  gap: 8px;
}

.btn-primary {
  background: #007bff;
  color: white;
}

.btn-primary:hover:not(:disabled) {
  background: #0056b3;
}

.btn-primary:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.btn-success {
  background: #28a745;
  color: white;
}

.btn-success:hover {
  background: #1e7e34;
}

.btn-danger {
  background: #dc3545;
  color: white;
}

.btn-danger:hover {
  background: #c82333;
}

.btn-sm {
  padding: 8px 16px;
  font-size: 0.8rem;
}

.two-factor-section {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 20px;
}

.two-factor-status {
  display: flex;
  align-items: center;
  gap: 15px;
}

.status-indicator {
  width: 50px;
  height: 50px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #f8f9fa;
  color: #dc3545;
  font-size: 1.5rem;
}

.status-indicator.active {
  background: #d4edda;
  color: #28a745;
}

.status-text h3 {
  margin: 0 0 5px 0;
  color: #333;
  font-size: 1.1rem;
}

.status-text p {
  margin: 0;
  color: #666;
  font-size: 0.9rem;
}

.sessions-list {
  display: flex;
  flex-direction: column;
  gap: 15px;
}

.session-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px;
  border: 1px solid #e9ecef;
  border-radius: 8px;
}

.session-info {
  display: flex;
  align-items: center;
  gap: 20px;
}

.session-device {
  display: flex;
  align-items: center;
  gap: 12px;
}

.session-device i {
  font-size: 1.5rem;
  color: #6c757d;
}

.session-device h4 {
  margin: 0 0 5px 0;
  color: #333;
  font-size: 1rem;
}

.session-device p {
  margin: 0;
  color: #666;
  font-size: 0.8rem;
}

.session-time {
  text-align: right;
}

.current-session {
  display: block;
  color: #28a745;
  font-size: 0.8rem;
  font-weight: 500;
  margin-bottom: 5px;
}

.last-active {
  color: #666;
  font-size: 0.8rem;
}

@media (max-width: 768px) {
  .two-factor-section {
    flex-direction: column;
    align-items: flex-start;
  }
  
  .session-item {
    flex-direction: column;
    align-items: flex-start;
    gap: 15px;
  }
  
  .session-info {
    flex-direction: column;
    align-items: flex-start;
    gap: 10px;
  }
}
</style>
