<template>
  <div class="page">
    <h1>Learners</h1>
    <div v-if="loading">Loading learners...</div>
    <table v-else>
      <thead><tr><th>Name</th><th>Email</th><th>Progress</th></tr></thead>
      <tbody>
        <tr v-for="l in learners" :key="l.id">
          <td>{{ l.name }}</td>
          <td>{{ l.email }}</td>
          <td>{{ l.avg_progress ?? 0 }}%</td>
        </tr>
        <tr v-if="!learners.length"><td colspan="3">No learners found.</td></tr>
      </tbody>
    </table>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { API_BASE_URL } from '@/config/api'
import { useAuthStore } from '@/stores/auth'
const auth = useAuthStore()
const learners = ref([])
const loading = ref(true)

onMounted(async () => {
  try {
    const res = await fetch(`${API_BASE_URL}/instructor/learners`, {
      headers: { Authorization: `Bearer ${auth.token || localStorage.getItem('token')}` }
    })
    if (res.ok) {
      const data = await res.json()
      learners.value = Array.isArray(data) ? data : (data.data || data)
    }
  } catch (e) {
    console.error(e)
  } finally {
    loading.value = false
  }
})
</script>

<style scoped>
.page { padding: 20px }
table { width: 100%; border-collapse: collapse }
th, td { border-bottom: 1px solid #eee; padding: 8px }
</style>