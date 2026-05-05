<template>
  <div class="admin-assignments">
    <!-- Header -->
    <div class="assignments-header">
      <div class="header-content">
        <div class="header-left">
          <h1><i class="fas fa-tasks"></i> Assignments</h1>
          <p>Manage course assignments and student submissions</p>
        </div>
        <div class="header-actions">
          <button @click="showAddAssignmentModal = true" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add Assignment
          </button>
        </div>
      </div>
    </div>

    <!-- Stats Overview -->
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-icon total">
          <i class="fas fa-clipboard-list"></i>
        </div>
        <div class="stat-content">
          <h3>{{ totalAssignments }}</h3>
          <p>Total Assignments</p>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon pending">
          <i class="fas fa-clock"></i>
        </div>
        <div class="stat-content">
          <h3>{{ pendingSubmissions }}</h3>
          <p>Pending Submissions</p>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon graded">
          <i class="fas fa-check-double"></i>
        </div>
        <div class="stat-content">
          <h3>{{ gradedSubmissions }}</h3>
          <p>Graded Submissions</p>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon overdue">
          <i class="fas fa-exclamation-triangle"></i>
        </div>
        <div class="stat-content">
          <h3>{{ overdueAssignments }}</h3>
          <p>Overdue</p>
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
            placeholder="Search assignments..."
            @input="filterAssignments"
          >
        </div>
        <select v-model="statusFilter" @change="filterAssignments" class="filter-select">
          <option value="">All Status</option>
          <option value="draft">Draft</option>
          <option value="published">Published</option>
          <option value="closed">Closed</option>
        </select>
        <select v-model="courseFilter" @change="filterAssignments" class="filter-select">
          <option value="">All Courses</option>
          <option v-for="course in courses" :key="course.id" :value="course.id">
            {{ course.title }}
          </option>
        </select>
      </div>
      <div class="filters-right">
        <button @click="refreshAssignments" class="btn btn-secondary" :disabled="refreshing">
          <i :class="refreshing ? 'fas fa-spinner fa-spin' : 'fas fa-sync-alt'"></i>
          Refresh
        </button>
      </div>
    </div>

    <!-- Assignments Table -->
    <div class="assignments-table-container">
      <table class="assignments-table">
        <thead>
          <tr>
            <th>Assignment</th>
            <th>Course</th>
            <th>Due Date</th>
            <th>Submissions</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="assignment in filteredAssignments" :key="assignment.id">
            <td>
              <div class="assignment-info">
                <div class="assignment-title">{{ assignment.title }}</div>
                <div class="assignment-description">{{ assignment.description }}</div>
              </div>
            </td>
            <td>
              <div class="course-info">
                <div class="course-title">{{ assignment.course_title }}</div>
                <div class="course-facilitator">{{ assignment.facilitator_name }}</div>
              </div>
            </td>
            <td>
              <div class="due-date" :class="{ overdue: isOverdue(assignment.due_date) }">
                <i class="fas fa-calendar"></i>
                {{ formatDate(assignment.due_date) }}
              </div>
            </td>
            <td>
              <div class="submission-stats">
                <span class="submitted">{{ assignment.submitted_count }}</span>
                <span class="separator">/</span>
                <span class="total">{{ assignment.total_students }}</span>
                <div class="progress-bar">
                  <div class="progress-fill" :style="{ width: getSubmissionProgress(assignment) + '%' }"></div>
                </div>
              </div>
            </td>
            <td>
              <span class="status-badge" :class="assignment.status">
                {{ assignment.status }}
              </span>
            </td>
            <td>
              <div class="action-buttons">
                <button @click="viewAssignment(assignment)" class="action-btn view" title="View Assignment">
                  <i class="fas fa-eye"></i>
                </button>
                <button @click="editAssignment(assignment)" class="action-btn edit" title="Edit Assignment">
                  <i class="fas fa-edit"></i>
                </button>
                <button @click="gradeSubmissions(assignment)" class="action-btn grade" title="Grade Submissions">
                  <i class="fas fa-graduation-cap"></i>
                </button>
                <button @click="deleteAssignment(assignment)" class="action-btn delete" title="Delete Assignment">
                  <i class="fas fa-trash"></i>
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
      
      <!-- Empty State -->
      <div v-if="filteredAssignments.length === 0" class="empty-state">
        <i class="fas fa-tasks"></i>
        <h3>No assignments found</h3>
        <p>Try adjusting your search or filters</p>
      </div>
    </div>

    <!-- Add Assignment Modal -->
    <div v-if="showAddAssignmentModal" class="modal-overlay" @click="showAddAssignmentModal = false">
      <div class="modal" @click.stop>
        <div class="modal-header">
          <h3>Add New Assignment</h3>
          <button @click="showAddAssignmentModal = false" class="btn-close">×</button>
        </div>
        <div class="modal-body">
          <form @submit.prevent="addAssignment">
            <div class="form-group">
              <label>Assignment Title</label>
              <input v-model="assignmentForm.title" type="text" required>
            </div>
            <div class="form-group">
              <label>Description</label>
              <textarea v-model="assignmentForm.description" rows="3" required></textarea>
            </div>
            <div class="form-group">
              <label>Course</label>
              <select v-model="assignmentForm.course_id" required>
                <option value="">Select Course</option>
                <option v-for="course in courses" :key="course.id" :value="course.id">
                  {{ course.title }}
                </option>
              </select>
            </div>
            <div class="form-group">
              <label>Due Date</label>
              <input v-model="assignmentForm.due_date" type="datetime-local" required>
            </div>
            <div class="form-group">
              <label>Max Points</label>
              <input v-model="assignmentForm.max_points" type="number" min="1" value="100" required>
            </div>
            <div class="form-actions">
              <button type="submit" class="btn btn-primary">Add Assignment</button>
              <button type="button" @click="showAddAssignmentModal = false" class="btn btn-secondary">Cancel</button>
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
const assignments = ref([])
const courses = ref([])
const searchQuery = ref('')
const statusFilter = ref('')
const courseFilter = ref('')
const refreshing = ref(false)
const showAddAssignmentModal = ref(false)
const message = ref('')
const messageType = ref('')

// Form
const assignmentForm = ref({
  title: '',
  description: '',
  course_id: '',
  due_date: '',
  max_points: 100
})

// Computed
const filteredAssignments = computed(() => {
  let filtered = assignments.value

  // Search filter
  if (searchQuery.value) {
    filtered = filtered.filter(assignment => 
      assignment.title.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      assignment.description.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      assignment.course_title.toLowerCase().includes(searchQuery.value.toLowerCase())
    )
  }

  // Status filter
  if (statusFilter.value) {
    filtered = filtered.filter(assignment => assignment.status === statusFilter.value)
  }

  // Course filter
  if (courseFilter.value) {
    filtered = filtered.filter(assignment => assignment.course_id == courseFilter.value)
  }

  return filtered
})

// Stats
const totalAssignments = computed(() => assignments.value.length)
const pendingSubmissions = computed(() => {
  return assignments.value.reduce((total, assignment) => 
    total + (assignment.total_students - assignment.submitted_count), 0)
})
const gradedSubmissions = computed(() => {
  return assignments.value.reduce((total, assignment) => total + assignment.graded_count, 0)
})
const overdueAssignments = computed(() => {
  return assignments.value.filter(assignment => 
    new Date(assignment.due_date) < new Date() && assignment.status === 'published'
  ).length
})

// Methods
const fetchAssignments = async () => {
  try {
    const response = await fetch('http://127.0.0.1:8000/api/admin/assignments', {
      headers: {
        'Authorization': `Bearer ${authStore.token}`,
        'Accept': 'application/json'
      }
    })
    const data = await response.json()
    assignments.value = data.data || []
  } catch (error) {
    console.error('Error fetching assignments:', error)
    showMessage('Error fetching assignments', 'error')
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

const filterAssignments = () => {
  // Filtering is handled by computed property
}

const refreshAssignments = async () => {
  refreshing.value = true
  await fetchAssignments()
  refreshing.value = false
  showMessage('Assignments refreshed successfully', 'success')
}

const viewAssignment = (assignment) => {
  console.log('View assignment:', assignment)
  // TODO: Open assignment details modal
}

const editAssignment = (assignment) => {
  console.log('Edit assignment:', assignment)
  // TODO: Open edit modal
}

const gradeSubmissions = (assignment) => {
  console.log('Grade submissions for:', assignment)
  // TODO: Navigate to grading interface
}

const deleteAssignment = async (assignment) => {
  if (!confirm(`Are you sure you want to delete "${assignment.title}"?`)) return
  
  try {
    const response = await fetch(`http://127.0.0.1:8000/api/admin/assignments/${assignment.id}`, {
      method: 'DELETE',
      headers: {
        'Authorization': `Bearer ${authStore.token}`,
        'Accept': 'application/json'
      }
    })
    
    if (response.ok) {
      assignments.value = assignments.value.filter(a => a.id !== assignment.id)
      showMessage('Assignment deleted successfully', 'success')
    }
  } catch (error) {
    console.error('Error deleting assignment:', error)
    showMessage('Error deleting assignment', 'error')
  }
}

const addAssignment = async () => {
  try {
    const response = await fetch('http://127.0.0.1:8000/api/admin/assignments', {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${authStore.token}`,
        'Accept': 'application/json',
        'Content-Type': 'application/json'
      },
      body: JSON.stringify(assignmentForm.value)
    })
    
    if (response.ok) {
      await fetchAssignments()
      showAddAssignmentModal.value = false
      assignmentForm.value = {
        title: '',
        description: '',
        course_id: '',
        due_date: '',
        max_points: 100
      }
      showMessage('Assignment added successfully', 'success')
    }
  } catch (error) {
    console.error('Error adding assignment:', error)
    showMessage('Error adding assignment', 'error')
  }
}

const isOverdue = (dueDate) => {
  return new Date(dueDate) < new Date()
}

const getSubmissionProgress = (assignment) => {
  return assignment.total_students > 0 
    ? (assignment.submitted_count / assignment.total_students) * 100
    : 0
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
  fetchAssignments()
  fetchCourses()
})
</script>

<style scoped>
.admin-assignments {
  padding: 0;
}

/* Header */
.assignments-header {
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

.stat-icon.pending {
  background: linear-gradient(135deg, #FF9800 0%, #f57c00 100%);
}

.stat-icon.graded {
  background: linear-gradient(135deg, #4CAF50 0%, #45a049 100%);
}

.stat-icon.overdue {
  background: linear-gradient(135deg, #f44336 0%, #d32f2f 100%);
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
.assignments-table-container {
  background: white;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.assignments-table {
  width: 100%;
  border-collapse: collapse;
}

.assignments-table th {
  background: #f8f9fa;
  padding: 15px;
  text-align: left;
  font-weight: 600;
  color: #495057;
  border-bottom: 2px solid #dee2e6;
}

.assignments-table td {
  padding: 15px;
  border-bottom: 1px solid #f1f3f5;
}

.assignment-title {
  font-weight: 600;
  color: #333;
}

.assignment-description {
  font-size: 0.8rem;
  color: #666;
  margin-top: 5px;
}

.course-title {
  font-weight: 600;
  color: #333;
}

.course-facilitator {
  font-size: 0.8rem;
  color: #6c757d;
}

.due-date {
  display: flex;
  align-items: center;
  gap: 8px;
  color: #666;
}

.due-date.overdue {
  color: #f44336;
}

.submission-stats {
  display: flex;
  align-items: center;
  gap: 10px;
}

.submitted {
  font-weight: 600;
  color: #4CAF50;
}

.separator {
  color: #666;
}

.total {
  color: #666;
}

.progress-bar {
  width: 50px;
  height: 6px;
  background: #e9ecef;
  border-radius: 3px;
  overflow: hidden;
}

.progress-fill {
  height: 100%;
  background: linear-gradient(90deg, #4CAF50, #45a049);
  transition: width 0.3s ease;
}

.status-badge {
  padding: 4px 12px;
  border-radius: 20px;
  font-size: 0.8rem;
  font-weight: 600;
}

.status-badge.draft {
  background: #f8f9fa;
  color: #6c757d;
}

.status-badge.published {
  background: #d4edda;
  color: #155724;
}

.status-badge.closed {
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

.action-btn.grade {
  background: #e8f5e8;
  color: #4caf50;
}

.action-btn.grade:hover {
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
  max-width: 600px;
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

.form-group input,
.form-group textarea,
.form-group select {
  width: 100%;
  padding: 12px;
  border: 1px solid #dee2e6;
  border-radius: 8px;
  font-size: 0.9rem;
}

.form-group textarea {
  resize: vertical;
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
  
  .assignments-table {
    font-size: 0.8rem;
  }
  
  .assignments-table th,
  .assignments-table td {
    padding: 10px;
  }
  
  .action-buttons {
    flex-direction: column;
  }
}
</style>
