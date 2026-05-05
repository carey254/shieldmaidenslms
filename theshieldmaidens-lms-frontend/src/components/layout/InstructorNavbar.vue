<template>
  <nav class="navbar">
    <div class="navbar-container">
      <!-- Logo Section -->
      <div class="navbar-brand">
        <div class="logo-container">
          <img src="/logo.png" alt="Shield Maidens Logo" class="logo-img" />
          <div class="logo-text">
            <span class="logo-title">Shield Maidens</span>
            <span class="logo-subtitle">Learning Management System</span>
          </div>
        </div>
      </div>

      <!-- Navigation Links -->
      <div class="navbar-menu">
        <router-link to="/instructor/dashboard" class="nav-link" :class="{ active: isActive('/instructor/dashboard') }">
          <i class="fas fa-tachometer-alt"></i>
          Dashboard
        </router-link>
        <router-link to="/instructor/courses" class="nav-link" :class="{ active: isActive('/instructor/courses') }">
          <i class="fas fa-book-open"></i>
          My Courses
        </router-link>
        <router-link to="/instructor/assessments" class="nav-link" :class="{ active: isActive('/instructor/assessments') }">
          <i class="fas fa-clipboard-check"></i>
          Assessments
        </router-link>
        <router-link to="/instructor/learners" class="nav-link" :class="{ active: isActive('/instructor/learners') }">
          <i class="fas fa-users"></i>
          Learners
        </router-link>
        <router-link to="/instructor/progress" class="nav-link" :class="{ active: isActive('/instructor/progress') }">
          <i class="fas fa-chart-line"></i>
          Progress
        </router-link>
        <router-link to="/instructor/communication" class="nav-link" :class="{ active: isActive('/instructor/communication') }">
          <i class="fas fa-comments"></i>
          Communication
        </router-link>
      </div>

      <!-- Right Section -->
      <div class="navbar-actions">
        <!-- Notifications -->
        <div class="notifications-dropdown" @click="toggleNotifications">
          <button class="notification-btn">
            <i class="fas fa-bell"></i>
            <span v-if="unreadNotifications > 0" class="notification-badge">{{ unreadNotifications }}</span>
          </button>
          <div v-if="showNotifications" class="notifications-menu">
            <div class="notifications-header">
              <h3>Notifications</h3>
              <button @click="markAllAsRead" class="mark-read-btn">Mark all as read</button>
            </div>
            <div class="notifications-list">
              <div v-if="notifications.length === 0" class="no-notifications">
                <i class="fas fa-bell-slash"></i>
                <p>No notifications</p>
              </div>
              <div v-for="notification in notifications" :key="notification.id" 
                   class="notification-item" 
                   :class="{ unread: !notification.read }"
                   @click="handleNotificationClick(notification)">
                <div class="notification-icon" :class="notification.type">
                  <i :class="getNotificationIcon(notification.type)"></i>
                </div>
                <div class="notification-content">
                  <h4>{{ notification.title }}</h4>
                  <p>{{ notification.message }}</p>
                  <span class="notification-time">{{ formatTime(notification.created_at) }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- User Profile Dropdown -->
        <div class="profile-dropdown" @click="toggleProfileMenu">
          <button class="profile-btn">
            <div class="profile-avatar">
              <img v-if="user?.avatar" :src="user.avatar" :alt="user.name" />
              <div v-else class="avatar-placeholder">
                {{ user?.name?.charAt(0)?.toUpperCase() || 'I' }}
              </div>
            </div>
            <div class="profile-info">
              <span class="profile-name">{{ user?.name || 'Instructor' }}</span>
              <span class="profile-role">{{ user?.role || 'Instructor' }}</span>
            </div>
            <i class="fas fa-chevron-down"></i>
          </button>
          <div v-if="showProfileMenu" class="profile-menu">
            <div class="profile-header">
              <div class="profile-avatar large">
                <img v-if="user?.avatar" :src="user.avatar" :alt="user.name" />
                <div v-else class="avatar-placeholder large">
                  {{ user?.name?.charAt(0)?.toUpperCase() || 'I' }}
                </div>
              </div>
              <div class="profile-details">
                <h3>{{ user?.name || 'Instructor' }}</h3>
                <p>{{ user?.email || 'instructor@theshieldmaidens.org' }}</p>
                <span class="profile-role-badge">{{ user?.role || 'Instructor' }}</span>
              </div>
            </div>
            <div class="profile-menu-items">
              <router-link to="/instructor/profile" class="menu-item">
                <i class="fas fa-user"></i>
                Profile Settings
              </router-link>
              <router-link to="/instructor/courses/create" class="menu-item">
                <i class="fas fa-plus"></i>
                Create Course
              </router-link>
              <router-link to="/instructor/analytics" class="menu-item">
                <i class="fas fa-chart-bar"></i>
                My Analytics
              </router-link>
              <router-link to="/instructor/help" class="menu-item">
                <i class="fas fa-question-circle"></i>
                Help & Support
              </router-link>
              <div class="menu-divider"></div>
              <button @click="handleLogout" class="menu-item logout">
                <i class="fas fa-sign-out-alt"></i>
                Logout
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </nav>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { useAuthStore } from '@/stores/auth';

const router = useRouter();
const route = useRoute();
const authStore = useAuthStore();

const user = computed(() => authStore.user);
const showNotifications = ref(false);
const showProfileMenu = ref(false);
const notifications = ref([]);
const unreadNotifications = ref(0);

// Click outside handlers
const handleClickOutside = (event) => {
  if (!event.target.closest('.notifications-dropdown')) {
    showNotifications.value = false;
  }
  if (!event.target.closest('.profile-dropdown')) {
    showProfileMenu.value = false;
  }
};

onMounted(() => {
  document.addEventListener('click', handleClickOutside);
  fetchNotifications();
});

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside);
});

// Methods
const isActive = (path) => {
  return route.path === path;
};

const toggleNotifications = () => {
  showNotifications.value = !showNotifications.value;
  showProfileMenu.value = false;
};

const toggleProfileMenu = () => {
  showProfileMenu.value = !showProfileMenu.value;
  showNotifications.value = false;
};

const fetchNotifications = async () => {
  try {
    // Fetch notifications from API
    const response = await fetch('/api/instructor/notifications', {
      headers: {
        'Authorization': `Bearer ${authStore.token}`,
        'Content-Type': 'application/json'
      }
    });
    
    if (response.ok) {
      const data = await response.json();
      notifications.value = data.notifications || [];
      unreadNotifications.value = notifications.value.filter(n => !n.read).length;
    }
  } catch (error) {
    console.error('Error fetching notifications:', error);
    // Set empty state on error
    notifications.value = [];
    unreadNotifications.value = 0;
  }
};

const markAllAsRead = async () => {
  try {
    await fetch('/api/instructor/notifications/mark-all-read', {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${authStore.token}`,
        'Content-Type': 'application/json'
      }
    });
    
    notifications.value.forEach(notification => {
      notification.read = true;
    });
    unreadNotifications.value = 0;
  } catch (error) {
    console.error('Error marking notifications as read:', error);
  }
};

const handleNotificationClick = async (notification) => {
  if (!notification.read) {
    try {
      await fetch(`/api/instructor/notifications/${notification.id}/read`, {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${authStore.token}`,
          'Content-Type': 'application/json'
        }
      });
      
      notification.read = true;
      unreadNotifications.value = Math.max(0, unreadNotifications.value - 1);
    } catch (error) {
      console.error('Error marking notification as read:', error);
    }
  }
  
  // Handle notification action (e.g., navigate to relevant page)
  if (notification.action_url) {
    router.push(notification.action_url);
  }
};

const handleLogout = async () => {
  try {
    await authStore.logout();
    router.push('/instructor/login');
  } catch (error) {
    console.error('Logout error:', error);
  }
};

const getNotificationIcon = (type) => {
  const icons = {
    'submission': 'fas fa-file-alt',
    'message': 'fas fa-envelope',
    'course': 'fas fa-graduation-cap',
    'assignment': 'fas fa-clipboard-check',
    'student': 'fas fa-user',
    'announcement': 'fas fa-bullhorn',
    'error': 'fas fa-exclamation-triangle',
    'success': 'fas fa-check-circle',
    'info': 'fas fa-info-circle'
  };
  return icons[type] || 'fas fa-bell';
};

const formatTime = (timestamp) => {
  const date = new Date(timestamp);
  const now = new Date();
  const diff = now - date;
  
  if (diff < 60000) return 'Just now';
  if (diff < 3600000) return `${Math.floor(diff / 60000)} min ago`;
  if (diff < 86400000) return `${Math.floor(diff / 3600000)} hours ago`;
  if (diff < 604800000) return `${Math.floor(diff / 86400000)} days ago`;
  
  return date.toLocaleDateString();
};
</script>

<style scoped>
.navbar {
  background: linear-gradient(135deg, #28a745, #20c997);
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  z-index: 1000;
}

.navbar-container {
  max-width: 1400px;
  margin: 0 auto;
  padding: 0 20px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  height: 70px;
}

/* Logo Section */
.navbar-brand {
  display: flex;
  align-items: center;
}

.logo-container {
  display: flex;
  align-items: center;
  gap: 12px;
}

.logo-img {
  height: 40px;
  width: auto;
  object-fit: contain;
}

.logo-text {
  display: flex;
  flex-direction: column;
}

.logo-title {
  color: white;
  font-size: 1.25rem;
  font-weight: 700;
  line-height: 1.2;
}

.logo-subtitle {
  color: rgba(255, 255, 255, 0.9);
  font-size: 0.75rem;
  font-weight: 500;
  line-height: 1.2;
}

/* Navigation Links */
.navbar-menu {
  display: flex;
  align-items: center;
  gap: 8px;
}

.nav-link {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 10px 16px;
  color: rgba(255, 255, 255, 0.9);
  text-decoration: none;
  border-radius: 8px;
  font-weight: 500;
  font-size: 0.9rem;
  transition: all 0.3s ease;
  position: relative;
}

.nav-link:hover {
  background: rgba(255, 255, 255, 0.1);
  color: white;
}

.nav-link.active {
  background: rgba(255, 255, 255, 0.2);
  color: white;
}

.nav-link.active::after {
  content: '';
  position: absolute;
  bottom: -2px;
  left: 50%;
  transform: translateX(-50%);
  width: 30px;
  height: 3px;
  background: white;
  border-radius: 2px;
}

/* Right Section */
.navbar-actions {
  display: flex;
  align-items: center;
  gap: 20px;
}

/* Notifications Dropdown */
.notifications-dropdown {
  position: relative;
}

.notification-btn {
  background: none;
  border: none;
  color: white;
  font-size: 1.2rem;
  cursor: pointer;
  padding: 8px;
  border-radius: 8px;
  transition: all 0.3s ease;
  position: relative;
}

.notification-btn:hover {
  background: rgba(255, 255, 255, 0.1);
}

.notification-badge {
  position: absolute;
  top: 0;
  right: 0;
  background: #dc3545;
  color: white;
  font-size: 0.7rem;
  font-weight: 600;
  padding: 2px 6px;
  border-radius: 10px;
  min-width: 18px;
  text-align: center;
}

.notifications-menu {
  position: absolute;
  top: 100%;
  right: 0;
  width: 350px;
  background: white;
  border-radius: 12px;
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
  margin-top: 10px;
  max-height: 400px;
  overflow: hidden;
}

.notifications-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 16px 20px;
  border-bottom: 1px solid #f0f0f0;
}

.notifications-header h3 {
  color: #333;
  font-size: 1rem;
  font-weight: 600;
  margin: 0;
}

.mark-read-btn {
  background: none;
  border: none;
  color: #28a745;
  font-size: 0.85rem;
  font-weight: 500;
  cursor: pointer;
  padding: 4px 8px;
  border-radius: 4px;
  transition: all 0.3s ease;
}

.mark-read-btn:hover {
  background: #e8f5e8;
}

.notifications-list {
  max-height: 300px;
  overflow-y: auto;
}

.no-notifications {
  text-align: center;
  padding: 40px 20px;
  color: #999;
}

.no-notifications i {
  font-size: 2rem;
  margin-bottom: 10px;
  display: block;
}

.no-notifications p {
  margin: 0;
  font-size: 0.9rem;
}

.notification-item {
  display: flex;
  gap: 12px;
  padding: 16px 20px;
  border-bottom: 1px solid #f8f9fa;
  cursor: pointer;
  transition: all 0.3s ease;
}

.notification-item:hover {
  background: #f8f9fa;
}

.notification-item.unread {
  background: #e8f5e8;
  border-left: 3px solid #28a745;
}

.notification-icon {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.9rem;
  flex-shrink: 0;
}

.notification-icon.submission {
  background: #e3f2fd;
  color: #1976d2;
}

.notification-icon.message {
  background: #f3e5f5;
  color: #7b1fa2;
}

.notification-icon.course {
  background: #e8f5e8;
  color: #2e7d32;
}

.notification-icon.assignment {
  background: #fff3e0;
  color: #f57c00;
}

.notification-icon.student {
  background: #e8f5e8;
  color: #2e7d32;
}

.notification-icon.announcement {
  background: #e3f2fd;
  color: #1976d2;
}

.notification-content {
  flex: 1;
  min-width: 0;
}

.notification-content h4 {
  color: #333;
  font-size: 0.9rem;
  font-weight: 600;
  margin: 0 0 4px 0;
  line-height: 1.3;
}

.notification-content p {
  color: #666;
  font-size: 0.85rem;
  margin: 0 0 8px 0;
  line-height: 1.4;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.notification-time {
  color: #999;
  font-size: 0.75rem;
}

/* Profile Dropdown */
.profile-dropdown {
  position: relative;
}

.profile-btn {
  display: flex;
  align-items: center;
  gap: 10px;
  background: rgba(255, 255, 255, 0.1);
  border: 1px solid rgba(255, 255, 255, 0.2);
  border-radius: 8px;
  padding: 6px 12px;
  color: white;
  cursor: pointer;
  transition: all 0.3s ease;
}

.profile-btn:hover {
  background: rgba(255, 255, 255, 0.2);
}

.profile-avatar {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  overflow: hidden;
  display: flex;
  align-items: center;
  justify-content: center;
}

.profile-avatar img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.avatar-placeholder {
  background: rgba(255, 255, 255, 0.3);
  color: white;
  font-weight: 600;
  font-size: 0.9rem;
}

.profile-info {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
}

.profile-name {
  font-size: 0.9rem;
  font-weight: 600;
  line-height: 1.2;
}

.profile-role {
  font-size: 0.75rem;
  opacity: 0.9;
  line-height: 1.2;
}

.profile-menu {
  position: absolute;
  top: 100%;
  right: 0;
  width: 280px;
  background: white;
  border-radius: 12px;
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
  margin-top: 10px;
  overflow: hidden;
}

.profile-header {
  padding: 20px;
  border-bottom: 1px solid #f0f0f0;
  display: flex;
  gap: 15px;
  align-items: center;
}

.profile-avatar.large {
  width: 48px;
  height: 48px;
}

.avatar-placeholder.large {
  font-size: 1.2rem;
}

.profile-details h3 {
  color: #333;
  font-size: 1rem;
  font-weight: 600;
  margin: 0 0 4px 0;
}

.profile-details p {
  color: #666;
  font-size: 0.85rem;
  margin: 0 0 8px 0;
}

.profile-role-badge {
  background: #28a745;
  color: white;
  font-size: 0.7rem;
  font-weight: 600;
  padding: 2px 8px;
  border-radius: 12px;
  text-transform: uppercase;
}

.profile-menu-items {
  padding: 8px 0;
}

.menu-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px 20px;
  color: #333;
  text-decoration: none;
  font-size: 0.9rem;
  font-weight: 500;
  transition: all 0.3s ease;
  border: none;
  background: none;
  width: 100%;
  text-align: left;
  cursor: pointer;
}

.menu-item:hover {
  background: #f8f9fa;
  color: #28a745;
}

.menu-item.logout {
  color: #dc3545;
}

.menu-item.logout:hover {
  background: #fff5f5;
  color: #dc3545;
}

.menu-divider {
  height: 1px;
  background: #f0f0f0;
  margin: 8px 0;
}

/* Responsive Design */
@media (max-width: 1024px) {
  .navbar-menu {
    display: none;
  }
}

@media (max-width: 768px) {
  .navbar-container {
    padding: 0 15px;
  }
  
  .logo-subtitle {
    display: none;
  }
  
  .navbar-actions {
    gap: 15px;
  }
  
  .notifications-menu {
    width: 300px;
  }
  
  .profile-menu {
    width: 250px;
  }
}

@media (max-width: 480px) {
  .navbar-container {
    gap: 10px;
  }
  
  .logo-title {
    font-size: 1rem;
  }
  
  .logo-img {
    height: 32px;
  }
  
  .profile-info {
    display: none;
  }
}
</style>
