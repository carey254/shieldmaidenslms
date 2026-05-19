<template>
  <div class="instructor-dashboard">
    <!-- Loading Overlay -->
    <div v-if="loading" class="loading-overlay">
      <div class="loading-spinner">
        <div class="spinner"></div>
        <p>Loading dashboard...</p>
      </div>
    </div>

    <!-- Top row -->
    <div class="dash-top">
      <div class="greeting">
        <div class="title">
          {{ greetingLine }}, {{ user?.name || 'Instructor' }}!
        </div>
        <div class="subtitle">Live data from your instructor API — courses, sessions, learners, and tasks.</div>
      </div>

      <div class="top-right">
        <div class="pill">
          <span class="pill-text">{{ currentDateTime }}</span>
        </div>
        <button class="refresh-btn" @click="refreshWorkspace" :class="{ spinning: workspaceRefreshing }">
          <span class="refresh-icon">↻</span>
        </button>
      </div>
    </div>

    <!-- Quick Stats (dark cards) -->
    <section class="quick-stats">
      <div class="stats-container">
        <div v-for="(stat, index) in animatedStats" :key="index" class="stat-card" :class="`stat-${index}`" @mouseenter="animateStat(index)" @mouseleave="resetStat(index)">
          <div class="stat-icon"></div>
          <div class="stat-kicker">{{ stat.label }}</div>
          <div class="stat-main">
            <div class="stat-number">
              <span class="counter" :data-target="stat.value">{{ formatNumber(stat.value) }}</span><span class="stat-unit">{{ stat.unit }}</span>
            </div>
            <div class="stat-change" :class="getTrendClass(stat.trend)">
              {{ stat.change }}
            </div>
          </div>
          <div class="stat-glow" :style="{ background: stat.color }"></div>
        </div>
      </div>
    </section>

    <!-- Main grid -->
    <div class="dash-grid">
      <section class="card weekly-progress">
        <div class="card-head">
          <div class="card-title">
            Weekly Progress
          </div>
          <div class="card-actions">
            <button class="action-btn" @click="refreshProgress" title="Refresh">
              <span>↻</span>
            </button>
          </div>
        </div>
        <div class="chart">
          <div v-for="(v, i) in weeklyBars" :key="i" class="bar-wrapper">
            <div class="bar" :style="{ height: v + '%', '--delay': i * 0.1 + 's' }" :class="{ 'bar-highlight': i === highlightedBar }" @mouseenter="highlightedBar = i" @mouseleave="highlightedBar = -1">
              <div class="bar-tooltip">{{ v }}%</div>
            </div>
            <div class="bar-label">{{ getDayLabel(i) }}</div>
          </div>
        </div>
        <div class="mini-stats">
          <div class="mini" v-for="(m, idx) in miniStats" :key="idx" @mouseenter="animateMini(idx)" @mouseleave="resetMini(idx)">
            <div class="mini-value">{{ m.value }}</div>
            <div class="mini-label">{{ m.label }}</div>
            <div class="mini-glow"></div>
          </div>
        </div>
      </section>

      <section class="card next-lessons">
        <div class="card-head">
          <div class="card-title">
            My next lessons
          </div>
          <button class="link-btn" type="button" @click="goToSessions">
            View All <span class="arrow">→</span>
          </button>
        </div>
        <div class="lessons">
          <div v-for="(l, idx) in nextLessons" :key="idx" class="lesson" :class="{ 'lesson-urgent': isUrgent(l.time) }" @click="viewLessonDetails(l)">
            <div class="lesson-left">
              <div class="lesson-title">{{ l.title }}</div>
              <div class="lesson-sub">{{ l.tag }}</div>
            </div>
            <div class="lesson-right">
              <div class="lesson-time">{{ l.time }}</div>
              <div class="lesson-dur">{{ l.duration }}</div>
            </div>
            <div class="lesson-status" :class="{ 'status-soon': isSoon(l.time) }"></div>
          </div>
          <div v-if="!nextLessons.length" class="empty-state">
            <p>No scheduled sessions. Add one under Sessions.</p>
          </div>
        </div>
      </section>

      <section class="card my-courses">
        <div class="card-head">
          <div class="card-title">
            My courses
          </div>
          <div class="tabs">
            <button class="tab" :class="{ active: courseTab === 'all' }" @click="courseTab = 'all'">
              All
            </button>
            <button class="tab" :class="{ active: courseTab === 'mandatory' }" @click="courseTab = 'mandatory'">
              Mandatory
            </button>
            <button class="tab" :class="{ active: courseTab === 'completed' }" @click="courseTab = 'completed'">
              Completed
            </button>
          </div>
        </div>

        <div class="course-grid">
          <div v-for="course in filteredMyCourses" :key="course.id" class="course-tile" @click="manageContent(course)" @mouseenter="hoverCourse(course.id)" @mouseleave="unhoverCourse(course.id)">
            <div class="course-gradient" :style="getCourseGradient(course.id)"></div>
            <div class="course-top">
              <div class="course-name">{{ course.title }}</div>
              <div class="chip" :class="getStatusClass(course.status)">{{ course.status || 'published' }}</div>
            </div>
            <div class="course-desc">{{ course.description || 'Course details will appear here.' }}</div>
            <div class="course-bottom">
              <div class="progress">
                <div class="progress-bar">
                  <div class="progress-fill" :style="{ width: (course.completion_rate || 0) + '%', background: getProgressColor(course.completion_rate) }"></div>
                </div>
                <div class="progress-text">{{ course.completion_rate || 0 }}%</div>
              </div>
              <div class="meta">
                <span class="meta-item">{{ course.enrolled_count || 0 }} students</span>
                <span class="meta-item">{{ course.modules_count || 0 }} modules</span>
              </div>
            </div>
            <div class="course-hover-effect"></div>
          </div>
          <div v-if="filteredMyCourses.length === 0" class="empty-state">
            <p>No courses yet. Ask admin to assign courses.</p>
          </div>
        </div>
      </section>
    </div>

    <!-- Floating Action Button -->
    <button class="fab" @click="showQuickActions = !showQuickActions" :class="{ active: showQuickActions }">
      <span class="fab-icon">+</span>
      <div class="fab-menu" v-if="showQuickActions">
        <button class="fab-item" @click.stop="goToCourses">
          <span>Courses</span>
        </button>
        <button class="fab-item" @click.stop="goToSessions">
          <span>Sessions</span>
        </button>
        <button class="fab-item" @click.stop="goToSettings">
          <span>Settings</span>
        </button>
      </div>
    </button>
  </div>
</template>
<script setup>
import { ref, computed, onMounted, watch, nextTick } from 'vue';
import { useAuthStore } from '@/stores/auth';
import { useRouter, useRoute } from 'vue-router';
import { API_BASE_URL } from '@/config/api';

const authStore = useAuthStore();
const user = computed(() => authStore.user);
const router = useRouter();
const route = useRoute();

/** Instructor portal uses `/api/instructor/*`; facilitator role uses `/api/facilitator/*`. */
const portalPrefix = computed(() => {
  const r = user.value?.role;
  return r === 'facilitator' ? 'facilitator' : 'instructor';
});

const apiInst = (path) => {
  const p = path.startsWith('/') ? path : `/${path}`;
  return `${API_BASE_URL}/${portalPrefix.value}${p}`;
};

const greetingLine = computed(() => {
  const h = new Date().getHours();
  if (h < 12) return 'Good morning';
  if (h < 17) return 'Good afternoon';
  return 'Good evening';
});

// Loading state
const loading = ref(true);

// Current date/time
const currentDateTime = ref(new Date().toLocaleString());

const courseTab = ref('all');

const weeklyBars = ref([22, 34, 41, 48, 36, 44, 52]);

// Interactive states
const highlightedBar = ref(-1);
const showQuickActions = ref(false);
const hoveredCourseId = ref(null);
const statAnimations = ref({});
const miniAnimations = ref({});

// Animation helpers
const animateStat = (index) => {
  statAnimations.value[index] = true;
};

const resetStat = (index) => {
  statAnimations.value[index] = false;
};

const animateMini = (index) => {
  miniAnimations.value[index] = true;
};

const resetMini = (index) => {
  miniAnimations.value[index] = false;
};

const hoverCourse = (id) => {
  hoveredCourseId.value = id;
};

const unhoverCourse = (id) => {
  hoveredCourseId.value = null;
};

// Icon helpers
const getTrendClass = (trend) => {
  return `trend-${trend || 'stable'}`;
};

const getDayLabel = (index) => {
  const days = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
  return days[index] || '';
};

const isUrgent = (time) => {
  // Check if lesson is within 1 hour
  if (!time) return false;
  const lessonTime = new Date(time);
  const now = new Date();
  const diff = lessonTime - now;
  return diff > 0 && diff < 3600000; // 1 hour in ms
};

const isSoon = (time) => {
  if (!time) return false;
  const lessonTime = new Date(time);
  const now = new Date();
  const diff = lessonTime - now;
  return diff > 0 && diff < 86400000; // 24 hours in ms
};

const viewLessonDetails = (lesson) => {
  console.log('Viewing lesson details:', lesson);
  // Navigate to lesson details
};

const getStatusClass = (status) => {
  const statusMap = {
    'published': 'status-published',
    'draft': 'status-draft',
    'archived': 'status-archived'
  };
  return statusMap[status?.toLowerCase()] || 'status-published';
};

const getCourseGradient = (id) => {
  const gradients = [
    'linear-gradient(135deg, #f97316 0%, #ea580c 100%)',
    'linear-gradient(135deg, #fb923c 0%, #f59e0b 100%)',
    'linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%)',
    'linear-gradient(135deg, #fcd34d 0%, #f59e0b 100%)',
    'linear-gradient(135deg, #fed7aa 0%, #fb923c 100%)',
    'linear-gradient(135deg, #fdba74 0%, #f97316 100%)'
  ];
  return { background: gradients[id % gradients.length] };
};

const getProgressColor = (progress) => {
  if (progress >= 80) return 'linear-gradient(90deg, #10b981, #34d399)';
  if (progress >= 50) return 'linear-gradient(90deg, #f59e0b, #fbbf24)';
  if (progress >= 20) return 'linear-gradient(90deg, #f97316, #fb923c)';
  return 'linear-gradient(90deg, #ef4444, #f87171)';
};

const refreshProgress = () => {
  // Animate bars
  weeklyBars.value = weeklyBars.value.map(v => {
    const random = Math.random() * 20 - 10;
    return Math.min(100, Math.max(10, Math.round(v + random)));
  });
};

const miniStats = ref([
  { label: 'Courses', value: '0' },
  { label: 'Learners', value: '0' },
  { label: 'Avg completion', value: '0%' },
  { label: 'To grade', value: '0' },
]);

const nextLessons = ref([]);

const recentActivities = ref([]);
const pendingTasksList = ref([]);
const workspaceRefreshing = ref(false);

// Quick stats - will be fetched from API
const animatedStats = ref([
  { label: 'My Courses', value: 0, unit: '', change: '+0', trend: 'stable', icon: 'fas fa-book', color: '#4CAF50' },
  { label: 'Total Students', value: 0, unit: '', change: '+0', trend: 'stable', icon: 'fas fa-users', color: '#2196F3' },
  { label: 'Avg Completion', value: 0, unit: '%', change: '+0%', trend: 'stable', icon: 'fas fa-chart-line', color: '#FF9800' },
  { label: 'Satisfaction', value: 0, unit: '/5', change: '+0', trend: 'stable', icon: 'fas fa-star', color: '#9C27B0' }
]);

// Course Content Management - will be fetched from API
const myCourses = ref([]);
const refreshingCourses = ref(false);
const showCreateCourseModal = ref(false);

// Assessment Management - will be fetched from API
const activeAssessmentTab = ref('quizzes');
const refreshingAssessments = ref(false);
const showCreateAssessmentModal = ref(false);

const quizzes = ref([]);
const assignments = ref([]);
const exams = ref([]);
const gradingQueue = ref([]);

// Learner Management - will be fetched from API
const learners = ref([]);
const learnerSearchQuery = ref('');
const courseFilter = ref('');
const progressFilter = ref('');
const refreshingLearners = ref(false);

// Progress Tracking - will be fetched from API
const progressPeriod = ref('30');

// Communication & Support - will be fetched from API
const activeCommTab = ref('messages');
const messages = ref([]);
const discussions = ref([]);
const announcements = ref([]);
const showComposeMessageModal = ref(false);

// Course Improvement - will be fetched from API
const feedback = ref({
  average: 0,
  total_reviews: 0,
  highlights: []
});

// Opportunity Sharing - will be fetched from API
const sharedOpportunities = ref([]);
const showShareOpportunityModal = ref(false);

// Computed properties
const filteredLearners = computed(() => {
  return learners.value.filter(learner => {
    const matchesSearch = learner.name.toLowerCase().includes(learnerSearchQuery.value.toLowerCase()) ||
                         learner.email.toLowerCase().includes(learnerSearchQuery.value.toLowerCase());
    const matchesCourse = !courseFilter.value || learner.courses.some(course => course.id === parseInt(courseFilter.value));
    
    let matchesProgress = true;
    if (progressFilter.value === 'not-started') {
      matchesProgress = learner.avg_progress === 0;
    } else if (progressFilter.value === 'in-progress') {
      matchesProgress = learner.avg_progress > 0 && learner.avg_progress < 100;
    } else if (progressFilter.value === 'completed') {
      matchesProgress = learner.avg_progress === 100;
    }
    
    return matchesSearch && matchesCourse && matchesProgress;
  });
});

const filteredMyCourses = computed(() => {
  const list = Array.isArray(myCourses.value) ? myCourses.value : [];
  if (courseTab.value === 'completed') {
    return list.filter((c) => (c.completion_rate || 0) >= 100);
  }
  if (courseTab.value === 'mandatory') {
    // Placeholder tag until course metadata exists
    return list.slice(0, 4);
  }
  return list;
});

const goToCourses = () => {
  router.push('/instructor/courses');
};

const goToSessions = () => {
  router.push('/instructor/sessions');
};

const goToSettings = () => {
  router.push('/instructor/settings');
};

const pendingGradeCount = computed(() => {
  const t = pendingTasksList.value[0];
  if (!t || !t.description) return 0;
  const m = String(t.description).match(/(\d+)/);
  return m ? parseInt(m[1], 10) : 0;
});

const authFetchJson = async (url, options = {}) => {
  const headers = {
    Authorization: `Bearer ${authStore.token}`,
    Accept: 'application/json',
    ...(options.headers || {})
  };
  return fetch(url, { ...options, headers });
};

// API — all paths respect instructor vs facilitator role
const fetchDashboardStats = async () => {
  try {
    const response = await authFetchJson(apiInst('/dashboard/stats'));
    if (!response.ok) return;
    const data = await response.json();
    const ch = data.changes || {};
    animatedStats.value = [
      { label: 'My Courses', value: data.totalCourses ?? 0, unit: '', change: `+${ch.coursesThisMonth ?? 0}`, trend: 'stable', icon: 'fas fa-book', color: '#4CAF50' },
      { label: 'Total Students', value: data.totalStudents ?? 0, unit: '', change: `+${ch.studentsThisWeek ?? 0}`, trend: 'stable', icon: 'fas fa-users', color: '#2196F3' },
      { label: 'Avg Completion', value: data.avgCompletion ?? 0, unit: '%', change: '+0%', trend: 'stable', icon: 'fas fa-chart-line', color: '#FF9800' },
      { label: 'Satisfaction', value: data.avgRating ?? 0, unit: '/5', change: '+0', trend: 'stable', icon: 'fas fa-star', color: '#9C27B0' }
    ];
    const base = Math.min(92, Math.max(12, Number(data.avgCompletion) || 24));
    weeklyBars.value = [base - 8, base - 2, base + 4, base, base - 4, base + 6, base - 1].map((v) =>
      Math.min(100, Math.max(10, Math.round(v)))
    );
    miniStats.value = miniStats.value.map((row, i) => {
      if (i === 0) return { label: 'Courses', value: String(data.totalCourses ?? 0) };
      if (i === 1) return { label: 'Learners', value: String(data.totalStudents ?? 0) };
      if (i === 2) return { label: 'Avg completion', value: `${data.avgCompletion ?? 0}%` };
      return row;
    });
  } catch (error) {
    console.error('Error fetching instructor dashboard stats:', error);
  }
};

const fetchMyCourses = async () => {
  try {
    const response = await authFetchJson(apiInst('/dashboard/courses'));
    if (!response.ok) return;
    const data = await response.json();
    const rows = Array.isArray(data) ? data : data.courses || data.data || [];
    myCourses.value = rows;
  } catch (error) {
    console.error('Error fetching instructor courses:', error);
  }
};

const fetchDashboardActivities = async () => {
  try {
    const response = await authFetchJson(apiInst('/dashboard/activities'));
    if (!response.ok) return;
    const data = await response.json();
    recentActivities.value = Array.isArray(data) ? data : [];
  } catch (error) {
    console.error('Error fetching activities:', error);
  }
};

const fetchDashboardTasks = async () => {
  try {
    const response = await authFetchJson(apiInst('/dashboard/tasks'));
    if (!response.ok) return;
    const data = await response.json();
    pendingTasksList.value = Array.isArray(data) ? data : [];
    const g = pendingTasksList.value[0];
    let n = 0;
    if (g && g.description) {
      const m = String(g.description).match(/(\d+)/);
      if (m) n = parseInt(m[1], 10);
    }
    miniStats.value = miniStats.value.map((row, i) =>
      i === 3 ? { label: 'To grade', value: String(n) } : row
    );
  } catch (error) {
    console.error('Error fetching tasks:', error);
  }
};

const fetchClassSessionsData = async () => {
  try {
    const response = await authFetchJson(apiInst('/class-sessions'));
    if (!response.ok) return;
    const data = await response.json();
    const sessions = data.sessions || [];
    const now = Date.now();
    const upcoming = sessions
      .filter((s) => s.starts_at && new Date(s.starts_at).getTime() >= now - 36e5)
      .sort((a, b) => new Date(a.starts_at) - new Date(b.starts_at))
      .slice(0, 6);
    nextLessons.value = upcoming.map((s) => ({
      title: s.title || 'Session',
      tag: s.course_title || 'Course',
      time: formatSessionTime(s.starts_at),
      duration: s.duration_minutes ? `${s.duration_minutes} min` : '—'
    }));
    if (!nextLessons.value.length && sessions.length) {
      nextLessons.value = sessions.slice(0, 4).map((s) => ({
        title: s.title || 'Session',
        tag: s.course_title || 'Course',
        time: formatSessionTime(s.starts_at),
        duration: s.duration_minutes ? `${s.duration_minutes} min` : '—'
      }));
    }
  } catch (error) {
    console.error('Error fetching class sessions:', error);
  }
};

const fetchAssignments = async () => {
  try {
    const response = await authFetchJson(apiInst('/assignments'));
    if (!response.ok) return;
    const data = await response.json();
    assignments.value = data.data || data.assignments || [];
  } catch (error) {
    console.error('Error fetching assignments:', error);
  }
};

const fetchExams = async () => {
  try {
    const response = await authFetchJson(apiInst('/exams'));
    if (!response.ok) return;
    const data = await response.json();
    exams.value = data.data || data.exams || [];
  } catch (error) {
    console.error('Error fetching exams:', error);
  }
};

const fetchLearners = async () => {
  try {
    const response = await authFetchJson(apiInst('/learners'));
    if (!response.ok) return;
    const data = await response.json();
    learners.value = data.data || data.learners || [];
  } catch (error) {
    console.error('Error fetching learners:', error);
  }
};

const fetchMessages = async () => {
  try {
    const response = await authFetchJson(apiInst('/messages'));
    if (!response.ok) return;
    const data = await response.json();
    messages.value = Array.isArray(data) ? data : data.messages || [];
  } catch (error) {
    console.error('Error fetching messages:', error);
  }
};

const fetchAnnouncements = async () => {
  try {
    const response = await authFetchJson(apiInst('/announcements'));
    if (!response.ok) return;
    const data = await response.json();
    announcements.value = Array.isArray(data) ? data : data.announcements || [];
  } catch (error) {
    console.error('Error fetching announcements:', error);
  }
};

const fetchSharedOpportunities = async () => {
  try {
    const response = await authFetchJson(apiInst('/opportunities'));
    if (!response.ok) return;
    const data = await response.json();
    sharedOpportunities.value = data.opportunities || [];
  } catch (error) {
    console.error('Error fetching shared opportunities:', error);
  }
};

// Load all data on mount
const loadAllData = async () => {
  loading.value = true;
  try {
    await Promise.all([
      fetchDashboardStats(),
      fetchMyCourses(),
      fetchDashboardActivities(),
      fetchDashboardTasks(),
      fetchClassSessionsData(),
      fetchAssignments(),
      fetchExams(),
      fetchLearners(),
      fetchMessages(),
      fetchAnnouncements(),
      fetchSharedOpportunities()
    ]);
  } finally {
    loading.value = false;
  }
};

const refreshWorkspace = async () => {
  workspaceRefreshing.value = true;
  try {
    await loadAllData();
  } finally {
    workspaceRefreshing.value = false;
  }
};

// Refresh methods
const refreshCourses = async () => {
  refreshingCourses.value = true;
  await fetchMyCourses();
  refreshingCourses.value = false;
};

const refreshAssessments = async () => {
  refreshingAssessments.value = true;
  await Promise.all([fetchAssignments(), fetchExams(), fetchDashboardTasks()]);
  refreshingAssessments.value = false;
};

const refreshLearners = async () => {
  refreshingLearners.value = true;
  await fetchLearners();
  refreshingLearners.value = false;
};

// Action methods
const manageContent = (course) => {
  console.log('Managing content for:', course);
  // TODO: Navigate to course content management
  router.push(`/instructor/courses/${course.id}/content`);
};

const uploadMaterials = (course) => {
  console.log('Uploading materials for:', course);
  // TODO: Open upload modal
};

const viewAnalytics = (course) => {
  console.log('Viewing analytics for:', course);
  // TODO: Navigate to course analytics
  router.push(`/instructor/courses/${course.id}/analytics`);
};

// Assessment Management methods
const editQuiz = (quiz) => {
  console.log('Editing quiz:', quiz);
  // TODO: Open edit modal
};

const viewSubmissions = (assessment) => {
  console.log('Viewing submissions for:', assessment);
  // TODO: Navigate to submissions page
  router.push(`/instructor/assessments/${assessment.id}/submissions`);
};

const gradeQuiz = (quiz) => {
  console.log('Grading quiz:', quiz);
  // TODO: Navigate to grading interface
  router.push(`/instructor/assessments/${quiz.id}/grade`);
};

const editAssignment = (assignment) => {
  console.log('Editing assignment:', assignment);
  // TODO: Open edit modal
};

const gradeAssignment = (assignment) => {
  console.log('Grading assignment:', assignment);
  // TODO: Navigate to grading interface
  router.push(`/instructor/assignments/${assignment.id}/grade`);
};

const editExam = (exam) => {
  console.log('Editing exam:', exam);
  // TODO: Open edit modal
};

const viewExamResults = (exam) => {
  console.log('Viewing exam results for:', exam);
  // TODO: Navigate to results page
  router.push(`/instructor/exams/${exam.id}/results`);
};

const gradeSubmission = (submission) => {
  console.log('Grading submission:', submission);
  // TODO: Navigate to grading interface
  router.push(`/instructor/submissions/${submission.id}/grade`);
};
const viewSubmission = (submission) => {
  console.log('Viewing submission:', submission);
  // TODO: Navigate to submission details
  router.push(`/instructor/submissions/${submission.id}`);
};

// Learner Management methods
const viewLearnerProfile = (learner) => {
  console.log('Viewing profile for:', learner);
  // TODO: Navigate to learner profile
  router.push(`/instructor/learners/${learner.id}`);
};

const viewLearnerProgress = (learner) => {
  console.log('Viewing progress for:', learner);
  // TODO: Navigate to learner progress page
  router.push(`/instructor/learners/${learner.id}/progress`);
};

const messageLearner = (learner) => {
  console.log('Messaging learner:', learner);
  // TODO: Open message modal
};

// Progress Tracking methods
const generateProgressReport = () => {
  console.log('Generating progress report for period:', progressPeriod.value);
  // TODO: Generate and download report
};

const viewCourseAnalytics = (course) => {
  console.log('Viewing analytics for course:', course);
  // TODO: Navigate to course analytics
  router.push(`/instructor/courses/${course.id}/analytics`);
};

const exportProgress = (course) => {
  console.log('Exporting progress for course:', course);
  // TODO: Export progress data
};

// Communication & Support methods
const viewMessage = (message) => {
  console.log('Viewing message:', message);
  // TODO: Open message modal
};

const replyToMessage = (message) => {
  console.log('Replying to message:', message);
  // TODO: Open reply modal
};

const createDiscussion = () => {
  console.log('Creating new discussion');
  // TODO: Open create discussion modal
};

const viewDiscussion = (discussion) => {
  console.log('Viewing discussion:', discussion);
  // TODO: Navigate to discussion
  router.push(`/instructor/discussions/${discussion.id}`);
};

const moderateDiscussion = (discussion) => {
  console.log('Moderating discussion:', discussion);
  // TODO: Open moderation interface
};

const editAnnouncement = (announcement) => {
  console.log('Editing announcement:', announcement);
  // TODO: Open edit modal
};

const deleteAnnouncement = async (announcement) => {
  if (confirm('Delete this announcement?')) {
    try {
      const response = await fetch(`/api/instructor/announcements/${announcement.id}`, {
        method: 'DELETE',
        headers: {
          'Authorization': `Bearer ${authStore.token}`,
          'Content-Type': 'application/json'
        }
      });
      
      if (response.ok) {
        await fetchAnnouncements();
      }
    } catch (error) {
      console.error('Error deleting announcement:', error);
    }
  }
};

// Course Improvement methods
const requestFeedback = () => {
  console.log('Requesting feedback');
  // TODO: Open feedback request modal
};

const viewDetailedFeedback = () => {
  console.log('Viewing detailed feedback');
  // TODO: Navigate to feedback page
};

const viewDetailedAnalytics = () => {
  console.log('Viewing detailed analytics');
  // TODO: Navigate to analytics dashboard
};

const implementImprovements = () => {
  console.log('Implementing improvements');
  // TODO: Navigate to improvement interface
};

// Opportunity Sharing methods
const viewOpportunityDetails = (opportunity) => {
  console.log('Viewing opportunity details:', opportunity);
  // TODO: Navigate to opportunity details
};

const editSharedOpportunity = (opportunity) => {
  console.log('Editing shared opportunity:', opportunity);
  // TODO: Open edit modal
};

const revokeOpportunity = async (opportunity) => {
  if (confirm('Revoke this opportunity?')) {
    try {
      const response = await fetch(`/api/instructor/opportunities/${opportunity.id}`, {
        method: 'DELETE',
        headers: {
          'Authorization': `Bearer ${authStore.token}`,
          'Content-Type': 'application/json'
        }
      });
      
      if (response.ok) {
        await fetchSharedOpportunities();
      }
    } catch (error) {
      console.error('Error revoking opportunity:', error);
    }
  }
};

// Methods
const formatNumber = (num) => {
  const n = Number(num);
  if (Number.isNaN(n)) return '0';
  return n.toLocaleString();
};

const formatDate = (dateString) => {
  if (!dateString) return 'Never';
  return new Date(dateString).toLocaleDateString();
};

const formatShortDate = (iso) => {
  if (!iso) return '—';
  const d = new Date(iso);
  if (Number.isNaN(d.getTime())) return '—';
  return d.toLocaleDateString(undefined, { month: 'short', day: 'numeric' });
};

const formatSessionTime = (iso) => {
  if (!iso) return '—';
  const d = new Date(iso);
  if (Number.isNaN(d.getTime())) return '—';
  return d.toLocaleString(undefined, { weekday: 'short', hour: 'numeric', minute: '2-digit' });
};

const scrollToWorkspaceHash = () => {
  const raw = route.hash || '';
  const id = raw.startsWith('#') ? raw.slice(1) : raw;
  if (!id || !id.startsWith('workspace-')) return;
  nextTick(() => {
    requestAnimationFrame(() => {
      document.getElementById(id)?.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });
  });
};

watch(() => route.hash, () => scrollToWorkspaceHash());

onMounted(() => {
  setInterval(() => {
    currentDateTime.value = new Date().toLocaleString();
  }, 1000);

  loadAllData();
  scrollToWorkspaceHash();
  
  // Initialize counter animations
  nextTick(() => {
    animateCounters();
  });
});

// Counter animation
const animateCounters = () => {
  const counters = document.querySelectorAll('.counter');
  counters.forEach(counter => {
    const target = parseInt(counter.getAttribute('data-target'));
    const duration = 2000;
    const step = target / (duration / 16);
    let current = 0;
    
    const updateCounter = () => {
      current += step;
      if (current < target) {
        counter.textContent = Math.floor(current).toLocaleString();
        requestAnimationFrame(updateCounter);
      } else {
        counter.textContent = target.toLocaleString();
      }
    };
    
    updateCounter();
  });
};

// Close FAB when clicking outside
document.addEventListener('click', (e) => {
  if (!e.target.closest('.fab')) {
    showQuickActions.value = false;
  }
});
</script>

<style scoped>
.instructor-dashboard {
  padding: 22px 22px 48px;
  background: radial-gradient(1200px 600px at 10% -10%, rgba(99, 102, 241, 0.12), transparent),
    radial-gradient(900px 500px at 90% 0%, rgba(249, 115, 22, 0.1), transparent),
    #f1f5f9;
  min-height: 100dvh;
  max-width: 100%;
  overflow-x: hidden;
  box-sizing: border-box;
  color: #0f172a;
  position: relative;
}

.dash-top {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
  margin-bottom: 18px;
}

.greeting {
  flex: 1;
}

.title {
  font-size: 22px;
  font-weight: 800;
  letter-spacing: -0.02em;
}

.subtitle {
  margin-top: 4px;
  color: #64748b;
  font-size: 13px;
}

.top-right {
  display: flex;
  align-items: center;
  gap: 12px;
}

.pill {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 10px 16px;
  border-radius: 14px;
  background: #ffffff;
  border: 1px solid #e2e8f0;
  color: #475569;
  font-size: 12px;
  font-weight: 600;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
  transition: all 0.3s ease;
}

.pill:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.refresh-btn {
  width: 42px;
  height: 42px;
  border-radius: 12px;
  border: none;
  background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
  color: white;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 18px;
  transition: all 0.3s ease;
  box-shadow: 0 4px 12px rgba(249, 115, 22, 0.3);
}

.refresh-btn:hover {
  transform: scale(1.1);
  box-shadow: 0 6px 16px rgba(249, 115, 22, 0.4);
}

.refresh-btn.spinning .refresh-icon {
  animation: spin 1s linear infinite;
}

.quick-stats {
  margin-bottom: 24px;
}

.stats-container {
  display: grid;
  grid-template-columns: repeat(4, minmax(0, 1fr));
  gap: 16px;
}

.stat-card {
  background: linear-gradient(145deg, #ffffff 0%, #f8fafc 100%);
  border: 1px solid rgba(226, 232, 240, 0.95);
  border-radius: 20px;
  padding: 20px;
  box-shadow: 0 10px 30px rgba(15, 23, 42, 0.06);
  position: relative;
  overflow: hidden;
  cursor: pointer;
  transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

.stat-card:hover {
  transform: translateY(-8px) scale(1.02);
  box-shadow: 0 20px 40px rgba(15, 23, 42, 0.12);
}

.stat-card::before {
  content: '';
  position: absolute;
  left: 0;
  top: 0;
  right: 0;
  height: 4px;
  background: linear-gradient(90deg, #f97316, #fbbf24);
  opacity: 0.85;
}

.stat-kicker {
  color: #64748b;
  font-size: 12px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.stat-main {
  display: flex;
  align-items: flex-end;
  justify-content: space-between;
  margin-top: 12px;
}

.stat-number {
  font-size: 28px;
  font-weight: 900;
  letter-spacing: -0.02em;
  background: linear-gradient(135deg, #1e293b 0%, #475569 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.stat-unit {
  margin-left: 4px;
  font-size: 14px;
  font-weight: 700;
  color: #64748b;
}

.stat-change {
  font-size: 13px;
  font-weight: 700;
  display: flex;
  align-items: center;
  gap: 4px;
  padding: 4px 8px;
  border-radius: 8px;
}

.trend-up {
  color: #059669;
  background: rgba(5, 150, 105, 0.1);
}

.trend-down {
  color: #dc2626;
  background: rgba(220, 38, 38, 0.1);
}

.trend-stable {
  color: #64748b;
  background: rgba(100, 116, 139, 0.1);
}

.stat-glow {
  position: absolute;
  width: 100px;
  height: 100px;
  border-radius: 50%;
  filter: blur(40px);
  opacity: 0.15;
  top: -20px;
  right: -20px;
  transition: all 0.4s ease;
}

.stat-card:hover .stat-glow {
  opacity: 0.3;
  transform: scale(1.2);
}

/* Main Grid */
.dash-grid {
  display: grid;
  grid-template-columns: repeat(12, 1fr);
  gap: 20px;
}

.card {
  background: linear-gradient(145deg, #ffffff 0%, #f8fafc 100%);
  border: 1px solid rgba(226, 232, 240, 0.95);
  border-radius: 20px;
  padding: 24px;
  box-shadow: 0 10px 30px rgba(15, 23, 42, 0.06);
  transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

.card:hover {
  box-shadow: 0 20px 40px rgba(15, 23, 42, 0.12);
}

.weekly-progress {
  grid-column: span 5;
}

.next-lessons {
  grid-column: span 4;
}

.my-courses {
  grid-column: span 12;
}

.card-head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 20px;
}

.card-title {
  font-size: 16px;
  font-weight: 700;
  color: #1e293b;
}

.card-actions {
  display: flex;
  gap: 8px;
}

.action-btn {
  width: 32px;
  height: 32px;
  border-radius: 8px;
  border: 1px solid #e2e8f0;
  background: white;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 14px;
  transition: all 0.3s ease;
}

.action-btn:hover {
  background: #f1f5f9;
  transform: scale(1.1);
}

/* Chart */
.chart {
  display: flex;
  align-items: flex-end;
  justify-content: space-between;
  height: 120px;
  gap: 8px;
  margin-bottom: 20px;
}

.bar-wrapper {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 8px;
}

.bar {
  width: 100%;
  background: linear-gradient(180deg, #f97316 0%, #ea580c 100%);
  border-radius: 8px 8px 4px 4px;
  position: relative;
  transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
  animation: growUp 0.8s ease-out forwards;
  animation-delay: var(--delay);
  opacity: 0;
  transform: scaleY(0);
  transform-origin: bottom;
}

@keyframes growUp {
  from {
  opacity: 0;
  transform: scaleY(0);
  }
  to {
  opacity: 1;
  transform: scaleY(1);
  }
}

.bar:hover {
  filter: brightness(1.2);
  transform: scaleY(1.05);
}

.bar-highlight {
  background: linear-gradient(180deg, #fb923c 0%, #f97316 100%);
}

.bar-tooltip {
  position: absolute;
  top: -30px;
  left: 50%;
  transform: translateX(-50%);
  background: #1e293b;
  color: white;
  padding: 4px 8px;
  border-radius: 6px;
  font-size: 11px;
  font-weight: 600;
  white-space: nowrap;
  opacity: 0;
  transition: opacity 0.3s ease;
  pointer-events: none;
}

.bar:hover .bar-tooltip {
  opacity: 1;
}

.bar-label {
  font-size: 11px;
  font-weight: 600;
  color: #64748b;
}

.mini-stats {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 12px;
}

.mini {
  text-align: center;
  padding: 12px 8px;
  border-radius: 12px;
  background: #f8fafc;
  position: relative;
  overflow: hidden;
  transition: all 0.3s ease;
  cursor: pointer;
}

.mini:hover {
  background: #f1f5f9;
  transform: scale(1.05);
}

.mini-value {
  font-size: 18px;
  font-weight: 800;
  color: #1e293b;
}

.mini-label {
  font-size: 10px;
  font-weight: 600;
  color: #64748b;
  text-transform: uppercase;
  margin-top: 4px;
}

.mini-glow {
  position: absolute;
  width: 60px;
  height: 60px;
  border-radius: 50%;
  background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
  filter: blur(20px);
  opacity: 0;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  transition: opacity 0.3s ease;
}

.mini:hover .mini-glow {
  opacity: 0.1;
}

/* Lessons */
.lessons {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.lesson {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 14px;
  border-radius: 12px;
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  cursor: pointer;
  transition: all 0.3s ease;
  position: relative;
}

.lesson:hover {
  background: white;
  border-color: #f97316;
  transform: translateX(4px);
  box-shadow: 0 4px 12px rgba(249, 115, 22, 0.15);
}

.lesson-urgent {
  border-color: #f59e0b;
  background: rgba(245, 158, 11, 0.05);
}

.lesson-urgent:hover {
  background: rgba(245, 158, 11, 0.1);
}

.lesson-left {
  flex: 1;
  min-width: 0;
}

.lesson-title {
  font-size: 14px;
  font-weight: 700;
  color: #1e293b;
  margin-bottom: 4px;
}

.lesson-sub {
  font-size: 12px;
  color: #64748b;
}

.lesson-right {
  text-align: right;
  flex-shrink: 0;
}

.lesson-time {
  font-size: 13px;
  font-weight: 700;
  color: #1e293b;
}

.lesson-dur {
  font-size: 11px;
  color: #64748b;
  margin-top: 2px;
}

.lesson-status {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: #cbd5e1;
  flex-shrink: 0;
}

.status-soon {
  background: #f59e0b;
  animation: pulse 2s infinite;
}

@keyframes pulse {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.5; }
}

.link-btn {
  background: none;
  border: none;
  color: #f97316;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  padding: 6px 12px;
  border-radius: 8px;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  gap: 4px;
}

.link-btn:hover {
  background: rgba(249, 115, 22, 0.1);
}

.arrow {
  transition: transform 0.3s ease;
}

.link-btn:hover .arrow {
  transform: translateX(4px);
}

/* Courses */
.tabs {
  display: flex;
  gap: 4px;
  background: #f1f5f9;
  padding: 4px;
  border-radius: 10px;
}

.tab {
  padding: 8px 16px;
  border: none;
  background: transparent;
  color: #64748b;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  border-radius: 8px;
  transition: all 0.3s ease;
}

.tab:hover {
  color: #1e293b;
}

.tab.active {
  background: white;
  color: #f97316;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.course-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 20px;
}

.course-tile {
  background: white;
  border: 1px solid #e2e8f0;
  border-radius: 16px;
  padding: 20px;
  cursor: pointer;
  transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
  position: relative;
  overflow: hidden;
}

.course-tile:hover {
  transform: translateY(-8px);
  box-shadow: 0 20px 40px rgba(15, 23, 42, 0.15);
  border-color: #f97316;
}

.course-gradient {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 4px;
  opacity: 0.8;
}

.course-top {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  margin-bottom: 12px;
}

.course-name {
  font-size: 16px;
  font-weight: 700;
  color: #1e293b;
  line-height: 1.4;
}

.chip {
  padding: 4px 10px;
  border-radius: 20px;
  font-size: 11px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.status-published {
  background: rgba(16, 185, 129, 0.1);
  color: #059669;
}

.status-draft {
  background: rgba(245, 158, 11, 0.1);
  color: #d97706;
}

.status-archived {
  background: rgba(100, 116, 139, 0.1);
  color: #64748b;
}

.course-desc {
  font-size: 13px;
  color: #64748b;
  line-height: 1.5;
  margin-bottom: 16px;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.course-bottom {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.progress {
  display: flex;
  align-items: center;
  gap: 12px;
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
  border-radius: 4px;
  transition: width 0.6s ease;
}

.progress-text {
  font-size: 13px;
  font-weight: 700;
  color: #1e293b;
  min-width: 45px;
}

.meta {
  display: flex;
  gap: 16px;
}

.meta-item {
  font-size: 12px;
  color: #64748b;
  display: flex;
  align-items: center;
  gap: 4px;
}

.course-hover-effect {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: linear-gradient(135deg, rgba(249, 115, 22, 0.05) 0%, rgba(234, 88, 12, 0.05) 100%);
  opacity: 0;
  transition: opacity 0.3s ease;
  pointer-events: none;
}

.course-tile:hover .course-hover-effect {
  opacity: 1;
}

/* Empty States */
.empty-state {
  text-align: center;
  padding: 40px 20px;
  color: #64748b;
}

.empty-state p {
  font-size: 14px;
  margin: 0;
}

/* FAB */
.fab {
  position: fixed;
  bottom: 32px;
  right: 32px;
  width: 56px;
  height: 56px;
  border-radius: 50%;
  border: none;
  background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
  color: white;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 24px;
  box-shadow: 0 8px 24px rgba(249, 115, 22, 0.4);
  transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
  z-index: 100;
}

.fab:hover {
  transform: scale(1.1);
  box-shadow: 0 12px 32px rgba(249, 115, 22, 0.5);
}

.fab.active {
  transform: rotate(45deg);
}

.fab-menu {
  position: absolute;
  bottom: 70px;
  right: 0;
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.fab-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px 20px;
  border-radius: 12px;
  border: none;
  background: white;
  color: #1e293b;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  transition: all 0.3s ease;
  white-space: nowrap;
}

.fab-item:hover {
  background: #f1f5f9;
  transform: translateX(-4px);
}

/* Loading Overlay */
.loading-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(255, 255, 255, 0.95);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.loading-spinner {
  text-align: center;
}

.spinner {
  width: 60px;
  height: 60px;
  border: 5px solid #f3f3f3;
  border-top: 5px solid #f97316;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin: 0 auto 20px;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.loading-spinner p {
  font-size: 16px;
  font-weight: 600;
  color: #64748b;
  margin: 0;
}

/* Responsive Design */
@media (max-width: 1200px) {
  .stats-container {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
  
  .dash-grid {
    grid-template-columns: 1fr;
  }
  
  .weekly-progress,
  .next-lessons,
  .my-courses {
    grid-column: span 1;
  }
}

@media (max-width: 768px) {
  .instructor-dashboard {
    padding: 16px 16px 32px;
  }

  .dash-top {
    flex-direction: column;
    align-items: flex-start;
    gap: 12px;
  }

  .top-right {
    width: 100%;
    justify-content: space-between;
  }

  .stats-container {
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 12px;
  }

  .stat-card {
    padding: 16px;
  }

  .stat-number {
    font-size: 24px;
  }

  .chart {
    height: 100px;
  }

  .mini-stats {
    grid-template-columns: repeat(2, 1fr);
  }

  .course-grid {
    grid-template-columns: 1fr;
  }

  .fab {
    bottom: 24px;
    right: 24px;
    width: 48px;
    height: 48px;
    font-size: 20px;
  }

  .fab-menu {
    bottom: 60px;
  }
}

@media (max-width: 480px) {
  .stats-container {
    grid-template-columns: 1fr;
  }

  .mini-stats {
    grid-template-columns: 1fr;
  }

  .tabs {
    flex-wrap: wrap;
  }

  .tab {
    flex: 1;
    min-width: 80px;
    justify-content: center;
  }
}
</style>
