import { createApp } from 'vue';
import { createPinia } from 'pinia';
import { library } from '@fortawesome/fontawesome-svg-core';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { 
  faUser, 
  faLock, 
  faEnvelope, 
  faEye, 
  faEyeSlash, 
  faCheckCircle,
  faHome,
  faArrowLeft,
  faShieldAlt,
  faLaptopCode,
  faUsers,
  faSignOutAlt,
  faUserCog
} from '@fortawesome/free-solid-svg-icons';
import { 
  faGoogle, 
  faFacebook, 
  faTwitter
} from '@fortawesome/free-brands-svg-icons';
import App from './App.vue';
import router from './router';
import "./assets/main.css";

const apiBaseUrl = import.meta.env.VITE_API_BASE_URL || "http://127.0.0.1:8000/api";
// Clear any old API URL from localStorage
localStorage.removeItem("apiBaseUrl");
localStorage.setItem("apiBaseUrl", apiBaseUrl);

// Add Font Awesome icons to the library
library.add(
  faUser, 
  faLock, 
  faEnvelope, 
  faEye, 
  faEyeSlash, 
  faCheckCircle,
  faHome,
  faArrowLeft,
  faShieldAlt,
  faLaptopCode,
  faUsers,
  faSignOutAlt,
  faUserCog,
  faGoogle, 
  faFacebook, 
  faTwitter
);

// Create the app
const app = createApp(App);
const pinia = createPinia();

// Use plugins
app.use(pinia);
app.use(router);

// Register global components
app.component('font-awesome-icon', FontAwesomeIcon);

// Mount the app
app.mount('#app');
