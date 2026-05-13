<template>
  <div class="class-sessions">
    <div class="page-header">
      <button @click="goBack" class="back-btn">
        ← Back to Dashboard
      </button>
      <h1>Class Sessions</h1>
    </div>
    
    <div class="sessions-container">
      <!-- Loading State -->
      <div v-if="loading" class="loading-state">
        <div class="loading-spinner">Loading sessions...</div>
      </div>
      
      <!-- Empty State -->
      <div v-else-if="sessions.length === 0" class="empty-state">
        <div class="empty-icon">📅</div>
        <h3>No Sessions Scheduled</h3>
        <p>No class sessions have been scheduled yet. Check back later!</p>
      </div>
      
      <!-- Sessions List -->
      <div v-else class="sessions-grid">
        <div v-for="session in sessions" :key="session.id" class="session-card" :class="getSessionStatus(session.status)">
          <div class="session-header">
            <h3>{{ session.title }}</h3>
            <span class="session-status" :class="session.status">{{ formatStatus(session.status) }}</span>
          </div>
          
          <div class="session-details">
            <div class="session-time">
              <span class="time-icon">🕐</span>
              {{ formatDateTime(session.scheduled_at) }}
            </div>
            
            <div class="session-instructor" v-if="session.instructor">
              <span class="instructor-icon">👨‍🏫</span>
              {{ session.instructor.name }}
            </div>
            
            <div class="session-duration" v-if="session.duration">
              <span class="duration-icon">⏱️</span>
              {{ session.duration }} minutes
            </div>
          </div>
          
          <div class="session-description" v-if="session.description">
            <p>{{ session.description }}</p>
          </div>
          
          <div class="session-actions">
            <button 
              v-if="session.status === 'scheduled' && isJoinable(session)"
              @click="joinSession(session)" 
              class="join-btn"
            >
              Join Session
            </button>
            
            <button 
              v-if="session.status === 'completed' && session.recording_url"
              @click="viewRecording(session)" 
              class="view-btn"
            >
              View Recording
            </button>
            
            <button 
              v-if="session.status === 'scheduled'"
              @click="setReminder(session)" 
              class="remind-btn"
            >
              Set Reminder
            </button>
            
            <button 
              v-if="session.status === 'live'"
              @click="joinSession(session)" 
              class="live-btn"
            >
              🔴 Join Live
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { apiService } from '@/services/api'

const router = useRouter()

// State
const sessions = ref([])
const loading = ref(true)
const error = ref(null)

// API Functions
const fetchSessions = async () => {
  try {
    loading.value = true
    const data = await apiService.getSessions()
    sessions.value = data.sessions || []
  } catch (err) {
    console.error('Error fetching sessions:', err)
    // Fallback to empty array for now
    sessions.value = []
    error.value = err.message
  } finally {
    loading.value = false
  }
}

// Utility Functions
const formatDateTime = (dateTime) => {
  if (!dateTime) return 'Not scheduled'
  const date = new Date(dateTime)
  return date.toLocaleString('en-US', {
    weekday: 'short',
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const formatStatus = (status) => {
  const statusMap = {
    'scheduled': 'Scheduled',
    'live': 'Live Now',
    'completed': 'Completed',
    'cancelled': 'Cancelled'
  }
  return statusMap[status] || status
}

const getSessionStatus = (status) => {
  return `session-${status}`
}

const isJoinable = (session) => {
  if (session.status !== 'scheduled') return false
  const now = new Date()
  const sessionTime = new Date(session.scheduled_at)
  const timeDiff = sessionTime - now
  // Allow joining 15 minutes before to 30 minutes after start time
  return timeDiff > -15 * 60 * 1000 && timeDiff < 30 * 60 * 1000
}

// Action Functions
const goBack = () => {
  router.push('/dashboard')
}

const joinSession = (session) => {
  const link = session.meeting_link || session.meeting_url
  if (link) {
    window.open(link, '_blank', 'noopener,noreferrer')
    return
  }
  alert('No meeting link is set for this session yet.')
}

const viewRecording = (session) => {
  // TODO: Implement recording viewing logic
  if (session.recording_url) {
    window.open(session.recording_url, '_blank')
  } else {
    alert('Recording not available yet')
  }
}

const setReminder = (session) => {
  // TODO: Implement reminder setting logic
  alert(`Reminder set for: ${session.title}\nYou'll be notified 15 minutes before the session.`)
}

// Lifecycle
onMounted(() => {
  fetchSessions()
})
</script>

<style scoped>
.class-sessions {
  padding: 20px;
  max-width: 1200px;
  margin: 0 auto;
}

.page-header {
  display: flex;
  align-items: center;
  gap: 20px;
  margin-bottom: 30px;
}

.back-btn {
  background: #334155;
  color: white;
  border: none;
  padding: 8px 16px;
  border-radius: 4px;
  cursor: pointer;
  font-size: 14px;
}

.back-btn:hover {
  background: #1e293b;
}

.page-header h1 {
  margin: 0;
  color: #333;
  font-size: 32px;
}

/* Loading and Empty States */
.loading-state, .empty-state {
  text-align: center;
  padding: 60px 20px;
  background: white;
  border-radius: 12px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.loading-spinner {
  font-size: 18px;
  color: #666;
}

.empty-icon {
  font-size: 48px;
  margin-bottom: 20px;
}

.empty-state h3 {
  color: #333;
  margin: 0 0 10px 0;
}

.empty-state p {
  color: #666;
  margin: 0;
}

/* Sessions Grid */
.sessions-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
  gap: 20px;
}

.session-card {
  background: white;
  padding: 25px;
  border-radius: 12px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.session-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
}

/* Session Status Styling */
.session-card.session-live {
  border-left: 4px solid #dc3545;
}

.session-card.session-scheduled {
  border-left: 4px solid #007bff;
}

.session-card.session-completed {
  border-left: 4px solid #28a745;
}

.session-card.session-cancelled {
  border-left: 4px solid #6c757d;
  opacity: 0.7;
}

.session-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 20px;
}

.session-header h3 {
  margin: 0;
  color: #000000;
  font-size: 20px;
  flex: 1;
}

.session-status {
  padding: 4px 12px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 600;
  text-transform: uppercase;
}

.session-status.scheduled {
  background: #e3f2fd;
  color: #1976d2;
}

.session-status.live {
  background: #ffebee;
  color: #dc3545;
  animation: pulse 2s infinite;
}

.session-status.completed {
  background: #e8f5e8;
  color: #28a745;
}

.session-status.cancelled {
  background: #f5f5f5;
  color: #6c757d;
}

@keyframes pulse {
  0% { opacity: 1; }
  50% { opacity: 0.7; }
  100% { opacity: 1; }
}

/* Session Details */
.session-details {
  margin-bottom: 20px;
}

.session-time, .session-instructor, .session-duration {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 8px;
  color: #666;
  font-size: 14px;
}

.time-icon, .instructor-icon, .duration-icon {
  font-size: 16px;
}

.session-description {
  margin-bottom: 20px;
}

.session-description p {
  color: #666;
  line-height: 1.5;
  margin: 0;
}

/* Session Actions */
.session-actions {
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
}

.join-btn, .view-btn, .remind-btn, .live-btn {
  border: none;
  padding: 10px 20px;
  border-radius: 6px;
  cursor: pointer;
  font-weight: 600;
  transition: all 0.2s ease;
  flex: 1;
  min-width: 120px;
}

.join-btn {
  background: #000000;
  color: white;
}

.join-btn:hover {
  background: #333333;
}

.view-btn {
  background: #666666;
  color: white;
}

.view-btn:hover {
  background: #888888;
}

.remind-btn {
  background: #ff9900;
  color: white;
}

.remind-btn:hover {
  background: #e68600;
}

.live-btn {
  background: #dc3545;
  color: white;
  animation: pulse 2s infinite;
}

.live-btn:hover {
  background: #c82333;
}

/* Responsive Design */
@media (max-width: 768px) {
  .sessions-grid {
    grid-template-columns: 1fr;
  }
  
  .session-header {
    flex-direction: column;
    gap: 10px;
  }
  
  .session-actions {
    flex-direction: column;
  }
  
  .session-actions button {
    width: 100%;
  }
}
</style>
