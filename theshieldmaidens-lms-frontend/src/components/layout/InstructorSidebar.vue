<template>
  <aside class="sidebar">
    <div class="brand">
      <div class="brand-mark">
        <img :src="PUBLIC_BRAND_LOGO" alt="" class="brand-logo" />
      </div>
      <div class="brand-text">
        <div class="brand-title">Shield Maidens</div>
        <div class="brand-subtitle">Instructor</div>
      </div>
    </div>

    <nav class="nav" aria-label="Instructor navigation">
      <RouterLink
        class="nav-item"
        to="/instructor/dashboard"
        :class="{ active: isDashboardHome }"
      >
        <span class="label">Dashboard</span>
      </RouterLink>

      <RouterLink class="nav-item" to="/instructor/courses" :class="{ active: isActivePath('/instructor/courses') }">
        <span class="label">Courses</span>
      </RouterLink>

      <RouterLink class="nav-item" to="/instructor/upload" :class="{ active: isActivePath('/instructor/upload') }">
        <span class="label">Content upload</span>
      </RouterLink>

      <RouterLink class="nav-item" to="/instructor/sessions" :class="{ active: isActivePath('/instructor/sessions') }">
        <span class="label">Sessions</span>
      </RouterLink>

      <RouterLink
        class="nav-item"
        to="/instructor/assessments"
        :class="{ active: isActivePath('/instructor/assessments') }"
      >
        <span class="label">Assessments</span>
      </RouterLink>

      <RouterLink
        class="nav-item"
        to="/instructor/learners"
        :class="{ active: isActivePath('/instructor/learners') }"
      >
        <span class="label">Students</span>
      </RouterLink>

      <RouterLink
        class="nav-item"
        to="/instructor/analytics"
        :class="{ active: isActivePath('/instructor/analytics') }"
      >
        <span class="label">Analytics</span>
      </RouterLink>

      <RouterLink
        class="nav-item"
        to="/instructor/messages"
        :class="{ active: isActivePath('/instructor/messages') }"
      >
        <span class="label">Communication</span>
      </RouterLink>

      <RouterLink
        class="nav-item"
        to="/instructor/enrollments"
        :class="{ active: isActivePath('/instructor/enrollments') }"
      >
        <span class="label">Enrollment notes</span>
      </RouterLink>

      <RouterLink class="nav-item" to="/instructor/settings" :class="{ active: isActivePath('/instructor/settings') }">
        <span class="label">Settings</span>
      </RouterLink>
    </nav>

    <div class="spacer" />

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

    <div class="profile">
      <div class="avatar">{{ (user?.name || 'I').charAt(0).toUpperCase() }}</div>
      <div class="who">
        <div class="name">{{ user?.name || 'Instructor' }}</div>
        <div class="email">{{ user?.email || '' }}</div>
      </div>
      <button class="logout" type="button" @click="logout">Logout</button>
    </div>
  </aside>
</template>

<script setup lang="ts">
import { computed, ref, onMounted, onUnmounted } from 'vue'
import { useRoute, useRouter, RouterLink } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { PUBLIC_BRAND_LOGO } from '@/config/branding'

const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()

const user = computed(() => authStore.user as { name?: string; email?: string } | null)

const isActivePath = (path: string) => route.path === path

const isDashboardHome = computed(() => route.path === '/instructor/dashboard')

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

const selectLanguage = (lang: any) => {
  currentLanguage.value = lang
  showLanguageDropdown.value = false
  // TODO: Implement actual language change logic
  console.log('Language changed to:', lang.name)
}

// Close dropdown when clicking outside
const handleClickOutside = (event: MouseEvent) => {
  const target = event.target as HTMLElement
  const dropdown = target.closest('.language-dropdown')
  if (!dropdown) {
    showLanguageDropdown.value = false
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
})

const logout = async () => {
  await authStore.logout()
  router.push('/instructor/login')
}
</script>

<style scoped>
.sidebar {
  position: fixed;
  top: 0;
  left: 0;
  bottom: 0;
  width: 96px;
  padding: 14px 10px;
  background: #f8fafc;
  border-right: 1px solid #e2e8f0;
  display: flex;
  flex-direction: column;
  z-index: 1002;
  overflow-y: auto;
  overflow-x: hidden;
}

.brand {
  display: grid;
  grid-template-columns: 40px 1fr;
  gap: 10px;
  align-items: center;
  margin-bottom: 12px;
}

.brand-mark {
  width: 40px;
  height: 40px;
  border-radius: 12px;
  background: #e2e8f0;
  display: grid;
  place-items: center;
  overflow: hidden;
}

.brand-logo {
  width: 34px;
  height: 34px;
  object-fit: contain;
}

.brand-text {
  display: none;
}

.nav {
  display: flex;
  flex-direction: column;
  gap: 6px;
  margin-top: 4px;
}

.nav-group-label {
  display: none;
  margin: 10px 4px 4px;
  font-size: 10px;
  font-weight: 800;
  letter-spacing: 0.12em;
  text-transform: uppercase;
  color: #94a3b8;
}

.nav-item {
  min-height: 44px;
  border-radius: 14px;
  display: grid;
  place-items: center;
  color: #475569;
  text-decoration: none;
  background: #ffffff;
  border: 1px solid #e2e8f0;
  transition: background 180ms ease, transform 180ms ease, border-color 180ms ease;
}

.nav-item:hover {
  transform: translateY(-1px);
  background: #f1f5f9;
  border-color: #cbd5e1;
}

.nav-item.active {
  background: linear-gradient(135deg, #eef2ff 0%, #e0e7ff 100%);
  border-color: #a5b4fc;
  color: #312e81;
  box-shadow: 0 0 0 1px rgba(99, 102, 241, 0.12);
}

.label {
  display: none;
}

.spacer {
  flex: 1;
  min-height: 8px;
}

.profile {
  display: grid;
  gap: 10px;
  place-items: center;
}

.avatar {
  width: 40px;
  height: 40px;
  border-radius: 999px;
  background: #e2e8f0;
  color: #0f172a;
  display: grid;
  place-items: center;
  font-weight: 700;
}

.who {
  display: none;
}

.logout {
  width: 40px;
  height: 40px;
  border-radius: 14px;
  border: 1px solid #e2e8f0;
  background: #ffffff;
  color: #475569;
  cursor: pointer;
  font-size: 10px;
  font-weight: 600;
}

.logout:hover {
  background: #e2e8f0;
}

/* Language Dropdown Styles */
.language-dropdown {
  position: relative;
  margin-bottom: 12px;
  z-index: 10001;
}

.language-trigger {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 10px;
  background: white;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  cursor: pointer;
  transition: all 0.2s ease;
  pointer-events: auto !important;
  user-select: none;
}

.language-trigger:hover {
  background: #f1f5f9;
  border-color: #cbd5e1;
}

.language-dropdown.active .language-trigger {
  background: #f1f5f9;
  border-color: #ff9900;
  box-shadow: 0 2px 8px rgba(255, 153, 0, 0.2);
}

.language-icon {
  font-size: 16px;
}

.language-text {
  font-size: 11px;
  font-weight: 600;
  color: #475569;
}

.dropdown-arrow {
  color: #64748b;
  transition: transform 0.2s ease;
  flex-shrink: 0;
}

.dropdown-arrow.rotated {
  transform: rotate(180deg);
}

.language-menu {
  position: absolute;
  bottom: calc(100% + 8px);
  left: 0;
  background: white;
  border-radius: 12px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  min-width: 160px;
  z-index: 10000;
  pointer-events: auto;
}

.language-option {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 10px 12px;
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
  font-size: 16px;
}

.lang-name {
  font-size: 12px;
  font-weight: 500;
  color: #334155;
}

.language-option.active .lang-name {
  color: #ff9900;
  font-weight: 600;
}

@media (min-width: 1100px) {
  .sidebar {
    width: 260px;
    padding: 18px 14px 20px;
  }

  .brand-text {
    display: block;
  }

  .brand-title {
    color: #0f172a;
    font-weight: 700;
    font-size: 14px;
  }

  .brand-subtitle {
    color: #64748b;
    font-size: 12px;
    margin-top: 2px;
  }

  .nav-group-label {
    display: block;
  }

  .nav-item {
    grid-template-columns: 1fr;
    justify-content: start;
    padding: 0 10px;
    place-items: center start;
    min-height: 42px;
  }

  .label {
    display: inline;
    font-size: 12px;
    font-weight: 600;
    line-height: 1.25;
  }

  .profile {
    grid-template-columns: 40px 1fr auto;
    place-items: center start;
  }

  .logout {
    width: auto;
    padding: 8px 16px;
    font-size: 12px;
  }

  .who {
    display: block;
    min-width: 0;
  }

  .name {
    color: #0f172a;
    font-weight: 700;
    font-size: 13px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }

  .email {
    color: #64748b;
    font-size: 12px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }
}
</style>
