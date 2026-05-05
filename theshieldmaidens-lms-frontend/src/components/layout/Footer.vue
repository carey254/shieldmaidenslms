<template>
  <footer class="footer">
    <div class="footer-content">
      <div class="footer-top">
        <div class="footer-logo" @click="goToDashboard">
          <img src="D:\LMS\theshieldmaidens-lms-frontend\public\logo.png" alt="GVRC Gender Violence Recovery Centre" class="logo" />
          <p>Dedicated to creating safer digital spaces for everyone.</p>
        </div>
        
        <div class="footer-links">
          <div class="links-column">
            <h4>Follow Us</h4>
            <ul>
              <li><a href="#">Facebook</a></li>
              <li><a href="#">Twitter</a></li>
              <li><a href="#">Instagram</a></li>
              <li><a href="#">LinkedIn</a></li>
            </ul>
          </div>
          
          <div class="links-column">
            <h4>Contact Us</h4>
            <ul>
              <li><a href="#">Privacy Policy</a></li>
              <li><a href="#">Terms of Service</a></li>
              <li><a href="#">Code of Conduct</a></li>
            </ul>
          </div>
          
          <div class="links-column">
            <h4>Emergency Contacts</h4>
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
        <div class="legal-links">
          <a href="#">Privacy Policy</a>
          <span>•</span>
          <a href="#">Terms of Service</a>
          <span>•</span>
          <a href="#">Data Usage</a>
          <span>•</span>
          <a href="#">Code of Conduct</a>
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

const router = useRouter();
const currentYear = computed(() => new Date().getFullYear());

// Navigation function
const goToDashboard = () => {
  router.push('/dashboard');
}
</script>

<style scoped>
.footer {
  background-color: #000000; /* Black instead of navy blue */
  color: #f7fafc; /* Cream white */
  padding: 3rem 1rem 1.5rem;
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
  gap: 2rem;
  margin-bottom: 2rem;
  padding-bottom: 2rem;
  border-bottom: 1px solid rgba(247, 250, 252, 0.1);
}

.footer-logo {
  flex: 1;
  min-width: 250px;
  text-align: left;
  cursor: pointer;
  transition: opacity 0.2s ease;
}

.footer-logo:hover {
  opacity: 0.8;
}

.logo {
  max-width: 180px;
  height: auto;
  margin-bottom: 1rem;
}

.footer-logo p {
  color: rgba(247, 250, 252, 0.8);
  line-height: 1.6;
  max-width: 300px;
}

.footer-links {
  display: flex;
  flex-wrap: wrap;
  gap: 3rem;
  flex: 2;
  justify-content: space-between;
}

.links-column {
  min-width: 150px;
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
  background-color: #ed8936; /* Orange accent */
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
}

.links-column a:hover {
  color: #ed8936; /* Orange accent */
}

.contact-info p {
  margin: 0.5rem 0;
  color: rgba(247, 250, 252, 0.8);
  line-height: 1.6;
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
  padding-top: 1.5rem;
  font-size: 0.9rem;
  color: rgba(247, 250, 252, 0.7);
}

.copyright {
  margin: 0.5rem 0;
}

.legal-links {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 0.75rem;
  margin: 0.5rem 0;
}

.legal-links a {
  color: rgba(247, 250, 252, 0.8);
  text-decoration: none;
  transition: color 0.3s ease;
}

.legal-links a:hover {
  color: #ed8936; /* Orange accent */
}

.emergency {
  background-color: rgba(237, 137, 54, 0.1);
  padding: 0.5rem 1rem;
  border-radius: 4px;
  margin: 0.5rem 0;
  font-weight: 600;
  color: #ed8936; /* Orange accent */
}

.emergency strong {
  color: #f7fafc;
}

@media (max-width: 768px) {
  .footer-top {
    flex-direction: column;
  }
  
  .footer-logo {
    text-align: center;
    margin: 0 auto;
  }
  
  .footer-logo p {
    max-width: 100%;
  }
  
  .footer-links {
    flex-direction: column;
    gap: 2rem;
    margin-top: 2rem;
  }
  
  .footer-bottom {
    flex-direction: column;
    text-align: center;
    gap: 1rem;
  }
  
  .legal-links {
    justify-content: center;
  }
}
</style>
