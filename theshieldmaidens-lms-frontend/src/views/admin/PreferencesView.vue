<template>
  <div class="preferences-view">
    <div class="page-header">
      <h1>Preferences</h1>
      <p>Customize your admin experience</p>
    </div>

    <div class="preferences-content">
      <div class="preferences-card">
        <h2>Appearance</h2>
        <div class="preference-group">
          <div class="preference-item">
            <label class="switch-label">
              <span>Dark Mode</span>
              <div class="toggle-switch">
                <input type="checkbox" v-model="preferences.darkMode" @change="updatePreferences" />
                <span class="slider"></span>
              </div>
            </label>
            <p class="preference-description">Enable dark theme for the admin interface</p>
          </div>

          <div class="preference-item">
            <label for="language">Language</label>
            <select id="language" v-model="preferences.language" @change="updatePreferences" class="form-control">
              <option value="en">English</option>
              <option value="sw">Swahili</option>
              <option value="fr">French</option>
            </select>
            <p class="preference-description">Choose your preferred language</p>
          </div>

          <div class="preference-item">
            <label for="timezone">Timezone</label>
            <select id="timezone" v-model="preferences.timezone" @change="updatePreferences" class="form-control">
              <option value="UTC">UTC</option>
              <option value="EAT">East Africa Time (EAT)</option>
              <option value="EST">Eastern Standard Time (EST)</option>
              <option value="PST">Pacific Standard Time (PST)</option>
            </select>
            <p class="preference-description">Set your local timezone</p>
          </div>
        </div>
      </div>

      <div class="preferences-card">
        <h2>Notifications</h2>
        <div class="preference-group">
          <div class="preference-item">
            <label class="switch-label">
              <span>Email Notifications</span>
              <div class="toggle-switch">
                <input type="checkbox" v-model="preferences.emailNotifications" @change="updatePreferences" />
                <span class="slider"></span>
              </div>
            </label>
            <p class="preference-description">Receive email updates about system activities</p>
          </div>

          <div class="preference-item">
            <label class="switch-label">
              <span>Browser Notifications</span>
              <div class="toggle-switch">
                <input type="checkbox" v-model="preferences.browserNotifications" @change="updatePreferences" />
                <span class="slider"></span>
              </div>
            </label>
            <p class="preference-description">Show desktop notifications for important events</p>
          </div>

          <div class="preference-item">
            <label class="switch-label">
              <span>Weekly Reports</span>
              <div class="toggle-switch">
                <input type="checkbox" v-model="preferences.weeklyReports" @change="updatePreferences" />
                <span class="slider"></span>
              </div>
            </label>
            <p class="preference-description">Receive weekly summary reports via email</p>
          </div>
        </div>
      </div>

      <div class="preferences-card">
        <h2>Dashboard</h2>
        <div class="preference-group">
          <div class="preference-item">
            <label for="defaultPage">Default Dashboard Page</label>
            <select id="defaultPage" v-model="preferences.defaultPage" @change="updatePreferences" class="form-control">
              <option value="overview">Overview</option>
              <option value="users">Users</option>
              <option value="courses">Courses</option>
              <option value="analytics">Analytics</option>
            </select>
            <p class="preference-description">Choose which page loads when you access the dashboard</p>
          </div>

          <div class="preference-item">
            <label class="switch-label">
              <span>Show Quick Stats</span>
              <div class="toggle-switch">
                <input type="checkbox" v-model="preferences.showQuickStats" @change="updatePreferences" />
                <span class="slider"></span>
              </div>
            </label>
            <p class="preference-description">Display quick statistics on the dashboard</p>
          </div>

          <div class="preference-item">
            <label for="refreshInterval">Auto Refresh Interval</label>
            <select id="refreshInterval" v-model="preferences.refreshInterval" @change="updatePreferences" class="form-control">
              <option value="0">Disabled</option>
              <option value="30">30 seconds</option>
              <option value="60">1 minute</option>
              <option value="300">5 minutes</option>
            </select>
            <p class="preference-description">Automatically refresh dashboard data</p>
          </div>
        </div>
      </div>

      <div class="preferences-card">
        <h2>Privacy</h2>
        <div class="preference-group">
          <div class="preference-item">
            <label class="switch-label">
              <span>Activity Tracking</span>
              <div class="toggle-switch">
                <input type="checkbox" v-model="preferences.activityTracking" @change="updatePreferences" />
                <span class="slider"></span>
              </div>
            </label>
            <p class="preference-description">Allow system to track your admin activities for analytics</p>
          </div>

          <div class="preference-item">
            <label class="switch-label">
              <span>Show Online Status</span>
              <div class="toggle-switch">
                <input type="checkbox" v-model="preferences.showOnlineStatus" @change="updatePreferences" />
                <span class="slider"></span>
              </div>
            </label>
            <p class="preference-description">Let other admins see when you're online</p>
          </div>
        </div>
      </div>

      <div class="preferences-actions">
        <button @click="resetToDefaults" class="btn btn-secondary">
          <i class="fas fa-undo"></i> Reset to Defaults
        </button>
        <button @click="saveAllPreferences" class="btn btn-primary" :disabled="loading">
          <i v-if="loading" class="fas fa-spinner fa-spin"></i>
          {{ loading ? 'Saving...' : 'Save All Preferences' }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'

const loading = ref(false)

const preferences = ref({
  darkMode: false,
  language: 'en',
  timezone: 'EAT',
  emailNotifications: true,
  browserNotifications: false,
  weeklyReports: true,
  defaultPage: 'overview',
  showQuickStats: true,
  refreshInterval: '60',
  activityTracking: true,
  showOnlineStatus: true
})

const updatePreferences = async () => {
  try {
    await authStore.updatePreferences(preferences.value)
    console.log('Preferences updated')
  } catch (error) {
    console.error('Error updating preferences:', error)
  }
}

const saveAllPreferences = async () => {
  loading.value = true
  try {
    await authStore.updatePreferences(preferences.value)
    
    alert('All preferences saved successfully!')
  } catch (error) {
    console.error('Error saving preferences:', error)
    alert('Error saving preferences. Please try again.')
  } finally {
    loading.value = false
  }
}

const resetToDefaults = () => {
  if (confirm('Are you sure you want to reset all preferences to default values?')) {
    preferences.value = {
      darkMode: false,
      language: 'en',
      timezone: 'EAT',
      emailNotifications: true,
      browserNotifications: false,
      weeklyReports: true,
      defaultPage: 'overview',
      showQuickStats: true,
      refreshInterval: '60',
      activityTracking: true,
      showOnlineStatus: true
    }
    
    updatePreferences()
    alert('Preferences reset to defaults')
  }
}

onMounted(async () => {
  try {
    // Load preferences from API
    const savedPreferences = await authStore.getPreferences()
    preferences.value = { ...preferences.value, ...savedPreferences }
  } catch (error) {
    console.error('Error loading preferences:', error)
    // Fallback to localStorage
    const saved = localStorage.getItem('adminPreferences')
    if (saved) {
      try {
        preferences.value = { ...preferences.value, ...JSON.parse(saved) }
      } catch (localStorageError) {
        console.error('Error loading preferences from localStorage:', localStorageError)
      }
    }
  }
})
</script>

<style scoped>
.preferences-view {
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

.preferences-content {
  display: flex;
  flex-direction: column;
  gap: 30px;
}

.preferences-card {
  background: white;
  padding: 30px;
  border-radius: 12px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

.preferences-card h2 {
  color: #333;
  font-size: 1.3rem;
  margin-bottom: 20px;
  padding-bottom: 10px;
  border-bottom: 1px solid #e9ecef;
}

.preference-group {
  display: flex;
  flex-direction: column;
  gap: 25px;
}

.preference-item {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.preference-item label {
  color: #333;
  font-weight: 500;
  font-size: 1rem;
}

.preference-description {
  color: #666;
  font-size: 0.8rem;
  margin: 0;
}

.form-control {
  padding: 10px 12px;
  border: 1px solid #ddd;
  border-radius: 6px;
  font-size: 1rem;
  transition: border-color 0.3s ease;
  max-width: 300px;
}

.form-control:focus {
  outline: none;
  border-color: #007bff;
  box-shadow: 0 0 0 2px rgba(0, 123, 255, 0.25);
}

.switch-label {
  display: flex;
  justify-content: space-between;
  align-items: center;
  cursor: pointer;
  margin-bottom: 5px;
}

.toggle-switch {
  position: relative;
  width: 50px;
  height: 24px;
}

.toggle-switch input {
  opacity: 0;
  width: 0;
  height: 0;
}

.slider {
  position: absolute;
  cursor: pointer;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: #ccc;
  transition: 0.4s;
  border-radius: 24px;
}

.slider:before {
  position: absolute;
  content: "";
  height: 18px;
  width: 18px;
  left: 3px;
  bottom: 3px;
  background-color: white;
  transition: 0.4s;
  border-radius: 50%;
}

input:checked + .slider {
  background-color: #007bff;
}

input:checked + .slider:before {
  transform: translateX(26px);
}

.preferences-actions {
  display: flex;
  justify-content: flex-end;
  gap: 15px;
  padding: 20px;
  background: white;
  border-radius: 12px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
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

.btn-secondary {
  background: #6c757d;
  color: white;
}

.btn-secondary:hover {
  background: #545b62;
}

@media (max-width: 768px) {
  .preferences-card {
    padding: 20px;
  }
  
  .form-control {
    max-width: 100%;
  }
  
  .preferences-actions {
    flex-direction: column;
  }
  
  .btn {
    width: 100%;
    justify-content: center;
  }
}
</style>
