<template>
  <div class="student-dashboard">
    <!-- LEFT SIDEBAR -->
    <div class="left-sidebar">
      <div class="logo" @click="goToDashboard">
        <span class="logo-icon">⚡</span>
        <span class="logo-text">Shieldmaidens</span>
      </div>
      
      <nav class="nav-menu">
        <div class="nav-column">
          <div class="nav-item active" @click="handleNavigation('/dashboard')">
            <span>Dashboard</span>
          </div>
          <div class="nav-item" @click="handleNavigation('/courses')">
            <span>Explore Courses</span>
          </div>
          <div class="nav-item" @click="handleNavigation('/sessions')">
            <span>Class Sessions</span>
          </div>
          <div class="nav-item" @click="handleNavigation('/submissions')">
            <span>Submissions</span>
          </div>
          <div class="nav-item" @click="handleNavigation('/groups')">
            <span>Groups</span>
          </div>
          <div class="nav-item" @click="handleNavigation('/community')">
            <span>Community</span>
          </div>
          
          <div class="nav-item" @click="handleNavigation('/certificates')">
            <span>Certificates</span>
          </div>
          <div class="nav-item" @click="handleNavigation('/my-courses')">
            <span>My Courses</span>
          </div>
        </div>
      </nav>
      
      <div class="sidebar-bottom">
        <div class="current-course">
          <div class="course-label">Welcome back,</div>
          <div class="course-name">{{ currentUser?.name || 'Student' }}</div>
        </div>
        <div class="collapse-btn"><< Collapse</div>
      </div>
    </div>

    <!-- MAIN CONTENT AREA -->
    <div class="main-content">
      <!-- HEADER -->
      <div class="header">
        <h1 class="page-title">Dashboard</h1>
        <button @click="refreshData" class="refresh-btn" :disabled="isLoading">
          {{ isLoading ? 'Refreshing...' : '🔄 Refresh' }}
        </button>
      </div>

      <!-- OVERVIEW SECTION -->
      <div class="overview-section">
        <h3 class="section-title">Overview</h3>
        <div class="overview-cards">
          <div class="overview-card">
            <div class="card-icon">🎓</div>
            <div class="card-number">{{ enrolledCourses.length }}</div>
            <div class="card-label">Enrolled Courses</div>
          </div>
          <div class="overview-card">
            <div class="card-icon">⭐</div>
            <div class="card-number">0</div>
            <div class="card-label">Points</div>
          </div>
          <div class="overview-card">
            <div class="card-icon">📋</div>
            <div class="card-number">0</div>
            <div class="card-label">Completed Courses</div>
          </div>
        </div>
      </div>

      <!-- CONTENT GRID -->
      <div class="content-grid">
        <!-- ENROLLED MODULES -->
        <div class="enrolled-modules">
          <h3 class="section-title">My Enrolled Courses</h3>
          
          <!-- Loading state -->
          <div v-if="isLoading" class="loading-state">
            <div class="loading-spinner">⏳ Loading your courses...</div>
          </div>
          
          <!-- Empty state -->
          <div v-else-if="enrolledCourses.length === 0" class="empty-state">
            <div class="empty-icon">📚</div>
            <p class="empty-text">No courses enrolled yet.</p>
            <button @click="handleNavigation('/courses')" class="browse-courses-btn">
              Browse Courses
            </button>
          </div>
          
          <!-- Course list -->
          <div v-else class="module-cards">
            <div 
              v-for="course in enrolledCourses" 
              :key="course.id" 
              class="module-card"
            >
              <div class="module-icon">📖</div>
              <h4 class="module-name">{{ course.title }}</h4>
              <div class="course-meta">
                <span class="duration">{{ course.duration }}</span>
                <span class="level">{{ course.level }}</span>
              </div>
              <div class="progress-bar">
                <div class="progress-fill" :style="{ width: (course.progress ?? 0) + '%' }"></div>
              </div>
              <p class="progress-text">{{ course.progress ?? 0 }}% Completed</p>
              <button @click="handleNavigation(`/course/${course.id}/learn`)" class="continue-learning-btn">
                Continue Learning
              </button>
            </div>
          </div>
        </div>

        <!-- RIGHT SIDEBAR -->
        <div class="right-sidebar">
          <!-- UPCOMING CLASSES -->
          <div class="upcoming-classes">
            <h3 class="sidebar-title">Upcoming Classes</h3>
            <div class="classes-card">
              <div class="no-classes-icon">👥</div>
              <p class="no-classes-text">No Classes Yet</p>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <!-- FLOATING ACTION BUTTONS -->
    <div class="floating-buttons">
      <div class="floating-btn accessibility-btn" @click="openAccessibility">
        <span class="btn-icon">♿</span>
        <span class="btn-tooltip">Accessibility Features</span>
      </div>
      <div class="floating-btn message-btn" @click="openFAQ">
        <span class="btn-icon">💬</span>
        <span class="btn-tooltip">FAQs</span>
      </div>
      <div class="floating-btn help-btn" @click="openHelp">
        <span class="btn-icon">❓</span>
        <span class="btn-tooltip">Need Help?</span>
      </div>
    </div>

    <!-- ACCESSIBILITY MODAL -->
    <div v-if="showAccessibility" class="modal-overlay" @click="closeAccessibility">
      <div class="modal-content" @click.stop>
        <div class="modal-header">
          <h2>Accessibility Features</h2>
          <button @click="closeAccessibility" class="close-btn">✕</button>
        </div>
        <div class="modal-body">
          <div class="accessibility-option">
            <h3>🔤 Font Size</h3>
            <div class="font-controls">
              <button @click="decreaseFontSize" class="font-btn">A-</button>
              <button @click="resetFontSize" class="font-btn">A</button>
              <button @click="increaseFontSize" class="font-btn">A+</button>
            </div>
          </div>
          
          <div class="accessibility-option">
            <h3>🎨 High Contrast</h3>
            <label class="switch">
              <input type="checkbox" v-model="highContrast" @change="toggleHighContrast">
              <span class="slider"></span>
            </label>
          </div>
          
          <div class="accessibility-option">
            <h3>🔊 Screen Reader</h3>
            <p>Enable screen reader compatibility mode</p>
            <label class="switch">
              <input type="checkbox" v-model="screenReader" @change="toggleScreenReader">
              <span class="slider"></span>
            </label>
          </div>
          
          <div class="accessibility-option">
            <h3>⌨️ Keyboard Navigation</h3>
            <p>Use Tab key to navigate, Enter to select, Escape to close modals</p>
          </div>
        </div>
        <div class="modal-footer">
          <button @click="closeAccessibility" class="back-btn">← Back</button>
        </div>
      </div>
    </div>

    <!-- FAQ MODAL -->
    <div v-if="showFAQ" class="modal-overlay" @click="closeFAQ">
      <div class="modal-content" @click.stop>
        <div class="modal-header">
          <h2>Frequently Asked Questions</h2>
          <button @click="closeFAQ" class="close-btn">✕</button>
        </div>
        <div class="modal-body">
          <div class="faq-item" v-for="(faq, index) in faqs" :key="index">
            <div class="faq-question" @click="toggleFAQ(index)">
              <span>{{ faq.question }}</span>
              <span class="faq-toggle">{{ faq.open ? '−' : '+' }}</span>
            </div>
            <div v-if="faq.open" class="faq-answer">
              {{ faq.answer }}
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button @click="closeFAQ" class="back-btn">← Back</button>
        </div>
      </div>
    </div>

    <!-- HELP MODAL -->
    <div v-if="showHelp" class="modal-overlay" @click="closeHelp">
      <div class="modal-content" @click.stop>
        <div class="modal-header">
          <h2>Submit Your Concern</h2>
          <button @click="closeHelp" class="close-btn">✕</button>
        </div>
        <div class="modal-body">
          <form @submit.prevent="submitHelpForm">
            <div class="form-group">
              <label for="help-name">Your Name</label>
              <input type="text" id="help-name" v-model="helpForm.name" required>
            </div>
            
            <div class="form-group">
              <label for="help-email">Email Address</label>
              <input type="email" id="help-email" v-model="helpForm.email" required>
            </div>
            
            <div class="form-group">
              <label for="help-subject">Subject</label>
              <select id="help-subject" v-model="helpForm.subject" required>
                <option value="">Select a subject</option>
                <option value="technical">Technical Issue</option>
                <option value="course">Course Related</option>
                <option value="account">Account Problem</option>
                <option value="other">Other</option>
              </select>
            </div>
            
            <div class="form-group">
              <label for="help-message">Describe Your Concern</label>
              <textarea id="help-message" v-model="helpForm.message" rows="5" required placeholder="Please describe your issue or concern in detail..."></textarea>
            </div>
            
            <button type="submit" class="submit-btn">Submit Concern</button>
          </form>
        </div>
        <div class="modal-footer">
          <button @click="closeHelp" class="back-btn">← Back</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { apiService } from '@/services/api'

const router = useRouter()
const authStore = useAuthStore()

// State for real-time data
const currentUser = ref(null)
const enrolledCourses = ref([])
const availableCourses = ref([])
const isLoading = ref(true)

// Modal states
const showAccessibility = ref(false)
const showFAQ = ref(false)
const showHelp = ref(false)

// Accessibility options
const highContrast = ref(false)
const screenReader = ref(false)
const fontSize = ref(16)

// Help form
const helpForm = ref({
  name: '',
  email: '',
  subject: '',
  message: ''
})

// FAQs data
const faqs = ref([
  {
    question: 'How do I enroll in a course?',
    answer: 'Navigate to the "Explore Courses" section from your dashboard, find a course that interests you, and click the "Enroll Now" button. You can access enrolled courses from "My Courses".',
    open: false
  },
  {
    question: 'How do I track my progress?',
    answer: 'Your progress is automatically tracked as you complete modules and lessons. You can view detailed progress reports in the "Progress" section of your dashboard.',
    open: false
  },
  {
    question: 'Can I download course certificates?',
    answer: 'Yes! Once you complete a course, certificates are automatically generated and available in the "Certificates" section. You can download them in PDF format.',
    open: false
  },
  {
    question: 'How do I contact instructors?',
    answer: 'You can contact instructors through the course discussion forums or by using the help form. Instructors typically respond within 24-48 hours.',
    open: false
  },
  {
    question: 'What are the system requirements?',
    answer: 'Our LMS works on modern browsers (Chrome, Firefox, Safari, Edge) and requires an internet connection. Mobile devices are fully supported.',
    open: false
  }
])

const handleNavigation = (route: string) => {
  router.push(route)
}

// Navigation function for logo
const goToDashboard = () => {
  router.push('/dashboard')
}

// Real-time data loading functions
const loadCurrentUser = () => {
  try {
    const user = apiService.getCurrentUserFromStorage()
    currentUser.value = user
  } catch (error) {
    console.error('Error loading current user:', error)
  }
}

const formatLevel = (level: string | undefined) => {
  if (!level) return 'Student'
  const m: Record<string, string> = {
    beginner: 'Beginner',
    intermediate: 'Intermediate',
    advanced: 'Advanced',
  }
  return m[level] || level
}

const loadEnrolledCourses = async () => {
  enrolledCourses.value = []
  availableCourses.value = []
  try {
    const data = await apiService.getEnrollments()
    const rows = Array.isArray(data.enrollments) ? data.enrollments : []
    enrolledCourses.value = rows.map((e: Record<string, unknown>) => ({
      id: e.course_id,
      title: e.title,
      description: e.description,
      duration: e.duration,
      level: formatLevel(e.level as string),
      progress: typeof e.progress === 'number' ? e.progress : Number(e.progress || 0),
    }))
  } catch (error) {
    console.error('Error loading enrolled courses:', error)
    enrolledCourses.value = []
  }
}

// Initialize data on component mount
onMounted(async () => {
  await refreshData()
})

// Refresh data function
const refreshData = async () => {
  isLoading.value = true
  loadCurrentUser()
  await loadEnrolledCourses()
  isLoading.value = false
}

// Modal functions
const openAccessibility = () => {
  showAccessibility.value = true
}

const closeAccessibility = () => {
  showAccessibility.value = false
}

const openFAQ = () => {
  showFAQ.value = true
}

const closeFAQ = () => {
  showFAQ.value = false
}

const openHelp = () => {
  showHelp.value = true
}

const closeHelp = () => {
  showHelp.value = false
}

// Accessibility functions
const increaseFontSize = () => {
  if (fontSize.value < 24) {
    fontSize.value += 2
    document.documentElement.style.fontSize = fontSize.value + 'px'
  }
}

const decreaseFontSize = () => {
  if (fontSize.value > 12) {
    fontSize.value -= 2
    document.documentElement.style.fontSize = fontSize.value + 'px'
  }
}

const resetFontSize = () => {
  fontSize.value = 16
  document.documentElement.style.fontSize = '16px'
}

const toggleHighContrast = () => {
  document.body.classList.toggle('high-contrast', highContrast.value)
}

const toggleScreenReader = () => {
  document.body.classList.toggle('screen-reader', screenReader.value)
}

// FAQ functions
const toggleFAQ = (index: number) => {
  faqs.value[index].open = !faqs.value[index].open
}

// Help form functions
const submitHelpForm = () => {
  console.log('Help form submitted:', helpForm.value)
  // TODO: Send to backend API
  alert('Thank you for your concern! We will get back to you soon.')
  
  // Reset form
  helpForm.value = {
    name: '',
    email: '',
    subject: '',
    message: ''
  }
  closeHelp()
}

const handleLogout = async () => {
  await authStore.logout()
  router.push('/login')
}
</script>

<style scoped>
.student-dashboard {
  display: flex;
  height: 100vh;
  font-family: Arial, sans-serif;
  background: #f5f5f5;
}

/* LEFT SIDEBAR */
.left-sidebar {
  width: 250px;
  background: #000000; /* Black background */
  color: white;
  padding: 20px;
  height: 100vh;
  position: fixed;
  left: 0;
  top: 0;
  z-index: 1000;
  overflow-y: auto;
  overflow-x: hidden;
  display: flex;
  flex-direction: column;
}

/* Custom scrollbar for sidebar */
.left-sidebar::-webkit-scrollbar {
  width: 6px;
}

.left-sidebar::-webkit-scrollbar-track {
  background: rgba(255, 255, 255, 0.1);
}

.left-sidebar::-webkit-scrollbar-thumb {
  background: rgba(255, 255, 255, 0.3);
  border-radius: 3px;
}

.left-sidebar::-webkit-scrollbar-thumb:hover {
  background: rgba(255, 255, 255, 0.5);
}

.logo {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 30px;
  cursor: pointer;
  transition: opacity 0.2s ease;
}

.logo:hover {
  opacity: 0.8;
}

.logo-icon {
  font-size: 28px;
}

.logo-text {
  font-size: 18px;
  font-weight: bold;
  color: #ffffff; /* White text on black background */
}

.nav-menu {
  flex: 1;
}

.nav-column {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.nav-item {
  display: flex;
  align-items: center;
  padding: 15px 12px;
  cursor: pointer;
  border-radius: 6px;
  font-size: 16px;
  font-weight: 500;
  transition: background-color 0.2s;
  border-bottom: 1px solid #333333; /* Dark gray border */
}

.nav-item:hover {
  background-color: rgba(255, 255, 255, 0.1);
}

.nav-item.active {
  background-color: #333333; /* Dark gray for active state */
  border-left: 4px solid white;
  padding-left: 8px;
}

.sidebar-bottom {
  margin-top: auto;
  padding-top: 20px;
}

.current-course {
  margin-bottom: 15px;
}

.course-label {
  font-size: 12px;
  text-transform: uppercase;
  color: #b2dfdb;
  margin-bottom: 5px;
}

.course-name {
  font-weight: bold;
  font-size: 14px;
}

.collapse-btn {
  font-size: 12px;
  color: #b2dfdb;
  cursor: pointer;
  padding: 5px 10px;
  background: #333333; /* Dark gray background */
  border-radius: 4px;
  text-align: center;
}

/* MAIN CONTENT */
.main-content {
  flex: 1;
  margin-left: 250px;
  padding: 20px;
  overflow-y: auto;
}

/* HEADER */
.header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 30px;
  padding: 0 20px;
}

.page-title {
  font-size: 32px;
  font-weight: bold;
  color: #333;
  margin: 0;
}

.refresh-btn {
  background: #00897b;
  color: white;
  border: none;
  padding: 10px 20px;
  border-radius: 6px;
  cursor: pointer;
  font-size: 14px;
  font-weight: 600;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  gap: 5px;
}

.refresh-btn:hover:not(:disabled) {
  background: #00695c;
  transform: translateY(-1px);
}

.refresh-btn:disabled {
  background: #ccc;
  cursor: not-allowed;
  transform: none;
}

.header-right {
  display: flex;
  align-items: center;
  gap: 15px;
}

.language-selector,
.notification-icon,
.settings-icon,
.user-profile {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 8px 12px;
  background: white;
  border-radius: 6px;
  cursor: pointer;
  border: 1px solid #ddd;
}

.language-selector {
  gap: 8px;
}

.flag {
  font-size: 16px;
}

.dropdown-arrow {
  font-size: 12px;
  color: #666;
}

.notification-icon {
  position: relative;
  padding: 8px;
}

.notification-badge {
  position: absolute;
  top: -2px;
  right: -2px;
  width: 8px;
  height: 8px;
  background: #ff0000;
  border-radius: 50%;
}

.user-profile {
  background: #00897b;
  color: white;
  border: none;
}

/* COURSE BANNER */
.course-banner {
  background: linear-gradient(135deg, #9c27b0, #e91e63);
  color: white;
  padding: 30px;
  border-radius: 15px;
  margin-bottom: 30px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  position: relative;
  overflow: hidden;
}

.banner-content {
  flex: 1;
}

.course-title {
  font-size: 24px;
  font-weight: bold;
  margin: 0 0 15px 0;
}

.course-description {
  margin: 0;
  line-height: 1.6;
  max-width: 90%;
}

.banner-logo {
  font-size: 48px;
  opacity: 0.8;
}

/* OVERVIEW SECTION */
.overview-section {
  margin-bottom: 30px;
}

.section-title {
  font-size: 20px;
  font-weight: 600;
  color: #333;
  margin: 0 0 15px 0;
}

.overview-cards {
  display: flex;
  gap: 20px;
}

.overview-card {
  background: white;
  padding: 25px;
  border-radius: 10px;
  text-align: center;
  flex: 1;
  box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.card-icon {
  font-size: 36px;
  margin-bottom: 10px;
}

.card-number {
  font-size: 28px;
  font-weight: bold;
  margin: 0 0 5px 0;
}

.overview-card:nth-child(1) .card-icon,
.overview-card:nth-child(1) .card-number {
  color: #000000; /* Black accent */
}

.overview-card:nth-child(2) .card-icon,
.overview-card:nth-child(2) .card-number {
  color: #ffc107;
}

.overview-card:nth-child(3) .card-icon,
.overview-card:nth-child(3) .card-number {
  color: #4caf50;
}

.card-label {
  color: #666;
  font-weight: 500;
  margin: 0;
}

/* CONTENT GRID */
.content-grid {
  display: grid;
  grid-template-columns: 1fr 300px;
  gap: 30px;
}

/* ENROLLED MODULES */
.enrolled-modules {
  background: white;
  padding: 20px;
  border-radius: 10px;
  box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.module-cards {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 20px;
}

/* Loading and Empty States */
.loading-state {
  text-align: center;
  padding: 40px 20px;
  background: #f8f9fa;
  border-radius: 10px;
  border: 2px dashed #ddd;
}

.loading-spinner {
  font-size: 18px;
  color: #666;
  margin: 0;
}

.empty-state {
  text-align: center;
  padding: 40px 20px;
  background: #f8f9fa;
  border-radius: 10px;
  border: 2px dashed #ddd;
}

.empty-icon {
  font-size: 48px;
  margin-bottom: 15px;
  opacity: 0.5;
}

.empty-text {
  color: #666;
  margin: 0 0 20px 0;
  font-size: 16px;
}

.browse-courses-btn {
  background: #00897b;
  color: white;
  border: none;
  padding: 10px 20px;
  border-radius: 6px;
  cursor: pointer;
  font-size: 14px;
  font-weight: 600;
  transition: background 0.3s ease;
}

.browse-courses-btn:hover {
  background: #00695c;
}

/* Course Meta Info */
.course-meta {
  display: flex;
  gap: 8px;
  margin: 10px 0;
  flex-wrap: wrap;
}

.course-meta .duration,
.course-meta .level {
  background: #f0f0f0;
  color: #666;
  padding: 4px 8px;
  border-radius: 12px;
  font-size: 12px;
  font-weight: 500;
}

.course-meta .duration {
  background: #e3f2fd;
  color: #1976d2;
}

.course-meta .level {
  background: #f3e5f5;
  color: #7b1fa2;
}

.continue-learning-btn {
  width: 100%;
  background: linear-gradient(135deg, #00897b, #26a69a);
  color: white;
  border: none;
  padding: 10px 15px;
  border-radius: 6px;
  cursor: pointer;
  font-size: 14px;
  font-weight: 600;
  transition: all 0.3s ease;
  margin-top: 15px;
}

.continue-learning-btn:hover {
  background: linear-gradient(135deg, #00695c, #00897b);
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 137, 123, 0.3);
}

.module-card {
  text-align: center;
  padding: 20px;
  border-radius: 10px;
  background: #f8f9fa;
  cursor: default;
  transition: transform 0.2s;
}

.module-card:hover {
  transform: translateY(-2px);
}

.module-icon {
  font-size: 32px;
  margin-bottom: 10px;
}

.module-name {
  font-size: 16px;
  font-weight: 600;
  color: #333;
  margin: 0 0 10px 0;
}

.progress-bar {
  height: 8px;
  background: #e0e0e0;
  border-radius: 4px;
  margin: 10px 0;
  overflow: hidden;
}

.progress-fill {
  height: 100%;
  background: #00897b;
  border-radius: 4px;
  transition: width 0.3s ease;
}

.progress-text {
  font-size: 14px;
  color: #666;
  margin: 0;
}

/* RIGHT SIDEBAR */
.right-sidebar {
  display: flex;
  flex-direction: column;
  gap: 30px;
}

.upcoming-classes,
.leaderboard {
  background: white;
  padding: 20px;
  border-radius: 10px;
  box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.sidebar-title {
  font-size: 18px;
  font-weight: 600;
  color: #333;
  margin: 0 0 15px 0;
}

.classes-card {
  text-align: center;
  padding: 30px 20px;
  background: #f8f9fa;
  border-radius: 8px;
}

.no-classes-icon {
  font-size: 48px;
  margin-bottom: 15px;
  opacity: 0.5;
}

.no-classes-text {
  color: #666;
  margin: 0;
}

.leaderboard-card {
  background: #f8f9fa;
  border-radius: 8px;
  overflow: hidden;
}

.leaderboard-item {
  display: flex;
  align-items: center;
  padding: 12px 15px;
  border-bottom: 1px solid #e0e0e0;
}

.leaderboard-item:last-child {
  border-bottom: none;
}

.rank {
  width: 24px;
  height: 24px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 12px;
  font-weight: bold;
  margin-right: 10px;
  background: #ffc107;
  color: white;
}

.student-avatar {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  background: #00897b;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 12px;
  font-weight: bold;
  margin-right: 10px;
}

.student-info {
  flex: 1;
}

.student-name {
  font-weight: 500;
  color: #333;
  font-size: 14px;
}

.student-points {
  font-size: 12px;
  color: #666;
}

/* FLOATING ACTION BUTTONS */
.floating-buttons {
  position: fixed;
  bottom: 20px;
  right: 20px;
  display: flex;
  flex-direction: column;
  gap: 12px;
  z-index: 1000;
}

.floating-btn {
  background: none;
  border: none;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.3s ease;
  position: relative;
  padding: 8px;
}

.floating-btn:hover {
  transform: scale(1.2);
}

.btn-icon {
  font-size: 32px;
  pointer-events: none;
}

.btn-tooltip {
  position: absolute;
  right: 70px;
  background: #333;
  color: white;
  padding: 8px 12px;
  border-radius: 6px;
  font-size: 14px;
  white-space: nowrap;
  opacity: 0;
  visibility: hidden;
  transition: all 0.3s ease;
  pointer-events: none;
}

.btn-tooltip::after {
  content: '';
  position: absolute;
  left: 100%;
  top: 50%;
  transform: translateY(-50%);
  border: 6px solid transparent;
  border-left-color: #333;
}

.floating-btn:hover .btn-tooltip {
  opacity: 1;
  visibility: visible;
  right: 65px;
}

/* Remove specific button colors - just show emojis */
.accessibility-btn,
.message-btn,
.help-btn {
  background: none;
}

.accessibility-btn:hover,
.message-btn:hover,
.help-btn:hover {
  background: none;
}

/* MODAL STYLES */
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
  z-index: 2000;
}

.modal-content {
  background: white;
  border-radius: 12px;
  width: 90%;
  max-width: 600px;
  max-height: 80vh;
  overflow-y: auto;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px;
  border-bottom: 1px solid #eee;
}

.modal-header h2 {
  margin: 0;
  color: #333;
  font-size: 24px;
}

.close-btn {
  background: none;
  border: none;
  font-size: 24px;
  cursor: pointer;
  color: #666;
  padding: 5px;
  border-radius: 50%;
  width: 36px;
  height: 36px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.close-btn:hover {
  background: #f0f0f0;
}

.modal-body {
  padding: 20px;
}

.modal-footer {
  padding: 20px;
  border-top: 1px solid #eee;
  display: flex;
  justify-content: flex-start;
}

/* Accessibility Modal */
.accessibility-option {
  margin-bottom: 25px;
  padding: 15px;
  border: 1px solid #e0e0e0;
  border-radius: 8px;
}

.accessibility-option h3 {
  margin: 0 0 15px 0;
  color: #00897b;
  font-size: 18px;
}

.font-controls {
  display: flex;
  gap: 10px;
}

.font-btn {
  background: #00897b;
  color: white;
  border: none;
  padding: 8px 16px;
  border-radius: 4px;
  cursor: pointer;
  font-weight: bold;
}

.font-btn:hover {
  background: #00695c;
}

/* Toggle Switch */
.switch {
  position: relative;
  display: inline-block;
  width: 60px;
  height: 34px;
}

.switch input {
  opacity: 0;
  width: 0;
  height: 0;
}

.slider {
  position: absolute;
  cursor: pointer;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: #ccc;
  transition: .4s;
  border-radius: 34px;
}

.slider:before {
  position: absolute;
  content: "";
  height: 26px;
  width: 26px;
  left: 4px;
  bottom: 4px;
  background-color: white;
  transition: .4s;
  border-radius: 50%;
}

input:checked + .slider {
  background-color: #00897b;
}

input:checked + .slider:before {
  transform: translateX(26px);
}

/* FAQ Modal */
.faq-item {
  border-bottom: 1px solid #eee;
  margin-bottom: 10px;
}

.faq-question {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 15px;
  cursor: pointer;
  background: #f8f9fa;
  border-radius: 6px;
  transition: background 0.3s;
}

.faq-question:hover {
  background: #e9ecef;
}

.faq-question span:first-child {
  font-weight: 600;
  color: #333;
}

.faq-toggle {
  font-size: 20px;
  color: #00897b;
  font-weight: bold;
}

.faq-answer {
  padding: 15px;
  background: #f0f8ff;
  border-left: 4px solid #00897b;
  margin: 0 0 10px 0;
  line-height: 1.6;
  color: #333;
}

/* Help Form */
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
.form-group select,
.form-group textarea {
  width: 100%;
  padding: 12px;
  border: 1px solid #ddd;
  border-radius: 6px;
  font-size: 16px;
  box-sizing: border-box;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
  outline: none;
  border-color: #00897b;
  box-shadow: 0 0 0 2px rgba(0, 137, 123, 0.2);
}

.submit-btn {
  background: #00897b;
  color: white;
  border: none;
  padding: 12px 24px;
  border-radius: 6px;
  font-size: 16px;
  font-weight: 600;
  cursor: pointer;
  width: 100%;
}

.submit-btn:hover {
  background: #00695c;
}

/* High Contrast Mode */
:global(.high-contrast) {
  filter: contrast(1.5);
}

:global(.screen-reader) {
  /* Screen reader specific styles */
}
</style>
