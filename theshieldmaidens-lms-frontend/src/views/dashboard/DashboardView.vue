<template>
  <div class="dashboard">
    <h1>Welcome to Your Dashboard</h1>
    <p>This is your dashboard. More features coming soon!</p>
    <button @click="handleLogout" class="logout-button">Logout</button>
  </div>
</template>

<script setup lang="ts">
import { useRouter } from 'vue-router';

const router = useRouter();

const handleLogout = () => {
  // TODO: Implement actual logout logic
  console.log('Logging out...');
  router.push('/login');
};
</script>

<style scoped>
.dashboard {
  max-width: 1200px;
  margin: 2rem auto;
  padding: 0 1rem;
}

h1 {
  color: #1a365d;
  margin-bottom: 1rem;
}

p {
  color: #4a5568;
  margin-bottom: 2rem;
}

.logout-button {
  background-color: #e53e3e;
  color: white;
  padding: 0.5rem 1rem;
  border: none;
  border-radius: 0.375rem;
  cursor: pointer;
  font-weight: 500;
  transition: background-color 0.2s;
}

.logout-button:hover {
  background-color: #c53030;
}
</style>
