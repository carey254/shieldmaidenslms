<template>
  <footer class="footer">
    <div class="footer-content">
      <div class="footer-top">
        <div class="footer-logo" @click="goToDashboard">
          <img :src="PUBLIC_BRAND_LOGO" alt="The Shield Maidens" class="logo" />
          <p>Dedicated to creating safer digital spaces for everyone.</p>
          <div class="social-links">
            <a href="#" class="social-link">Facebook</a>
            <a href="#" class="social-link">Twitter</a>
            <a href="#" class="social-link">Instagram</a>
            <a href="#" class="social-link">LinkedIn</a>
          </div>
        </div>
        
        <div class="footer-links">
          <div class="links-column">
            <h4>Quick Links</h4>
            <ul>
              <li><router-link to="/">Home</router-link></li>
              <li><router-link to="/courses">Courses</router-link></li>
              <li><router-link to="/support">Support</router-link></li>
              <li><router-link to="/login">Login</router-link></li>
            </ul>
          </div>
          
          <div class="links-column">
            <h4>Legal</h4>
            <ul>
              <li><a href="#">Privacy Policy</a></li>
              <li><a href="#">Terms of Service</a></li>
              <li><a href="#">Data Usage</a></li>
              <li><a href="#">Code of Conduct</a></li>
            </ul>
          </div>
          
          <div class="links-column">
            <h4>Contact Us</h4>
            <div class="contact-info">
              <p><strong>Emergency Services:</strong> 1195</p>
              <p><strong>Inquiries:</strong> +254 702 997534</p>
              <p><strong>Email Support:</strong> support@theshieldmaidens.org</p>
            </div>
          </div>
        </div>
      </div>
      
      <div class="footer-bottom">
        <div class="copyright">
          © {{ currentYear }} The Shield Maidens. All rights reserved.
        </div>
        <div class="emergency">
          <strong>Emergency:</strong> 1195
        </div>
      </div>
    </div>
  </footer>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { useRouter } from 'vue-router';
import { PUBLIC_BRAND_LOGO } from '@/config/branding';

const router = useRouter();
const currentYear = computed(() => new Date().getFullYear());

// Navigation function
const goToDashboard = () => {
  router.push('/dashboard');
}
</script>

<style scoped>
.footer {
  background-color: #000000;
  color: #f7fafc;
  padding: 4rem 1rem 2rem;
  margin-top: auto;
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

.footer-content {
  max-width: 1200px;
  margin: 0 auto;
}

.footer-top {
  display: flex;
  flex-wrap: wrap;
  justify-content: space-between;
  gap: 3rem;
  margin-bottom: 3rem;
  padding-bottom: 3rem;
  border-bottom: 1px solid rgba(247, 250, 252, 0.1);
}

.footer-logo {
  flex: 1;
  min-width: 280px;
  text-align: left;
  cursor: pointer;
  transition: opacity 0.2s ease;
}

.footer-logo:hover {
  opacity: 0.8;
}

.logo {
  max-width: 200px;
  height: auto;
  margin-bottom: 1.5rem;
}

.footer-logo p {
  color: rgba(247, 250, 252, 0.8);
  line-height: 1.6;
  max-width: 320px;
  margin-bottom: 1.5rem;
}

.social-links {
  display: flex;
  gap: 1rem;
  flex-wrap: wrap;
}

.social-link {
  color: rgba(247, 250, 252, 0.8);
  text-decoration: none;
  transition: color 0.3s ease;
  font-size: 0.9rem;
}

.social-link:hover {
  color: #ed8936;
}

.footer-links {
  display: flex;
  gap: 4rem;
  flex: 2;
  justify-content: space-between;
}

.links-column {
  min-width: 180px;
  flex: 1;
}

.links-column h4 {
  color: #f7fafc;
  margin-bottom: 1.5rem;
  font-size: 1.1rem;
  position: relative;
  padding-bottom: 0.5rem;
}

.links-column h4::after {
  content: '';
  position: absolute;
  left: 0;
  bottom: 0;
  width: 40px;
  height: 2px;
  background-color: #ed8936;
}

.links-column ul {
  list-style: none;
  padding: 0;
  margin: 0;
}

.links-column li {
  margin-bottom: 0.75rem;
}

.links-column a {
  color: rgba(247, 250, 252, 0.8);
  text-decoration: none;
  transition: color 0.3s ease;
  font-size: 0.95rem;
}

.links-column a:hover {
  color: #ed8936;
}

.contact-info p {
  margin: 0.75rem 0;
  color: rgba(247, 250, 252, 0.8);
  line-height: 1.6;
  font-size: 0.95rem;
}

.contact-info strong {
  color: #f7fafc;
  font-weight: 600;
}

.footer-bottom {
  display: flex;
  flex-wrap: wrap;
  justify-content: space-between;
  align-items: center;
  padding-top: 2rem;
  font-size: 0.9rem;
  color: rgba(247, 250, 252, 0.7);
}

.copyright {
  margin: 0.5rem 0;
}

.emergency {
  background-color: rgba(237, 137, 54, 0.15);
  padding: 0.75rem 1.5rem;
  border-radius: 6px;
  margin: 0.5rem 0;
  font-weight: 600;
  color: #ed8936;
  border: 1px solid rgba(237, 137, 54, 0.3);
}

.emergency strong {
  color: #f7fafc;
}

@media (max-width: 768px) {
  .footer-top {
    flex-direction: column;
    gap: 2rem;
  }
  
  .footer-logo {
    text-align: center;
    margin: 0 auto;
  }
  
  .footer-logo p {
    max-width: 100%;
  }
  
  .social-links {
    justify-content: center;
  }
  
  .footer-links {
    flex-direction: column;
    gap: 2rem;
    margin-top: 2rem;
  }
  
  .links-column {
    text-align: center;
  }
  
  .links-column h4::after {
    left: 50%;
    transform: translateX(-50%);
  }
  
  .footer-bottom {
    flex-direction: column;
    text-align: center;
    gap: 1rem;
  }
}
</style>
