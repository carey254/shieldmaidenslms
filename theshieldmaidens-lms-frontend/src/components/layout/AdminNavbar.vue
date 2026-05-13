<template>
  <nav class="navbar">
    <div class="navbar-container">
      <!-- Logo Section -->
      <div class="navbar-brand">
        <div class="logo-container">
          <img :src="PUBLIC_BRAND_LOGO" alt="The Shield Maidens" class="logo-img" decoding="async" />
          <div class="logo-text">
            <span class="logo-title">E-learning Management System</span>
            <span class="logo-subtitle">Learning Management System</span>
          </div>
        </div>
      </div>

      <!-- Navigation Links -->
      <div class="navbar-menu">
        <router-link to="/admin/dashboard" class="nav-link" :class="{ active: isActive('/admin/dashboard') }">
          Dashboard
        </router-link>
        <router-link to="/admin/announcements" class="nav-link" :class="{ active: isActive('/admin/announcements') }">
          Announcements
        </router-link>
        <router-link to="/admin/users" class="nav-link" :class="{ active: isActive('/admin/users') }">
          Users
        </router-link>
        <router-link to="/admin/courses" class="nav-link" :class="{ active: isActive('/admin/courses') }">
          Courses
        </router-link>
        <router-link to="/admin/opportunities" class="nav-link" :class="{ active: isActive('/admin/opportunities') }">
          Opportunities
        </router-link>
        <router-link to="/admin/analytics" class="nav-link" :class="{ active: '/admin/analytics' }">
          Analytics
        </router-link>
        <router-link to="/admin/settings" class="nav-link" :class="{ active: '/admin/settings' }">
          Settings
        </router-link>
      </div>

      <!-- Right Section -->
      <div class="navbar-actions">
        <!-- Notifications -->
        <div class="notifications-dropdown" @click="toggleNotifications">
          <button class="notification-btn">
            <span v-if="unreadNotifications > 0" class="notification-badge">{{ unreadNotifications }}</span>
          </button>
        </div>

        <!-- User Profile Dropdown -->
        <div class="profile-dropdown" @click="toggleProfileMenu">
          <button class="profile-btn">
            <div class="profile-avatar">
              <img v-if="user?.avatar" :src="user.avatar" :alt="user.name" />
              <div v-else class="avatar-placeholder">
                {{ user?.name?.charAt(0)?.toUpperCase() || 'A' }}
              </div>
            </div>
            <div class="profile-info">
              <span class="profile-name">{{ user?.email || 'Admin User' }}</span>
              <span class="profile-role">{{ user?.role || 'Administrator' }}</span>
            </div>
          </button>
          <div v-if="showProfileMenu" class="profile-menu">
            <div class="profile-header">
              <div class="profile-avatar large">
                <img v-if="user?.avatar" :src="user.avatar" :alt="user.name" />
                <div v-else class="avatar-placeholder large">
                  {{ user?.name?.charAt(0)?.toUpperCase() || 'A' }}
                </div>
              </div>
              <div class="profile-details">
                <h3>{{ user?.email || 'Admin User' }}</h3>
                <p>{{ user?.email || 'admin@theshieldmaidens.org' }}</p>
                <span class="profile-role-badge">{{ user?.role || 'Administrator' }}</span>
              </div>
            </div>
            <div class="profile-menu-items">
              <router-link to="/admin/profile" class="menu-item">
                Profile Settings
              </router-link>
              <router-link to="/admin/security" class="menu-item">
                Security
              </router-link>
              <router-link to="/admin/preferences" class="menu-item">
                Preferences
              </router-link>
              <div class="menu-divider"></div>
              <button @click="handleLogout" class="menu-item logout">
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
import { ref, computed, onMounted } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import { PUBLIC_BRAND_LOGO } from '@/config/branding';

const router = useRouter();
const route = useRoute();
const authStore = useAuthStore();

const isAdminPage = computed(() => {
  return route.path.startsWith('/admin');
});

const user = computed(() => authStore.user);
const showNotifications = ref(false);
const showProfileMenu = ref(false);
const notifications = ref([]);
const unreadNotifications = ref(0);

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

const handleLogout = async () => {
  try {
    await authStore.logout();
    router.push('/admin/login');
  } catch (error) {
    console.error('Logout error:', error);
  }
};

const currentDateTime = ref(new Date().toLocaleString());

onMounted(() => {
  setInterval(() => {
    currentDateTime.value = new Date().toLocaleString();
  }, 1000);
});
</script>

<style scoped>
.navbar {
  background: #fff8f9fa;
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
  gap: 12px;
}

.logo-container {
  display: flex;
  align-items: center;
  gap: 12px;
}

.logo-img {
  height: 50px;
  width: auto;
  object-fit: contain;
}

.logo-text {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 4px;
}

.logo-title {
  color: #333;
  font-size: 1.25rem;
  font-weight: bold;
  line-height: 1.2;
}

.logo-subtitle {
  color: #666;
  font-size: 0.75rem;
  font-weight: bold;
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
  border-radius: 8px;
  text-decoration: none;
  color: #333;
  font-weight: bold;
  transition: all 0.3s ease;
}

.nav-link:hover {
  background: rgba(0, 0, 0, 0.05);
  color: #333;
  transform: translateY(-1px);
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.nav-link.active {
  background: rgba(0, 0, 0, 0.1);
  color: #333;
}

.nav-link.active::after {
  content: '';
  position: absolute;
  bottom: -2px;
  left: 50%;
  transform: translateX(-50%);
  width: 30px;
  height: 3px;
  background: #333;
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
  z-index: 1001;
}

.notification-btn {
  background: none;
  border: none;
  color: #333;
  font-size: 1.2rem;
  cursor: pointer;
  padding: 8px;
  border-radius: 8px;
  transition: all 0.3s ease;
  position: relative;
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

.profile-btn {
  display: flex;
  align-items: center;
  gap: 10px;
  background: rgba(0, 0, 0, 0.05);
  border: 1px solid rgba(0, 0, 0, 0.1);
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.3s ease;
  position: relative;
}

.profile-btn:hover {
  background: rgba(0, 0, 0, 0.1);
  border-color: rgba(0, 0, 0, 0.2);
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
  background: rgba(0, 0, 0, 0.1);
  color: #333;
  font-weight: 600;
  font-size: 0.9rem;
}

.profile-info {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  gap: 2px;
}

.profile-name {
  font-size: 0.9rem;
  font-weight: 600;
  color: #333;
  line-height: 1.2;
}

.profile-role {
  font-size: 0.75rem;
  opacity: 0.9;
  line-height: 1.2;
}


/* Responsive Design */
@media (max-width: 1024px) {
  .navbar-menu {
    display: none;
  }
}

@media (max-width: 767px) {
  .mobile-menu-toggle {
    display: block;
  }
  
  .navbar-container {
    gap: 15px;
    padding: 0 15px;
  }
  
  .navbar-brand {
    gap: 8px;
  }
  
  .logo-title {
    font-size: 1.1rem;
  }
  
  .logo-subtitle {
    display: none;
  }
  
  .navbar-menu {
    display: block;
    position: fixed;
    top: 70px;
    left: 0;
    right: 0;
    background: white;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    border-radius: 0 0 12px 12px;
    max-height: 0;
    overflow: hidden;
    transition: max-height 0.3s ease;
    z-index: 999;
  }
  
  .navbar-menu.mobile-open {
    max-height: 400px;
    overflow-y: auto;
  }
  
  .nav-link {
    display: block;
    padding: 1rem 20px;
    border-bottom: 1px solid #f0f0f0;
    border-radius: 0;
    margin: 0;
    transition: background-color 0.2s ease;
  }
  
  .nav-link:hover {
    background: rgba(0, 0, 0, 0.05);
    transform: none;
    box-shadow: none;
  }
  
  .nav-link.active {
    background: rgba(0, 0, 0, 0.1);
    border-left: none;
    border-bottom: 2px solid #333;
  }
  
  .nav-link.active::after {
    display: none;
  }
  
  .navbar-actions {
    gap: 10px;
  }
  
  .profile-info {
    display: none;
  }
  
  .profile-avatar {
    width: 28px;
    height: 28px;
  }
  
  .profile-name,
  .profile-role {
    font-size: 0.8rem;
  }
  
  .notification-btn {
    padding: 6px;
    font-size: 1rem;
  }
  
  .notification-badge {
    font-size: 0.6rem;
    min-width: 16px;
    padding: 1px 4px;
  }
}

@media (max-width: 480px) {
  .navbar-container {
    gap: 8px;
    padding: 0 10px;
    height: 60px;
  }
  
  .logo-img {
    height: 35px;
  }
  
  .logo-title {
    font-size: 0.875rem;
  }
  
  .navbar-menu {
    top: 60px;
  }
  
  .nav-link {
    padding: 0.875rem 15px;
    font-size: 0.875rem;
  }
  
  .mobile-menu-toggle {
    padding: 6px;
  }
  
  .hamburger-line {
    width: 20px;
    height: 2px;
    margin: 3px 0;
  }
  
  .profile-avatar {
    width: 24px;
    height: 24px;
  }
  
  .notification-btn {
    padding: 4px;
    font-size: 0.875rem;
  }
}

/* Landscape orientation for mobile */
@media (max-width: 767px) and (orientation: landscape) {
  .navbar-container {
    height: 55px;
  }
  
  .navbar-menu {
    top: 55px;
  }
  
  .nav-link {
    padding: 0.75rem 15px;
  }
}

/* Touch-friendly interactions for mobile */
@media (hover: none) and (pointer: coarse) {
  .mobile-menu-toggle:hover {
    background: none;
  }
  
  .mobile-menu-toggle:active {
    background: rgba(0, 0, 0, 0.1);
  }
  
  .nav-link:hover {
    background: none;
  }
  
  .nav-link:active {
    background: rgba(0, 0, 0, 0.05);
  }
  
  .profile-btn:hover {
    background: rgba(0, 0, 0, 0.05);
  }
  
  .profile-btn:active {
    background: rgba(0, 0, 0, 0.1);
  }
}
</style>
