<template>
  <div class="settings-view">
    <div class="settings-container">
      <!-- Profile Settings -->
      <div class="settings-section">
        <h2>Profile Information</h2>
        <div class="settings-form">
          <div class="form-group">
            <label for="name">Full Name</label>
            <input type="text" id="name" v-model="profile.name" />
          </div>
          <div class="form-group">
            <label for="email">Email Address</label>
            <input type="email" id="email" v-model="profile.email" disabled />
          </div>
          <div class="form-group">
            <label for="phone">Phone Number</label>
            <input type="tel" id="phone" v-model="profile.phone" />
          </div>
          <button class="save-btn" @click="saveProfile">Save Profile</button>
        </div>
      </div>

      <!-- Password Settings -->
      <div class="settings-section">
        <h2>Security</h2>
        <div class="settings-form">
          <div class="form-group">
            <label for="current-password">Current Password</label>
            <input type="password" id="current-password" v-model="password.current" />
          </div>
          <div class="form-group">
            <label for="new-password">New Password</label>
            <input type="password" id="new-password" v-model="password.new" />
          </div>
          <div class="form-group">
            <label for="confirm-password">Confirm New Password</label>
            <input type="password" id="confirm-password" v-model="password.confirm" />
          </div>
          <button class="save-btn" @click="changePassword">Change Password</button>
        </div>
      </div>

      <!-- Notification Settings -->
      <div class="settings-section">
        <h2>Notifications</h2>
        <div class="settings-form">
          <div class="checkbox-group">
            <label class="checkbox-label">
              <input type="checkbox" v-model="notifications.email" />
              <span>Email Notifications</span>
            </label>
          </div>
          <div class="checkbox-group">
            <label class="checkbox-label">
              <input type="checkbox" v-model="notifications.push" />
              <span>Push Notifications</span>
            </label>
          </div>
          <div class="checkbox-group">
            <label class="checkbox-label">
              <input type="checkbox" v-model="notifications.assignments" />
              <span>Assignment Reminders</span>
            </label>
          </div>
          <div class="checkbox-group">
            <label class="checkbox-label">
              <input type="checkbox" v-model="notifications.updates" />
              <span>Course Updates</span>
            </label>
          </div>
          <button class="save-btn" @click="saveNotifications">Save Preferences</button>
        </div>
      </div>

      <!-- Privacy Settings -->
      <div class="settings-section">
        <h2>Privacy</h2>
        <div class="settings-form">
          <div class="checkbox-group">
            <label class="checkbox-label">
              <input type="checkbox" v-model="privacy.showProfile" />
              <span>Show Profile to Other Students</span>
            </label>
          </div>
          <div class="checkbox-group">
            <label class="checkbox-label">
              <input type="checkbox" v-model="privacy.showProgress" />
              <span>Show Progress in Leaderboard</span>
            </label>
          </div>
          <button class="save-btn" @click="savePrivacy">Save Privacy Settings</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'

const profile = ref({
  name: 'Caren',
  email: 'caren@theshieldmaidens.org',
  phone: '+254 700 000000'
})

const password = ref({
  current: '',
  new: '',
  confirm: ''
})

const notifications = ref({
  email: true,
  push: true,
  assignments: true,
  updates: false
})

const privacy = ref({
  showProfile: true,
  showProgress: true
})

const saveProfile = () => {
  console.log('Saving profile:', profile.value)
  // TODO: Save to API
}

const changePassword = () => {
  if (password.value.new !== password.value.confirm) {
    alert('New passwords do not match')
    return
  }
  console.log('Changing password')
  // TODO: Change password via API
  password.value = { current: '', new: '', confirm: '' }
}

const saveNotifications = () => {
  console.log('Saving notifications:', notifications.value)
  // TODO: Save to API
}

const savePrivacy = () => {
  console.log('Saving privacy:', privacy.value)
  // TODO: Save to API
}
</script>

<style scoped>
.settings-view {
  padding: 20px;
  max-width: 800px;
  margin: 0 auto;
}

.settings-header {
  text-align: center;
  margin-bottom: 40px;
}

.settings-header h1 {
  font-size: 32px;
  color: #333;
  margin: 0 0 10px 0;
}

.settings-header p {
  color: #666;
  margin: 0;
  font-size: 16px;
}

.settings-container {
  display: flex;
  flex-direction: column;
  gap: 30px;
}

.settings-section {
  background: white;
  padding: 30px;
  border-radius: 12px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.settings-section h2 {
  font-size: 20px;
  color: #333;
  margin: 0 0 20px 0;
  border-bottom: 2px solid #00897b;
  padding-bottom: 10px;
}

.settings-form {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.form-group label {
  font-weight: 500;
  color: #333;
  font-size: 14px;
}

.form-group input {
  padding: 12px;
  border: 1px solid #ddd;
  border-radius: 6px;
  font-size: 14px;
}

.form-group input:focus {
  outline: none;
  border-color: #00897b;
}

.form-group input:disabled {
  background: #f5f5f5;
  color: #999;
}

.checkbox-group {
  display: flex;
  align-items: center;
  gap: 10px;
}

.checkbox-label {
  display: flex;
  align-items: center;
  gap: 10px;
  cursor: pointer;
  font-size: 14px;
  color: #333;
}

.checkbox-label input[type="checkbox"] {
  width: 18px;
  height: 18px;
  cursor: pointer;
}

.save-btn {
  background: #00897b;
  color: white;
  border: none;
  padding: 12px 24px;
  border-radius: 6px;
  font-size: 14px;
  font-weight: 500;
  cursor: pointer;
  transition: background-color 0.2s;
  align-self: flex-start;
}

.save-btn:hover {
  background: #00796b;
}
</style>
