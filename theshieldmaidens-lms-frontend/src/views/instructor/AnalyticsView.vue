<template>
  <div class="page">
    <h1>Analytics</h1>
    <div v-if="loading">Loading analytics...</div>
    <div v-else>
      <div>Total Courses: <strong>{{ stats.totalCourses ?? '—' }}</strong></div>
      <div>Total Students: <strong>{{ stats.totalStudents ?? '—' }}</strong></div>
      <div>Avg Completion: <strong>{{ stats.avgCompletion ?? '—' }}%</strong></div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { API_BASE_URL } from '@/config/api'
import { useAuthStore } from '@/stores/auth'
const auth = useAuthStore()
const stats = ref({})
const loading = ref(true)

onMounted(async () => {
  try {
    const res = await fetch(`${API_BASE_URL}/instructor/dashboard/stats`, {
      headers: { Authorization: `Bearer ${auth.token || localStorage.getItem('token')}` }
    })
    if (res.ok) {
      const data = await res.json()
      stats.value = data
    }
  } catch (e) {
    console.error(e)
  } finally { loading.value = false }
})
</script>

<style scoped>
.page { padding: 20px }
</style>