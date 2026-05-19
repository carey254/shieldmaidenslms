<template>
  <div class="content-upload">
    <div class="upload-header">
      <h1>Upload Course Content</h1>
      <div class="course-selector">
        <label>Select Course:</label>
        <select v-model="selectedCourse" class="course-select">
          <option value="">Choose a course...</option>
          <option v-for="course in courses" :key="course.id" :value="course.id">
            {{ course.title }}
          </option>
        </select>
      </div>
    </div>

    <div v-if="selectedCourse" class="upload-container">
      <!-- Upload Tabs -->
      <div class="upload-tabs">
        <button 
          v-for="tab in tabs" 
          :key="tab.id"
          @click="activeTab = tab.id"
          :class="['tab-btn', { active: activeTab === tab.id }]"
        >
          <i :class="tab.icon"></i>
          {{ tab.name }}
        </button>
      </div>

      <!-- Video Upload -->
      <div v-if="activeTab === 'videos'" class="upload-section">
        <h3>Upload Videos</h3>
        <div class="upload-area" @drop="handleVideoDrop" @dragover.prevent @dragenter.prevent>
          <div class="upload-icon">
            <i class="fas fa-video"></i>
          </div>
          <p>Drag and drop videos here or click to browse</p>
          <input type="file" @change="handleVideoSelect" multiple accept="video/*" class="file-input">
          <button @click="$refs.videoInput.click()" class="browse-btn">Browse Videos</button>
        </div>
        
        <div v-if="videos.length > 0" class="uploaded-list">
          <h4>Uploaded Videos</h4>
          <div v-for="(video, index) in videos" :key="index" class="upload-item">
            <div class="item-info">
              <i class="fas fa-video"></i>
              <div>
                <div class="item-name">{{ video.name }}</div>
                <div class="item-size">{{ formatFileSize(video.size) }}</div>
              </div>
            </div>
            <button @click="removeVideo(index)" class="remove-btn">
              <i class="fas fa-times"></i>
            </button>
          </div>
        </div>
      </div>

      <!-- Notes Upload -->
      <div v-if="activeTab === 'notes'" class="upload-section">
        <h3>Upload Notes & Documents</h3>
        <div class="upload-area" @drop="handleNotesDrop" @dragover.prevent @dragenter.prevent>
          <div class="upload-icon">
            <i class="fas fa-file-alt"></i>
          </div>
          <p>Drag and drop documents here or click to browse</p>
          <input type="file" @change="handleNotesSelect" multiple accept=".pdf,.doc,.docx,.txt" class="file-input">
          <button @click="$refs.notesInput.click()" class="browse-btn">Browse Documents</button>
        </div>
        
        <div v-if="notes.length > 0" class="uploaded-list">
          <h4>Uploaded Documents</h4>
          <div v-for="(note, index) in notes" :key="index" class="upload-item">
            <div class="item-info">
              <i class="fas fa-file-alt"></i>
              <div>
                <div class="item-name">{{ note.name }}</div>
                <div class="item-size">{{ formatFileSize(note.size) }}</div>
              </div>
            </div>
            <button @click="removeNote(index)" class="remove-btn">
              <i class="fas fa-times"></i>
            </button>
          </div>
        </div>
      </div>

      <!-- Assignments -->
      <div v-if="activeTab === 'assignments'" class="upload-section">
        <h3>Create Assignment</h3>
        <div class="assignment-form">
          <div class="form-group">
            <label>Assignment Title</label>
            <input v-model="assignment.title" type="text" placeholder="Enter assignment title" class="form-input">
          </div>
          
          <div class="form-group">
            <label>Description</label>
            <textarea v-model="assignment.description" placeholder="Enter assignment description" class="form-textarea"></textarea>
          </div>
          
          <div class="form-group">
            <label>Due Date</label>
            <input v-model="assignment.dueDate" type="datetime-local" class="form-input">
          </div>
          
          <div class="form-group">
            <label>Points</label>
            <input v-model="assignment.points" type="number" placeholder="100" class="form-input">
          </div>
          
          <div class="form-group">
            <label>Assignment Files (Optional)</label>
            <div class="upload-area small" @drop="handleAssignmentDrop" @dragover.prevent @dragenter.prevent>
              <div class="upload-icon">
                <i class="fas fa-paperclip"></i>
              </div>
              <p>Drag and drop files or click to browse</p>
              <input type="file" @change="handleAssignmentSelect" multiple class="file-input">
              <button @click="$refs.assignmentInput.click()" class="browse-btn">Browse Files</button>
            </div>
          </div>
          
          <button @click="saveAssignment" class="btn btn-primary">
            <i class="fas fa-save"></i>
            Save Assignment
          </button>
        </div>
      </div>

      <!-- Quizzes -->
      <div v-if="activeTab === 'quizzes'" class="upload-section">
        <h3>Create Quiz</h3>
        <div class="quiz-form">
          <div class="form-group">
            <label>Quiz Title</label>
            <input v-model="quiz.title" type="text" placeholder="Enter quiz title" class="form-input">
          </div>
          
          <div class="form-group">
            <label>Time Limit (minutes)</label>
            <input v-model="quiz.timeLimit" type="number" placeholder="30" class="form-input">
          </div>
          
          <div class="questions-section">
            <div class="section-header">
              <h4>Questions</h4>
              <button @click="addQuestion" class="btn btn-sm btn-primary">
                <i class="fas fa-plus"></i>
                Add Question
              </button>
            </div>
            
            <div v-for="(question, qIndex) in quiz.questions" :key="qIndex" class="question-item">
              <div class="question-header">
                <span>Question {{ qIndex + 1 }}</span>
                <button @click="removeQuestion(qIndex)" class="btn btn-sm btn-danger">
                  <i class="fas fa-trash"></i>
                </button>
              </div>
              
              <div class="form-group">
                <input v-model="question.text" type="text" placeholder="Enter question" class="form-input">
              </div>
              
              <div class="form-group">
                <label>Options</label>
                <div v-for="(option, oIndex) in question.options" :key="oIndex" class="option-item">
                  <input 
                    v-model="option.text" 
                    type="text" 
                    :placeholder="`Option ${oIndex + 1}`" 
                    class="form-input"
                  >
                  <input 
                    v-model="question.correctAnswer" 
                    type="radio" 
                    :value="oIndex"
                    class="radio-input"
                  >
                  <label>Correct</label>
                </div>
              </div>
            </div>
          </div>
          
          <button @click="saveQuiz" class="btn btn-primary">
            <i class="fas fa-save"></i>
            Save Quiz
          </button>
        </div>
      </div>
    </div>

    <!-- No Course Selected -->
    <div v-else class="no-course-selected">
      <div class="empty-icon">
        <i class="fas fa-book"></i>
      </div>
      <h3>Select a Course</h3>
      <p>Please select a course to upload content.</p>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()

const selectedCourse = ref('')
const activeTab = ref('videos')
const videos = ref([])
const notes = ref([])
const assignmentFiles = ref([])

const assignment = ref({
  title: '',
  description: '',
  dueDate: '',
  points: 100
})

const quiz = ref({
  title: '',
  timeLimit: 30,
  questions: []
})

const tabs = [
  { id: 'videos', name: 'Videos', icon: 'fas fa-video' },
  { id: 'notes', name: 'Notes & Docs', icon: 'fas fa-file-alt' },
  { id: 'assignments', name: 'Assignments', icon: 'fas fa-tasks' },
  { id: 'quizzes', name: 'Quizzes', icon: 'fas fa-question-circle' }
]

const courses = ref([
  { id: 1, title: 'Digital Safety Basics' },
  { id: 2, title: 'Coding for Beginners' },
  { id: 3, title: 'Cybersecurity Fundamentals' },
  { id: 4, title: 'TFGBV Awareness' }
])

const handleVideoSelect = (event) => {
  const files = Array.from(event.target.files)
  videos.value.push(...files)
}

const handleVideoDrop = (event) => {
  const files = Array.from(event.dataTransfer.files).filter(file => file.type.startsWith('video/'))
  videos.value.push(...files)
}

const removeVideo = (index) => {
  videos.value.splice(index, 1)
}

const handleNotesSelect = (event) => {
  const files = Array.from(event.target.files)
  notes.value.push(...files)
}

const handleNotesDrop = (event) => {
  const files = Array.from(event.dataTransfer.files)
  notes.value.push(...files)
}

const removeNote = (index) => {
  notes.value.splice(index, 1)
}

const handleAssignmentSelect = (event) => {
  const files = Array.from(event.target.files)
  assignmentFiles.value.push(...files)
}

const handleAssignmentDrop = (event) => {
  const files = Array.from(event.dataTransfer.files)
  assignmentFiles.value.push(...files)
}

const addQuestion = () => {
  quiz.value.questions.push({
    text: '',
    options: [
      { text: '' },
      { text: '' },
      { text: '' },
      { text: '' }
    ],
    correctAnswer: 0
  })
}

const removeQuestion = (index) => {
  quiz.value.questions.splice(index, 1)
}

const saveAssignment = () => {
  // Save assignment logic
  alert('Assignment saved successfully!')
  assignment.value = { title: '', description: '', dueDate: '', points: 100 }
}

const saveQuiz = () => {
  // Save quiz logic
  alert('Quiz saved successfully!')
  quiz.value = { title: '', timeLimit: 30, questions: [] }
}

const formatFileSize = (bytes) => {
  if (bytes === 0) return '0 Bytes'
  const k = 1024
  const sizes = ['Bytes', 'KB', 'MB', 'GB']
  const i = Math.floor(Math.log(bytes) / Math.log(k))
  return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i]
}
</script>

<style scoped>
.content-upload {
  padding: 2rem;
  max-width: 1200px;
  margin: 0 auto;
}

.upload-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
  gap: 2rem;
}

.upload-header h1 {
  color: #1a365d;
  margin: 0;
}

.course-selector {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.course-selector label {
  color: #374151;
  font-weight: 500;
}

.course-select {
  padding: 0.75rem 1rem;
  border: 2px solid #e2e8f0;
  border-radius: 8px;
  font-size: 0.875rem;
  background: white;
  min-width: 250px;
}

.course-select:focus {
  outline: none;
  border-color: #1a365d;
}

.upload-container {
  background: white;
  border-radius: 16px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  overflow: hidden;
}

.upload-tabs {
  display: flex;
  border-bottom: 2px solid #f1f5f9;
}

.tab-btn {
  flex: 1;
  padding: 1rem;
  background: none;
  border: none;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  color: #64748b;
  transition: all 0.2s;
}

.tab-btn.active {
  background: #1a365d;
  color: white;
}

.tab-btn:hover:not(.active) {
  background: #f8fafc;
}

.upload-section {
  padding: 2rem;
}

.upload-section h3 {
  color: #1a365d;
  margin-bottom: 1.5rem;
}

.upload-area {
  border: 2px dashed #cbd5e1;
  border-radius: 12px;
  padding: 3rem 2rem;
  text-align: center;
  cursor: pointer;
  transition: all 0.2s;
  position: relative;
}

.upload-area:hover {
  border-color: #1a365d;
  background: #f8fafc;
}

.upload-area.small {
  padding: 2rem 1rem;
}

.upload-icon {
  font-size: 3rem;
  margin-bottom: 1rem;
  color: #64748b;
}

.upload-area p {
  color: #64748b;
  margin-bottom: 1rem;
}

.file-input {
  display: none;
}

.browse-btn {
  background: #f97316;
  color: white;
  border: none;
  padding: 0.75rem 1.5rem;
  border-radius: 8px;
  cursor: pointer;
  font-size: 0.875rem;
  transition: background 0.2s;
}

.browse-btn:hover {
  background: #ea580c;
}

.uploaded-list {
  margin-top: 2rem;
}

.uploaded-list h4 {
  color: #f97316;
  margin-bottom: 1rem;
}

.upload-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem;
  background: #f8fafc;
  border-radius: 8px;
  margin-bottom: 0.75rem;
}

.item-info {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.item-info i {
  color: #f97316;
  font-size: 1.25rem;
}

.item-name {
  font-weight: 500;
  color: #374151;
}

.item-size {
  font-size: 0.75rem;
  color: #64748b;
}

.remove-btn {
  background: #ef4444;
  color: white;
  border: none;
  width: 32px;
  height: 32px;
  border-radius: 50%;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
}

.assignment-form,
.quiz-form {
  max-width: 600px;
}

.form-group {
  margin-bottom: 1.5rem;
}

.form-group label {
  display: block;
  color: #374151;
  font-weight: 500;
  margin-bottom: 0.5rem;
}

.form-input,
.form-textarea {
  width: 100%;
  padding: 0.75rem;
  border: 2px solid #e2e8f0;
  border-radius: 8px;
  font-size: 0.875rem;
}

.form-input:focus,
.form-textarea:focus {
  outline: none;
  border-color: #f97316;
}

.form-textarea {
  min-height: 120px;
  resize: vertical;
}

.questions-section {
  margin-top: 2rem;
}

.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}

.section-header h4 {
  color: #f97316;
  margin: 0;
}

.question-item {
  background: #f8fafc;
  padding: 1.5rem;
  border-radius: 8px;
  margin-bottom: 1rem;
}

.question-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}

.option-item {
  display: flex;
  align-items: center;
  gap: 1rem;
  margin-bottom: 0.75rem;
}

.radio-input {
  margin: 0;
}

.btn {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1.5rem;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-size: 0.875rem;
  font-weight: 600;
  transition: all 0.2s;
}

.btn-primary {
  background: #f97316;
  color: white;
}

.btn-primary:hover {
  background: #ea580c;
}

.btn-sm {
  padding: 0.5rem 1rem;
  font-size: 0.75rem;
}

.btn-danger {
  background: #ef4444;
  color: white;
}

.btn-danger:hover {
  background: #dc2626;
}

.no-course-selected {
  text-align: center;
  padding: 4rem 2rem;
  color: #64748b;
}

.empty-icon {
  font-size: 4rem;
  margin-bottom: 1rem;
  color: #64748b;
}

.no-course-selected h3 {
  color: #f97316;
  margin-bottom: 0.5rem;
}

@media (max-width: 768px) {
  .content-upload {
    padding: 1rem;
  }
  
  .upload-header {
    flex-direction: column;
    gap: 1rem;
    align-items: stretch;
  }
  
  .upload-tabs {
    flex-wrap: wrap;
  }
  
  .tab-btn {
    flex: 1 1 50%;
  }
}
</style>
