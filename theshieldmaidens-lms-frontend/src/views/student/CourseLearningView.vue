<template>
  <div class="course-learning">
    <!-- Course Header -->
    <div class="course-header">
      <button @click="goBack" class="back-btn">
        ← Back to Dashboard
      </button>
      <h1>{{ courseTitle }}</h1>
    </div>

    <div v-if="showEnrolledFlash" class="enroll-flash" role="status">
      Successfully enrolled. Continue with your lessons below.
    </div>

    <!-- Learning Progress -->
    <div class="progress-section">
      <h2>Your Learning Progress</h2>
      <div class="progress-overview">
        <div class="progress-item">
          <div class="progress-icon">📊</div>
          <div class="progress-info">
            <div class="progress-percentage">{{ overallProgress }}% Complete</div>
            <div class="progress-bar">
              <div class="progress-fill" :style="{ width: overallProgress + '%' }"></div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Course Content Grid -->
    <div class="content-grid">
      <!-- Left Column - Stats -->
      <div class="stats-column">
        <div class="stat-card">
          <h3>Books</h3>
          <div class="stat-value">{{ booksCompleted }}</div>
        </div>
        
        <div class="stat-card">
          <h3>Weeks Completed</h3>
          <div class="stat-value">{{ weeksCompleted }}</div>
        </div>
        
        <div class="stat-card">
          <h3>Tests</h3>
          <div class="stat-value">{{ testsCompleted }}</div>
        </div>
        
        <div class="stat-card">
          <h3>CATs Completed</h3>
          <div class="stat-value">{{ catsCompleted }}</div>
        </div>
        
        <div class="stat-card">
          <h3>Average Score</h3>
          <div class="stat-value">{{ averageScore }}%</div>
        </div>
      </div>

      <!-- Middle Column - Weekly Curriculum -->
      <div class="curriculum-column">
        <h2>Weekly Curriculum</h2>
        <div class="weeks-list">
          <div 
            v-for="(week, index) in weeks" 
            :key="index"
            :class="['week-item', { locked: week.locked, completed: week.completed, 'in-progress': week.inProgress }]"
            @click="accessWeek(week, index)"
          >
            <div class="week-header">
              <div class="week-icon">
                <span v-if="week.completed">✓</span>
                <span v-else-if="week.locked">🔒</span>
                <span v-else-if="week.inProgress">📖</span>
                <span v-else>📚</span>
              </div>
              <div class="week-info">
                <h4>Week {{ index + 1 }}: {{ week.title }}</h4>
                <p>{{ week.description }}</p>
              </div>
            </div>
            <div class="week-status">
              <span v-if="week.completed" class="status completed">Completed</span>
              <span v-else-if="week.inProgress" class="status in-progress">In Progress</span>
              <span v-else-if="week.locked" class="status locked">Locked</span>
              <span v-else class="status not-started">Not Started</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Right Column - Instructors & Final Exam -->
      <div class="info-column">
        <!-- Course Instructors -->
        <div class="instructors-section">
          <h3>Course Instructors</h3>
          <div class="instructors-list">
            <div class="instructor-card">
              <div class="instructor-avatar">
                <div class="avatar-placeholder">FJ</div>
              </div>
              <div class="instructor-info">
                <h4>Faith Jeptoo</h4>
                <p class="instructor-title">Lead Instructor - Digital Safety Expert</p>
                
              </div>
            </div>
            
            <div class="instructor-card">
              
              <div class="instructor-info">
                
              </div>
            </div>
          </div>
        </div>

        <!-- Final Examination -->
        <div class="exam-section">
          <h3>Final Examination</h3>
          <div class="exam-card">
            <div class="exam-info">
              <div class="exam-detail">
                <span class="exam-label">Duration:</span>
                <span class="exam-value">2 hours</span>
              </div>
              <div class="exam-detail">
                <span class="exam-label">Questions:</span>
                <span class="exam-value">50 (Multiple choice, short answer, scenarios)</span>
              </div>
              <div class="exam-detail">
                <span class="exam-label">Passing Score:</span>
                <span class="exam-value">70%</span>
              </div>
            </div>
            <div class="exam-description">
              <p>Covers all 6 weeks of course material</p>
            </div>
            <div class="exam-status">
              <div v-if="!canTakeExam" class="exam-locked">
                <div class="lock-icon">🔒</div>
                <p>Complete all weeks first</p>
              </div>
              <div v-else class="exam-unlocked">
                <div class="unlock-icon">📝</div>
                <button @click="startExam" class="start-exam-btn">Start Exam</button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { API_BASE_URL } from '@/config/api'

const router = useRouter()
const route = useRoute()
const showEnrolledFlash = ref(false)

// State
const courseTitle = ref('Coding for Kids and Youth')
const overallProgress = ref(0)
const booksCompleted = ref(0)
const weeksCompleted = ref(0)
const testsCompleted = ref(0)
const catsCompleted = ref(0)
const averageScore = ref(0)
const canTakeExam = ref(false)
const showQuizModal = ref(false)
const currentWeekIndex = ref(0)
const quizAnswers = ref({})
const quizScore = ref(0)

// Weekly curriculum data
const weeks = ref([
  {
    title: 'Intro to Coding',
    description: 'Introduction to the world of programming and computational thinking...',
    locked: false,
    completed: false,
    inProgress: false
  },
  {
    title: 'Programming Basics',
    description: 'Fundamental programming concepts and logic building blocks...',
    locked: true,
    completed: false,
    inProgress: false
  },
  {
    title: 'Scratch',
    description: 'Learning programming with visual blocks using Scratch...',
    locked: true,
    completed: false,
    inProgress: false
  },
  {
    title: 'HTML & CSS',
    description: 'Building web pages with HTML and styling with CSS...',
    locked: true,
    completed: false,
    inProgress: false
  },
  {
    title: 'Simple Projects',
    description: 'Creating small programming projects to practice skills...',
    locked: true,
    completed: false,
    inProgress: false
  },
  {
    title: 'Problem Solving',
    description: 'Developing computational thinking and problem-solving skills...',
    locked: true,
    completed: false,
    inProgress: false
  },
  {
    title: 'Real-world Applications',
    description: 'Understanding how coding applies to real-world scenarios...',
    locked: true,
    completed: false,
    inProgress: false
  }
])

// Functions
const goBack = () => {
  router.push('/dashboard')
}

onMounted(async () => {
  const courseId = Number(route.params.id)
  const token = localStorage.getItem('token')

  if (!courseId || Number.isNaN(courseId)) {
    router.replace('/courses')
    return
  }

  if (!token) {
    router.replace('/login')
    return
  }

  const justEnrolled = route.query.enrolled === '1'
  if (justEnrolled) {
    showEnrolledFlash.value = true
    router.replace({ path: route.path, query: {} })
  }

  try {
    const res = await fetch(`${API_BASE_URL}/student/courses/${courseId}`, {
      headers: {
        Accept: 'application/json',
        Authorization: `Bearer ${token}`,
      },
    })

    if (res.status === 401) {
      router.replace('/login')
      return
    }

    if (!res.ok) {
      router.replace('/courses')
      return
    }

    const data = (await res.json()) as { title?: string; isEnrolled?: boolean }
    if (data.title) courseTitle.value = data.title

    if (!data.isEnrolled) {
      router.replace({ path: '/courses', query: { notice: 'enroll_first' } })
    }
  } catch {
    router.replace('/courses')
  }
})

const accessWeek = (week, index) => {
  if (week.locked) {
    alert('Please complete the previous week to unlock this one.')
    return
  }
  
  // Update week status
  weeks.value.forEach((w, i) => {
    if (i < index) {
      w.completed = true
      w.inProgress = false
    } else if (i === index) {
      w.inProgress = true
      w.locked = false
      // Open week content
      openWeekContent(week, index)
    }
  })
  
  updateProgress()
}

const openWeekContent = (week, index) => {
  // Show week content in a modal or navigate to dedicated page
  if (index === 0) {
    // Week 1 content
    showWeek1Content()
  } else {
    alert(`Week ${index + 1} content will be available after completing previous weeks`)
  }
}

const showWeek1Content = () => {
  // Create a modal or navigate to week content
  const weekContent = `
    <div style="max-width: 800px; margin: 0 auto; padding: 20px; font-family: Arial, sans-serif;">
      <h2 style="color: #00897b; text-align: center; margin-bottom: 30px;">
        🧩 WEEK 1: INTRODUCTION TO CODING
      </h2>
      <h3 style="color: #ff6b35; text-align: center; margin-bottom: 20px;">
        🎯 Topic: Introduction to Programming and Computational Thinking
      </h3>
      
      <div style="background: #f0f8ff; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
        <h4 style="color: #00897b; margin-bottom: 10px;">📖 WEEK 1: FULL NOTES (FORMAL / LMS STANDARD)</h4>
        
        <h5 style="color: #333; margin-top: 20px;">1.0 Introduction</h5>
        <p style="line-height: 1.6; margin-bottom: 15px;">
          In the modern digital world, coding has become a fundamental skill that enables individuals to interact with technology beyond basic usage. 
          Coding, also known as programming, is the process of writing instructions that a computer can follow to perform specific tasks.
        </p>
        <p style="line-height: 1.6; margin-bottom: 15px;">
          These instructions are written using programming languages, which act as a bridge between human thinking and machine execution. 
          Understanding coding not only allows learners to create software and applications but also develops logical thinking, creativity, 
          and problem-solving skills.
        </p>
        <p style="line-height: 1.6; margin-bottom: 15px;">
          This week introduces learners to the foundational concepts of coding and computational thinking.
        </p>
        
        <h5 style="color: #333; margin-top: 20px;">2.0 Definition of Coding and Programming</h5>
        <p style="line-height: 1.6; margin-bottom: 15px;">
          <strong>Coding</strong> refers to the process of translating human instructions into a language that a computer can understand and execute.
        </p>
        <p style="line-height: 1.6; margin-bottom: 15px;">
          <strong>Programming</strong>, on the other hand, is a broader concept that includes:
        </p>
        <ul style="margin-left: 20px; line-height: 1.8; margin-bottom: 15px;">
          <li>Designing a solution</li>
          <li>Writing code</li>
          <li>Testing the program</li>
          <li>Fixing errors (debugging)</li>
        </ul>
        <p style="line-height: 1.6; margin-bottom: 15px;">
          A computer operates based on input, processing, and output. Coding provides the instructions that control this process.
        </p>
        
        <div style="background: #e8f5e8; padding: 15px; border-radius: 8px; margin: 15px 0;">
          <h6 style="color: #2e7d32; margin-bottom: 10px;">Example 2.1: Simple Program Logic</h6>
          <p style="margin-bottom: 10px;">A basic program may follow this structure:</p>
          <ul style="margin-left: 20px; line-height: 1.8;">
            <li><strong>Input:</strong> User enters a number</li>
            <li><strong>Process:</strong> System calculates the square</li>
            <li><strong>Output:</strong> Result is displayed</li>
          </ul>
          <p style="margin-top: 10px;">This demonstrates how coding controls how computers handle tasks.</p>
        </div>
        
        <h5 style="color: #333; margin-top: 20px;">3.0 Programming Languages</h5>
        <p style="line-height: 1.6; margin-bottom: 15px;">
          <strong>Programming languages</strong> are formal languages used to communicate with computers. 
          Each language has its own syntax (rules) and structure.
        </p>
        
        <h6 style="color: #00897b; margin: 15px 0 10px 0;">Examples of Programming Languages:</h6>
        <ul style="margin-left: 20px; line-height: 1.8; margin-bottom: 15px;">
          <li><strong>Scratch</strong> – visual programming for beginners</li>
          <li><strong>Python</strong> – simple and widely used</li>
          <li><strong>JavaScript</strong> – used for web development</li>
          <li><strong>HTML/CSS</strong> – used for designing web pages</li>
        </ul>
        <p style="line-height: 1.6; margin-bottom: 15px;">
          For beginners, Scratch is often preferred because it uses graphical blocks instead of text-based code, 
          making it easier to understand programming logic.
        </p>
        
        <div style="background: #fff3e0; padding: 15px; border-radius: 8px; margin: 15px 0;">
          <h6 style="color: #f57c00; margin-bottom: 10px;">Example 3.1: Scratch Concept</h6>
          <p style="margin-bottom: 10px;">Instead of writing code, a learner can:</p>
          <ul style="margin-left: 20px; line-height: 1.8;">
            <li>Drag a "move 10 steps" block</li>
            <li>Attach it to a "start" block</li>
          </ul>
          <p style="margin-top: 10px;">This represents a simple instruction sequence.</p>
        </div>
        
        <h5 style="color: #333; margin-top: 20px;">4.0 Computational Thinking</h5>
        <p style="line-height: 1.6; margin-bottom: 15px;">
          <strong>Computational thinking</strong> is a systematic approach to problem-solving that involves breaking down 
          complex problems into manageable parts and solving them logically.
        </p>
        <p style="line-height: 1.6; margin-bottom: 15px;">
          It is a key skill in coding and includes four main components:
        </p>
        
        <h6 style="color: #00897b; margin: 15px 0 10px 0;">4.1 Decomposition</h6>
        <p style="line-height: 1.6; margin-bottom: 15px;">
          <strong>Decomposition</strong> involves breaking down a complex problem into smaller, manageable parts.
        </p>
        <p style="margin-bottom: 10px;"><strong>Example:</strong></p>
        <p style="margin-left: 20px; line-height: 1.6;">
          Developing a game can be divided into:
        </p>
        <ul style="margin-left: 40px; line-height: 1.8; margin-bottom: 15px;">
          <li>Designing characters</li>
          <li>Creating movement</li>
          <li>Adding scoring system</li>
        </ul>
        
        <h6 style="color: #00897b; margin: 15px 0 10px 0;">4.2 Pattern Recognition</h6>
        <p style="line-height: 1.6; margin-bottom: 15px;">
          <strong>Pattern recognition</strong> involves identifying similarities or repeated elements within a problem.
        </p>
        <p style="margin-bottom: 10px;"><strong>Example:</strong></p>
        <p style="margin-left: 20px; line-height: 1.6;">
          If a character jumps multiple times in a game, the same instruction can be reused instead of rewriting it.
        </p>
        
        <h6 style="color: #00897b; margin: 15px 0 10px 0;">4.3 Abstraction</h6>
        <p style="line-height: 1.6; margin-bottom: 15px;">
          <strong>Abstraction</strong> focuses on removing unnecessary details and concentrating on essential elements of a problem.
        </p>
        <p style="margin-bottom: 10px;"><strong>Example:</strong></p>
        <p style="margin-left: 20px; line-height: 1.6;">
          When designing a system, focus on functionality rather than appearance in early stages.
        </p>
        
        <h6 style="color: #00897b; margin: 15px 0 10px 0;">4.4 Algorithm Design</h6>
        <p style="line-height: 1.6; margin-bottom: 15px;">
          <strong>Algorithm design</strong> involves creating a step-by-step procedure to solve a problem.
        </p>
        
        <h5 style="color: #333; margin-top: 20px;">5.0 Algorithms</h5>
        <p style="line-height: 1.6; margin-bottom: 15px;">
          An <strong>algorithm</strong> is a finite sequence of well-defined instructions used to solve a problem or perform a task.
        </p>
        <p style="line-height: 1.6; margin-bottom: 15px;">
          Algorithms must be:
        </p>
        <ul style="margin-left: 20px; line-height: 1.8; margin-bottom: 15px;">
          <li>Clear</li>
          <li>Ordered</li>
          <li>Finite (must end)</li>
        </ul>
        
        <div style="background: #e8f5e8; padding: 15px; border-radius: 8px; margin: 15px 0;">
          <h6 style="color: #2e7d32; margin-bottom: 10px;">Example 5.1: Everyday Algorithm</h6>
          <p style="margin-bottom: 10px;"><strong>Task: Making Tea</strong></p>
          <ol style="margin-left: 20px; line-height: 1.8;">
            <li>Boil water</li>
            <li>Add tea leaves</li>
            <li>Add sugar</li>
            <li>Pour into a cup</li>
          </ol>
        </div>
        
        <div style="background: #fff3e0; padding: 15px; border-radius: 8px; margin: 15px 0;">
          <h6 style="color: #f57c00; margin-bottom: 10px;">Example 5.2: Simple Coding Algorithm</h6>
          <p style="margin-bottom: 10px;"><strong>Task: Move a character</strong></p>
          <ol style="margin-left: 20px; line-height: 1.8;">
            <li>Start program</li>
            <li>Move forward</li>
            <li>Turn right</li>
            <li>Stop</li>
          </ol>
        </div>
        
        <h5 style="color: #333; margin-top: 20px;">6.0 Basic Programming Concepts</h5>
        
        <h6 style="color: #00897b; margin: 15px 0 10px 0;">6.1 Sequence</h6>
        <p style="line-height: 1.6; margin-bottom: 15px;">
          <strong>Sequence</strong> refers to the order in which instructions are executed.
        </p>
        <div style="background: #ffebee; padding: 15px; border-radius: 8px; margin: 15px 0;">
          <p style="margin-bottom: 10px;"><strong>Incorrect order:</strong></p>
          <p style="margin-left: 20px; color: #c62828;">Wear shoes → wear socks</p>
          <p style="margin-bottom: 10px;"><strong>Correct order:</strong></p>
          <p style="margin-left: 20px; color: #2e7d32;">Wear socks → wear shoes</p>
          <p style="margin-top: 10px;">In coding, incorrect sequence leads to incorrect output.</p>
        </div>
        
        <h6 style="color: #00897b; margin: 15px 0 10px 0;">6.2 Iteration (Loops)</h6>
        <p style="line-height: 1.6; margin-bottom: 15px;">
          <strong>Iteration</strong> refers to repeating a set of instructions multiple times.
        </p>
        <p style="margin-bottom: 10px;"><strong>Example:</strong></p>
        <p style="margin-left: 20px; line-height: 1.6;">
          A loop can be used to:
        </p>
        <ul style="margin-left: 40px; line-height: 1.8; margin-bottom: 15px;">
          <li>Repeat a movement 10 times</li>
          <li>Play a sound continuously</li>
        </ul>
        
        <h6 style="color: #00897b; margin: 15px 0 10px 0;">6.3 Selection (Conditional Statements)</h6>
        <p style="line-height: 1.6; margin-bottom: 15px;">
          <strong>Selection</strong> allows a program to make decisions based on conditions.
        </p>
        <p style="margin-bottom: 10px;"><strong>Example:</strong></p>
        <ul style="margin-left: 20px; line-height: 1.8; margin-bottom: 15px;">
          <li>If a button is pressed → move character</li>
          <li>Else → remain still</li>
        </ul>
        
        <h5 style="color: #333; margin-top: 20px;">7.0 Importance of Learning Coding</h5>
        <p style="line-height: 1.6; margin-bottom: 15px;">
          Learning coding provides several benefits:
        </p>
        <ul style="margin-left: 20px; line-height: 1.8; margin-bottom: 15px;">
          <li>Enhances logical and critical thinking</li>
          <li>Improves problem-solving skills</li>
          <li>Encourages creativity and innovation</li>
          <li>Prepares learners for careers in technology</li>
          <li>Enables creation of digital solutions</li>
        </ul>
        
        <h5 style="color: #333; margin-top: 20px;">8.0 Application of Coding in Real Life</h5>
        <p style="line-height: 1.6; margin-bottom: 15px;">
          Coding is used in various real-world applications:
        </p>
        <ul style="margin-left: 20px; line-height: 1.8; margin-bottom: 15px;">
          <li>Mobile applications</li>
          <li>Websites</li>
          <li>Banking systems</li>
          <li>Healthcare systems</li>
          <li>Educational platforms</li>
          <li>Games and animations</li>
        </ul>
        
        <div style="background: #f3e5f5; padding: 15px; border-radius: 8px; margin: 15px 0;">
          <h6 style="color: #9c27b0; margin-bottom: 10px;">Example 8.1: Real-Life Computational Thinking</h6>
          <p style="margin-bottom: 10px;"><strong>Crossing the Road:</strong></p>
          <ol style="margin-left: 20px; line-height: 1.8;">
            <li>Look left</li>
            <li>Look right</li>
            <li>If road is clear → cross</li>
            <li>Else → wait</li>
          </ol>
          <p style="margin-top: 10px;">This demonstrates:</p>
          <ul style="margin-left: 20px; line-height: 1.8;">
            <li>Algorithm</li>
            <li>Condition</li>
            <li>Logical reasoning</li>
          </ul>
        </div>
        
        <h5 style="color: #333; margin-top: 20px;">9.0 Summary of Key Concepts</h5>
        <p style="line-height: 1.6; margin-bottom: 15px;">
          By the end of this week, learners should understand:
        </p>
        <ul style="margin-left: 20px; line-height: 1.8; margin-bottom: 15px;">
          <li>The meaning of coding and programming</li>
          <li>The role of programming languages</li>
          <li>The concept of computational thinking</li>
          <li>The importance of algorithms</li>
          <li>Basic programming structures: sequence, loops, and conditions</li>
        </ul>
        
        <div style="background: linear-gradient(135deg, #f1762eff, #ff9318ff); color: white; padding: 20px; border-radius: 8px; margin-top: 30px; text-align: center;">
          <h4 style="margin: 0 0 15px 0;">📝 END OF WEEK 1 QUIZ</h4>
          <div style="background: rgba(255,255,255,0.1); padding: 15px; border-radius: 8px;">
            
            
            
            <button onclick="window.open('/course/1/quiz', '_blank')" style="background: white; color: #00897b; border: 2px solid white; padding: 12px 30px; border-radius: 6px; font-size: 16px; font-weight: bold; cursor: pointer; margin-top: 20px;">
              📝 Take Week 1 Quiz
            </button>
          </div>
        </div>
      </div>
    </div>
  `
  
  // Create a new window to show the content
  const contentWindow = window.open('', '_blank', 'width=900,height=700,scrollbars=yes,resizable=yes')
  if (contentWindow) {
    contentWindow.document.write(weekContent)
    contentWindow.document.close()
  }
}

const completeWeek1 = () => {
  // Mark Week 1 as completed and unlock Week 2
  weeks.value[0].completed = true
  weeks.value[0].inProgress = false
  weeks.value[1].locked = false
  weeks.value[1].inProgress = true
  
  updateProgress()
  alert('Congratulations! Week 1 completed. Week 2 is now unlocked!')
  
  // Close the content window if it exists
  if (window.contentWindow && !window.contentWindow.closed) {
    window.contentWindow.close()
  }
}

const updateProgress = () => {
  const completedWeeks = weeks.value.filter(w => w.completed).length
  weeksCompleted.value = completedWeeks
  overallProgress.value = Math.round((completedWeeks / weeks.value.length) * 100)
  
  // Unlock exam if all weeks completed
  canTakeExam.value = completedWeeks === weeks.value.length
}

const startExam = () => {
  if (!canTakeExam.value) {
    alert('Please complete all weeks before taking the exam.')
    return
  }
  
  alert('Starting final exam...')
  // TODO: Navigate to exam page
}

</script>

<style scoped>
.course-learning {
  min-height: 100vh;
  background: #f5f5f5;
  font-family: Arial, sans-serif;
}

/* Course Header */
.course-header {
  display: flex;
  align-items: center;
  gap: 20px;
  padding: 20px 30px;
  background: white;
  border-bottom: 1px solid #e0e0e0;
  box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.enroll-flash {
  margin: 0 30px 16px;
  padding: 12px 16px;
  background: #ecfdf5;
  border: 1px solid #6ee7b7;
  border-radius: 8px;
  color: #065f46;
  font-size: 0.95rem;
}

.back-btn {
  background: #00897b;
  color: white;
  border: none;
  padding: 10px 20px;
  border-radius: 6px;
  cursor: pointer;
  font-size: 14px;
  font-weight: 600;
  transition: background 0.3s ease;
}

.back-btn:hover {
  background: #00695c;
}

.course-header h1 {
  margin: 0;
  color: #333;
  font-size: 28px;
  font-weight: bold;
}

/* Progress Section */
.progress-section {
  padding: 30px;
  background: white;
  margin: 20px 30px;
  border-radius: 12px;
  box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.progress-section h2 {
  margin: 0 0 20px 0;
  color: #00897b;
  font-size: 24px;
}

.progress-overview {
  display: flex;
  gap: 20px;
}

.progress-item {
  display: flex;
  align-items: center;
  gap: 15px;
  padding: 20px;
  background: linear-gradient(135deg, #e8f5e8, #f3e5f5);
  border-radius: 10px;
  border-left: 4px solid #4caf50;
}

.progress-icon {
  font-size: 32px;
}

.progress-info {
  flex: 1;
}

.progress-percentage {
  font-size: 18px;
  font-weight: bold;
  color: #4caf50;
  margin-bottom: 10px;
}

.progress-bar {
  width: 200px;
  height: 8px;
  background: #e0e0e0;
  border-radius: 4px;
  overflow: hidden;
}

.progress-fill {
  height: 100%;
  background: #4caf50;
  transition: width 0.3s ease;
}

/* Content Grid */
.content-grid {
  display: grid;
  grid-template-columns: 250px 1fr 300px;
  gap: 30px;
  padding: 0 30px 30px;
}

/* Stats Column */
.stats-column {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.stat-card {
  background: white;
  padding: 20px;
  border-radius: 10px;
  text-align: center;
  box-shadow: 0 2px 10px rgba(0,0,0,0.1);
  transition: transform 0.3s ease;
}

.stat-card:hover {
  transform: translateY(-2px);
}

.stat-card h3 {
  margin: 0 0 10px 0;
  color: #666;
  font-size: 14px;
  font-weight: 500;
}

.stat-value {
  font-size: 32px;
  font-weight: bold;
  color: #00897b;
}

/* Curriculum Column */
.curriculum-column {
  background: white;
  padding: 25px;
  border-radius: 12px;
  box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.curriculum-column h2 {
  margin: 0 0 20px 0;
  color: #00897b;
  font-size: 20px;
}

.weeks-list {
  display: flex;
  flex-direction: column;
  gap: 15px;
}

.week-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px;
  border: 2px solid #e0e0e0;
  border-radius: 10px;
  cursor: pointer;
  transition: all 0.3s ease;
}

.week-item:hover:not(.locked) {
  border-color: #00897b;
  box-shadow: 0 4px 12px rgba(0, 137, 123, 0.2);
  transform: translateY(-2px);
}

.week-item.locked {
  background: #f5f5f5;
  border-color: #ccc;
  cursor: not-allowed;
  opacity: 0.7;
}

.week-item.completed {
  border-color: #4caf50;
  background: #e8f5e8;
}

.week-item.in-progress {
  border-color: #ff9800;
  background: #fff3e0;
}

.week-header {
  display: flex;
  align-items: center;
  gap: 15px;
  flex: 1;
}

.week-icon {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 18px;
  font-weight: bold;
}

.week-item.completed .week-icon {
  background: #4caf50;
  color: white;
}

.week-item.locked .week-icon {
  background: #f44336;
  color: white;
}

.week-item.in-progress .week-icon {
  background: #ff9800;
  color: white;
}

.week-info {
  flex: 1;
}

.week-info h4 {
  margin: 0 0 5px 0;
  color: #333;
  font-size: 16px;
}

.week-info p {
  margin: 0;
  color: #666;
  font-size: 14px;
  line-height: 1.5;
}

.week-status {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  gap: 5px;
}

.status {
  padding: 4px 12px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 600;
}

.status.completed {
  background: #e8f5e8;
  color: #2e7d32;
}

.status.in-progress {
  background: #fff3e0;
  color: #f57c00;
}

.status.locked {
  background: #ffebee;
  color: #c62828;
}

.status.not-started {
  background: #f3e5f5;
  color: #9c27b0;
}

/* Info Column */
.info-column {
  display: flex;
  flex-direction: column;
  gap: 30px;
}

/* Instructors Section */
.instructors-section {
  background: white;
  padding: 25px;
  border-radius: 12px;
  box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.instructors-section h3 {
  margin: 0 0 20px 0;
  color: #00897b;
  font-size: 20px;
}

.instructors-list {
  display: flex;
  flex-direction: column;
  gap: 15px;
}

.instructor-card {
  display: flex;
  align-items: center;
  gap: 15px;
  padding: 15px;
  border: 1px solid #e0e0e0;
  border-radius: 8px;
}

.instructor-avatar {
  width: 60px;
  height: 60px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, #00897b, #26a69a);
  color: white;
  font-weight: bold;
  font-size: 16px;
}

.avatar-placeholder {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
  font-size: 16px;
  color: white;
}

.instructor-info {
  flex: 1;
}

.instructor-info h4 {
  margin: 0 0 5px 0;
  color: #333;
  font-size: 16px;
}

.instructor-title {
  color: #00897b;
  font-weight: 600;
  margin: 0 0 5px 0;
}

.contact-label {
  color: #666;
  font-size: 12px;
  margin: 0;
}

.office-hours {
  color: #ff9800;
  font-weight: 600;
  font-size: 12px;
  margin: 0;
}

/* Exam Section */
.exam-section {
  background: white;
  padding: 25px;
  border-radius: 12px;
  box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.exam-section h3 {
  margin: 0 0 20px 0;
  color: #00897b;
  font-size: 20px;
}

.exam-card {
  border: 2px solid #e0e0e0;
  border-radius: 10px;
  overflow: hidden;
}

.exam-info {
  padding: 20px;
  background: #f8f9fa;
}

.exam-detail {
  display: flex;
  justify-content: space-between;
  margin-bottom: 10px;
}

.exam-label {
  color: #666;
  font-size: 14px;
}

.exam-value {
  color: #333;
  font-weight: 600;
  font-size: 14px;
}

.exam-description {
  padding: 15px 20px;
  background: white;
  text-align: center;
  color: #666;
  font-style: italic;
}

.exam-status {
  padding: 20px;
  text-align: center;
}

.exam-locked {
  color: #f44336;
}

.exam-locked .lock-icon {
  font-size: 32px;
  margin-bottom: 10px;
}

.exam-locked p {
  margin: 0;
  font-weight: 600;
}

.exam-unlocked {
  color: #4caf50;
}

.unlock-icon {
  font-size: 32px;
  margin-bottom: 10px;
}

.start-exam-btn {
  background: #4caf50;
  color: white;
  border: none;
  padding: 12px 30px;
  border-radius: 6px;
  cursor: pointer;
  font-size: 16px;
  font-weight: 600;
  transition: background 0.3s ease;
}

.start-exam-btn:hover {
  background: #45a049;
}

/* Responsive Design */
@media (max-width: 1200px) {
  .content-grid {
    grid-template-columns: 1fr;
    gap: 20px;
  }
  
  .stats-column {
    flex-direction: row;
    justify-content: space-around;
  }
}

@media (max-width: 768px) {
  .course-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 15px;
  }
  
  .stats-column {
    flex-direction: column;
  }
  
  .week-item {
    flex-direction: column;
    align-items: flex-start;
    gap: 10px;
  }
  
  .instructor-card {
    flex-direction: column;
    text-align: center;
  }
}
</style>
