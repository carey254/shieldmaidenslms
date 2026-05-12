<template>
  <div class="header-container">
    <!-- Top Contact Bar -->
    <div class="contact-bar">
      <div class="contact-info">
        <div class="language-selector">
          <div class="language-selected" @click="toggleLanguageDropdown">
            English
            <svg class="dropdown-arrow" :class="{ 'rotated': showLanguageDropdown }" width="12" height="8" viewBox="0 0 12 8" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M1 1.5L6 6.5L11 1.5" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
          </div>
          <div v-if="showLanguageDropdown" class="language-dropdown" @mouseleave="hideLanguageDropdown">
            <div 
              v-for="lang in languages" 
              :key="lang.value" 
              class="language-option"
              :class="{ 'active': selectedLanguage === lang.value }"
              @click="selectLanguage(lang.value)"
            >
              {{ lang.label }}
            </div>
          </div>
        </div>
        <div class="contact-item">
          <span class="contact-label"></span>
          <span class="contact-value">Emergency Services: 1195</span>
        </div>
        <div class="contact-item">
          <span class="contact-label">Inquiries</span>
          <span class="contact-value">+254 702 997534</span>
        </div>
        <div class="contact-item">
          <span class="contact-label">Email Support</span>
          <a href="mailto:support@theshieldmaidens.org" class="contact-email">support@theshieldmaidens.org</a>
        </div>
      </div>
    </div>

    <!-- Main Navigation -->
    <header class="header">
      <div class="logo">
        <img src="D:\LMS\theshieldmaidens-lms-frontend\public\logo.png" alt="GVRC Gender Violence Recovery Centre" class="logo-img">
        <span class="logo-text">Learning Management System</span>
      </div>
      <!-- Navigation for All Users -->
      <nav class="nav-links">
          <router-link to="/" class="nav-link">HOME</router-link>
          <router-link to="/courses" class="nav-link">OUR CURRICULUM</router-link>
          
          <router-link to="/support" class="nav-link">SUPPORT</router-link>
          <a href="https://theshieldmaidens.org/" target="_blank" class="nav-link external-link">Visit Our Website</a>
          <router-link to="/login" class="nav-link login-btn">Login</router-link>
      </nav>
    </header>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { useRouter } from 'vue-router';

const router = useRouter();

const selectedLanguage = ref('en');
const showLanguageDropdown = ref(false);

const languages = [
  { value: 'en', label: 'English' },
  { value: 'sw', label: 'Swahili' },
  { value: 'ar', label: 'العربية (Arabic)' }
];

const toggleLanguageDropdown = () => {
  showLanguageDropdown.value = !showLanguageDropdown.value;
};

const hideLanguageDropdown = () => {
  showLanguageDropdown.value = false;
};

const selectLanguage = (lang) => {
  selectedLanguage.value = lang;
  showLanguageDropdown.value = false;
  // TODO: Implement language change logic
  console.log('Language changed to:', lang);
};
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
  padding-top: 130px !important; /* Restored to 130px since contact bar is back */
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

/* Top Contact Bar */
.contact-bar {
  background-color: #000000; /* Black instead of orange */
  color: #ffffff;
  padding: 0.5rem 2rem;
  font-size: 0.9rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.contact-info {
  display: flex;
  justify-content: space-between;
  align-items: center;
  max-width: 1200px;
  margin: 0 auto;
  padding: 0;
}

.language-selector {
  position: relative;
  min-width: 120px;
  z-index: 1001;
}

.language-selected {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0.25rem 0.75rem;
  background: rgba(255, 255, 255, 0.1);
  border: 1px solid rgba(255, 255, 255, 0.2);
  color: white;
  border-radius: 4px;
  cursor: pointer;
  font-size: 0.9rem;
  transition: all 0.2s;
  min-height: 32px;
}

.language-selected:hover {
  background: rgba(255, 255, 255, 0.15);
}

.dropdown-arrow {
  margin-left: 8px;
  transition: transform 0.2s;
}

.dropdown-arrow.rotated {
  transform: rotate(180deg);
}

.language-dropdown {
  position: absolute;
  top: 100%;
  left: 0;
  width: 100%;
  background: white;
  border-radius: 4px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  overflow: hidden;
  margin-top: 4px;
  z-index: 1000;
}

.language-option {
  padding: 0.5rem 0.75rem;
  color: #1a365d;
  cursor: pointer;
  transition: all 0.2s;
  font-size: 0.9rem;
}

.language-option:hover {
  background-color: #f0f4f8;
}

.language-option.active {
  background-color: #e2e8f0;
  font-weight: 500;
}

.contact-item {
  display: flex;
  align-items: center;
  margin-left: 2rem;
  color: #ffffff;
  font-size: 0.9rem;
}

.contact-label {
  font-weight: 500;
  color: rgba(255, 255, 255, 0.9);
}

.contact-value, .contact-email {
  color: #ffffff;
  text-decoration: none;
  font-weight: 400;
  transition: color 0.2s;
}

.contact-email:hover {
  color: #ed8936; /* Orange on hover */
  text-decoration: underline;
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

/* Responsive adjustments */
@media (max-width: 768px) {
  .contact-bar {
    display: none;
  }

  .contact-info {
    flex-direction: column;
    align-items: flex-start;
    gap: 0.5rem;
  }
  
  .header {
    flex-direction: row;
    justify-content: center;
    padding: 0.75rem 1rem;
    min-height: 68px;
    text-align: left;
  }
  
  .logo {
    margin-bottom: 0;
  }
  
  .nav-links {
    display: none;
  }
}
</style>
