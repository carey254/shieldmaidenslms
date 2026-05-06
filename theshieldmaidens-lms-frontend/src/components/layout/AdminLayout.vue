<template>
  <div class="admin-layout">
    <!-- Sidebar -->
    <aside class="sidebar" :class="{ collapsed: sidebarCollapsed }">
      <!-- Logo -->
      <div class="sidebar-header">
        <div class="logo">
          <i class="fas fa-shield-alt"></i>
          <span v-if="!sidebarCollapsed" class="logo-text">LMS Admin</span>
        </div>
        <button @click="toggleSidebar" class="sidebar-toggle">
          <i :class="sidebarCollapsed ? 'fas fa-bars' : 'fas fa-times'"></i>
        </button>
      </div>

      <!-- Navigation -->
      <nav class="sidebar-nav">
        <router-link to="/admin/dashboard" class="nav-item" :class="{ active: isActive('/admin/dashboard') }">
          <i class="fas fa-tachometer-alt"></i>
          <span v-if="!sidebarCollapsed" class="nav-text">Dashboard</span>
        </router-link>
        
        <router-link to="/admin/users" class="nav-item" :class="{ active: isActive('/admin/users') }">
          <i class="fas fa-users"></i>
          <span v-if="!sidebarCollapsed" class="nav-text">Users</span>
        </router-link>
        
        <router-link to="/admin/facilitators" class="nav-item" :class="{ active: isActive('/admin/facilitators') }">
          <i class="fas fa-chalkboard-teacher"></i>
          <span v-if="!sidebarCollapsed" class="nav-text">Facilitators</span>
        </router-link>
        
        <router-link to="/admin/courses" class="nav-item" :class="{ active: isActive('/admin/courses') }">
          <i class="fas fa-graduation-cap"></i>
          <span v-if="!sidebarCollapsed" class="nav-text">Courses</span>
        </router-link>
        
        <router-link to="/admin/categories" class="nav-item" :class="{ active: isActive('/admin/categories') }">
          <i class="fas fa-tags"></i>
          <span v-if="!sidebarCollapsed" class="nav-text">Categories</span>
        </router-link>
        
        <router-link to="/admin/enrollments" class="nav-item" :class="{ active: isActive('/admin/enrollments') }">
          <i class="fas fa-user-graduate"></i>
          <span v-if="!sidebarCollapsed" class="nav-text">Enrollments</span>
        </router-link>
        
        <router-link to="/admin/assignments" class="nav-item" :class="{ active: isActive('/admin/assignments') }">
          <i class="fas fa-tasks"></i>
          <span v-if="!sidebarCollapsed" class="nav-text">Assignments</span>
        </router-link>
        
        <router-link to="/admin/exams" class="nav-item" :class="{ active: isActive('/admin/exams') }">
          <i class="fas fa-clipboard-list"></i>
          <span v-if="!sidebarCollapsed" class="nav-text">Exams</span>
        </router-link>
        
        <router-link to="/admin/reports" class="nav-item" :class="{ active: isActive('/admin/reports') }">
          <i class="fas fa-chart-bar"></i>
          <span v-if="!sidebarCollapsed" class="nav-text">Reports</span>
        </router-link>
        
        <router-link to="/admin/notifications" class="nav-item" :class="{ active: isActive('/admin/notifications') }">
          <i class="fas fa-bell"></i>
          <span v-if="!sidebarCollapsed" class="nav-text">Notifications</span>
        </router-link>
        
        <router-link to="/admin/settings" class="nav-item" :class="{ active: isActive('/admin/settings') }">
          <i class="fas fa-cog"></i>
          <span v-if="!sidebarCollapsed" class="nav-text">Settings</span>
        </router-link>
      </nav>
    </aside>

    <!-- Main Content Wrapper -->
    <div class="content-wrapper">
      <!-- Main Content -->
      <div class="main-content" :class="{ collapsed: sidebarCollapsed }">
        <!-- Top Bar -->
        <header class="topbar" :class="{ collapsed: sidebarCollapsed }">
          <div class="topbar-left">
            <button @click="toggleSidebar" class="mobile-sidebar-toggle">
              <i class="fas fa-bars"></i>
            </button>
            <div class="search-bar">
              <i class="fas fa-search"></i>
              <input 
                v-model="searchQuery" 
                type="text" 
                placeholder="Search for users, courses, assignments..."
                @input="handleSearch"
              >
            </div>
          </div>

          <div class="topbar-right">
            <!-- Top Navigation -->
            <nav class="top-nav">
              <router-link to="/admin/dashboard" class="nav-link" :class="{ active: isActive('/admin/dashboard') }">
                <i class="fas fa-tachometer-alt"></i>
                <span>Dashboard</span>
              </router-link>
              <router-link to="/admin/users" class="nav-link" :class="{ active: isActive('/admin/users') }">
                <i class="fas fa-users"></i>
                <span>Users</span>
              </router-link>
              <router-link to="/admin/courses" class="nav-link" :class="{ active: isActive('/admin/courses') }">
                <i class="fas fa-graduation-cap"></i>
                <span>Courses</span>
              </router-link>
              <router-link to="/admin/opportunities" class="nav-link" :class="{ active: isActive('/admin/opportunities') }">
                <i class="fas fa-bullhorn"></i>
                <span>Opportunities</span>
              </router-link>
              <router-link to="/admin/analytics" class="nav-link" :class="{ active: isActive('/admin/analytics') }">
                <i class="fas fa-chart-line"></i>
                <span>Analytics</span>
              </router-link>
              <router-link to="/admin/settings" class="nav-link" :class="{ active: isActive('/admin/settings') }">
                <i class="fas fa-cog"></i>
                <span>Settings</span>
              </router-link>
            </nav>

            <!-- Date Range Picker -->
            <div class="date-range">
              <i class="fas fa-calendar"></i>
              <span>{{ dateRange }}</span>
            </div>

            <!-- Notifications -->
            <div class="notifications" @click="toggleNotifications">
              <i class="fas fa-bell"></i>
              <span v-if="unreadNotifications > 0" class="notification-badge">{{ unreadNotifications }}</span>
            </div>

            <!-- User Profile -->
            <div class="user-profile" @click="toggleProfileMenu">
              <img v-if="user?.avatar" :src="user.avatar" :alt="user.name" class="user-avatar">
              <div v-else class="user-avatar-placeholder">
                {{ user?.name?.charAt(0)?.toUpperCase() || 'A' }}
              </div>
              <div class="user-info">
                <div class="user-name">{{ user?.name || 'Admin User' }}</div>
                <div class="user-role">{{ user?.role || 'Administrator' }}</div>
              </div>
            </div>

            <!-- Profile Dropdown -->
            <div v-if="showProfileMenu" class="profile-dropdown">
              <div class="dropdown-header">
                <img v-if="user?.avatar" :src="user.avatar" :alt="user.name" class="dropdown-avatar">
                <div v-else class="dropdown-avatar-placeholder">
                  {{ user?.name?.charAt(0)?.toUpperCase() || 'A' }}
                </div>
                <div class="dropdown-user-info">
                  <h4>{{ user?.name || 'Admin User' }}</h4>
                  <p>{{ user?.email || 'admin@theshieldmaidens.org' }}</p>
                  <span class="role-badge">{{ user?.role || 'Administrator' }}</span>
                </div>
              </div>
              <div class="dropdown-menu">
                <router-link to="/admin/profile" class="menu-item">
                  <i class="fas fa-user"></i> Profile Settings
                </router-link>
                <router-link to="/admin/security" class="menu-item">
                  <i class="fas fa-shield-alt"></i> Security
                </router-link>
                <router-link to="/admin/preferences" class="menu-item">
                  <i class="fas fa-cog"></i> Preferences
                </router-link>
                <div class="menu-divider"></div>
                <button @click="handleLogout" class="menu-item logout">
                  <i class="fas fa-sign-out-alt"></i> Logout
                </button>
              </div>
            </div>
          </div>
        </header>

        <!-- Page Content -->
        <main class="page-content">
          <slot></slot>
        </main>
      </div>
    </div>

    <!-- Notifications Dropdown -->
    <div v-if="showNotifications" class="notifications-dropdown" @click="showNotifications = false">
      <div class="notifications-content" @click.stop>
        <div class="notifications-header">
          <h3>Notifications</h3>
          <button @click="markAllAsRead" class="mark-all-read">Mark all as read</button>
        </div>
        <div class="notifications-list">
          <div v-for="notification in notifications" :key="notification.id" class="notification-item" :class="{ unread: !notification.read }">
            <div class="notification-icon" :class="notification.type">
              <i :class="notification.icon"></i>
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
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const route = useRoute()
const authStore = useAuthStore()

const user = computed(() => authStore.user)
const sidebarCollapsed = ref(false)
const showNotifications = ref(false)
const showProfileMenu = ref(false)
const searchQuery = ref('')
const dateRange = ref('May 12, 2024 - Jun 12, 2024')

// Mock data - replace with real data from API
const notifications = ref([
  {
    id: 1,
    type: 'info',
    icon: 'fas fa-info-circle',
    title: 'New user registered',
    message: 'Jane Doe has joined the platform',
    created_at: new Date(Date.now() - 1000 * 60 * 5),
    read: false
  },
  {
    id: 2,
    type: 'success',
    icon: 'fas fa-check-circle',
    title: 'Course published',
    message: 'Web Development Bootcamp has been published',
    created_at: new Date(Date.now() - 1000 * 60 * 30),
    read: false
  },
  {
    id: 3,
    type: 'warning',
    icon: 'fas fa-exclamation-triangle',
    title: 'Assignment submitted',
    message: 'John Smith submitted Python Basics assignment',
    created_at: new Date(Date.now() - 1000 * 60 * 60),
    read: true
  }
])

const unreadNotifications = computed(() => {
  return notifications.value.filter(n => !n.read).length
})

const isActive = (path) => {
  return route.path === path
}

const toggleSidebar = () => {
  sidebarCollapsed.value = !sidebarCollapsed.value
}

const toggleNotifications = () => {
  showNotifications.value = !showNotifications.value
  showProfileMenu.value = false
}

const toggleProfileMenu = () => {
  showProfileMenu.value = !showProfileMenu.value
  showNotifications.value = false
}

const handleSearch = () => {
  // Implement search functionality
  console.log('Searching for:', searchQuery.value)
}

const markAllAsRead = () => {
  notifications.value.forEach(n => n.read = true)
}

const formatTime = (date) => {
  const now = new Date()
  const diff = now - date
  const minutes = Math.floor(diff / 60000)
  const hours = Math.floor(diff / 3600000)
  const days = Math.floor(diff / 86400000)

  if (minutes < 60) return `${minutes} minutes ago`
  if (hours < 24) return `${hours} hours ago`
  return `${days} days ago`
}

const handleLogout = async () => {
  try {
    await authStore.logout()
    router.push('/login')
  } catch (error) {
    console.error('Logout error:', error)
  }
}

// Close dropdowns when clicking outside
const handleClickOutside = (event) => {
  if (!event.target.closest('.user-profile') && !event.target.closest('.profile-dropdown')) {
    showProfileMenu.value = false
  }
  if (!event.target.closest('.notifications') && !event.target.closest('.notifications-dropdown')) {
    showNotifications.value = false
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
.admin-layout {
  display: flex;
  width: 100%;
  flex: 1;
  background: #f8f9fa;
}

.content-wrapper {
  flex: 1;
  display: flex;
  flex-direction: column;
  margin-left: 260px;
  transition: margin-left 0.3s ease;
}

/* Sidebar */
.sidebar {
  width: 260px;
  height: 100vh;
  background: #fff8f9fa;
  color: #333;
  position: relative;
  left: 0;
  top: 0;
  transition: all 0.3s ease;
  z-index: 999;
}

.sidebar.collapsed {
  width: 70px;
}

.sidebar-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 20px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.logo {
  display: flex;
  align-items: center;
  gap: 12px;
  font-size: 1.2rem;
  font-weight: 700;
}

.logo i {
  color: #3498db;
  font-size: 1.5rem;
}

.logo-text {
  color: white;
}

.sidebar-toggle {
  background: none;
  border: none;
  color: white;
  font-size: 1.2rem;
  cursor: pointer;
  padding: 8px;
  border-radius: 6px;
  transition: background 0.3s ease;
}

.sidebar-toggle:hover {
  background: rgba(255, 255, 255, 0.1);
}

/* Navigation */
.sidebar-nav {
  padding: 20px 0;
}

.nav-item {
  display: flex;
  align-items: center;
  gap: 15px;
  padding: 15px 20px;
  color: rgba(0, 0, 0, 0.8);
  text-decoration: none;
  transition: all 0.3s ease;
  position: relative;
  font-weight: bold;
}

.nav-item:hover {
  background: rgba(0, 0, 0, 0.1);
  color: #333;
}

.nav-item.active {
  background: rgba(0, 0, 0, 0.1);
  border-left: 4px solid #333;
  color: #333;
}

.nav-item i {
  font-size: 1.1rem;
  width: 20px;
  text-align: center;
}

.nav-text {
  font-weight: bold;
}


/* Main Content */
.main-content {
  flex: 1;
  display: flex;
  flex-direction: column;
  min-height: 100vh;
}

.main-content.collapsed {
  margin-left: -190px; /* Adjust for sidebar collapse */
}

.content-wrapper.collapsed {
  margin-left: 70px;
}

.topbar.collapsed {
  left: 70px;
}

/* Top Bar */
.topbar {
  background: white;
  padding: 15px 30px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  position: fixed;
  top: 0;
  right: 0;
  left: 260px;
  z-index: 100;
  transition: left 0.3s ease;
}

.topbar-left {
  display: flex;
  align-items: center;
  gap: 20px;
}

.mobile-sidebar-toggle {
  display: none;
  background: none;
  border: none;
  font-size: 1.2rem;
  cursor: pointer;
  color: #333;
}

.search-bar {
  display: flex;
  align-items: center;
  gap: 10px;
  background: #f8f9fa;
  border: 1px solid #e9ecef;
  border-radius: 8px;
  padding: 10px 15px;
  min-width: 300px;
}

.search-bar i {
  color: #6c757d;
}

.search-bar input {
  border: none;
  background: none;
  outline: none;
  flex: 1;
  font-size: 0.9rem;
}

.topbar-right {
  display: flex;
  align-items: center;
  gap: 20px;
}

/* Top Navigation */
.top-nav {
  display: flex;
  align-items: center;
  gap: 5px;
  background: #f8f9fa;
  padding: 8px 16px;
  border-radius: 12px;
  border: 1px solid #e9ecef;
}

.nav-link {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 10px 16px;
  border-radius: 8px;
  text-decoration: none;
  color: #495057;
  font-weight: bold;
  font-size: 0.9rem;
  transition: all 0.3s ease;
  position: relative;
}

.nav-link:hover {
  background: white;
  color: #333;
  transform: translateY(-1px);
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.nav-link.active {
  background: rgba(0, 0, 0, 0.1);
  color: #333;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.nav-link i {
  font-size: 1rem;
}

.nav-link span {
  white-space: nowrap;
}

/* Active indicator */
.nav-link.active::after {
  content: '';
  position: absolute;
  bottom: -2px;
  left: 50%;
  transform: translateX(-50%);
  width: 4px;
  height: 4px;
  background: #333;
  border-radius: 50%;
}

.date-range {
  display: flex;
  align-items: center;
  gap: 8px;
  color: #6c757d;
  font-size: 0.9rem;
}

.notifications {
  position: relative;
  cursor: pointer;
  color: #6c757d;
  font-size: 1.2rem;
}

.notification-badge {
  position: absolute;
  top: -5px;
  right: -5px;
  background: #dc3545;
  color: white;
  font-size: 0.7rem;
  font-weight: 600;
  padding: 2px 6px;
  border-radius: 10px;
  min-width: 18px;
  text-align: center;
}

.user-profile {
  display: flex;
  align-items: center;
  gap: 12px;
  cursor: pointer;
  padding: 8px 12px;
  border-radius: 8px;
  transition: background 0.3s ease;
}

.user-profile:hover {
  background: #f8f9fa;
}

.user-avatar,
.user-avatar-placeholder {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  object-fit: cover;
}

.user-avatar-placeholder {
  background: #3498db;
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 600;
  font-size: 0.9rem;
}

.user-info {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
}

.user-name {
  font-weight: 600;
  color: #333;
  font-size: 0.9rem;
}

.user-role {
  font-size: 0.8rem;
  color: #6c757d;
}

/* Profile Dropdown */
.profile-dropdown {
  position: absolute;
  top: 100%;
  right: 0;
  background: white;
  border-radius: 12px;
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
  width: 320px;
  margin-top: 10px;
  z-index: 1001;
}

.dropdown-header {
  padding: 20px;
  border-bottom: 1px solid #e9ecef;
  display: flex;
  align-items: center;
  gap: 15px;
}

.dropdown-avatar,
.dropdown-avatar-placeholder {
  width: 50px;
  height: 50px;
  border-radius: 50%;
  object-fit: cover;
}

.dropdown-avatar-placeholder {
  background: #3498db;
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 600;
  font-size: 1.2rem;
}

.dropdown-user-info h4 {
  margin: 0 0 5px 0;
  font-size: 1rem;
  font-weight: 600;
  color: #333;
}

.dropdown-user-info p {
  margin: 0 0 8px 0;
  font-size: 0.85rem;
  color: #6c757d;
}

.role-badge {
  background: #e3f2fd;
  color: #1976d2;
  padding: 4px 8px;
  border-radius: 12px;
  font-size: 0.75rem;
  font-weight: 600;
}

.dropdown-menu {
  padding: 10px 0;
}

.menu-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px 20px;
  color: #333;
  text-decoration: none;
  transition: background 0.3s ease;
  font-size: 0.9rem;
}

.menu-item:hover {
  background: #f8f9fa;
}

.menu-item.logout {
  color: #dc3545;
}

.menu-item i {
  width: 20px;
  text-align: center;
}

.menu-divider {
  height: 1px;
  background: #e9ecef;
  margin: 10px 0;
}

/* Notifications Dropdown */
.notifications-dropdown {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: flex-start;
  justify-content: flex-end;
  padding: 20px;
  z-index: 1002;
}

.notifications-content {
  background: white;
  border-radius: 12px;
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
  width: 400px;
  max-height: 500px;
  overflow: hidden;
}

.notifications-header {
  padding: 20px;
  border-bottom: 1px solid #e9ecef;
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.notifications-header h3 {
  margin: 0;
  font-size: 1.1rem;
  font-weight: 600;
  color: #333;
}

.mark-all-read {
  background: none;
  border: none;
  color: #3498db;
  font-size: 0.85rem;
  cursor: pointer;
  font-weight: 500;
}

.notifications-list {
  max-height: 400px;
  overflow-y: auto;
}

.notification-item {
  display: flex;
  gap: 15px;
  padding: 15px 20px;
  border-bottom: 1px solid #f8f9fa;
  transition: background 0.3s ease;
}

.notification-item:hover {
  background: #f8f9fa;
}

.notification-item.unread {
  background: #e3f2fd;
}

.notification-icon {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1rem;
}

.notification-icon.info {
  background: #e3f2fd;
  color: #1976d2;
}

.notification-icon.success {
  background: #e8f5e8;
  color: #4caf50;
}

.notification-icon.warning {
  background: #fff3e0;
  color: #ff9800;
}

.notification-content {
  flex: 1;
}

.notification-content h4 {
  margin: 0 0 5px 0;
  font-size: 0.9rem;
  font-weight: 600;
  color: #333;
}

.notification-content p {
  margin: 0 0 5px 0;
  font-size: 0.85rem;
  color: #6c757d;
  line-height: 1.4;
}

.notification-time {
  font-size: 0.75rem;
  color: #6c757d;
}

/* Page Content */
.page-content {
  flex: 1;
  padding: 30px;
  padding-top: 100px;
  display: flex;
  flex-direction: column;
}


/* Responsive Design */
@media (max-width: 1024px) {
  .sidebar {
    transform: translateX(-100%);
  }
}

.sidebar.collapsed {
  transform: translateX(-100%);
}
  
  .main-content {
    margin-left: 0;
  }
  
  .mobile-sidebar-toggle {
    display: block;
  }

@media (max-width: 1200px) {
  .top-nav {
    gap: 2px;
    padding: 6px 12px;
  }
  
  .nav-link {
    padding: 8px 12px;
  }
  
  .nav-link span {
    display: none;
  }
}

@media (max-width: 768px) {
  .topbar {
    padding: 15px 20px;
  }
  
  .top-nav {
    display: none; /* Hide on mobile, use sidebar instead */
  }
  
  .search-bar {
    min-width: 200px;
  }
  
  .date-range {
    display: none;
  }
  
  .user-info {
    display: none;
  }
  
  .page-content {
    padding: 20px;
  }
  
  .notifications-content {
    width: 90vw;
    max-width: 350px;
  }
}
</style>
