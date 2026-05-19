<template>
  <div class="landing-page">
    <!-- Hero Section -->
    <section class="hero-section">
      <div class="hero-content">
        <div class="hero-left">
          <div class="learner-badge"></div>
          <h1 class="hero-title">Learn new skills. Advance your career.</h1>
          <p class="hero-subtitle">
            <span class="typing-text">{{ currentTopic }}</span><span class="typing-cursor">|</span>
          </p>
          
          <!-- Search Bar -->
          <div class="hero-search">
            <input 
              v-model="searchQuery" 
              type="text" 
              placeholder="Search for a course..."
              class="search-input"
              @keyup.enter="handleSearch"
            >
            <button @click="handleSearch" class="search-btn">
              <i class="fas fa-search"></i>
              Search
            </button>
          </div>

          <!-- Statistics -->
          <div class="hero-stats">
            <div class="stat-item">
              <div class="stat-icon"><i class="fas fa-bullseye"></i></div>
              <div class="stat-info">
                <div class="stat-value">Impact</div>
                <div class="stat-label">Community Growth</div>
              </div>
            </div>
            <div class="stat-item">
              <div class="stat-icon"><i class="fas fa-users"></i></div>
              <div class="stat-info">
                <div class="stat-value">100+ Learners </div>
                <div class="stat-label">Reached</div>
              </div>
            </div>
            <div class="stat-item">
              <div class="stat-icon"><i class="fas fa-certificate"></i></div>
              <div class="stat-info">
                <div class="stat-value">Certificates Issued</div>
                <div class="stat-label">Upon Completion</div>
              </div>
            </div>
            <div class="stat-item">
              <div class="stat-icon"><i class="fas fa-star"></i></div>
              <div class="stat-info">
                <div class="stat-value">4.8</div>
                <div class="stat-label">Avg. rating</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Public feed: announcements & opportunities (no login required) -->
    <section v-if="homeAnnouncements.length || homeOpportunities.length" class="announcements-section">
      <div class="container">
        <h2>Latest announcements &amp; opportunities</h2>
        <p class="feed-lead">Published by our team for learners and the community.</p>

        <div v-if="homeAnnouncements.length" class="announcements-grid">
          <div 
            v-for="announcement in homeAnnouncements.slice(0, 3)" 
            :key="'a-' + announcement.id"
            class="announcement-card"
            :class="{ urgent: announcement.priority === 'urgent' }"
            @click="viewAnnouncement(announcement)"
          >
            <div class="announcement-header">
              <span class="category-badge" :class="announcement.category">
                {{ getCategoryLabel(announcement.category) }}
              </span>
              <span v-if="announcement.priority === 'urgent'" class="urgent-badge">
                URGENT
              </span>
            </div>
            
            <h3>{{ announcement.title }}</h3>
            <div class="announcement-content">
              {{ getExcerpt(announcement.content, 120) }}
            </div>
            
            <div class="announcement-actions">
              <button type="button" @click.stop="viewAnnouncement(announcement)" class="btn btn-outline">
                Read more
              </button>
              <button 
                v-if="announcement.application_link && !isExpired(announcement.expiry_date)" 
                type="button"
                @click.stop="openApplicationLink(announcement.application_link)" 
                class="btn btn-primary"
              >
                Apply
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

        <div v-if="homeOpportunities.length" class="opportunities-block">
          <h3 class="opp-heading">Open opportunities</h3>
          <div class="opportunities-grid">
            <div
              v-for="opp in homeOpportunities.slice(0, 3)"
              :key="'o-' + opp.id"
              class="opportunity-card"
            >
              <div class="opp-type">{{ opp.type }}</div>
              <h4>{{ opp.title }}</h4>
              <p class="opp-org">{{ opp.organization }}</p>
              <p class="opp-meta">Apply by {{ formatDate(opp.deadline) }}</p>
              <button
                v-if="opp.application_link"
                type="button"
                class="btn btn-primary"
                @click="openApplicationLink(opp.application_link)"
              >
                Learn more
              </button>
            </div>
          </div>
        </div>
        
        <div class="view-all-section">
          <router-link v-if="authStore.token" to="/student/announcements" class="view-all-btn">
            View all in your portal
            <i class="fas fa-arrow-right"></i>
          </router-link>
          <router-link v-else to="/login" class="view-all-btn">
            Sign in for the full feed
            <i class="fas fa-arrow-right"></i>
          </router-link>
        </div>
      </div>
    </section>

    <!-- Popular Courses Section -->
    <section class="courses-section">
      <div class="container">
        <h2 class="section-title">Our Courses</h2>
        <p class="section-subtitle">Explore our most popular courses and start your learning journey today</p>
        <div class="courses-grid">
          <div 
            v-for="course in popularCourses" 
            :key="course.id" 
            class="course-card"
            @click="goToCourse(course.id)"
          >
            <div class="course-icon-wrapper">
              <img v-if="course.image" :src="course.image" :alt="course.title" class="course-image" />
              <div v-else class="course-emoji">{{ course.emoji }}</div>
            </div>
            <div class="course-category">{{ course.category }}</div>
            <div class="course-content">
              <h3>{{ course.title }}</h3>
              <p class="course-description">{{ course.description }}</p>
            </div>
          </div>
        </div>
        <div class="view-all-container">
          <button @click="router.push('/courses')" class="view-all-btn">View All Courses</button>
        </div>
      </div>
    </section>

    <!-- Why Learn With Us Section -->
    <section class="why-section">
      <div class="container">
        <h2 class="section-title">Why Learn With Us?</h2>
        <div class="features-grid">
          <div class="feature-card">
            <div class="feature-icon">
              <img src="/curriculum.png" alt="Comprehensive Curriculum" />
            </div>
            <h3>Comprehensive Curriculum</h3>
            <p>Access a wide range of courses covering various topics</p>
          </div>
          <div class="feature-card">
            <div class="feature-icon">
              <img src="/practical.png" alt="Practical Learning" />
            </div>
            <h3>Practical Learning</h3>
            <p>Hands-on projects and real-world applications</p>
          </div>
          <div class="feature-card">
            <div class="feature-icon">
              <img src="/inclusive.png" alt="Inclusive Learning" />
            </div>
            <h3>Inclusive Learning</h3>
            <p>We create accessible learning opportunities for everyone, regardless of background or ability</p>
          </div>
          <div class="feature-card">
            <div class="feature-icon">
              <img src="/community.png" alt="Community Support" />
            </div>
            <h3>Community Support</h3>
            <p>Join a supportive community of learners and mentors</p>
          </div>
        </div>
      </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section">
      <div class="container">
        <div class="cta-content">
          <h2>Ready to start learning?</h2>
          <p>Join thousands of learners already improving their skills</p>
          <div class="cta-buttons">
            <router-link to="/register" class="btn-primary">Get Started Now</router-link>
            <router-link to="/courses" class="btn-primary">View All Courses</router-link>
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
import { PUBLIC_BRAND_LOGO } from '@/config/branding';
import axios from 'axios';

const router = useRouter();
const authStore = useAuthStore();

const announcements = ref([]);
const opportunities = ref([]);
const selectedAnnouncement = ref(null);
const searchQuery = ref('');

// Typing animation for topics
const topics = [
  'Build Digital Confidence.',
  'Stay Safe Online.',
  'Learn digital safety and cybersecurity skills to navigate and protect yourself in today\'s connected world.',
  'Technology for Everyone.',
  'Empowering Inclusive Learning.',
  'We support accessibility and inclusion by creating learning opportunities that are open, supportive, and accessible to all learners.',
  'Code the Future.',
  'Create Real Solutions.',
  'Gain practical coding and technology skills through hands-on learning designed to help you grow and innovate.'
];
const currentTopic = ref('');
const topicIndex = ref(0);
const charIndex = ref(0);
const isDeleting = ref(false);

const typeTopic = () => {
  const currentTopicText = topics[topicIndex.value];
  
  if (isDeleting.value) {
    charIndex.value--;
    if (charIndex.value < 0) charIndex.value = 0;
    currentTopic.value = currentTopicText.substring(0, charIndex.value);
  } else {
    charIndex.value++;
    if (charIndex.value > currentTopicText.length) charIndex.value = currentTopicText.length;
    currentTopic.value = currentTopicText.substring(0, charIndex.value);
  }

  let typeSpeed = isDeleting.value ? 10 : 15;

  if (!isDeleting.value && charIndex.value >= currentTopicText.length) {
    typeSpeed = 800;
    isDeleting.value = true;
  } else if (isDeleting.value && charIndex.value <= 0) {
    isDeleting.value = false;
    topicIndex.value = (topicIndex.value + 1) % topics.length;
    charIndex.value = 0;
    typeSpeed = 200;
  }

  setTimeout(typeTopic, typeSpeed);
};

onMounted(() => {
  typeTopic();
  fetchAnnouncements();
  fetchOpportunities();
});

const handleSearch = () => {
  if (searchQuery.value.trim()) {
    router.push(`/courses?search=${searchQuery.value}`);
  }
};

// Actual course data from your curriculum
const courses = ref([
  {
    id: 1,
    title: 'HTML & CSS Fundamentals',
    category: 'Web Development',
    image: '/background.jpg',
    description: 'Learn the building blocks of web development'
  },
  {
    id: 2,
    title: 'Cybersecurity Basics',
    category: 'Cybersecurity',
    image: '/cyber.png',
    description: 'Protect yourself and your data online'
  },
  {
    id: 3,
    title: 'Media Information Literacy',
    category: 'Digital Literacy',
    emoji: '📚',
    description: 'Navigate the digital world safely and responsibly'
  },
  {
    id: 4,
    title: 'Coding for Kids',
    category: 'Programming',
    image: '/kids.png',
    description: 'Fun and interactive coding lessons designed for young learners'
  }
]);

// Show first 4 courses as popular
const popularCourses = computed(() => courses.value.slice(0, 4));

const goToCourse = (courseId) => {
  router.push(`/courses`);
};

const homeAnnouncements = computed(() => {
  const active = announcements.value.filter((a) => !isExpired(a.expiry_date));
  const featured = active.filter((a) => a.is_featured);
  const list = featured.length ? featured : active;
  return list.slice(0, 6);
});

const homeOpportunities = computed(() => opportunities.value.slice(0, 6));

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

const fetchHomeFeed = async () => {
  try {
    const { data } = await axios.get(`${API_BASE_URL}/catalog/home-feed`);
    announcements.value = data.announcements || [];
    opportunities.value = data.opportunities || [];
  } catch (error) {
    console.error('Error fetching home feed:', error);
    announcements.value = [];
    opportunities.value = [];
  }
};

onMounted(() => {
  fetchHomeFeed();
});
</script>

<style scoped>
.landing-page {
  min-height: 100dvh;
  width: 100%;
  max-width: 100vw;
  overflow-x: hidden;
  background: linear-gradient(135deg, #f7fafc 0%, #e2e8f0 100%);
}

/* Hero Section */
.hero-section {
  color: white;
  padding: 2rem 0;
  text-align: center;
  position: relative;
  overflow: hidden;
  min-height: min(100dvh, 900px);
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
  background: url('/publica.png') no-repeat center center;
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
  max-width: 800px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  text-align: center;
}

.hero-brand {
  margin-bottom: 1rem;
}

.hero-logo {
  width: min(220px, 72vw);
  max-height: 72px;
  height: auto;
  object-fit: contain;
  filter: drop-shadow(0 2px 10px rgba(0, 0, 0, 0.45));
}


.learner-badge {
  display: inline-block;
  background: rgba(255, 255, 255, 0.2);
  backdrop-filter: blur(10px);
  color: white;
  padding: 0.5rem 1.5rem;
  border-radius: 50px;
  font-size: 0.9rem;
  font-weight: 500;
  margin-bottom: 1.5rem;
  border: 1px solid rgba(255, 255, 255, 0.3);
}

.hero-title {
  font-size: 3.5rem;
  font-weight: 700;
  color: #FFFFFF;
  margin-bottom: 1rem;
  text-shadow: 0 2px 8px rgba(0, 0, 0, 0.8);
  line-height: 1.2;
}

.hero-subtitle {
  font-size: 1.2rem;
  color: #FFFFFF;
  margin-bottom: 2rem;
  opacity: 0.95;
  max-width: 600px;
  text-shadow: 0 1px 4px rgba(0, 0, 0, 0.8);
  font-weight: 400;
}

.typing-text {
  color: #ff9900;
  font-weight: 600;
}

.typing-cursor {
  animation: blink 1s infinite;
  color: #ff9900;
  font-weight: 600;
}

@keyframes blink {
  0%, 50% { opacity: 1; }
  51%, 100% { opacity: 0; }
}

/* Hero Search Bar */
.hero-search {
  display: flex;
  max-width: 600px;
  margin: 0 auto 3rem auto;
  gap: 0.5rem;
}

.hero-search .search-input {
  flex: 1;
  padding: 1rem 1.5rem;
  border: none;
  border-radius: 50px;
  font-size: 1rem;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
}

.hero-search .search-input:focus {
  outline: none;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
}

.hero-search .search-btn {
  padding: 1rem 2rem;
  background: #ff9900;
  color: white;
  border: none;
  border-radius: 50px;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  box-shadow: 0 4px 15px rgba(255, 153, 0, 0.3);
}

.hero-search .search-btn:hover {
  background: #e68600;
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(255, 153, 0, 0.4);
}

/* Hero Statistics */
.hero-stats {
  display: flex;
  gap: 2rem;
  justify-content: center;
  flex-wrap: nowrap;
  margin: 2rem auto 0 auto;
}

.stat-item {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  background: rgba(255, 255, 255, 0.15);
  backdrop-filter: blur(10px);
  padding: 1rem 1.5rem;
  border-radius: 12px;
  border: 1px solid rgba(255, 255, 255, 0.2);
}

.stat-icon {
  font-size: 1.5rem;
  color: #ff9900;
}

.stat-info {
  display: flex;
  flex-direction: column;
}

.stat-value {
  font-size: 1.1rem;
  font-weight: 700;
  color: white;
}

.stat-label {
  font-size: 0.8rem;
  color: rgba(255, 255, 255, 0.8);
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
  margin-bottom: 0.5rem;
}

.feed-lead {
  text-align: center;
  color: #6b7280;
  max-width: 640px;
  margin: 0 auto 2rem;
  line-height: 1.5;
}

.opportunities-block {
  margin-top: 2.5rem;
}

.opp-heading {
  text-align: center;
  font-size: 1.25rem;
  color: #1f2937;
  margin-bottom: 1rem;
}

.opportunities-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(min(100%, 260px), 1fr));
  gap: 1.25rem;
}

.opportunity-card {
  background: #fff;
  border-radius: 12px;
  padding: 1.25rem 1.35rem;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.06);
  border: 1px solid #e5e7eb;
}

.opp-type {
  font-size: 0.75rem;
  text-transform: uppercase;
  letter-spacing: 0.04em;
  color: #6366f1;
  font-weight: 700;
  margin-bottom: 0.35rem;
}

.opportunity-card h4 {
  margin: 0 0 0.35rem;
  color: #111827;
  font-size: 1.1rem;
}

.opp-org {
  margin: 0 0 0.5rem;
  color: #4b5563;
  font-size: 0.95rem;
}

.opp-meta {
  margin: 0 0 1rem;
  color: #6b7280;
  font-size: 0.85rem;
}

.announcements-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(min(100%, 280px), 1fr));
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

/* Popular Courses Section */
.courses-section {
  padding: 5rem 0;
  background: #f8f9fa;
}

.section-title {
  font-size: 2.5rem;
  font-weight: 700;
  color: #1a365d;
  text-align: center;
  margin-bottom: 1rem;
}

.section-subtitle {
  text-align: center;
  color: #6c757d;
  font-size: 1.1rem;
  margin-bottom: 3rem;
  max-width: 600px;
  margin-left: auto;
  margin-right: auto;
}

.courses-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 1.5rem;
  max-width: 1400px;
  margin: 0 auto;
}

.course-card {
  background: white;
  border-radius: 20px;
  padding: 2rem;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
  transition: transform 0.3s ease, box-shadow 0.3s ease;
  cursor: pointer;
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  border: 1px solid #e5e7eb;
}

.course-card:hover {
  transform: translateY(-8px);
  box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
  border-color: #e68c07;
}

.course-icon-wrapper {
  width: 120px;
  height: 120px;
  border-radius: 50%;
  overflow: hidden;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 1.5rem;
  background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
}

.course-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.course-emoji {
  font-size: 4rem;
  line-height: 1;
}

.course-category {
  background: linear-gradient(135deg, #e68c07 0%, #cc8809 100%);
  color: white;
  padding: 0.5rem 1rem;
  border-radius: 25px;
  font-size: 0.8rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  margin-bottom: 1rem;
}

.course-content {
  width: 100%;
  display: flex;
  flex-direction: column;
  align-items: center;
}

.course-content h3 {
  font-size: 1.25rem;
  font-weight: 700;
  color: #1f2937;
  margin-bottom: 0.75rem;
  line-height: 1.4;
}

.course-description {
  color: #6b7280;
  font-size: 0.85rem;
  margin-bottom: 0.75rem;
}

.view-all-container {
  display: flex;
  justify-content: center;
  margin-top: 2rem;
}

.view-all-btn {
  background: linear-gradient(135deg, #e68c07 0%, #cc8809 100%);
  color: white;
  padding: 0.875rem 2.5rem;
  border: none;
  border-radius: 50px;
  font-weight: 600;
  font-size: 1rem;
  cursor: pointer;
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.view-all-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(230, 140, 7, 0.4);
}

/* Why Shieldmaidens Section */
.why-section {
  padding: 5rem 0;
  background: #1f2937;
}

.why-section .section-title {
  color: white;
}

.why-section .features-grid {
  max-width: 1200px;
  margin: 0 auto;
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 2rem;
}

.why-section .feature-card {
  background: #374151;
  border-radius: 16px;
  padding: 2rem;
  text-align: center;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
  transition: transform 0.3s ease;
}

.why-section .feature-card:hover {
  transform: translateY(-4px);
}

.why-section .feature-icon {
  width: 100px;
  height: 100px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 1.5rem auto;
  overflow: hidden;
}

.why-section .feature-icon img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.why-section .feature-card h3 {
  font-size: 1.3rem;
  font-weight: 700;
  color: white;
  margin-bottom: 1rem;
}

.why-section .feature-card p {
  color: #d1d5db;
  line-height: 1.6;
  font-size: 0.95rem;
}

/* Hero Section */
.hero-section {
  min-height: 100vh;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  position: relative;
  overflow: hidden;
  padding: 2rem 0;
}

/* CTA Section */
.cta-section {
  padding: 5rem 0;
  background: linear-gradient(135deg, #1a365d 0%, #2d3748 100%);
  color: white;
  text-align: center;
}

.cta-content {
  max-width: 800px;
  margin: 0 auto;
  padding: 0 1rem;
}

.cta-content h2 {
  font-size: 2.5rem;
  font-weight: 700;
  margin-bottom: 1rem;
}

.cta-content p {
  font-size: 1.2rem;
  margin-bottom: 2rem;
  opacity: 0.9;
}

.cta-section .cta-buttons {
  justify-content: center;
}

.cta-section .btn-primary {
  background: linear-gradient(135deg, #e68c07 0%, #cc8809 100%);
  color: white;
}

.cta-section .btn-primary:hover {
  background: linear-gradient(135deg, #cc8809 0%, #b37a08 100%);
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(230, 140, 7, 0.4);
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

  .learner-badge {
    font-size: 0.8rem;
    padding: 0.4rem 1rem;
    margin-bottom: 1rem;
  }

  .hero-title {
    font-size: 2rem;
    margin-bottom: 0.75rem;
  }

  .hero-subtitle {
    font-size: 1rem;
    margin-bottom: 1.5rem;
    max-width: 350px;
  }

  .hero-search {
    flex-direction: column;
    gap: 0.75rem;
    max-width: 350px;
    margin: 0 auto 3rem auto;
  }

  .hero-search .search-input {
    padding: 0.875rem 1rem;
  }

  .hero-search .search-btn {
    padding: 0.875rem 1.5rem;
    justify-content: center;
  }

  .hero-stats {
    flex-direction: row;
    flex-wrap: nowrap;
    gap: 1rem;
    overflow-x: auto;
    padding: 0 1rem;
    justify-content: center;
  }

  .stat-item {
    padding: 0.75rem 1rem;
  }

  .stat-icon {
    font-size: 1.25rem;
  }

  .stat-value {
    font-size: 1rem;
  }

  .courses-grid {
    grid-template-columns: 1fr;
  }

  .course-icon-wrapper {
    width: 80px;
    height: 80px;
  }

  .course-emoji {
    font-size: 3rem;
  }

  .why-section .features-grid {
    grid-template-columns: 1fr;
    gap: 1.5rem;
  }
}

@media (min-width: 768px) and (max-width: 1024px) {
  .courses-grid {
    grid-template-columns: repeat(2, 1fr);
    gap: 1.25rem;
  }

  .course-icon-wrapper {
    width: 100px;
    height: 100px;
  }

  .why-section .features-grid {
    grid-template-columns: repeat(2, 1fr);
    gap: 1.5rem;
  }

  .stat-label {
    font-size: 0.75rem;
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
  
  .courses-section {
    padding: 3rem 0;
  }

  .section-title {
    font-size: 1.75rem;
    padding: 0 1rem;
  }

  .section-subtitle {
    font-size: 1rem;
    padding: 0 1rem;
  }

  .why-section {
    padding: 3rem 0;
  }

  .why-section .section-title {
    font-size: 1.75rem;
    padding: 0 1rem;
  }

  .why-section .features-grid {
    grid-template-columns: 1fr;
    gap: 1rem;
    padding: 0 1rem;
  }

  .why-section .feature-card {
    padding: 1.5rem;
    border-radius: 12px;
  }

  .why-section .feature-icon {
    font-size: 2.5rem;
    margin-bottom: 1rem;
  }

  .why-section .feature-card h3 {
    font-size: 1.25rem;
    margin-bottom: 0.75rem;
  }

  .why-section .feature-card p {
    font-size: 0.875rem;
  }

  .cta-section {
    padding: 3rem 0;
  }

  .cta-content h2 {
    font-size: 1.75rem;
  }

  .cta-content p {
    font-size: 1rem;
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
  
  .learner-badge {
    font-size: 0.75rem;
    padding: 0.35rem 0.875rem;
  }

  .hero-title {
    font-size: 1.75rem;
    margin-bottom: 0.75rem;
  }
  
  .hero-subtitle {
    font-size: 0.9rem;
    margin-bottom: 1.25rem;
    max-width: 350px;
  }

  .hero-search {
    flex-direction: column;
    gap: 0.5rem;
    max-width: 280px;
    margin: 0 auto 3rem auto;
  }

  .hero-search .search-input {
    padding: 0.75rem 1rem;
    font-size: 0.9rem;
  }

  .hero-search .search-btn {
    padding: 0.75rem 1.25rem;
    font-size: 0.9rem;
  }

  .hero-stats {
    flex-direction: column;
    gap: 0.75rem;
    align-items: center;
  }

  .stat-item {
    padding: 0.625rem 0.875rem;
  }

  .stat-icon {
    font-size: 1.125rem;
  }

  .stat-value {
    font-size: 0.9rem;
  }

  .stat-label {
    font-size: 0.7rem;
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

  .courses-section {
    padding: 2rem 0;
  }

  .section-title {
    font-size: 1.5rem;
  }

  .courses-grid {
    padding: 0 0.75rem;
  }

  .course-image {
    height: 120px;
  }

  .why-section {
    padding: 2rem 0;
  }

  .why-section .section-title {
    font-size: 1.5rem;
  }

  .why-section .features-grid {
    padding: 0 0.75rem;
  }

  .why-section .feature-card {
    padding: 1rem;
  }

  .why-section .feature-icon {
    font-size: 2rem;
    margin-bottom: 0.75rem;
  }

  .why-section .feature-card h3 {
    font-size: 1.1rem;
  }

  .why-section .feature-card p {
    font-size: 0.8rem;
  }

  .cta-section {
    padding: 2rem 0;
  }

  .cta-content h2 {
    font-size: 1.5rem;
  }

  .cta-content p {
    font-size: 0.9rem;
  }
}

/* Landscape orientation for mobile */
@media (max-width: 767px) and (orientation: landscape) {
  .hero-section {
    min-height: 100vh;
  }
  
  .learner-badge {
    font-size: 0.8rem;
  }

  .hero-title {
    font-size: 1.8rem;
  }
  
  .hero-subtitle {
    font-size: 0.85rem;
    margin-bottom: 1rem;
  }

  .hero-search {
    flex-direction: row;
    max-width: 400px;
  }

  .hero-stats {
    flex-direction: row;
    flex-wrap: wrap;
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
  .learner-badge {
    font-size: 0.85rem;
  }

  .hero-title {
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
