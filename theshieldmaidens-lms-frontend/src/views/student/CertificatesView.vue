<template>
  <div class="certificates">
    <!-- LEFT SIDEBAR -->
    <div class="left-sidebar">
      <div class="logo" @click="goToDashboard">
        <img :src="PUBLIC_BRAND_LOGO" alt="The Shield Maidens" class="logo-image" />
        <span class="logo-text">Shield Maidens</span>
      </div>
      
      <nav class="nav-menu">
        <div class="nav-column">
          <div class="nav-item" @click="handleNavigation('/dashboard')">
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
          <div class="nav-item active" @click="handleNavigation('/certificates')">
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
          <div class="course-name">{{ currentUser?.name ?? currentUser?.username ?? 'Student' }}</div>
        </div>
      </div>
    </div>

    <!-- MAIN CONTENT -->
    <div class="main-content">
      <div class="page-header">
        <h1>Certificates</h1>
      </div>
      
      <div class="certificates-container">
        <!-- Loading state -->
        <div v-if="isLoading" class="loading-state">
          <div class="loading-spinner">⏳ Loading certificates...</div>
        </div>
        
        <!-- Empty state -->
        <div v-else-if="certificates.length === 0" class="empty-state">
          <div class="empty-icon">🏆</div>
          <p class="empty-text">No certificates yet.</p>
          <p class="empty-hint">Complete courses to earn your certificates!</p>
        </div>
        
        <!-- Certificates list -->
        <div v-else class="certificate-card" v-for="cert in certificates" :key="cert.id">
          <div class="certificate-icon">🏆</div>
          <h3>{{ cert.title }}</h3>
          <p class="completion-date">Completed: {{ cert.completedDate }}</p>
          <p class="certificate-id">Certificate ID: {{ cert.certificateId }}</p>
          <button class="download-btn">Download Certificate</button>
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
import axios from 'axios'
import { apiService } from '@/services/api'
import { PUBLIC_BRAND_LOGO } from '@/config/branding'
import { API_BASE_URL } from '@/config/api'

interface User {
  name?: string
  username?: string
}

interface Certificate {
  id: string | number
  title: string
  completedDate: string
  certificateId: string
}

const router = useRouter()

// State
const currentUser = ref<User | null>(null)
const certificates = ref<Certificate[]>([])
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
    question: 'How do I download certificates?',
    answer: 'Go to the Certificates section and click "Download Certificate" for any completed course. Certificates are available in PDF format.',
    open: false
  },
  {
    question: 'Are certificates shareable?',
    answer: 'Yes! You can share certificates on LinkedIn, add to your resume, or include in professional portfolios. Each certificate has a unique verification code.',
    open: false
  },
  {
    question: 'How long are certificates valid?',
    answer: 'Certificates are valid indefinitely and show the completion date. They serve as proof of course completion and skill achievement.',
    open: false
  }
])

const handleNavigation = (route: string) => {
  router.push(route)
}

const goToDashboard = () => {
  router.push('/dashboard')
}

const loadCurrentUser = () => {
  try {
    const user = apiService.getCurrentUserFromStorage()
    currentUser.value = user
  } catch (error) {
    console.error('Error loading current user:', error)
  }
}

const loadCertificates = async () => {
  certificates.value = []
  try {
    const token = localStorage.getItem('token')
    if (!token) return
    const { data } = await axios.get(`${API_BASE_URL}/student/certificates`, {
      headers: { Authorization: `Bearer ${token}` },
    })
    certificates.value = (Array.isArray(data) ? data : []) as Certificate[]
  } catch (error) {
    console.error('Error loading certificates:', error)
    certificates.value = []
  }
}

onMounted(async () => {
  loadCurrentUser()
  await loadCertificates()
  isLoading.value = false
})

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
</script>

<style scoped>
.certificates {
  display: flex;
  min-height: 100dvh;
  background: #f5f5f5;
}

/* LEFT SIDEBAR */
.left-sidebar {
  width: 250px;
  background: #000000;
  color: white;
  padding: 20px;
  min-height: 100dvh;
  position: fixed;
  left: 0;
  top: 0;
  z-index: 1000;
  overflow-y: auto;
  display: flex;
  flex-direction: column;
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

.logo-text {
  font-size: 18px;
  font-weight: bold;
  color: #ffffff;
}

.logo-image {
  height: 36px;
  width: auto;
  object-fit: contain;
  flex-shrink: 0;
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
  border-bottom: 1px solid #333333;
}

.nav-item:hover {
  background-color: rgba(255, 255, 255, 0.1);
}

.nav-item.active {
  background-color: #333333;
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

/* MAIN CONTENT */
.main-content {
  flex: 1;
  margin-left: 250px;
  padding: 20px;
  overflow-y: auto;
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
  margin: 0 0 10px 0;
  font-size: 16px;
}

.empty-hint {
  color: #999;
  margin: 0;
  font-size: 14px;
}

.page-header {
  display: flex;
  align-items: center;
  gap: 20px;
  margin-bottom: 30px;
}

.back-btn {
  background: #00897b;
  color: white;
  border: none;
  padding: 8px 16px;
  border-radius: 4px;
  cursor: pointer;
  font-size: 14px;
}

.back-btn:hover {
  background: #00695c;
}

.page-header h1 {
  margin: 0;
  color: #333;
}

.certificates-container {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 20px;
}

.certificate-card {
  background: white;
  padding: 20px;
  border-radius: 10px;
  box-shadow: 0 2px 10px rgba(0,0,0,0.1);
  text-align: center;
}

.certificate-icon {
  font-size: 48px;
  margin-bottom: 15px;
}

.certificate-card h3 {
  margin: 0 0 10px 0;
  color: #00897b;
}

.completion-date {
  color: #666;
  margin: 5px 0;
  font-size: 14px;
}

.certificate-id {
  color: #333;
  margin: 10px 0;
  font-size: 12px;
  font-family: monospace;
}

.download-btn {
  background: #00897b;
  color: white;
  border: none;
  padding: 10px 20px;
  border-radius: 4px;
  cursor: pointer;
  margin-top: 15px;
}

.download-btn:hover {
  background: #00695c;
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
  line-height: 1.5;
}
</style>
