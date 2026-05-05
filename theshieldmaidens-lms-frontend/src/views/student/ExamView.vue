<template>
  <div class="exam-container">
    <!-- Exam Header -->
    <div class="exam-header">
      <div class="exam-info">
        <h1>📝 Online Safety Final Exam</h1>
        <div class="exam-meta">
          <span class="cat-points">🐱 Cat Points: {{ catPoints }}</span>
          <span class="timer">⏱️ Time: {{ formatTime(timeRemaining) }}</span>
          <span class="progress">Question {{ currentQuestion + 1 }} of {{ totalQuestions }}</span>
        </div>
      </div>
      <div class="progress-bar-container">
        <div class="progress-bar">
          <div class="progress-fill" :style="{ width: examProgress + '%' }"></div>
        </div>
        <span class="progress-text">{{ examProgress }}% Complete</span>
      </div>
    </div>

    <!-- Exam Content -->
    <div class="exam-content">
      <!-- Loading State -->
      <div v-if="isLoading" class="loading-state">
        <div class="loading-spinner"></div>
        <p>Loading exam questions...</p>
      </div>
      
      <!-- Real-time Questions -->
      <div v-else-if="!isLoading && questions.length > 0">
        <!-- Multiple Choice Questions -->
        <div v-if="currentQuestionType === 'multiple'" class="question-section">
          <div class="question-card">
            <div class="question-header">
              <span class="question-number">Question {{ currentQuestion + 1 }}</span>
              <span class="question-points">{{ currentQuestionData.points }} points</span>
            </div>
            
            <h3 class="question-text">{{ currentQuestionData.question }}</h3>
            
            <div class="options-grid">
              <div 
                v-for="(option, index) in currentQuestionData.options" 
                :key="index"
                class="option-card"
                :class="{ 
                  selected: selectedAnswers[currentQuestion] === index,
                  correct: showResults && currentQuestionData.correct === index,
                  incorrect: showResults && selectedAnswers[currentQuestion] === index && currentQuestionData.correct !== index
                }"
                @click="selectAnswer(index)"
              >
                <div class="option-label">{{ String.fromCharCode(65 + index) }}</div>
                <div class="option-text">{{ option }}</div>
                <div class="option-indicator">
                  <span v-if="showResults && currentQuestionData.correct === index">✅</span>
                  <span v-if="showResults && selectedAnswers[currentQuestion] === index && currentQuestionData.correct !== index">❌</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Short Answer Questions -->
        <div v-else-if="currentQuestionType === 'short'" class="question-section">
          <div class="question-card">
            <div class="question-header">
              <span class="question-number">Question {{ currentQuestion + 1 }}</span>
              <span class="question-points">{{ currentQuestionData.points }} points</span>
            </div>
            
            <h3 class="question-text">{{ currentQuestionData.question }}</h3>
            
            <div class="answer-area">
              <textarea 
                v-model="shortAnswers[currentQuestion - multipleQuestions.length]" 
                class="short-answer-input"
                placeholder="Type your answer here..."
                rows="4"
                :disabled="showResults"
                @blur="saveShortAnswer"
              ></textarea>
              
              <div v-if="showResults" class="answer-feedback">
                <div class="correct-answer">
                  <strong>Sample Answer:</strong>
                  <p>{{ currentQuestionData.sampleAnswer }}</p>
                </div>
                <div class="points-earned">
                  Points: {{ getShortAnswerPoints() }}/{{ currentQuestionData.points }}
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Scenario Questions -->
        <div v-else-if="currentQuestionType === 'scenario'" class="question-section">
          <div class="question-card">
            <div class="question-header">
              <span class="question-number">Question {{ currentQuestion + 1 }}</span>
              <span class="question-points">{{ currentQuestionData.points }} points</span>
            </div>
            
            <div class="scenario-content">
              <h3 class="scenario-title">{{ currentQuestionData.scenario }}</h3>
              <p class="scenario-text">{{ currentQuestionData.question }}</p>
              
              <div class="answer-area">
                <textarea 
                  v-model="scenarioAnswers[currentQuestion - multipleQuestions.length - shortQuestions.length]" 
                  class="scenario-answer-input"
                  placeholder="Provide your detailed answer..."
                  rows="6"
                  :disabled="showResults"
                  @blur="saveScenarioAnswer"
                ></textarea>
                
                <div v-if="showResults" class="answer-feedback">
                  <div class="correct-answer">
                    <strong>Key Points Expected:</strong>
                    <ul>
                      <li v-for="point in currentQuestionData.keyPoints" :key="point">{{ point }}</li>
                    </ul>
                  </div>
                  <div class="points-earned">
                    Points: {{ getScenarioPoints() }}/{{ currentQuestionData.points }}
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Navigation Buttons -->
    <div class="exam-navigation">
      <button 
        @click="previousQuestion" 
        class="nav-btn prev-btn"
        :disabled="currentQuestion === 0"
      >
        ← Previous
      </button>
      
      <button 
        v-if="currentQuestion < totalQuestions - 1"
        @click="nextQuestion" 
        class="nav-btn next-btn"
      >
        Next →
      </button>
      
      <button 
        v-else-if="!showResults"
        @click="submitExam" 
        class="nav-btn submit-btn"
        :disabled="!canSubmit"
      >
        🐱 Submit Exam
      </button>
      
      <button 
        v-else
        @click="viewResults" 
        class="nav-btn results-btn"
      >
        📊 View Results
      </button>
    </div>

    <!-- Cat Points Animation -->
    <div v-if="showCatAnimation" class="cat-animation">
      <div class="cat-celebration">
        <div class="cat-icon">🐱</div>
        <div class="points-popup">+{{ earnedCatPoints }} Cat Points!</div>
      </div>
    </div>

    <!-- Results Modal -->
    <div v-if="showResultsModal" class="modal-overlay" @click="closeResultsModal">
      <div class="modal-content results-modal" @click.stop>
        <div class="modal-header">
          <h2>🎉 Exam Results!</h2>
          <button @click="closeResultsModal" class="close-btn">✕</button>
        </div>
        
        <div class="results-content">
          <div class="score-display">
            <div class="score-circle">
              <div class="score-number">{{ examScore }}%</div>
              <div class="score-label">{{ examScore >= 70 ? 'PASSED!' : 'FAILED' }}</div>
            </div>
          </div>
          
          <div class="score-breakdown">
            <h3>Score Breakdown:</h3>
            <div class="score-item">
              <span>Multiple Choice:</span>
              <span>{{ multipleChoiceScore }}/{{ totalMultiplePoints }}</span>
            </div>
            <div class="score-item">
              <span>Short Answer:</span>
              <span>{{ shortAnswerScore }}/{{ totalShortPoints }}</span>
            </div>
            <div class="score-item">
              <span>Scenarios:</span>
              <span>{{ scenarioScore }}/{{ totalScenarioPoints }}</span>
            </div>
            <div class="score-item total">
              <span>Total:</span>
              <span>{{ totalPoints }}/{{ maxPoints }}</span>
            </div>
          </div>
          
          <div class="cat-points-earned">
            <h3>🐱 Cat Points Earned:</h3>
            <div class="cat-points-display">
              <span class="points-number">{{ catPoints }}</span>
              <span class="points-label">Cat Points</span>
            </div>
          </div>
          
          <div class="course-completion">
            <h3>📚 Course Status:</h3>
            <div class="completion-badge">
              <span class="completion-text">Online Safety Course</span>
              <span class="completion-percent">100% Complete</span>
            </div>
          </div>
          
          <div class="results-actions">
            <button @click="downloadCertificate" class="action-btn">
              📜 Download Certificate
            </button>
            <button @click="closeResultsModal" class="action-btn">
              🏠 Back to Dashboard
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import axios from 'axios'
import { API_BASE_URL as BASE_API_URL } from '@/config/api'

const router = useRouter()
const route = useRoute()

// Direct API integration - no external imports
const API_BASE_URL = BASE_API_URL

// Create axios instance
const apiClient = axios.create({
  baseURL: API_BASE_URL,
  timeout: 10000,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  }
})

// Add auth interceptor
apiClient.interceptors.request.use((config) => {
  const token = localStorage.getItem('token')
  if (token) {
    config.headers.Authorization = `Bearer ${token}`
  }
  return config
})

// Direct API functions inside the component
const startExam = async (courseId) => {
  try {
    const response = await apiClient.post('/exam/start', { courseId })
    return response.data
  } catch (error) {
    console.error('Error starting exam:', error)
    throw error
  }
}

const submitAnswer = async (examSessionId, questionId, answer) => {
  try {
    const response = await apiClient.post('/exam/answer', {
      examSessionId,
      questionId,
      answer
    })
    return response.data
  } catch (error) {
    console.error('Error submitting answer:', error)
    throw error
  }
}

const submitExamApi = async (examSessionId, answers) => {
  try {
    const response = await apiClient.post('/exam/submit', {
      examSessionId,
      answers
    })
    return response.data
  } catch (error) {
    console.error('Error submitting exam:', error)
    throw error
  }
}

const getResults = async (examSessionId) => {
  try {
    const response = await apiClient.get(`/exam/results/${examSessionId}`)
    return response.data
  } catch (error) {
    console.error('Error getting results:', error)
    throw error
  }
}

const getQuestions = async (courseId) => {
  try {
    const response = await apiClient.get(`/exam/questions/${courseId}`)
    return response.data
  } catch (error) {
    console.error('Error getting questions:', error)
    throw error
  }
}

const getRemainingTime = async (examSessionId) => {
  try {
    const response = await apiClient.get(`/exam/time/${examSessionId}`)
    return response.data
  } catch (error) {
    console.error('Error getting time:', error)
    throw error
  }
}

const endExam = async (examSessionId) => {
  try {
    const response = await apiClient.post('/exam/end', {
      examSessionId
    })
    return response.data
  } catch (error) {
    console.error('Error ending exam:', error)
    throw error
  }
}

// State
const examSession = ref(null)
const courseId = ref(route.params.courseId)
const currentQuestion = ref(0)
const questions = ref([])
const selectedAnswers = ref({})
const shortAnswers = ref({})
const scenarioAnswers = ref({})
const showResults = ref(false)
const showResultsModal = ref(false)
const showCatAnimation = ref(false)
const timeRemaining = ref(3600) // Will be updated from server
const catPoints = ref(0)
const timer = ref(null)
const isLoading = ref(false)
const examResults = ref(null)
const isSubmitting = ref(false)

// Computed properties
const totalQuestions = computed(() => questions.value.length)

const currentQuestionType = computed(() => {
  if (!questions.value[currentQuestion.value]) return 'multiple'
  const question = questions.value[currentQuestion.value]
  return question.type || 'multiple'
})

const currentQuestionData = computed(() => {
  return questions.value[currentQuestion.value] || {}
})

const examProgress = computed(() => {
  if (totalQuestions.value === 0) return 0
  const answered = Object.keys(selectedAnswers.value).length + 
                   Object.keys(shortAnswers.value).filter(key => shortAnswers.value[key].trim()).length +
                   Object.keys(scenarioAnswers.value).filter(key => scenarioAnswers.value[key].trim()).length
  return Math.round((answered / totalQuestions.value) * 100)
})

const canSubmit = computed(() => {
  return examProgress.value === 100 && !isSubmitting.value
})

// Helper functions for scoring (used only after results are loaded)
const getShortAnswerPoints = () => {
  if (!examResults.value || !examResults.value.questionResults) return 0
  
  const questionId = currentQuestionData.value.id
  const result = examResults.value.questionResults.find(r => r.questionId === questionId)
  return result?.points || 0
}

const getScenarioPoints = () => {
  if (!examResults.value || !examResults.value.questionResults) return 0
  
  const questionId = currentQuestionData.value.id
  const result = examResults.value.questionResults.find(r => r.questionId === questionId)
  return result?.points || 0
}

// Methods
const formatTime = (seconds) => {
  const mins = Math.floor(seconds / 60)
  const secs = seconds % 60
  return `${mins}:${secs.toString().padStart(2, '0')}`
}

const selectAnswer = async (index) => {
  if (showResults.value || !examSession.value) return
  
  try {
    const question = currentQuestionData.value
    await submitAnswer(examSession.value.id, question.id, index)
    selectedAnswers.value[currentQuestion.value] = index
  } catch (error) {
    console.error('Error submitting answer:', error)
    alert('Error submitting answer. Please try again.')
  }
}

const nextQuestion = () => {
  if (currentQuestion.value < totalQuestions.value - 1) {
    currentQuestion.value++
  }
}

const previousQuestion = () => {
  if (currentQuestion.value > 0) {
    currentQuestion.value--
  }
}

const saveShortAnswer = async () => {
  if (!examSession.value) return
  
  try {
    const question = currentQuestionData.value
    const shortIndex = currentQuestion.value - questions.value.filter(q => q.type === 'multiple').length
    await submitAnswer(examSession.value.id, question.id, shortAnswers.value[shortIndex])
  } catch (error) {
    console.error('Error saving short answer:', error)
  }
}

const saveScenarioAnswer = async () => {
  if (!examSession.value) return
  
  try {
    const question = currentQuestionData.value
    const scenarioIndex = currentQuestion.value - questions.value.filter(q => q.type === 'multiple').length - questions.value.filter(q => q.type === 'short').length
    await submitAnswer(examSession.value.id, question.id, scenarioAnswers.value[scenarioIndex])
  } catch (error) {
    console.error('Error saving scenario answer:', error)
  }
}

const submitExam = async () => {
  if (!canSubmit.value || !examSession.value) return
  
  isSubmitting.value = true
  
  try {
    // Prepare all answers for submission
    const allAnswers = {}
    
    // Multiple choice answers
    questions.value.forEach((question, index) => {
      if (question.type === 'multiple') {
        allAnswers[question.id] = selectedAnswers.value[index]
      } else if (question.type === 'short') {
        const shortIndex = index - questions.value.filter(q => q.type === 'multiple').length
        allAnswers[question.id] = shortAnswers.value[shortIndex]
      } else if (question.type === 'scenario') {
        const scenarioIndex = index - questions.value.filter(q => q.type === 'multiple').length - questions.value.filter(q => q.type === 'short').length
        allAnswers[question.id] = scenarioAnswers.value[scenarioIndex]
      }
    })
    
    // Submit exam
    const response = await submitExamApi(examSession.value.id, allAnswers)
    
    // Get results
    const results = await getResults(examSession.value.id)
    examResults.value = results
    
    // Calculate cat points
    catPoints.value = Math.floor(results.score / 10)
    
    // Show results
    showResults.value = true
    
    // Show cat animation
    showCatAnimation.value = true
    setTimeout(() => {
      showCatAnimation.value = false
    }, 3000)
    
    // Update course progress
    updateCourseProgress(results)
    
  } catch (error) {
    console.error('Error submitting exam:', error)
    alert('Error submitting exam. Please try again.')
  } finally {
    isSubmitting.value = false
  }
}

const updateCourseProgress = (results) => {
  try {
    // Progress/exam results should be persisted server-side.
    // This SPA no longer mutates localStorage enrollments.
  } catch (error) {
    console.error('Error updating course progress:', error)
  }
}

const viewResults = () => {
  showResultsModal.value = true
}

const closeResultsModal = () => {
  showResultsModal.value = false
  router.push('/dashboard')
}

const downloadCertificate = () => {
  // Generate certificate using real results
  const certificate = `
📜 CERTIFICATE OF COMPLETION 📜

This certifies that the student has successfully completed
the Online Safety course with a score of ${examResults.value.score}%

🐱 Cat Points Earned: ${catPoints.value}
📅 Date: ${new Date().toLocaleDateString()}
🕐 Duration: ${examResults.value.duration || '60 minutes'}

Course Instructor: Whiskers the Cat 🐱
Session ID: ${examSession.value.id}
  `
  
  // Create download
  const blob = new Blob([certificate], { type: 'text/plain' })
  const url = window.URL.createObjectURL(blob)
  const a = document.createElement('a')
  a.href = url
  a.download = 'Online_Safety_Certificate.txt'
  a.click()
  window.URL.revokeObjectURL(url)
}

// Real-time timer updates
const startTimer = () => {
  timer.value = setInterval(async () => {
    if (examSession.value && !showResults.value) {
      try {
        const response = await getRemainingTime(examSession.value.id)
        timeRemaining.value = response.remainingTime
        
        if (timeRemaining.value <= 0) {
          clearInterval(timer.value)
          submitExam() // Auto-submit when time runs out
        }
      } catch (error) {
        console.error('Error getting remaining time:', error)
        // Fallback to local timer
        if (timeRemaining.value > 0) {
          timeRemaining.value--
        } else {
          clearInterval(timer.value)
          submitExam()
        }
      }
    }
  }, 1000)
}

// Load exam data from server
const loadExamData = async () => {
  if (!courseId.value) {
    alert('No course ID provided')
    router.push('/courses')
    return
  }
  
  isLoading.value = true
  
  try {
    // Start exam session
    const sessionResponse = await startExam(courseId.value)
    examSession.value = sessionResponse.session
    
    // Get questions
    const questionsResponse = await getQuestions(courseId.value)
    questions.value = questionsResponse.questions
    
    // Get initial time
    const timeResponse = await getRemainingTime(examSession.value.id)
    timeRemaining.value = timeResponse.remainingTime
    
    // Start timer
    startTimer()
    
  } catch (error) {
    console.error('Error loading exam data:', error)
    alert('Error loading exam. Please try again.')
    router.push('/courses')
  } finally {
    isLoading.value = false
  }
}

// Auto-save answers
const autoSaveAnswers = () => {
  if (currentQuestionType.value === 'short' && shortAnswers.value[currentQuestion.value - questions.value.filter(q => q.type === 'multiple').length]) {
    saveShortAnswer()
  } else if (currentQuestionType.value === 'scenario' && scenarioAnswers.value[currentQuestion.value - questions.value.filter(q => q.type === 'multiple').length - questions.value.filter(q => q.type === 'short').length]) {
    saveScenarioAnswer()
  }
}

// Auto-save on answer changes
watch(shortAnswers, autoSaveAnswers, { deep: true })
watch(scenarioAnswers, autoSaveAnswers, { deep: true })

onMounted(() => {
  loadExamData()
})

onUnmounted(() => {
  if (timer.value) {
    clearInterval(timer.value)
  }
  if (examSession.value) {
    endExam(examSession.value.id).catch(console.error)
  }
})
</script>

<style scoped>
.exam-container {
  max-width: 800px;
  margin: 0 auto;
  padding: 2rem;
  background: #f8fafc;
  min-height: 100vh;
}

.exam-header {
  background: white;
  border-radius: 12px;
  padding: 2rem;
  margin-bottom: 2rem;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.exam-info h1 {
  color: #1a365d;
  margin: 0 0 1rem 0;
  font-size: 2rem;
}

.exam-meta {
  display: flex;
  gap: 2rem;
  flex-wrap: wrap;
  font-size: 0.9rem;
}

.cat-points {
  background: #fef3c7;
  color: #d97706;
  padding: 0.5rem 1rem;
  border-radius: 20px;
  font-weight: bold;
}

.timer {
  background: #fee2e2;
  color: #dc2626;
  padding: 0.5rem 1rem;
  border-radius: 20px;
  font-weight: bold;
}

.progress {
  background: #dcfce7;
  color: #16a34a;
  padding: 0.5rem 1rem;
  border-radius: 20px;
  font-weight: bold;
}

.progress-bar-container {
  margin-top: 1rem;
  display: flex;
  align-items: center;
  gap: 1rem;
}

.progress-bar {
  flex: 1;
  height: 8px;
  background: #e2e8f0;
  border-radius: 4px;
  overflow: hidden;
}

.progress-fill {
  height: 100%;
  background: linear-gradient(90deg, #10b981, #059669);
  transition: width 0.3s ease;
}

.progress-text {
  font-weight: bold;
  color: #1a365d;
  min-width: 50px;
}

.exam-content {
  margin-bottom: 2rem;
}

.question-section {
  background: white;
  border-radius: 12px;
  padding: 2rem;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.question-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}

.question-number {
  background: #3b82f6;
  color: white;
  padding: 0.5rem 1rem;
  border-radius: 20px;
  font-weight: bold;
}

.question-points {
  background: #f59e0b;
  color: white;
  padding: 0.5rem 1rem;
  border-radius: 20px;
  font-weight: bold;
}

.question-text {
  color: #1a365d;
  margin-bottom: 2rem;
  font-size: 1.2rem;
  line-height: 1.6;
}

.options-grid {
  display: grid;
  gap: 1rem;
}

.option-card {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1rem;
  border: 2px solid #e2e8f0;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.3s ease;
}

.option-card:hover {
  border-color: #3b82f6;
  background: #f0f9ff;
}

.option-card.selected {
  border-color: #3b82f6;
  background: #dbeafe;
}

.option-card.correct {
  border-color: #10b981;
  background: #dcfce7;
}

.option-card.incorrect {
  border-color: #ef4444;
  background: #fee2e2;
}

.option-label {
  width: 30px;
  height: 30px;
  background: #3b82f6;
  color: white;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
}

.option-text {
  flex: 1;
  color: #374151;
}

.option-indicator {
  font-size: 1.2rem;
}

.answer-area {
  margin-top: 1rem;
}

.short-answer-input,
.scenario-answer-input {
  width: 100%;
  padding: 1rem;
  border: 2px solid #e2e8f0;
  border-radius: 8px;
  font-size: 1rem;
  font-family: inherit;
  resize: vertical;
}

.answer-feedback {
  margin-top: 1rem;
  padding: 1rem;
  background: #f8fafc;
  border-radius: 8px;
}

.correct-answer {
  margin-bottom: 1rem;
}

.correct-answer strong {
  color: #16a34a;
}

.points-earned {
  font-weight: bold;
  color: #1a365d;
}

.exam-navigation {
  display: flex;
  justify-content: space-between;
  gap: 1rem;
}

.nav-btn {
  padding: 1rem 2rem;
  border: none;
  border-radius: 8px;
  font-weight: bold;
  cursor: pointer;
  transition: all 0.3s ease;
}

.prev-btn {
  background: #6b7280;
  color: white;
}

.next-btn {
  background: #3b82f6;
  color: white;
}

.submit-btn {
  background: #10b981;
  color: white;
}

.submit-btn:disabled {
  background: #9ca3af;
  cursor: not-allowed;
}

.results-btn {
  background: #8b5cf6;
  color: white;
}

.nav-btn:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

/* Cat Animation */
.cat-animation {
  position: fixed;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  z-index: 1000;
}

.cat-celebration {
  text-align: center;
  animation: bounce 0.5s ease-in-out;
}

.cat-icon {
  font-size: 4rem;
  margin-bottom: 1rem;
}

.points-popup {
  background: #fef3c7;
  color: #d97706;
  padding: 1rem 2rem;
  border-radius: 20px;
  font-weight: bold;
  font-size: 1.2rem;
  animation: fadeIn 0.5s ease-in-out;
}

@keyframes bounce {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(-20px); }
}

@keyframes fadeIn {
  from { opacity: 0; transform: scale(0.8); }
  to { opacity: 1; transform: scale(1); }
}

/* Loading State */
.loading-state {
  text-align: center;
  padding: 4rem 2rem;
  background: white;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.loading-spinner {
  width: 50px;
  height: 50px;
  border: 4px solid #e2e8f0;
  border-top: 4px solid #3b82f6;
  border-radius: 50%;
  margin: 0 auto 1rem auto;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.loading-state p {
  color: #6c757d;
  font-size: 1.1rem;
  margin: 0;
}

/* Results Modal */
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
}

.results-modal {
  max-width: 600px;
  width: 90%;
  max-height: 80vh;
  overflow-y: auto;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
}

.modal-header h2 {
  color: #1a365d;
  margin: 0;
  font-size: 2rem;
}

.close-btn {
  background: none;
  border: none;
  font-size: 1.5rem;
  cursor: pointer;
  color: #6c757d;
}

.results-content {
  text-align: center;
}

.score-display {
  margin-bottom: 2rem;
}

.score-circle {
  width: 150px;
  height: 150px;
  margin: 0 auto;
  border-radius: 50%;
  background: linear-gradient(135deg, #10b981, #059669);
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  color: white;
  font-weight: bold;
}

.score-number {
  font-size: 3rem;
  line-height: 1;
}

.score-label {
  font-size: 1.2rem;
}

.score-breakdown {
  background: #f8fafc;
  padding: 1.5rem;
  border-radius: 8px;
  margin-bottom: 2rem;
  text-align: left;
}

.score-breakdown h3 {
  color: #1a365d;
  margin: 0 0 1rem 0;
}

.score-item {
  display: flex;
  justify-content: space-between;
  padding: 0.5rem 0;
  border-bottom: 1px solid #e2e8f0;
}

.score-item:last-child {
  border-bottom: none;
}

.score-item.total {
  font-weight: bold;
  color: #1a365d;
  padding-top: 0.5rem;
  border-top: 2px solid #3b82f6;
}

.cat-points-earned {
  background: #fef3c7;
  padding: 1.5rem;
  border-radius: 8px;
  margin-bottom: 2rem;
}

.cat-points-earned h3 {
  color: #d97706;
  margin: 0 0 1rem 0;
}

.cat-points-display {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 1rem;
}

.points-number {
  font-size: 2rem;
  font-weight: bold;
  color: #d97706;
}

.points-label {
  color: #92400e;
}

.course-completion {
  background: #dcfce7;
  padding: 1.5rem;
  border-radius: 8px;
  margin-bottom: 2rem;
}

.course-completion h3 {
  color: #16a34a;
  margin: 0 0 1rem 0;
}

.completion-badge {
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: white;
  padding: 1rem;
  border-radius: 8px;
}

.completion-text {
  font-weight: bold;
  color: #1a365d;
}

.completion-percent {
  background: #10b981;
  color: white;
  padding: 0.5rem 1rem;
  border-radius: 20px;
  font-weight: bold;
}

.results-actions {
  display: flex;
  gap: 1rem;
  justify-content: center;
}

.action-btn {
  padding: 1rem 2rem;
  border: none;
  border-radius: 8px;
  font-weight: bold;
  cursor: pointer;
  transition: all 0.3s ease;
}

.action-btn:first-child {
  background: #10b981;
  color: white;
}

.action-btn:last-child {
  background: #3b82f6;
  color: white;
}

.action-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.scenario-content {
  margin-top: 1rem;
}

.scenario-title {
  color: #1a365d;
  margin-bottom: 1rem;
  font-size: 1.1rem;
}

.scenario-text {
  color: #374151;
  margin-bottom: 1rem;
  line-height: 1.6;
}

@media (max-width: 768px) {
  .exam-container {
    padding: 1rem;
  }
  
  .exam-header {
    padding: 1.5rem;
  }
  
  .exam-meta {
    flex-direction: column;
    gap: 1rem;
  }
  
  .question-section {
    padding: 1.5rem;
  }
  
  .exam-navigation {
    flex-direction: column;
  }
  
  .nav-btn {
    width: 100%;
  }
  
  .results-modal {
    padding: 1rem;
    margin: 1rem;
  }
}
</style>
