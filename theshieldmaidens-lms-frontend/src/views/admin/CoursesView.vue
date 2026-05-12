<template>
  <div class="admin-courses">
    <!-- Header -->
    <div class="courses-header">
      <div class="header-content">
        <div class="header-left">
          <h1><i class="fas fa-graduation-cap"></i> Course Management</h1>
          <p>Upload, edit, and manage all course content</p>
        </div>
        <div class="header-actions">
          <button @click="showAddCourseModal = true" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add New Course
          </button>
          <button @click="uploadCourse" class="btn btn-secondary">
            <i class="fas fa-upload"></i> Upload Course
          </button>
        </div>
      </div>
    </div>

    <!-- Quick Stats -->
    <div class="quick-stats">
      <div class="stat-card">
        <div class="stat-icon">
          <i class="fas fa-book"></i>
        </div>
        <div class="stat-info">
          <div class="stat-number">{{ totalCourses }}</div>
          <div class="stat-label">Total Courses</div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon">
          <i class="fas fa-eye"></i>
        </div>
        <div class="stat-info">
          <div class="stat-number">{{ publishedCourses }}</div>
          <div class="stat-label">Published</div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon">
          <i class="fas fa-edit"></i>
        </div>
        <div class="stat-info">
          <div class="stat-number">{{ draftCourses }}</div>
          <div class="stat-label">Draft</div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon">
          <i class="fas fa-archive"></i>
        </div>
        <div class="stat-info">
          <div class="stat-number">{{ archivedCourses }}</div>
          <div class="stat-label">Archived</div>
        </div>
      </div>
    </div>

    <!-- Search and Filter -->
    <div class="courses-filters">
      <div class="search-bar">
        <i class="fas fa-search"></i>
        <input 
          v-model="searchQuery" 
          type="text" 
          placeholder="Search courses by title or description..."
          class="search-input"
        >
      </div>
      <select v-model="selectedCategory" class="filter-select">
        <option value="">All Categories</option>
        <option value="digital-safety">Digital Safety & Online Security</option>
        <option value="tfgbv-awareness">TFGBV Awareness</option>
        <option value="safe-internet">Safe Internet Practices</option>
        <option value="password-privacy">Password & Privacy Management</option>
        <option value="stem-technology">STEM & Technology Skills</option>
        <option value="coding-youth">Coding for Kids and Youth</option>
        <option value="it-literacy">Basic IT Literacy</option>
        <option value="cybersecurity">Cybersecurity Fundamentals</option>
        <option value="life-skills">Life Skills & Empowerment</option>
        <option value="confidence">Confidence Building</option>
        <option value="communication">Communication Skills</option>
        <option value="leadership">Leadership for Youth</option>
        <option value="climate">Climate Awareness</option>
        <option value="civic">Civic Education</option>
        <option value="volunteer">Community Volunteer Programs</option>
      </select>
      <select v-model="statusFilter" class="filter-select">
        <option value="">All Status</option>
        <option value="draft">Draft</option>
        <option value="published">Published</option>
        <option value="archived">Archived</option>
      </select>
      <button @click="refreshCourses" class="btn btn-refresh">
        <i class="fas fa-sync-alt" :class="{ 'fa-spin': refreshing }"></i> Refresh
      </button>
    </div>

    <!-- Courses Grid -->
    <div class="courses-grid">
      <div 
        v-for="course in filteredCourses" 
        :key="course.id" 
        class="course-card"
      >
        <div class="course-image">
          <img :src="course.image || '/placeholder-course.jpg'" :alt="course.title">
          <div class="course-status" :class="course.status">
            {{ course.status }}
          </div>
          <div class="course-actions-overlay">
            <button @click="viewCourse(course)" class="action-btn view-btn">
              <i class="fas fa-eye"></i>
            </button>
            <button @click="editCourse(course)" class="action-btn edit-btn">
              <i class="fas fa-edit"></i>
            </button>
            <button @click="duplicateCourse(course)" class="action-btn duplicate-btn">
              <i class="fas fa-copy"></i>
            </button>
            <button @click="deleteCourse(course)" class="action-btn delete-btn">
              <i class="fas fa-trash"></i>
            </button>
          </div>
        </div>
        <div class="course-content">
          <h3>{{ course.title }}</h3>
          <p class="course-description">{{ course.description }}</p>
          <div class="course-meta">
            <span class="category">{{ getCategoryName(course.category) }}</span>
            <span class="duration">{{ course.duration }}</span>
            <span class="status-badge">{{ course.status }}</span>
          </div>
          <div class="course-management">
            <div class="course-actions">
              <button @click="editCourse(course)" class="btn btn-sm btn-primary">
                <i class="fas fa-edit"></i> Edit
              </button>
              <button @click="manageContent(course)" class="btn btn-sm btn-secondary">
                <i class="fas fa-folder-open"></i> Content
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Add/Edit Course Modal -->
    <div v-if="showAddCourseModal || editingCourse" class="modal-overlay">
      <div class="modal-content">
        <div class="modal-header">
          <h2>{{ editingCourse ? 'Edit Course' : 'Add New Course' }}</h2>
          <button @click="closeModal" class="close-btn">&times;</button>
        </div>
        
        <form @submit.prevent="saveCourse" class="course-form">
          <div class="form-group">
            <label>Course Title</label>
            <input v-model="courseForm.title" type="text" required class="form-control">
          </div>
          
          <div class="form-group">
            <label>Description</label>
            <textarea v-model="courseForm.description" rows="3" class="form-control"></textarea>
          </div>
          
          <div class="form-group">
            <label>Category</label>
            <select v-model="courseForm.category" class="form-control">
              <option value="">Select Category</option>
              <option value="digital-safety">Digital Safety & Online Security</option>
              <option value="tfgbv-awareness">TFGBV Awareness</option>
              <option value="safe-internet">Safe Internet Practices</option>
              <option value="password-privacy">Password & Privacy Management</option>
              <option value="stem-technology">STEM & Technology Skills</option>
              <option value="coding-youth">Coding for Kids and Youth</option>
              <option value="it-literacy">Basic IT Literacy</option>
              <option value="cybersecurity">Cybersecurity Fundamentals</option>
              <option value="life-skills">Life Skills & Empowerment</option>
              <option value="confidence">Confidence Building</option>
              <option value="communication">Communication Skills</option>
              <option value="leadership">Leadership for Youth</option>
              <option value="climate">Climate Awareness</option>
              <option value="civic">Civic Education</option>
              <option value="volunteer">Community Volunteer Programs</option>
            </select>
          </div>
          
          <div class="form-group">
            <label>Duration</label>
            <input v-model="courseForm.duration" type="text" placeholder="e.g., 4 weeks" class="form-control">
          </div>

          <div class="form-group">
            <label>Difficulty</label>
            <select v-model="courseForm.difficulty_level" class="form-control">
              <option value="beginner">Beginner</option>
              <option value="intermediate">Intermediate</option>
              <option value="advanced">Advanced</option>
            </select>
          </div>
          
          <div class="form-row">
            <div class="form-group">
              <label>Modules</label>
              <input v-model.number="courseForm.modules" type="number" min="0" class="form-control">
            </div>
            <div class="form-group">
              <label>Status</label>
              <select v-model="courseForm.status" class="form-control">
                <option value="draft">Draft</option>
                <option value="published">Published</option>
                <option value="archived">Archived</option>
              </select>
            </div>
          </div>
          
          <div class="form-group">
            <label>Course Image URL</label>
            <input v-model="courseForm.image" type="url" placeholder="https://..." class="form-control">
          </div>
          
          <div class="form-actions">
            <button type="button" @click="closeModal" class="btn btn-secondary">Cancel</button>
            <button type="submit" class="btn btn-primary">
              {{ editingCourse ? 'Update Course' : 'Create Course' }}
            </button>
          </div>
        </form>
      </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { API_BASE_URL } from '@/config/api'

const router = useRouter()
const authStore = useAuthStore()
const showAddCourseModal = ref(false)
const editingCourse = ref(null)
const searchQuery = ref('')
const selectedCategory = ref('')
const statusFilter = ref('')
const refreshing = ref(false)

// Course data - will be fetched from API
const courses = ref([])
const refreshInterval = ref(null)

const courseForm = ref({
  title: '',
  description: '',
  category: '',
  duration: '',
  difficulty_level: 'beginner',
  status: 'draft',
  image: '',
  modules: 0,
  videos: 0,
  assignments: 0
})

// Computed properties
const filteredCourses = computed(() => {
  return courses.value.filter(course => {
    const matchesSearch = course.title.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
                         course.description.toLowerCase().includes(searchQuery.value.toLowerCase())
    const matchesCategory = !selectedCategory.value || course.category === selectedCategory.value
    const matchesStatus = !statusFilter.value || course.status === statusFilter.value
    
    return matchesSearch && matchesCategory && matchesStatus
  })
})

const totalCourses = computed(() => courses.value.length)
const publishedCourses = computed(() => courses.value.filter(c => c.status === 'published').length)
const draftCourses = computed(() => courses.value.filter(c => c.status === 'draft').length)
const archivedCourses = computed(() => courses.value.filter(c => c.status === 'archived').length)

// Methods
const getCategoryName = (category) => {
  const categories = {
    'digital-safety': 'Digital Safety',
    'tfgbv-awareness': 'TFGBV Awareness',
    'safe-internet': 'Safe Internet',
    'password-privacy': 'Password & Privacy',
    'stem-technology': 'STEM & Tech',
    'coding-youth': 'Coding for Youth',
    'it-literacy': 'IT Literacy',
    'cybersecurity': 'Cybersecurity',
    'life-skills': 'Life Skills',
    'confidence': 'Confidence Building',
    'communication': 'Communication',
    'leadership': 'Leadership',
    'climate': 'Climate Awareness',
    'civic': 'Civic Education',
    'volunteer': 'Community Volunteer'
  }
  return categories[category] || category
}

const viewCourse = (course) => {
  router.push(`/admin/courses/${course.id}`)
}

const editCourse = (course) => {
  editingCourse.value = course
  courseForm.value = { ...course }
}

const duplicateCourse = (course) => {
  const duplicatedCourse = {
    ...course,
    id: Date.now(),
    title: `${course.title} (Copy)`,
    status: 'draft'
  }
  courses.value.push(duplicatedCourse)
}

const deleteCourse = (course) => {
  if (confirm(`Are you sure you want to delete "${course.title}"? This action cannot be undone.`)) {
    courses.value = courses.value.filter(c => c.id !== course.id)
  }
}

const manageContent = (course) => {
  router.push(`/admin/courses/${course.id}/content`)
}

const uploadCourse = () => {
  // Open file upload dialog or navigate to upload page
  console.log('Upload course functionality')
  // Could implement file upload for course materials
}

const loadCourses = async () => {
  if (!authStore.token) {
    courses.value = []
    return
  }
  try {
    const response = await fetch(`${API_BASE_URL}/admin/courses`, {
      headers: {
        Accept: 'application/json',
        Authorization: `Bearer ${authStore.token}`,
      },
    })
    if (response.ok) {
      const data = await response.json()
      courses.value = Array.isArray(data.courses) ? data.courses : []
    } else {
      courses.value = []
    }
  } catch (error) {
    console.error('Error loading courses:', error)
    courses.value = []
  }
}

const refreshCourses = async () => {
  refreshing.value = true
  await loadCourses()
  refreshing.value = false
}

const saveCourse = async () => {
  if (!authStore.token) {
    alert('You must be logged in as admin to save courses.')
    return
  }
  const payload = {
    title: courseForm.value.title,
    description: courseForm.value.description || '',
    category: courseForm.value.category || 'general',
    duration: courseForm.value.duration || null,
    modules_count: Number(courseForm.value.modules) || 0,
    thumbnail: courseForm.value.image || null,
    status: courseForm.value.status || 'draft',
    difficulty_level: courseForm.value.difficulty_level || 'beginner',
    visibility: 'public',
    price: 0,
    max_students: 9999,
  }
  try {
    if (editingCourse.value) {
      const res = await fetch(
        `${API_BASE_URL}/admin/courses/${editingCourse.value.id}`,
        {
          method: 'PATCH',
          headers: {
            'Content-Type': 'application/json',
            Accept: 'application/json',
            Authorization: `Bearer ${authStore.token}`,
          },
          body: JSON.stringify(payload),
        },
      )
      if (!res.ok) {
        const err = await res.json().catch(() => ({}))
        alert(err.message || 'Update failed')
        return
      }
    } else {
      const res = await fetch(`${API_BASE_URL}/admin/courses`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          Accept: 'application/json',
          Authorization: `Bearer ${authStore.token}`,
        },
        body: JSON.stringify(payload),
      })
      if (!res.ok) {
        const err = await res.json().catch(() => ({}))
        alert(err.message || 'Create failed')
        return
      }
    }
    await loadCourses()
    closeModal()
  } catch (e) {
    console.error(e)
    alert('Network error saving course')
  }
}

const closeModal = () => {
  showAddCourseModal.value = false
  editingCourse.value = null
  courseForm.value = {
    title: '',
    description: '',
    category: '',
    duration: '',
    difficulty_level: 'beginner',
    status: 'draft',
    image: '',
    modules: 0,
    videos: 0,
    assignments: 0
  }
}

onMounted(() => {
  loadCourses()
  
  // Set up real-time refresh every 30 seconds
  refreshInterval.value = setInterval(() => {
    loadCourses()
  }, 30000)
})

onUnmounted(() => {
  if (refreshInterval.value) {
    clearInterval(refreshInterval.value)
  }
})
</script>

<style scoped>
.admin-courses {
  min-height: 100vh;
  background: #f7f7f7;
  color: #333;
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

/* Header */
.courses-header {
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

/* Quick Stats */
.quick-stats {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1rem;
  padding: 2rem;
  max-width: 1400px;
  margin: 0 auto;
}

.stat-card {
  background: #fff;
  border-radius: 8px;
  padding: 1.5rem;
  border-left: 4px solid #ff9900;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  display: flex;
  align-items: center;
  gap: 1rem;
  transition: all 0.3s ease;
}

.stat-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
}

.stat-icon {
  width: 48px;
  height: 48px;
  background: rgba(255, 153, 0, 0.1);
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #ff9900;
  font-size: 1.25rem;
}

.stat-info {
  flex: 1;
}

.stat-number {
  font-size: 1.5rem;
  font-weight: 700;
  color: #333;
  margin-bottom: 0.25rem;
}

.stat-label {
  color: #666;
  font-size: 0.875rem;
  font-weight: 600;
}

/* Filters */
.courses-filters {
  display: flex;
  gap: 1rem;
  margin-bottom: 2rem;
  padding: 0 2rem;
  flex-wrap: wrap;
  align-items: center;
}

.search-bar {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  background: #fff;
  border: 1px solid #ddd;
  border-radius: 6px;
  padding: 0.75rem 1rem;
  flex: 1;
  min-width: 300px;
}

.search-bar i {
  color: #ff9900;
}

.search-input {
  border: none;
  outline: none;
  flex: 1;
  font-size: 0.875rem;
  background: transparent;
}

.filter-select {
  padding: 0.75rem 1rem;
  border: 1px solid #ddd;
  border-radius: 6px;
  background: #fff;
  font-size: 0.875rem;
  color: #333;
  min-width: 150px;
}

.btn-refresh {
  background: #333;
  color: #fff;
  border: none;
  padding: 0.75rem 1rem;
  border-radius: 6px;
  cursor: pointer;
  font-size: 0.875rem;
  font-weight: 600;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  transition: all 0.2s;
}

.btn-refresh:hover {
  background: #555;
}

/* Courses Grid */
.courses-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
  gap: 1.5rem;
  padding: 0 2rem 2rem;
  max-width: 1400px;
  margin: 0 auto;
}

.course-card {
  background: #fff;
  border-radius: 8px;
  overflow: hidden;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  transition: all 0.3s ease;
  position: relative;
}

.course-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15);
}

.course-image {
  position: relative;
  height: 200px;
  overflow: hidden;
}

.course-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.3s ease;
}

.course-card:hover .course-image img {
  transform: scale(1.05);
}

.course-status {
  position: absolute;
  top: 10px;
  left: 10px;
  padding: 0.25rem 0.75rem;
  border-radius: 20px;
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
  z-index: 5;
}

.course-status.published {
  background: #ff9900;
  color: white;
}

.course-status.draft {
  background: #333;
  color: white;
}

.course-status.archived {
  background: #666;
  color: white;
}

.course-actions-overlay {
  position: absolute;
  top: 10px;
  right: 10px;
  display: flex;
  gap: 0.5rem;
  opacity: 0;
  transition: opacity 0.3s ease;
  z-index: 10;
}

.course-card:hover .course-actions-overlay {
  opacity: 1;
}

.action-btn {
  width: 32px;
  height: 32px;
  border: none;
  border-radius: 50%;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.75rem;
  transition: all 0.2s;
  backdrop-filter: blur(4px);
}

.view-btn {
  background: rgba(255, 153, 0, 0.9);
  color: white;
}

.edit-btn {
  background: rgba(51, 51, 51, 0.9);
  color: white;
}

.duplicate-btn {
  background: rgba(0, 128, 0, 0.9);
  color: white;
}

.delete-btn {
  background: rgba(204, 0, 0, 0.9);
  color: white;
}

.action-btn:hover {
  transform: scale(1.1);
}

.course-content {
  padding: 1.5rem;
}

.course-content h3 {
  margin: 0 0 0.5rem 0;
  color: #333;
  font-size: 1.25rem;
  font-weight: 700;
}

.course-description {
  color: #666;
  margin-bottom: 1rem;
  line-height: 1.5;
  font-size: 0.875rem;
}

.course-meta {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
  margin-bottom: 1rem;
}

.category, .duration, .status-badge {
  background: #f9f9f9;
  padding: 0.25rem 0.5rem;
  border-radius: 4px;
  font-size: 0.75rem;
  color: #666;
  font-weight: 600;
}

.course-management {
  border-top: 1px solid #f0f0f0;
  padding-top: 1rem;
}

.course-stats {
  display: flex;
  gap: 1rem;
  margin-bottom: 1rem;
  flex-wrap: wrap;
}

.course-stats span {
  display: flex;
  align-items: center;
  gap: 0.25rem;
  font-size: 0.75rem;
  color: #666;
}

.course-stats i {
  color: #ff9900;
}

.course-actions {
  display: flex;
  gap: 0.5rem;
}

/* Buttons */
.btn {
  padding: 0.5rem 1rem;
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

.btn-sm {
  padding: 0.375rem 0.75rem;
  font-size: 0.8rem;
}

/* Modal Styles */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.8);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 9999;
}

.modal-content {
  background: #fff;
  border-radius: 8px;
  width: 90%;
  max-width: 600px;
  max-height: 90vh;
  overflow-y: auto;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.5rem;
  border-bottom: 2px solid #ff9900;
}

.modal-header h2 {
  color: #333;
  font-size: 1.25rem;
  font-weight: 700;
}

.close-btn {
  background: none;
  border: none;
  font-size: 1.5rem;
  cursor: pointer;
  color: #666;
  padding: 0.5rem;
  border-radius: 4px;
  transition: all 0.2s;
}

.close-btn:hover {
  background: #f0f0f0;
}

.course-form {
  padding: 1.5rem;
}

.form-group {
  margin-bottom: 1rem;
}

.form-group label {
  display: block;
  margin-bottom: 0.5rem;
  font-weight: 600;
  color: #333;
  font-size: 0.875rem;
}

.form-control {
  width: 100%;
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

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
}

.form-actions {
  display: flex;
  gap: 1rem;
  justify-content: flex-end;
  margin-top: 1.5rem;
  padding-top: 1rem;
  border-top: 1px solid #f0f0f0;
}

/* Responsive Design - Mobile First */
@media (max-width: 767px) {
  .admin-courses {
    padding: 0;
  }
  
  .courses-header {
    padding: 1rem;
  }
  
  .header-content {
    flex-direction: column;
    gap: 1rem;
    text-align: center;
  }
  
  .header-left h1 {
    font-size: 1.5rem;
  }
  
  .header-left p {
    font-size: 0.875rem;
  }
  
  .header-actions {
    flex-direction: column;
    width: 100%;
    gap: 0.75rem;
  }
  
  .header-actions .btn {
    width: 100%;
    justify-content: center;
  }
  
  .quick-stats {
    grid-template-columns: 1fr;
    gap: 0.75rem;
    padding: 1rem;
  }
  
  .stat-card {
    padding: 1rem;
    flex-direction: row;
    text-align: left;
  }
  
  .stat-icon {
    width: 40px;
    height: 40px;
    font-size: 1rem;
  }
  
  .stat-number {
    font-size: 1.25rem;
  }
  
  .stat-label {
    font-size: 0.8rem;
  }
  
  .courses-filters {
    flex-direction: column;
    padding: 0 1rem;
    gap: 0.75rem;
  }
  
  .search-bar {
    min-width: auto;
    order: -1;
  }
  
  .search-input {
    padding: 0.625rem 2.5rem 0.625rem 0.75rem;
    font-size: 0.875rem;
  }
  
  .filter-select {
    padding: 0.625rem;
    font-size: 0.875rem;
    min-width: auto;
  }
  
  .btn-refresh {
    padding: 0.625rem 0.75rem;
    font-size: 0.8rem;
  }
  
  .courses-grid {
    grid-template-columns: 1fr;
    padding: 0 1rem 1rem;
    gap: 1rem;
  }
  
  .course-card {
    border-radius: 8px;
  }
  
  .course-image {
    height: 160px;
  }
  
  .course-content {
    padding: 1rem;
  }
  
  .course-content h3 {
    font-size: 1.125rem;
  }
  
  .course-description {
    font-size: 0.8rem;
  }
  
  .course-meta {
    gap: 0.5rem;
    margin-bottom: 0.75rem;
  }
  
  .category, .duration, .status-badge {
    font-size: 0.7rem;
    padding: 0.2rem 0.4rem;
  }
  
  .course-management {
    padding-top: 0.75rem;
  }
  
  .course-actions {
    gap: 0.5rem;
  }
  
  .btn-sm {
    padding: 0.375rem 0.625rem;
    font-size: 0.75rem;
  }
  
  .modal-overlay {
    padding: 1rem;
  }
  
  .modal-content {
    width: 95%;
    max-height: 95vh;
    border-radius: 8px;
  }
  
  .modal-header {
    padding: 1rem;
  }
  
  .modal-header h2 {
    font-size: 1.25rem;
  }
  
  .course-form {
    padding: 1rem;
  }
  
  .form-group {
    margin-bottom: 0.75rem;
  }
  
  .form-group label {
    font-size: 0.875rem;
    margin-bottom: 0.375rem;
  }
  
  .form-control {
    padding: 0.625rem;
    font-size: 0.875rem;
  }
  
  .form-row {
    grid-template-columns: 1fr;
    gap: 0.75rem;
  }
  
  .form-actions {
    flex-direction: column;
    gap: 0.75rem;
  }
  
  .form-actions .btn {
    width: 100%;
  }
}

@media (max-width: 480px) {
  .courses-header {
    padding: 0.75rem;
  }
  
  .header-left h1 {
    font-size: 1.25rem;
  }
  
  .header-left p {
    font-size: 0.8rem;
  }
  
  .quick-stats {
    padding: 0.75rem;
  }
  
  .stat-card {
    padding: 0.75rem;
  }
  
  .stat-icon {
    width: 36px;
    height: 36px;
    font-size: 0.875rem;
  }
  
  .stat-number {
    font-size: 1.125rem;
  }
  
  .stat-label {
    font-size: 0.75rem;
  }
  
  .courses-filters {
    padding: 0 0.75rem;
  }
  
  .courses-grid {
    padding: 0 0.75rem 0.75rem;
    gap: 0.75rem;
  }
  
  .course-image {
    height: 140px;
  }
  
  .course-content {
    padding: 0.75rem;
  }
  
  .course-content h3 {
    font-size: 1rem;
  }
  
  .course-description {
    font-size: 0.75rem;
  }
  
  .modal-content {
    width: 98%;
  }
  
  .course-form {
    padding: 0.75rem;
  }
}

/* Landscape orientation for mobile */
@media (max-width: 767px) and (orientation: landscape) {
  .quick-stats {
    grid-template-columns: repeat(2, 1fr);
    gap: 0.5rem;
  }
  
  .stat-card {
    padding: 0.75rem;
  }
  
  .courses-filters {
    flex-direction: row;
    flex-wrap: wrap;
    gap: 0.5rem;
  }
  
  .search-bar {
    flex: 1;
    min-width: 200px;
  }
}

/* Tablet styles */
@media (min-width: 768px) and (max-width: 1023px) {
  .courses-header {
    padding: 1.5rem;
  }
  
  .header-content {
    gap: 1.5rem;
  }
  
  .header-left h1 {
    font-size: 1.75rem;
  }
  
  .quick-stats {
    grid-template-columns: repeat(2, 1fr);
    padding: 1.5rem;
  }
  
  .courses-filters {
    padding: 0 1.5rem;
  }
  
  .courses-grid {
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    padding: 0 1.5rem 1.5rem;
    gap: 1.25rem;
  }
  
  .modal-content {
    width: 90%;
    max-width: 600px;
  }
}

@media (min-width: 1024px) and (max-width: 1199px) {
  .courses-grid {
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
  }
}

/* Touch-friendly interactions for mobile */
@media (hover: none) and (pointer: coarse) {
  .course-card:hover {
    transform: none;
  }
  
  .course-card:active {
    transform: scale(0.98);
  }
  
  .btn:hover {
    transform: none;
  }
  
  .btn:active {
    transform: scale(0.95);
  }
  
  .stat-card:hover {
    transform: none;
  }
  
  .stat-card:active {
    transform: scale(0.98);
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

.course-card {
  animation: fadeIn 0.6s ease forwards;
}

/* Spin animation for refresh button */
.fa-spin {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}
</style>
