<template>
  <div class="student-navbar">
    <!-- Logo -->
    <div class="navbar-logo" @click="goToDashboard">
      <img :src="PUBLIC_BRAND_LOGO" alt="The Shield Maidens" class="logo-image" />
      <span class="logo-text">Learning Management System</span>
    </div>
    
    <!-- Right Side Actions -->
    <div class="navbar-actions">
      <!-- Language Dropdown -->
      <div class="language-dropdown" :class="{ 'active': showLanguageDropdown }">
        <div class="language-trigger" @click="toggleLanguageDropdown">
          <span class="language-icon">🌐</span>
          <span class="language-text">{{ currentLanguage.name }}</span>
          <svg class="dropdown-arrow" :class="{ 'rotated': showLanguageDropdown }" width="12" height="8" viewBox="0 0 12 8" fill="none">
            <path d="M1 1.5L6 6.5L11 1.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </div>
        
        <div v-if="showLanguageDropdown" class="language-menu">
          <div 
            v-for="lang in languages" 
            :key="lang.code" 
            class="language-option"
            :class="{ 'active': lang.code === currentLanguage.code }"
            @click.stop="selectLanguage(lang)"
          >
            <span class="flag">{{ lang.flag }}</span>
            <span class="lang-name">{{ lang.name }}</span>
          </div>
        </div>
      </div>
      
      <!-- User Profile Dropdown -->
      <UserProfileDropdown />
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import UserProfileDropdown from './UserProfileDropdown.vue'
import { PUBLIC_BRAND_LOGO } from '@/config/branding'

const router = useRouter()

// Language state
const showLanguageDropdown = ref(false)
const currentLanguage = ref({
  code: 'en',
  name: 'English',
  flag: '🇺🇸'
})

const languages = ref([
  { code: 'en', name: 'English', flag: '🇺🇸' },
  { code: 'sw', name: 'Kiswahili', flag: '🇰🇪' },
  { code: 'fr', name: 'Français', flag: '🇫🇷' },
  { code: 'ar', name: 'العربية', flag: '🇸🇦' }
])

// Language functions
const toggleLanguageDropdown = () => {
  showLanguageDropdown.value = !showLanguageDropdown.value
}

const hideLanguageDropdown = () => {
  showLanguageDropdown.value = false
}

const selectLanguage = (lang: any) => {
  currentLanguage.value = lang
  hideLanguageDropdown()
  // TODO: Implement actual language change logic
  console.log('Language changed to:', lang.name)
}

// Close dropdown when clicking outside
const handleClickOutside = (event: MouseEvent) => {
  const target = event.target as HTMLElement
  const dropdown = target.closest('.language-dropdown')
  if (!dropdown) {
    hideLanguageDropdown()
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
})

// Navigation function
const goToDashboard = () => {
  router.push('/student/dashboard')
}
</script>

<style scoped>
.student-navbar {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  min-height: 64px;
  height: auto;
  background: #f8f9fa; /* Cream white background */
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 8px 20px;
  z-index: 1000;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
  border-bottom: 1px solid #e9ecef;
}

.navbar-logo {
  display: flex;
  align-items: center;
  gap: 15px;
  cursor: pointer;
  transition: opacity 0.2s ease;
}

.navbar-logo:hover {
  opacity: 0.8;
}

.logo-image {
  width: 48px;
  height: 48px;
  object-fit: contain;
}

.logo-text {
  font-size: 16px;
  font-weight: bold;
  color: #ff9900; /* Orange color */
  max-width: min(52vw, 320px);
  line-height: 1.25;
}

.navbar-actions {
  display: flex;
  align-items: center;
  gap: 20px;
}

/* Language Dropdown Styles */
.language-dropdown {
  position: relative;
  z-index: 10001;
}

.language-trigger {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 8px 12px;
  background: white;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s ease;
  pointer-events: auto !important;
  user-select: none;
}

.language-trigger:hover {
  background: #f8f9fa;
  border-color: #ced4da;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.language-dropdown.active .language-trigger {
  background: #f8f9fa;
  border-color: #ff9900;
  box-shadow: 0 2px 8px rgba(255, 153, 0, 0.2);
}

.language-icon {
  font-size: 16px;
}

.language-text {
  font-size: 14px;
  font-weight: 500;
  color: #333;
}

.dropdown-arrow {
  color: #666;
  transition: transform 0.2s ease;
  flex-shrink: 0;
}

.dropdown-arrow.rotated {
  transform: rotate(180deg);
}

.language-menu {
  position: absolute;
  top: calc(100% + 8px);
  right: 0;
  background: white;
  border-radius: 8px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  min-width: 200px;
  max-height: 300px;
  overflow-y: auto;
  z-index: 10000;
  pointer-events: auto;
}

.language-option {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px 16px;
  cursor: pointer;
  transition: background-color 0.2s ease;
  border-bottom: 1px solid #f1f3f4;
}

.language-option:last-child {
  border-bottom: none;
}

.language-option:hover {
  background: #f8f9fa;
}

.language-option.active {
  background: #fff5f5;
  color: #ff9900;
}

.flag {
  font-size: 18px;
}

.lang-name {
  font-size: 14px;
  font-weight: 500;
  color: #333;
}

.language-option.active .lang-name {
  color: #ff9900;
  font-weight: 600;
}

/* Responsive */
@media (max-width: 768px) {
  .student-navbar {
    padding: 8px 12px;
  }
  
  .logo-image {
    width: 40px;
    height: 40px;
  }
  
  .logo-text {
    font-size: 13px;
    max-width: 46vw;
  }
  
  .navbar-actions {
    gap: 15px;
  }
  
  .language-text {
    display: none; /* Hide language text on mobile */
  }
  
  .language-menu {
    min-width: 150px;
  }
}
</style>
