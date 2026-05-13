<template>
  <div class="facilitator-sessions">
    <header class="head">
      <div>
        <h1>Class sessions</h1>
        <p class="lead">Schedule Google Meet or Zoom links for your courses. Enrolled students see these on Class Sessions.</p>
      </div>
    </header>

    <section class="card">
      <h2>New session</h2>
      <form class="grid-form" @submit.prevent="createSession">
        <label>Course
          <select v-model="form.course_id" required class="inp">
            <option disabled value="">Select course</option>
            <option v-for="c in courses" :key="c.id" :value="c.id">{{ c.title }}</option>
          </select>
        </label>
        <label>Title<input v-model="form.title" required class="inp" placeholder="Week 4 live workshop" /></label>
        <label>Starts (local)<input v-model="form.starts_at" type="datetime-local" required class="inp" /></label>
        <label>Ends (optional)<input v-model="form.ends_at" type="datetime-local" class="inp" /></label>
        <label>Duration (minutes)<input v-model.number="form.duration_minutes" type="number" min="15" class="inp" placeholder="60" /></label>
        <label class="full">Meeting link
          <input v-model="form.meeting_link" type="url" class="inp" placeholder="https://meet.google.com/..." required />
        </label>
        <label class="full">Notes<textarea v-model="form.description" rows="2" class="inp"></textarea></label>
        <div class="actions full">
          <button type="submit" class="btn primary" :disabled="saving">{{ saving ? 'Saving…' : 'Publish session' }}</button>
        </div>
      </form>
    </section>

    <section class="card">
      <h2>Upcoming &amp; recent</h2>
      <p v-if="!sessions.length" class="muted">No sessions yet.</p>
      <ul class="session-list">
        <li v-for="s in sessions" :key="s.id" class="session-row">
          <div>
            <strong>{{ s.title }}</strong>
            <div class="muted">{{ s.course_title }} · {{ formatStart(s.starts_at) }}</div>
          </div>
          <div class="row-actions">
            <a v-if="s.meeting_link" :href="s.meeting_link" target="_blank" rel="noopener" class="link">Open link</a>
            <button type="button" class="btn ghost" @click="remove(s.id)">Delete</button>
          </div>
        </li>
      </ul>
    </section>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import axios from 'axios'
import { API_BASE_URL } from '@/config/api'
import { useAuthStore } from '@/stores/auth'

const auth = useAuthStore()

const courses = ref<any[]>([])
const sessions = ref<any[]>([])
const saving = ref(false)

const form = ref({
  course_id: '' as string | number,
  title: '',
  description: '',
  meeting_link: '',
  starts_at: '',
  ends_at: '',
  duration_minutes: undefined as number | undefined,
})

const headers = () => ({
  Authorization: `Bearer ${auth.token}`,
  Accept: 'application/json',
  'Content-Type': 'application/json',
})

const prefix = () => (auth.user?.role === 'facilitator' ? '/facilitator' : '/instructor')

const loadCourses = async () => {
  const res = await axios.get(`${API_BASE_URL}${prefix()}/dashboard/courses`, { headers: headers() })
  const raw = res.data
  courses.value = Array.isArray(raw) ? raw : []
}

const loadSessions = async () => {
  const res = await axios.get(`${API_BASE_URL}${prefix()}/class-sessions`, { headers: headers() })
  sessions.value = res.data.sessions || []
}

const toIso = (local: string) => {
  if (!local) return ''
  const d = new Date(local)
  return Number.isNaN(d.getTime()) ? '' : d.toISOString()
}

const createSession = async () => {
  saving.value = true
  try {
    await axios.post(
      `${API_BASE_URL}${prefix()}/class-sessions`,
      {
        course_id: Number(form.value.course_id),
        title: form.value.title,
        description: form.value.description || null,
        meeting_link: form.value.meeting_link,
        starts_at: toIso(form.value.starts_at),
        ends_at: form.value.ends_at ? toIso(form.value.ends_at) : null,
        duration_minutes: form.value.duration_minutes || null,
      },
      { headers: headers() }
    )
    form.value = {
      course_id: '',
      title: '',
      description: '',
      meeting_link: '',
      starts_at: '',
      ends_at: '',
      duration_minutes: undefined,
    }
    await loadSessions()
  } catch (e) {
    console.error(e)
    alert('Could not create session. Check course assignment and datetime fields.')
  } finally {
    saving.value = false
  }
}

const remove = async (id: number) => {
  if (!confirm('Delete this session?')) return
  try {
    await axios.delete(`${API_BASE_URL}${prefix()}/class-sessions/${id}`, { headers: headers() })
    await loadSessions()
  } catch (e) {
    console.error(e)
  }
}

const formatStart = (iso: string) => {
  if (!iso) return ''
  return new Date(iso).toLocaleString()
}

onMounted(async () => {
  await loadCourses()
  await loadSessions()
})
</script>

<style scoped>
.facilitator-sessions {
  max-width: 900px;
  margin: 0 auto;
  padding: 1.25rem 1rem 2.5rem;
}
.head h1 {
  margin: 0 0 0.35rem;
  font-size: 1.65rem;
  color: #0f172a;
}
.lead {
  margin: 0;
  color: #475569;
  font-size: 0.95rem;
  line-height: 1.5;
}
.card {
  margin-top: 1.25rem;
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 14px;
  padding: 1.15rem 1.2rem 1.35rem;
  box-shadow: 0 1px 2px rgba(15, 23, 42, 0.05);
}
h2 {
  margin: 0 0 0.85rem;
  font-size: 1.05rem;
  color: #0f172a;
}
.grid-form {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
  gap: 0.75rem 1rem;
}
label {
  display: flex;
  flex-direction: column;
  gap: 0.3rem;
  font-size: 0.78rem;
  font-weight: 600;
  color: #334155;
}
.full {
  grid-column: 1 / -1;
}
.inp {
  border: 1px solid #cbd5e1;
  border-radius: 10px;
  padding: 0.45rem 0.6rem;
  font: inherit;
}
.actions {
  margin-top: 0.35rem;
}
.btn.primary {
  background: #4f46e5;
  color: #fff;
  border: none;
  border-radius: 10px;
  padding: 0.5rem 1rem;
  font-weight: 600;
  cursor: pointer;
}
.btn.ghost {
  background: transparent;
  border: 1px solid #cbd5e1;
  border-radius: 8px;
  padding: 0.35rem 0.65rem;
  cursor: pointer;
  font-size: 0.85rem;
}
.muted {
  color: #64748b;
  font-size: 0.85rem;
}
.session-list {
  list-style: none;
  margin: 0;
  padding: 0;
}
.session-row {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 1rem;
  padding: 0.75rem 0;
  border-bottom: 1px solid #f1f5f9;
}
.row-actions {
  display: flex;
  flex-direction: column;
  gap: 0.35rem;
  align-items: flex-end;
}
.link {
  color: #2563eb;
  font-size: 0.88rem;
}
</style>
