<template>
  <div class="user-profile-dropdown">
    <div class="profile-trigger" @click="toggleDropdown" :class="{ 'active': showDropdown }">
      <div class="user-avatar">
        <img v-if="user?.avatar" :src="user.avatar" :alt="user.name" />
        <div v-else class="avatar-placeholder">
          {{ getUserInitials() }}
        </div>
      </div>
      <span class="user-name">{{ user?.name || 'Student' }}</span>
      <svg class="dropdown-arrow" :class="{ 'rotated': showDropdown }" width="12" height="8" viewBox="0 0 12 8" fill="none">
        <path d="M1 1.5L6 6.5L11 1.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
      </svg>
    </div>

    <div v-if="showDropdown" class="dropdown-menu" @mouseleave="hideDropdown">
      <div class="dropdown-header">
        <div class="user-info">
          <div class="user-avatar large">
            <img v-if="user?.avatar" :src="user.avatar" :alt="user.name" />
            <div v-else class="avatar-placeholder">
              {{ getUserInitials() }}
            </div>
          </div>
          <div class="user-details">
            <div class="user-name-full">{{ user?.name || 'Student' }}</div>
            <div class="user-email">{{ user?.email || 'student@example.com' }}</div>
            <div class="user-role">Student</div>
          </div>
        </div>
      </div>

      <div class="dropdown-items">
        <router-link to="/dashboard" class="dropdown-item" @click="hideDropdown">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
            <path d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
          Dashboard
        </router-link>

        <router-link to="/my-courses" class="dropdown-item" @click="hideDropdown">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
            <path d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
          My Courses
        </router-link>

        <router-link to="/settings" class="dropdown-item" @click="hideDropdown">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
            <path d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
          Settings
        </router-link>

        <div class="dropdown-divider"></div>

        <button @click="handleLogout" class="dropdown-item logout">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
            <path d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
          Logout
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const authStore = useAuthStore()

const showDropdown = ref(false)

// Close dropdown when clicking outside
onMounted(() => {
  document.addEventListener('click', (e) => {
    if (!e.target.closest('.user-profile-dropdown')) {
      showDropdown.value = false
    }
  })
})

onUnmounted(() => {
  document.removeEventListener('click', () => {})
})

const user = computed(() => authStore.user)

const toggleDropdown = () => {
  showDropdown.value = !showDropdown.value
}

const hideDropdown = () => {
  showDropdown.value = false
}

const getUserInitials = () => {
  if (!user.value?.name) return 'S'
  const names = user.value.name.split(' ')
  return names.map(name => name.charAt(0).toUpperCase()).join('').slice(0, 2)
}

const handleLogout = async () => {
  try {
    await authStore.logoutAction()
    router.push('/login')
    hideDropdown()
  } catch (error) {
    console.error('Logout error:', error)
  }
}
</script>

<style scoped>
.user-profile-dropdown {
  position: relative;
  z-index: 1001;
}

.profile-trigger {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.5rem 1rem;
  background: rgba(255, 255, 255, 0.95);
  border: 1px solid rgba(0, 0, 0, 0.1);
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s ease;
  backdrop-filter: blur(10px);
}

.profile-trigger:hover {
  background: rgba(255, 255, 255, 1);
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.profile-trigger.active {
  background: rgba(255, 255, 255, 1);
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.user-avatar {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  overflow: hidden;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, #ff9900, #ff6b00);
  flex-shrink: 0;
}

.user-avatar.large {
  width: 48px;
  height: 48px;
}

.user-avatar img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.avatar-placeholder {
  color: white;
  font-weight: 600;
  font-size: 14px;
}

.user-name {
  color: #333;
  font-weight: 500;
  font-size: 14px;
  white-space: nowrap;
}

.dropdown-arrow {
  color: #666;
  transition: transform 0.2s ease;
  flex-shrink: 0;
}

.dropdown-arrow.rotated {
  transform: rotate(180deg);
}

.dropdown-menu {
  position: absolute;
  top: calc(100% + 0.5rem);
  right: 0;
  background: white;
  border-radius: 12px;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
  min-width: 280px;
  overflow: hidden;
  z-index: 1002;
}

.dropdown-header {
  padding: 1.25rem;
  border-bottom: 1px solid #f0f0f0;
  background: linear-gradient(135deg, #f8f9fa, #ffffff);
}

.user-info {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.user-details {
  flex: 1;
}

.user-name-full {
  font-weight: 600;
  color: #333;
  font-size: 16px;
  margin-bottom: 0.25rem;
}

.user-email {
  color: #666;
  font-size: 13px;
  margin-bottom: 0.25rem;
}

.user-role {
  display: inline-block;
  background: linear-gradient(135deg, #ff9900, #ff6b00);
  color: white;
  padding: 0.25rem 0.75rem;
  border-radius: 12px;
  font-size: 11px;
  font-weight: 600;
  text-transform: uppercase;
}

.dropdown-items {
  padding: 0.5rem;
}

.dropdown-item {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.75rem 1rem;
  color: #333;
  text-decoration: none;
  border-radius: 8px;
  transition: all 0.2s ease;
  font-size: 14px;
  font-weight: 500;
  border: none;
  background: none;
  width: 100%;
  text-align: left;
  cursor: pointer;
}

.dropdown-item:hover {
  background: #f8f9fa;
  color: #ff9900;
}

.dropdown-item.logout {
  color: #dc3545;
  pointer-events: auto;
  position: relative;
  z-index: 1002;
}

.dropdown-item.logout:hover {
  background: #fff5f5;
  color: #dc3545;
}

.dropdown-divider {
  height: 1px;
  background: #f0f0f0;
  margin: 0.5rem 0;
}

/* Responsive */
@media (max-width: 768px) {
  .profile-trigger {
    padding: 0.4rem 0.8rem;
  }
  
  .user-name {
    display: none;
  }
  
  .dropdown-menu {
    min-width: 250px;
    right: -1rem;
  }
}
</style>
