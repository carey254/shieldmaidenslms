<template>
  <div class="landing-page">
    <!-- Hero Section -->
    <section class="hero-section">
      <div class="hero-content">
        <div class="hero-left">
          <p class="hero-subtitle">Empowering Young People with Digital Safety & Technology Skills</p>
          <div class="cta-buttons">
            <router-link to="/login" class="btn-primary">Sign In to Learn</router-link>
            <router-link to="/register" class="btn-secondary">Create Account</router-link>
          </div>
        </div>
      </div>
    </section>

    <!-- Student Announcements Section -->
    <section v-if="featuredAnnouncements.length > 0" class="announcements-section">
      <div class="container">
        <h2>📢 Latest Opportunities & Announcements</h2>
        <div class="announcements-grid">
          <div 
            v-for="announcement in featuredAnnouncements.slice(0, 3)" 
            :key="announcement.id"
            class="announcement-card"
            :class="{ urgent: announcement.priority === 'urgent' }"
            @click="viewAnnouncement(announcement)"
          >
            <div class="announcement-header">
              <span class="category-badge" :class="announcement.category">
                {{ getCategoryLabel(announcement.category) }}
              </span>
              <span v-if="announcement.priority === 'urgent'" class="urgent-badge">
                🚨 URGENT
              </span>
            </div>
            
            <h3>{{ announcement.title }}</h3>
            <div class="announcement-content">
              {{ getExcerpt(announcement.content, 120) }}
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
              </button>
            </div>
            
            <div class="announcement-footer">
              <span class="date">{{ formatDate(announcement.created_at) }}</span>
              <span v-if="announcement.expiry_date" class="expiry">
                {{ isExpired(announcement.expiry_date) ? 'Expired' : `Expires: ${formatDate(announcement.expiry_date)}` }}
              </span>
            </div>
          </div>
        </div>
        
        <div class="view-all-section">
          <router-link to="/student/announcements" class="view-all-btn">
            View All Announcements
            <i class="fas fa-arrow-right"></i>
          </router-link>
        </div>
      </div>
    </section>

    <!-- Features Section -->
    <section class="features-section">
      <div class="container">
        <h2>Why Choose The Shield Maidens?</h2>
        <div class="features-grid">
          <div class="feature-card">
            <div class="feature-icon"></div>
            <h3>Digital Safety</h3>
            <p>Learn essential online safety skills and protect yourself in digital world</p>
          </div>
          <div class="feature-card">
            <div class="feature-icon"></div>
            <h3>Technology Skills</h3>
            <p>Build fundamental tech skills that prepare you for the future</p>
          </div>
          <div class="feature-card">
            <div class="feature-icon"></div>
            <h3>Life Skills</h3>
            <p>Develop confidence and communication skills for personal growth</p>
          </div>
        </div>
      </div>
    </section>

    <!-- Welcome Section Above Footer -->
    <section class="welcome-section">
      <div class="container">
        <div class="welcome-content">
          <p class="hero-subtitle">Empowering Young People with Digital Safety & Technology Skills</p>
          <div class="cta-buttons">
            <router-link to="/courses" class="btn-primary">View Our Curriculum</router-link>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import { API_BASE_URL } from '@/config/api';
import axios from 'axios';

const router = useRouter();
const authStore = useAuthStore();

// Announcements functionality
const announcements = ref([]);
const selectedAnnouncement = ref(null);

const featuredAnnouncements = computed(() => {
  return announcements.value.filter(announcement => 
    announcement.is_featured && !isExpired(announcement.expiry_date)
  );
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

const openApplicationLink = (link) => {
  window.open(link, '_blank');
};

const trackView = async (announcementId) => {
  if (!authStore.token) return;

  try {
    await axios.post(`${API_BASE_URL}/announcements/${announcementId}/view`, {}, {
      headers: {
        'Authorization': `Bearer ${authStore.token}`
      }
    });
  } catch (error) {
    console.error('Error tracking view:', error);
  }
};

const fetchAnnouncements = async () => {
  if (!authStore.token) {
    announcements.value = [];
    return;
  }

  try {
    const response = await axios.get(`${API_BASE_URL}/student/announcements`, {
      headers: {
        'Authorization': `Bearer ${authStore.token}`
      }
    });
    announcements.value = response.data;
  } catch (error) {
    console.error('Error fetching announcements:', error);
  }
};

// Fetch announcements on component mount
onMounted(() => {
  fetchAnnouncements();
});
</script>

<style scoped>
.landing-page {
  min-height: 100vh;
  background: linear-gradient(135deg, #f7fafc 0%, #e2e8f0 100%);
}

/* Hero Section */
.hero-section {
  color: white;
  padding: 2rem 0;
  text-align: center;
  position: relative;
  overflow: hidden;
  min-height: 100vh;
  display: flex;
  align-items: center;
}

.hero-section::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: url('/background.jpg') no-repeat center center;
  background-size: cover;
  background-position: center;
  filter: blur(2px);
  z-index: 0;
}

.hero-content {
  position: relative;
  z-index: 1;
  display: flex;
  justify-content: center;
  align-items: center;
  height: 100%;
  width: 100%;
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 1rem;
  gap: 2rem;
}

.hero-left {
  flex: 1;
  max-width: 600px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  text-align: center;
}


.hero-content h1 {
  font-size: 2.5rem;
  font-weight: 700;
  color: #FFFFFF;
  margin-bottom: 1.5rem;
  text-shadow: 0 2px 8px rgba(0, 0, 0, 0.8);
}

.hero-subtitle {
  font-size: 1.1rem;
  color: #FFFFFF;
  margin-bottom: 2rem;
  opacity: 0.95;
  max-width: 500px;
  margin-left: auto;
  margin-right: auto;
  text-shadow: 0 1px 4px rgba(0, 0, 0, 0.8);
  font-weight: 700;
}

.cta-buttons {
  display: flex;
  gap: 1.5rem;
  justify-content: center;
  flex-wrap: wrap;
  margin-top: 2rem;
}

/* Welcome Section Above Footer */
.welcome-section {
  padding: 4rem 0;
  background: #f8f9fa;
  text-align: center;
}

.welcome-section .welcome-content {
  max-width: 800px;
  margin: 0 auto;
}

.welcome-section .hero-subtitle {
  font-size: 1.3rem;
  color: #1a365d;
  margin-bottom: 2rem;
  font-weight: 500;
}

.welcome-section .cta-buttons {
  justify-content: center;
}

/* Announcements Section Styles */
.announcements-section {
  padding: 4rem 0;
  background: #f8f9fa;
}

.announcements-section h2 {
  text-align: center;
  font-size: 2rem;
  font-weight: 700;
  color: #1f2937;
  margin-bottom: 2rem;
}

.announcements-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
  gap: 1.5rem;
  margin-bottom: 2rem;
}

.announcement-card {
  background: white;
  border-radius: 12px;
  padding: 1.5rem;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
  border-left: 4px solid #e5e7eb;
  cursor: pointer;
  transition: all 0.3s ease;
}

.announcement-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
}

.announcement-card.urgent {
  border-left-color: #ef4444;
  box-shadow: 0 4px 15px rgba(239, 68, 68, 0.15);
}

.announcement-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 1rem;
  flex-wrap: wrap;
  gap: 0.5rem;
}

.category-badge,
.urgent-badge {
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

.urgent-badge {
  background: #fee2e2;
  color: #991b1b;
  animation: pulse 2s infinite;
}

@keyframes pulse {
  0% { opacity: 1; }
  50% { opacity: 0.7; }
  100% { opacity: 1; }
}

.announcement-card h3 {
  font-size: 1.1rem;
  font-weight: 600;
  color: #1f2937;
  margin-bottom: 0.75rem;
  line-height: 1.4;
}

.announcement-content {
  color: #4b5563;
  line-height: 1.6;
  margin-bottom: 1rem;
  font-size: 0.9rem;
}

.announcement-actions {
  display: flex;
  gap: 0.75rem;
  margin-bottom: 1rem;
}

.announcement-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 0.85rem;
  color: #6b7280;
  border-top: 1px solid #f3f4f6;
  padding-top: 0.75rem;
}

.date,
.expiry {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.expiry {
  color: #ef4444;
  font-weight: 500;
}

.view-all-section {
  text-align: center;
  margin-top: 2rem;
}

.view-all-btn {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  background: linear-gradient(135deg, #e68c07ff 0%, #cc8809ff 100%);
  color: white;
  text-decoration: none;
  padding: 1rem 2rem;
  border-radius: 8px;
  font-weight: 600;
  font-size: 1rem;
  transition: all 0.3s ease;
}

.view-all-btn:hover {
  background: linear-gradient(135deg, #1d1d1fff 0%, #080808ff 100%);
  transform: translateY(-2px);
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

@media (max-width: 768px) {
  .announcements-grid {
    grid-template-columns: 1fr;
  }
  
  .announcement-header {
    flex-direction: column;
    gap: 0.75rem;
  }
  
  .announcement-actions {
    flex-direction: column;
  }
  
  .announcement-footer {
    flex-direction: column;
    gap: 0.5rem;
    align-items: flex-start;
  }
}

.btn-primary, .btn-secondary {
  padding: 1rem 2rem;
  border-radius: 50px;
  font-size: 1.1rem;
  font-weight: 700;
  text-decoration: none;
  transition: all 0.3s ease;
  display: inline-block;
}

.btn-primary {
  background: #ff9900;
  color: white;
}

.btn-primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(255, 153, 0, 0.3);
}

.btn-secondary {
  background: transparent;
  color: #1a365d;
  border: 2px solid #1a365d;
  font-weight: 700;
}

.btn-secondary:hover {
  background: #1a365d;
  color: white;
  transform: translateY(-2px);
}

/* Features Section */
.features-section {
  padding: 4rem 0;
  background: white;
}

.features-section h2 {
  font-size: 2.5rem;
  font-weight: 700;
  color: #1a365d;
  text-align: center;
  margin-bottom: 3rem;
}

.features-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 2rem;
}

.feature-card {
  background: #f8f9fa;
  border-radius: 16px;
  padding: 2rem;
  text-align: center;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
  transition: transform 0.3s ease;
}

.feature-card:hover {
  transform: translateY(-4px);
}

.feature-icon {
  font-size: 3rem;
  margin-bottom: 1.5rem;
}

.feature-card h3 {
  font-size: 1.5rem;
  font-weight: 600;
  color: #1a365d;
  margin-bottom: 1rem;
}

.feature-card p {
  color: #6c757d;
  line-height: 1.6;
}

/* Responsive Design - Mobile First */
@media (max-width: 767px) {
  .hero-section {
    padding: 1rem 0;
    min-height: 90vh;
  }
  
  .hero-content {
    padding: 0 1rem;
    gap: 1rem;
  }
  
  .hero-left {
    max-width: 100%;
    text-align: center;
  }
  
  .hero-content h1 {
    font-size: 2rem;
    margin-bottom: 1rem;
  }
  
  .hero-subtitle {
    font-size: 1rem;
    margin-bottom: 1.5rem;
    max-width: 400px;
  }
  
  .cta-buttons {
    flex-direction: column;
    gap: 1rem;
    align-items: center;
  }
  
  .btn-primary, .btn-secondary {
    width: 100%;
    max-width: 280px;
    padding: 0.875rem 1.5rem;
    font-size: 1rem;
  }
  
  .announcements-section {
    padding: 2rem 0;
  }
  
  .announcements-section h2 {
    font-size: 1.5rem;
    margin-bottom: 1.5rem;
    padding: 0 1rem;
  }
  
  .announcements-grid {
    grid-template-columns: 1fr;
    gap: 1rem;
    padding: 0 1rem;
  }
  
  .announcement-card {
    padding: 1rem;
    border-radius: 8px;
  }
  
  .announcement-card h3 {
    font-size: 1rem;
    margin-bottom: 0.5rem;
  }
  
  .announcement-content {
    font-size: 0.875rem;
    margin-bottom: 0.75rem;
  }
  
  .announcement-header {
    flex-direction: column;
    gap: 0.5rem;
    align-items: flex-start;
  }
  
  .announcement-actions {
    flex-direction: column;
    gap: 0.5rem;
  }
  
  .announcement-footer {
    flex-direction: column;
    gap: 0.5rem;
    align-items: flex-start;
  }
  
  .view-all-section {
    margin-top: 1.5rem;
    padding: 0 1rem;
  }
  
  .view-all-btn {
    width: 100%;
    padding: 0.875rem 1.5rem;
    font-size: 0.9rem;
  }
  
  .features-section {
    padding: 2rem 0;
  }
  
  .features-section h2 {
    font-size: 1.75rem;
    margin-bottom: 1.5rem;
    padding: 0 1rem;
  }
  
  .features-grid {
    grid-template-columns: 1fr;
    gap: 1rem;
    padding: 0 1rem;
  }
  
  .feature-card {
    padding: 1.5rem;
    border-radius: 12px;
  }
  
  .feature-icon {
    font-size: 2.5rem;
    margin-bottom: 1rem;
  }
  
  .feature-card h3 {
    font-size: 1.25rem;
    margin-bottom: 0.75rem;
  }
  
  .feature-card p {
    font-size: 0.875rem;
  }
  
  .welcome-section {
    padding: 2rem 0;
  }
  
  .welcome-section .welcome-content {
    padding: 0 1rem;
  }
  
  .welcome-section .hero-subtitle {
    font-size: 1rem;
    margin-bottom: 1.5rem;
  }
}

@media (max-width: 480px) {
  .hero-section {
    padding: 0.5rem 0;
    min-height: 85vh;
  }
  
  .hero-content {
    padding: 0 0.75rem;
  }
  
  .hero-content h1 {
    font-size: 1.75rem;
    margin-bottom: 0.75rem;
  }
  
  .hero-subtitle {
    font-size: 0.9rem;
    margin-bottom: 1.25rem;
    max-width: 350px;
  }
  
  .cta-buttons {
    gap: 0.75rem;
  }
  
  .btn-primary, .btn-secondary {
    max-width: 250px;
    padding: 0.75rem 1.25rem;
    font-size: 0.9rem;
  }
  
  .announcements-section {
    padding: 1.5rem 0;
  }
  
  .announcements-section h2 {
    font-size: 1.25rem;
  }
  
  .announcements-grid {
    padding: 0 0.75rem;
  }
  
  .announcement-card {
    padding: 0.75rem;
  }
  
  .announcement-card h3 {
    font-size: 0.9rem;
  }
  
  .announcement-content {
    font-size: 0.8rem;
  }
  
  .category-badge, .urgent-badge {
    font-size: 0.65rem;
    padding: 0.2rem 0.5rem;
  }
  
  .btn {
    font-size: 0.75rem;
    padding: 0.5rem 0.75rem;
  }
  
  .view-all-btn {
    font-size: 0.8rem;
    padding: 0.75rem 1.25rem;
  }
  
  .features-section {
    padding: 1.5rem 0;
  }
  
  .features-section h2 {
    font-size: 1.5rem;
  }
  
  .features-grid {
    padding: 0 0.75rem;
  }
  
  .feature-card {
    padding: 1rem;
  }
  
  .feature-icon {
    font-size: 2rem;
    margin-bottom: 0.75rem;
  }
  
  .feature-card h3 {
    font-size: 1.1rem;
  }
  
  .feature-card p {
    font-size: 0.8rem;
  }
  
  .welcome-section {
    padding: 1.5rem 0;
  }
  
  .welcome-section .hero-subtitle {
    font-size: 0.9rem;
  }
}

/* Landscape orientation for mobile */
@media (max-width: 767px) and (orientation: landscape) {
  .hero-section {
    min-height: 100vh;
  }
  
  .hero-content h1 {
    font-size: 1.8rem;
  }
  
  .hero-subtitle {
    font-size: 0.85rem;
    margin-bottom: 1rem;
  }
  
  .cta-buttons {
    flex-direction: row;
    gap: 1rem;
  }
  
  .btn-primary, .btn-secondary {
    width: auto;
    max-width: 200px;
  }
}

/* Tablet styles */
@media (min-width: 768px) and (max-width: 1023px) {
  .hero-content h1 {
    font-size: 3rem;
  }
  
  .hero-subtitle {
    font-size: 1.2rem;
    max-width: 550px;
  }
  
  .announcements-grid {
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  }
  
  .features-grid {
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 1.5rem;
  }
}

/* Touch-friendly interactions for mobile */
@media (hover: none) and (pointer: coarse) {
  .announcement-card:hover {
    transform: none;
  }
  
  .announcement-card:active {
    transform: scale(0.98);
  }
  
  .feature-card:hover {
    transform: none;
  }
  
  .feature-card:active {
    transform: scale(0.98);
  }
  
  .btn-primary:hover, .btn-secondary:hover {
    transform: none;
  }
  
  .btn-primary:active, .btn-secondary:active {
    transform: scale(0.95);
  }
  
  .view-all-btn:hover {
    transform: none;
  }
  
  .view-all-btn:active {
    transform: scale(0.98);
  }
}
</style>
