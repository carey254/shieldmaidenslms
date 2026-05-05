<template>
  <div class="courses-view">
    <div v-if="enrollNotice" class="notice-banner" role="alert">
      {{ enrollNotice }}
    </div>
    <div class="page-header">
      <button @click="goBack" class="back-btn">
        ← Back to Dashboard
      </button>
      <h1>Explore Courses</h1>
    </div>
    
    <!-- Available Courses -->
    <div class="courses-section">
      <h2>Available Courses</h2>
       <p>We offer a range of beginner to intermediate courses designed to equip learners with essential digital, cybersecurity, and personal development skills.</p>
      <div v-if="courses.length === 0" class="empty-state">
        <p>No courses available at the moment. Please check back later!</p>
      </div>
      
      <div v-else class="courses-grid">
        <div v-for="course in courses" :key="course.id" class="course-card">
          <div class="course-header">
            <h3>{{ course.title }}</h3>
            <div class="course-meta">
              <span class="duration">{{ course.duration }}</span>
              <span class="level">{{ course.level }}</span>
              <span class="enrolled">{{ course.enrolledCount }} enrolled</span>
            </div>
          </div>
          
          <div class="course-content">
            <h4>Course Overview:</h4>
            <p class="course-description">{{ course.description }}</p>
            <div class="course-modules-info">
              <span class="modules-count">{{ course.modules.length }} Modules</span>
              <span class="duration">{{ course.duration }}</span>
            </div>
          </div>
          
          <div class="course-actions">
            <button @click="viewCourseDetails(course)" class="view-details-btn">
              View Details
            </button>
            <button 
              v-if="!isEnrolled(course.id)"
              @click="enrollInCourse(course)" 
              class="enroll-btn"
            >
              Enroll
            </button>
            <button 
              v-else
              @click="goToCurriculum(course)" 
              class="curriculum-btn"
            >
              View Curriculum
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Course Details Modal -->
    <div v-if="showCourseModal && selectedCourse" class="modal-overlay" @click="closeCourseModal">
      <div class="modal-content course-modal" @click.stop>
        <div class="modal-header">
          <h2>{{ selectedCourse.title }}</h2>
          <div class="header-buttons">
            <button @click="closeCourseModal" class="cancel-btn-header">Cancel</button>
            <button @click="closeCourseModal" class="close-btn">&times;</button>
          </div>
        </div>
        
        <div class="modal-body">
          <div class="course-info">
            <div class="info-item">
              <span class="label">Duration:</span>
              <span class="value">{{ selectedCourse.duration }}</span>
            </div>
            <div class="info-item">
              <span class="label">Level:</span>
              <span class="value">{{ selectedCourse.level }}</span>
            </div>
            <div class="info-item">
              <span class="label">Category:</span>
              <span class="value">{{ selectedCourse.category }}</span>
            </div>
          </div>
          
          <div class="course-description">
            <h3>Course Description</h3>
            <p>{{ selectedCourse.description }}</p>
          </div>
          
          <div class="course-modules">
            <h3>Course Modules</h3>
            <div class="modules-list">
              <div v-for="(module, index) in selectedCourse.modules" :key="index" class="module-detail">
                <h4>{{ module.title }}</h4>
                <ul class="subtopics-list">
                  <li v-for="subtopic in module.subtopics" :key="subtopic">{{ subtopic }}</li>
                </ul>
              </div>
            </div>
          </div>
        </div>
        
        <div class="modal-footer">
          <button 
            v-if="!isEnrolled(selectedCourse.id)"
            @click="enrollInCourse(selectedCourse)" 
            class="enroll-btn"
          >
            Enroll Now
          </button>
          <button 
            v-else
            @click="goToCurriculum(selectedCourse)" 
            class="curriculum-btn"
          >
            View Curriculum
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { apiService } from '@/services/api'

const API_BASE_URL = 'http://127.0.0.1:8000/api'

const router = useRouter()
const route = useRoute()
const authStore = useAuthStore()

// State
const enrollNotice = ref('')
const courses = ref([])
const enrolledCourses = ref([])
const showCourseModal = ref(false)
const selectedCourse = ref(null)
const currentUser = ref(null)
const courseToEnroll = ref(null)
const activeTab = ref('overview')

// Curriculum modules data
const curriculumModules = ref([
  {
    title: 'AI Fluency',
    description: 'Understand how AI works and write clear, structured prompts using practical frameworks.',
    duration: '4 hours',
    status: 'Not Started',
    locked: false,
    completed: false
  },
  {
    title: 'Ethical AI',
    description: 'Move from basic prompting to structured, strategic thinking with AI.',
    duration: '5 hours',
    status: 'Locked',
    locked: true,
    completed: false
  },
  {
    title: 'AI Agents & Automation',
    description: 'Learn how to use AI responsibly within African and global contexts.',
    duration: '6 hours',
    status: 'Locked',
    locked: true,
    completed: false
  },
  {
    title: 'Advanced AI Applications',
    description: 'Build simple autonomous AI systems that automate real business tasks.',
    duration: '5 hours',
    status: 'Locked',
    locked: true,
    completed: false
  }
])

// Load current user
const loadCurrentUser = () => {
  try {
    const userData = localStorage.getItem('user')
    currentUser.value = userData ? JSON.parse(userData) : null
  } catch (error) {
    console.error('Error loading user:', error)
  }
}

// Catalog from PostgreSQL (published courses)
const loadCourses = async () => {
  try {
    const data = await apiService.getCourses()
    const raw = Array.isArray(data.courses) ? data.courses : []

    courses.value = raw.map((c: any) => ({
      id: c.id,
      title: c.title,
      description: c.description ?? '',
      duration: c.duration ?? '',
      level: formatLevel(c.level),
      category: formatCategory(c.category),
      enrolledCount: '--',
      modules:
        Array.isArray(c.modules) && c.modules.length > 0
          ? c.modules
          : stubModules(Number(c.modules_count || 0)),
    }))
  } catch (error) {
    console.error('Error loading courses:', error)
    courses.value = []
  }
}

function formatLevel(level: string | undefined) {
  if (!level) return 'Beginner'
  const m: Record<string, string> = {
    beginner: 'Beginner',
    intermediate: 'Intermediate',
    advanced: 'Advanced',
  }
  return m[level] || level
}

function formatCategory(cat: string | undefined) {
  return cat?.replace(/-/g, ' ') || ''
}

function stubModules(count: number): { title: string; subtopics: string[] }[] {
  const n = Math.min(Math.max(count, 0), 40)
  const out: { title: string; subtopics: string[] }[] = []
  for (let k = 1; k <= n; k++) out.push({ title: 'Module ' + k, subtopics: [] })
  return out
}

// Enrolled course IDs from PostgreSQL (authenticated students only)
const loadEnrolledCourses = async () => {
  enrolledCourses.value = []
  if (!authStore.isAuthenticated) return
  try {
    const data = await apiService.getEnrolledCourseIds()
    enrolledCourses.value = Array.isArray(data.course_ids)
      ? data.course_ids.map((x: string | number) => Number(x))
      : []
  } catch (error) {
    console.error('Error loading enrolled courses:', error)
    enrolledCourses.value = []
  }
}

const isEnrolled = (courseId: number) => {
  return enrolledCourses.value.includes(Number(courseId))
}

// Navigation functions
const goBack = () => {
  router.push('/dashboard')
}

const goToCurriculum = (course) => {
  router.push(`/course/${course.id}/learn`)
}

// Course interaction functions
const viewCourseDetails = (course) => {
  selectedCourse.value = course
  showCourseModal.value = true
}

const enrollInCourse = async (course: { id: number; title: string }) => {
  if (authStore.isAuthenticated && authStore.token) {
    try {
      await apiService.enrollInCourse(course.id)
      closeCourseModal()
      await loadEnrolledCourses()
      window.alert(`Successfully enrolled in "${course.title}".`)
      router.push(`/course/${course.id}/learn`)
      return
    } catch (e: any) {
      console.error(e)
      window.alert('Enrollment failed. Try again.')
      return
    }
  }

  courseToEnroll.value = course
  localStorage.setItem(
    'courseToEnroll',
    JSON.stringify({ id: course.id, title: course.title }),
  )
  authStore.setReturnUrl('/courses')
  router.push('/login')
}


// Access module with locking mechanism
const accessModule = (module, index) => {
  if (module.locked) {
    // Show message that module is locked
    alert('Please complete the previous module to unlock this one.')
    return
  }
  
  if (module.completed) {
    // Navigate to completed module
    console.log('Opening completed module:', module.title)
    // TODO: Navigate to module content
  } else {
    // Navigate to current module
    console.log('Opening module:', module.title)
    // TODO: Navigate to module content
  }
}

// Modal functions
const closeCourseModal = () => {
  showCourseModal.value = false
  selectedCourse.value = null
}

// Lifecycle hook
onMounted(async () => {
  if (route.query.notice === 'enroll_first') {
    enrollNotice.value = 'Please enroll in a course before opening lessons.'
    router.replace({ path: '/courses', query: {} })
  }
  loadCurrentUser()
  await loadCourses()
  await loadEnrolledCourses()
})
</script>

<style scoped>
.courses-view {
  max-width: 1200px;
  margin: 0 auto;
  padding: 20px;
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

.notice-banner {
  background: #fffbeb;
  border: 1px solid #fcd34d;
  color: #92400e;
  padding: 12px 16px;
  border-radius: 8px;
  margin-bottom: 16px;
  font-size: 0.95rem;
}

.page-header {
  display: flex;
  align-items: center;
  gap: 20px;
  margin-bottom: 30px;
  padding-bottom: 20px;
  border-bottom: 2px solid #e0e0e0;
}

.back-btn {
  background: #f5f5f5;
  border: none;
  padding: 10px 16px;
  border-radius: 6px;
  cursor: pointer;
  font-size: 14px;
  transition: all 0.3s ease;
}

.back-btn:hover {
  background: #e0e0e0;
}

.page-header h1 {
  color: #333;
  font-size: 28px;
  margin: 0;
  font-weight: 600;
}

.courses-section {
  margin-bottom: 40px;
}

.courses-section h2 {
  color: #333;
  font-size: 22px;
  margin-bottom: 20px;
  font-weight: 600;
}

.empty-state {
  text-align: center;
  padding: 60px 20px;
  background: #f9f9f9;
  border-radius: 12px;
  border: 2px dashed #ddd;
}

.empty-state p {
  color: #666;
  font-size: 18px;
  margin: 0;
}

.courses-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
  gap: 25px;
  align-items: stretch;
}

.course-card {
  background: white;
  border-radius: 12px;
  padding: 25px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  border: 1px solid #e0e0e0;
  transition: all 0.3s ease;
  position: relative;
  overflow: hidden;
  display: flex;
  flex-direction: column;
  height: 100%;
}

.course-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 4px;
  background: linear-gradient(90deg, #00897b, #26a69a);
}

.course-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
}

.course-header h3 {
  color: #333;
  font-size: 20px;
  margin: 0 0 15px 0;
  font-weight: 600;
  line-height: 1.3;
}

.course-meta {
  display: flex;
  flex-wrap: wrap;
  gap: 12px;
  margin-bottom: 20px;
}

.course-meta span {
  padding: 6px 12px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 500;
  background: #f5f5f5;
  color: #666;
}

.duration {
  background: #e3f2fd;
  color: #1976d2;
}

.level {
  background: #f3e5f5;
  color: #7b1fa2;
}

.enrolled {
  background: #e8f5e8;
  color: #388e3c;
}

.course-content {
  margin-bottom: 25px;
}

.course-content h4 {
  color: #333;
  font-size: 16px;
  margin: 0 0 15px 0;
  font-weight: 600;
}

.weeks-preview {
  background: #f9f9f9;
  border-radius: 8px;
  padding: 15px;
  border-left: 4px solid #00897b;
}

.week-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 8px 0;
  border-bottom: 1px solid #eee;
}

.week-item:last-child {
  border-bottom: none;
}

.week-number {
  font-weight: 600;
  color: #00897b;
  font-size: 14px;
}

.week-topic {
  color: #666;
  font-size: 14px;
  flex: 1;
  margin-left: 10px;
}

.more-weeks {
  text-align: center;
  color: #00897b;
  font-weight: 500;
  font-size: 14px;
  padding: 8px 0;
  border-top: 1px solid #eee;
  margin-top: 8px;
}

.course-actions {
  display: flex;
  gap: 12px;
  margin-top: auto;
  padding-top: 20px;
}

.view-details-btn,
.enroll-btn,
.curriculum-btn {
  flex: 1;
  padding: 12px 20px;
  border: none;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  text-align: center;
}

.view-details-btn {
  background: #f5f5f5;
  color: #333;
  border: 2px solid #e0e0e0;
}

.view-details-btn:hover {
  background: #e0e0e0;
  border-color: #ccc;
}

.enroll-btn {
  background: linear-gradient(135deg, #ff9800, #f57c00);
  color: white;
  border: 2px solid transparent;
}

.enroll-btn:hover:not(:disabled) {
  background: #808080;
  transform: none;
  box-shadow: none;
}

.enroll-btn:disabled {
  background: #ccc;
  cursor: not-allowed;
  transform: none;
  box-shadow: none;
}

.enroll-btn.enrolled {
  background: #4caf50;
  cursor: default;
}

.enroll-btn.enrolled:hover {
  background: #45a049;
  transform: none;
  box-shadow: none;
}

.curriculum-btn {
  background: linear-gradient(135deg, #ff9800, #f57c00);
  color: white;
  border: 2px solid transparent;
}

.curriculum-btn:hover {
  background: #808080;
  transform: none;
  box-shadow: none;
}

/* Modal Styles */
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
  padding: 20px;
}

.modal-content {
  background: white;
  border-radius: 16px;
  max-width: 600px;
  width: 100%;
  max-height: 80vh;
  overflow-y: auto;
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
  animation: modalSlideIn 0.3s ease-out;
}

@keyframes modalSlideIn {
  from {
    opacity: 0;
    transform: translateY(-50px) scale(0.9);
  }
  to {
    opacity: 1;
    transform: translateY(0) scale(1);
  }
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 25px 30px;
  border-bottom: 1px solid #e0e0e0;
  background: linear-gradient(135deg, #00897b, #26a69a);
  color: white;
  border-radius: 16px 16px 0 0;
}

.modal-header h2 {
  margin: 0;
  font-size: 24px;
  font-weight: 600;
}

.close-btn {
  background: rgba(255, 255, 255, 0.2);
  border: none;
  width: 36px;
  height: 36px;
  border-radius: 50%;
  color: white;
  font-size: 18px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.3s ease;
}

.close-btn:hover {
  background: rgba(255, 255, 255, 0.3);
  transform: rotate(90deg);
}

.modal-body {
  padding: 30px;
}

.course-details {
  color: #333;
}

.course-detail-item {
  margin-bottom: 25px;
  padding: 20px;
  background: #f9f9f9;
  border-radius: 12px;
  border-left: 4px solid #00897b;
}

.course-detail-item h4 {
  margin: 0 0 10px 0;
  color: #00897b;
  font-size: 18px;
  font-weight: 600;
}

.course-detail-item p {
  margin: 0;
  line-height: 1.6;
  color: #666;
}

/* Simplified Modal Content */
.weeks-simplified {
  margin-top: 15px;
}

.week-simplified {
  margin-bottom: 8px;
  padding: 8px 0;
  border-bottom: 1px solid #eee;
}

.week-simplified:last-child {
  border-bottom: none;
}

.week-simplified p {
  margin: 0;
  color: #333;
  font-size: 14px;
  line-height: 1.4;
}

.expectations {
  margin-top: 15px;
}

.expectations p {
  margin: 0 0 10px 0;
  color: #666;
  font-size: 14px;
  line-height: 1.5;
}

.expectations p:last-child {
  margin-bottom: 0;
}

/* Instructors Section */
.instructors-list {
  display: flex;
  gap: 20px;
  margin-top: 15px;
}

.instructor-card {
  display: flex;
  align-items: center;
  gap: 15px;
  background: white;
  border: 1px solid #e0e0e0;
  border-radius: 12px;
  padding: 15px;
  flex: 1;
  transition: all 0.3s ease;
}

.instructor-card:hover {
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  transform: translateY(-2px);
}

.instructor-avatar {
  width: 50px;
  height: 50px;
  border-radius: 50%;
  background: linear-gradient(135deg, #00897b, #26a69a);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 24px;
}

.instructor-info h5 {
  margin: 0 0 5px 0;
  color: #333;
  font-size: 16px;
  font-weight: 600;
}

.instructor-info p {
  margin: 0 0 8px 0;
  color: #666;
  font-size: 14px;
}

.instructor-badge {
  background: #4caf50;
  color: white;
  padding: 2px 8px;
  border-radius: 12px;
  font-size: 12px;
  font-weight: 500;
}

/* Weeks Overview */
.weeks-overview {
  display: flex;
  flex-direction: column;
  gap: 15px;
  margin-top: 15px;
}

.week-overview-card {
  background: white;
  border: 1px solid #e0e0e0;
  border-radius: 12px;
  padding: 20px;
  transition: all 0.3s ease;
}

.week-overview-card:hover {
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  transform: translateY(-2px);
}

.week-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 15px;
}

.week-header h5 {
  margin: 0;
  color: #00897b;
  font-size: 16px;
  font-weight: 600;
}

.week-duration {
  background: #e3f2fd;
  color: #1976d2;
  padding: 4px 12px;
  border-radius: 16px;
  font-size: 12px;
  font-weight: 500;
}

.week-content-preview p {
  margin: 0 0 12px 0;
  color: #666;
  font-size: 14px;
  line-height: 1.5;
}

.week-topics {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
}

.topic-tag {
  background: #f5f5f5;
  color: #666;
  padding: 4px 10px;
  border-radius: 12px;
  font-size: 12px;
  font-weight: 500;
}

/* Learning Path */
.learning-path {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 10px;
  margin-top: 15px;
}

.path-item {
  display: flex;
  align-items: center;
  gap: 15px;
  background: white;
  border: 1px solid #e0e0e0;
  border-radius: 12px;
  padding: 20px;
  width: 100%;
  transition: all 0.3s ease;
}

.path-item:hover {
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  transform: translateY(-2px);
}

.path-icon {
  width: 50px;
  height: 50px;
  border-radius: 50%;
  background: linear-gradient(135deg, #00897b, #26a69a);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 24px;
  flex-shrink: 0;
}

.path-content h5 {
  margin: 0 0 5px 0;
  color: #333;
  font-size: 16px;
  font-weight: 600;
}

.path-content p {
  margin: 0;
  color: #666;
  font-size: 14px;
}

.path-arrow {
  color: #00897b;
  font-size: 24px;
  font-weight: bold;
  margin: 5px 0;
}

/* Course Info Grid */
.course-info-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 15px;
  margin-top: 15px;
}

.info-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: white;
  border: 1px solid #e0e0e0;
  border-radius: 8px;
  padding: 12px 15px;
}

.info-label {
  color: #666;
  font-size: 14px;
  font-weight: 500;
}

.info-value {
  color: #333;
  font-size: 14px;
  font-weight: 600;
}

.weeks-list {
  margin-top: 15px;
}

.week-detail {
  background: white;
  border: 1px solid #e0e0e0;
  border-radius: 8px;
  padding: 15px;
  margin-bottom: 12px;
}

.week-detail h5 {
  margin: 0 0 8px 0;
  color: #333;
  font-size: 16px;
  font-weight: 600;
}

.week-detail p {
  margin: 0;
  color: #666;
  font-size: 14px;
  line-height: 1.5;
}

.modal-footer {
  padding: 20px 30px;
  border-top: 1px solid #e0e0e0;
  background: #f9f9f9;
  display: flex;
  justify-content: flex-end;
  gap: 12px;
  border-radius: 0 0 16px 16px;
}

/* Responsive Design */
@media (max-width: 768px) {
  .courses-view {
    padding: 15px;
  }
  
  .page-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 15px;
  }
  
  .page-header h1 {
    font-size: 24px;
  }
  
  .courses-grid {
    grid-template-columns: 1fr;
    gap: 20px;
  }
  
  .course-card {
    padding: 20px;
  }
  
  .course-actions {
    flex-direction: column;
  }
  
  .modal-content {
    margin: 10px;
    max-height: 90vh;
  }
  
  .modal-header,
  .modal-body,
  .modal-footer {
    padding: 20px;
  }
}

@media (max-width: 480px) {
  .courses-view {
    padding: 10px;
  }
  
  .course-card {
    padding: 15px;
  }
  
  .course-meta {
    gap: 8px;
  }
  
  .course-meta span {
    font-size: 11px;
    padding: 4px 8px;
  }
  
  .modal-header,
  .modal-body,
  .modal-footer {
    padding: 15px;
  }
  
  .modal-header h2 {
    font-size: 20px;
  }
}

/* Accessibility */
.modal-overlay:focus-within {
  outline: 2px solid #00897b;
}

.course-card:focus-within {
  outline: 2px solid #00897b;
  outline-offset: 2px;
}

button:focus {
  outline: 2px solid #00897b;
  outline-offset: 2px;
}

/* Course Detail Modal Styles */
.course-detail-modal {
  width: 95%;
  max-width: 1200px;
  max-height: 90vh;
  overflow-y: auto;
}

/* Tabs Navigation */
.tabs-navigation {
  display: flex;
  border-bottom: 2px solid #e0e0e0;
  margin-bottom: 20px;
}

.tab-btn {
  background: none;
  border: none;
  padding: 15px 25px;
  font-size: 16px;
  font-weight: 600;
  color: #666;
  cursor: pointer;
  border-bottom: 3px solid transparent;
  transition: all 0.3s ease;
}

.tab-btn:hover {
  color: #00897b;
  background: rgba(0, 137, 123, 0.1);
}

.tab-btn.active {
  color: #00897b;
  border-bottom-color: #00897b;
  background: rgba(0, 137, 123, 0.1);
}

/* Tab Content */
.tab-content {
  min-height: 400px;
}

/* Overview Content */
.overview-content h3 {
  color: #00897b;
  margin: 20px 0 10px 0;
  font-size: 20px;
}

.overview-content p {
  line-height: 1.6;
  margin-bottom: 15px;
  color: #333;
}

.overview-content ul {
  margin: 15px 0;
  padding-left: 20px;
}

.overview-content li {
  margin-bottom: 8px;
  line-height: 1.5;
}

.modules-table {
  margin: 20px 0;
  overflow-x: auto;
}

.modules-table table {
  width: 100%;
  border-collapse: collapse;
  background: white;
  border-radius: 8px;
  overflow: hidden;
  box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.modules-table th,
.modules-table td {
  padding: 12px;
  text-align: left;
  border-bottom: 1px solid #e0e0e0;
  vertical-align: top;
}

.modules-table th {
  background: #00897b;
  color: white;
  font-weight: 600;
}

.modules-table tr:nth-child(even) {
  background: #f8f9fa;
}

.info-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 20px;
  margin: 20px 0;
}

.info-block {
  background: #f8f9fa;
  padding: 20px;
  border-radius: 8px;
  border-left: 4px solid #00897b;
}

.info-block h4 {
  color: #00897b;
  margin: 0 0 10px 0;
}

.why-section,
.ready-section {
  margin: 30px 0;
  padding: 20px;
  background: linear-gradient(135deg, #f8f9fa, #e8f5e8);
  border-radius: 8px;
}

.features {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
  margin: 15px 0;
}

.feature {
  background: #00897b;
  color: white;
  padding: 8px 15px;
  border-radius: 20px;
  font-size: 14px;
  font-weight: 500;
}

.tagline {
  text-align: center;
  font-style: italic;
  color: #666;
  margin-top: 15px;
}

/* Curriculum Content */
.curriculum-modules {
  display: grid;
  gap: 20px;
}

.module-item {
  background: white;
  border: 2px solid #e0e0e0;
  border-radius: 12px;
  padding: 20px;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.module-item:hover:not(.locked) {
  border-color: #00897b;
  box-shadow: 0 4px 12px rgba(0, 137, 123, 0.2);
  transform: translateY(-2px);
}

.module-item.locked {
  background: #f5f5f5;
  border-color: #ccc;
  cursor: not-allowed;
  opacity: 0.7;
}

.module-item.completed {
  border-color: #4caf50;
  background: #e8f5e8;
}

.module-header {
  display: flex;
  align-items: center;
  gap: 15px;
  flex: 1;
}

.module-icon {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 18px;
  font-weight: bold;
}

.module-item.completed .module-icon {
  background: #4caf50;
  color: white;
}

.module-item.locked .module-icon {
  background: #f44336;
  color: white;
}

.module-info {
  flex: 1;
}

.module-info h4 {
  margin: 0 0 5px 0;
  color: #333;
  font-size: 16px;
}

.module-info p {
  margin: 0;
  color: #666;
  font-size: 14px;
}

.module-mdeta {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  gap: 5px;
}

.duration {
  background: #e3f2fd;
  color: #1976d2;
  padding: 4px 8px;
  border-radius: 12px;
  font-size: 12px;
  font-weight: 500;
}

.status {
  font-size: 12px;
  font-weight: 600;
  padding: 4px 8px;
  border-radius: 12px;
}

.module-item.locked .status {
  background: #ffebee;
  color: #c62828;
}

.module-item.completed .status {
  background: #e8f5e8;
  color: #2e7d32;
}

/* Instructor Content */
.instructor-profile {
  display: flex;
  flex-direction: column;
  gap: 30px;
}

.instructor-header {
  display: flex;
  align-items: center;
  gap: 20px;
  padding: 20px;
  background: #f8f9fa;
  border-radius: 12px;
}

.instructor-avatar {
  width: 80px;
  height: 80px;
  border-radius: 50%;
  overflow: hidden;
  border: 3px solid #00897b;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, #00897b, #26a69a);
  color: white;
  font-weight: bold;
  font-size: 24px;
}

.avatar-placeholder {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
  font-size: 24px;
  color: white;
}

.instructor-info h3 {
  margin: 0 0 5px 0;
  color: #333;
  font-size: 20px;
}

.instructor-title {
  color: #00897b;
  font-weight: 600;
  margin: 0 0 5px 0;
}

.instructor-email {
  color: #666;
  margin: 0;
  font-size: 14px;
}

.instructor-bio {
  background: white;
  padding: 25px;
  border-radius: 12px;
  border-left: 4px solid #00897b;
  box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.instructor-bio h4 {
  color: #00897b;
  margin: 0 0 15px 0;
}

.instructor-bio p {
  line-height: 1.6;
  color: #333;
  margin: 0;
}

/* Course Modal Styles */
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
  z-index: 9999;
}

.course-modal {
  background: white;
  border-radius: 12px;
  max-width: 800px;
  width: 90%;
  max-height: 80vh;
  overflow-y: auto;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
  position: relative;
  z-index: 10000;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 24px 24px 20px 24px;
  border-bottom: 1px solid #e0e0e0;
  position: relative;
}

.header-buttons {
  display: flex;
  align-items: center;
  gap: 8px;
  position: absolute;
  top: 20px;
  right: 24px;
  z-index: 99999;
}

.cancel-btn-header {
  background: #f5f5f5;
  color: #666;
  border: 1px solid #ddd;
  padding: 6px 12px;
  border-radius: 6px;
  cursor: pointer;
  font-weight: 500;
  font-size: 14px;
}

.cancel-btn-header:hover {
  background: #e0e0e0;
  color: #333;
}

.modal-header h2 {
  margin: 0;
  color: #333;
  font-size: 24px;
  font-weight: 600;
  padding-right: 120px;
}

.close-btn {
  background: none;
  border: none;
  font-size: 24px;
  cursor: pointer;
  color: #666;
  padding: 0;
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
}

.close-btn:hover {
  background: #f5f5f5;
  color: #333;
}

.modal-body {
  padding: 24px;
}

.course-info {
  display: flex;
  gap: 20px;
  margin-bottom: 24px;
  flex-wrap: wrap;
}

.info-item {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.info-item .label {
  font-size: 12px;
  color: #666;
  font-weight: 500;
}

.info-item .value {
  font-size: 14px;
  color: #333;
  font-weight: 600;
}

.course-description {
  margin-bottom: 24px;
}

.course-description h3 {
  margin: 0 0 12px 0;
  color: #333;
  font-size: 18px;
  font-weight: 600;
}

.course-description p {
  margin: 0;
  color: #666;
  line-height: 1.6;
}

.course-modules h3 {
  margin: 0 0 16px 0;
  color: #333;
  font-size: 18px;
  font-weight: 600;
}

.modules-list {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.module-detail {
  background: #f8f9fa;
  padding: 16px;
  border-radius: 8px;
  border-left: 4px solid #00897b;
}

.module-detail h4 {
  margin: 0 0 12px 0;
  color: #333;
  font-size: 16px;
  font-weight: 600;
}

.subtopics-list {
  margin: 0;
  padding-left: 20px;
  color: #666;
}

.subtopics-list li {
  margin-bottom: 4px;
  line-height: 1.5;
}

.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 12px;
  padding: 20px 24px;
  border-top: 1px solid #e0e0e0;
  background: #f8f9fa;
}

.cancel-btn {
  background: #f5f5f5;
  color: #666;
  border: 1px solid #ddd;
  padding: 10px 20px;
  border-radius: 6px;
  cursor: pointer;
  font-weight: 500;
}

.cancel-btn:hover {
  background: #e0e0e0;
}
</style>
