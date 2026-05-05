<template>
  <div class="groups">
    <div class="page-header">
      <button @click="goBack" class="back-btn">
        ← Back to Dashboard
      </button>
      <h1>Groups</h1>
    </div>
    
    <div class="groups-container">
      <div class="group-card">
        <h3>Digital Safety Group</h3>
        <p class="group-members">25 members</p>
        <p class="group-description">Learn about online safety and privacy best practices</p>
        <button class="join-btn">Join Group</button>
      </div>
      
      <div class="group-card">
        <h3>Cybersecurity Basics</h3>
        <p class="group-members">18 members</p>
        <p class="group-description">Fundamental cybersecurity concepts and practices</p>
        <button class="join-btn">Join Group</button>
      </div>
      
      <div class="group-card">
        <h3>Privacy Champions</h3>
        <p class="group-members">32 members</p>
        <p class="group-description">Advanced privacy protection techniques</p>
        <button class="joined-btn">Already Joined</button>
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
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()

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
    question: 'How do I join a study group?',
    answer: 'Click the "Join Group" button on any group card. You\'ll receive notifications about group activities and discussions.',
    open: false
  },
  {
    question: 'Can I create my own group?',
    answer: 'Yes! Contact your instructor or admin to request permission to create a new study group. Groups should have a clear learning objective.',
    open: false
  },
  {
    question: 'What can I do in groups?',
    answer: 'In groups, you can collaborate on assignments, share resources, discuss topics, and participate in group activities led by your peers.',
    open: false
  }
])

const goBack = () => {
  router.push('/dashboard')
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
</script>

<style>
.groups {
  padding: 20px;
  padding-bottom: 2rem;
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

.groups-container {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 20px;
}

.group-card {
  background: white;
  padding: 20px;
  border-radius: 10px;
  box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.group-card h3 {
  margin: 0 0 10px 0;
  color: #00897b;
}

.group-members {
  color: #666;
  margin: 5px 0;
  font-size: 14px;
}

.group-description {
  margin: 10px 0;
  color: #333;
}

.join-btn, .joined-btn {
  background: #00897b;
  color: white;
  border: none;
  padding: 10px 20px;
  border-radius: 4px;
  cursor: pointer;
  margin-top: 10px;
}

.joined-btn {
  background: #6c757d;
  cursor: not-allowed;
}

.join-btn:hover {
  background: #00695c;
}
</style>

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
