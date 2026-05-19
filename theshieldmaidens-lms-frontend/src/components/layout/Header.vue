<template>
  <div class="header-container">
    <!-- Main Navigation -->
    <header class="header">
      <div class="logo">
        <img :src="PUBLIC_BRAND_LOGO" alt="The Shield Maidens" class="logo-img">
        <span class="logo-text">Learning Management System</span>
      </div>
      <!-- Navigation for All Users -->
      <nav class="nav-links">
          <router-link to="/" class="nav-link">HOME</router-link>
          <router-link to="/courses" class="nav-link">OUR CURRICULUM</router-link>
          
          <router-link to="/support" class="nav-link">SUPPORT</router-link>
          <a href="https://theshieldmaidens.org/" target="_blank" class="nav-link external-link">Visit Our Website</a>
          <router-link to="/login" class="nav-link login-btn">Login</router-link>
          
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
      </nav>
    </header>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue';
import { PUBLIC_BRAND_LOGO } from '@/config/branding';

// Language state
const showLanguageDropdown = ref(false);
const currentLanguage = ref({
  code: 'en',
  name: 'English',
  flag: '🇺🇸'
});

const languages = ref([
  { code: 'en', name: 'English', flag: '🇺🇸' },
  { code: 'sw', name: 'Kiswahili', flag: '🇰🇪' },
  { code: 'fr', name: 'Français', flag: '🇫🇷' },
  { code: 'ar', name: 'العربية', flag: '🇸🇦' }
]);

// Language functions
const toggleLanguageDropdown = () => {
  showLanguageDropdown.value = !showLanguageDropdown.value;
};

const hideLanguageDropdown = () => {
  showLanguageDropdown.value = false;
};

const selectLanguage = (lang: any) => {
  currentLanguage.value = lang;
  hideLanguageDropdown();
  // TODO: Implement actual language change logic
  console.log('Language changed to:', lang.name);
};

// Close dropdown when clicking outside
const handleClickOutside = (event: MouseEvent) => {
  const target = event.target as HTMLElement;
  const dropdown = target.closest('.language-dropdown');
  if (!dropdown) {
    hideLanguageDropdown();
  }
};

onMounted(() => {
  document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside);
});
</script>

<style scoped>
/* Header Container */
.header-container {
  width: 100%;
  position: fixed;
  top: 0;
  left: 0;
  z-index: 1000;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  pointer-events: auto !important;
}

/* Body styles with background */
body {
  margin: 0;
  padding: 0;
  min-height: 100vh;
  position: relative;
}

/* Background overlay */
body::before {
  content: '';
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), 
              url('/background.jpg') no-repeat center center;
  background-size: cover;
  z-index: -1;
}

/* Main app container */
#app {
  position: relative;
  z-index: 1;
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  background-color: rgba(255, 255, 255, 0.95);
  box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
  padding-top: 80px !important; /* Reduced since contact bar is removed */
  margin-top: 0 !important;
  background-color: rgba(255, 255, 255, 0.95);
  box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
}

/* Force content below header */
main {
  flex: 1;
  position: relative;
  z-index: 1;
  margin-top: 0 !important;
  padding-top: 0 !important;
}

/* Force header positioning */
.header-container {
  width: 100%;
  position: fixed !important;
  top: 0 !important;
  left: 0 !important;
  z-index: 1000 !important;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

/* Main Navigation */
.header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem 2rem;
  background-color: #faf8f3; /* Cream white instead of creamy white */
  color: #1a365d; /* Navy blue */
  min-height: 80px;
  position: relative;
  pointer-events: auto !important;
}

.logo {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  text-decoration: none;
  flex-shrink: 0;
  z-index: 10;
  position: relative;
}

.logo-placeholder {
  font-size: 2rem;
  width: 50px;
  height: 50px;
  display: flex;
  align-items: center;
  justify-content: center;
  background-color: #000000; /* Black instead of orange */
  border-radius: 50%;
}

.logo-img {
  height: 50px;
  width: auto;
  object-fit: contain;
}

.logo-text {
  font-size: 1.5rem;
  font-weight: 600;
  color: #ff9900; /* Orange instead of navy blue */
}

.nav-links {
  display: flex;
  gap: 0.25rem;
  align-items: center;
  flex-wrap: wrap;
  justify-content: flex-end;
  position: relative;
  z-index: 5;
  max-width: 65%;
  pointer-events: auto !important;
}

.nav-link {
  color: #333333; /* Dark gray instead of orange */
  text-decoration: none;
  font-weight: 500;
  padding: 0.25rem 0.5rem;
  border-radius: 4px;
  transition: all 0.2s;
  border: 1px solid transparent;
  font-size: 0.75rem;
  white-space: nowrap;
  pointer-events: auto !important;
  cursor: pointer !important;
}

.nav-link:hover {
  color: #333333; /* Dark gray on hover */
  background-color: rgba(51, 51, 51, 0.1); /* Light gray background */
}

.external-link {
  background: linear-gradient(135deg, #ff9900, #ff6b00);
  color: white !important;
  border-radius: 6px;
  padding: 0.5rem 1rem;
  font-weight: 600;
  transition: all 0.2s ease;
}

.external-link:hover {
  background: linear-gradient(135deg, #ff6b00, #e68600);
  transform: translateY(-1px);
  box-shadow: 0 2px 8px rgba(255, 153, 0, 0.3);
}

.login-btn {
  background-color: #ff9900; /* Orange for login button */
  color: #ffffff !important; /* White text */
  border: 1px solid #ff9900 !important;
}

.login-btn:hover {
  background-color: #e68600; /* Darker orange on hover */
  border-color: #e68600;
}

.register {
  background-color: #000000; /* Black instead of orange */
  color: #ffffff !important; /* White text */
  border: 1px solid #000000 !important;
}

.register:hover {
  background-color: #333333; /* Darker black on hover */
  border-color: #333333;
}

/* Language Dropdown Styles */
.language-dropdown {
  position: relative;
  z-index: 10001;
  pointer-events: auto !important;
}

.language-trigger {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 0.5rem 1rem;
  background: white;
  border: 1px solid #dee2e6;
  border-radius: 6px;
  cursor: pointer;
  transition: all 0.2s ease;
  margin-left: 0.5rem;
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
  font-size: 0.8rem;
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
  min-width: 180px;
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
  font-size: 0.9rem;
  font-weight: 500;
  color: #333;
}

.language-option.active .lang-name {
  color: #ff9900;
  font-weight: 600;
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .header {
    flex-direction: row;
    justify-content: center;
    padding: 0.75rem 1rem;
    min-height: 68px;
  }
  
  .logo {
    margin-bottom: 0;
  }
  
  .nav-links {
    display: none;
  }
  
  .language-dropdown {
    display: none;
  }
}
</style>
