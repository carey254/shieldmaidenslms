<template>
  <div class="progress-view">
    <div class="progress-header">
      <h1>My Progress</h1>
      <p>Track your learning journey and achievements</p>
    </div>

    <div class="progress-stats">
      <div class="stat-card">
        <div class="stat-icon">🎯</div>
        <div class="stat-number">{{ totalProgress }}%</div>
        <div class="stat-label">Overall Progress</div>
      </div>
      <div class="stat-card">
        <div class="stat-icon">📚</div>
        <div class="stat-number">{{ completedModules }}</div>
        <div class="stat-label">Modules Completed</div>
      </div>
      <div class="stat-card">
        <div class="stat-icon">⏱️</div>
        <div class="stat-number">{{ totalHours }}</div>
        <div class="stat-label">Hours Learned</div>
      </div>
      <div class="stat-card">
        <div class="stat-icon">🏆</div>
        <div class="stat-number">{{ certificates }}</div>
        <div class="stat-label">Certificates Earned</div>
      </div>
    </div>

    <div class="progress-details">
      <div class="course-progress">
        <h2>Course Progress</h2>
        <div class="course-list">
          <div v-for="course in courses" :key="course.id" class="course-item">
            <div class="course-info">
              <h3>{{ course.name }}</h3>
              <p>{{ course.description }}</p>
            </div>
            <div class="course-progress-bar">
              <div class="progress-bar">
                <div class="progress-fill" :style="{ width: course.progress + '%' }"></div>
              </div>
              <span class="progress-text">{{ course.progress }}%</span>
            </div>
          </div>
        </div>
      </div>

      <div class="recent-achievements">
        <h2>Recent Achievements</h2>
        <div class="achievements-grid">
          <div v-for="achievement in achievements" :key="achievement.id" class="achievement-card">
            <div class="achievement-icon">{{ achievement.icon }}</div>
            <h4>{{ achievement.title }}</h4>
            <p>{{ achievement.description }}</p>
            <div class="achievement-date">{{ achievement.date }}</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'

const totalProgress = ref(67)
const completedModules = ref(8)
const totalHours = ref(124)
const certificates = ref(2)

const courses = ref([
  {
    id: 1,
    name: 'Software Development',
    description: 'Full-stack development with modern technologies',
    progress: 75
  },
  {
    id: 2,
    name: 'Python Programming',
    description: 'Learn Python from basics to advanced concepts',
    progress: 100
  },
  {
    id: 3,
    name: 'Database Design',
    description: 'Master database design and SQL',
    progress: 45
  }
])

const achievements = ref([
  {
    id: 1,
    icon: '🎓',
    title: 'Python Master',
    description: 'Completed all Python modules with distinction',
    date: '2024-03-01'
  },
  {
    id: 2,
    icon: '⭐',
    title: 'First Steps',
    description: 'Completed your first module',
    date: '2024-02-15'
  },
  {
    id: 3,
    icon: '🔥',
    title: '7-Day Streak',
    description: 'Logged in for 7 consecutive days',
    date: '2024-02-28'
  },
  {
    id: 4,
    icon: '💯',
    title: 'Perfect Score',
    description: 'Scored 100% on a quiz',
    date: '2024-03-05'
  }
])
</script>

<style scoped>
.progress-view {
  padding: 20px;
  max-width: 1200px;
  margin: 0 auto;
}

.progress-header {
  text-align: center;
  margin-bottom: 40px;
}

.progress-header h1 {
  font-size: 32px;
  color: #333;
  margin: 0 0 10px 0;
}

.progress-header p {
  color: #666;
  margin: 0;
  font-size: 16px;
}

.progress-stats {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 20px;
  margin-bottom: 40px;
}

.stat-card {
  background: white;
  padding: 25px;
  border-radius: 12px;
  text-align: center;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.stat-icon {
  font-size: 36px;
  margin-bottom: 10px;
}

.stat-number {
  font-size: 28px;
  font-weight: bold;
  color: #00897b;
  margin: 0 0 5px 0;
}

.stat-label {
  color: #666;
  font-size: 14px;
  margin: 0;
}

.progress-details {
  display: grid;
  grid-template-columns: 2fr 1fr;
  gap: 30px;
}

.course-progress, .recent-achievements {
  background: white;
  padding: 25px;
  border-radius: 12px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.course-progress h2, .recent-achievements h2 {
  font-size: 20px;
  color: #333;
  margin: 0 0 20px 0;
}

.course-list {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.course-item {
  padding: 15px 0;
  border-bottom: 1px solid #eee;
}

.course-item:last-child {
  border-bottom: none;
}

.course-info h3 {
  font-size: 16px;
  color: #333;
  margin: 0 0 5px 0;
}

.course-info p {
  color: #666;
  font-size: 14px;
  margin: 0 0 10px 0;
}

.course-progress-bar {
  display: flex;
  align-items: center;
  gap: 10px;
}

.progress-bar {
  flex: 1;
  height: 8px;
  background: #e0e0e0;
  border-radius: 4px;
  overflow: hidden;
}

.progress-fill {
  height: 100%;
  background: #00897b;
  border-radius: 4px;
  transition: width 0.3s ease;
}

.progress-text {
  font-size: 12px;
  color: #666;
  font-weight: 500;
  min-width: 40px;
}

.achievements-grid {
  display: flex;
  flex-direction: column;
  gap: 15px;
}

.achievement-card {
  padding: 15px;
  border: 1px solid #e0e0e0;
  border-radius: 8px;
  text-align: center;
}

.achievement-icon {
  font-size: 24px;
  margin-bottom: 8px;
}

.achievement-card h4 {
  font-size: 14px;
  color: #333;
  margin: 0 0 5px 0;
}

.achievement-card p {
  font-size: 12px;
  color: #666;
  margin: 0 0 8px 0;
}

.achievement-date {
  font-size: 10px;
  color: #999;
}
</style>
