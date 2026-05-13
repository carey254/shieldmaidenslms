<template>
  <div class="opportunities-admin">
    <header class="page-header">
      <h1>Opportunities</h1>
      <p>Create programs and roles that appear on the public home page and in learner portals.</p>
    </header>

    <section class="create-card">
      <h2>Add opportunity</h2>
      <form class="grid-form" @submit.prevent="submit">
        <label>Title<input v-model="form.title" required class="inp" /></label>
        <label>Organization<input v-model="form.organization" required class="inp" /></label>
        <label>Type
          <select v-model="form.type" class="inp" required>
            <option value="internship">Internship</option>
            <option value="training">Training</option>
            <option value="job">Job</option>
            <option value="scholarship">Scholarship</option>
          </select>
        </label>
        <label>Location<input v-model="form.location" required class="inp" /></label>
        <label>Deadline<input v-model="form.deadline" type="date" required class="inp" /></label>
        <label>Contact email<input v-model="form.contact_email" type="email" required class="inp" /></label>
        <label>Visibility
          <select v-model="form.visibility" class="inp" required>
            <option value="all">Everyone (home + portals)</option>
            <option value="students">Students</option>
            <option value="instructors">Facilitators / instructors</option>
          </select>
        </label>
        <label class="full">Description<textarea v-model="form.description" rows="3" required class="inp"></textarea></label>
        <label class="full">Requirements<textarea v-model="form.requirements" rows="2" required class="inp"></textarea></label>
        <label class="full">Benefits<textarea v-model="form.benefits" rows="2" required class="inp"></textarea></label>
        <label class="full">Application link (optional)<input v-model="form.application_link" type="url" class="inp" placeholder="https://..." /></label>
        <div class="actions full">
          <button type="submit" class="btn primary" :disabled="saving">{{ saving ? 'Saving…' : 'Publish' }}</button>
        </div>
      </form>
    </section>

    <section class="list-card">
      <h2>Published</h2>
      <p v-if="!rows.length" class="muted">No opportunities yet.</p>
      <ul class="rows">
        <li v-for="o in rows" :key="o.id" class="row">
          <div>
            <strong>{{ o.title }}</strong>
            <span class="muted">{{ o.organization }} · {{ o.type }} · due {{ o.deadline }}</span>
          </div>
          <span class="pill">{{ o.visibility }}</span>
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

const rows = ref<any[]>([])
const saving = ref(false)

const form = ref({
  title: '',
  description: '',
  type: 'training',
  organization: '',
  location: '',
  deadline: '',
  requirements: '',
  benefits: '',
  contact_email: '',
  visibility: 'all',
  application_link: '',
})

const headers = () => ({
  Authorization: `Bearer ${auth.token}`,
  Accept: 'application/json',
  'Content-Type': 'application/json',
})

const load = async () => {
  const res = await axios.get(`${API_BASE_URL}/admin/opportunities`, { headers: headers() })
  rows.value = res.data.opportunities || []
}

const submit = async () => {
  saving.value = true
  try {
    await axios.post(`${API_BASE_URL}/admin/opportunities`, form.value, { headers: headers() })
    form.value = {
      title: '',
      description: '',
      type: 'training',
      organization: '',
      location: '',
      deadline: '',
      requirements: '',
      benefits: '',
      contact_email: '',
      visibility: 'all',
      application_link: '',
    }
    await load()
  } catch (e) {
    console.error(e)
    alert('Could not save. Check all fields (deadline must be in the future).')
  } finally {
    saving.value = false
  }
}

onMounted(load)
</script>

<style scoped>
.opportunities-admin {
  max-width: 960px;
  margin: 0 auto;
  padding: 1.5rem 1.25rem 3rem;
}
.page-header h1 {
  margin: 0 0 0.35rem;
  font-size: 1.75rem;
  color: #0f172a;
}
.page-header p {
  margin: 0 0 1.5rem;
  color: #64748b;
}
.create-card,
.list-card {
  background: #fff;
  border: 1px solid #e2e8f0;
  border-radius: 14px;
  padding: 1.25rem 1.25rem 1.5rem;
  margin-bottom: 1.25rem;
  box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
}
h2 {
  margin: 0 0 1rem;
  font-size: 1.1rem;
  color: #0f172a;
}
.grid-form {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
  gap: 0.85rem 1rem;
}
label {
  display: flex;
  flex-direction: column;
  gap: 0.35rem;
  font-size: 0.8rem;
  font-weight: 600;
  color: #334155;
}
.full {
  grid-column: 1 / -1;
}
.inp {
  border: 1px solid #cbd5e1;
  border-radius: 10px;
  padding: 0.5rem 0.65rem;
  font: inherit;
}
.actions {
  margin-top: 0.25rem;
}
.btn.primary {
  background: #2563eb;
  color: #fff;
  border: none;
  border-radius: 10px;
  padding: 0.55rem 1.1rem;
  font-weight: 600;
  cursor: pointer;
}
.btn.primary:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}
.muted {
  color: #64748b;
  font-size: 0.9rem;
}
.rows {
  list-style: none;
  margin: 0;
  padding: 0;
}
.row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 1rem;
  padding: 0.75rem 0;
  border-bottom: 1px solid #f1f5f9;
}
.pill {
  font-size: 0.75rem;
  padding: 0.2rem 0.55rem;
  border-radius: 999px;
  background: #f1f5f9;
  color: #475569;
  text-transform: capitalize;
}
</style>
