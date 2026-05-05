<template>
  <div class="announcements-page">
    <div class="page-header">
      <h1>Student Announcements & Opportunities</h1>
      <p>Share important information, free programs, mentorship opportunities, and educational resources with students</p>
    </div>

    <!-- Create New Announcement -->
    <div class="create-section">
      <h2>Create New Announcement</h2>
      <form @submit.prevent="createAnnouncement" class="announcement-form">
        <div class="form-grid">
          <div class="form-group">
            <label for="title">Title *</label>
            <input
              id="title"
              v-model="newAnnouncement.title"
              type="text"
              placeholder="Enter announcement title"
              required
              class="form-control"
            >
          </div>

          <div class="form-group">
            <label for="category">Category *</label>
            <select id="category" v-model="newAnnouncement.category" required class="form-control">
              <option value="">Select Category</option>
              <option value="general">General Announcement</option>
              <option value="opportunity">Free Program/Opportunity</option>
              <option value="mentorship">Mentorship Program</option>
              <option value="scholarship">Scholarship</option>
              <option value="workshop">Workshop/Event</option>
              <option value="resource">Educational Resource</option>
            </select>
          </div>

          <div class="form-group">
            <label for="priority">Priority *</label>
            <select id="priority" v-model="newAnnouncement.priority" required class="form-control">
              <option value="">Select Priority</option>
              <option value="low">Low</option>
              <option value="medium">Medium</option>
              <option value="high">High</option>
              <option value="urgent">Urgent</option>
            </select>
          </div>

          <div class="form-group">
            <label for="expiry_date">Expiry Date (Optional)</label>
            <input
              id="expiry_date"
              v-model="newAnnouncement.expiry_date"
              type="datetime-local"
              class="form-control"
            >
          </div>
        </div>

        <div class="form-group full-width">
          <label for="content">Content *</label>
          <textarea
            id="content"
            v-model="newAnnouncement.content"
            placeholder="Write your announcement content here..."
            required
            rows="8"
            class="form-control"
          ></textarea>
        </div>

        <div class="form-group full-width">
          <label for="application_link">Application Link (Optional)</label>
          <input
            id="application_link"
            v-model="newAnnouncement.application_link"
            type="url"
            placeholder="https://example.com/apply"
            class="form-control"
          >
          <small>For programs that require applications</small>
        </div>

        <div class="form-group full-width">
          <label class="checkbox-container">
            <input
              type="checkbox"
              v-model="newAnnouncement.is_featured"
              class="checkbox-input"
            >
            <span class="checkmark"></span>
            Feature this announcement (will appear prominently)
          </label>
        </div>

        <div class="form-actions">
          <button type="submit" class="btn btn-primary" :disabled="isSubmitting">
            <span v-if="isSubmitting">Publishing...</span>
            <span v-else>Publish Announcement</span>
          </button>
        </div>
      </form>
    </div>

    <!-- Existing Announcements -->
    <div class="announcements-section">
      <h2>Published Announcements</h2>
      
      <!-- Filters -->
      <div class="filters">
        <select v-model="filterCategory" class="filter-select">
          <option value="">All Categories</option>
          <option value="general">General</option>
          <option value="opportunity">Free Programs</option>
          <option value="mentorship">Mentorship</option>
          <option value="scholarship">Scholarships</option>
          <option value="workshop">Workshops</option>
          <option value="resource">Resources</option>
        </select>

        <select v-model="filterPriority" class="filter-select">
          <option value="">All Priorities</option>
          <option value="urgent">Urgent</option>
          <option value="high">High</option>
          <option value="medium">Medium</option>
          <option value="low">Low</option>
        </select>

        <button @click="fetchAnnouncements" class="btn btn-outline">Refresh</button>
      </div>

      <!-- Announcements List -->
      <div class="announcements-list">
        <div 
          v-for="announcement in filteredAnnouncements" 
          :key="announcement.id"
          class="announcement-card"
          :class="{ 
            featured: announcement.is_featured,
            urgent: announcement.priority === 'urgent',
            expired: isExpired(announcement.expiry_date)
          }"
        >
          <div class="announcement-header">
            <div class="announcement-meta">
              <span class="category-badge" :class="announcement.category">
                {{ getCategoryLabel(announcement.category) }}
              </span>
              <span class="priority-badge" :class="announcement.priority">
                {{ announcement.priority.toUpperCase() }}
              </span>
              <span v-if="announcement.is_featured" class="featured-badge">
                ⭐ FEATURED
              </span>
            </div>
            <div class="announcement-actions">
              <button @click="editAnnouncement(announcement)" class="btn btn-sm btn-outline">
                Edit
              </button>
              <button @click="deleteAnnouncement(announcement.id)" class="btn btn-sm btn-danger">
                Delete
              </button>
            </div>
          </div>

          <h3 class="announcement-title">{{ announcement.title }}</h3>
          <div class="announcement-content" v-html="announcement.content"></div>
          
          <div v-if="announcement.application_link" class="application-section">
            <strong>Application Link:</strong>
            <a :href="announcement.application_link" target="_blank" class="application-link">
              {{ announcement.application_link }}
              <i class="fas fa-external-link-alt"></i>
            </a>
          </div>

          <div class="announcement-footer">
            <div class="announcement-stats">
              <span><i class="fas fa-eye"></i> {{ announcement.views || 0 }} views</span>
              <span><i class="fas fa-calendar"></i> {{ formatDate(announcement.created_at) }}</span>
              <span v-if="announcement.expiry_date" class="expiry-info">
                <i class="fas fa-clock"></i> 
                {{ isExpired(announcement.expiry_date) ? 'Expired' : `Expires: ${formatDate(announcement.expiry_date)}` }}
              </span>
            </div>
          </div>
        </div>

        <div v-if="filteredAnnouncements.length === 0" class="no-announcements">
          <i class="fas fa-bullhorn"></i>
          <p>No announcements found. Create your first announcement above!</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';

const API_BASE_URL = localStorage.getItem('apiBaseUrl') || 'http://localhost:3000/api';

const newAnnouncement = ref({
  title: '',
  category: '',
  priority: 'medium',
  content: '',
  application_link: '',
  expiry_date: '',
  is_featured: false
});

const announcements = ref([]);
const isSubmitting = ref(false);
const filterCategory = ref('');
const filterPriority = ref('');

const filteredAnnouncements = computed(() => {
  return announcements.value.filter(announcement => {
    const matchesCategory = !filterCategory.value || announcement.category === filterCategory.value;
    const matchesPriority = !filterPriority.value || announcement.priority === filterPriority.value;
    return matchesCategory && matchesPriority;
  });
});

const getCategoryLabel = (category) => {
  const labels = {
    general: 'General',
    opportunity: 'Free Program',
    mentorship: 'Mentorship',
    scholarship: 'Scholarship',
    workshop: 'Workshop',
    resource: 'Resource'
  };
  return labels[category] || category;
};

const isExpired = (expiryDate) => {
  if (!expiryDate) return false;
  return new Date(expiryDate) < new Date();
};

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
};

const createAnnouncement = async () => {
  if (isSubmitting.value) return;
  
  isSubmitting.value = true;
  
  try {
    const response = await axios.post(`${API_BASE_URL}/admin/announcements`, newAnnouncement.value, {
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('token')}`,
        'Content-Type': 'application/json'
      }
    });
    
    // Reset form
    newAnnouncement.value = {
      title: '',
      category: '',
      priority: 'medium',
      content: '',
      application_link: '',
      expiry_date: '',
      is_featured: false
    };
    
    // Refresh announcements list
    await fetchAnnouncements();
    
    alert('Announcement published successfully!');
    
  } catch (error) {
    console.error('Error creating announcement:', error);
    alert('Failed to publish announcement. Please try again.');
  } finally {
    isSubmitting.value = false;
  }
};

const fetchAnnouncements = async () => {
  try {
    const response = await axios.get(`${API_BASE_URL}/admin/announcements`, {
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('token')}`
      }
    });
    announcements.value = response.data;
  } catch (error) {
    console.error('Error fetching announcements:', error);
  }
};

const editAnnouncement = (announcement) => {
  // Populate form with announcement data for editing
  newAnnouncement.value = { ...announcement };
  // Scroll to form
  window.scrollTo({ top: 0, behavior: 'smooth' });
};

const deleteAnnouncement = async (id) => {
  if (!confirm('Are you sure you want to delete this announcement?')) return;
  
  try {
    await axios.delete(`${API_BASE_URL}/admin/announcements/${id}`, {
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('token')}`
      }
    });
    
    // Refresh announcements list
    await fetchAnnouncements();
    
    alert('Announcement deleted successfully!');
    
  } catch (error) {
    console.error('Error deleting announcement:', error);
    alert('Failed to delete announcement. Please try again.');
  }
};

onMounted(() => {
  fetchAnnouncements();
});
</script>

<style scoped>
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
}

.announcements-page {
  padding: 2rem;
  max-width: 1200px;
  margin: 0 auto;
}

.page-header {
  text-align: center;
  margin-bottom: 3rem;
}

.page-header h1 {
  font-size: 2.5rem;
  font-weight: 700;
  color: #1f2937;
  margin-bottom: 1rem;
}

.page-header p {
  font-size: 1.1rem;
  color: #6b7280;
  line-height: 1.6;
  max-width: 600px;
  margin: 0 auto;
}

.create-section {
  background: white;
  border-radius: 16px;
  padding: 2.5rem;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
  margin-bottom: 3rem;
}

.create-section h2 {
  font-size: 1.8rem;
  font-weight: 600;
  color: #1f2937;
  margin-bottom: 2rem;
}

.announcement-form {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
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
  min-height: 150px;
}

.checkbox-container {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  cursor: pointer;
  font-size: 0.9rem;
  color: #4b5563;
}

.checkbox-input {
  transform: scale(1.2);
}

.form-actions {
  display: flex;
  justify-content: flex-start;
  margin-top: 1rem;
}

.announcements-section h2 {
  font-size: 1.8rem;
  font-weight: 600;
  color: #1f2937;
  margin-bottom: 1.5rem;
}

.filters {
  display: flex;
  gap: 1rem;
  margin-bottom: 2rem;
  flex-wrap: wrap;
}

.filter-select {
  padding: 0.5rem 1rem;
  border: 1px solid #d1d5db;
  border-radius: 8px;
  font-size: 0.9rem;
}

.announcements-list {
  display: grid;
  gap: 1.5rem;
}

.announcement-card {
  background: white;
  border-radius: 12px;
  padding: 1.5rem;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
  border-left: 4px solid #e5e7eb;
  transition: all 0.3s ease;
}

.announcement-card.featured {
  border-left-color: #e68c07ff;
  box-shadow: 0 4px 20px rgba(230, 140, 7, 0.15);
}

.announcement-card.urgent {
  border-left-color: #ef4444;
  box-shadow: 0 4px 20px rgba(239, 68, 68, 0.15);
}

.announcement-card.expired {
  opacity: 0.7;
  background: #f9fafb;
}

.announcement-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 1rem;
}

.announcement-meta {
  display: flex;
  gap: 0.5rem;
  flex-wrap: wrap;
}

.category-badge,
.priority-badge,
.featured-badge {
  padding: 0.25rem 0.75rem;
  border-radius: 20px;
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
}

.category-badge {
  background: #e0e7ff;
  color: #3730a3;
}

.priority-badge {
  background: #fef3c7;
  color: #92400e;
}

.priority-badge.urgent {
  background: #fee2e2;
  color: #991b1b;
}

.featured-badge {
  background: linear-gradient(135deg, #e68c07ff 0%, #cc8809ff 100%);
  color: white;
}

.announcement-actions {
  display: flex;
  gap: 0.5rem;
}

.announcement-title {
  font-size: 1.25rem;
  font-weight: 600;
  color: #1f2937;
  margin-bottom: 1rem;
}

.announcement-content {
  color: #4b5563;
  line-height: 1.6;
  margin-bottom: 1rem;
}

.application-section {
  background: #f8f9fa;
  padding: 1rem;
  border-radius: 8px;
  margin-bottom: 1rem;
}

.application-link {
  color: #e68c07ff;
  text-decoration: none;
  margin-left: 0.5rem;
}

.application-link:hover {
  text-decoration: underline;
}

.announcement-footer {
  border-top: 1px solid #e5e7eb;
  padding-top: 1rem;
}

.announcement-stats {
  display: flex;
  gap: 1.5rem;
  font-size: 0.85rem;
  color: #6b7280;
}

.no-announcements {
  text-align: center;
  padding: 3rem;
  color: #6b7280;
}

.no-announcements i {
  font-size: 3rem;
  margin-bottom: 1rem;
  opacity: 0.5;
}

.btn {
  padding: 0.5rem 1rem;
  border-radius: 6px;
  font-weight: 500;
  font-size: 0.85rem;
  cursor: pointer;
  transition: all 0.3s ease;
  border: none;
  text-decoration: none;
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
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
  border: 1px solid #e68c07ff;
}

.btn-outline:hover {
  background: #e68c07ff;
  color: white;
}

.btn-danger {
  background: #ef4444;
  color: white;
}

.btn-danger:hover {
  background: #dc2626;
}

.btn-sm {
  padding: 0.375rem 0.75rem;
  font-size: 0.75rem;
}

.btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

@media (max-width: 768px) {
  .announcements-page {
    padding: 1rem;
  }
  
  .form-grid {
    grid-template-columns: 1fr;
  }
  
  .announcement-header {
    flex-direction: column;
    gap: 1rem;
  }
  
  .announcement-actions {
    align-self: flex-start;
  }
  
  .filters {
    flex-direction: column;
  }
  
  .announcement-stats {
    flex-direction: column;
    gap: 0.5rem;
  }
}
</style>
