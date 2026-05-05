<template>
  <div class="course-curriculum">
    <div class="page-header">
      <button @click="goBack" class="back-btn">
        ← Back to Course
      </button>
      <h1>{{ course?.title }} - Curriculum</h1>
    </div>

    <!-- Course Progress Overview -->
    <div class="progress-overview">
      <div class="progress-header">
        <h2>Your Learning Progress</h2>
        <div class="overall-progress">
          <div class="progress-bar-container">
            <div class="progress-bar" :style="{ width: `${overallProgress}%` }"></div>
          </div>
          <span class="progress-text">{{ overallProgress }}% Complete</span>
        </div>
      </div>
      
      <div class="progress-stats">
        <div class="stat-item">
          <div class="stat-icon">Books</div>
          <div class="stat-info">
            <h3>{{ completedModules }}</h3>
            <p>Modules Completed</p>
          </div>
        </div>
        <div class="stat-item">
          <div class="stat-icon">Tests</div>
          <div class="stat-info">
            <h3>{{ completedCATs }}</h3>
            <p>CATs Completed</p>
          </div>
        </div>
        <div class="stat-item">
          <div class="stat-icon">Score</div>
          <div class="stat-info">
            <h3>{{ averageScore }}%</h3>
            <p>Average Score</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Course Instructors -->
    <div class="instructors-section">
      <h2>Course Instructors</h2>
      <div class="instructors-grid">
        <div class="instructor-card">
          <div class="instructor-avatar">SJ</div>
          <div class="instructor-info">
            <h3>Faith Jeptoo</h3>
            <p>Lead Instructor - Digital Safety Expert</p>
            <div class="instructor-actions">
              
            </div>
          </div>
        </div>
        <div class="instructor-card">
          
           
        </div>
      </div>
    </div>

    <!-- Module Curriculum -->
    <div class="curriculum-section">
      <h2>Course Modules</h2>
      <div class="modules-container">
        <div v-for="(module, index) in course?.modules" :key="index" class="module-card" :class="{ 
          'locked': isModuleLocked(index),
          'completed': isModuleCompleted(index),
          'current': isCurrentModule(index)
        }">
          <div class="module-header">
            <div class="module-info">
              <h3>{{ module.title }}</h3>
              <div class="module-status">
                <span v-if="isModuleCompleted(index)" class="status-badge completed">Completed</span>
                <span v-else-if="isCurrentModule(index)" class="status-badge current">In Progress</span>
                <span v-else-if="isModuleLocked(index)" class="status-badge locked">Locked</span>
                <span v-else class="status-badge available">Available</span>
              </div>
            </div>
            <div class="module-actions">
              <button 
                v-if="!isModuleLocked(index)"
                @click="openModule(index)" 
                class="open-module-btn"
                :disabled="false"
              >
                {{ isModuleCompleted(index) ? 'Review' : 'Start Learning' }}
              </button>
              <button 
                v-else
                class="locked-btn"
                disabled
              >
                Locked
              </button>
            </div>
          </div>

          <div class="module-content">
            <div class="module-preview">
              <ul class="subtopics-list">
                <li v-for="subtopic in module.subtopics" :key="subtopic">
                  {{ subtopic }}
                </li>
              </ul>
            </div>
            
            <div class="module-activities">
              <div class="activity-item">
                <div class="activity-icon">Reading</div>
                <div class="activity-info">
                  <h4>Reading Materials</h4>
                  <p>Comprehensive notes and resources</p>
                </div>
                <div class="activity-status">
                  <span v-if="getModuleProgress(index).reading" class="completed">Done</span>
                  <span v-else class="pending">Todo</span>
                </div>
              </div>
              
              <div class="activity-item">
                <div class="activity-icon">Video</div>
                <div class="activity-info">
                  <h4>Video Lessons</h4>
                  <p>Interactive video content</p>
                </div>
                <div class="activity-status">
                  <span v-if="getModuleProgress(index).videos" class="completed">Done</span>
                  <span v-else class="pending">Todo</span>
                </div>
              </div>
              
              <div class="activity-item">
                <div class="activity-icon">Assign</div>
                <div class="activity-info">
                  <h4>Assignments</h4>
                  <p>Practical exercises</p>
                </div>
                <div class="activity-status">
                  <span v-if="getModuleProgress(index).assignments" class="completed">Done</span>
                  <span v-else class="pending">Todo</span>
                </div>
              </div>
              
              <div class="activity-item">
                <div class="activity-icon">CAT</div>
                <div class="activity-info">
                  <h4>Module Assessment</h4>
                  <p>Continuous Assessment Test</p>
                </div>
                <div class="activity-status">
                  <span v-if="getModuleProgress(index).cat" class="completed">Done</span>
                  <span v-else-if="canTakeCAT(index)" class="available">Take</span>
                  <span v-else class="pending">Todo</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Final Exam Section -->
    <div class="final-exam-section" :class="{ 'locked': !canTakeFinalExam }">
      <div class="exam-header">
        <h2>Final Examination</h2>
        <div class="exam-status">
          <span v-if="finalExamCompleted" class="status-badge completed">Completed</span>
          <span v-else-if="canTakeFinalExam" class="status-badge available">Available</span>
          <span v-else class="status-badge locked">Complete all modules first</span>
        </div>
      </div>
      
      <div class="exam-details">
        <div class="exam-info">
          <h3>Exam Information</h3>
          <ul>
            <li>Duration: 2 hours</li>
            <li>Questions: 50 (Multiple choice, short answer, scenarios)</li>
            <li>Passing Score: 70%</li>
            <li>Covers all course modules</li>
          </ul>
        </div>
        
        <div class="exam-action">
          <button 
            v-if="canTakeFinalExam && !finalExamCompleted"
            @click="startFinalExam"
            class="start-exam-btn"
          >
            Start Final Exam
          </button>
          <button 
            v-else-if="finalExamCompleted"
            @click="viewExamResults"
            class="view-results-btn"
          >
            View Results
          </button>
          <button 
            v-else
            class="locked-btn"
            disabled
          >
            Locked - Complete all weeks first
          </button>
        </div>
      </div>
    </div>

    <!-- Week Details Modal -->
    <div v-if="showWeekModal" class="modal-overlay" @click="closeWeekModal">
      <div class="modal-content" @click.stop>
        <div class="modal-header">
          <h2>Week {{ selectedWeek + 1 }}: {{ course?.weeks[selectedWeek]?.topic }}</h2>
          <button @click="closeWeekModal" class="close-btn">✕</button>
        </div>
        <div class="modal-body">
          <div class="week-full-content">
            <div class="content-section">
              <h3>Reading Materials</h3>
              <div class="content-text">
                {{ course?.weeks[selectedWeek]?.notes }}
              </div>
            </div>
            
            <div class="content-section">
              <h3>Video Lessons</h3>
              <div class="video-placeholder">
                <div class="video-icon">Video</div>
                <p>Interactive video lessons will be available here</p>
              </div>
            </div>
            
            <div class="content-section">
              <h3>Assignments</h3>
              <div class="assignment-placeholder">
                <p>Practical exercises and assignments will be available here</p>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button @click="closeWeekModal" class="back-btn">← Back</button>
          <button 
            v-if="canTakeCAT(selectedWeek)"
            @click="startCAT(selectedWeek)"
            class="cat-btn"
          >
            Take Week {{ selectedWeek + 1 }} CAT
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { API_BASE_URL } from '@/config/api'

const router = useRouter()
const route = useRoute()

// State
const course = ref(null)
const courseProgress = ref({})
const showModuleModal = ref(false)
const selectedModule = ref(0)

// Computed properties
const overallProgress = computed(() => {
  const totalModules = course.value?.modules?.length || 0
  const completed = completedModules.value
  return totalModules > 0 ? Math.round((completed / totalModules) * 100) : 0
})

const completedModules = computed(() => {
  return Object.values(courseProgress.value).filter(module => module.completed).length
})

const completedCATs = computed(() => {
  return Object.values(courseProgress.value).filter(module => module.cat).length
})

const averageScore = computed(() => {
  const scores = Object.values(courseProgress.value)
    .filter(module => module.catScore)
    .map(module => module.catScore)
  
  if (scores.length === 0) return 0
  return Math.round(scores.reduce((a, b) => a + b, 0) / scores.length)
})

const finalExamCompleted = computed(() => {
  return courseProgress.value.finalExam?.completed || false
})

// Methods
const buildPlaceholderModules = (count: number) => {
  const n = Number.isFinite(count) && count > 0 ? count : 4
  return Array.from({ length: n }).map((_, idx) => ({
    title: `Module ${idx + 1}`,
    subtopics: ['Lesson content will appear here'],
  }))
}

const loadCourse = async () => {
  const courseId = parseInt(String(route.params.id))
  if (!Number.isFinite(courseId)) {
    router.push('/courses')
    return
  }

  const token = localStorage.getItem('token')
  if (!token) {
    router.push('/login')
    return
  }

  try {
    const res = await fetch(`${API_BASE_URL}/student/courses/${courseId}`, {
      headers: {
        Accept: 'application/json',
        Authorization: `Bearer ${token}`,
      },
    })

    if (!res.ok) {
      router.push('/courses')
      return
    }

    const data = await res.json()
    if (!data?.isEnrolled) {
      router.push('/courses')
      return
    }

    course.value = {
      id: data.id,
      title: data.title,
      description: data.description,
      duration: data.duration,
      modules_count: data.modules_count,
      modules: buildPlaceholderModules(Number(data.modules_count)),
      weeks: [],
    }
  } catch (e) {
    console.error('Error loading course:', e)
    router.push('/courses')
    return
  }

  loadCourseProgress()
}

const loadCourseProgress = () => {
  const user = JSON.parse(localStorage.getItem('user') || '{}')
  const progressKey = `course_progress_${course.value.id}_${user.id}`
  courseProgress.value = JSON.parse(localStorage.getItem(progressKey) || '{}')
  
  // Initialize progress if empty
  if (Object.keys(courseProgress.value).length === 0) {
    courseProgress.value = initializeProgress()
    saveProgress()
  }
}

const initializeProgress = () => {
  const progress = {}
  course.value.modules.forEach((module, index) => {
    progress[`module_${index}`] = {
      completed: false,
      reading: false,
      videos: false,
      assignments: false,
      cat: false,
      catScore: null
    }
  })
  progress.finalExam = {
    completed: false,
    score: null,
    takenAt: null
  }
  return progress
}

const saveProgress = () => {
  const user = JSON.parse(localStorage.getItem('user') || '{}')
  const progressKey = `course_progress_${course.value.id}_${user.id}`
  localStorage.setItem(progressKey, JSON.stringify(courseProgress.value))
}

const isModuleLocked = (index) => {
  if (index === 0) return false
  return !courseProgress.value[`module_${index - 1}`]?.completed
}

const isModuleCompleted = (index) => {
  return courseProgress.value[`module_${index}`]?.completed || false
}

const isCurrentModule = (index) => {
  if (isModuleCompleted(index)) return false
  if (isModuleLocked(index)) return false
  return true
}

const getModuleProgress = (index) => {
  return courseProgress.value[`module_${index}`] || {}
}

const canTakeCAT = (index) => {
  const moduleProgress = getModuleProgress(index)
  return moduleProgress.reading && moduleProgress.videos && moduleProgress.assignments && !moduleProgress.cat
}

const canTakeFinalExam = computed(() => {
  return completedModules.value === course.value?.modules?.length
})

const openModule = (index) => {
  selectedModule.value = index
  showModuleModal.value = true
}

const closeModuleModal = () => {
  showModuleModal.value = false
}

const markActivityComplete = (moduleIndex, activity) => {
  courseProgress.value[`module_${moduleIndex}`][activity] = true
  
  // Check if module is completed
  const moduleProgress = courseProgress.value[`module_${moduleIndex}`]
  if (moduleProgress.reading && moduleProgress.videos && moduleProgress.assignments && moduleProgress.cat) {
    moduleProgress.completed = true
  }
  
  saveProgress()
}

const startCAT = (moduleIndex) => {
  // Navigate to CAT page
  router.push(`/course/${course.value.id}/cat/${moduleIndex}`)
}

const startFinalExam = () => {
  // Navigate to final exam page
  router.push(`/course/${course.value.id}/exam`)
}

const viewExamResults = () => {
  // Navigate to exam results page
  router.push(`/course/${course.value.id}/exam/results`)
}

const goBack = () => {
  router.push('/courses')
}

// Lifecycle
onMounted(() => {
  loadCourse()
})
</script>

<style scoped>
.course-curriculum {
  max-width: 1200px;
  margin: 0 auto;
  padding: 20px;
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

.page-header {
  display: flex;
  align-items: center;
  gap: 20px;
  margin-bottom: 30px;
  padding-bottom: 20px;
  border-bottom: 2px solid #e0e0e0;
}

.back-btn {
  background: #f5f5f5;
  border: none;
  padding: 10px 16px;
  border-radius: 6px;
  cursor: pointer;
  font-size: 14px;
  transition: all 0.3s ease;
}

.back-btn:hover {
  background: #e0e0e0;
}

.page-header h1 {
  color: #333;
  font-size: 28px;
  margin: 0;
  font-weight: 600;
}

/* Progress Overview */
.progress-overview {
  background: white;
  border-radius: 16px;
  padding: 30px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  margin-bottom: 30px;
}

.progress-header {
  margin-bottom: 25px;
}

.progress-header h2 {
  color: #333;
  font-size: 24px;
  margin: 0 0 15px 0;
}

.overall-progress {
  display: flex;
  align-items: center;
  gap: 15px;
}

.progress-bar-container {
  flex: 1;
  height: 20px;
  background: #f0f0f0;
  border-radius: 10px;
  overflow: hidden;
}

.progress-bar {
  height: 100%;
  background: linear-gradient(90deg, #00897b, #26a69a);
  transition: width 0.5s ease;
}

.progress-text {
  font-weight: 600;
  color: #00897b;
  min-width: 60px;
}

.progress-stats {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
  gap: 20px;
}

.stat-item {
  display: flex;
  align-items: center;
  gap: 15px;
  background: #f9f9f9;
  padding: 20px;
  border-radius: 12px;
}

.stat-icon {
  font-size: 32px;
  width: 60px;
  height: 60px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, #00897b, #26a69a);
  border-radius: 50%;
}

.stat-info h3 {
  margin: 0;
  color: #333;
  font-size: 24px;
  font-weight: 600;
}

.stat-info p {
  margin: 0;
  color: #666;
  font-size: 14px;
}

/* Instructors Section */
.instructors-section {
  background: white;
  border-radius: 16px;
  padding: 30px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  margin-bottom: 30px;
}

.instructors-section h2 {
  color: #333;
  font-size: 24px;
  margin: 0 0 20px 0;
}

.instructors-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 20px;
}

.instructor-card {
  display: flex;
  align-items: center;
  gap: 20px;
  background: #f9f9f9;
  padding: 20px;
  border-radius: 12px;
  border: 1px solid #e0e0e0;
}

.instructor-avatar {
  width: 60px;
  height: 60px;
  border-radius: 50%;
  background: linear-gradient(135deg, #00897b, #26a69a);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 28px;
  flex-shrink: 0;
}

.instructor-info {
  flex: 1;
}

.instructor-info h3 {
  margin: 0 0 5px 0;
  color: #333;
  font-size: 18px;
  font-weight: 600;
}

.instructor-info p {
  margin: 0 0 10px 0;
  color: #666;
  font-size: 14px;
}

.instructor-actions {
  display: flex;
  gap: 10px;
}

.contact-btn, .office-btn {
  padding: 6px 12px;
  border: none;
  border-radius: 6px;
  font-size: 12px;
  cursor: pointer;
  transition: all 0.3s ease;
}

.contact-btn {
  background: #e3f2fd;
  color: #1976d2;
}

.office-btn {
  background: #f3e5f5;
  color: #7b1fa2;
}

.contact-btn:hover, .office-btn:hover {
  transform: translateY(-1px);
}

/* Curriculum Section */
.curriculum-section {
  background: white;
  border-radius: 16px;
  padding: 30px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  margin-bottom: 30px;
}

.curriculum-section h2 {
  color: #333;
  font-size: 24px;
  margin: 0 0 20px 0;
}

.weeks-container {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.week-card {
  border: 2px solid #e0e0e0;
  border-radius: 16px;
  padding: 25px;
  transition: all 0.3s ease;
}

.week-card.locked {
  opacity: 0.7;
  border-color: #ccc;
}

.week-card.completed {
  border-color: #4caf50;
  background: #f1f8e9;
}

.week-card.current {
  border-color: #00897b;
  background: #e0f2f1;
}

.week-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.week-info h3 {
  margin: 0 0 10px 0;
  color: #333;
  font-size: 20px;
  font-weight: 600;
}

.status-badge {
  padding: 6px 12px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 500;
}

.status-badge.completed {
  background: #4caf50;
  color: white;
}

.status-badge.current {
  background: #00897b;
  color: white;
}

.status-badge.locked {
  background: #ccc;
  color: #666;
}

.status-badge.available {
  background: #ff9800;
  color: white;
}

.open-week-btn, .locked-btn {
  padding: 10px 20px;
  border: none;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
}

.open-week-btn {
  background: linear-gradient(135deg, #00897b, #26a69a);
  color: white;
}

.open-week-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 137, 123, 0.3);
}

.locked-btn {
  background: #ccc;
  color: #666;
  cursor: not-allowed;
}

.week-activities {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 15px;
}

.activity-item {
  display: flex;
  align-items: center;
  gap: 15px;
  padding: 15px;
  background: #f9f9f9;
  border-radius: 8px;
  border: 1px solid #e0e0e0;
}

.activity-icon {
  font-size: 24px;
  width: 40px;
  height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: white;
  border-radius: 50%;
  flex-shrink: 0;
}

.activity-info {
  flex: 1;
}

.activity-info h4 {
  margin: 0 0 5px 0;
  color: #333;
  font-size: 14px;
  font-weight: 600;
}

.activity-info p {
  margin: 0;
  color: #666;
  font-size: 12px;
}

.activity-status span {
  font-size: 20px;
  width: 30px;
  height: 30px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
}

.completed {
  background: #4caf50;
  color: white;
}

.available {
  background: #ff9800;
  color: white;
}

.pending {
  background: #ccc;
  color: #666;
}

/* Final Exam Section */
.final-exam-section {
  background: white;
  border-radius: 16px;
  padding: 30px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  border: 2px solid #4caf50;
}

.final-exam-section.locked {
  border-color: #ccc;
  opacity: 0.7;
}

.exam-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 25px;
}

.exam-header h2 {
  margin: 0;
  color: #333;
  font-size: 24px;
  font-weight: 600;
}

.exam-details {
  display: grid;
  grid-template-columns: 2fr 1fr;
  gap: 30px;
  align-items: start;
}

.exam-info h3 {
  margin: 0 0 15px 0;
  color: #333;
  font-size: 18px;
}

.exam-info ul {
  margin: 0;
  padding-left: 20px;
  color: #666;
}

.exam-info li {
  margin-bottom: 8px;
}

.start-exam-btn, .view-results-btn, .locked-btn {
  padding: 15px 30px;
  border: none;
  border-radius: 12px;
  font-size: 16px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  width: 100%;
}

.start-exam-btn {
  background: linear-gradient(135deg, #4caf50, #66bb6a);
  color: white;
}

.start-exam-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(76, 175, 80, 0.3);
}

.view-results-btn {
  background: linear-gradient(135deg, #2196f3, #42a5f5);
  color: white;
}

.view-results-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(33, 150, 243, 0.3);
}

.locked-btn {
  background: #ccc;
  color: #666;
  cursor: not-allowed;
}

/* Modal Styles */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  padding: 20px;
}

.modal-content {
  background: white;
  border-radius: 16px;
  max-width: 800px;
  width: 100%;
  max-height: 80vh;
  overflow-y: auto;
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 25px 30px;
  border-bottom: 1px solid #e0e0e0;
  background: linear-gradient(135deg, #00897b, #26a69a);
  color: white;
  border-radius: 16px 16px 0 0;
}

.modal-header h2 {
  margin: 0;
  font-size: 20px;
  font-weight: 600;
}

.close-btn {
  background: rgba(255, 255, 255, 0.2);
  border: none;
  width: 36px;
  height: 36px;
  border-radius: 50%;
  color: white;
  font-size: 18px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
}

.close-btn:hover {
  background: rgba(255, 255, 255, 0.3);
}

.modal-body {
  padding: 30px;
}

.week-full-content {
  display: flex;
  flex-direction: column;
  gap: 25px;
}

.content-section h3 {
  color: #00897b;
  font-size: 18px;
  margin: 0 0 15px 0;
}

.content-text {
  background: #f9f9f9;
  padding: 20px;
  border-radius: 8px;
  border-left: 4px solid #00897b;
  line-height: 1.6;
  color: #666;
}

.video-placeholder, .assignment-placeholder {
  background: #f0f0f0;
  padding: 40px;
  border-radius: 8px;
  text-align: center;
  color: #666;
}

.video-icon {
  font-size: 48px;
  margin-bottom: 10px;
}

.modal-footer {
  padding: 20px 30px;
  border-top: 1px solid #e0e0e0;
  background: #f9f9f9;
  display: flex;
  justify-content: space-between;
  border-radius: 0 0 16px 16px;
}

.cat-btn {
  background: linear-gradient(135deg, #ff9800, #ffa726);
  color: white;
  border: none;
  padding: 12px 24px;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
}

.cat-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(255, 152, 0, 0.3);
}

/* Responsive Design */
@media (max-width: 768px) {
  .course-curriculum {
    padding: 15px;
  }
  
  .page-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 15px;
  }
  
  .page-header h1 {
    font-size: 24px;
  }
  
  .progress-stats {
    grid-template-columns: 1fr;
  }
  
  .instructors-grid {
    grid-template-columns: 1fr;
  }
  
  .week-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 15px;
  }
  
  .week-activities {
    grid-template-columns: 1fr;
  }
  
  .exam-details {
    grid-template-columns: 1fr;
  }
  
  .modal-content {
    margin: 10px;
    max-height: 90vh;
  }
}
</style>
