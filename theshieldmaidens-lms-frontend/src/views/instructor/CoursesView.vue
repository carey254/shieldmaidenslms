<template>
  <div class="page">
    <div class="head">
      <div>
        <div class="title">Courses</div>
        <div class="subtitle">Your assigned courses and quick actions.</div>
      </div>
      <button class="btn" type="button" @click="load">
        Refresh
      </button>
    </div>

    <div class="grid">
      <div v-for="c in courses" :key="c.id" class="tile" @click="openCourse(c)">
        <div class="top">
          <div class="name">{{ c.title }}</div>
          <div class="chip">{{ c.status }}</div>
        </div>
        <div class="desc">{{ c.description }}</div>
        <div class="meta">
          <span>{{ c.enrolled_count }} students</span>
          <span>{{ c.modules_count }} modules</span>
        </div>
      </div>

      <div v-if="!loading && courses.length === 0" class="empty">
        No courses found. Ask admin to assign/publish courses.
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { API_BASE_URL } from '@/config/api'

type CourseRow = {
  id: number
  title: string
  description: string
  status: string
  modules_count: number
  enrolled_count: number
}

const router = useRouter()
const courses = ref<CourseRow[]>([])
const loading = ref(false)

const load = async () => {
  loading.value = true
  try {
    const token = localStorage.getItem('token')
    if (!token) {
      courses.value = []
      return
    }

    const res = await fetch(`${API_BASE_URL}/instructor/courses`, {
      headers: {
        Accept: 'application/json',
        Authorization: `Bearer ${token}`,
      },
    })
    if (!res.ok) {
      courses.value = []
      return
    }
    const data = await res.json()
    const rows = Array.isArray(data.courses) ? data.courses : []
    courses.value = rows.map((c: any) => ({
      id: Number(c.id),
      title: String(c.title || 'Course'),
      description: String(c.description || 'Course details will appear here.'),
      status: String(c.status || 'published'),
      modules_count: Number(c.modules_count || 0),
      enrolled_count: Number(c.enrolled_count || 0),
    }))
  } catch {
    courses.value = []
  } finally {
    loading.value = false
  }
}

const openCourse = (c: CourseRow) => {
  router.push(`/instructor/courses/${c.id}/content`)
}

onMounted(load)
</script>

<style scoped>
.page {
  padding: 22px 22px 40px;
  color: rgba(255, 255, 255, 0.92);
}

.head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 14px;
  margin-bottom: 18px;
}

.title {
  font-size: 20px;
  font-weight: 900;
  letter-spacing: -0.02em;
}

.subtitle {
  margin-top: 4px;
  color: rgba(255, 255, 255, 0.6);
  font-size: 13px;
}

.btn {
  padding: 10px 12px;
  border-radius: 14px;
  border: 1px solid rgba(255, 255, 255, 0.08);
  background: rgba(255, 255, 255, 0.03);
  color: rgba(255, 255, 255, 0.82);
  cursor: pointer;
  font-weight: 800;
  font-size: 12px;
}

.btn:hover {
  background: rgba(255, 255, 255, 0.05);
}

.grid {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 12px;
}

.tile {
  cursor: pointer;
  border-radius: 20px;
  padding: 14px;
  background: rgba(255, 255, 255, 0.04);
  border: 1px solid rgba(255, 255, 255, 0.06);
  transition: transform 180ms ease, border-color 180ms ease, background 180ms ease;
}

.tile:hover {
  transform: translateY(-1px);
  border-color: rgba(171, 145, 255, 0.35);
  background: rgba(255, 255, 255, 0.05);
}

.top {
  display: flex;
  align-items: start;
  justify-content: space-between;
  gap: 10px;
}

.name {
  font-weight: 900;
  letter-spacing: -0.01em;
}

.chip {
  padding: 6px 10px;
  border-radius: 999px;
  font-size: 11px;
  font-weight: 900;
  color: rgba(255, 232, 173, 0.95);
  background: rgba(255, 232, 173, 0.12);
  border: 1px solid rgba(255, 232, 173, 0.18);
  white-space: nowrap;
}

.desc {
  margin-top: 8px;
  color: rgba(255, 255, 255, 0.6);
  font-size: 12px;
  line-height: 1.5;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.meta {
  margin-top: 12px;
  display: flex;
  justify-content: space-between;
  gap: 10px;
  color: rgba(255, 255, 255, 0.5);
  font-size: 12px;
  font-weight: 800;
}

.empty {
  grid-column: 1 / -1;
  border-radius: 20px;
  padding: 16px;
  border: 1px dashed rgba(255, 255, 255, 0.12);
  background: rgba(255, 255, 255, 0.03);
  color: rgba(255, 255, 255, 0.62);
}

@media (max-width: 1100px) {
  .grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
}

@media (max-width: 768px) {
  .page {
    padding: 12px 12px 30px;
  }
  .grid {
    grid-template-columns: 1fr;
  }
}
</style>

