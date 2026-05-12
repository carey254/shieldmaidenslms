<template>
  <div class="global-mobile-nav">
    <!-- Mobile Menu Toggle -->
    <button 
      class="mobile-menu-toggle" 
      @click="toggleMobileMenu"
      :class="{ 'active': mobileMenuOpen }"
      aria-label="Toggle mobile menu"
    >
      <span class="hamburger-line"></span>
      <span class="hamburger-line"></span>
      <span class="hamburger-line"></span>
    </button>

    <!-- Mobile Menu Overlay -->
    <div 
      class="mobile-menu-overlay" 
      :class="{ 'active': mobileMenuOpen }"
      @click="closeMobileMenu"
    ></div>

    <!-- Mobile Menu Panel -->
    <nav class="mobile-menu-panel" :class="{ 'active': mobileMenuOpen }">
      <div class="mobile-menu-header">
        <div class="mobile-logo">
          <img src="/logo.png" alt="The Shield Maidens" class="mobile-logo-img">
          <span class="mobile-logo-text">Shieldmaidens</span>
        </div>
        <button class="mobile-close-btn" @click="closeMobileMenu" aria-label="Close menu">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="18" y1="6" x2="6" y2="18"></line>
            <line x1="6" y1="6" x2="18" y2="18"></line>
          </svg>
        </button>
      </div>

      <div class="mobile-menu-content">
        <!-- Navigation Links -->
        <div class="mobile-nav-section">
          <h3>Navigation</h3>
          <div class="mobile-nav-links">
            <router-link to="/" @click="closeMobileMenu" class="mobile-nav-link">
              <span class="nav-icon">🏠</span>
              <span>Home</span>
            </router-link>
            <router-link to="/courses" @click="closeMobileMenu" class="mobile-nav-link">
              <span class="nav-icon">📚</span>
              <span>Courses</span>
            </router-link>
            <router-link to="/dashboard" @click="closeMobileMenu" class="mobile-nav-link" v-if="!isAuthenticated">
              <span class="nav-icon">🎯</span>
              <span>Dashboard</span>
            </router-link>
          </div>
        </div>

        <!-- User Section -->
        <div class="mobile-nav-section" v-if="isAuthenticated">
          <h3>My Account</h3>
          <div class="mobile-nav-links">
            <router-link to="/dashboard" @click="closeMobileMenu" class="mobile-nav-link">
              <span class="nav-icon">📊</span>
              <span>Dashboard</span>
            </router-link>
            <router-link to="/student/courses" @click="closeMobileMenu" class="mobile-nav-link">
              <span class="nav-icon">📖</span>
              <span>My Courses</span>
            </router-link>
            <router-link to="/student/progress" @click="closeMobileMenu" class="mobile-nav-link">
              <span class="nav-icon">📈</span>
              <span>Progress</span>
            </router-link>
            <router-link to="/student/certificates" @click="closeMobileMenu" class="mobile-nav-link">
              <span class="nav-icon">🏆</span>
              <span>Certificates</span>
            </router-link>
            <router-link to="/student/settings" @click="closeMobileMenu" class="mobile-nav-link">
              <span class="nav-icon">⚙️</span>
              <span>Settings</span>
            </router-link>
          </div>
        </div>

        <!-- Admin Section -->
        <div class="mobile-nav-section" v-if="isAdmin">
          <h3>Admin</h3>
          <div class="mobile-nav-links">
            <router-link to="/admin/dashboard" @click="closeMobileMenu" class="mobile-nav-link">
              <span class="nav-icon">📊</span>
              <span>Dashboard</span>
            </router-link>
            <router-link to="/admin/courses" @click="closeMobileMenu" class="mobile-nav-link">
              <span class="nav-icon">📚</span>
              <span>Courses</span>
            </router-link>
            <router-link to="/admin/users" @click="closeMobileMenu" class="mobile-nav-link">
              <span class="nav-icon">👥</span>
              <span>Users</span>
            </router-link>
            <router-link to="/admin/analytics" @click="closeMobileMenu" class="mobile-nav-link">
              <span class="nav-icon">📈</span>
              <span>Analytics</span>
            </router-link>
          </div>
        </div>

        <!-- Auth Section -->
        <div class="mobile-nav-section" v-if="!isAuthenticated">
          <h3>Account</h3>
          <div class="mobile-nav-links">
            <router-link to="/login" @click="closeMobileMenu" class="mobile-nav-link">
              <span class="nav-icon">🔐</span>
              <span>Sign In</span>
            </router-link>
            <router-link to="/register" @click="closeMobileMenu" class="mobile-nav-link">
              <span class="nav-icon">📝</span>
              <span>Sign Up</span>
            </router-link>
          </div>
        </div>

        <!-- Actions -->
        <div class="mobile-nav-actions">
          <button v-if="isAuthenticated" @click="handleLogout" class="mobile-logout-btn">
            <span class="nav-icon">🚪</span>
            <span>Sign Out</span>
          </button>
        </div>
      </div>
    </nav>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const route = useRoute()
const authStore = useAuthStore()

const mobileMenuOpen = ref(false)

const isAuthenticated = computed(() => authStore.isAuthenticated)
const isAdmin = computed(() => authStore.user?.role === 'admin')

const toggleMobileMenu = () => {
  mobileMenuOpen.value = !mobileMenuOpen.value
  // Prevent body scroll when menu is open
  document.body.style.overflow = mobileMenuOpen.value ? 'hidden' : ''
}

const closeMobileMenu = () => {
  mobileMenuOpen.value = false
  document.body.style.overflow = ''
}

const handleLogout = async () => {
  try {
    await authStore.logout()
    closeMobileMenu()
    router.push('/login')
  } catch (error) {
    console.error('Logout error:', error)
  }
}

// Close menu when route changes
const handleRouteChange = () => {
  closeMobileMenu()
}

// Close menu on escape key
const handleEscapeKey = (event) => {
  if (event.key === 'Escape' && mobileMenuOpen.value) {
    closeMobileMenu()
  }
}

onMounted(() => {
  document.addEventListener('keydown', handleEscapeKey)
})

onUnmounted(() => {
  document.removeEventListener('keydown', handleEscapeKey)
  document.body.style.overflow = ''
})
</script>

<style scoped>
.global-mobile-nav {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  z-index: 9999;
  pointer-events: none;
}

.mobile-menu-toggle {
  position: fixed;
  top: 15px;
  left: 15px;
  z-index: 10000;
  background: rgba(0, 0, 0, 0.8);
  border: none;
  border-radius: 8px;
  padding: 12px;
  cursor: pointer;
  display: none;
  flex-direction: column;
  gap: 4px;
  transition: all 0.3s ease;
  pointer-events: all;
}

.mobile-menu-toggle:hover {
  background: rgba(0, 0, 0, 0.9);
}

.mobile-menu-toggle.active {
  background: #1a365d;
}

.hamburger-line {
  width: 25px;
  height: 3px;
  background: white;
  border-radius: 2px;
  transition: all 0.3s ease;
}

.mobile-menu-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  z-index: 9998;
  opacity: 0;
  visibility: hidden;
  transition: all 0.3s ease;
  pointer-events: none;
}

.mobile-menu-overlay.active {
  opacity: 1;
  visibility: visible;
  pointer-events: all;
}

.mobile-menu-panel {
  position: fixed;
  top: 0;
  left: 0;
  bottom: 0;
  width: 280px;
  background: white;
  box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
  z-index: 9999;
  transform: translateX(-100%);
  transition: transform 0.3s ease;
  overflow-y: auto;
  pointer-events: none;
}

.mobile-menu-panel.active {
  transform: translateX(0);
  pointer-events: all;
}

.mobile-menu-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1rem;
  border-bottom: 1px solid #e2e8f0;
  background: #f8fafc;
}

.mobile-logo {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.mobile-logo-img {
  height: 30px;
  width: auto;
}

.mobile-logo-text {
  font-weight: 600;
  color: #1a365d;
  font-size: 0.875rem;
}

.mobile-close-btn {
  background: none;
  border: none;
  padding: 0.25rem;
  border-radius: 4px;
  cursor: pointer;
  color: #64748b;
  transition: color 0.2s;
}

.mobile-close-btn:hover {
  color: #1a365d;
}

.mobile-menu-content {
  padding: 1rem;
}

.mobile-nav-section {
  margin-bottom: 1.5rem;
}

.mobile-nav-section:last-child {
  margin-bottom: 0;
}

.mobile-nav-section h3 {
  font-size: 0.75rem;
  font-weight: 600;
  color: #64748b;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  margin-bottom: 0.75rem;
  padding: 0 0.5rem;
}

.mobile-nav-links {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.mobile-nav-link {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.75rem 0.5rem;
  color: #374151;
  text-decoration: none;
  border-radius: 6px;
  transition: all 0.2s ease;
  font-size: 0.875rem;
}

.mobile-nav-link:hover {
  background: #f3f4f6;
  color: #1a365d;
}

.mobile-nav-link.router-link-active {
  background: #e0e7ff;
  color: #3730a3;
  font-weight: 500;
}

.nav-icon {
  font-size: 1.125rem;
  width: 20px;
  text-align: center;
}

.mobile-nav-actions {
  padding: 1rem 0.5rem;
  border-top: 1px solid #e2e8f0;
  margin-top: 1rem;
}

.mobile-logout-btn {
  width: 100%;
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.75rem;
  background: #ef4444;
  color: white;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-size: 0.875rem;
  transition: background 0.2s ease;
}

.mobile-logout-btn:hover {
  background: #dc2626;
}

/* Responsive display */
@media (max-width: 767px) {
  .mobile-menu-toggle {
    display: flex;
  }
}

/* Small phones */
@media (max-width: 480px) {
  .mobile-menu-toggle {
    top: 10px;
    left: 10px;
    padding: 10px;
  }
  
  .hamburger-line {
    width: 20px;
    height: 2px;
  }
  
  .mobile-menu-panel {
    width: 250px;
  }
  
  .mobile-menu-header {
    padding: 0.75rem;
  }
  
  .mobile-logo-img {
    height: 25px;
  }
  
  .mobile-logo-text {
    font-size: 0.75rem;
  }
  
  .mobile-menu-content {
    padding: 0.75rem;
  }
  
  .mobile-nav-link {
    padding: 0.625rem 0.375rem;
    font-size: 0.8rem;
  }
  
  .nav-icon {
    font-size: 1rem;
  width: 18px;
  }
}

/* Landscape */
@media (max-width: 767px) and (orientation: landscape) {
  .mobile-menu-toggle {
    top: 8px;
    left: 8px;
    padding: 8px;
  }
}

/* Touch interactions */
@media (hover: none) and (pointer: coarse) {
  .mobile-menu-toggle:hover {
    background: rgba(0, 0, 0, 0.8);
  }
  
  .mobile-menu-toggle:active {
    background: #1a365d;
  }
  
  .mobile-nav-link:hover {
    background: none;
  }
  
  .mobile-nav-link:active {
    background: #f3f4f6;
    transform: scale(0.98);
  }
  
  .mobile-close-btn:hover {
    color: #64748b;
  }
  
  .mobile-close-btn:active {
    color: #1a365d;
  }
}
</style>
