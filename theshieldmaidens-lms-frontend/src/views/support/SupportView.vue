<template>
  <div class="support-page">
    <!-- Support Options -->
    <div class="support-options">
      <div class="support-grid">
        <!-- Contact Support -->
        <div class="support-card">
          <div class="card-icon">
            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M22 16.92v3a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2v-3"></path>
              <path d="M22 11.08V12a10 10 0 1 0-20 0v-1a10 10 0 1 0 20 0v1.08"></path>
              <circle cx="12" cy="11" r="3"></circle>
            </svg>
          </div>
          <h2>Contact Support</h2>
          <p>Get help from our support team via email or phone</p>
          <div class="contact-info">
            <div class="contact-item">
              <span class="contact-label">Email:</span>
              <a href="mailto:support@theshieldmaidens.org" class="contact-value">support@theshieldmaidens.org</a>
            </div>
            <div class="contact-item">
              <span class="contact-label">Phone:</span>
              <a href="tel:+254702997534" class="contact-value">+254 702 997534</a>
            </div>
            <div class="contact-item">
              <span class="contact-label">Emergency:</span>
              <a href="tel:1195" class="contact-value emergency">1195</a>
            </div>
          </div>
          <button @click="openContactForm" class="contact-btn">Send Message</button>
        </div>

        <!-- FAQ -->
        <div class="support-card">
          <div class="card-icon">
            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="12" cy="12" r="10"></circle>
              <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2 1 4 3 4s3-2 3-4 3a3 3 0 0 1-5.83-1c0-2-1-4-3-4s-3 2-3 4-3z"></path>
              <line x1="12" y1="1" x2="12" y2="6"></line>
            </svg>
          </div>
          <h2>Frequently Asked Questions</h2>
          <p>Find quick answers to common questions</p>
          <div class="faq-list">
            <div v-for="faq in filteredFAQs" :key="faq.id" class="faq-item">
              <div class="faq-question" @click="toggleFAQ(faq)">
                <h4>{{ faq.question }}</h4>
                <span class="faq-toggle">{{ faq.isOpen ? '−' : '+' }}</span>
              </div>
              <div v-show="faq.isOpen" class="faq-answer">
                <p>{{ faq.answer }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Contact Form Modal -->
    <div v-if="showContactForm" class="modal-overlay" @click="closeContactForm">
      <div class="contact-modal" @click.stop>
        <div class="modal-header">
          <h3>Contact Support</h3>
          <button @click="closeContactForm" class="close-btn">✕</button>
        </div>
        
        <form @submit.prevent="submitContactForm" class="contact-form">
          <div class="form-group">
            <label>Your Name</label>
            <input v-model="contactForm.name" type="text" class="form-input" required>
          </div>
          
          <div class="form-group">
            <label>Email Address</label>
            <input v-model="contactForm.email" type="email" class="form-input" required>
          </div>
          
          <div class="form-group">
            <label>Subject</label>
            <select v-model="contactForm.subject" class="form-select" required>
              <option value="">Select a subject</option>
              <option value="technical">Technical Issue</option>
              <option value="course">Course Question</option>
              <option value="billing">Billing/Payment</option>
              <option value="account">Account Issue</option>
              <option value="other">Other</option>
            </select>
          </div>
          
          <div class="form-group">
            <label>Message</label>
            <textarea v-model="contactForm.message" class="form-textarea" rows="5" required></textarea>
          </div>
          
          <div class="form-actions">
            <button type="button" @click="closeContactForm" class="cancel-btn">Cancel</button>
            <button type="submit" class="submit-btn" :disabled="isSubmitting">
              {{ isSubmitting ? 'Sending...' : 'Send Message' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'

// State
const selectedCategory = ref('general')
const showContactForm = ref(false)
const isSubmitting = ref(false)

// Contact form
const contactForm = ref({
  name: '',
  email: '',
  subject: '',
  message: ''
})

// FAQ data - simplified
const faqs = ref([
  {
    id: 1,
    question: 'How do I reset my password?',
    answer: 'Click on the "Forgot Password" link on the login page. Enter your email address and we\'ll send you instructions to reset your password.',
    isOpen: false
  },
  {
    id: 2,
    question: 'How do I enroll in a course?',
    answer: 'Browse our course catalog, click on a course you\'re interested in, and click the "Enroll Now" button. You\'ll need to be logged in to enroll.',
    isOpen: false
  },
  {
    id: 3,
    question: 'What browsers are supported?',
    answer: 'We support the latest versions of Chrome, Firefox, Safari, and Edge. For the best experience, we recommend using Chrome or Firefox.',
    isOpen: false
  },
  {
    id: 4,
    question: 'How do I contact support?',
    answer: 'You can reach us via email at support@theshieldmaidens.org or call us at +254 702 997534. For emergencies, dial 1195.',
    isOpen: false
  }
])

// Computed properties
const filteredFAQs = computed(() => {
  return faqs.value
})

// Methods
const toggleFAQ = (faq) => {
  faq.isOpen = !faq.isOpen
}

const openContactForm = () => {
  showContactForm.value = true
}

const closeContactForm = () => {
  showContactForm.value = false
  contactForm.value = {
    name: '',
    email: '',
    subject: '',
    message: ''
  }
}

const submitContactForm = async () => {
  isSubmitting.value = true
  
  try {
    // Simulate API call
    await new Promise(resolve => setTimeout(resolve, 1000))
    
    alert('Your message has been sent successfully! We\'ll respond within 24 hours.')
    closeContactForm()
  } catch (error) {
    alert('Failed to send message. Please try again.')
  } finally {
    isSubmitting.value = false
  }
}

onMounted(() => {
  console.log('Support page mounted')
})
</script>

<style scoped>
.support-page {
  padding: 2rem;
  max-width: 1400px;
  margin: 0 auto;
  background: linear-gradient(135deg, #f7fafc 0%, #e2e8f0 100%);
  min-height: 100vh;
}

/* Header */
.support-header {
  text-align: center;
  margin-bottom: 3rem;
  padding: 2rem 0;
  background: #f8f9fa;
  border-radius: 12px;
  color: #1a365d;
}

.support-header h1 {
  font-size: 2rem;
  font-weight: 600;
  margin-bottom: 0;
}

/* Support Options */
.support-options {
  margin-bottom: 3rem;
}

.support-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
  gap: 2rem;
}

.support-card {
  background: white;
  border-radius: 12px;
  padding: 2rem;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
  transition: transform 0.2s ease;
}

.support-card:hover {
  transform: translateY(-2px);
}

.card-icon {
  width: 48px;
  height: 48px;
  margin: 0 auto 1.5rem;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #e9ecef;
  border-radius: 12px;
  color: #6c757d;
}

.support-card h2 {
  font-size: 1.3rem;
  font-weight: 600;
  color: #1a365d;
  margin-bottom: 0.5rem;
  text-align: center;
}

.support-card p {
  color: #6c757d;
  margin-bottom: 1.5rem;
  text-align: center;
  font-size: 0.9rem;
}

/* Contact Support */
.contact-info {
  margin-bottom: 1.5rem;
}

.contact-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.75rem;
  background: #f8f9fa;
  border-radius: 6px;
  margin-bottom: 0.5rem;
}

.contact-label {
  font-weight: 600;
  color: #1a365d;
}

.contact-value {
  color: #ff9900;
  text-decoration: none;
  font-weight: 500;
}

.contact-value:hover {
  text-decoration: underline;
}

.contact-value.emergency {
  color: #dc3545;
  font-weight: 700;
}

.contact-btn {
  width: 100%;
  background: #6c757d;
  color: white;
  border: none;
  padding: 0.75rem;
  border-radius: 6px;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  transition: background-color 0.2s ease;
}

.contact-btn:hover {
  background: #5a6268;
}

/* FAQ */
.faq-list {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.faq-item {
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  overflow: hidden;
}

.faq-question {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem;
  background: #f8f9fa;
  cursor: pointer;
  transition: background-color 0.2s ease;
}

.faq-question:hover {
  background: #e9ecef;
}

.faq-question h4 {
  font-size: 0.9rem;
  font-weight: 600;
  color: #1a365d;
  margin: 0;
}

.faq-toggle {
  font-size: 1.2rem;
  color: #6c757d;
  font-weight: 600;
}

.faq-answer {
  padding: 1rem;
  background: white;
  border-top: 1px solid #e2e8f0;
}

.faq-answer p {
  margin: 0;
  color: #495057;
  line-height: 1.6;
}

/* Resources */
.resources-list {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.resource-item {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1rem;
  background: #f8f9fa;
  border-radius: 8px;
  transition: background-color 0.2s ease;
}

.resource-item:hover {
  background: #e9ecef;
}

.resource-icon {
  font-size: 1.5rem;
  width: 40px;
  height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: white;
  border-radius: 8px;
}

.resource-info {
  flex: 1;
}

.resource-info h4 {
  font-size: 0.9rem;
  font-weight: 600;
  color: #1a365d;
  margin-bottom: 0.25rem;
}

.resource-info p {
  font-size: 0.8rem;
  color: #6c757d;
  margin: 0;
}

.resource-btn {
  background: #ff9900;
  color: white;
  border: none;
  padding: 0.5rem 1rem;
  border-radius: 6px;
  font-size: 0.8rem;
  font-weight: 600;
  cursor: pointer;
  transition: background-color 0.2s ease;
}

.resource-btn:hover {
  background: #ff6b00;
}

/* Community */
.community-options {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.community-item {
  padding: 1rem;
  background: #f8f9fa;
  border-radius: 8px;
}

.community-item h4 {
  font-size: 1rem;
  font-weight: 600;
  color: #1a365d;
  margin-bottom: 0.5rem;
}

.community-item p {
  font-size: 0.8rem;
  color: #6c757d;
  margin-bottom: 1rem;
}

.community-btn {
  background: #28a745;
  color: white;
  border: none;
  padding: 0.5rem 1rem;
  border-radius: 6px;
  font-size: 0.8rem;
  font-weight: 600;
  cursor: pointer;
  transition: background-color 0.2s ease;
}

.community-btn:hover {
  background: #218838;
}

/* Contact Form Modal */
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
  z-index: 1000;
}

.contact-modal {
  background: white;
  border-radius: 16px;
  padding: 2rem;
  max-width: 500px;
  width: 90%;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
}

.modal-header h3 {
  font-size: 1.5rem;
  font-weight: 600;
  color: #1a365d;
}

.close-btn {
  background: transparent;
  border: none;
  font-size: 1.5rem;
  cursor: pointer;
  color: #6c757d;
  padding: 0.5rem;
}

.contact-form {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.form-group {
  display: flex;
  flex-direction: column;
}

.form-group label {
  font-weight: 600;
  color: #1a365d;
  margin-bottom: 0.5rem;
}

.form-input, .form-select, .form-textarea {
  padding: 0.75rem;
  border: 1px solid #e2e8f0;
  border-radius: 6px;
  font-size: 1rem;
  outline: none;
}

.form-input:focus, .form-select:focus, .form-textarea:focus {
  border-color: #ff9900;
  box-shadow: 0 0 0 3px rgba(255, 153, 0, 0.1);
}

.form-textarea {
  resize: vertical;
}

.form-actions {
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
}

.cancel-btn, .submit-btn {
  padding: 0.75rem 1.5rem;
  border-radius: 6px;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  transition: background-color 0.2s ease;
}

.cancel-btn {
  background: #6c757d;
  color: white;
  border: none;
}

.cancel-btn:hover {
  background: #5a6268;
}

.submit-btn {
  background: #ff9900;
  color: white;
  border: none;
}

.submit-btn:hover:not(:disabled) {
  background: #ff6b00;
}

.submit-btn:disabled {
  background: #6c757d;
  cursor: not-allowed;
}

/* Responsive Design */
@media (max-width: 768px) {
  .support-page {
    padding: 1rem;
  }
  
  .support-header h1 {
    font-size: 2rem;
  }
  
  .support-grid {
    grid-template-columns: 1fr;
    gap: 1.5rem;
  }
  
  .faq-categories {
    justify-content: center;
  }
  
  .contact-item {
    flex-direction: column;
    align-items: flex-start;
    gap: 0.5rem;
  }
  
  .resource-item {
    flex-direction: column;
    text-align: center;
  }
  
  .form-actions {
    flex-direction: column;
  }
}
</style>
