<template>
  <div class="page">
    <h1>Communications</h1>
    <div v-if="loading">Loading messages...</div>
    <ul v-else>
      <li v-for="m in messages" :key="m.id">
        <strong>{{ m.subject || m.title }}</strong> — {{ m.sender_name || m.sender || 'System' }}
      </li>
      <li v-if="!messages.length">No messages.</li>
    </ul>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { API_BASE_URL } from '@/config/api'
import { useAuthStore } from '@/stores/auth'
const auth = useAuthStore()
const messages = ref([])
const loading = ref(true)

onMounted(async () => {
  try {
    const res = await fetch(`${API_BASE_URL}/instructor/messages`, {
      headers: { Authorization: `Bearer ${auth.token || localStorage.getItem('token')}` }
    })
    if (res.ok) {
      const data = await res.json()
      messages.value = Array.isArray(data) ? data : (data.data || data)
    }
  } catch (e) {
    console.error(e)
  } finally { loading.value = false }
})
</script>

<style scoped>
.page { padding: 20px }
</style>