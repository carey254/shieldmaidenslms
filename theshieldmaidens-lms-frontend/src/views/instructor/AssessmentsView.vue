<template>
  <div class="page">
    <h1>Assessments</h1>
    <div v-if="loading">Loading assessments...</div>
    <ul v-else>
      <li v-for="a in assessments" :key="a.id">{{ a.title }} — {{ a.course_title || '—' }}</li>
      <li v-if="!assessments.length">No assessments found.</li>
    </ul>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { API_BASE_URL } from '@/config/api'
import { useAuthStore } from '@/stores/auth'
const auth = useAuthStore()
const assessments = ref([])
const loading = ref(true)

onMounted(async () => {
  try {
    const res = await fetch(`${API_BASE_URL}/instructor/assignments`, {
      headers: { Authorization: `Bearer ${auth.token || localStorage.getItem('token')}` }
    })
    if (res.ok) {
      const data = await res.json()
      assessments.value = Array.isArray(data) ? data : (data.data || data)
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
</style>