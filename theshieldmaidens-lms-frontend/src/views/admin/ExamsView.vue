<template>
  <div class="admin-exams">
    <!-- Header -->
    <div class="exams-header">
      <div class="header-content">
        <div class="header-left">
          <h1><i class="fas fa-clipboard-list"></i> Exams</h1>
          <p>Manage course exams and monitor student performance</p>
        </div>
        <div class="header-actions">
          <button @click="showAddExamModal = true" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add Exam
          </button>
        </div>
      </div>
    </div>

    <!-- Stats Overview -->
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-icon total">
          <i class="fas fa-file-alt"></i>
        </div>
        <div class="stat-content">
          <h3>{{ totalExams }}</h3>
          <p>Total Exams</p>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon ongoing">
          <i class="fas fa-play-circle"></i>
        </div>
        <div class="stat-content">
          <h3>{{ ongoingExams }}</h3>
          <p>Ongoing</p>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon completed">
          <i class="fas fa-check-circle"></i>
        </div>
        <div class="stat-content">
          <h3>{{ completedExams }}</h3>
          <p>Completed</p>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon scheduled">
          <i class="fas fa-calendar-alt"></i>
        </div>
        <div class="stat-content">
          <h3>{{ scheduledExams }}</h3>
          <p>Scheduled</p>
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
            placeholder="Search exams..."
            @input="filterExams"
          >
        </div>
        <select v-model="statusFilter" @change="filterExams" class="filter-select">
          <option value="">All Status</option>
          <option value="draft">Draft</option>
          <option value="scheduled">Scheduled</option>
          <option value="ongoing">Ongoing</option>
          <option value="completed">Completed</option>
        </select>
        <select v-model="courseFilter" @change="filterExams" class="filter-select">
          <option value="">All Courses</option>
          <option v-for="course in courses" :key="course.id" :value="course.id">
            {{ course.title }}
          </option>
        </select>
      </div>
      <div class="filters-right">
        <button @click="refreshExams" class="btn btn-secondary" :disabled="refreshing">
          <i :class="refreshing ? 'fas fa-spinner fa-spin' : 'fas fa-sync-alt'"></i>
          Refresh
        </button>
      </div>
    </div>

    <!-- Exams Table -->
    <div class="exams-table-container">
      <table class="exams-table">
        <thead>
          <tr>
            <th>Exam</th>
            <th>Course</th>
            <th>Start Date</th>
            <th>Duration</th>
            <th>Submissions</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="exam in filteredExams" :key="exam.id">
            <td>
              <div class="exam-info">
                <div class="exam-title">{{ exam.title }}</div>
                <div class="exam-description">{{ exam.description }}</div>
              </div>
            </td>
            <td>
              <div class="course-info">
                <div class="course-title">{{ exam.course_title }}</div>
                <div class="course-facilitator">{{ exam.facilitator_name }}</div>
              </div>
            </td>
            <td>
              <div class="start-date">
                <i class="fas fa-calendar"></i>
                {{ formatDate(exam.start_date) }}
              </div>
            </td>
            <td>
              <div class="duration">
                <i class="fas fa-clock"></i>
                {{ exam.duration_minutes }} min
              </div>
            </td>
            <td>
              <div class="submission-stats">
                <span class="submitted">{{ exam.submitted_count }}</span>
                <span class="separator">/</span>
                <span class="total">{{ exam.total_students }}</span>
                <div class="progress-bar">
                  <div class="progress-fill" :style="{ width: getSubmissionProgress(exam) + '%' }"></div>
                </div>
              </div>
            </td>
            <td>
              <span class="status-badge" :class="exam.status">
                {{ exam.status }}
              </span>
            </td>
            <td>
              <div class="action-buttons">
                <button @click="viewExam(exam)" class="action-btn view" title="View Exam">
                  <i class="fas fa-eye"></i>
                </button>
                <button @click="editExam(exam)" class="action-btn edit" title="Edit Exam">
                  <i class="fas fa-edit"></i>
                </button>
                <button @click="gradeExam(exam)" class="action-btn grade" title="Grade Exam">
                  <i class="fas fa-graduation-cap"></i>
                </button>
                <button @click="deleteExam(exam)" class="action-btn delete" title="Delete Exam">
                  <i class="fas fa-trash"></i>
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
      
      <!-- Empty State -->
      <div v-if="filteredExams.length === 0" class="empty-state">
        <i class="fas fa-clipboard-list"></i>
        <h3>No exams found</h3>
        <p>Try adjusting your search or filters</p>
      </div>
    </div>

    <!-- Add Exam Modal -->
    <div v-if="showAddExamModal" class="modal-overlay" @click="showAddExamModal = false">
      <div class="modal" @click.stop>
        <div class="modal-header">
          <h3>Add New Exam</h3>
          <button @click="showAddExamModal = false" class="btn-close">×</button>
        </div>
        <div class="modal-body">
          <form @submit.prevent="addExam">
            <div class="form-group">
              <label>Exam Title</label>
              <input v-model="examForm.title" type="text" required>
            </div>
            <div class="form-group">
              <label>Description</label>
              <textarea v-model="examForm.description" rows="3" required></textarea>
            </div>
            <div class="form-group">
              <label>Course</label>
              <select v-model="examForm.course_id" required>
                <option value="">Select Course</option>
                <option v-for="course in courses" :key="course.id" :value="course.id">
                  {{ course.title }}
                </option>
              </select>
            </div>
            <div class="form-group">
              <label>Start Date</label>
              <input v-model="examForm.start_date" type="datetime-local" required>
            </div>
            <div class="form-group">
              <label>Duration (minutes)</label>
              <input v-model="examForm.duration_minutes" type="number" min="1" required>
            </div>
            <div class="form-group">
              <label>Max Points</label>
              <input v-model="examForm.max_points" type="number" min="1" value="100" required>
            </div>
            <div class="form-group">
              <label>Passing Score (%)</label>
              <input v-model="examForm.passing_score" type="number" min="0" max="100" value="60" required>
            </div>
            <div class="form-actions">
              <button type="submit" class="btn btn-primary">Add Exam</button>
              <button type="button" @click="showAddExamModal = false" class="btn btn-secondary">Cancel</button>
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
const exams = ref([])
const courses = ref([])
const searchQuery = ref('')
const statusFilter = ref('')
const courseFilter = ref('')
const refreshing = ref(false)
const showAddExamModal = ref(false)
const message = ref('')
const messageType = ref('')

// Form
const examForm = ref({
  title: '',
  description: '',
  course_id: '',
  start_date: '',
  duration_minutes: 60,
  max_points: 100,
  passing_score: 60
})

// Computed
const filteredExams = computed(() => {
  let filtered = exams.value

  // Search filter
  if (searchQuery.value) {
    filtered = filtered.filter(exam => 
      exam.title.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      exam.description.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      exam.course_title.toLowerCase().includes(searchQuery.value.toLowerCase())
    )
  }

  // Status filter
  if (statusFilter.value) {
    filtered = filtered.filter(exam => exam.status === statusFilter.value)
  }

  // Course filter
  if (courseFilter.value) {
    filtered = filtered.filter(exam => exam.course_id == courseFilter.value)
  }

  return filtered
})

// Stats
const totalExams = computed(() => exams.value.length)
const ongoingExams = computed(() => {
  const now = new Date()
  return exams.value.filter(exam => 
    exam.status === 'ongoing' || 
    (exam.status === 'scheduled' && new Date(exam.start_date) <= now && new Date(exam.end_date) >= now)
  ).length
})
const completedExams = computed(() => {
  return exams.value.filter(exam => exam.status === 'completed').length
})
const scheduledExams = computed(() => {
  return exams.value.filter(exam => exam.status === 'scheduled').length
})

// Methods
const fetchExams = async () => {
  try {
    const response = await fetch('http://127.0.0.1:8000/api/admin/exams', {
      headers: {
        'Authorization': `Bearer ${authStore.token}`,
        'Accept': 'application/json'
      }
    })
    const data = await response.json()
    exams.value = data.data || []
  } catch (error) {
    console.error('Error fetching exams:', error)
    showMessage('Error fetching exams', 'error')
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

const filterExams = () => {
  // Filtering is handled by computed property
}

const refreshExams = async () => {
  refreshing.value = true
  await fetchExams()
  refreshing.value = false
  showMessage('Exams refreshed successfully', 'success')
}

const viewExam = (exam) => {
  console.log('View exam:', exam)
  // TODO: Open exam details modal
}

const editExam = (exam) => {
  console.log('Edit exam:', exam)
  // TODO: Open edit modal
}

const gradeExam = (exam) => {
  console.log('Grade exam:', exam)
  // TODO: Navigate to grading interface
}

const deleteExam = async (exam) => {
  if (!confirm(`Are you sure you want to delete "${exam.title}"?`)) return
  
  try {
    const response = await fetch(`http://127.0.0.1:8000/api/admin/exams/${exam.id}`, {
      method: 'DELETE',
      headers: {
        'Authorization': `Bearer ${authStore.token}`,
        'Accept': 'application/json'
      }
    })
    
    if (response.ok) {
      exams.value = exams.value.filter(e => e.id !== exam.id)
      showMessage('Exam deleted successfully', 'success')
    }
  } catch (error) {
    console.error('Error deleting exam:', error)
    showMessage('Error deleting exam', 'error')
  }
}

const addExam = async () => {
  try {
    const response = await fetch('http://127.0.0.1:8000/api/admin/exams', {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${authStore.token}`,
        'Accept': 'application/json',
        'Content-Type': 'application/json'
      },
      body: JSON.stringify(examForm.value)
    })
    
    if (response.ok) {
      await fetchExams()
      showAddExamModal.value = false
      examForm.value = {
        title: '',
        description: '',
        course_id: '',
        start_date: '',
        duration_minutes: 60,
        max_points: 100,
        passing_score: 60
      }
      showMessage('Exam added successfully', 'success')
    }
  } catch (error) {
    console.error('Error adding exam:', error)
    showMessage('Error adding exam', 'error')
  }
}

const getSubmissionProgress = (exam) => {
  return exam.total_students > 0 
    ? (exam.submitted_count / exam.total_students) * 100
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
  fetchExams()
  fetchCourses()
})
</script>

<style scoped>
.admin-exams {
  padding: 0;
}

/* Header */
.exams-header {
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

.stat-icon.ongoing {
  background: linear-gradient(135deg, #4CAF50 0%, #45a049 100%);
}

.stat-icon.completed {
  background: linear-gradient(135deg, #2196F3 0%, #1976d2 100%);
}

.stat-icon.scheduled {
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
.exams-table-container {
  background: white;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.exams-table {
  width: 100%;
  border-collapse: collapse;
}

.exams-table th {
  background: #f8f9fa;
  padding: 15px;
  text-align: left;
  font-weight: 600;
  color: #495057;
  border-bottom: 2px solid #dee2e6;
}

.exams-table td {
  padding: 15px;
  border-bottom: 1px solid #f1f3f5;
}

.exam-title {
  font-weight: 600;
  color: #333;
}

.exam-description {
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

.start-date,
.duration {
  display: flex;
  align-items: center;
  gap: 8px;
  color: #666;
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

.status-badge.scheduled {
  background: #fff3cd;
  color: #856404;
}

.status-badge.ongoing {
  background: #d4edda;
  color: #155724;
}

.status-badge.completed {
  background: #cce5ff;
  color: #004085;
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
  
  .exams-table {
    font-size: 0.8rem;
  }
  
  .exams-table th,
  .exams-table td {
    padding: 10px;
  }
  
  .action-buttons {
    flex-direction: column;
  }
}
</style>
