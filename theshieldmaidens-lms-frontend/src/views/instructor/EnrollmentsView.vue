<template>
  <div class="page">
    <h1>Enrollments</h1>
    <div v-if="loading">Loading enrollments...</div>
    <table v-else-if="enrollments.length">
      <thead>
        <tr>
          <th>Student</th>
          <th>Course</th>
          <th>Status</th>
          <th>Progress</th>
          <th>Enrolled Date</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="e in enrollments" :key="e.id">
          <td>
            <div>{{ e.student_name }}</div>
            <small>{{ e.student_email }}</small>
          </td>
          <td>{{ e.course_title }}</td>
          <td>
            <span :class="['status-badge', e.status]">{{ e.status }}</span>
          </td>
          <td>{{ e.progress }}%</td>
          <td>{{ formatDate(e.enrolled_at) }}</td>
        </tr>
      </tbody>
    </table>
    <div v-else class="empty">No enrollments found.</div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { API_BASE_URL } from '@/config/api'
import { useAuthStore } from '@/stores/auth'
const auth = useAuthStore()
const enrollments = ref([])
const loading = ref(true)

const formatDate = (dateStr) => {
  if (!dateStr) return '—'
  return new Date(dateStr).toLocaleDateString()
}

onMounted(async () => {
  try {
    const res = await fetch(`${API_BASE_URL}/instructor/enrollments`, {
      headers: { Authorization: `Bearer ${auth.token || localStorage.getItem('token')}` }
    })
    if (res.ok) {
      const data = await res.json()
      enrollments.value = data.data || data
    }
  } catch (e) {
    console.error(e)
  } finally { loading.value = false }
})
</script>

<style scoped>
.page { padding: 20px }
table { width: 100%; border-collapse: collapse }
th, td { border-bottom: 1px solid #eee; padding: 12px 8px; text-align: left }
th { font-weight: 600; color: #475569 }
small { color: #64748b; font-size: 12px }
.status-badge {
  padding: 4px 8px;
  border-radius: 12px;
  font-size: 12px;
  font-weight: 500;
}
.status-badge.enrolled { background: #ffedd5; color: #c2410c }
.status-badge.in_progress { background: #fef3c7; color: #92400e }
.status-badge.completed { background: #dcfce7; color: #166534 }
.empty { text-align: center; padding: 40px; color: #64748b }
</style>