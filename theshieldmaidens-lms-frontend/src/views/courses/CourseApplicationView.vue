<template>
  <div class="application-page">
    <div class="application-container">
      <div class="application-header">
        <h1>Apply for Course Access</h1>
        <p>Complete this form to apply for access to our courses. Our admin team will review your application and create an account for you.</p>
      </div>

      <form @submit.prevent="submitApplication" class="application-form">
        <!-- Personal Information -->
        <div class="form-section">
          <h2>Personal Information</h2>
          <div class="form-grid">
            <div class="form-group">
              <label for="first_name">First Name *</label>
              <input
                id="first_name"
                v-model="application.first_name"
                type="text"
                placeholder="Enter your first name"
                required
                class="form-control"
              >
            </div>

            <div class="form-group">
              <label for="last_name">Last Name *</label>
              <input
                id="last_name"
                v-model="application.last_name"
                type="text"
                placeholder="Enter your last name"
                required
                class="form-control"
              >
            </div>

            <div class="form-group">
              <label for="email">Email Address *</label>
              <input
                id="email"
                v-model="application.email"
                type="email"
                placeholder="Enter your email address"
                required
                class="form-control"
              >
            </div>

            <div class="form-group">
              <label for="phone">Phone Number</label>
              <input
                id="phone"
                v-model="application.phone"
                type="tel"
                placeholder="Enter your phone number"
                class="form-control"
              >
            </div>

            <div class="form-group">
              <label for="date_of_birth">Date of Birth *</label>
              <input
                id="date_of_birth"
                v-model="application.date_of_birth"
                type="date"
                required
                class="form-control"
              >
            </div>

            <div class="form-group">
              <label for="gender">Gender</label>
              <select id="gender" v-model="application.gender" class="form-control">
                <option value="">Select Gender</option>
                <option value="male">Male</option>
                <option value="female">Female</option>
                <option value="other">Other</option>
                <option value="prefer_not_to_say">Prefer not to say</option>
              </select>
            </div>
          </div>
        </div>

        <!-- Course Selection -->
        <div class="form-section">
          <h2>Course Selection</h2>
          <div class="course-selection">
            <label class="checkbox-label">Select the courses you're interested in:</label>
            <div class="courses-grid">
              <div 
                v-for="course in availableCourses" 
                :key="course.id"
                class="course-checkbox"
              >
                <label class="course-item">
                  <input
                    type="checkbox"
                    :value="course.id"
                    v-model="application.selected_courses"
                    class="course-checkbox-input"
                  >
                  <div class="course-info">
                    <h4>{{ course.title }}</h4>
                    <p>{{ course.description }}</p>
                    <span class="course-duration">{{ course.duration }}</span>
                    <span class="course-price">{{ course.price === 0 ? 'FREE' : `$${course.price}` }}</span>
                  </div>
                </label>
              </div>
            </div>
          </div>
        </div>

        <!-- Educational Background -->
        <div class="form-section">
          <h2>Educational Background</h2>
          <div class="form-grid">
            <div class="form-group">
              <label for="education_level">Current Education Level *</label>
              <select id="education_level" v-model="application.education_level" required class="form-control">
                <option value="">Select Education Level</option>
                <option value="primary">Primary School</option>
                <option value="secondary">Secondary School</option>
                <option value="high_school">High School</option>
                <option value="college">College/University</option>
                <option value="other">Other</option>
              </select>
            </div>

            <div class="form-group">
              <label for="school_name">School/Institution Name *</label>
              <input
                id="school_name"
                v-model="application.school_name"
                type="text"
                placeholder="Enter your school name"
                required
                class="form-control"
              >
            </div>
          </div>
        </div>

        <!-- Additional Information -->
        <div class="form-section">
          <h2>Additional Information</h2>
          <div class="form-group full-width">
            <label for="motivation">Why do you want to join these courses? *</label>
            <textarea
              id="motivation"
              v-model="application.motivation"
              placeholder="Tell us why you're interested in these courses and what you hope to achieve..."
              required
              rows="4"
              class="form-control"
            ></textarea>
          </div>

          <div class="form-group full-width">
            <label for="special_needs">Do you have any special needs or requirements?</label>
            <textarea
              id="special_needs"
              v-model="application.special_needs"
              placeholder="Please let us know if you need any special accommodations..."
              rows="3"
              class="form-control"
            ></textarea>
          </div>
        </div>

        <!-- Terms and Conditions -->
        <div class="form-section">
          <div class="form-group full-width">
            <label class="checkbox-container">
              <input
                type="checkbox"
                v-model="application.agree_terms"
                required
                class="checkbox-input"
              >
              <span class="checkmark"></span>
              I agree to the terms and conditions and understand that my application will be reviewed by the admin team. I will receive login credentials via email if approved.
            </label>
          </div>
        </div>

        <!-- Submit Button -->
        <div class="form-actions">
          <button type="submit" class="btn btn-primary" :disabled="isSubmitting || !isFormValid">
            <span v-if="isSubmitting">Submitting Application...</span>
            <span v-else>Submit Application</span>
          </button>
          
          <router-link to="/courses" class="btn btn-outline">
            Back to Courses
          </router-link>
        </div>

        <!-- Success/Error Messages -->
        <div v-if="successMessage" class="success-message">
          {{ successMessage }}
        </div>

        <div v-if="errorMessage" class="error-message">
          {{ errorMessage }}
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';

const router = useRouter();

const API_BASE_URL = localStorage.getItem('apiBaseUrl') || 'http://localhost:3000/api';

const application = ref({
  first_name: '',
  last_name: '',
  email: '',
  phone: '',
  date_of_birth: '',
  gender: '',
  selected_courses: [],
  education_level: '',
  school_name: '',
  motivation: '',
  special_needs: '',
  agree_terms: false
});

const availableCourses = ref([]);
const isSubmitting = ref(false);
const successMessage = ref('');
const errorMessage = ref('');

const isFormValid = computed(() => {
  return application.value.first_name &&
         application.value.last_name &&
         application.value.email &&
         application.value.date_of_birth &&
         application.value.education_level &&
         application.value.school_name &&
         application.value.motivation &&
         application.value.selected_courses.length > 0 &&
         application.value.agree_terms;
});

const fetchCourses = async () => {
  try {
    const response = await axios.get(`${API_BASE_URL}/courses`);
    availableCourses.value = response.data.map(course => ({
      ...course,
      selected: false
    }));
  } catch (error) {
    console.error('Error fetching courses:', error);
  }
};

const submitApplication = async () => {
  if (isSubmitting.value || !isFormValid.value) return;
  
  isSubmitting.value = true;
  errorMessage.value = '';
  successMessage.value = '';
  
  try {
    const response = await axios.post(`${API_BASE_URL}/course-applications`, application.value);
    
    successMessage.value = 'Application submitted successfully! Our admin team will review your application and contact you with login credentials within 2-3 business days.';
    
    // Reset form
    application.value = {
      first_name: '',
      last_name: '',
      email: '',
      phone: '',
      date_of_birth: '',
      gender: '',
      selected_courses: [],
      education_level: '',
      school_name: '',
      motivation: '',
      special_needs: '',
      agree_terms: false
    };
    
    // Scroll to top to show success message
    window.scrollTo({ top: 0, behavior: 'smooth' });
    
  } catch (error) {
    errorMessage.value = error.response?.data?.message || 'Failed to submit application. Please try again.';
  } finally {
    isSubmitting.value = false;
  }
};

onMounted(() => {
  fetchCourses();
});
</script>

<style scoped>
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
}

.application-page {
  min-height: 100vh;
  background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
  padding: 2rem 1rem;
}

.application-container {
  max-width: 800px;
  margin: 0 auto;
  background: white;
  border-radius: 16px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
  overflow: hidden;
}

.application-header {
  background: linear-gradient(135deg, #e68c07ff 0%, #cc8809ff 100%);
  color: white;
  padding: 2.5rem;
  text-align: center;
}

.application-header h1 {
  font-size: 2rem;
  font-weight: 700;
  margin-bottom: 1rem;
}

.application-header p {
  font-size: 1rem;
  opacity: 0.9;
  line-height: 1.6;
}

.application-form {
  padding: 2.5rem;
}

.form-section {
  margin-bottom: 2.5rem;
}

.form-section h2 {
  font-size: 1.5rem;
  font-weight: 600;
  color: #1f2937;
  margin-bottom: 1.5rem;
  padding-bottom: 0.5rem;
  border-bottom: 2px solid #e5e7eb;
}

.form-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 1.5rem;
}

.form-group {
  display: flex;
  flex-direction: column;
}

.form-group.full-width {
  grid-column: 1 / -1;
}

.form-group label {
  font-weight: 500;
  color: #374151;
  margin-bottom: 0.5rem;
  font-size: 0.9rem;
}

.form-control {
  padding: 0.75rem;
  border: 1px solid #d1d5db;
  border-radius: 8px;
  font-size: 0.9rem;
  transition: border-color 0.3s ease, box-shadow 0.3s ease;
}

.form-control:focus {
  outline: none;
  border-color: #e68c07ff;
  box-shadow: 0 0 0 3px rgba(230, 140, 7, 0.1);
}

textarea.form-control {
  resize: vertical;
  min-height: 100px;
}

.course-selection {
  margin-top: 1rem;
}

.checkbox-label {
  font-weight: 500;
  color: #374151;
  margin-bottom: 1rem;
  display: block;
}

.courses-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 1rem;
}

.course-checkbox {
  border: 1px solid #e5e7eb;
  border-radius: 12px;
  overflow: hidden;
  transition: all 0.3s ease;
}

.course-checkbox:hover {
  border-color: #e68c07ff;
  box-shadow: 0 4px 12px rgba(230, 140, 7, 0.15);
}

.course-item {
  display: flex;
  align-items: flex-start;
  padding: 1rem;
  cursor: pointer;
  gap: 1rem;
}

.course-checkbox-input {
  margin-top: 0.25rem;
  transform: scale(1.2);
}

.course-info h4 {
  font-size: 1rem;
  font-weight: 600;
  color: #1f2937;
  margin-bottom: 0.5rem;
}

.course-info p {
  font-size: 0.85rem;
  color: #6b7280;
  margin-bottom: 0.5rem;
  line-height: 1.4;
}

.course-duration,
.course-price {
  font-size: 0.8rem;
  color: #9ca3af;
  margin-right: 1rem;
}

.course-price {
  font-weight: 600;
  color: #e68c07ff;
}

.checkbox-container {
  display: flex;
  align-items: flex-start;
  gap: 0.75rem;
  cursor: pointer;
  font-size: 0.9rem;
  color: #4b5563;
  line-height: 1.5;
}

.checkbox-input {
  margin-top: 0.25rem;
  transform: scale(1.2);
}

.form-actions {
  display: flex;
  gap: 1rem;
  justify-content: center;
  margin-top: 2rem;
}

.btn {
  padding: 0.875rem 2rem;
  border-radius: 8px;
  font-weight: 600;
  font-size: 0.9rem;
  cursor: pointer;
  transition: all 0.3s ease;
  text-decoration: none;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border: none;
}

.btn-primary {
  background: linear-gradient(135deg, #e68c07ff 0%, #cc8809ff 100%);
  color: white;
}

.btn-primary:hover:not(:disabled) {
  background: linear-gradient(135deg, #1d1d1fff 0%, #080808ff 100%);
}

.btn-outline {
  background: transparent;
  color: #e68c07ff;
  border: 2px solid #e68c07ff;
}

.btn-outline:hover {
  background: #e68c07ff;
  color: white;
}

.btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.success-message {
  background: #d1fae5;
  border: 1px solid #a7f3d0;
  color: #065f46;
  padding: 1rem;
  border-radius: 8px;
  margin-top: 1rem;
  text-align: center;
}

.error-message {
  background: #fee2e2;
  border: 1px solid #fca5a5;
  color: #b91c1c;
  padding: 1rem;
  border-radius: 8px;
  margin-top: 1rem;
  text-align: center;
}

@media (max-width: 768px) {
  .application-container {
    margin: 1rem;
  }
  
  .application-header {
    padding: 2rem 1.5rem;
  }
  
  .application-form {
    padding: 2rem 1.5rem;
  }
  
  .form-grid {
    grid-template-columns: 1fr;
  }
  
  .courses-grid {
    grid-template-columns: 1fr;
  }
  
  .form-actions {
    flex-direction: column;
  }
  
  .btn {
    width: 100%;
  }
}
</style>
