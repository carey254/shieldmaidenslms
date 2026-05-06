<script setup lang="ts">
import { RouterView, useRoute } from 'vue-router';
import { computed, ref, onMounted } from 'vue';
import Header from '@/components/layout/Header.vue';
import Footer from '@/components/layout/Footer.vue';
import UserProfileDropdown from '@/components/layout/UserProfileDropdown.vue';
import StudentNavbar from '@/components/layout/StudentNavbar.vue';
import InstructorSidebar from '@/components/layout/InstructorSidebar.vue';
import { useAuthStore } from '@/stores/auth';

const route = useRoute();
const authStore = useAuthStore();

// Check if current route is a student page
const isStudentPage = computed(() => {
  return route.path.startsWith('/dashboard') || 
         route.path.startsWith('/course/') || 
         route.path.startsWith('/settings') ||
         route.path.startsWith('/sessions') ||
         route.path.startsWith('/submissions') ||
         route.path.startsWith('/groups') ||
         route.path.startsWith('/community') ||
         route.path.startsWith('/certificates') ||
         route.path.startsWith('/my-courses');
});


// Check if current route is instructor page
const isInstructorPage = computed(() => {
  return route.path.startsWith('/instructor');
});

const isInstructorLoginRoute = computed(() => route.path === '/instructor/login');

const useInstructorShell = computed(
  () => route.path.startsWith('/instructor') && !isInstructorLoginRoute.value
);

const showAppFooter = computed(() => {
  if (isInstructorLoginRoute.value) return false;
  // Don't show footer for admin pages - AdminWrapper handles it
  if (route.path.startsWith('/admin')) return false;
  return (
    (!isStudentPage.value && !isInstructorPage.value) ||
    (isStudentPage.value && !isDashboard.value)
  );
});

// Check if current route is dashboard
const isDashboard = computed(() => {
  return route.path === '/dashboard';
});

// Notification state
const showNotifications = ref(false);
const unreadCount = ref(0);
const notifications = ref([]);

// Notification functions
const toggleNotifications = () => {
  showNotifications.value = !showNotifications.value;
  if (showNotifications.value) {
    markAllAsRead();
  }
};

const markAllAsRead = () => {
  notifications.value.forEach(notification => {
    notification.read = true;
  });
  unreadCount.value = 0;
};

// Generate real notification from API or real events
const generateNotification = (title, message, sender = 'System') => {
  return {
    id: Date.now(),
    title: title,
    message: message,
    sender: sender,
    time: 'Just now',
    read: false
  };
};

// Real-time notifications will be handled by API calls
onMounted(() => {
  // Fetch real notifications from API when implemented
});
</script>

<template>
  <div class="app" :class="{ 
    'student-page': isStudentPage, 
    'dashboard-page': isDashboard,
    'instructor-page': useInstructorShell,
    'instructor-auth-page': isInstructorLoginRoute,
    'admin-page': route.path.startsWith('/admin')
  }">
    <!-- Show Header only for public pages (not student, admin, or instructor pages) -->
    <Header v-if="!isStudentPage && !route.path.startsWith('/admin') && !isInstructorPage" />
    
        
    <!-- Instructor sidebar only when inside authenticated instructor area -->
    <InstructorSidebar v-if="useInstructorShell" />
    
    <!-- Show Student Navbar for student pages (except dashboard) -->
    <StudentNavbar v-if="isStudentPage && !isDashboard" />
    
    <!-- Show User Profile Dropdown only for dashboard -->
    <div v-if="isDashboard" class="student-header">
      <div class="notification-icon" @click="toggleNotifications">
        <span class="notification-bell">🔔</span>
        <span v-if="unreadCount > 0" class="notification-badge">{{ unreadCount }}</span>
      </div>
      
      <!-- Notification Dropdown -->
      <div v-if="showNotifications" class="notification-dropdown">
        <div class="notification-header">
          <h3>Notifications</h3>
          <span class="mark-all-read" @click="markAllAsRead">Mark all as read</span>
        </div>
        <div class="notification-list">
          <div v-if="notifications.length === 0" class="no-notifications">
            <span class="no-notifications-icon">📭</span>
            <p>No notifications yet</p>
            <small>Messages from instructors and admins will appear here</small>
          </div>
          <div 
            v-for="notification in notifications" 
            :key="notification.id" 
            class="notification-item"
            :class="{ 'unread': !notification.read }"
          >
            <div class="notification-content">
              <div class="notification-header-info">
                <h4>{{ notification.title }}</h4>
                <span class="notification-sender">{{ notification.sender }}</span>
              </div>
              <p>{{ notification.message }}</p>
              <span class="notification-time">{{ notification.time }}</span>
            </div>
          </div>
        </div>
      </div>
      
      <UserProfileDropdown />
    </div>
    
    <main class="main-content" :class="{ 'student-content': isStudentPage, 'dashboard-content': isDashboard }">
      <RouterView />
    </main>
    
    <!-- Show Footer for public pages, student pages (except dashboard), and admin pages -->
    <Footer v-if="showAppFooter" />
  </div>
</template>

<style>
/* Global styles */
:root {
  --primary: #1a365d;      /* Navy blue */
  --secondary: #ed8936;    /* Orange */
  --light: #f7fafc;        /* Cream white */
  --dark: #2d3748;         /* Dark gray */
  --gray: #718096;         /* Medium gray */
  --light-gray: #e2e8f0;   /* Light gray */
}

* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

html, body, #app {
  height: 100%;
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  line-height: 1.6;
  color: #213547;
  background-color: #ffffff;
  margin: 0;
  padding: 0;
}

#app {
  display: flex;
  flex-direction: column;
  height: 100vh;
}

.app {
  display: flex;
  flex-direction: column;
  height: 100vh;
  position: relative;
}

.main-content {
  flex: 1;
  width: 100%;
  position: relative;
  z-index: 1;
  padding-top: 120px; /* Account for fixed header */
}

/* Instructor pages - have navbar, so more padding */
.instructor-page .main-content {
  padding-top: 90px;
}

/* Instructor pages use left sidebar shell */
.instructor-page .main-content {
  padding-top: 24px;
  padding-left: 110px;
  background: #0f1116;
  min-height: 100vh;
}

@media (min-width: 1100px) {
  .instructor-page .main-content {
    padding-left: 280px;
  }
}

/* Only admin pages should have no scrolling */
.admin-page {
  overflow: hidden;
}

.admin-page .main-content {
  padding-top: 0 !important;
  height: 100vh;
  overflow: hidden;
}

/* Standalone auth pages: no top nav, full viewport for login */
.admin-auth-page .main-content,
.instructor-auth-page .main-content {
  padding-top: 0 !important;
  min-height: 100vh;
}

/* Student page specific styles */
.student-header {
  position: fixed;
  top: 0;
  right: 20px;
  z-index: 1001;
  padding: 20px;
  background: transparent;
  display: flex;
  align-items: center;
  gap: 15px;
}

/* Notification Styles */
.notification-icon {
  position: relative;
  cursor: pointer;
  transition: transform 0.2s ease;
}

.notification-icon:hover {
  transform: scale(1.1);
}

.notification-bell {
  font-size: 24px;
  color: #333;
}

.notification-badge {
  position: absolute;
  top: -8px;
  right: -8px;
  background: #ff4444;
  color: white;
  font-size: 12px;
  font-weight: bold;
  padding: 2px 6px;
  border-radius: 10px;
  min-width: 18px;
  text-align: center;
}

.notification-dropdown {
  position: absolute;
  top: 60px;
  right: 0;
  background: white;
  border-radius: 12px;
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
  width: 350px;
  max-height: 400px;
  overflow-y: auto;
  z-index: 1002;
}

.notification-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 16px 20px;
  border-bottom: 1px solid #f0f0f0;
}

.notification-header h3 {
  margin: 0;
  font-size: 16px;
  font-weight: 600;
  color: #333;
}

.mark-all-read {
  font-size: 12px;
  color: #007bff;
  cursor: pointer;
  transition: color 0.2s ease;
}

.mark-all-read:hover {
  color: #0056b3;
}

.notification-list {
  max-height: 300px;
  overflow-y: auto;
}

.notification-item {
  padding: 16px 20px;
  border-bottom: 1px solid #f8f9fa;
  transition: background-color 0.2s ease;
}

.notification-item:hover {
  background: #f8f9fa;
}

.notification-item.unread {
  background: #fff5f5;
  border-left: 4px solid #ff4444;
}

.notification-content h4 {
  margin: 0 0 8px 0;
  font-size: 14px;
  font-weight: 600;
  color: #333;
}

.notification-header-info {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 8px;
}

.notification-sender {
  font-size: 11px;
  font-weight: 600;
  color: #007bff;
  background: #e7f3ff;
  padding: 2px 8px;
  border-radius: 12px;
}

.notification-content p {
  margin: 0 0 8px 0;
  font-size: 13px;
  color: #666;
  line-height: 1.4;
}

.notification-time {
  font-size: 11px;
  color: #999;
}

.no-notifications {
  text-align: center;
  padding: 40px 20px;
  color: #999;
}

.no-notifications-icon {
  font-size: 48px;
  display: block;
  margin-bottom: 16px;
  opacity: 0.5;
}

.no-notifications p {
  margin: 0 0 8px 0;
  font-size: 14px;
  font-weight: 500;
  color: #666;
}

.no-notifications small {
  font-size: 12px;
  color: #999;
  line-height: 1.4;
}

.student-content {
  padding-top: 140px; /* Increased padding for taller student navbar */
  flex: 1; /* Take up remaining space to push footer down */
}

.student-page .main-content {
  background: transparent;
  flex: 1; /* Take up remaining space to push footer down */
}

/* Dashboard specific styles */
.dashboard-content {
  padding-top: 80px; /* Reduced padding for dashboard since no header */
  min-height: 100vh; /* Full height without footer */
}

.dashboard-page .main-content {
  background: transparent;
  margin-bottom: 0; /* No footer margin needed */
}



a {
  color: var(--secondary);
  text-decoration: none;
  transition: color 0.2s;
}

a:hover {
  color: #dd6b20; /* Darker orange */
}

.container {
  width: 100%;
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 1.5rem;
}

/* Buttons */
.btn {
  display: inline-block;
  padding: 0.6rem 1.25rem;
  border: none;
  border-radius: 4px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
  text-align: center;
}

.btn-primary {
  background-color: var(--secondary);
  color: white;
}

.btn-primary:hover {
  background-color: #dd6b20;
  transform: translateY(-1px);
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.btn-outline {
  background-color: transparent;
  border: 1px solid var(--primary);
  color: var(--primary);
}

.btn-outline:hover {
  background-color: rgba(26, 54, 93, 0.1);
}

/* Form elements */
input, textarea, select {
  font-family: inherit;
  font-size: 1rem;
}

/* Utility classes */
.text-center {
  text-align: center;
}

.mt-1 { margin-top: 0.5rem; }
.mt-2 { margin-top: 1rem; }
.mt-3 { margin-top: 1.5rem; }
.mt-4 { margin-top: 2rem; }
.mb-1 { margin-bottom: 0.5rem; }
.mb-2 { margin-bottom: 1rem; }
.mb-3 { margin-bottom: 1.5rem; }
.mb-4 { margin-bottom: 2rem; }
</style>
