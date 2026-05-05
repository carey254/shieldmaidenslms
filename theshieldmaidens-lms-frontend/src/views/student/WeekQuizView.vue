<template>
  <div class="quiz-container">
    <div class="quiz-header">
      <h1>📝 Week 1 Quiz</h1>
      <p>Introduction to Coding - Test Your Knowledge</p>
    </div>

    <div class="quiz-progress">
      <div class="progress-bar">
        <div class="progress-fill" :style="{ width: progressPercentage + '%' }"></div>
      </div>
      <p>Question {{ currentQuestion + 1 }} of {{ totalQuestions }}</p>
    </div>

    <div v-if="!quizCompleted" class="quiz-content">
      <!-- Section A: Multiple Choice -->
      <div v-if="currentSection === 'multiple-choice'" class="quiz-section">
        <h3>Section A: Multiple Choice</h3>
        <div class="question-card">
          <h4>{{ multipleChoiceQuestions[currentQuestion].question }}</h4>
          <div class="options">
            <label 
              v-for="(option, index) in multipleChoiceQuestions[currentQuestion].options" 
              :key="index"
              class="option-label"
            >
              <input 
                type="radio" 
                :name="'mcq-' + currentQuestion" 
                :value="option.value"
                v-model="answers['mcq-' + currentQuestion]"
                class="option-input"
              >
              <span class="option-text">{{ option.text }}</span>
              <span class="option-indicator"></span>
            </label>
          </div>
        </div>
      </div>

      <!-- Section B: Short Answer -->
      <div v-if="currentSection === 'short-answer'" class="quiz-section">
        <h3>Section B: Short Answer</h3>
        <div class="question-card">
          <h4>{{ shortAnswerQuestions[currentQuestion].question }}</h4>
          <textarea 
            v-model="answers['sa-' + currentQuestion]"
            class="answer-textarea"
            placeholder="Type your answer here..."
            rows="4"
          ></textarea>
        </div>
      </div>

      <!-- Section C: True/False -->
      <div v-if="currentSection === 'true-false'" class="quiz-section">
        <h3>Section C: True/False</h3>
        <div class="question-card">
          <h4>{{ trueFalseQuestions[currentQuestion].statement }}</h4>
          <div class="tf-options">
            <label class="tf-option">
              <input 
                type="radio" 
                :name="'tf-' + currentQuestion" 
                value="true"
                v-model="answers['tf-' + currentQuestion]"
                class="tf-input"
              >
              <span class="tf-text">True</span>
              <span class="tf-indicator"></span>
            </label>
            <label class="tf-option">
              <input 
                type="radio" 
                :name="'tf-' + currentQuestion" 
                value="false"
                v-model="answers['tf-' + currentQuestion]"
                class="tf-input"
              >
              <span class="tf-text">False</span>
              <span class="tf-indicator"></span>
            </label>
          </div>
        </div>
      </div>

      <div class="quiz-navigation">
        <button 
          v-if="currentQuestion > 0 || currentSectionIndex > 0" 
          @click="previousQuestion" 
          class="nav-btn prev-btn"
        >
          ← Previous
        </button>
        
        <button 
          v-if="!isLastQuestion" 
          @click="nextQuestion" 
          class="nav-btn next-btn"
          :disabled="!isCurrentQuestionAnswered"
        >
          Next →
        </button>
        
        <button 
          v-if="isLastQuestion" 
          @click="submitQuiz" 
          class="nav-btn submit-btn"
          :disabled="!isCurrentQuestionAnswered"
        >
          Submit Quiz 📝
        </button>
      </div>
    </div>

    <!-- Quiz Results -->
    <div v-if="quizCompleted" class="quiz-results">
      <div class="results-header">
        <h2>🎉 Quiz Completed!</h2>
        <p>Your score: {{ score }}%</p>
      </div>
      
      <div class="score-display">
        <div class="score-circle">
          <div class="score-value">{{ score }}%</div>
        </div>
        <div class="score-message">
          <p v-if="score >= 70">🌟 Excellent work! You've mastered Week 1!</p>
          <p v-else-if="score >= 50">👍 Good effort! Review the material and try again.</p>
          <p v-else>📚 Keep studying! You'll get better with practice.</p>
        </div>
      </div>

      <div class="results-details">
        <h3>Quiz Summary</h3>
        <div class="summary-grid">
          <div class="summary-item">
            <span class="summary-label">Multiple Choice:</span>
            <span class="summary-value">{{ sectionScores.multipleChoice }}%</span>
          </div>
          <div class="summary-item">
            <span class="summary-label">Short Answer:</span>
            <span class="summary-value">{{ sectionScores.shortAnswer }}%</span>
          </div>
          <div class="summary-item">
            <span class="summary-label">True/False:</span>
            <span class="summary-value">{{ sectionScores.trueFalse }}%</span>
          </div>
        </div>
      </div>

      <div class="results-actions">
        <button 
          v-if="score >= 70"
          @click="reviewAnswers" 
          class="action-btn review-btn"
        >
          Review Answers
        </button>
        <button 
          @click="retakeQuiz" 
          class="action-btn retake-btn"
        >
          Retake Quiz
        </button>
        <button 
          v-if="score >= 70"
          @click="completeWeek" 
          class="action-btn complete-btn"
        >
          Complete Week 1
        </button>
      </div>
    </div>

    <!-- Review Answers Section -->
    <div v-if="showReview" class="quiz-review">
      <div class="quiz-header">
        <h1>Quiz Review</h1>
        <p>Review your answers and see the correct responses</p>
      </div>

      <div class="quiz-content">
        <!-- Section A: Multiple Choice Review -->
        <div class="quiz-section">
          <h3>Section A: Multiple Choice</h3>
          <div v-for="(question, index) in multipleChoiceQuestions" :key="index" class="question-card">
            <h4 :data-question="'Question ' + (index + 1)">{{ question.question }}</h4>
            <div class="options">
              <label 
                v-for="(option, optIndex) in question.options" 
                :key="optIndex"
                class="option-label"
                :class="{
                  'correct-answer': option.value === question.correct,
                  'user-answer': answers['mcq-' + index] === option.value,
                  'incorrect-answer': answers['mcq-' + index] === option.value && option.value !== question.correct
                }"
              >
                <span class="option-text">{{ option.text }}</span>
                <span v-if="option.value === question.correct" class="answer-indicator correct">✓</span>
                <span v-else-if="answers['mcq-' + index] === option.value" class="answer-indicator incorrect">✗</span>
              </label>
            </div>
          </div>
        </div>

        <!-- Section B: Short Answer Review -->
        <div class="quiz-section">
          <h3>Section B: Short Answer</h3>
          <div v-for="(question, index) in shortAnswerQuestions" :key="index" class="question-card">
            <h4 :data-question="'Question ' + (index + 1)">{{ question.question }}</h4>
            <div class="answer-review">
              <div class="user-answer">
                <strong>Your Answer:</strong> {{ answers['sa-' + index] || 'Not answered' }}
              </div>
              <div class="correct-answer">
                <strong>Key Points:</strong> {{ question.keyPoints.join(', ') }}
              </div>
            </div>
          </div>
        </div>

        <!-- Section C: True/False Review -->
        <div class="quiz-section">
          <h3>Section C: True/False</h3>
          <div v-for="(question, index) in trueFalseQuestions" :key="index" class="question-card">
            <h4 :data-question="'Question ' + (index + 1)">{{ question.statement }}</h4>
            <div class="tf-options">
              <label class="tf-option" :class="{
                'correct-answer': question.correct === true,
                'user-answer': answers['tf-' + index] === 'true',
                'incorrect-answer': answers['tf-' + index] === 'true' && question.correct !== true
              }">
                <span class="tf-text">True</span>
                <span v-if="question.correct === true" class="answer-indicator correct">✓</span>
                <span v-else-if="answers['tf-' + index] === 'true'" class="answer-indicator incorrect">✗</span>
              </label>
              <label class="tf-option" :class="{
                'correct-answer': question.correct === false,
                'user-answer': answers['tf-' + index] === 'false',
                'incorrect-answer': answers['tf-' + index] === 'false' && question.correct !== false
              }">
                <span class="tf-text">False</span>
                <span v-if="question.correct === false" class="answer-indicator correct">✓</span>
                <span v-else-if="answers['tf-' + index] === 'false'" class="answer-indicator incorrect">✗</span>
              </label>
            </div>
          </div>
        </div>
      </div>

      <div class="quiz-navigation">
        <button @click="showResults = true; showReview = false" class="nav-btn prev-btn">
          Back to Results
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()

// State
const currentSection = ref('multiple-choice')
const currentSectionIndex = ref(0)
const currentQuestion = ref(0)
const answers = ref({})
const quizCompleted = ref(false)
const showResults = ref(false)
const score = ref(0)
const showReview = ref(false)
const sectionScores = ref({
  multipleChoice: 0,
  shortAnswer: 0,
  trueFalse: 0
})

// Quiz Data
const sections = ['multiple-choice', 'short-answer', 'true-false']

const multipleChoiceQuestions = [
  {
    question: "Coding is defined as:",
    options: [
      { text: "The process of writing instructions that a computer can follow", value: "a" },
      { text: "The process of building hardware", value: "b" },
      { text: "The process of designing websites only", value: "c" },
      { text: "The process of fixing computers", value: "d" }
    ],
    correct: "a"
  },
  {
    question: "Which of the following is a programming language?",
    options: [
      { text: "Microsoft Word", value: "a" },
      { text: "Scratch", value: "b" },
      { text: "Google Chrome", value: "c" },
      { text: "Adobe Photoshop", value: "d" }
    ],
    correct: "b"
  },
  {
    question: "An algorithm is:",
    options: [
      { text: "A type of computer hardware", value: "a" },
      { text: "A programming language", value: "b" },
      { text: "A finite sequence of well-defined instructions", value: "c" },
      { text: "A type of computer virus", value: "d" }
    ],
    correct: "c"
  }
]

const shortAnswerQuestions = [
  {
    question: "Explain computational thinking",
    keywords: ["systematic", "approach", "problem", "solving", "breaking", "complex", "logical"],
    sampleAnswer: "Computational thinking is a systematic approach to problem-solving that involves breaking down complex problems into manageable parts and solving them logically."
  },
  {
    question: "Give an example of an algorithm",
    keywords: ["steps", "sequence", "instructions", "order", "process"],
    sampleAnswer: "Example: Making tea - 1. Boil water, 2. Add tea leaves, 3. Add sugar, 4. Pour into cup"
  },
  {
    question: "State one importance of coding",
    keywords: ["logical", "thinking", "problem", "solving", "creativity", "technology", "careers"],
    sampleAnswer: "Coding enhances logical and critical thinking skills"
  }
]

const trueFalseQuestions = [
  {
    statement: "Computers can think independently",
    correct: false
  },
  {
    statement: "Sequence is important in programming",
    correct: true
  },
  {
    statement: "Scratch is a beginner programming tool",
    correct: true
  }
]

// Computed properties
const totalQuestions = computed(() => {
  return multipleChoiceQuestions.length + shortAnswerQuestions.length + trueFalseQuestions.length
})

const progressPercentage = computed(() => {
  const answeredQuestions = Object.keys(answers.value).length
  return Math.round((answeredQuestions / totalQuestions.value) * 100)
})

const isLastQuestion = computed(() => {
  return currentSection.value === 'true-false' && currentQuestion.value === trueFalseQuestions.length - 1
})

const isCurrentQuestionAnswered = computed(() => {
  const key = `${getSectionPrefix()}-${currentQuestion.value}`
  return answers.value[key] !== undefined && answers.value[key] !== ''
})

// Functions
const getSectionPrefix = () => {
  switch (currentSection.value) {
    case 'multiple-choice': return 'mcq'
    case 'short-answer': return 'sa'
    case 'true-false': return 'tf'
    default: return ''
  }
}

const nextQuestion = () => {
  const currentQuestions = getCurrentQuestions()
  if (currentQuestion.value < currentQuestions.length - 1) {
    currentQuestion.value++
  } else {
    // Move to next section
    if (currentSectionIndex.value < sections.length - 1) {
      currentSectionIndex.value++
      currentSection.value = sections[currentSectionIndex.value]
      currentQuestion.value = 0
    }
  }
}

const previousQuestion = () => {
  if (currentQuestion.value > 0) {
    currentQuestion.value--
  } else {
    // Move to previous section
    if (currentSectionIndex.value > 0) {
      currentSectionIndex.value--
      currentSection.value = sections[currentSectionIndex.value]
      currentQuestion.value = getCurrentQuestions().length - 1
    }
  }
}

const getCurrentQuestions = () => {
  switch (currentSection.value) {
    case 'multiple-choice': return multipleChoiceQuestions
    case 'short-answer': return shortAnswerQuestions
    case 'true-false': return trueFalseQuestions
    default: return []
  }
}

const submitQuiz = () => {
  calculateScore()
  quizCompleted.value = true
}

const calculateScore = () => {
  let totalScore = 0
  let maxScore = totalQuestions.value

  // Calculate multiple choice score
  let mcqScore = 0
  multipleChoiceQuestions.forEach((question, index) => {
    const answer = answers.value[`mcq-${index}`]
    if (answer === question.correct) {
      mcqScore++
      totalScore++
    }
  })
  sectionScores.value.multipleChoice = Math.round((mcqScore / multipleChoiceQuestions.length) * 100)

  // Calculate short answer score (simplified - checking for keywords)
  let saScore = 0
  shortAnswerQuestions.forEach((question, index) => {
    const answer = answers.value[`sa-${index}`] || ''
    const hasKeywords = question.keywords.some(keyword => 
      answer.toLowerCase().includes(keyword.toLowerCase())
    )
    if (hasKeywords && answer.length > 10) {
      saScore++
      totalScore++
    }
  })
  sectionScores.value.shortAnswer = Math.round((saScore / shortAnswerQuestions.length) * 100)

  // Calculate true/false score
  let tfScore = 0
  trueFalseQuestions.forEach((question, index) => {
    const answer = answers.value[`tf-${index}`]
    if (answer === String(question.correct)) {
      tfScore++
      totalScore++
    }
  })
  sectionScores.value.trueFalse = Math.round((tfScore / trueFalseQuestions.length) * 100)

  score.value = Math.round((totalScore / maxScore) * 100)
}

const reviewAnswers = () => {
  // Show detailed review of answers
}

const retakeQuiz = () => {
  currentSection.value = 'multiple-choice'
  currentSectionIndex.value = 0
  currentQuestion.value = 0
  answers.value = {}
  showResults.value = false
  showReview.value = false
  score.value = 0
}

const completeWeek = () => {
  // Send message to parent window to mark week as complete
  if (window.opener) {
    window.opener.postMessage({
      type: 'weekCompleted',
      week: 1,
      score: score.value
    }, '*')
  }
  
  // Close the quiz window
  window.close()
}

onMounted(() => {
  console.log('Week 1 Quiz loaded')
})
</script>

<style scoped>
.quiz-container {
  max-width: 900px;
  margin: 0 auto;
  padding: 20px;
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
  background: #f8f9fa;
  min-height: 100vh;
}

.quiz-header {
  text-align: center;
  margin-bottom: 30px;
  background: white;
  padding: 40px;
  border-radius: 20px;
  box-shadow: 0 20px 40px rgba(0,0,0,0.1);
  position: relative;
  overflow: hidden;
}

.quiz-header h1 {
  color: #2d3748;
  margin: 0 0 10px 0;
  font-size: 36px;
  font-weight: 800;
}

.quiz-header p {
  color: #718096;
  margin: 0;
  font-size: 18px;
  font-weight: 500;
}

.quiz-progress {
  background: white;
  padding: 25px;
  border-radius: 16px;
  margin-bottom: 25px;
  box-shadow: 0 10px 30px rgba(0,0,0,0.1);
}

.progress-bar {
  width: 100%;
  height: 12px;
  background: #e2e8f0;
  border-radius: 100px;
  overflow: hidden;
  margin-bottom: 15px;
}

.progress-fill {
  height: 100%;
  background: #ff6b35;
  border-radius: 100px;
  transition: width 0.5s cubic-bezier(0.4, 0, 0.2, 1);
}

.quiz-progress p {
  margin: 0;
  color: #4a5568;
  font-weight: 600;
  font-size: 16px;
}

.quiz-content {
  background: white;
  padding: 40px;
  border-radius: 20px;
  box-shadow: 0 20px 40px rgba(0,0,0,0.1);
  position: relative;
}

.quiz-section {
  margin-bottom: 30px;
}

.quiz-section h3 {
  color: #2d3748;
  margin: 0 0 25px 0;
  font-size: 28px;
  font-weight: 700;
}

.question-card {
  background: #f7fafc;
  padding: 35px;
  border-radius: 16px;
  border: 2px solid #e2e8f0;
  margin-bottom: 30px;
  position: relative;
  transition: all 0.3s ease;
}

.question-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 15px 35px rgba(0,0,0,0.1);
}

.question-card::before {
  content: attr(data-question);
  position: absolute;
  top: -15px;
  left: 35px;
  background: #ff6b35;
  color: white;
  padding: 8px 20px;
  border-radius: 25px;
  font-weight: 600;
  font-size: 14px;
}

.question-card h4 {
  color: #2d3748;
  margin: 0 0 25px 0;
  font-size: 20px;
  font-weight: 600;
  line-height: 1.6;
  padding-top: 10px;
}

.options {
  display: flex;
  flex-direction: column;
  gap: 15px;
}

.option-label {
  display: flex;
  align-items: center;
  padding: 20px;
  background: white;
  border: 2px solid #e2e8f0;
  border-radius: 12px;
  cursor: pointer;
  transition: all 0.3s ease;
  position: relative;
}

.option-label:hover {
  border-color: #ff6b35;
  transform: translateX(5px);
  box-shadow: 0 8px 25px rgba(255, 107, 53, 0.15);
}

.option-label input:checked + .option-text {
  color: #ff6b35;
  font-weight: 700;
}

.option-label input:checked ~ .option-indicator {
  background: #ff6b35;
  border-color: #ff6b35;
}

.option-label input:checked ~ .option-indicator::after {
  transform: scale(1);
}

.option-input {
  position: absolute;
  opacity: 0;
  cursor: pointer;
}

.option-text {
  flex: 1;
  color: #4a5568;
  font-weight: 500;
  font-size: 16px;
  position: relative;
  z-index: 1;
}

.option-indicator {
  width: 24px;
  height: 24px;
  border: 3px solid #cbd5e0;
  border-radius: 50%;
  margin-left: 15px;
  position: relative;
  z-index: 1;
  transition: all 0.3s ease;
}

.option-indicator::after {
  content: '';
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%) scale(0);
  width: 8px;
  height: 8px;
  background: white;
  border-radius: 50%;
  transition: transform 0.3s ease;
}

.answer-textarea {
  width: 100%;
  padding: 20px;
  border: 2px solid #e2e8f0;
  border-radius: 12px;
  font-size: 16px;
  font-family: inherit;
  resize: vertical;
  transition: all 0.3s ease;
  background: white;
}

.answer-textarea:focus {
  outline: none;
  border-color: #667eea;
  box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
}

.answer-textarea::placeholder {
  color: #a0aec0;
  font-style: italic;
}

.tf-options {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 20px;
}

.tf-option {
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 25px;
  background: white;
  border: 2px solid #e2e8f0;
  border-radius: 12px;
  cursor: pointer;
  transition: all 0.3s ease;
  position: relative;
}

.tf-option:hover {
  border-color: #ff6b35;
  transform: translateY(-3px);
  box-shadow: 0 12px 30px rgba(255, 107, 53, 0.2);
}

.tf-option input:checked + .tf-text {
  color: #ff6b35;
  font-weight: 700;
}

.tf-option input:checked ~ .tf-indicator {
  background: #ff6b35;
  border-color: #ff6b35;
}

.tf-option input:checked ~ .tf-indicator::after {
  transform: scale(1);
}

.tf-input {
  position: absolute;
  opacity: 0;
  cursor: pointer;
}

.tf-text {
  font-size: 18px;
  font-weight: 600;
  color: #4a5568;
  position: relative;
  z-index: 1;
}

.tf-indicator {
  width: 24px;
  height: 24px;
  border: 3px solid #cbd5e0;
  border-radius: 50%;
  margin-left: 15px;
  position: relative;
  z-index: 1;
  transition: all 0.3s ease;
}

.tf-indicator::after {
  content: '';
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%) scale(0);
  width: 8px;
  height: 8px;
  background: white;
  border-radius: 50%;
  transition: transform 0.3s ease;
}

.quiz-navigation {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 20px;
  margin-top: 40px;
  padding-top: 30px;
  border-top: 2px solid #e2e8f0;
}

.nav-btn {
  padding: 15px 35px;
  border: none;
  border-radius: 12px;
  font-size: 16px;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.3s ease;
  position: relative;
  overflow: hidden;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.nav-btn::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: linear-gradient(135deg, #667eea, #764ba2);
  opacity: 0;
  transition: opacity 0.3s ease;
}

.nav-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
  transform: none;
}

.prev-btn {
  background: #f7fafc;
  color: #4a5568;
  border: 2px solid #e2e8f0;
}

.prev-btn:hover:not(:disabled) {
  background: #edf2f7;
  transform: translateX(-5px);
  box-shadow: 0 8px 25px rgba(0,0,0,0.1);
}

.next-btn, .submit-btn {
  background: #ff6b35;
  color: white;
  box-shadow: 0 10px 30px rgba(255, 107, 53, 0.3);
}

.next-btn:hover:not(:disabled), .submit-btn:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 15px 40px rgba(255, 107, 53, 0.4);
}

.submit-btn {
  background: #4caf50;
  box-shadow: 0 10px 30px rgba(76, 175, 80, 0.3);
}

.submit-btn:hover:not(:disabled) {
  box-shadow: 0 15px 40px rgba(76, 175, 80, 0.4);
}

.quiz-results {
  background: white;
  padding: 50px;
  border-radius: 20px;
  box-shadow: 0 20px 40px rgba(0,0,0,0.1);
  text-align: center;
}

.results-header h2 {
  color: #2d3748;
  margin: 0 0 15px 0;
  font-size: 36px;
  font-weight: 800;
}

.results-header p {
  color: #718096;
  margin: 0;
  font-size: 20px;
  font-weight: 600;
}

.score-display {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 40px;
  margin: 40px 0;
}

.score-circle {
  width: 150px;
  height: 150px;
  border-radius: 50%;
  background: #4caf50;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 32px;
  font-weight: 800;
  box-shadow: 0 15px 35px rgba(76, 175, 80, 0.3);
}

.score-message p {
  font-size: 20px;
  color: #4a5568;
  margin: 0;
  font-weight: 600;
  max-width: 300px;
  line-height: 1.6;
}

.results-details {
  background: #f7fafc;
  padding: 35px;
  border-radius: 16px;
  margin: 40px 0;
  border: 2px solid #e2e8f0;
}

.results-details h3 {
  color: #2d3748;
  margin: 0 0 25px 0;
  font-size: 24px;
  font-weight: 700;
}

.summary-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 20px;
}

.summary-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px;
  background: white;
  border-radius: 12px;
  border: 2px solid #e2e8f0;
  transition: all 0.3s ease;
}

.summary-item:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(0,0,0,0.1);
}

.summary-label {
  color: #4a5568;
  font-weight: 600;
  font-size: 16px;
}

.summary-value {
  color: #667eea;
  font-weight: 800;
  font-size: 18px;
}

.results-actions {
  display: flex;
  justify-content: center;
  gap: 20px;
  flex-wrap: wrap;
}

.action-btn {
  padding: 15px 30px;
  border: none;
  border-radius: 12px;
  font-size: 16px;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.3s ease;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  position: relative;
  overflow: hidden;
}

.action-btn::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  opacity: 0;
  transition: opacity 0.3s ease;
}

.review-btn {
  background: #ff6b35;
  color: white;
  box-shadow: 0 10px 30px rgba(255, 107, 53, 0.3);
}

.review-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 15px 40px rgba(255, 107, 53, 0.4);
}

.retake-btn {
  background: #ff9800;
  color: white;
  box-shadow: 0 10px 30px rgba(255, 152, 0, 0.3);
}

.retake-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 15px 40px rgba(255, 152, 0, 0.4);
}

.complete-btn {
  background: #4caf50;
  color: white;
  box-shadow: 0 10px 30px rgba(76, 175, 80, 0.3);
}

.complete-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 15px 40px rgba(76, 175, 80, 0.4);
}

/* Review Section Styles */
.quiz-review {
  max-width: 900px;
  margin: 0 auto;
  padding: 20px;
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
  background: #f8f9fa;
  min-height: 100vh;
}

.answer-review {
  display: flex;
  flex-direction: column;
  gap: 15px;
  margin-top: 20px;
}

.user-answer,
.correct-answer {
  padding: 15px;
  border-radius: 8px;
  font-size: 16px;
  line-height: 1.6;
}

.user-answer {
  background: #fff3cd;
  border: 1px solid #ffeaa7;
  color: #856404;
}

.correct-answer {
  background: #d4edda;
  border: 1px solid #c3e6cb;
  color: #155724;
}

.answer-indicator {
  margin-left: 10px;
  font-weight: bold;
  font-size: 18px;
}

.answer-indicator.correct {
  color: #4caf50;
}

.answer-indicator.incorrect {
  color: #f44336;
}

.correct-answer {
  background: #e8f5e8;
  border-color: #4caf50;
}

.incorrect-answer {
  background: #ffebee;
  border-color: #f44336;
}

.option-label.correct-answer,
.option-label.incorrect-answer {
  border-width: 2px;
}

.tf-option.correct-answer,
.tf-option.incorrect-answer {
  border-width: 2px;
}

@media (max-width: 768px) {
  .quiz-container {
    padding: 15px;
  }
  
  .quiz-header {
    padding: 30px 20px;
  }
  
  .quiz-header h1 {
    font-size: 28px;
  }
  
  .quiz-content {
    padding: 25px;
  }
  
  .quiz-section h3 {
    font-size: 24px;
  }
  
  .question-card {
    padding: 25px;
  }
  
  .question-card h4 {
    font-size: 18px;
  }
  
  .tf-options {
    grid-template-columns: 1fr;
  }
  
  .quiz-navigation {
    flex-direction: column;
    gap: 15px;
  }
  
  .nav-btn {
    width: 100%;
  }
  
  .score-display {
    flex-direction: column;
    gap: 30px;
  }
  
  .score-circle {
    width: 120px;
    height: 120px;
    font-size: 24px;
  }
  
  .results-actions {
    flex-direction: column;
  }
  
  .action-btn {
    width: 100%;
  }
}
</style>
