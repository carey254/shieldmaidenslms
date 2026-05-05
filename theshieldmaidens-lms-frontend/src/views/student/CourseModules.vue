<template>
  <div class="course-modules">
    <div class="course-header">
      <button class="back-btn" @click="$router.go(-1)">
        ← Back
      </button>
      <h1>{{ course.name }}</h1>
      <p>{{ course.description }}</p>
    </div>

    <div class="modules-container">
      <div class="module-list">
        <div 
          v-for="module in modules" 
          :key="module.id"
          class="module-item"
          :class="{ completed: module.completed, current: module.current }"
        >
          <div class="module-icon">
            <span v-if="module.completed">✅</span>
            <span v-else-if="module.current">📖</span>
            <span v-else>🔒</span>
          </div>
          <div class="module-content">
            <h3>{{ module.name }}</h3>
            <p>{{ module.description }}</p>
            <div class="module-meta">
              <span class="lessons">{{ module.lessons }} lessons</span>
              <span class="duration">{{ module.duration }}</span>
            </div>
          </div>
          <div class="module-progress">
            <div class="progress-bar">
              <div class="progress-fill" :style="{ width: module.progress + '%' }"></div>
            </div>
            <span class="progress-text">{{ module.progress }}%</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useRoute } from 'vue-router'

const route = useRoute()

const course = ref({
  id: route.params.id,
  name: 'Software Development',
  description: 'Learn modern software development practices'
})

const modules = ref([
  {
    id: 1,
    name: 'Introduction to Programming',
    description: 'Learn the basics of programming concepts and logic',
    lessons: 12,
    duration: '4 weeks',
    progress: 100,
    completed: true,
    current: false
  },
  {
    id: 2,
    name: 'Python Fundamentals',
    description: 'Master Python syntax and core programming concepts',
    lessons: 15,
    duration: '5 weeks',
    progress: 100,
    completed: true,
    current: false
  },
  {
    id: 3,
    name: 'Web Development Basics',
    description: 'HTML, CSS, and JavaScript fundamentals',
    lessons: 18,
    duration: '6 weeks',
    progress: 75,
    completed: false,
    current: true
  },
  {
    id: 4,
    name: 'Database Design',
    description: 'Learn database design and SQL',
    lessons: 10,
    duration: '3 weeks',
    progress: 0,
    completed: false,
    current: false
  },
  {
    id: 5,
    name: 'Software Engineering',
    description: 'Advanced software engineering principles',
    lessons: 20,
    duration: '7 weeks',
    progress: 0,
    completed: false,
    current: false
  }
])
</script>

<style scoped>
.course-modules {
  padding: 20px;
  max-width: 1000px;
  margin: 0 auto;
}

.course-header {
  margin-bottom: 30px;
}

.back-btn {
  background: none;
  border: none;
  color: #00897b;
  font-size: 16px;
  cursor: pointer;
  margin-bottom: 15px;
  display: flex;
  align-items: center;
  gap: 5px;
}

.back-btn:hover {
  text-decoration: underline;
}

.course-header h1 {
  font-size: 32px;
  color: #333;
  margin: 0 0 10px 0;
}

.course-header p {
  color: #666;
  margin: 0;
  font-size: 16px;
}

.modules-container {
  background: white;
  border-radius: 12px;
  padding: 20px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.module-list {
  display: flex;
  flex-direction: column;
  gap: 15px;
}

.module-item {
  display: flex;
  align-items: center;
  padding: 20px;
  border: 2px solid #e0e0e0;
  border-radius: 8px;
  transition: all 0.2s;
}

.module-item:hover {
  border-color: #00897b;
  box-shadow: 0 2px 8px rgba(0, 137, 123, 0.1);
}

.module-item.completed {
  border-color: #4caf50;
  background: #f8fff8;
}

.module-item.current {
  border-color: #ffc107;
  background: #fffcf0;
}

.module-icon {
  font-size: 24px;
  margin-right: 20px;
  min-width: 30px;
  text-align: center;
}

.module-content {
  flex: 1;
}

.module-content h3 {
  font-size: 18px;
  color: #333;
  margin: 0 0 5px 0;
}

.module-content p {
  color: #666;
  margin: 0 0 10px 0;
  font-size: 14px;
}

.module-meta {
  display: flex;
  gap: 15px;
  font-size: 12px;
  color: #999;
}

.module-progress {
  display: flex;
  align-items: center;
  gap: 10px;
  min-width: 150px;
}

.progress-bar {
  width: 80px;
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
}
</style>
