<template>
  <div class="announcements-page">
    <div class="page-header">
      <h1>Student Announcements & Opportunities</h1>
      <p>Stay updated with the latest news, free programs, mentorship opportunities, and educational resources</p>
    </div>

    <!-- Featured Announcements -->
    <div v-if="featuredAnnouncements.length > 0" class="featured-section">
      <h2>⭐ Featured Opportunities</h2>
      <div class="featured-grid">
        <div 
          v-for="announcement in featuredAnnouncements" 
          :key="announcement.id"
          class="featured-card"
          @click="viewAnnouncement(announcement)"
        >
          <div class="featured-header">
            <span class="category-badge" :class="announcement.category">
              {{ getCategoryLabel(announcement.category) }}
            </span>
            <span v-if="announcement.priority === 'urgent'" class="urgent-badge">
              🚨 URGENT
            </span>
          </div>
          
          <h3>{{ announcement.title }}</h3>
          <div class="featured-content">
            {{ getExcerpt(announcement.content) }}
          </div>
          
          <div v-if="announcement.application_link" class="apply-section">
            <button @click.stop="openApplicationLink(announcement.application_link)" class="btn btn-primary">
              Apply Now
              <i class="fas fa-external-link-alt"></i>
            </button>
          </div>
          
          <div class="featured-footer">
            <span class="date">{{ formatDate(announcement.created_at) }}</span>
            <span v-if="announcement.expiry_date" class="expiry">
              {{ isExpired(announcement.expiry_date) ? 'Expired' : `Expires: ${formatDate(announcement.expiry_date)}` }}
            </span>
          </div>
        </div>
      </div>
    </div>

    <!-- Filters -->
    <div class="filters-section">
      <h2>Browse Announcements</h2>
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
          <option value="urgent">Urgent Only</option>
          <option value="high">High Priority</option>
          <option value="medium">Medium Priority</option>
          <option value="low">Low Priority</option>
        </select>

        <button @click="fetchAnnouncements" class="btn btn-outline">
          <i class="fas fa-sync-alt"></i> Refresh
        </button>
      </div>
    </div>

    <!-- Announcements List -->
    <div class="announcements-section">
      <div class="announcements-list">
        <div 
          v-for="announcement in filteredAnnouncements" 
          :key="announcement.id"
          class="announcement-card"
          :class="{ 
            urgent: announcement.priority === 'urgent',
            expired: isExpired(announcement.expiry_date)
          }"
          @click="viewAnnouncement(announcement)"
        >
          <div class="announcement-header">
            <div class="announcement-meta">
              <span class="category-badge" :class="announcement.category">
                {{ getCategoryLabel(announcement.category) }}
              </span>
              <span class="priority-badge" :class="announcement.priority">
                {{ announcement.priority.toUpperCase() }}
              </span>
            </div>
            <div class="announcement-date">
              {{ formatDate(announcement.created_at) }}
            </div>
          </div>

          <h3 class="announcement-title">{{ announcement.title }}</h3>
          <div class="announcement-content">
            {{ getExcerpt(announcement.content, 200) }}
          </div>
          
          <div class="announcement-actions">
            <button @click.stop="viewAnnouncement(announcement)" class="btn btn-outline">
              Read More
            </button>
            <button 
              v-if="announcement.application_link && !isExpired(announcement.expiry_date)" 
              @click.stop="openApplicationLink(announcement.application_link)" 
              class="btn btn-primary"
            >
              Apply Now
              <i class="fas fa-external-link-alt"></i>
            </button>
          </div>
          
          <div v-if="announcement.expiry_date" class="expiry-info">
            <i class="fas fa-clock"></i>
            {{ isExpired(announcement.expiry_date) ? 'This opportunity has expired' : `Apply before: ${formatDate(announcement.expiry_date)}` }}
          </div>
        </div>

        <div v-if="filteredAnnouncements.length === 0" class="no-announcements">
          <i class="fas fa-bullhorn"></i>
          <h3>No announcements found</h3>
          <p>Try adjusting your filters or check back later for new opportunities.</p>
        </div>
      </div>
    </div>

    <!-- Announcement Detail Modal -->
    <div v-if="selectedAnnouncement" class="modal-overlay" @click="closeModal">
      <div class="modal-content" @click.stop>
        <div class="modal-header">
          <h2>{{ selectedAnnouncement.title }}</h2>
          <button @click="closeModal" class="close-btn">&times;</button>
        </div>
        
        <div class="modal-body">
          <div class="announcement-details">
            <div class="detail-meta">
              <span class="category-badge" :class="selectedAnnouncement.category">
                {{ getCategoryLabel(selectedAnnouncement.category) }}
              </span>
              <span class="priority-badge" :class="selectedAnnouncement.priority">
                {{ selectedAnnouncement.priority.toUpperCase() }}
              </span>
              <span class="detail-date">
                <i class="fas fa-calendar"></i>
                {{ formatDate(selectedAnnouncement.created_at) }}
              </span>
            </div>
            
            <div class="detail-content" v-html="selectedAnnouncement.content"></div>
            
            <div v-if="selectedAnnouncement.application_link" class="application-section">
              <h4>How to Apply:</h4>
              <button @click="openApplicationLink(selectedAnnouncement.application_link)" class="btn btn-primary btn-large">
                Apply for this Opportunity
                <i class="fas fa-external-link-alt"></i>
              </button>
            </div>
            
            <div v-if="selectedAnnouncement.expiry_date" class="expiry-section">
              <div class="expiry-info" :class="{ expired: isExpired(selectedAnnouncement.expiry_date) }">
                <i class="fas fa-clock"></i>
                {{ isExpired(selectedAnnouncement.expiry_date) 
                  ? 'This opportunity has expired' 
                  : `Application Deadline: ${formatDate(selectedAnnouncement.expiry_date)}` 
                }}
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';

const API_BASE_URL = localStorage.getItem('apiBaseUrl') || 'http://localhost:3000/api';

const announcements = ref([]);
const selectedAnnouncement = ref(null);
const filterCategory = ref('');
const filterPriority = ref('');

const featuredAnnouncements = computed(() => {
  return announcements.value.filter(announcement => 
    announcement.is_featured && !isExpired(announcement.expiry_date)
  );
});

const filteredAnnouncements = computed(() => {
  return announcements.value.filter(announcement => {
    const matchesCategory = !filterCategory.value || announcement.category === filterCategory.value;
    const matchesPriority = !filterPriority.value || announcement.priority === filterPriority.value;
    const isNotExpired = !isExpired(announcement.expiry_date);
    return matchesCategory && matchesPriority && isNotExpired;
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
    day: 'numeric'
  });
};

const getExcerpt = (content, length = 150) => {
  if (!content) return '';
  const plainText = content.replace(/<[^>]*>/g, '');
  return plainText.length > length ? plainText.substring(0, length) + '...' : plainText;
};

const viewAnnouncement = (announcement) => {
  selectedAnnouncement.value = announcement;
  // Track view (optional)
  trackView(announcement.id);
};

const closeModal = () => {
  selectedAnnouncement.value = null;
};

const openApplicationLink = (link) => {
  window.open(link, '_blank');
};

const trackView = async (announcementId) => {
  try {
    await axios.post(`${API_BASE_URL}/announcements/${announcementId}/view`, {}, {
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('token')}`
      }
    });
  } catch (error) {
    console.error('Error tracking view:', error);
  }
};

const fetchAnnouncements = async () => {
  try {
    const response = await axios.get(`${API_BASE_URL}/student/announcements`, {
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('token')}`
      }
    });
    announcements.value = response.data;
  } catch (error) {
    console.error('Error fetching announcements:', error);
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
  background: #f8f9fa;
  min-height: 100vh;
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

.featured-section {
  margin-bottom: 3rem;
}

.featured-section h2 {
  font-size: 1.8rem;
  font-weight: 600;
  color: #1f2937;
  margin-bottom: 1.5rem;
  text-align: center;
}

.featured-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
  gap: 1.5rem;
  margin-bottom: 2rem;
}

.featured-card {
  background: white;
  border-radius: 16px;
  padding: 1.5rem;
  box-shadow: 0 8px 25px rgba(230, 140, 7, 0.15);
  border: 2px solid #e68c07ff;
  cursor: pointer;
  transition: all 0.3s ease;
}

.featured-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 12px 35px rgba(230, 140, 7, 0.25);
}

.featured-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}

.urgent-badge {
  background: #ef4444;
  color: white;
  padding: 0.25rem 0.75rem;
  border-radius: 20px;
  font-size: 0.75rem;
  font-weight: 600;
  animation: pulse 2s infinite;
}

@keyframes pulse {
  0% { opacity: 1; }
  50% { opacity: 0.7; }
  100% { opacity: 1; }
}

.featured-card h3 {
  font-size: 1.25rem;
  font-weight: 600;
  color: #1f2937;
  margin-bottom: 1rem;
}

.featured-content {
  color: #4b5563;
  line-height: 1.6;
  margin-bottom: 1.5rem;
}

.apply-section {
  margin-bottom: 1rem;
}

.featured-footer {
  display: flex;
  justify-content: space-between;
  font-size: 0.85rem;
  color: #6b7280;
}

.filters-section {
  background: white;
  border-radius: 12px;
  padding: 1.5rem;
  margin-bottom: 2rem;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
}

.filters-section h2 {
  font-size: 1.5rem;
  font-weight: 600;
  color: #1f2937;
  margin-bottom: 1rem;
}

.filters {
  display: flex;
  gap: 1rem;
  flex-wrap: wrap;
}

.filter-select {
  padding: 0.5rem 1rem;
  border: 1px solid #d1d5db;
  border-radius: 8px;
  font-size: 0.9rem;
  min-width: 150px;
}

.announcements-section h2 {
  font-size: 1.8rem;
  font-weight: 600;
  color: #1f2937;
  margin-bottom: 1.5rem;
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
  cursor: pointer;
  transition: all 0.3s ease;
}

.announcement-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
}

.announcement-card.urgent {
  border-left-color: #ef4444;
  box-shadow: 0 4px 20px rgba(239, 68, 68, 0.15);
}

.announcement-card.expired {
  opacity: 0.6;
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

.announcement-date {
  font-size: 0.85rem;
  color: #6b7280;
}

.category-badge,
.priority-badge {
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

.announcement-title {
  font-size: 1.1rem;
  font-weight: 600;
  color: #1f2937;
  margin-bottom: 0.75rem;
}

.announcement-content {
  color: #4b5563;
  line-height: 1.6;
  margin-bottom: 1rem;
}

.announcement-actions {
  display: flex;
  gap: 0.75rem;
  margin-bottom: 1rem;
}

.expiry-info {
  font-size: 0.85rem;
  color: #6b7280;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.expiry-info.expired {
  color: #ef4444;
  font-weight: 500;
}

.no-announcements {
  text-align: center;
  padding: 4rem 2rem;
  color: #6b7280;
}

.no-announcements i {
  font-size: 4rem;
  margin-bottom: 1.5rem;
  opacity: 0.3;
}

.no-announcements h3 {
  font-size: 1.5rem;
  font-weight: 600;
  margin-bottom: 0.5rem;
  color: #4b5563;
}

/* Modal Styles */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.7);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  padding: 2rem;
}

.modal-content {
  background: white;
  border-radius: 16px;
  max-width: 800px;
  max-height: 90vh;
  overflow-y: auto;
  width: 100%;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.5rem;
  border-bottom: 1px solid #e2e8f0;
}

.modal-header h2 {
  font-size: 1.5rem;
  font-weight: 600;
  color: #1f2937;
}

.close-btn {
  background: none;
  border: none;
  font-size: 1.5rem;
  cursor: pointer;
  color: #6b7280;
  padding: 0.5rem;
  border-radius: 50%;
  transition: all 0.3s ease;
}

.close-btn:hover {
  background: #f3f4f6;
  color: #1f2937;
}

.modal-body {
  padding: 1.5rem;
}

.detail-meta {
  display: flex;
  gap: 0.75rem;
  margin-bottom: 1.5rem;
  flex-wrap: wrap;
}

.detail-date {
  font-size: 0.85rem;
  color: #6b7280;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.detail-content {
  color: #4b5563;
  line-height: 1.7;
  font-size: 1rem;
  margin-bottom: 2rem;
}

.application-section {
  background: #f8f9fa;
  padding: 1.5rem;
  border-radius: 12px;
  text-align: center;
  margin-bottom: 1.5rem;
}

.application-section h4 {
  font-size: 1.1rem;
  font-weight: 600;
  color: #1f2937;
  margin-bottom: 1rem;
}

.expiry-section {
  border-top: 1px solid #e5e7eb;
  padding-top: 1rem;
}

.btn {
  padding: 0.75rem 1.5rem;
  border-radius: 8px;
  font-weight: 500;
  font-size: 0.9rem;
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

.btn-large {
  padding: 1rem 2rem;
  font-size: 1rem;
}

@media (max-width: 768px) {
  .announcements-page {
    padding: 1rem;
  }
  
  .featured-grid {
    grid-template-columns: 1fr;
  }
  
  .filters {
    flex-direction: column;
  }
  
  .filter-select {
    width: 100%;
  }
  
  .announcement-header {
    flex-direction: column;
    gap: 0.75rem;
  }
  
  .announcement-actions {
    flex-direction: column;
  }
  
  .modal-content {
    margin: 1rem;
    max-height: 95vh;
  }
}
</style>
