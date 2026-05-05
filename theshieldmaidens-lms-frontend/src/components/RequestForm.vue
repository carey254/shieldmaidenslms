<template>
  <div class="request-form">
    <div class="form-header">
      <h3><i class="fas fa-paper-plane"></i> Send Request to Admin</h3>
      <p>Submit a request to the system administrator</p>
    </div>

    <form @submit.prevent="submitRequest" class="request-form-content">
      <div class="form-group">
        <label for="request-type">Request Type</label>
        <select v-model="form.type" id="request-type" class="form-select" required>
          <option value="">Select request type</option>
          <option value="course">Course Access</option>
          <option value="role">Role Change</option>
          <option value="access">System Access</option>
          <option value="support">Technical Support</option>
          <option value="other">Other</option>
        </select>
      </div>

      <div class="form-group">
        <label for="request-title">Title</label>
        <input
          v-model="form.title"
          type="text"
          id="request-title"
          class="form-input"
          placeholder="Enter request title"
          required
        />
      </div>

      <div class="form-group">
        <label for="request-description">Description</label>
        <textarea
          v-model="form.description"
          id="request-description"
          class="form-textarea"
          rows="4"
          placeholder="Provide detailed information about your request..."
          required
        ></textarea>
      </div>

      <div class="form-group">
        <label for="request-priority">Priority</label>
        <select v-model="form.priority" id="request-priority" class="form-select">
          <option value="low">Low</option>
          <option value="medium" selected>Medium</option>
          <option value="high">High</option>
          <option value="urgent">Urgent</option>
        </select>
      </div>

      <div class="form-actions">
        <button type="submit" class="btn btn-primary" :disabled="submitting">
          <i class="fas fa-paper-plane"></i>
          {{ submitting ? 'Sending...' : 'Send Request' }}
        </button>
        <button type="button" @click="resetForm" class="btn btn-secondary">
          <i class="fas fa-times"></i>
          Clear
        </button>
      </div>
    </form>

    <!-- Success Message -->
    <div v-if="successMessage" class="alert alert-success">
      <i class="fas fa-check-circle"></i>
      {{ successMessage }}
    </div>

    <!-- Error Message -->
    <div v-if="errorMessage" class="alert alert-error">
      <i class="fas fa-exclamation-circle"></i>
      {{ errorMessage }}
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { useAuthStore } from '@/stores/auth'

const authStore = useAuthStore()

// Form state
const form = reactive({
  type: '',
  title: '',
  description: '',
  priority: 'medium'
})

const submitting = ref(false)
const successMessage = ref('')
const errorMessage = ref('')

// Methods
const submitRequest = async () => {
  submitting.value = true
  successMessage.value = ''
  errorMessage.value = ''

  try {
    const response = await fetch('/api/user/requests', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${authStore.token}`
      },
      body: JSON.stringify(form)
    })

    const data = await response.json()

    if (response.ok) {
      successMessage.value = 'Request submitted successfully! The admin will review it soon.'
      resetForm()
      
      // Log activity
      logActivity('Request Sent', `Sent ${form.type} request: ${form.title}`)
    } else {
      errorMessage.value = data.message || 'Failed to submit request. Please try again.'
    }
  } catch (error) {
    console.error('Error submitting request:', error)
    errorMessage.value = 'Network error. Please try again.'
  } finally {
    submitting.value = false
  }
}

const resetForm = () => {
  form.type = ''
  form.title = ''
  form.description = ''
  form.priority = 'medium'
  successMessage.value = ''
  errorMessage.value = ''
}

const logActivity = (title, description) => {
  // This would typically call an API to log the activity
  console.log('Activity:', title, description)
}
</script>

<style scoped>
.request-form {
  background: #fff;
  border-radius: 8px;
  padding: 1.5rem;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  border-left: 4px solid #ff9900;
}

.form-header {
  margin-bottom: 1.5rem;
  padding-bottom: 1rem;
  border-bottom: 1px solid #eee;
}

.form-header h3 {
  color: #333;
  font-size: 1.25rem;
  font-weight: 600;
  margin-bottom: 0.5rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.form-header h3 i {
  color: #ff9900;
}

.form-header p {
  color: #666;
  font-size: 0.875rem;
  margin: 0;
}

.form-group {
  margin-bottom: 1.25rem;
}

.form-group label {
  display: block;
  margin-bottom: 0.5rem;
  color: #333;
  font-weight: 500;
  font-size: 0.875rem;
}

.form-input,
.form-select,
.form-textarea {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid #ddd;
  border-radius: 6px;
  font-size: 0.875rem;
  transition: border-color 0.2s;
}

.form-input:focus,
.form-select:focus,
.form-textarea:focus {
  outline: none;
  border-color: #ff9900;
  box-shadow: 0 0 0 2px rgba(255, 153, 0, 0.2);
}

.form-textarea {
  resize: vertical;
  min-height: 100px;
}

.form-actions {
  display: flex;
  gap: 1rem;
  margin-top: 1.5rem;
}

.btn {
  padding: 0.75rem 1.5rem;
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
  color: #fff;
}

.btn-primary:hover:not(:disabled) {
  background: #e68a00;
}

.btn-primary:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.btn-secondary {
  background: #333;
  color: #fff;
}

.btn-secondary:hover {
  background: #555;
}

.alert {
  padding: 0.75rem 1rem;
  border-radius: 6px;
  margin-top: 1rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.875rem;
}

.alert-success {
  background: #d4edda;
  color: #155724;
  border: 1px solid #c3e6cb;
}

.alert-error {
  background: #f8d7da;
  color: #721c24;
  border: 1px solid #f5c6cb;
}

.alert i {
  font-size: 1rem;
}
</style>
