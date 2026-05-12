<template>
  <div class="admin-layout">
    <!-- Top Navigation Bar (Fixed) -->
    <header class="admin-header">
      <div class="header-left">
        <div class="logo-mini">
          <img src="D:\LMS\theshieldmaidens-lms-frontend\public\logo.png" alt="The Shield Maidens" class="logo-img-mini" />
          <span class="logo-text-mini">Shield Maidens LMS</span>
        </div>
      </div>
      <div class="header-right">
        <!-- Admin Profile Dropdown -->
        <div class="admin-profile" @click="toggleDropdown">
          <div class="profile-avatar-mini">
            <img v-if="user?.avatar" :src="user.avatar" :alt="user.name" />
            <div v-else>{{ user?.name?.charAt(0)?.toUpperCase() || 'A' }}</div>
          </div>
          <span>{{ user?.name || 'Admin' }}</span>
          <i class="fas fa-chevron-down"></i>
          
          <!-- Profile Dropdown Menu -->
          <div v-if="showDropdown" class="profile-dropdown">
            <div class="profile-header">
              <div class="profile-avatar">
                <img v-if="user?.avatar" :src="user.avatar" :alt="user.name" />
                <div v-else>{{ user?.name?.charAt(0)?.toUpperCase() || 'A' }}</div>
              </div>
              <div class="profile-details">
                <h3>{{ user?.name || 'Admin User' }}</h3>
                <p>{{ user?.email || 'admin@theshieldmaidens.org' }}</p>
                <span class="role-badge">Administrator</span>
              </div>
            </div>
            <div class="profile-menu">
              <router-link to="/admin/profile" class="menu-item">
                <i class="fas fa-user-circle"></i>
                <div class="menu-content">
                  <span class="menu-title">Profile Settings</span>
                  <span class="menu-description">Manage your personal information</span>
                </div>
              </router-link>
              <router-link to="/admin/security" class="menu-item">
                <i class="fas fa-shield-alt"></i>
                <div class="menu-content">
                  <span class="menu-title">Security</span>
                  <span class="menu-description">Password and authentication</span>
                </div>
              </router-link>
              <router-link to="/admin/preferences" class="menu-item">
                <i class="fas fa-cog"></i>
                <div class="menu-content">
                  <span class="menu-title">Preferences</span>
                  <span class="menu-description">Customize your experience</span>
                </div>
              </router-link>
              <div class="menu-divider"></div>
              <button @click="handleLogout" class="menu-item logout">
                <i class="fas fa-sign-out-alt"></i>
                <div class="menu-content">
                  <span class="menu-title">Logout</span>
                  <span class="menu-description">Sign out of your account</span>
                </div>
              </button>
            </div>
          </div>
        </div>
      </div>
    </header>

    <!-- Main Container (Below Header) -->
    <div class="admin-container">
      <!-- Left Sidebar Navigation -->
      <aside class="admin-sidebar">
        <router-link to="/admin/dashboard" class="nav-item-compact" :class="{ active: isActive('/admin/dashboard') }">
          <i class="fas fa-tachometer-alt"></i>
          <span>Dashboard</span>
        </router-link>
        <router-link to="/admin/users" class="nav-item-compact" :class="{ active: isActive('/admin/users') }">
          <i class="fas fa-users"></i>
          <span>Users</span>
        </router-link>
        <router-link to="/admin/facilitators" class="nav-item-compact" :class="{ active: isActive('/admin/facilitators') }">
          <i class="fas fa-chalkboard-teacher"></i>
          <span>Facilitators</span>
        </router-link>
        <router-link to="/admin/courses" class="nav-item-compact" :class="{ active: isActive('/admin/courses') }">
          <i class="fas fa-graduation-cap"></i>
          <span>Courses</span>
        </router-link>
        <router-link to="/admin/categories" class="nav-item-compact" :class="{ active: isActive('/admin/categories') }">
          <i class="fas fa-tags"></i>
          <span>Categories</span>
        </router-link>
        <router-link to="/admin/enrollments" class="nav-item-compact" :class="{ active: isActive('/admin/enrollments') }">
          <i class="fas fa-user-graduate"></i>
          <span>Enrollments</span>
        </router-link>
        <router-link to="/admin/assignments" class="nav-item-compact" :class="{ active: isActive('/admin/assignments') }">
          <i class="fas fa-tasks"></i>
          <span>Assignments</span>
        </router-link>
        <router-link to="/admin/exams" class="nav-item-compact" :class="{ active: isActive('/admin/exams') }">
          <i class="fas fa-clipboard-list"></i>
          <span>Exams</span>
        </router-link>
        <router-link to="/admin/reports" class="nav-item-compact" :class="{ active: isActive('/admin/reports') }">
          <i class="fas fa-chart-bar"></i>
          <span>Reports</span>
        </router-link>
        <router-link to="/admin/notifications" class="nav-item-compact" :class="{ active: isActive('/admin/notifications') }">
          <i class="fas fa-bell"></i>
          <span>Notifications</span>
        </router-link>
        <router-link to="/admin/settings" class="nav-item-compact" :class="{ active: isActive('/admin/settings') }">
          <i class="fas fa-cog"></i>
          <span>Settings</span>
        </router-link>
        <div class="dropdown-divider"></div>
        <router-link to="/admin/profile" class="nav-item-compact">
          <i class="fas fa-user"></i>
          <span>Profile</span>
        </router-link>
        <router-link to="/admin/security" class="nav-item-compact">
          <i class="fas fa-shield-alt"></i>
          <span>Security</span>
        </router-link>
      </aside>

      <!-- Main Content Area -->
      <main class="admin-content">
        <router-view />
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const route = useRoute()
const authStore = useAuthStore()

const showDropdown = ref(false)
const selectedLanguage = ref('en')

const user = computed(() => authStore.user)

const isActive = (path) => {
  return route.path === path
}

const toggleDropdown = () => {
  showDropdown.value = !showDropdown.value
}

const handleLogout = async () => {
  try {
    await authStore.logout()
    router.push('/admin/login')
  } catch (error) {
    console.error('Logout error:', error)
  }
}

// Close dropdown when clicking outside
const handleClickOutside = (event) => {
  if (!event.target.closest('.admin-profile') && !event.target.closest('.nav-dropdown')) {
    showDropdown.value = false
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
})
</script>


<style scoped>
/* Admin Layout Structure */
.admin-layout {
  display: flex;
  flex-direction: column;
  height: 100vh;
  overflow: hidden;
}

/* Fixed Top Header */
.admin-header {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  height: 60px;
  background: #fff8f9fa;
  color: #333;
  padding: 8px 20px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  z-index: 1000;
}

/* Main Container (Below Header) */
.admin-container {
  display: flex;
  flex: 1;
  margin-top: 60px; /* Account for fixed header */
  height: calc(100vh - 60px);
  overflow: hidden;
}

/* Left Sidebar Navigation */
.admin-sidebar {
  width: 260px;
  background: #fff8f9fa;
  color: #333;
  padding: 20px 0;
  border-right: 1px solid rgba(0, 0, 0, 0.1);
  overflow-y: auto;
  height: 100%;
}

/* Main Content Area */
.admin-content {
  flex: 1;
  padding: 20px;
  overflow-y: auto;
  background: #ffffff;
  color: #213547;
}

/* Header Components */
.header-left {
  display: flex;
  align-items: center;
}

.logo-mini {
  display: flex;
  align-items: center;
  gap: 8px;
  font-weight: 600;
  font-size: 0.9rem;
}

.logo-img-mini {
  height: 40px;
  width: auto;
  object-fit: contain;
}

.logo-text-mini {
  color: #333;
  font-weight: bold;
}

.header-right {
  display: flex;
  align-items: center;
  gap: 15px;
}

.admin-profile {
  display: flex;
  align-items: center;
  gap: 8px;
  background: rgba(255, 255, 255, 0.1);
  padding: 6px 12px;
  border-radius: 6px;
  cursor: pointer;
  transition: background 0.3s ease;
  position: relative;
}

.admin-profile:hover {
  background: rgba(255, 255, 255, 0.2);
}

.profile-avatar-mini {
  width: 28px;
  height: 28px;
  border-radius: 50%;
  background: #f8f9fa;
  color: #333;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 600;
  font-size: 0.8rem;
}

.profile-avatar-mini img {
  width: 100%;
  height: 100%;
  border-radius: 50%;
  object-fit: cover;
}

.admin-profile span {
  font-size: 0.85rem;
  font-weight: 500;
}

.admin-profile i {
  font-size: 0.7rem;
  margin-left: 5px;
}

/* Profile Dropdown */
.profile-dropdown {
  position: absolute;
  top: 100%;
  right: 0;
  background: white;
  border-radius: 16px;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
  min-width: 320px;
  z-index: 1001;
  margin-top: 10px;
  overflow: hidden;
  border: 1px solid rgba(0, 0, 0, 0.08);
}

.profile-header {
  padding: 24px;
  background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
  border-bottom: 1px solid rgba(0, 0, 0, 0.08);
}

.profile-avatar {
  width: 60px;
  height: 60px;
  border-radius: 50%;
  background: #fff;
  color: #333;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
  font-size: 1.5rem;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  margin-bottom: 16px;
}

.profile-avatar img {
  width: 100%;
  height: 100%;
  border-radius: 50%;
  object-fit: cover;
}

.profile-details {
  text-align: center;
}

.profile-details h3 {
  margin: 0 0 8px 0;
  font-size: 1.1rem;
  font-weight: 600;
  color: #333;
}

.profile-details p {
  margin: 0 0 12px 0;
  font-size: 0.9rem;
  color: #6c757d;
}

.role-badge {
  background: #007bff;
  color: white;
  padding: 4px 12px;
  border-radius: 12px;
  font-size: 0.75rem;
  font-weight: 500;
}

.profile-menu {
  padding: 8px 0;
}

.menu-item {
  display: flex;
  align-items: center;
  gap: 16px;
  padding: 16px 24px;
  color: #333;
  text-decoration: none;
  transition: all 0.3s ease;
  border: none;
  background: none;
  width: 100%;
  text-align: left;
  cursor: pointer;
}

.menu-item:hover {
  background: #f8f9fa;
}

.menu-item.logout {
  color: #dc3545;
}

.menu-item i {
  width: 24px;
  text-align: center;
  font-size: 1.1rem;
  color: #6c757d;
}

.menu-item.logout i {
  color: #dc3545;
}

.menu-content {
  flex: 1;
}

.menu-title {
  display: block;
  font-weight: 600;
  font-size: 0.95rem;
  margin-bottom: 2px;
}

.menu-description {
  display: block;
  font-size: 0.8rem;
  color: #6c757d;
}

.menu-divider {
  height: 1px;
  background: #e9ecef;
  margin: 8px 0;
}

/* Navigation Items */
.nav-item-compact {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 6px;
  padding: 12px 16px;
  color: #495057;
  text-decoration: none;
  border-radius: 6px;
  transition: all 0.3s ease;
  font-size: 0.75rem;
  font-weight: bold;
  min-width: 0;
  border-bottom: 1px solid rgba(73, 80, 87, 0.1);
}

.nav-item-compact:hover {
  background: rgba(0, 0, 0, 0.1);
  color: #333;
  transform: translateY(-2px);
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
}

.nav-item-compact.active {
  background: rgba(0, 0, 0, 0.15);
  color: #333;
  border-bottom: 1px solid rgba(73, 80, 87, 0.1);
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
}

.nav-item-compact i {
  font-size: 0.9rem;
}

.nav-item-compact span {
  font-weight: bold;
  font-size: 0.7rem;
  text-align: center;
}

.dropdown-divider {
  height: 1px;
  background: rgba(0, 0, 0, 0.1);
  margin: 10px 16px;
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .admin-layout {
    min-height: 100dvh;
    height: auto;
    overflow: visible;
  }

  .admin-header {
    padding: 8px 12px;
  }

  .logo-text-mini {
    font-size: 0.8rem;
  }

  .admin-profile span {
    display: none;
  }

  .admin-container {
    flex-direction: column;
    margin-top: 60px;
    height: auto;
    min-height: calc(100dvh - 60px);
    overflow: visible;
  }

  .admin-sidebar {
    width: 100%;
    height: auto;
    max-height: none;
    padding: 8px;
    border-right: 0;
    border-bottom: 1px solid rgba(0, 0, 0, 0.1);
    display: flex;
    gap: 8px;
    overflow-x: auto;
    overflow-y: hidden;
    -webkit-overflow-scrolling: touch;
  }

  .nav-item-compact {
    flex: 0 0 auto;
    min-width: 86px;
    padding: 8px 10px;
    border-bottom: none;
    border: 1px solid rgba(73, 80, 87, 0.15);
  }

  .nav-item-compact i {
    font-size: 0.85rem;
  }

  .nav-item-compact span {
    font-size: 0.65rem;
    line-height: 1.2;
  }

  .dropdown-divider {
    display: none;
  }

  .admin-content {
    padding: 12px;
    overflow: visible;
  }

  .profile-dropdown {
    min-width: 260px;
    right: 0;
  }
}
</style>
