<template>
  <div class="instructor-settings">
    <div class="settings-header">
      <h1>Instructor Settings</h1>
      <p>Manage your account settings and preferences</p>
    </div>

    <div class="settings-container">
      <!-- Profile Settings -->
      <div class="settings-section">
        <h3>Profile Information</h3>
        <div class="form-group">
          <label>Full Name</label>
          <input v-model="profile.name" type="text" class="form-input">
        </div>
        <div class="form-group">
          <label>Email Address</label>
          <input v-model="profile.email" type="email" class="form-input" readonly>
        </div>
        <div class="form-group">
          <label>Phone Number</label>
          <input v-model="profile.phone" type="tel" class="form-input" placeholder="+254 XXX XXX XXX">
        </div>
        <div class="form-group">
          <label>Bio</label>
          <textarea v-model="profile.bio" class="form-textarea" placeholder="Tell us about yourself..."></textarea>
        </div>
        <button @click="updateProfile" class="btn btn-primary">Update Profile</button>
      </div>

      <!-- Security Settings -->
      <div class="settings-section">
        <h3>Security</h3>
        <div class="form-group">
          <label>Current Password</label>
          <input v-model="password.current" type="password" class="form-input" placeholder="Enter current password">
        </div>
        <div class="form-group">
          <label>New Password</label>
          <input v-model="password.new" type="password" class="form-input" placeholder="Enter new password">
        </div>
        <div class="form-group">
          <label>Confirm New Password</label>
          <input v-model="password.confirm" type="password" class="form-input" placeholder="Confirm new password">
        </div>
        <button @click="changePassword" class="btn btn-primary">Change Password</button>
      </div>

      <!-- Preferences -->
      <div class="settings-section">
        <h3>Preferences</h3>
        <div class="form-group">
          <label>Language</label>
          <select v-model="preferences.language" class="form-select">
            <option value="en">English</option>
            <option value="sw">Swahili</option>
            <option value="ar">Arabic</option>
          </select>
        </div>
        <div class="form-group">
          <label>Timezone</label>
          <select v-model="preferences.timezone" class="form-select">
            <option value="UTC">UTC</option>
            <option value="EAT">East Africa Time (EAT)</option>
            <option value="GMT">Greenwich Mean Time (GMT)</option>
          </select>
        </div>
        <div class="form-group">
          <label>Email Notifications</label>
          <div class="checkbox-group">
            <label class="checkbox-label">
              <input v-model="preferences.emailNotifications" type="checkbox">
              <span>Receive email notifications</span>
            </label>
            <label class="checkbox-label">
              <input v-model="preferences.assignmentAlerts" type="checkbox">
              <span>Assignment submission alerts</span>
            </label>
            <label class="checkbox-label">
              <input v-model="preferences.studentMessages" type="checkbox">
              <span>Student messages</span>
            </label>
          </div>
        </div>
        <button @click="updatePreferences" class="btn btn-primary">Save Preferences</button>
      </div>

      <!-- Course Settings -->
      <div class="settings-section">
        <h3>Course Settings</h3>
        <div class="form-group">
          <label>Default Course Duration</label>
          <select v-model="courseSettings.defaultDuration" class="form-select">
            <option value="2">2 weeks</option>
            <option value="4">4 weeks</option>
            <option value="6">6 weeks</option>
            <option value="8">8 weeks</option>
          </select>
        </div>
        <div class="form-group">
          <label>Auto-approve Submissions</label>
          <label class="checkbox-label">
            <input v-model="courseSettings.autoApprove" type="checkbox">
            <span>Automatically approve assignment submissions</span>
          </label>
        </div>
        <div class="form-group">
          <label>Grading Scale</label>
          <select v-model="courseSettings.gradingScale" class="form-select">
            <option value="percentage">Percentage (0-100)</option>
            <option value="gpa">GPA Scale (0-4.0)</option>
            <option value="letter">Letter Grade (A-F)</option>
          </select>
        </div>
        <button @click="updateCourseSettings" class="btn btn-primary">Save Course Settings</button>
      </div>

      <!-- Account Actions -->
      <div class="settings-section">
        <h3>Account Actions</h3>
        <div class="action-buttons">
          <button @click="exportData" class="btn btn-outline">
            <i class="fas fa-download"></i>
            Export My Data
          </button>
          <button @click="clearCache" class="btn btn-outline">
            <i class="fas fa-broom"></i>
            Clear Cache
          </button>
          <button @click="logout" class="btn btn-danger">
            <i class="fas fa-sign-out-alt"></i>
            Logout
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const authStore = useAuthStore()

const profile = ref({
  name: '',
  email: '',
  phone: '',
  bio: ''
})

const password = ref({
  current: '',
  new: '',
  confirm: ''
})

const preferences = ref({
  language: 'en',
  timezone: 'EAT',
  emailNotifications: true,
  assignmentAlerts: true,
  studentMessages: true
})

const courseSettings = ref({
  defaultDuration: '4',
  autoApprove: false,
  gradingScale: 'percentage'
})

onMounted(() => {
  // Load user data
  if (authStore.user) {
    profile.value.name = authStore.user.name || ''
    profile.value.email = authStore.user.email || ''
    profile.value.phone = authStore.user.phone || ''
    profile.value.bio = authStore.user.bio || ''
  }
  
  // Load saved preferences from localStorage
  const savedPreferences = localStorage.getItem('instructorPreferences')
  if (savedPreferences) {
    const prefs = JSON.parse(savedPreferences)
    preferences.value = { ...preferences.value, ...prefs }
  }
})

const updateProfile = async () => {
  try {
    // API call to update profile
    console.log('Updating profile:', profile.value)
    alert('Profile updated successfully!')
  } catch (error) {
    console.error('Error updating profile:', error)
    alert('Error updating profile. Please try again.')
  }
}

const changePassword = async () => {
  if (password.value.new !== password.value.confirm) {
    alert('New passwords do not match!')
    return
  }
  
  if (password.value.new.length < 6) {
    alert('Password must be at least 6 characters long!')
    return
  }
  
  try {
    // API call to change password
    console.log('Changing password')
    alert('Password changed successfully!')
    
    // Clear password fields
    password.value = { current: '', new: '', confirm: '' }
  } catch (error) {
    console.error('Error changing password:', error)
    alert('Error changing password. Please try again.')
  }
}

const updatePreferences = () => {
  localStorage.setItem('instructorPreferences', JSON.stringify(preferences.value))
  alert('Preferences saved successfully!')
}

const updateCourseSettings = () => {
  localStorage.setItem('courseSettings', JSON.stringify(courseSettings.value))
  alert('Course settings saved successfully!')
}

const exportData = () => {
  const data = {
    profile: profile.value,
    preferences: preferences.value,
    courseSettings: courseSettings.value
  }
  
  const blob = new Blob([JSON.stringify(data, null, 2)], { type: 'application/json' })
  const url = URL.createObjectURL(blob)
  const a = document.createElement('a')
  a.href = url
  a.download = 'instructor-data.json'
  a.click()
  URL.revokeObjectURL(url)
}

const clearCache = () => {
  if (confirm('Are you sure you want to clear all cached data?')) {
    localStorage.removeItem('instructorPreferences')
    localStorage.removeItem('courseSettings')
    alert('Cache cleared successfully!')
  }
}

const logout = async () => {
  if (confirm('Are you sure you want to logout?')) {
    await authStore.logout()
    router.push('/login')
  }
}
</script>

<style scoped>
.instructor-settings {
  padding: 2rem;
  max-width: 1200px;
  margin: 0 auto;
}

.settings-header {
  margin-bottom: 2rem;
}

.settings-header h1 {
  color: #1a365d;
  margin-bottom: 0.5rem;
}

.settings-header p {
  color: #64748b;
  font-size: 1.125rem;
}

.settings-container {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
  gap: 2rem;
}

.settings-section {
  background: white;
  padding: 2rem;
  border-radius: 16px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.settings-section h3 {
  color: #1a365d;
  margin-bottom: 1.5rem;
  padding-bottom: 0.75rem;
  border-bottom: 2px solid #f1f5f9;
}

.form-group {
  margin-bottom: 1.5rem;
}

.form-group label {
  display: block;
  color: #374151;
  font-weight: 500;
  margin-bottom: 0.5rem;
}

.form-input,
.form-textarea,
.form-select {
  width: 100%;
  padding: 0.75rem 1rem;
  border: 2px solid #e2e8f0;
  border-radius: 8px;
  font-size: 0.875rem;
  transition: border-color 0.2s;
}

.form-input:focus,
.form-textarea:focus,
.form-select:focus {
  outline: none;
  border-color: #1a365d;
}

.form-input[readonly] {
  background: #f8fafc;
  color: #64748b;
}

.form-textarea {
  min-height: 100px;
  resize: vertical;
}

.form-select {
  cursor: pointer;
}

.checkbox-group {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.checkbox-label {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  cursor: pointer;
  color: #374151;
}

.checkbox-label input[type="checkbox"] {
  width: 18px;
  height: 18px;
  accent-color: #1a365d;
}

.btn {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1.5rem;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-size: 0.875rem;
  font-weight: 600;
  transition: all 0.2s;
}

.btn-primary {
  background: #1a365d;
  color: white;
}

.btn-primary:hover {
  background: #2c5282;
  transform: translateY(-1px);
}

.btn-outline {
  background: white;
  color: #1a365d;
  border: 2px solid #1a365d;
}

.btn-outline:hover {
  background: #1a365d;
  color: white;
}

.btn-danger {
  background: #ef4444;
  color: white;
}

.btn-danger:hover {
  background: #dc2626;
}

.action-buttons {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

@media (max-width: 768px) {
  .instructor-settings {
    padding: 1rem;
  }
  
  .settings-container {
    grid-template-columns: 1fr;
    gap: 1.5rem;
  }
  
  .settings-section {
    padding: 1.5rem;
  }
  
  .action-buttons {
    flex-direction: column;
  }
}
</style>
