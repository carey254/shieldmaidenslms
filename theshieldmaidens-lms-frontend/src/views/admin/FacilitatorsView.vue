<template>
  <div class="admin-facilitators">
    <!-- Header -->
    <div class="facilitators-header">
      <div class="header-content">
        <div class="header-left">
          <h1><i class="fas fa-chalkboard-teacher"></i> Facilitators</h1>
          <p>Manage all facilitators and their assigned courses</p>
        </div>
        <div class="header-actions">
          <button @click="showAddFacilitatorModal = true" class="btn btn-primary">
            <i class="fas fa-user-plus"></i> Add Facilitator
          </button>
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
            placeholder="Search facilitators..."
            @input="filterFacilitators"
          >
        </div>
        <select v-model="statusFilter" @change="filterFacilitators" class="filter-select">
          <option value="">All Status</option>
          <option value="active">Active</option>
          <option value="inactive">Inactive</option>
        </select>
      </div>
      <div class="filters-right">
        <button @click="refreshFacilitators" class="btn btn-secondary" :disabled="refreshing">
          <i :class="refreshing ? 'fas fa-spinner fa-spin' : 'fas fa-sync-alt'"></i>
          Refresh
        </button>
      </div>
    </div>

    <!-- Facilitators Table -->
    <div class="facilitators-table-container">
      <!-- Loading State -->
      <div v-if="loading" class="loading-state">
        <div class="spinner"></div>
        <p>Loading facilitators...</p>
      </div>
      
      <!-- Table Content -->
      <table v-else class="facilitators-table">
        <thead>
          <tr>
            <th>Facilitator</th>
            <th>Email</th>
            <th>Assigned Courses</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="facilitator in filteredFacilitators" :key="facilitator.id">
            <td>
              <div class="facilitator-info">
                <div class="facilitator-avatar">
                  {{ facilitator.name.charAt(0).toUpperCase() }}
                </div>
                <div class="facilitator-details">
                  <div class="facilitator-name">{{ facilitator.name }}</div>
                  <div class="facilitator-id">ID: {{ facilitator.id }}</div>
                </div>
              </div>
            </td>
            <td>{{ facilitator.email }}</td>
            <td>
              <div class="assigned-courses">
                <span v-if="facilitator.courses && facilitator.courses.length > 0">
                  {{ facilitator.courses.map(c => c.title).join(', ') }}
                </span>
                <span v-else class="no-courses">No courses assigned</span>
              </div>
            </td>
            <td>
              <span class="status-badge" :class="facilitator.status">
                {{ facilitator.status || 'active' }}
              </span>
            </td>
            <td>
              <div class="action-buttons">
                <button @click="viewFacilitator(facilitator)" class="action-btn view" title="View Profile">
                  <i class="fas fa-eye"></i>
                </button>
                <button @click="editFacilitator(facilitator)" class="action-btn edit" title="Edit Facilitator">
                  <i class="fas fa-edit"></i>
                </button>
                <button @click="assignCourses(facilitator)" class="action-btn assign" title="Assign Courses">
                  <i class="fas fa-graduation-cap"></i>
                </button>
                <button @click="removeFacilitator(facilitator)" class="action-btn delete" title="Remove Facilitator">
                  <i class="fas fa-trash"></i>
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
      
      <!-- Empty State -->
      <div v-if="!loading && filteredFacilitators.length === 0" class="empty-state">
        <i class="fas fa-chalkboard-teacher"></i>
        <h3>No facilitators found</h3>
        <p v-if="searchQuery || statusFilter">Try adjusting your search or filters</p>
        <p v-else>No facilitators have been added yet. Click "Add Facilitator" to get started.</p>
      </div>
    </div>

    <!-- Add Facilitator Modal -->
    <div v-if="showAddFacilitatorModal" class="modal-overlay" @click="showAddFacilitatorModal = false">
      <div class="modal" @click.stop>
        <div class="modal-header">
          <h3>Add New Facilitator</h3>
          <button @click="showAddFacilitatorModal = false" class="btn-close">×</button>
        </div>
        <div class="modal-body">
          <form @submit.prevent="addFacilitator">
            <div class="form-group">
              <label>Name</label>
              <input v-model="newFacilitator.name" type="text" required>
            </div>
            <div class="form-group">
              <label>Email</label>
              <input v-model="newFacilitator.email" type="email" required>
            </div>
            <div class="form-group">
              <label>Password</label>
              <input v-model="newFacilitator.password" type="password" required>
            </div>
            <div class="form-group">
              <label>Specialization</label>
              <input v-model="newFacilitator.specialization" type="text" placeholder="e.g. Web Development, Data Science">
            </div>
            <div class="form-group">
              <label>Assign Courses</label>
              <select v-model="newFacilitator.course_ids" multiple size="4">
                <option v-for="course in availableCourses" :key="course.id" :value="course.id">
                  {{ course.title }}
                </option>
              </select>
              <small>Select multiple courses or leave empty to assign later</small>
            </div>
            <div class="form-actions">
              <button type="submit" class="btn btn-primary">Add Facilitator</button>
              <button type="button" @click="showAddFacilitatorModal = false" class="btn btn-secondary">Cancel</button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Assign Courses Modal -->
    <div v-if="showAssignCoursesModal" class="modal-overlay" @click="showAssignCoursesModal = false">
      <div class="modal" @click.stop>
        <div class="modal-header">
          <h3>Assign Courses to {{ selectedFacilitator?.name }}</h3>
          <button @click="showAssignCoursesModal = false" class="btn-close">×</button>
        </div>
        <div class="modal-body">
          <form @submit.prevent="saveCourseAssignments">
            <div class="form-group">
              <label>Select Courses</label>
              <div class="course-checkboxes">
                <label v-for="course in availableCourses" :key="course.id" class="course-checkbox">
                  <input 
                    type="checkbox" 
                    :value="course.id" 
                    v-model="selectedCourseIds"
                  >
                  <span class="course-info">
                    <strong>{{ course.title }}</strong>
                    <small>{{ course.category }} • {{ course.difficulty_level }}</small>
                  </span>
                </label>
              </div>
            </div>
            <div class="form-actions">
              <button type="submit" class="btn btn-primary">Save Assignments</button>
              <button type="button" @click="showAssignCoursesModal = false" class="btn btn-secondary">Cancel</button>
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
const facilitators = ref([])
const availableCourses = ref([])
const searchQuery = ref('')
const statusFilter = ref('')
const refreshing = ref(false)
const loading = ref(true)
const showAddFacilitatorModal = ref(false)
const showAssignCoursesModal = ref(false)
const selectedFacilitator = ref(null)
const selectedCourseIds = ref([])
const message = ref('')
const messageType = ref('')

// New facilitator form
const newFacilitator = ref({
  name: '',
  email: '',
  password: '',
  specialization: '',
  course_ids: []
})

// Computed
const filteredFacilitators = computed(() => {
  let filtered = facilitators.value

  // Search filter
  if (searchQuery.value) {
    filtered = filtered.filter(facilitator => 
      facilitator.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      facilitator.email.toLowerCase().includes(searchQuery.value.toLowerCase())
    )
  }

  // Status filter
  if (statusFilter.value) {
    filtered = filtered.filter(facilitator => facilitator.status === statusFilter.value)
  }

  return filtered
})

// Methods
const fetchFacilitators = async () => {
  try {
    const response = await fetch('http://127.0.0.1:8000/api/admin/facilitators', {
      headers: {
        'Authorization': `Bearer ${authStore.token}`,
        'Accept': 'application/json'
      }
    })
    
    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`)
    }
    
    const data = await response.json()
    facilitators.value = data.data || []
    console.log('Facilitators loaded:', facilitators.value.length)
  } catch (error) {
    console.error('Error fetching facilitators:', error)
    showMessage('Error loading facilitators. Please check your connection.', 'error')
  } finally {
    loading.value = false
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
    
    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`)
    }
    
    const data = await response.json()
    availableCourses.value = data.data || []
    console.log('Courses loaded:', availableCourses.value.length)
  } catch (error) {
    console.error('Error fetching courses:', error)
    showMessage('Error loading courses. Please check your connection.', 'error')
  }
}

const filterFacilitators = () => {
  // Filtering is handled by computed property
}

const refreshFacilitators = async () => {
  refreshing.value = true
  try {
    await Promise.all([fetchFacilitators(), fetchCourses()])
    showMessage('Data refreshed successfully', 'success')
  } catch (error) {
    showMessage('Error refreshing data', 'error')
  } finally {
    refreshing.value = false
  }
}

const viewFacilitator = (facilitator) => {
  console.log('View facilitator:', facilitator)
  // TODO: Open facilitator profile modal
}

const editFacilitator = (facilitator) => {
  console.log('Edit facilitator:', facilitator)
  // TODO: Open edit modal
}

const assignCourses = (facilitator) => {
  selectedFacilitator.value = facilitator
  selectedCourseIds.value = facilitator.courses ? facilitator.courses.map(c => c.id) : []
  showAssignCoursesModal.value = true
}

const saveCourseAssignments = async () => {
  try {
    const response = await fetch(`http://127.0.0.1:8000/api/admin/facilitators/${selectedFacilitator.value.id}/courses`, {
      method: 'PUT',
      headers: {
        'Authorization': `Bearer ${authStore.token}`,
        'Accept': 'application/json',
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({ course_ids: selectedCourseIds.value })
    })
    
    if (response.ok) {
      await fetchFacilitators()
      showAssignCoursesModal.value = false
      selectedFacilitator.value = null
      selectedCourseIds.value = []
      showMessage('Course assignments saved successfully', 'success')
    }
  } catch (error) {
    console.error('Error saving course assignments:', error)
    showMessage('Error saving course assignments', 'error')
  }
}

const addFacilitator = async () => {
  try {
    const response = await fetch('http://127.0.0.1:8000/api/admin/facilitators', {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${authStore.token}`,
        'Accept': 'application/json',
        'Content-Type': 'application/json'
      },
      body: JSON.stringify(newFacilitator.value)
    })
    
    if (response.ok) {
      await fetchFacilitators()
      showAddFacilitatorModal.value = false
      newFacilitator.value = { 
        name: '', 
        email: '', 
        password: '', 
        specialization: '',
        course_ids: []
      }
      showMessage('Facilitator added successfully', 'success')
    }
  } catch (error) {
    console.error('Error adding facilitator:', error)
    showMessage('Error adding facilitator', 'error')
  }
}

const removeFacilitator = async (facilitator) => {
  if (!confirm(`Are you sure you want to remove ${facilitator.name}?`)) return
  
  try {
    const response = await fetch(`http://127.0.0.1:8000/api/admin/facilitators/${facilitator.id}`, {
      method: 'DELETE',
      headers: {
        'Authorization': `Bearer ${authStore.token}`,
        'Accept': 'application/json'
      }
    })
    
    if (response.ok) {
      facilitators.value = facilitators.value.filter(f => f.id !== facilitator.id)
      showMessage('Facilitator removed successfully', 'success')
    }
  } catch (error) {
    console.error('Error removing facilitator:', error)
    showMessage('Error removing facilitator', 'error')
  }
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
  console.log('FacilitatorsView mounted')
  fetchFacilitators()
  fetchCourses()
})
</script>

<style scoped>
.admin-facilitators {
  padding: 0;
}

/* Header */
.facilitators-header {
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
.facilitators-table-container {
  background: white;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.facilitators-table {
  width: 100%;
  border-collapse: collapse;
}

.facilitators-table th {
  background: #f8f9fa;
  padding: 15px;
  text-align: left;
  font-weight: 600;
  color: #495057;
  border-bottom: 2px solid #dee2e6;
}

.facilitators-table td {
  padding: 15px;
  border-bottom: 1px solid #f1f3f5;
}

.facilitator-info {
  display: flex;
  align-items: center;
  gap: 12px;
}

.facilitator-avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: #2196F3;
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 600;
}

.facilitator-name {
  font-weight: 600;
  color: #333;
}

.facilitator-id {
  font-size: 0.8rem;
  color: #6c757d;
}

.assigned-courses {
  max-width: 300px;
}

.no-courses {
  color: #6c757d;
  font-style: italic;
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

.status-badge.inactive {
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

.action-btn.assign {
  background: #e8f5e8;
  color: #4caf50;
}

.action-btn.assign:hover {
  background: #c8e6c9;
}

.action-btn.delete {
  background: #ffebee;
  color: #f44336;
}

.action-btn.delete:hover {
  background: #ffcdd2;
}

/* Course Checkboxes */
.course-checkboxes {
  display: flex;
  flex-direction: column;
  gap: 10px;
  max-height: 300px;
  overflow-y: auto;
}

.course-checkbox {
  display: flex;
  align-items: flex-start;
  gap: 10px;
  padding: 10px;
  border: 1px solid #e9ecef;
  border-radius: 8px;
  cursor: pointer;
  transition: background 0.3s ease;
}

.course-checkbox:hover {
  background: #f8f9fa;
}

.course-info {
  flex: 1;
}

.course-info strong {
  display: block;
  margin-bottom: 5px;
}

.course-info small {
  color: #6c757d;
  font-size: 0.8rem;
}

/* Loading State */
.loading-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 60px 20px;
  color: #6c757d;
}

.spinner {
  width: 40px;
  height: 40px;
  border: 4px solid #f3f3f3;
  border-top: 4px solid #ff6b35;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin-bottom: 20px;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
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
.form-group select {
  width: 100%;
  padding: 12px;
  border: 1px solid #dee2e6;
  border-radius: 8px;
  font-size: 0.9rem;
}

.form-group select[multiple] {
  height: 120px;
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
  
  .facilitators-table {
    font-size: 0.8rem;
  }
  
  .facilitators-table th,
  .facilitators-table td {
    padding: 10px;
  }
  
  .action-buttons {
    flex-direction: column;
  }
}
</style>
