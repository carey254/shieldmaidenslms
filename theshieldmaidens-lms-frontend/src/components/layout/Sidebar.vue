<template>
  <aside class="sidebar">
    <h2 class="logo">LMS</h2>
    
    <ul>
      <li>
        <router-link to="/student/dashboard">Dashboard</router-link>
      </li>
      
      <li>
        Explore Courses
      </li>
      
      <li>
        Class Sessions
      </li>
      
      <li>
        Submissions
      </li>
      
      <li>
        Groups
      </li>
      
      <li>
        Community
      </li>
    </ul>
  </aside>
</template>

<script setup lang="ts">
import { onMounted } from 'vue'

onMounted(() => {
  console.log('Sidebar component mounted')
})
</script>

<style scoped>
.sidebar {
  width: 230px;
  background: #0f0f0f;
  color: white;
  padding: 20px;
  height: 100vh;
  position: fixed;
  left: 0;
  top: 0;
  box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
}

.logo {
  color: #ff6b00;
  margin-bottom: 20px;
  font-size: 2rem;
  font-weight: 700;
  text-align: center;
}

.sidebar ul {
  list-style: none;
  padding: 0;
  margin: 0;
}

.sidebar li {
  margin: 15px 0;
}

.sidebar a {
  color: white;
  text-decoration: none;
  font-size: 1rem;
  padding: 10px 15px;
  display: block;
  border-radius: 5px;
  transition: all 0.3s ease;
}

.sidebar a:hover {
  color: #ff6b00;
  background: rgba(255, 107, 0, 0.1);
}

.sidebar a.router-link-active {
  color: #ff6b00;
  background: rgba(255, 107, 0, 0.1);
  border-left: 3px solid #ff6b00;
}

.sidebar li:not(:has(a)) {
  color: #999;
  padding: 10px 15px;
  cursor: not-allowed;
  opacity: 0.7;
}
</style>
