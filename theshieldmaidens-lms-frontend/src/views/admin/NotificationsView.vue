<template>
  <div class="notification-center">
    <div class="page-header">
      <h1>Notification Center</h1>
      <p>Send announcements, alerts, and system messages</p>
    </div>

    <!-- Notification Stats -->
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-icon">
          <i class="fas fa-paper-plane"></i>
        </div>
        <div class="stat-content">
          <div class="stat-number">{{ stats.sentToday }}</div>
          <div class="stat-label">Sent Today</div>
        </div>
      </div>
      
      <div class="stat-card">
        <div class="stat-icon">
          <i class="fas fa-users"></i>
        </div>
        <div class="stat-content">
          <div class="stat-number">{{ stats.totalRecipients }}</div>
          <div class="stat-label">Total Recipients</div>
        </div>
      </div>
      
      <div class="stat-card">
        <div class="stat-icon">
          <i class="fas fa-chart-line"></i>
        </div>
        <div class="stat-content">
          <div class="stat-number">{{ stats.openRate }}%</div>
          <div class="stat-label">Open Rate</div>
        </div>
      </div>
      
      <div class="stat-card">
        <div class="stat-icon">
          <i class="fas fa-clock"></i>
        </div>
        <div class="stat-content">
          <div class="stat-number">{{ stats.scheduled }}</div>
          <div class="stat-label">Scheduled</div>
        </div>
      </div>
    </div>

    <!-- Compose Notification -->
    <div class="compose-section">
      <div class="section-header">
        <h3>Compose New Notification</h3>
        <div class="compose-actions">
          <button @click="saveDraft" class="btn btn-outline">
            <i class="fas fa-save"></i>
            Save Draft
          </button>
          <button @click="sendNotification" class="btn btn-primary">
            <i class="fas fa-paper-plane"></i>
            Send Now
          </button>
        </div>
      </div>
      
      <div class="compose-form">
        <div class="form-row">
          <div class="form-group">
            <label>Notification Type</label>
            <select v-model="notification.type" class="form-select">
              <option value="announcement">System Announcement</option>
              <option value="alert">Urgent Alert</option>
              <option value="reminder">Reminder</option>
              <option value="welcome">Welcome Message</option>
              <option value="maintenance">Maintenance Notice</option>
            </select>
          </div>
          
          <div class="form-group">
            <label>Priority</label>
            <select v-model="notification.priority" class="form-select">
              <option value="low">Low</option>
              <option value="medium">Medium</option>
              <option value="high">High</option>
              <option value="urgent">Urgent</option>
            </select>
          </div>
        </div>
        
        <div class="form-group">
          <label>Recipients</label>
          <div class="recipient-options">
            <label class="radio-label">
              <input v-model="notification.recipientType" type="radio" value="all">
              <span>All Users</span>
            </label>
            <label class="radio-label">
              <input v-model="notification.recipientType" type="radio" value="role">
              <span>By Role</span>
            </label>
            <label class="radio-label">
              <input v-model="notification.recipientType" type="radio" value="course">
              <span>By Course</span>
            </label>
            <label class="radio-label">
              <input v-model="notification.recipientType" type="radio" value="individual">
              <span>Individual Users</span>
            </label>
          </div>
        </div>
        
        <div v-if="notification.recipientType === 'role'" class="form-group">
          <label>Select Roles</label>
          <div class="checkbox-group">
            <label class="checkbox-label">
              <input v-model="notification.roles" type="checkbox" value="admin">
              <span>Administrators</span>
            </label>
            <label class="checkbox-label">
              <input v-model="notification.roles" type="checkbox" value="instructor">
              <span>Instructors</span>
            </label>
            <label class="checkbox-label">
              <input v-model="notification.roles" type="checkbox" value="student">
              <span>Students</span>
            </label>
            <label class="checkbox-label">
              <input v-model="notification.roles" type="checkbox" value="parent">
              <span>Parents</span>
            </label>
          </div>
        </div>
        
        <div v-if="notification.recipientType === 'course'" class="form-group">
          <label>Select Courses</label>
          <select v-model="notification.courses" multiple class="form-select">
            <option v-for="course in courses" :key="course.id" :value="course.id">
              {{ course.name }}
            </option>
          </select>
        </div>
        
        <div class="form-group">
          <label>Subject</label>
          <input v-model="notification.subject" type="text" class="form-input" placeholder="Enter notification subject">
        </div>
        
        <div class="form-group">
          <label>Message</label>
          <textarea v-model="notification.message" class="form-textarea" rows="6" placeholder="Compose your message here..."></textarea>
        </div>
        
        <div class="form-row">
          <div class="form-group">
            <label>Schedule</label>
            <select v-model="notification.schedule" class="form-select">
              <option value="now">Send Immediately</option>
              <option value="scheduled">Schedule for Later</option>
            </select>
          </div>
          
          <div v-if="notification.schedule === 'scheduled'" class="form-group">
            <label>Schedule Date & Time</label>
            <input v-model="notification.scheduledDate" type="datetime-local" class="form-input">
          </div>
        </div>
        
        <div class="form-group">
          <label class="checkbox-label">
            <input v-model="notification.sendEmail" type="checkbox">
            <span>Also send via email</span>
          </label>
        </div>
      </div>
    </div>

    <!-- Recent Notifications -->
    <div class="recent-notifications">
      <div class="section-header">
        <h3>Recent Notifications</h3>
        <div class="filter-controls">
          <input v-model="searchTerm" type="text" placeholder="Search notifications..." class="search-input">
          <select v-model="filterType" class="filter-select">
            <option value="all">All Types</option>
            <option value="announcement">Announcements</option>
            <option value="alert">Alerts</option>
            <option value="reminder">Reminders</option>
          </select>
        </div>
      </div>
      
      <div class="notifications-list">
        <div v-for="notif in filteredNotifications" :key="notif.id" class="notification-item">
          <div class="notification-header">
            <div class="notification-info">
              <h4>{{ notif.subject }}</h4>
              <div class="notification-meta">
                <span :class="['type-badge', notif.type]">{{ notif.type }}</span>
                <span :class="['priority-badge', notif.priority]">{{ notif.priority }}</span>
                <span class="date">{{ formatDate(notif.createdAt) }}</span>
              </div>
            </div>
            <div class="notification-status">
              <span :class="['status-badge', notif.status]">{{ notif.status }}</span>
            </div>
          </div>
          
          <div class="notification-content">
            <p>{{ notif.message }}</p>
          </div>
          
          <div class="notification-details">
            <div class="detail-item">
              <i class="fas fa-users"></i>
              <span>Recipients: {{ notif.recipients }}</span>
            </div>
            <div class="detail-item">
              <i class="fas fa-envelope-open"></i>
              <span>Opened: {{ notif.opened }} / {{ notif.totalSent }}</span>
            </div>
            <div class="detail-item">
              <i class="fas fa-clock"></i>
              <span>Sent: {{ formatTime(notif.sentAt) }}</span>
            </div>
          </div>
          
          <div class="notification-actions">
            <button @click="viewDetails(notif)" class="btn btn-outline">
              <i class="fas fa-eye"></i>
              View Details
            </button>
            <button @click="duplicateNotification(notif)" class="btn btn-outline">
              <i class="fas fa-copy"></i>
              Duplicate
            </button>
            <button v-if="notif.status === 'draft'" @click="sendNotification(notif)" class="btn btn-success">
              <i class="fas fa-paper-plane"></i>
              Send
            </button>
            <button @click="deleteNotification(notif)" class="btn btn-danger">
              <i class="fas fa-trash"></i>
              Delete
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Notification Templates -->
    <div class="templates-section">
      <h3>Notification Templates</h3>
      <div class="templates-grid">
        <div v-for="template in templates" :key="template.id" class="template-card" @click="useTemplate(template)">
          <div class="template-header">
            <h4>{{ template.name }}</h4>
            <span :class="['type-badge', template.type]">{{ template.type }}</span>
          </div>
          <p>{{ template.description }}</p>
          <div class="template-preview">
            <small>{{ template.subject }}</small>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'

const searchTerm = ref('')
const filterType = ref('all')

const stats = ref({
  sentToday: 0,
  totalRecipients: 0,
  openRate: 0,
  scheduled: 0
})

const notification = ref({
  type: 'announcement',
  priority: 'medium',
  recipientType: 'all',
  roles: [],
  courses: [],
  subject: '',
  message: '',
  schedule: 'now',
  scheduledDate: '',
  sendEmail: false
})

const courses = ref([])
const notifications = ref([])
const templates = ref([])
const loading = ref(true)

const filteredNotifications = computed(() => {
  return notifications.value.filter(notif => {
    const matchesSearch = notif.subject.toLowerCase().includes(searchTerm.value.toLowerCase()) ||
                         notif.message.toLowerCase().includes(searchTerm.value.toLowerCase())
    const matchesType = filterType.value === 'all' || notif.type === filterType.value
    return matchesSearch && matchesType
  })
})

// Real-time data fetching
const fetchNotificationStats = async () => {
  try {
    const response = await fetch('http://127.0.0.1:8000/api/admin/notifications/stats', {
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('token')}`,
        'Accept': 'application/json'
      }
    })
    if (response.ok) {
      const data = await response.json()
      stats.value = data
    }
  } catch (error) {
    console.error('Error fetching notification stats:', error)
  }
}

const fetchNotifications = async () => {
  try {
    const response = await fetch('http://127.0.0.1:8000/api/admin/notifications', {
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('token')}`,
        'Accept': 'application/json'
      }
    })
    if (response.ok) {
      const data = await response.json()
      notifications.value = data.notifications || []
    }
  } catch (error) {
    console.error('Error fetching notifications:', error)
  }
}

const fetchCourses = async () => {
  try {
    const response = await fetch('http://127.0.0.1:8000/api/admin/courses', {
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('token')}`,
        'Accept': 'application/json'
      }
    })
    if (response.ok) {
      const data = await response.json()
      courses.value = data.data || []
    }
  } catch (error) {
    console.error('Error fetching courses:', error)
  }
}

const fetchTemplates = async () => {
  try {
    const response = await fetch('http://127.0.0.1:8000/api/admin/notification-templates', {
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('token')}`,
        'Accept': 'application/json'
      }
    })
    if (response.ok) {
      const data = await response.json()
      templates.value = data.templates || []
    }
  } catch (error) {
    console.error('Error fetching templates:', error)
  }
}

// Load all data
const loadNotificationData = async () => {
  loading.value = true
  try {
    await Promise.all([
      fetchNotificationStats(),
      fetchNotifications(),
      fetchCourses(),
      fetchTemplates()
    ])
  } catch (error) {
    console.error('Error loading notification data:', error)
  } finally {
    loading.value = false
  }
}

const saveDraft = async () => {
  try {
    const response = await fetch('http://127.0.0.1:8000/api/admin/notifications', {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('token')}`,
        'Accept': 'application/json',
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({
        ...notification.value,
        status: 'draft'
      })
    })
    
    if (response.ok) {
      await fetchNotifications()
      // Reset form
      notification.value = {
        type: 'announcement',
        priority: 'medium',
        recipientType: 'all',
        roles: [],
        courses: [],
        subject: '',
        message: '',
        schedule: 'now',
        scheduledDate: '',
        sendEmail: false
      }
    }
  } catch (error) {
    console.error('Error saving draft:', error)
  }
}

const sendNotification = async (notif = null) => {
  try {
    const notificationToSend = notif || notification.value
    const url = notif 
      ? `http://127.0.0.1:8000/api/admin/notifications/${notif.id}/send`
      : 'http://127.0.0.1:8000/api/admin/notifications/send'
    
    const response = await fetch(url, {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('token')}`,
        'Accept': 'application/json',
        'Content-Type': 'application/json'
      },
      body: JSON.stringify(notificationToSend)
    })
    
    if (response.ok) {
      await fetchNotifications()
      await fetchNotificationStats()
    }
  } catch (error) {
    console.error('Error sending notification:', error)
  }
}

const viewDetails = (notif) => {
  // TODO: Open notification details modal
  console.log('View details:', notif)
}

const duplicateNotification = (notif) => {
  notification.value = {
    ...notif,
    id: null,
    status: 'draft',
    subject: `Copy of ${notif.subject}`
  }
}

const deleteNotification = async (notif) => {
  if (!confirm(`Are you sure you want to delete "${notif.subject}"?`)) return
  
  try {
    const response = await fetch(`http://127.0.0.1:8000/api/admin/notifications/${notif.id}`, {
      method: 'DELETE',
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('token')}`,
        'Accept': 'application/json'
      }
    })
    
    if (response.ok) {
      await fetchNotifications()
      await fetchNotificationStats()
    }
  } catch (error) {
    console.error('Error deleting notification:', error)
  }
}

const useTemplate = (template) => {
  notification.value.subject = template.subject
  notification.value.type = template.type
}

const formatDate = (timestamp) => {
  return new Date(timestamp).toLocaleDateString()
}

const formatTime = (timestamp) => {
  if (!timestamp) return 'Not sent'
  return new Date(timestamp).toLocaleString()
}

onMounted(() => {
  loadNotificationData()
})
</script>

<style scoped>
.notification-center {
  padding: 2rem;
  max-width: 1400px;
  margin: 0 auto;
}

.page-header {
  margin-bottom: 2rem;
}

.page-header h1 {
  color: #1a365d;
  margin-bottom: 0.5rem;
}

.page-header p {
  color: #64748b;
  font-size: 1.125rem;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 1.5rem;
  margin-bottom: 2rem;
}

.stat-card {
  background: white;
  padding: 1.5rem;
  border-radius: 12px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  display: flex;
  align-items: center;
  gap: 1rem;
}

.stat-icon {
  font-size: 2rem;
  width: 60px;
  height: 60px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #f1f5f9;
  border-radius: 12px;
  color: #1a365d;
}

.stat-content {
  flex: 1;
}

.stat-number {
  font-size: 2rem;
  font-weight: bold;
  color: #1a365d;
}

.stat-label {
  color: #64748b;
  font-size: 0.875rem;
}

.compose-section {
  background: white;
  padding: 2rem;
  border-radius: 16px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  margin-bottom: 2rem;
}

.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
}

.section-header h3 {
  color: #1a365d;
}

.compose-actions {
  display: flex;
  gap: 1rem;
}

.compose-form {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1.5rem;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.form-group label {
  color: #374151;
  font-weight: 500;
}

.form-input,
.form-select,
.form-textarea {
  padding: 0.75rem 1rem;
  border: 2px solid #e2e8f0;
  border-radius: 8px;
  font-size: 0.875rem;
  transition: border-color 0.2s;
}

.form-input:focus,
.form-select:focus,
.form-textarea:focus {
  outline: none;
  border-color: #1a365d;
}

.form-textarea {
  resize: vertical;
  min-height: 120px;
}

.recipient-options {
  display: flex;
  gap: 1.5rem;
}

.radio-label {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  cursor: pointer;
}

.checkbox-group {
  display: flex;
  flex-wrap: wrap;
  gap: 1.5rem;
}

.checkbox-label {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  cursor: pointer;
}

.recent-notifications {
  background: white;
  padding: 2rem;
  border-radius: 16px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  margin-bottom: 2rem;
}

.filter-controls {
  display: flex;
  gap: 1rem;
}

.search-input {
  padding: 0.5rem 1rem;
  border: 2px solid #e2e8f0;
  border-radius: 8px;
  width: 300px;
}

.filter-select {
  padding: 0.5rem 1rem;
  border: 2px solid #e2e8f0;
  border-radius: 8px;
}

.notifications-list {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.notification-item {
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  padding: 1.5rem;
}

.notification-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 1rem;
}

.notification-info h4 {
  color: #1a365d;
  margin-bottom: 0.5rem;
}

.notification-meta {
  display: flex;
  gap: 0.75rem;
  flex-wrap: wrap;
}

.type-badge,
.priority-badge,
.status-badge {
  padding: 0.25rem 0.75rem;
  border-radius: 20px;
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
}

.type-badge.announcement {
  background: #e3f2fd;
  color: #1565c0;
}

.type-badge.alert {
  background: #ffebee;
  color: #c62828;
}

.type-badge.reminder {
  background: #fff3e0;
  color: #ef6c00;
}

.type-badge.maintenance {
  background: #fce4ec;
  color: #ad1457;
}

.priority-badge.low {
  background: #f1f8e9;
  color: #2e7d32;
}

.priority-badge.medium {
  background: #fff3e0;
  color: #ef6c00;
}

.priority-badge.high {
  background: #ffebee;
  color: #c62828;
}

.priority-badge.urgent {
  background: #d32f2f;
  color: white;
}

.status-badge.sent {
  background: #e8f5e8;
  color: #2e7d32;
}

.status-badge.draft {
  background: #f5f5f5;
  color: #616161;
}

.date {
  color: #64748b;
  font-size: 0.875rem;
}

.notification-content {
  margin-bottom: 1rem;
}

.notification-content p {
  color: #374151;
  line-height: 1.6;
}

.notification-details {
  display: flex;
  gap: 1.5rem;
  margin-bottom: 1rem;
  flex-wrap: wrap;
}

.detail-item {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  color: #64748b;
  font-size: 0.875rem;
}

.detail-item i {
  color: #1a365d;
}

.notification-actions {
  display: flex;
  gap: 0.5rem;
  flex-wrap: wrap;
}

.btn {
  padding: 0.5rem 1rem;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-size: 0.875rem;
  font-weight: 500;
  transition: all 0.2s;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.btn-primary {
  background: #1a365d;
  color: white;
}

.btn-primary:hover {
  background: #2c5282;
}

.btn-outline {
  background: white;
  color: #1a365d;
  border: 1px solid #1a365d;
}

.btn-success {
  background: #ff6b35;
  color: white;
}

.btn-danger {
  background: #dc3545;
  color: white;
}

.btn-danger:hover {
  background: #c82333;
}

.templates-section {
  background: white;
  padding: 2rem;
  border-radius: 16px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.templates-section h3 {
  color: #1a365d;
  margin-bottom: 1.5rem;
}

.templates-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 1.5rem;
}

.template-card {
  border: 2px solid #e2e8f0;
  border-radius: 12px;
  padding: 1.5rem;
  cursor: pointer;
  transition: all 0.2s;
}

.template-card:hover {
  border-color: #1a365d;
  transform: translateY(-2px);
}

.template-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}

.template-header h4 {
  color: #1a365d;
}

.template-card p {
  color: #64748b;
  margin-bottom: 1rem;
  line-height: 1.4;
}

.template-preview {
  background: #f8fafc;
  padding: 0.75rem;
  border-radius: 6px;
  border-left: 4px solid #1a365d;
}

.template-preview small {
  color: #374151;
  font-style: italic;
}

@media (max-width: 768px) {
  .notification-center {
    padding: 1rem;
  }
  
  .stats-grid {
    grid-template-columns: 1fr;
  }
  
  .form-row {
    grid-template-columns: 1fr;
  }
  
  .recipient-options {
    flex-direction: column;
    gap: 0.75rem;
  }
  
  .section-header {
    flex-direction: column;
    align-items: stretch;
    gap: 1rem;
  }
  
  .filter-controls {
    flex-direction: column;
  }
  
  .search-input {
    width: 100%;
  }
  
  .notification-details {
    flex-direction: column;
    gap: 0.75rem;
  }
  
  .notification-actions {
    justify-content: stretch;
  }
  
  .notification-actions .btn {
    flex: 1;
    justify-content: center;
  }
  
  .templates-grid {
    grid-template-columns: 1fr;
  }
}
</style>
