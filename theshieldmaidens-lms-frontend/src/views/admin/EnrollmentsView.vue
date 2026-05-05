<template>
  <div class="admin-enrollments">
    <!-- Header -->
    <div class="enrollments-header">
      <div class="header-content">
        <div class="header-left">
          <h1><i class="fas fa-user-graduate"></i> Enrollments</h1>
          <p>Track and manage student course enrollments</p>
        </div>
        <div class="header-actions">
          <button @click="showAddEnrollmentModal = true" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add Enrollment
          </button>
        </div>
      </div>
    </div>

    <!-- Stats Overview -->
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-icon total">
          <i class="fas fa-users"></i>
        </div>
        <div class="stat-content">
          <h3>{{ totalEnrollments }}</h3>
          <p>Total Enrollments</p>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon active">
          <i class="fas fa-play-circle"></i>
        </div>
        <div class="stat-content">
          <h3>{{ activeEnrollments }}</h3>
          <p>Active Enrollments</p>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon completed">
          <i class="fas fa-check-circle"></i>
        </div>
        <div class="stat-content">
          <h3>{{ completedEnrollments }}</h3>
          <p>Completed</p>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon pending">
          <i class="fas fa-clock"></i>
        </div>
        <div class="stat-content">
          <h3>{{ pendingEnrollments }}</h3>
          <p>Pending</p>
        </div>
      </div>
    </div>

    <!-- Filters -->
    <div class="filters-section">
      <div class="filters-left">
        <div class="search-bar">
          <i class="fas fa-search"></i>
          <input 
            v-model="searchQuery" 
            type="text" 
            placeholder="Search enrollments..."
            @input="filterEnrollments"
          >
        </div>
        <select v-model="statusFilter" @change="filterEnrollments" class="filter-select">
          <option value="">All Status</option>
          <option value="active">Active</option>
          <option value="completed">Completed</option>
          <option value="pending">Pending</option>
          <option value="suspended">Suspended</option>
        </select>
        <select v-model="courseFilter" @change="filterEnrollments" class="filter-select">
          <option value="">All Courses</option>
          <option v-for="course in courses" :key="course.id" :value="course.id">
            {{ course.title }}
          </option>
        </select>
      </div>
      <div class="filters-right">
        <button @click="refreshEnrollments" class="btn btn-secondary" :disabled="refreshing">
          <i :class="refreshing ? 'fas fa-spinner fa-spin' : 'fas fa-sync-alt'"></i>
          Refresh
        </button>
      </div>
    </div>

    <!-- Enrollments Table -->
    <div class="enrollments-table-container">
      <table class="enrollments-table">
        <thead>
          <tr>
            <th>Student</th>
            <th>Course</th>
            <th>Enrollment Date</th>
            <th>Progress</th>
            <th>Status</th>
            <th>Completion Date</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="enrollment in filteredEnrollments" :key="enrollment.id">
            <td>
              <div class="student-info">
                <div class="student-avatar">
                  {{ enrollment.student_name.charAt(0).toUpperCase() }}
                </div>
                <div class="student-details">
                  <div class="student-name">{{ enrollment.student_name }}</div>
                  <div class="student-email">{{ enrollment.student_email }}</div>
                </div>
              </div>
            </td>
            <td>
              <div class="course-info">
                <div class="course-title">{{ enrollment.course_title }}</div>
                <div class="course-category">{{ enrollment.course_category }}</div>
              </div>
            </td>
            <td>{{ formatDate(enrollment.enrollment_date) }}</td>
            <td>
              <div class="progress-bar">
                <div class="progress-fill" :style="{ width: enrollment.progress + '%' }"></div>
              </div>
              <span class="progress-text">{{ enrollment.progress }}%</span>
            </td>
            <td>
              <span class="status-badge" :class="enrollment.status">
                {{ enrollment.status }}
              </span>
            </td>
            <td>{{ enrollment.completion_date ? formatDate(enrollment.completion_date) : '-' }}</td>
            <td>
              <div class="action-buttons">
                <button @click="viewEnrollment(enrollment)" class="action-btn view" title="View Details">
                  <i class="fas fa-eye"></i>
                </button>
                <button @click="editEnrollment(enrollment)" class="action-btn edit" title="Edit Enrollment">
                  <i class="fas fa-edit"></i>
                </button>
                <button @click="updateProgress(enrollment)" class="action-btn progress" title="Update Progress">
                  <i class="fas fa-chart-line"></i>
                </button>
                <button @click="removeEnrollment(enrollment)" class="action-btn delete" title="Remove Enrollment">
                  <i class="fas fa-trash"></i>
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
      
      <!-- Empty State -->
      <div v-if="filteredEnrollments.length === 0" class="empty-state">
        <i class="fas fa-user-graduate"></i>
        <h3>No enrollments found</h3>
        <p>Try adjusting your search or filters</p>
      </div>
    </div>

    <!-- Add Enrollment Modal -->
    <div v-if="showAddEnrollmentModal" class="modal-overlay" @click="showAddEnrollmentModal = false">
      <div class="modal" @click.stop>
        <div class="modal-header">
          <h3>Add New Enrollment</h3>
          <button @click="showAddEnrollmentModal = false" class="btn-close">×</button>
        </div>
        <div class="modal-body">
          <form @submit.prevent="addEnrollment">
            <div class="form-group">
              <label>Student</label>
              <select v-model="newEnrollment.student_id" required>
                <option value="">Select Student</option>
                <option v-for="student in students" :key="student.id" :value="student.id">
                  {{ student.name }} ({{ student.email }})
                </option>
              </select>
            </div>
            <div class="form-group">
              <label>Course</label>
              <select v-model="newEnrollment.course_id" required>
                <option value="">Select Course</option>
                <option v-for="course in courses" :key="course.id" :value="course.id">
                  {{ course.title }}
                </option>
              </select>
            </div>
            <div class="form-group">
              <label>Status</label>
              <select v-model="newEnrollment.status">
                <option value="active">Active</option>
                <option value="pending">Pending</option>
                <option value="suspended">Suspended</option>
              </select>
            </div>
            <div class="form-actions">
              <button type="submit" class="btn btn-primary">Add Enrollment</button>
              <button type="button" @click="showAddEnrollmentModal = false" class="btn btn-secondary">Cancel</button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Success/Error Messages -->
    <div v-if="message" class="message" :class="messageType">
      <i :class="messageType === 'success' ? 'fas fa-check-circle' : 'fas fa-exclamation-circle'"></i>
      {{ message }}
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'

const authStore = useAuthStore()

// State
const enrollments = ref([])
const students = ref([])
const courses = ref([])
const searchQuery = ref('')
const statusFilter = ref('')
const courseFilter = ref('')
const refreshing = ref(false)
const showAddEnrollmentModal = ref(false)
const message = ref('')
const messageType = ref('')

// New enrollment form
const newEnrollment = ref({
  student_id: '',
  course_id: '',
  status: 'active'
})

// Computed
const filteredEnrollments = computed(() => {
  let filtered = enrollments.value

  // Search filter
  if (searchQuery.value) {
    filtered = filtered.filter(enrollment => 
      enrollment.student_name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      enrollment.student_email.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      enrollment.course_title.toLowerCase().includes(searchQuery.value.toLowerCase())
    )
  }

  // Status filter
  if (statusFilter.value) {
    filtered = filtered.filter(enrollment => enrollment.status === statusFilter.value)
  }

  // Course filter
  if (courseFilter.value) {
    filtered = filtered.filter(enrollment => enrollment.course_id == courseFilter.value)
  }

  return filtered
})

// Stats
const totalEnrollments = computed(() => enrollments.value.length)
const activeEnrollments = computed(() => enrollments.value.filter(e => e.status === 'active').length)
const completedEnrollments = computed(() => enrollments.value.filter(e => e.status === 'completed').length)
const pendingEnrollments = computed(() => enrollments.value.filter(e => e.status === 'pending').length)

// Methods
const fetchEnrollments = async () => {
  try {
    const response = await fetch('http://127.0.0.1:8000/api/admin/enrollments', {
      headers: {
        'Authorization': `Bearer ${authStore.token}`,
        'Accept': 'application/json'
      }
    })
    const data = await response.json()
    enrollments.value = data.data || []
  } catch (error) {
    console.error('Error fetching enrollments:', error)
    showMessage('Error fetching enrollments', 'error')
  }
}

const fetchStudents = async () => {
  try {
    const response = await fetch('http://127.0.0.1:8000/api/admin/students', {
      headers: {
        'Authorization': `Bearer ${authStore.token}`,
        'Accept': 'application/json'
      }
    })
    const data = await response.json()
    students.value = data.data || []
  } catch (error) {
    console.error('Error fetching students:', error)
  }
}

const fetchCourses = async () => {
  try {
    const response = await fetch('http://127.0.0.1:8000/api/admin/courses', {
      headers: {
        'Authorization': `Bearer ${authStore.token}`,
        'Accept': 'application/json'
      }
    })
    const data = await response.json()
    courses.value = data.data || []
  } catch (error) {
    console.error('Error fetching courses:', error)
  }
}

const filterEnrollments = () => {
  // Filtering is handled by computed property
}

const refreshEnrollments = async () => {
  refreshing.value = true
  await fetchEnrollments()
  refreshing.value = false
  showMessage('Enrollments refreshed successfully', 'success')
}

const viewEnrollment = (enrollment) => {
  console.log('View enrollment:', enrollment)
  // TODO: Open enrollment details modal
}

const editEnrollment = (enrollment) => {
  console.log('Edit enrollment:', enrollment)
  // TODO: Open edit modal
}

const updateProgress = (enrollment) => {
  const newProgress = prompt('Enter new progress (0-100):', enrollment.progress)
  if (newProgress !== null && !isNaN(newProgress)) {
    saveProgress(enrollment.id, Math.min(100, Math.max(0, parseInt(newProgress))))
  }
}

const saveProgress = async (enrollmentId, progress) => {
  try {
    const response = await fetch(`http://127.0.0.1:8000/api/admin/enrollments/${enrollmentId}/progress`, {
      method: 'PUT',
      headers: {
        'Authorization': `Bearer ${authStore.token}`,
        'Accept': 'application/json',
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({ progress })
    })
    
    if (response.ok) {
      const enrollment = enrollments.value.find(e => e.id === enrollmentId)
      if (enrollment) {
        enrollment.progress = progress
        if (progress === 100) {
          enrollment.status = 'completed'
          enrollment.completion_date = new Date().toISOString()
        }
      }
      showMessage('Progress updated successfully', 'success')
    }
  } catch (error) {
    console.error('Error updating progress:', error)
    showMessage('Error updating progress', 'error')
  }
}

const addEnrollment = async () => {
  try {
    const response = await fetch('http://127.0.0.1:8000/api/admin/enrollments', {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${authStore.token}`,
        'Accept': 'application/json',
        'Content-Type': 'application/json'
      },
      body: JSON.stringify(newEnrollment.value)
    })
    
    if (response.ok) {
      await fetchEnrollments()
      showAddEnrollmentModal.value = false
      newEnrollment.value = { student_id: '', course_id: '', status: 'active' }
      showMessage('Enrollment added successfully', 'success')
    }
  } catch (error) {
    console.error('Error adding enrollment:', error)
    showMessage('Error adding enrollment', 'error')
  }
}

const removeEnrollment = async (enrollment) => {
  if (!confirm(`Are you sure you want to remove this enrollment?`)) return
  
  try {
    const response = await fetch(`http://127.0.0.1:8000/api/admin/enrollments/${enrollment.id}`, {
      method: 'DELETE',
      headers: {
        'Authorization': `Bearer ${authStore.token}`,
        'Accept': 'application/json'
      }
    })
    
    if (response.ok) {
      enrollments.value = enrollments.value.filter(e => e.id !== enrollment.id)
      showMessage('Enrollment removed successfully', 'success')
    }
  } catch (error) {
    console.error('Error removing enrollment:', error)
    showMessage('Error removing enrollment', 'error')
  }
}

const formatDate = (date) => {
  return new Date(date).toLocaleDateString()
}

const showMessage = (text, type) => {
  message.value = text
  messageType.value = type
  setTimeout(() => {
    message.value = ''
  }, 3000)
}

// Lifecycle
onMounted(() => {
  fetchEnrollments()
  fetchStudents()
  fetchCourses()
})
</script>

<style scoped>
.admin-enrollments {
  padding: 0;
}

/* Header */
.enrollments-header {
  background: white;
  padding: 25px;
  border-radius: 12px;
  margin-bottom: 25px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.header-content {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.header-left h1 {
  margin: 0 0 5px 0;
  color: #333;
  font-size: 1.5rem;
}

.header-left p {
  margin: 0;
  color: #666;
  font-size: 0.9rem;
}

/* Stats Grid */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 20px;
  margin-bottom: 25px;
}

.stat-card {
  background: white;
  padding: 25px;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  display: flex;
  align-items: center;
  gap: 20px;
}

.stat-icon {
  width: 60px;
  height: 60px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
  color: white;
}

.stat-icon.total {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.stat-icon.active {
  background: linear-gradient(135deg, #4CAF50 0%, #45a049 100%);
}

.stat-icon.completed {
  background: linear-gradient(135deg, #2196F3 0%, #1976d2 100%);
}

.stat-icon.pending {
  background: linear-gradient(135deg, #FF9800 0%, #f57c00 100%);
}

.stat-content h3 {
  margin: 0;
  font-size: 2rem;
  font-weight: 700;
  color: #333;
}

.stat-content p {
  margin: 5px 0 0 0;
  color: #666;
  font-size: 0.9rem;
}

/* Filters */
.filters-section {
  background: white;
  padding: 20px 25px;
  border-radius: 12px;
  margin-bottom: 25px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.filters-left {
  display: flex;
  gap: 15px;
  align-items: center;
}

.search-bar {
  display: flex;
  align-items: center;
  gap: 10px;
  background: #f8f9fa;
  border: 1px solid #dee2e6;
  border-radius: 8px;
  padding: 10px 15px;
  min-width: 300px;
}

.search-bar i {
  color: #6c757d;
}

.search-bar input {
  border: none;
  background: none;
  outline: none;
  flex: 1;
  font-size: 0.9rem;
}

.filter-select {
  padding: 10px 15px;
  border: 1px solid #dee2e6;
  border-radius: 8px;
  background: white;
  font-size: 0.9rem;
}

/* Table */
.enrollments-table-container {
  background: white;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.enrollments-table {
  width: 100%;
  border-collapse: collapse;
}

.enrollments-table th {
  background: #f8f9fa;
  padding: 15px;
  text-align: left;
  font-weight: 600;
  color: #495057;
  border-bottom: 2px solid #dee2e6;
}

.enrollments-table td {
  padding: 15px;
  border-bottom: 1px solid #f1f3f5;
}

.student-info {
  display: flex;
  align-items: center;
  gap: 12px;
}

.student-avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: #3498db;
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 600;
}

.student-name {
  font-weight: 600;
  color: #333;
}

.student-email {
  font-size: 0.8rem;
  color: #6c757d;
}

.course-title {
  font-weight: 600;
  color: #333;
}

.course-category {
  font-size: 0.8rem;
  color: #6c757d;
}

.progress-bar {
  width: 100px;
  height: 8px;
  background: #e9ecef;
  border-radius: 4px;
  overflow: hidden;
  display: inline-block;
  margin-right: 10px;
}

.progress-fill {
  height: 100%;
  background: linear-gradient(90deg, #4CAF50, #45a049);
  transition: width 0.3s ease;
}

.progress-text {
  font-size: 0.8rem;
  color: #666;
}

.status-badge {
  padding: 4px 12px;
  border-radius: 20px;
  font-size: 0.8rem;
  font-weight: 600;
}

.status-badge.active {
  background: #d4edda;
  color: #155724;
}

.status-badge.completed {
  background: #cce5ff;
  color: #004085;
}

.status-badge.pending {
  background: #fff3cd;
  color: #856404;
}

.status-badge.suspended {
  background: #f8d7da;
  color: #721c24;
}

.action-buttons {
  display: flex;
  gap: 8px;
}

.action-btn {
  width: 32px;
  height: 32px;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.3s ease;
}

.action-btn.view {
  background: #fff3e0;
  color: #ff6b35;
}

.action-btn.view:hover {
  background: #ffe0cc;
}

.action-btn.edit {
  background: #fff3e0;
  color: #f57c00;
}

.action-btn.edit:hover {
  background: #ffe0b2;
}

.action-btn.progress {
  background: #e8f5e8;
  color: #4caf50;
}

.action-btn.progress:hover {
  background: #c8e6c9;
}

.action-btn.delete {
  background: #ffebee;
  color: #f44336;
}

.action-btn.delete:hover {
  background: #ffcdd2;
}

/* Empty State */
.empty-state {
  text-align: center;
  padding: 60px 20px;
  color: #6c757d;
}

.empty-state i {
  font-size: 3rem;
  margin-bottom: 20px;
  opacity: 0.5;
}

.empty-state h3 {
  margin: 0 0 10px 0;
  color: #495057;
}

/* Modal */
.modal-overlay {
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
}

.modal {
  background: white;
  border-radius: 12px;
  width: 90%;
  max-width: 500px;
  max-height: 90vh;
  overflow-y: auto;
}

.modal-header {
  padding: 20px 25px;
  border-bottom: 1px solid #dee2e6;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.modal-header h3 {
  margin: 0;
  color: #333;
}

.btn-close {
  background: none;
  border: none;
  font-size: 1.5rem;
  cursor: pointer;
  color: #6c757d;
}

.modal-body {
  padding: 25px;
}

.form-group {
  margin-bottom: 20px;
}

.form-group label {
  display: block;
  margin-bottom: 8px;
  font-weight: 600;
  color: #333;
}

.form-group select {
  width: 100%;
  padding: 12px;
  border: 1px solid #dee2e6;
  border-radius: 8px;
  font-size: 0.9rem;
}

.form-actions {
  display: flex;
  gap: 10px;
  justify-content: flex-end;
  margin-top: 20px;
}

.btn {
  padding: 10px 20px;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
}

.btn-primary {
  background: #ff6b35;
  color: white;
}

.btn-primary:hover {
  background: #e55a2b;
}

.btn-secondary {
  background: #6c757d;
  color: white;
}

.btn-secondary:hover {
  background: #5a6268;
}

/* Message */
.message {
  position: fixed;
  top: 20px;
  right: 20px;
  padding: 15px 20px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  gap: 10px;
  z-index: 1001;
  animation: slideIn 0.3s ease;
}

.message.success {
  background: #d4edda;
  color: #155724;
  border: 1px solid #c3e6cb;
}

.message.error {
  background: #f8d7da;
  color: #721c24;
  border: 1px solid #f5c6cb;
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

/* Responsive */
@media (max-width: 768px) {
  .stats-grid {
    grid-template-columns: repeat(2, 1fr);
  }
  
  .filters-section {
    flex-direction: column;
    gap: 15px;
  }
  
  .filters-left {
    flex-wrap: wrap;
  }
  
  .search-bar {
    min-width: 100%;
  }
  
  .enrollments-table {
    font-size: 0.8rem;
  }
  
  .enrollments-table th,
  .enrollments-table td {
    padding: 10px;
  }
  
  .action-buttons {
    flex-direction: column;
  }
}
</style>
