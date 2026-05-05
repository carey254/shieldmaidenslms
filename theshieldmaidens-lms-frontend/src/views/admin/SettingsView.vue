<template>
  <div class="admin-settings">
    <!-- Header -->
    <div class="settings-header">
      <div class="header-content">
        <div class="header-left">
          <h1><i class="fas fa-cogs"></i> System Settings</h1>
          <p>Configure platform settings and preferences</p>
        </div>
        <div class="header-actions">
          <button @click="saveAllSettings" class="btn btn-primary">
            <i class="fas fa-save"></i> Save All Settings
          </button>
          <button @click="resetSettings" class="btn btn-secondary">
            <i class="fas fa-undo"></i> Reset to Defaults
          </button>
        </div>
      </div>
    </div>

    <!-- Settings Navigation -->
    <div class="settings-nav">
      <div class="nav-tabs">
        <button 
          v-for="tab in settingsTabs" 
          :key="tab.id"
          @click="activeTab = tab.id"
          :class="['nav-tab', { active: activeTab === tab.id }]"
        >
          <i :class="tab.icon"></i>
          {{ tab.label }}
        </button>
      </div>
    </div>

    <!-- Settings Content -->
    <div class="settings-content">
      <!-- General Settings -->
      <div v-if="activeTab === 'general'" class="settings-section">
        <div class="section-header">
          <h2><i class="fas fa-globe"></i> General Settings</h2>
          <p>Basic platform configuration</p>
        </div>

        <div class="settings-grid">
          <div class="setting-card">
            <div class="setting-header">
              <h3>Platform Information</h3>
              <p>Basic platform details</p>
            </div>
            <div class="setting-fields">
              <div class="form-group">
                <label>Platform Name</label>
                <input v-model="settings.general.platformName" type="text" class="form-control">
              </div>
              <div class="form-group">
                <label>Platform Description</label>
                <textarea v-model="settings.general.platformDescription" rows="3" class="form-control"></textarea>
              </div>
              <div class="form-group">
                <label>Admin Email</label>
                <input v-model="settings.general.adminEmail" type="email" class="form-control">
              </div>
            </div>
          </div>

          <div class="setting-card">
            <div class="setting-header">
              <h3>Time & Date Settings</h3>
              <p>Configure timezone and date formats</p>
            </div>
            <div class="setting-fields">
              <div class="form-group">
                <label>Default Timezone</label>
                <select v-model="settings.general.timezone" class="form-control">
                  <option value="UTC">UTC</option>
                  <option value="Africa/Nairobi">Africa/Nairobi</option>
                  <option value="America/New_York">America/New_York</option>
                  <option value="Europe/London">Europe/London</option>
                </select>
              </div>
              <div class="form-group">
                <label>Date Format</label>
                <select v-model="settings.general.dateFormat" class="form-control">
                  <option value="MM/DD/YYYY">MM/DD/YYYY</option>
                  <option value="DD/MM/YYYY">DD/MM/YYYY</option>
                  <option value="YYYY-MM-DD">YYYY-MM-DD</option>
                </select>
              </div>
              <div class="form-group">
                <label>Time Format</label>
                <select v-model="settings.general.timeFormat" class="form-control">
                  <option value="12h">12-hour</option>
                  <option value="24h">24-hour</option>
                </select>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Branding Settings -->
      <div v-if="activeTab === 'branding'" class="settings-section">
        <div class="section-header">
          <h2><i class="fas fa-palette"></i> Branding & Appearance</h2>
          <p>Customize platform look and feel</p>
        </div>

        <div class="settings-grid">
          <div class="setting-card">
            <div class="setting-header">
              <h3>Logo & Branding</h3>
              <p>Platform identity elements</p>
            </div>
            <div class="setting-fields">
              <div class="form-group">
                <label>Platform Logo</label>
                <div class="file-upload">
                  <input type="file" @change="handleLogoUpload" accept="image/*" class="file-input">
                  <div class="file-upload-label">
                    <i class="fas fa-upload"></i>
                    <span>Upload Logo</span>
                  </div>
                </div>
              </div>
              <div class="form-group">
                <label>Favicon</label>
                <div class="file-upload">
                  <input type="file" @change="handleFaviconUpload" accept="image/x-icon" class="file-input">
                  <div class="file-upload-label">
                    <i class="fas fa-image"></i>
                    <span>Upload Favicon</span>
                  </div>
                </div>
              </div>
              <div class="form-group">
                <label>Brand Colors</label>
                <div class="color-inputs">
                  <div class="color-input">
                    <label>Primary Color</label>
                    <input v-model="settings.branding.primaryColor" type="color" class="color-picker">
                  </div>
                  <div class="color-input">
                    <label>Secondary Color</label>
                    <input v-model="settings.branding.secondaryColor" type="color" class="color-picker">
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="setting-card">
            <div class="setting-header">
              <h3>Custom CSS</h3>
              <p>Advanced styling customization</p>
            </div>
            <div class="setting-fields">
              <div class="form-group">
                <label>Custom CSS</label>
                <textarea v-model="settings.branding.customCSS" rows="8" class="form-control" placeholder="Enter custom CSS rules..."></textarea>
              </div>
              <div class="form-group">
                <label>
                  <input v-model="settings.branding.enableCustomCSS" type="checkbox" class="checkbox">
                  Enable Custom CSS
                </label>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Security Settings -->
      <div v-if="activeTab === 'security'" class="settings-section">
        <div class="section-header">
          <h2><i class="fas fa-shield-alt"></i> Security Settings</h2>
          <p>Platform security and authentication</p>
        </div>

        <div class="settings-grid">
          <div class="setting-card">
            <div class="setting-header">
              <h3>Password Policy</h3>
              <p>Password requirements and security</p>
            </div>
            <div class="setting-fields">
              <div class="form-group">
                <label>Minimum Password Length</label>
                <input v-model.number="settings.security.minPasswordLength" type="number" min="6" max="20" class="form-control">
              </div>
              <div class="form-group">
                <label>
                  <input v-model="settings.security.requireUppercase" type="checkbox" class="checkbox">
                  Require Uppercase Letters
                </label>
              </div>
              <div class="form-group">
                <label>
                  <input v-model="settings.security.requireNumbers" type="checkbox" class="checkbox">
                  Require Numbers
                </label>
              </div>
              <div class="form-group">
                <label>
                  <input v-model="settings.security.requireSpecialChars" type="checkbox" class="checkbox">
                  Require Special Characters
                </label>
              </div>
            </div>
          </div>

          <div class="setting-card">
            <div class="setting-header">
              <h3>Session Management</h3>
              <p>User session configuration</p>
            </div>
            <div class="setting-fields">
              <div class="form-group">
                <label>Session Timeout (minutes)</label>
                <input v-model.number="settings.security.sessionTimeout" type="number" min="5" max="1440" class="form-control">
              </div>
              <div class="form-group">
                <label>
                  <input v-model="settings.security.rememberMeEnabled" type="checkbox" class="checkbox">
                  Enable "Remember Me" Function
                </label>
              </div>
              <div class="form-group">
                <label>
                  <input v-model="settings.security.twoFactorEnabled" type="checkbox" class="checkbox">
                  Enable Two-Factor Authentication
                </label>
              </div>
              <div class="form-group">
                <label>Max Login Attempts</label>
                <input v-model.number="settings.security.maxLoginAttempts" type="number" min="3" max="10" class="form-control">
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Email Settings -->
      <div v-if="activeTab === 'email'" class="settings-section">
        <div class="section-header">
          <h2><i class="fas fa-envelope"></i> Email Configuration</h2>
          <p>Email server and notification settings</p>
        </div>

        <div class="settings-grid">
          <div class="setting-card">
            <div class="setting-header">
              <h3>SMTP Configuration</h3>
              <p>Email server settings</p>
            </div>
            <div class="setting-fields">
              <div class="form-group">
                <label>SMTP Host</label>
                <input v-model="settings.email.smtpHost" type="text" class="form-control" placeholder="smtp.example.com">
              </div>
              <div class="form-group">
                <label>SMTP Port</label>
                <input v-model.number="settings.email.smtpPort" type="number" class="form-control" placeholder="587">
              </div>
              <div class="form-group">
                <label>Username</label>
                <input v-model="settings.email.smtpUsername" type="text" class="form-control">
              </div>
              <div class="form-group">
                <label>Password</label>
                <input v-model="settings.email.smtpPassword" type="password" class="form-control">
              </div>
              <div class="form-group">
                <label>Encryption</label>
                <select v-model="settings.email.encryption" class="form-control">
                  <option value="none">None</option>
                  <option value="ssl">SSL</option>
                  <option value="tls">TLS</option>
                </select>
              </div>
            </div>
          </div>

          <div class="setting-card">
            <div class="setting-header">
              <h3>Email Notifications</h3>
              <p>Automated email settings</p>
            </div>
            <div class="setting-fields">
              <div class="form-group">
                <label>From Email Address</label>
                <input v-model="settings.email.fromEmail" type="email" class="form-control">
              </div>
              <div class="form-group">
                <label>From Name</label>
                <input v-model="settings.email.fromName" type="text" class="form-control">
              </div>
              <div class="form-group">
                <label>
                  <input v-model="settings.email.enableNotifications" type="checkbox" class="checkbox">
                  Enable Email Notifications
                </label>
              </div>
              <div class="form-group">
                <label>
                  <input v-model="settings.email.enableWelcomeEmail" type="checkbox" class="checkbox">
                  Send Welcome Email to New Users
                </label>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- System Settings -->
      <div v-if="activeTab === 'system'" class="settings-section">
        <div class="section-header">
          <h2><i class="fas fa-server"></i> System Configuration</h2>
          <p>Platform system settings</p>
        </div>

        <div class="settings-grid">
          <div class="setting-card">
            <div class="setting-header">
              <h3>File Upload Settings</h3>
              <p>File handling configuration</p>
            </div>
            <div class="setting-fields">
              <div class="form-group">
                <label>Max File Size (MB)</label>
                <input v-model.number="settings.system.maxFileSize" type="number" min="1" max="100" class="form-control">
              </div>
              <div class="form-group">
                <label>Allowed File Types</label>
                <textarea v-model="settings.system.allowedFileTypes" rows="3" class="form-control" placeholder="pdf,doc,docx,jpg,png"></textarea>
              </div>
              <div class="form-group">
                <label>Upload Path</label>
                <input v-model="settings.system.uploadPath" type="text" class="form-control" placeholder="/uploads">
              </div>
            </div>
          </div>

          <div class="setting-card">
            <div class="setting-header">
              <h3>Backup Settings</h3>
              <p>Data backup configuration</p>
            </div>
            <div class="setting-fields">
              <div class="form-group">
                <label>
                  <input v-model="settings.system.autoBackupEnabled" type="checkbox" class="checkbox">
                  Enable Automatic Backups
                </label>
              </div>
              <div class="form-group">
                <label>Backup Frequency</label>
                <select v-model="settings.system.backupFrequency" class="form-control">
                  <option value="daily">Daily</option>
                  <option value="weekly">Weekly</option>
                  <option value="monthly">Monthly</option>
                </select>
              </div>
              <div class="form-group">
                <label>Backup Retention (days)</label>
                <input v-model.number="settings.system.backupRetention" type="number" min="1" max="365" class="form-control">
              </div>
              <div class="form-group">
                <label>Backup Location</label>
                <input v-model="settings.system.backupLocation" type="text" class="form-control" placeholder="/backups">
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Success Message -->
    <div v-if="showSuccessMessage" class="success-message">
      <i class="fas fa-check-circle"></i>
      Settings saved successfully!
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'

const activeTab = ref('general')
const showSuccessMessage = ref(false)

const settingsTabs = [
  { id: 'general', label: 'General', icon: 'fas fa-globe' },
  { id: 'branding', label: 'Branding', icon: 'fas fa-palette' },
  { id: 'security', label: 'Security', icon: 'fas fa-shield-alt' },
  { id: 'email', label: 'Email', icon: 'fas fa-envelope' },
  { id: 'system', label: 'System', icon: 'fas fa-server' }
]

const settings = ref({
  general: {
    platformName: 'The Shield Maidens LMS',
    platformDescription: 'Learning Management System for Digital Safety Education',
    adminEmail: 'admin@theshieldmaidens.org',
    timezone: 'Africa/Nairobi',
    dateFormat: 'DD/MM/YYYY',
    timeFormat: '24h'
  },
  branding: {
    primaryColor: '#ff9900',
    secondaryColor: '#333333',
    customCSS: '',
    enableCustomCSS: false
  },
  security: {
    minPasswordLength: 8,
    requireUppercase: true,
    requireNumbers: true,
    requireSpecialChars: true,
    sessionTimeout: 120,
    rememberMeEnabled: true,
    twoFactorEnabled: false,
    maxLoginAttempts: 5
  },
  email: {
    smtpHost: '',
    smtpPort: 587,
    smtpUsername: '',
    smtpPassword: '',
    encryption: 'tls',
    fromEmail: 'noreply@theshieldmaidens.org',
    fromName: 'The Shield Maidens',
    enableNotifications: true,
    enableWelcomeEmail: true
  },
  system: {
    maxFileSize: 10,
    allowedFileTypes: 'pdf,doc,docx,jpg,png,gif,mp4,mp3',
    uploadPath: '/uploads',
    autoBackupEnabled: true,
    backupFrequency: 'daily',
    backupRetention: 30,
    backupLocation: '/backups'
  }
})

const saveAllSettings = () => {
  // Save settings to backend
  console.log('Saving settings:', settings.value)
  showSuccessMessage.value = true
  setTimeout(() => {
    showSuccessMessage.value = false
  }, 3000)
}

const resetSettings = () => {
  if (confirm('Are you sure you want to reset all settings to defaults?')) {
    // Reset to default values
    console.log('Resetting settings to defaults')
    // Reload default settings
  }
}

const handleLogoUpload = (event) => {
  const file = event.target.files[0]
  if (file) {
    console.log('Logo uploaded:', file)
    // Handle logo upload
  }
}

const handleFaviconUpload = (event) => {
  const file = event.target.files[0]
  if (file) {
    console.log('Favicon uploaded:', file)
    // Handle favicon upload
  }
}

onMounted(() => {
  console.log('Admin Settings page loaded')
})
</script>

<style scoped>
.admin-settings {
  min-height: 100vh;
  background: #f7f7f7;
  color: #333;
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

/* Header */
.settings-header {
  background: #ff9900;
  color: #fff;
  padding: 2rem;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.header-content {
  display: flex;
  justify-content: space-between;
  align-items: center;
  max-width: 1400px;
  margin: 0 auto;
}

.header-left h1 {
  color: #fff;
  font-size: 2rem;
  font-weight: 700;
  margin-bottom: 0.5rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.header-left p {
  color: rgba(255, 255, 255, 0.8);
  font-size: 1rem;
}

.header-actions {
  display: flex;
  gap: 1rem;
}

/* Navigation */
.settings-nav {
  background: #fff;
  border-bottom: 2px solid #ff9900;
  padding: 0 2rem;
}

.nav-tabs {
  display: flex;
  gap: 0;
  max-width: 1400px;
  margin: 0 auto;
}

.nav-tab {
  background: none;
  border: none;
  padding: 1rem 1.5rem;
  cursor: pointer;
  font-size: 0.875rem;
  font-weight: 600;
  color: #666;
  border-bottom: 3px solid transparent;
  transition: all 0.2s;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.nav-tab:hover {
  color: #ff9900;
  background: rgba(255, 153, 0, 0.05);
}

.nav-tab.active {
  color: #ff9900;
  border-bottom-color: #ff9900;
  background: rgba(255, 153, 0, 0.05);
}

/* Content */
.settings-content {
  padding: 2rem;
  max-width: 1400px;
  margin: 0 auto;
}

.settings-section {
  animation: fadeIn 0.6s ease;
}

.section-header {
  margin-bottom: 2rem;
}

.section-header h2 {
  color: #333;
  font-size: 1.5rem;
  font-weight: 700;
  margin-bottom: 0.5rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.section-header h2 i {
  color: #ff9900;
}

.section-header p {
  color: #666;
  font-size: 1rem;
}

.settings-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
  gap: 1.5rem;
}

.setting-card {
  background: #fff;
  border-radius: 8px;
  padding: 1.5rem;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  border-top: 4px solid #ff9900;
}

.setting-header {
  margin-bottom: 1.5rem;
  padding-bottom: 1rem;
  border-bottom: 1px solid #f0f0f0;
}

.setting-header h3 {
  color: #333;
  font-size: 1.125rem;
  font-weight: 600;
  margin-bottom: 0.25rem;
}

.setting-header p {
  color: #666;
  font-size: 0.875rem;
}

.setting-fields {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.form-group label {
  color: #333;
  font-weight: 600;
  font-size: 0.875rem;
}

.form-control {
  padding: 0.75rem;
  border: 1px solid #ddd;
  border-radius: 6px;
  font-size: 0.875rem;
  color: #333;
  background: #fff;
  transition: all 0.2s;
}

.form-control:focus {
  outline: none;
  border-color: #ff9900;
  box-shadow: 0 0 0 3px rgba(255, 153, 0, 0.1);
}

.checkbox {
  width: auto;
  margin-right: 0.5rem;
}

/* File Upload */
.file-upload {
  position: relative;
}

.file-input {
  display: none;
}

.file-upload-label {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1rem;
  border: 2px dashed #ddd;
  border-radius: 6px;
  cursor: pointer;
  transition: all 0.2s;
  color: #666;
}

.file-upload-label:hover {
  border-color: #ff9900;
  color: #ff9900;
}

/* Color Inputs */
.color-inputs {
  display: flex;
  gap: 1rem;
}

.color-input {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.color-picker {
  width: 60px;
  height: 40px;
  border: 1px solid #ddd;
  border-radius: 4px;
  cursor: pointer;
}

/* Buttons */
.btn {
  padding: 0.75rem 1.5rem;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-size: 0.875rem;
  font-weight: 600;
  transition: all 0.2s;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.btn-primary {
  background: #ff9900;
  color: white;
}

.btn-primary:hover {
  background: #e68a00;
  transform: translateY(-2px);
}

.btn-secondary {
  background: #333;
  color: white;
}

.btn-secondary:hover {
  background: #555;
}

/* Success Message */
.success-message {
  position: fixed;
  top: 20px;
  right: 20px;
  background: #008000;
  color: white;
  padding: 1rem 1.5rem;
  border-radius: 6px;
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
  display: flex;
  align-items: center;
  gap: 0.5rem;
  z-index: 9999;
  animation: slideIn 0.3s ease;
}

/* Responsive Design */
@media (max-width: 768px) {
  .header-content {
    flex-direction: column;
    gap: 1rem;
    text-align: center;
  }
  
  .nav-tabs {
    flex-wrap: wrap;
  }
  
  .settings-grid {
    grid-template-columns: 1fr;
  }
  
  .color-inputs {
    flex-direction: column;
  }
}

/* Animations */
@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes slideIn {
  from {
    transform: translateX(100%);
    opacity: 0;
  }
  to {
    transform: translateX(0);
    opacity: 1;
  }
}
</style>
