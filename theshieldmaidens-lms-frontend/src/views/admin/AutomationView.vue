<template>
  <div class="automation-dashboard">
    <div class="page-header">
      <h1>Automation Tools</h1>
      <p>Schedule notifications, reminders, and automate enrollment rules</p>
    </div>

    <!-- Automation Overview -->
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-icon">
          <i class="fas fa-clock"></i>
        </div>
        <div class="stat-content">
          <div class="stat-number">{{ stats.activeAutomations }}</div>
          <div class="stat-label">Active Automations</div>
        </div>
      </div>
      
      <div class="stat-card">
        <div class="stat-icon">
          <i class="fas fa-paper-plane"></i>
        </div>
        <div class="stat-content">
          <div class="stat-number">{{ stats.notificationsSent }}</div>
          <div class="stat-label">Notifications Sent Today</div>
        </div>
      </div>
      
      <div class="stat-card">
        <div class="stat-icon">
          <i class="fas fa-users"></i>
        </div>
        <div class="stat-content">
          <div class="stat-number">{{ stats.autoEnrollments }}</div>
          <div class="stat-label">Auto-Enrollments</div>
        </div>
      </div>
      
      <div class="stat-card">
        <div class="stat-icon">
          <i class="fas fa-check-circle"></i>
        </div>
        <div class="stat-content">
          <div class="stat-number">{{ stats.successRate }}%</div>
          <div class="stat-label">Success Rate</div>
        </div>
      </div>
    </div>

    <!-- Automation Categories -->
    <div class="automation-categories">
      <h3>Automation Categories</h3>
      <div class="category-grid">
        <button @click="activeCategory = 'notifications'" :class="['category-btn', { active: activeCategory === 'notifications' }]">
          <i class="fas fa-bell"></i>
          <span>Notifications</span>
        </button>
        
        <button @click="activeCategory = 'enrollment'" :class="['category-btn', { active: activeCategory === 'enrollment' }]">
          <i class="fas fa-user-plus"></i>
          <span>Enrollment Rules</span>
        </button>
        
        <button @click="activeCategory = 'reminders'" :class="['category-btn', { active: activeCategory === 'reminders' }]">
          <i class="fas fa-clock"></i>
          <span>Reminders</span>
        </button>
        
        <button @click="activeCategory = 'reports'" :class="['category-btn', { active: activeCategory === 'reports' }]">
          <i class="fas fa-chart-bar"></i>
          <span>Reports</span>
        </button>
      </div>
    </div>

    <!-- Notification Automations -->
    <div v-if="activeCategory === 'notifications'" class="automation-section">
      <div class="section-header">
        <h3>Notification Automations</h3>
        <button @click="showNotificationForm = true" class="btn btn-primary">
          <i class="fas fa-plus"></i>
          Create Automation
        </button>
      </div>
      
      <div class="automation-list">
        <div v-for="automation in notificationAutomations" :key="automation.id" class="automation-item">
          <div class="automation-header">
            <div class="automation-info">
              <h4>{{ automation.name }}</h4>
              <p>{{ automation.description }}</p>
            </div>
            <div class="automation-status">
              <span :class="['status-badge', automation.status]">{{ automation.status }}</span>
            </div>
          </div>
          
          <div class="automation-details">
            <div class="detail-item">
              <i class="fas fa-clock"></i>
              <span>Trigger: {{ automation.trigger }}</span>
            </div>
            <div class="detail-item">
              <i class="fas fa-users"></i>
              <span>Recipients: {{ automation.recipients }}</span>
            </div>
            <div class="detail-item">
              <i class="fas fa-calendar"></i>
              <span>Schedule: {{ automation.schedule }}</span>
            </div>
          </div>
          
          <div class="automation-actions">
            <button @click="editAutomation(automation)" class="btn btn-outline">
              <i class="fas fa-edit"></i>
              Edit
            </button>
            <button @click="toggleAutomation(automation)" :class="['btn', automation.status === 'active' ? 'btn-warning' : 'btn-success']">
              <i :class="automation.status === 'active' ? 'fas fa-pause' : 'fas fa-play'"></i>
              {{ automation.status === 'active' ? 'Pause' : 'Start' }}
            </button>
            <button @click="deleteAutomation(automation)" class="btn btn-danger">
              <i class="fas fa-trash"></i>
              Delete
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Enrollment Rules -->
    <div v-if="activeCategory === 'enrollment'" class="automation-section">
      <div class="section-header">
        <h3>Enrollment Rules</h3>
        <button @click="showEnrollmentForm = true" class="btn btn-primary">
          <i class="fas fa-plus"></i>
          Create Rule
        </button>
      </div>
      
      <div class="automation-list">
        <div v-for="rule in enrollmentRules" :key="rule.id" class="automation-item">
          <div class="automation-header">
            <div class="automation-info">
              <h4>{{ rule.name }}</h4>
              <p>{{ rule.description }}</p>
            </div>
            <div class="automation-status">
              <span :class="['status-badge', rule.status]">{{ rule.status }}</span>
            </div>
          </div>
          
          <div class="automation-details">
            <div class="detail-item">
              <i class="fas fa-filter"></i>
              <span>Condition: {{ rule.condition }}</span>
            </div>
            <div class="detail-item">
              <i class="fas fa-graduation-cap"></i>
              <span>Course: {{ rule.course }}</span>
            </div>
            <div class="detail-item">
              <i class="fas fa-user-tag"></i>
              <span>Role: {{ rule.role }}</span>
            </div>
          </div>
          
          <div class="automation-actions">
            <button @click="editRule(rule)" class="btn btn-outline">
              <i class="fas fa-edit"></i>
              Edit
            </button>
            <button @click="toggleRule(rule)" :class="['btn', rule.status === 'active' ? 'btn-warning' : 'btn-success']">
              <i :class="rule.status === 'active' ? 'fas fa-pause' : 'fas fa-play'"></i>
              {{ rule.status === 'active' ? 'Disable' : 'Enable' }}
            </button>
            <button @click="deleteRule(rule)" class="btn btn-danger">
              <i class="fas fa-trash"></i>
              Delete
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Reminder Automations -->
    <div v-if="activeCategory === 'reminders'" class="automation-section">
      <div class="section-header">
        <h3>Reminder Automations</h3>
        <button @click="showReminderForm = true" class="btn btn-primary">
          <i class="fas fa-plus"></i>
          Create Reminder
        </button>
      </div>
      
      <div class="automation-list">
        <div v-for="reminder in reminders" :key="reminder.id" class="automation-item">
          <div class="automation-header">
            <div class="automation-info">
              <h4>{{ reminder.name }}</h4>
              <p>{{ reminder.description }}</p>
            </div>
            <div class="automation-status">
              <span :class="['status-badge', reminder.status]">{{ reminder.status }}</span>
            </div>
          </div>
          
          <div class="automation-details">
            <div class="detail-item">
              <i class="fas fa-clock"></i>
              <span>Timing: {{ reminder.timing }}</span>
            </div>
            <div class="detail-item">
              <i class="fas fa-bullseye"></i>
              <span>Target: {{ reminder.target }}</span>
            </div>
            <div class="detail-item">
              <i class="fas fa-redo"></i>
              <span>Frequency: {{ reminder.frequency }}</span>
            </div>
          </div>
          
          <div class="automation-actions">
            <button @click="editReminder(reminder)" class="btn btn-outline">
              <i class="fas fa-edit"></i>
              Edit
            </button>
            <button @click="toggleReminder(reminder)" :class="['btn', reminder.status === 'active' ? 'btn-warning' : 'btn-success']">
              <i :class="reminder.status === 'active' ? 'fas fa-pause' : 'fas fa-play'"></i>
              {{ reminder.status === 'active' ? 'Pause' : 'Start' }}
            </button>
            <button @click="deleteReminder(reminder)" class="btn btn-danger">
              <i class="fas fa-trash"></i>
              Delete
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Report Automations -->
    <div v-if="activeCategory === 'reports'" class="automation-section">
      <div class="section-header">
        <h3>Report Automations</h3>
        <button @click="showReportForm = true" class="btn btn-primary">
          <i class="fas fa-plus"></i>
          Create Report
        </button>
      </div>
      
      <div class="automation-list">
        <div v-for="report in reportAutomations" :key="report.id" class="automation-item">
          <div class="automation-header">
            <div class="automation-info">
              <h4>{{ report.name }}</h4>
              <p>{{ report.description }}</p>
            </div>
            <div class="automation-status">
              <span :class="['status-badge', report.status]">{{ report.status }}</span>
            </div>
          </div>
          
          <div class="automation-details">
            <div class="detail-item">
              <i class="fas fa-file-alt"></i>
              <span>Type: {{ report.type }}</span>
            </div>
            <div class="detail-item">
              <i class="fas fa-calendar"></i>
              <span>Schedule: {{ report.schedule }}</span>
            </div>
            <div class="detail-item">
              <i class="fas fa-envelope"></i>
              <span>Recipients: {{ report.recipients }}</span>
            </div>
          </div>
          
          <div class="automation-actions">
            <button @click="editReport(report)" class="btn btn-outline">
              <i class="fas fa-edit"></i>
              Edit
            </button>
            <button @click="toggleReport(report)" :class="['btn', report.status === 'active' ? 'btn-warning' : 'btn-success']">
              <i :class="report.status === 'active' ? 'fas fa-pause' : 'fas fa-play'"></i>
              {{ report.status === 'active' ? 'Pause' : 'Start' }}
            </button>
            <button @click="deleteReport(report)" class="btn btn-danger">
              <i class="fas fa-trash"></i>
              Delete
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Automation Logs -->
    <div class="automation-logs">
      <h3>Recent Automation Activity</h3>
      <div class="logs-list">
        <div v-for="log in automationLogs" :key="log.id" class="log-item">
          <div class="log-icon">
            <i :class="log.icon"></i>
          </div>
          <div class="log-content">
            <p>{{ log.message }}</p>
            <small>{{ formatTime(log.timestamp) }}</small>
          </div>
          <div class="log-status">
            <span :class="['status-badge', log.status]">{{ log.status }}</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'

const activeCategory = ref('notifications')
const showNotificationForm = ref(false)
const showEnrollmentForm = ref(false)
const showReminderForm = ref(false)
const showReportForm = ref(false)

const stats = ref({
  activeAutomations: 24,
  notificationsSent: 156,
  autoEnrollments: 89,
  successRate: 98
})

const notificationAutomations = ref([
  {
    id: 1,
    name: 'Welcome Email Series',
    description: 'Send welcome emails to new users over 7 days',
    trigger: 'User Registration',
    recipients: 'New Users',
    schedule: 'Daily at 9:00 AM',
    status: 'active'
  },
  {
    id: 2,
    name: 'Course Completion Notification',
    description: 'Notify instructors when students complete courses',
    trigger: 'Course Completion',
    recipients: 'Course Instructors',
    schedule: 'Immediate',
    status: 'active'
  }
])

const enrollmentRules = ref([
  {
    id: 1,
    name: 'Auto-Enroll New Employees',
    description: 'Automatically enroll new users in onboarding courses',
    condition: 'User Role = Employee',
    course: 'Onboarding Essentials',
    role: 'Employee',
    status: 'active'
  },
  {
    id: 2,
    name: 'Department Course Assignment',
    description: 'Assign department-specific courses to users',
    condition: 'Department = IT',
    course: 'IT Security Basics',
    role: 'IT Staff',
    status: 'paused'
  }
])

const reminders = ref([
  {
    id: 1,
    name: 'Assignment Due Reminders',
    description: 'Remind students about upcoming assignment deadlines',
    timing: '3 days before due date',
    target: 'Students with pending assignments',
    frequency: 'Daily',
    status: 'active'
  },
  {
    id: 2,
    name: 'Certificate Expiry Alerts',
    description: 'Alert users about expiring certificates',
    timing: '30 days before expiry',
    target: 'Users with expiring certificates',
    frequency: 'Weekly',
    status: 'active'
  }
])

const reportAutomations = ref([
  {
    id: 1,
    name: 'Weekly Progress Reports',
    description: 'Generate and send weekly progress reports to managers',
    type: 'Course Progress',
    schedule: 'Every Monday at 8:00 AM',
    recipients: 'Department Managers',
    status: 'active'
  },
  {
    id: 2,
    name: 'Monthly Compliance Reports',
    description: 'Generate monthly compliance status reports',
    type: 'Compliance Status',
    schedule: '1st of each month',
    recipients: 'Compliance Officers',
    status: 'active'
  }
])

const automationLogs = ref([
  {
    id: 1,
    message: 'Welcome email series sent to 5 new users',
    icon: 'fas fa-envelope',
    status: 'success',
    timestamp: Date.now() - 3600000
  },
  {
    id: 2,
    message: 'Auto-enrollment completed for 12 users',
    icon: 'fas fa-user-plus',
    status: 'success',
    timestamp: Date.now() - 7200000
  },
  {
    id: 3,
    message: 'Weekly progress report generated',
    icon: 'fas fa-chart-bar',
    status: 'success',
    timestamp: Date.now() - 10800000
  }
])

const editAutomation = (automation) => {
  console.log('Edit automation:', automation)
}

const toggleAutomation = (automation) => {
  automation.status = automation.status === 'active' ? 'paused' : 'active'
}

const deleteAutomation = (automation) => {
  console.log('Delete automation:', automation)
}

const editRule = (rule) => {
  console.log('Edit rule:', rule)
}

const toggleRule = (rule) => {
  rule.status = rule.status === 'active' ? 'paused' : 'active'
}

const deleteRule = (rule) => {
  console.log('Delete rule:', rule)
}

const editReminder = (reminder) => {
  console.log('Edit reminder:', reminder)
}

const toggleReminder = (reminder) => {
  reminder.status = reminder.status === 'active' ? 'paused' : 'active'
}

const deleteReminder = (reminder) => {
  console.log('Delete reminder:', reminder)
}

const editReport = (report) => {
  console.log('Edit report:', report)
}

const toggleReport = (report) => {
  report.status = report.status === 'active' ? 'paused' : 'active'
}

const deleteReport = (report) => {
  console.log('Delete report:', report)
}

const formatTime = (timestamp) => {
  const diff = Date.now() - timestamp
  if (diff < 3600000) return `${Math.floor(diff / 60000)} minutes ago`
  if (diff < 86400000) return `${Math.floor(diff / 3600000)} hours ago`
  return `${Math.floor(diff / 86400000)} days ago`
}

onMounted(() => {
  console.log('Automation dashboard loaded')
})
</script>

<style scoped>
.automation-dashboard {
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

.automation-categories {
  margin-bottom: 2rem;
}

.automation-categories h3 {
  color: #1a365d;
  margin-bottom: 1rem;
}

.category-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1rem;
}

.category-btn {
  background: white;
  border: 2px solid #e2e8f0;
  border-radius: 8px;
  padding: 1rem;
  cursor: pointer;
  transition: all 0.2s;
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.category-btn:hover {
  border-color: #1a365d;
}

.category-btn.active {
  background: #1a365d;
  color: white;
  border-color: #1a365d;
}

.category-btn i {
  font-size: 1.25rem;
}

.automation-section {
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

.automation-list {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.automation-item {
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  padding: 1.5rem;
}

.automation-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 1rem;
}

.automation-info h4 {
  color: #1a365d;
  margin-bottom: 0.5rem;
}

.automation-info p {
  color: #64748b;
  font-size: 0.875rem;
}

.status-badge {
  padding: 0.25rem 0.75rem;
  border-radius: 20px;
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
}

.status-badge.active {
  background: #d4edda;
  color: #155724;
}

.status-badge.paused {
  background: #fff3cd;
  color: #856404;
}

.status-badge.success {
  background: #d4edda;
  color: #155724;
}

.status-badge.error {
  background: #f8d7da;
  color: #721c24;
}

.automation-details {
  display: flex;
  flex-wrap: wrap;
  gap: 1rem;
  margin-bottom: 1rem;
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

.automation-actions {
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

.btn-outline:hover {
  background: #1a365d;
  color: white;
}

.btn-success {
  background: #28a745;
  color: white;
}

.btn-success:hover {
  background: #218838;
}

.btn-warning {
  background: #ffc107;
  color: #212529;
}

.btn-warning:hover {
  background: #e0a800;
}

.btn-danger {
  background: #dc3545;
  color: white;
}

.btn-danger:hover {
  background: #c82333;
}

.automation-logs {
  background: white;
  padding: 2rem;
  border-radius: 16px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.automation-logs h3 {
  color: #1a365d;
  margin-bottom: 1.5rem;
}

.logs-list {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.log-item {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1rem;
  border-bottom: 1px solid #f1f5f9;
}

.log-item:last-child {
  border-bottom: none;
}

.log-icon {
  width: 40px;
  height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #f1f5f9;
  border-radius: 8px;
  color: #1a365d;
}

.log-content {
  flex: 1;
}

.log-content p {
  color: #374151;
  margin-bottom: 0.25rem;
}

.log-content small {
  color: #64748b;
  font-size: 0.75rem;
}

@media (max-width: 768px) {
  .automation-dashboard {
    padding: 1rem;
  }
  
  .stats-grid {
    grid-template-columns: 1fr;
  }
  
  .category-grid {
    grid-template-columns: 1fr;
  }
  
  .section-header {
    flex-direction: column;
    align-items: stretch;
    gap: 1rem;
  }
  
  .automation-details {
    flex-direction: column;
  }
  
  .automation-actions {
    justify-content: stretch;
  }
  
  .automation-actions .btn {
    flex: 1;
    justify-content: center;
  }
}
</style>
