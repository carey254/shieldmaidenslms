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
        <div class="title">Good Morning, {{ user?.name || 'Instructor' }}!</div>
        <div class="subtitle">Here’s what’s happening today.</div>
      </div>

      <div class="top-right">
        <div class="pill">
          <span class="pill-icon">⏱</span>
          <span class="pill-text">{{ currentDateTime }}</span>
        </div>
      </div>
    </div>

    <!-- Quick Stats (dark cards) -->
    <section class="quick-stats">
      <div class="stats-container">
        <div v-for="(stat, index) in animatedStats" :key="index" class="stat-card">
          <div class="stat-kicker">{{ stat.label }}</div>
          <div class="stat-main">
            <div class="stat-number">
              {{ formatNumber(stat.value) }}<span class="stat-unit">{{ stat.unit }}</span>
            </div>
            <div class="stat-change">{{ stat.change }}</div>
          </div>
        </div>
      </div>
    </section>

    <!-- Main grid -->
    <div class="dash-grid">
      <section class="card weekly-progress">
        <div class="card-head">
          <div class="card-title">Weekly Progress</div>
          <button class="icon-btn" type="button" title="Calendar">🗓</button>
        </div>
        <div class="chart">
          <div v-for="(v, i) in weeklyBars" :key="i" class="bar" :style="{ height: v + '%' }" />
        </div>
        <div class="mini-stats">
          <div class="mini" v-for="(m, idx) in miniStats" :key="idx">
            <div class="mini-value">{{ m.value }}</div>
            <div class="mini-label">{{ m.label }}</div>
          </div>
        </div>
      </section>

      <section class="card next-lessons">
        <div class="card-head">
          <div class="card-title">My next lessons</div>
          <button class="link-btn" type="button" @click="goToCourses">View all</button>
        </div>
        <div class="lessons">
          <div v-for="(l, idx) in nextLessons" :key="idx" class="lesson">
            <div class="lesson-left">
              <div class="lesson-title">{{ l.title }}</div>
              <div class="lesson-sub">{{ l.tag }}</div>
            </div>
            <div class="lesson-right">
              <div class="lesson-time">{{ l.time }}</div>
              <div class="lesson-dur">{{ l.duration }}</div>
            </div>
          </div>
        </div>
      </section>

      <section class="card my-courses">
        <div class="card-head">
          <div class="card-title">My courses</div>
          <div class="tabs">
            <button class="tab" :class="{ active: courseTab === 'all' }" @click="courseTab = 'all'">All</button>
            <button class="tab" :class="{ active: courseTab === 'mandatory' }" @click="courseTab = 'mandatory'">Mandatory</button>
            <button class="tab" :class="{ active: courseTab === 'completed' }" @click="courseTab = 'completed'">Completed</button>
          </div>
        </div>

        <div class="course-grid">
          <div v-for="course in filteredMyCourses" :key="course.id" class="course-tile" @click="manageContent(course)">
            <div class="course-top">
              <div class="course-name">{{ course.title }}</div>
              <div class="chip">{{ course.status || 'published' }}</div>
            </div>
            <div class="course-desc">{{ course.description || 'Course details will appear here.' }}</div>
            <div class="course-bottom">
              <div class="progress">
                <div class="progress-bar">
                  <div class="progress-fill" :style="{ width: (course.completion_rate || 0) + '%' }" />
                </div>
                <div class="progress-text">{{ course.completion_rate || 0 }}%</div>
              </div>
              <div class="meta">
                <span>{{ course.enrolled_count || 0 }} students</span>
                <span>{{ course.modules_count || 0 }} modules</span>
              </div>
            </div>
          </div>
          <div v-if="filteredMyCourses.length === 0" class="empty">
            No courses yet. Ask admin to assign courses.
          </div>
        </div>
      </section>
    </div>

    <!-- Legacy sections (kept for now, hidden) -->
    <div class="management-grid legacy" aria-hidden="true">
      
      <!-- 1. Course Content Management -->
      <section class="management-section course-content-management">
        <div class="section-header">
          <h2><i class="fas fa-book-open"></i> Course Content Management</h2>
          <div class="header-actions">
            <button @click="showCreateCourseModal = true" class="btn btn-primary">
              <i class="fas fa-plus"></i> Create Course
            </button>
            <button @click="refreshCourses" class="btn btn-secondary">
              <i class="fas fa-sync-alt" :class="{ 'fa-spin': refreshingCourses }"></i> Refresh
            </button>
          </div>
        </div>

        <div class="courses-grid">
          <div v-for="course in myCourses" :key="course.id" class="course-card">
            <div class="course-header">
              <h3>{{ course.title }}</h3>
              <span class="course-status" :class="course.status">{{ course.status }}</span>
            </div>
            <div class="course-stats">
              <div class="stat">
                <span class="stat-label">Modules</span>
                <span class="stat-value">{{ course.modules_count || 0 }}</span>
              </div>
              <div class="stat">
                <span class="stat-label">Students</span>
                <span class="stat-value">{{ course.enrolled_count || 0 }}</span>
              </div>
              <div class="stat">
                <span class="stat-label">Completion</span>
                <span class="stat-value">{{ course.completion_rate || 0 }}%</span>
              </div>
            </div>
            <div class="course-actions">
              <button @click="manageContent(course)" class="btn btn-sm btn-primary">
                <i class="fas fa-edit"></i> Manage Content
              </button>
              <button @click="uploadMaterials(course)" class="btn btn-sm btn-success">
                <i class="fas fa-upload"></i> Upload Materials
              </button>
              <button @click="viewAnalytics(course)" class="btn btn-sm btn-info">
                <i class="fas fa-chart-bar"></i> Analytics
              </button>
            </div>
          </div>
        </div>
      </section>

      <!-- 2. Assessment Management -->
      <section class="management-section assessment-management">
        <div class="section-header">
          <h2><i class="fas fa-clipboard-check"></i> Assessment Management</h2>
          <div class="header-actions">
            <button @click="showCreateAssessmentModal = true" class="btn btn-primary">
              <i class="fas fa-plus"></i> Create Assessment
            </button>
            <button @click="refreshAssessments" class="btn btn-secondary">
              <i class="fas fa-sync-alt" :class="{ 'fa-spin': refreshingAssessments }"></i> Refresh
            </button>
          </div>
        </div>

        <div class="tabs">
          <button @click="activeAssessmentTab = 'quizzes'" class="tab-btn" :class="{ active: activeAssessmentTab === 'quizzes' }">
            Quizzes
          </button>
          <button @click="activeAssessmentTab = 'assignments'" class="tab-btn" :class="{ active: activeAssessmentTab === 'assignments' }">
            Assignments
          </button>
          <button @click="activeAssessmentTab = 'exams'" class="tab-btn" :class="{ active: activeAssessmentTab === 'exams' }">
            Exams
          </button>
          <button @click="activeAssessmentTab = 'grading'" class="tab-btn" :class="{ active: activeAssessmentTab === 'grading' }">
            Grading Queue
          </button>
        </div>

        <div v-if="activeAssessmentTab === 'quizzes'" class="assessments-list">
          <div v-for="quiz in quizzes" :key="quiz.id" class="assessment-card">
            <div class="assessment-header">
              <h4>{{ quiz.title }}</h4>
              <span class="assessment-status" :class="quiz.status">{{ quiz.status }}</span>
            </div>
            <div class="assessment-meta">
              <span class="course">
                <i class="fas fa-book"></i>
                {{ quiz.course_title }}
              </span>
              <span class="questions">
                <i class="fas fa-question-circle"></i>
                {{ quiz.questions_count }} questions
              </span>
              <span class="duration">
                <i class="fas fa-clock"></i>
                {{ quiz.duration }} minutes
              </span>
            </div>
            <div class="assessment-stats">
              <div class="stat">
                <span class="stat-label">Submissions</span>
                <span class="stat-value">{{ quiz.submissions_count || 0 }}</span>
              </div>
              <div class="stat">
                <span class="stat-label">Avg Score</span>
                <span class="stat-value">{{ quiz.average_score || 0 }}%</span>
              </div>
            </div>
            <div class="assessment-actions">
              <button @click="editQuiz(quiz)" class="btn btn-sm btn-secondary">
                <i class="fas fa-edit"></i> Edit
              </button>
              <button @click="viewSubmissions(quiz)" class="btn btn-sm btn-primary">
                <i class="fas fa-eye"></i> View Submissions
              </button>
              <button @click="gradeQuiz(quiz)" class="btn btn-sm btn-success">
                <i class="fas fa-graduation-cap"></i> Grade
              </button>
            </div>
          </div>
        </div>

        <div v-if="activeAssessmentTab === 'assignments'" class="assessments-list">
          <div v-for="assignment in assignments" :key="assignment.id" class="assessment-card">
            <div class="assessment-header">
              <h4>{{ assignment.title }}</h4>
              <span class="assessment-status" :class="assignment.status">{{ assignment.status }}</span>
            </div>
            <div class="assessment-meta">
              <span class="course">
                <i class="fas fa-book"></i>
                {{ assignment.course_title }}
              </span>
              <span class="deadline">
                <i class="fas fa-calendar"></i>
                Due: {{ formatDate(assignment.deadline) }}
              </span>
              <span class="type">
                <i class="fas fa-file-alt"></i>
                {{ assignment.type }}
              </span>
            </div>
            <div class="assessment-stats">
              <div class="stat">
                <span class="stat-label">Submitted</span>
                <span class="stat-value">{{ assignment.submitted_count || 0 }}</span>
              </div>
              <div class="stat">
                <span class="stat-label">Graded</span>
                <span class="stat-value">{{ assignment.graded_count || 0 }}</span>
              </div>
            </div>
            <div class="assessment-actions">
              <button @click="editAssignment(assignment)" class="btn btn-sm btn-secondary">
                <i class="fas fa-edit"></i> Edit
              </button>
              <button @click="viewSubmissions(assignment)" class="btn btn-sm btn-primary">
                <i class="fas fa-eye"></i> View Submissions
              </button>
              <button @click="gradeAssignment(assignment)" class="btn btn-sm btn-success">
                <i class="fas fa-graduation-cap"></i> Grade
              </button>
            </div>
          </div>
        </div>

        <div v-if="activeAssessmentTab === 'exams'" class="assessments-list">
          <div v-for="exam in exams" :key="exam.id" class="assessment-card">
            <div class="assessment-header">
              <h4>{{ exam.title }}</h4>
              <span class="assessment-status" :class="exam.status">{{ exam.status }}</span>
            </div>
            <div class="assessment-meta">
              <span class="course">
                <i class="fas fa-book"></i>
                {{ exam.course_title }}
              </span>
              <span class="date">
                <i class="fas fa-calendar"></i>
                {{ formatDate(exam.exam_date) }}
              </span>
              <span class="duration">
                <i class="fas fa-clock"></i>
                {{ exam.duration }} hours
              </span>
            </div>
            <div class="assessment-actions">
              <button @click="editExam(exam)" class="btn btn-sm btn-secondary">
                <i class="fas fa-edit"></i> Edit
              </button>
              <button @click="viewExamResults(exam)" class="btn btn-sm btn-primary">
                <i class="fas fa-chart-bar"></i> View Results
              </button>
            </div>
          </div>
        </div>

        <div v-if="activeAssessmentTab === 'grading'" class="grading-queue">
          <div v-for="submission in gradingQueue" :key="submission.id" class="submission-card">
            <div class="submission-header">
              <h4>{{ submission.assessment_title }}</h4>
              <span class="submission-priority" :class="submission.priority">{{ submission.priority }}</span>
            </div>
            <div class="submission-student">
              <i class="fas fa-user"></i>
              {{ submission.student_name }}
            </div>
            <div class="submission-meta">
              <span class="submitted">
                <i class="fas fa-calendar"></i>
                Submitted: {{ formatDate(submission.submitted_at) }}
              </span>
              <span class="course">
                <i class="fas fa-book"></i>
                {{ submission.course_title }}
              </span>
            </div>
            <div class="submission-actions">
              <button @click="gradeSubmission(submission)" class="btn btn-sm btn-primary">
                <i class="fas fa-graduation-cap"></i> Grade Now
              </button>
              <button @click="viewSubmission(submission)" class="btn btn-sm btn-secondary">
                <i class="fas fa-eye"></i> View
              </button>
            </div>
          </div>
        </div>
      </section>

      <!-- 3. Learner Management -->
      <section class="management-section learner-management">
        <div class="section-header">
          <h2><i class="fas fa-users"></i> Learner Management</h2>
          <div class="header-actions">
            <button @click="refreshLearners" class="btn btn-secondary">
              <i class="fas fa-sync-alt" :class="{ 'fa-spin': refreshingLearners }"></i> Refresh
            </button>
          </div>
        </div>

        <div class="learner-controls">
          <div class="search-bar">
            <i class="fas fa-search"></i>
            <input v-model="learnerSearchQuery" type="text" placeholder="Search learners by name or email...">
          </div>
          <div class="filter-controls">
            <select v-model="courseFilter" class="filter-select">
              <option value="">All Courses</option>
              <option v-for="course in myCourses" :key="course.id" :value="course.id">
                {{ course.title }}
              </option>
            </select>
            <select v-model="progressFilter" class="filter-select">
              <option value="">All Progress</option>
              <option value="not-started">Not Started</option>
              <option value="in-progress">In Progress</option>
              <option value="completed">Completed</option>
            </select>
          </div>
        </div>

        <div class="learners-table-container">
          <table class="learners-table">
            <thead>
              <tr>
                <th>Learner</th>
                <th>Enrolled Courses</th>
                <th>Avg Progress</th>
                <th>Last Activity</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="learner in filteredLearners" :key="learner.id">
                <td>
                  <div class="learner-info">
                    <div class="learner-avatar">{{ learner.name.charAt(0) }}</div>
                    <div>
                      <div class="learner-name">{{ learner.name }}</div>
                      <div class="learner-email">{{ learner.email }}</div>
                    </div>
                  </div>
                </td>
                <td>
                  <div class="course-badges">
                    <span v-for="course in learner.courses" :key="course.id" class="course-badge">
                      {{ course.title.substring(0, 15) }}...
                    </span>
                  </div>
                </td>
                <td>
                  <div class="progress-bar">
                    <div class="progress-fill" :style="{ width: learner.avg_progress + '%' }"></div>
                  </div>
                  <span class="progress-text">{{ learner.avg_progress }}%</span>
                </td>
                <td>{{ formatDate(learner.last_activity) }}</td>
                <td>
                  <div class="action-buttons">
                    <button @click="viewLearnerProfile(learner)" class="btn-icon btn-view" title="View Profile">
                      <i class="fas fa-user"></i>
                    </button>
                    <button @click="viewLearnerProgress(learner)" class="btn-icon btn-progress" title="View Progress">
                      <i class="fas fa-chart-line"></i>
                    </button>
                    <button @click="messageLearner(learner)" class="btn-icon btn-message" title="Send Message">
                      <i class="fas fa-envelope"></i>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </section>

      <!-- 4. Progress Tracking -->
      <section class="management-section progress-tracking">
        <div class="section-header">
          <h2><i class="fas fa-chart-line"></i> Progress Tracking</h2>
          <div class="header-actions">
            <select v-model="progressPeriod" class="filter-select">
              <option value="7">Last 7 Days</option>
              <option value="30">Last 30 Days</option>
              <option value="90">Last 90 Days</option>
            </select>
            <button @click="generateProgressReport" class="btn btn-primary">
              <i class="fas fa-download"></i> Generate Report
            </button>
          </div>
        </div>

        <div class="progress-overview">
          <div class="overview-cards">
            <div class="overview-card">
              <h3>Course Completion Rates</h3>
              <div class="chart-placeholder">
                <i class="fas fa-chart-bar"></i>
                <p>Completion rates by course</p>
              </div>
            </div>
            <div class="overview-card">
              <h3>Student Engagement</h3>
              <div class="chart-placeholder">
                <i class="fas fa-chart-area"></i>
                <p>Activity over time</p>
              </div>
            </div>
            <div class="overview-card">
              <h3>Assessment Performance</h3>
              <div class="chart-placeholder">
                <i class="fas fa-chart-line"></i>
                <p>Score distribution</p>
              </div>
            </div>
          </div>
        </div>

        <div class="course-progress-list">
          <h3>Course Progress Details</h3>
          <div v-for="course in myCourses" :key="course.id" class="course-progress-card">
            <div class="course-progress-header">
              <h4>{{ course.title }}</h4>
              <span class="completion-rate">{{ course.completion_rate || 0 }}% Complete</span>
            </div>
            <div class="progress-stats">
              <div class="stat">
                <span class="stat-label">Total Students</span>
                <span class="stat-value">{{ course.enrolled_count || 0 }}</span>
              </div>
              <div class="stat">
                <span class="stat-label">Completed</span>
                <span class="stat-value">{{ course.completed_count || 0 }}</span>
              </div>
              <div class="stat">
                <span class="stat-label">In Progress</span>
                <span class="stat-value">{{ course.in_progress_count || 0 }}</span>
              </div>
              <div class="stat">
                <span class="stat-label">Not Started</span>
                <span class="stat-value">{{ course.not_started_count || 0 }}</span>
              </div>
            </div>
            <div class="progress-actions">
              <button @click="viewCourseAnalytics(course)" class="btn btn-sm btn-primary">
                <i class="fas fa-chart-bar"></i> View Analytics
              </button>
              <button @click="exportProgress(course)" class="btn btn-sm btn-secondary">
                <i class="fas fa-download"></i> Export Progress
              </button>
            </div>
          </div>
        </div>
      </section>

      <!-- 5. Communication & Support -->
      <section class="management-section communication-support">
        <div class="section-header">
          <h2><i class="fas fa-comments"></i> Communication & Support</h2>
          <div class="header-actions">
            <button @click="showComposeMessageModal = true" class="btn btn-primary">
              <i class="fas fa-envelope"></i> Compose Message
            </button>
            <button @click="createDiscussion" class="btn btn-success">
              <i class="fas fa-comments"></i> Create Discussion
            </button>
          </div>
        </div>

        <div class="communication-tabs">
          <button @click="activeCommTab = 'messages'" class="tab-btn" :class="{ active: activeCommTab === 'messages' }">
            Messages
          </button>
          <button @click="activeCommTab = 'discussions'" class="tab-btn" :class="{ active: activeCommTab === 'discussions' }">
            Discussions
          </button>
          <button @click="activeCommTab = 'announcements'" class="tab-btn" :class="{ active: activeCommTab === 'announcements' }">
            Announcements
          </button>
        </div>

        <div v-if="activeCommTab === 'messages'" class="messages-list">
          <div v-for="message in messages" :key="message.id" class="message-card">
            <div class="message-header">
              <div class="message-sender">
                <div class="sender-avatar">{{ message.sender_name.charAt(0) }}</div>
                <div>
                  <div class="sender-name">{{ message.sender_name }}</div>
                  <div class="sender-role">{{ message.sender_role }}</div>
                </div>
              </div>
              <span class="message-time">{{ formatDate(message.created_at) }}</span>
            </div>
            <div class="message-subject">{{ message.subject }}</div>
            <div class="message-preview">{{ message.content.substring(0, 100) }}...</div>
            <div class="message-actions">
              <button @click="viewMessage(message)" class="btn btn-sm btn-primary">
                <i class="fas fa-eye"></i> View
              </button>
              <button @click="replyToMessage(message)" class="btn btn-sm btn-secondary">
                <i class="fas fa-reply"></i> Reply
              </button>
            </div>
          </div>
        </div>

        <div v-if="activeCommTab === 'discussions'" class="discussions-list">
          <div v-for="discussion in discussions" :key="discussion.id" class="discussion-card">
            <div class="discussion-header">
              <h4>{{ discussion.title }}</h4>
              <span class="discussion-status" :class="discussion.status">{{ discussion.status }}</span>
            </div>
            <div class="discussion-meta">
              <span class="course">
                <i class="fas fa-book"></i>
                {{ discussion.course_title }}
              </span>
              <span class="participants">
                <i class="fas fa-users"></i>
                {{ discussion.participants_count }} participants
              </span>
              <span class="replies">
                <i class="fas fa-comment"></i>
                {{ discussion.replies_count }} replies
              </span>
            </div>
            <div class="discussion-preview">{{ discussion.last_message.substring(0, 100) }}...</div>
            <div class="discussion-actions">
              <button @click="viewDiscussion(discussion)" class="btn btn-sm btn-primary">
                <i class="fas fa-comments"></i> Join Discussion
              </button>
              <button @click="moderateDiscussion(discussion)" class="btn btn-sm btn-secondary">
                <i class="fas fa-shield-alt"></i> Moderate
              </button>
            </div>
          </div>
        </div>

        <div v-if="activeCommTab === 'announcements'" class="announcements-list">
          <div v-for="announcement in announcements" :key="announcement.id" class="announcement-card">
            <div class="announcement-header">
              <h4>{{ announcement.title }}</h4>
              <span class="announcement-audience">{{ announcement.audience }}</span>
            </div>
            <div class="announcement-content">{{ announcement.content }}</div>
            <div class="announcement-meta">
              <span class="date">
                <i class="fas fa-calendar"></i>
                {{ formatDate(announcement.created_at) }}
              </span>
              <span class="views">
                <i class="fas fa-eye"></i>
                {{ announcement.views_count }} views
              </span>
            </div>
            <div class="announcement-actions">
              <button @click="editAnnouncement(announcement)" class="btn btn-sm btn-secondary">
                <i class="fas fa-edit"></i> Edit
              </button>
              <button @click="deleteAnnouncement(announcement)" class="btn btn-sm btn-danger">
                <i class="fas fa-trash"></i> Delete
              </button>
            </div>
          </div>
        </div>
      </section>

      <!-- 6. Course Improvement -->
      <section class="management-section course-improvement">
        <div class="section-header">
          <h2><i class="fas fa-lightbulb"></i> Course Improvement</h2>
          <div class="header-actions">
            <button @click="requestFeedback" class="btn btn-primary">
              <i class="fas fa-comment-dots"></i> Request Feedback
            </button>
            <button @click="viewAnalytics" class="btn btn-secondary">
              <i class="fas fa-chart-line"></i> View Analytics
            </button>
          </div>
        </div>

        <div class="improvement-cards">
          <div class="improvement-card">
            <h3>Student Feedback</h3>
            <div class="feedback-summary">
              <div class="rating">
                <span class="rating-score">4.2</span>
                <div class="stars">
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star-half-alt"></i>
                </div>
              </div>
              <p class="feedback-count">Based on 156 reviews</p>
            </div>
            <div class="feedback-highlights">
              <div class="highlight positive">
                <i class="fas fa-thumbs-up"></i>
                <span>Great content structure</span>
              </div>
              <div class="highlight negative">
                <i class="fas fa-thumbs-down"></i>
                <span>More practical examples needed</span>
              </div>
            </div>
            <button @click="viewDetailedFeedback" class="btn btn-sm btn-primary">
              View Detailed Feedback
            </button>
          </div>

          <div class="improvement-card">
            <h3>Performance Insights</h3>
            <div class="insights-list">
              <div class="insight">
                <i class="fas fa-chart-line"></i>
                <div>
                  <div class="insight-title">Module 3 has low completion</div>
                  <div class="insight-desc">Only 45% of students complete this module</div>
                </div>
              </div>
              <div class="insight">
                <i class="fas fa-clock"></i>
                <div>
                  <div class="insight-title">Quiz 2 takes too long</div>
                  <div class="insight-desc">Average completion time: 45 minutes</div>
                </div>
              </div>
              <div class="insight">
                <i class="fas fa-users"></i>
                <div>
                  <div class="insight-title">High engagement in discussions</div>
                  <div class="insight-desc">89% participation rate</div>
                </div>
              </div>
            </div>
            <button @click="viewDetailedAnalytics" class="btn btn-sm btn-primary">
              View Analytics Dashboard
            </button>
          </div>

          <div class="improvement-card">
            <h3>Recommended Actions</h3>
            <div class="recommendations">
              <div class="recommendation">
                <i class="fas fa-edit"></i>
                <span>Revise Module 3 content for better clarity</span>
              </div>
              <div class="recommendation">
                <i class="fas fa-plus"></i>
                <span>Add more practical examples to theory sections</span>
              </div>
              <div class="recommendation">
                <i class="fas fa-video"></i>
                <span>Create supplementary video tutorials</span>
              </div>
              <div class="recommendation">
                <i class="fas fa-poll"></i>
                <span>Conduct mid-course survey for feedback</span>
              </div>
            </div>
            <button @click="implementImprovements" class="btn btn-sm btn-success">
              Start Improvements
            </button>
          </div>
        </div>
      </section>

      <!-- 7. Limited Opportunity Sharing -->
      <section class="management-section opportunity-sharing">
        <div class="section-header">
          <h2><i class="fas fa-briefcase"></i> Opportunity Sharing</h2>
          <div class="header-actions">
            <button @click="showShareOpportunityModal = true" class="btn btn-primary">
              <i class="fas fa-share"></i> Share Opportunity
            </button>
          </div>
        </div>

        <div class="opportunities-shared">
          <div v-for="opportunity in sharedOpportunities" :key="opportunity.id" class="shared-opportunity-card">
            <div class="opportunity-header">
              <h4>{{ opportunity.title }}</h4>
              <span class="opportunity-type" :class="opportunity.type">{{ opportunity.type }}</span>
            </div>
            <div class="opportunity-description">{{ opportunity.description }}</div>
            <div class="opportunity-meta">
              <span class="deadline">
                <i class="fas fa-calendar"></i>
                Deadline: {{ formatDate(opportunity.deadline) }}
              </span>
              <span class="shared-with">
                <i class="fas fa-users"></i>
                Shared with: {{ opportunity.shared_with_count }} students
              </span>
            </div>
            <div class="opportunity-stats">
              <div class="stat">
                <span class="stat-label">Views</span>
                <span class="stat-value">{{ opportunity.views_count }}</span>
              </div>
              <div class="stat">
                <span class="stat-label">Applications</span>
                <span class="stat-value">{{ opportunity.applications_count }}</span>
              </div>
            </div>
            <div class="opportunity-actions">
              <button @click="viewOpportunityDetails(opportunity)" class="btn btn-sm btn-primary">
                <i class="fas fa-eye"></i> View Details
              </button>
              <button @click="editSharedOpportunity(opportunity)" class="btn btn-sm btn-secondary">
                <i class="fas fa-edit"></i> Edit
              </button>
              <button @click="revokeOpportunity(opportunity)" class="btn btn-sm btn-danger">
                <i class="fas fa-times"></i> Revoke
              </button>
            </div>
          </div>
        </div>
      </section>
    </div>

    <!-- Modals would go here -->
    <!-- Create Course Modal, Create Assessment Modal, Compose Message Modal, etc. -->
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useAuthStore } from '@/stores/auth';
import { useRouter } from 'vue-router';

const authStore = useAuthStore();
const user = computed(() => authStore.user);
const router = useRouter();

// Loading state
const loading = ref(true);

// Current date/time
const currentDateTime = ref(new Date().toLocaleString());

const courseTab = ref('all');

const weeklyBars = ref([18, 38, 44, 54, 34, 42, 78]);

const miniStats = ref([
  { label: 'Courses', value: '8' },
  { label: 'In progress', value: '5' },
  { label: 'Tests', value: '3' },
  { label: 'Avg score', value: '82%' },
]);

const nextLessons = ref([
  { title: 'User Research for Product Teams', tag: 'Mandatory', time: '11:30 AM', duration: '15 min' },
  { title: 'Design Systems in Practice', tag: 'Recommended', time: '10:30 AM', duration: '20 min' },
  { title: 'Product Thinking for Designers', tag: 'Completed', time: '10:00 AM', duration: '17 min' },
  { title: 'Designing for Accessibility', tag: 'Completed', time: '9:00 AM', duration: '17 min' },
]);

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

// API Data Fetching Methods for Instructor Dashboard
const fetchDashboardStats = async () => {
  try {
    const response = await fetch('/api/instructor/dashboard/stats', {
      headers: {
        'Authorization': `Bearer ${authStore.token}`,
        'Content-Type': 'application/json'
      }
    });
    
    if (response.ok) {
      const data = await response.json();
      animatedStats.value = data.stats || animatedStats.value;
    }
  } catch (error) {
    console.error('Error fetching instructor dashboard stats:', error);
  }
};

const fetchMyCourses = async () => {
  try {
    const response = await fetch('/api/instructor/courses', {
      headers: {
        'Authorization': `Bearer ${authStore.token}`,
        'Content-Type': 'application/json'
      }
    });
    
    if (response.ok) {
      const data = await response.json();
      myCourses.value = data.courses || [];
    }
  } catch (error) {
    console.error('Error fetching instructor courses:', error);
  }
};

const fetchQuizzes = async () => {
  try {
    const response = await fetch('/api/instructor/quizzes', {
      headers: {
        'Authorization': `Bearer ${authStore.token}`,
        'Content-Type': 'application/json'
      }
    });
    
    if (response.ok) {
      const data = await response.json();
      quizzes.value = data.quizzes || [];
    }
  } catch (error) {
    console.error('Error fetching quizzes:', error);
  }
};

const fetchAssignments = async () => {
  try {
    const response = await fetch('/api/instructor/assignments', {
      headers: {
        'Authorization': `Bearer ${authStore.token}`,
        'Content-Type': 'application/json'
      }
    });
    
    if (response.ok) {
      const data = await response.json();
      assignments.value = data.assignments || [];
    }
  } catch (error) {
    console.error('Error fetching assignments:', error);
  }
};

const fetchExams = async () => {
  try {
    const response = await fetch('/api/instructor/exams', {
      headers: {
        'Authorization': `Bearer ${authStore.token}`,
        'Content-Type': 'application/json'
      }
    });
    
    if (response.ok) {
      const data = await response.json();
      exams.value = data.exams || [];
    }
  } catch (error) {
    console.error('Error fetching exams:', error);
  }
};

const fetchGradingQueue = async () => {
  try {
    const response = await fetch('/api/instructor/grading-queue', {
      headers: {
        'Authorization': `Bearer ${authStore.token}`,
        'Content-Type': 'application/json'
      }
    });
    
    if (response.ok) {
      const data = await response.json();
      gradingQueue.value = data.gradingQueue || [];
    }
  } catch (error) {
    console.error('Error fetching grading queue:', error);
  }
};

const fetchLearners = async () => {
  try {
    const response = await fetch('/api/instructor/learners', {
      headers: {
        'Authorization': `Bearer ${authStore.token}`,
        'Content-Type': 'application/json'
      }
    });
    
    if (response.ok) {
      const data = await response.json();
      learners.value = data.learners || [];
    }
  } catch (error) {
    console.error('Error fetching learners:', error);
  }
};

const fetchMessages = async () => {
  try {
    const response = await fetch('/api/instructor/messages', {
      headers: {
        'Authorization': `Bearer ${authStore.token}`,
        'Content-Type': 'application/json'
      }
    });
    
    if (response.ok) {
      const data = await response.json();
      messages.value = data.messages || [];
    }
  } catch (error) {
    console.error('Error fetching messages:', error);
  }
};

const fetchDiscussions = async () => {
  try {
    const response = await fetch('/api/instructor/discussions', {
      headers: {
        'Authorization': `Bearer ${authStore.token}`,
        'Content-Type': 'application/json'
      }
    });
    
    if (response.ok) {
      const data = await response.json();
      discussions.value = data.discussions || [];
    }
  } catch (error) {
    console.error('Error fetching discussions:', error);
  }
};

const fetchAnnouncements = async () => {
  try {
    const response = await fetch('/api/instructor/announcements', {
      headers: {
        'Authorization': `Bearer ${authStore.token}`,
        'Content-Type': 'application/json'
      }
    });
    
    if (response.ok) {
      const data = await response.json();
      announcements.value = data.announcements || [];
    }
  } catch (error) {
    console.error('Error fetching announcements:', error);
  }
};

const fetchFeedback = async () => {
  try {
    const response = await fetch('/api/instructor/feedback', {
      headers: {
        'Authorization': `Bearer ${authStore.token}`,
        'Content-Type': 'application/json'
      }
    });
    
    if (response.ok) {
      const data = await response.json();
      feedback.value = data.feedback || feedback.value;
    }
  } catch (error) {
    console.error('Error fetching feedback:', error);
  }
};

const fetchSharedOpportunities = async () => {
  try {
    const response = await fetch('/api/instructor/opportunities', {
      headers: {
        'Authorization': `Bearer ${authStore.token}`,
        'Content-Type': 'application/json'
      }
    });
    
    if (response.ok) {
      const data = await response.json();
      sharedOpportunities.value = data.opportunities || [];
    }
  } catch (error) {
    console.error('Error fetching shared opportunities:', error);
  }
};

// Load all data on mount
const loadAllData = async () => {
  await Promise.all([
    fetchDashboardStats(),
    fetchMyCourses(),
    fetchQuizzes(),
    fetchAssignments(),
    fetchExams(),
    fetchGradingQueue(),
    fetchLearners(),
    fetchMessages(),
    fetchDiscussions(),
    fetchAnnouncements(),
    fetchFeedback(),
    fetchSharedOpportunities()
  ]);
};

// Refresh methods
const refreshCourses = async () => {
  refreshingCourses.value = true;
  await fetchMyCourses();
  refreshingCourses.value = false;
};

const refreshAssessments = async () => {
  refreshingAssessments.value = true;
  await Promise.all([fetchQuizzes(), fetchAssignments(), fetchExams()]);
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
  return num.toLocaleString();
};

const formatDate = (dateString) => {
  if (!dateString) return 'Never';
  return new Date(dateString).toLocaleDateString();
};

// Update current time and load data
onMounted(() => {
  setInterval(() => {
    currentDateTime.value = new Date().toLocaleString();
  }, 1000);
  
  // Load all dashboard data
  loadAllData();
  
  setTimeout(() => {
    loading.value = false;
  }, 1000);
});
</script>

<style scoped>
.instructor-dashboard {
  padding: 22px 22px 40px;
  background: #f4f6fb;
  min-height: 100dvh;
  max-width: 100%;
  overflow-x: hidden;
  box-sizing: border-box;
  color: #0f172a;
}

.dash-top {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
  margin-bottom: 18px;
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

.pill {
  display: inline-flex;
  align-items: center;
  gap: 10px;
  padding: 10px 12px;
  border-radius: 14px;
  background: #ffffff;
  border: 1px solid #e2e8f0;
  color: #475569;
  font-size: 12px;
}

.quick-stats {
  margin-bottom: 18px;
}

.stats-container {
  display: grid;
  grid-template-columns: repeat(4, minmax(0, 1fr));
  gap: 14px;
}

.stat-card {
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 18px;
  padding: 14px 14px 12px;
}

.stat-kicker {
  color: #64748b;
  font-size: 12px;
  font-weight: 600;
}

.stat-main {
  display: flex;
  align-items: flex-end;
  justify-content: space-between;
  margin-top: 10px;
}

.stat-number {
  font-size: 22px;
  font-weight: 900;
  letter-spacing: -0.02em;
}

.stat-unit {
  margin-left: 4px;
  font-size: 12px;
  font-weight: 700;
  color: #64748b;
}

.stat-change {
  font-size: 12px;
  font-weight: 700;
  color: rgba(171, 145, 255, 0.9);
}

.dash-grid {
  display: grid;
  grid-template-columns: 1.15fr 1.25fr;
  grid-template-rows: auto auto;
  gap: 14px;
}

.card {
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 22px;
  padding: 16px;
  box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
}

.weekly-progress {
  grid-column: 1;
  grid-row: 1;
}

.next-lessons {
  grid-column: 1;
  grid-row: 2;
}

.my-courses {
  grid-column: 2;
  grid-row: 1 / span 2;
}

.card-head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 10px;
  margin-bottom: 14px;
}

.card-title {
  font-weight: 800;
  letter-spacing: -0.01em;
  color: #0f172a;
}

.icon-btn {
  width: 36px;
  height: 36px;
  border-radius: 12px;
  border: 1px solid #e2e8f0;
  background: #f8fafc;
  color: #475569;
  cursor: pointer;
}

.link-btn {
  border: none;
  background: transparent;
  color: #2563eb;
  cursor: pointer;
  font-weight: 700;
  font-size: 12px;
}

.tabs {
  display: inline-flex;
  padding: 4px;
  border-radius: 999px;
  background: #f1f5f9;
  border: 1px solid #e2e8f0;
  gap: 4px;
}

.tab {
  border: none;
  background: transparent;
  color: #64748b;
  padding: 8px 10px;
  border-radius: 999px;
  cursor: pointer;
  font-size: 12px;
  font-weight: 800;
}

.tab.active {
  background: #e0e7ff;
  color: #312e81;
}

.chart {
  height: 130px;
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  align-items: end;
  gap: 10px;
  padding: 10px 6px 6px;
  background: #f8fafc;
  border-radius: 18px;
  border: 1px solid #e2e8f0;
}

.bar {
  width: 100%;
  border-radius: 12px;
  background: linear-gradient(180deg, rgba(255, 232, 173, 0.9), rgba(171, 145, 255, 0.9));
  opacity: 0.9;
}

.mini-stats {
  display: grid;
  grid-template-columns: repeat(4, minmax(0, 1fr));
  gap: 10px;
  margin-top: 12px;
}

.mini {
  border-radius: 18px;
  padding: 12px 10px;
  background: rgba(255, 255, 255, 0.02);
  border: 1px solid rgba(255, 255, 255, 0.04);
}

.mini-value {
  font-weight: 900;
  font-size: 18px;
}

.mini-label {
  margin-top: 4px;
  color: rgba(255, 255, 255, 0.6);
  font-size: 12px;
  font-weight: 700;
}

.lessons {
  display: grid;
  gap: 10px;
}

.lesson {
  display: flex;
  justify-content: space-between;
  gap: 14px;
  padding: 12px 12px;
  border-radius: 18px;
  background: rgba(255, 255, 255, 0.02);
  border: 1px solid rgba(255, 255, 255, 0.04);
}

.lesson-title {
  font-weight: 800;
  font-size: 13px;
}

.lesson-sub {
  margin-top: 4px;
  color: rgba(255, 255, 255, 0.58);
  font-size: 12px;
}

.lesson-right {
  text-align: right;
  color: rgba(255, 255, 255, 0.72);
}

.lesson-time {
  font-weight: 800;
  font-size: 12px;
}

.lesson-dur {
  margin-top: 4px;
  font-size: 12px;
  color: rgba(255, 255, 255, 0.5);
}

.course-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 12px;
}

.course-tile {
  cursor: pointer;
  border-radius: 20px;
  padding: 14px;
  background: rgba(255, 255, 255, 0.02);
  border: 1px solid rgba(255, 255, 255, 0.06);
  transition: transform 180ms ease, border-color 180ms ease, background 180ms ease;
}

.course-tile:hover {
  transform: translateY(-1px);
  border-color: rgba(171, 145, 255, 0.35);
  background: rgba(255, 255, 255, 0.03);
}

.course-top {
  display: flex;
  align-items: start;
  justify-content: space-between;
  gap: 10px;
}

.course-name {
  font-weight: 900;
  letter-spacing: -0.01em;
}

.chip {
  padding: 6px 10px;
  border-radius: 999px;
  font-size: 11px;
  font-weight: 800;
  color: rgba(255, 232, 173, 0.95);
  background: rgba(255, 232, 173, 0.12);
  border: 1px solid rgba(255, 232, 173, 0.18);
}

.course-desc {
  margin-top: 8px;
  color: rgba(255, 255, 255, 0.6);
  font-size: 12px;
  line-height: 1.5;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.course-bottom {
  margin-top: 12px;
  display: grid;
  gap: 10px;
}

.progress {
  display: flex;
  align-items: center;
  gap: 10px;
}

.progress-bar {
  flex: 1;
  height: 8px;
  border-radius: 999px;
  background: rgba(255, 255, 255, 0.06);
  overflow: hidden;
}

.progress-fill {
  height: 100%;
  border-radius: 999px;
  background: linear-gradient(90deg, rgba(171, 145, 255, 0.9), rgba(255, 232, 173, 0.9));
}

.progress-text {
  font-size: 12px;
  font-weight: 900;
  color: rgba(255, 255, 255, 0.75);
  min-width: 40px;
  text-align: right;
}

.meta {
  display: flex;
  justify-content: space-between;
  gap: 10px;
  color: rgba(255, 255, 255, 0.5);
  font-size: 12px;
  font-weight: 700;
}

.empty {
  grid-column: 1 / -1;
  border-radius: 20px;
  padding: 14px;
  background: rgba(255, 255, 255, 0.02);
  border: 1px dashed rgba(255, 255, 255, 0.1);
  color: rgba(255, 255, 255, 0.62);
}

.legacy {
  display: none;
}

/* Keep legacy button layout from breaking other sections */
.course-actions {
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
}

/* Assessment Management Styles */
.tabs {
  display: flex;
  gap: 10px;
  margin-bottom: 20px;
  border-bottom: 2px solid #f0f0f0;
}

.tab-btn {
  padding: 12px 20px;
  border: none;
  background: none;
  color: #666;
  cursor: pointer;
  font-weight: 600;
  transition: all 0.3s ease;
  border-bottom: 3px solid transparent;
}

.tab-btn.active {
  color: #28a745;
  border-bottom-color: #28a745;
}

.assessments-list,
.grading-queue {
  display: grid;
  gap: 15px;
}

.assessment-card,
.submission-card {
  background: #f8f9fa;
  border-radius: 10px;
  padding: 20px;
  border: 1px solid #e9ecef;
}

.assessment-header,
.submission-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 10px;
}

.assessment-status,
.submission-priority {
  padding: 4px 10px;
  border-radius: 15px;
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
}

.assessment-status.published {
  background: #d4edda;
  color: #155724;
}

.assessment-status.draft {
  background: #fff3cd;
  color: #856404;
}

.submission-priority.high {
  background: #f8d7da;
  color: #721c24;
}

.submission-priority.medium {
  background: #fff3cd;
  color: #856404;
}

.assessment-meta,
.submission-meta {
  display: flex;
  gap: 20px;
  margin-bottom: 15px;
  font-size: 0.85rem;
  color: #666;
}

.assessment-stats {
  display: flex;
  gap: 20px;
  margin-bottom: 15px;
}

.assessment-actions,
.submission-actions {
  display: flex;
  gap: 10px;
}

/* Learner Management Styles */
.learner-controls {
  display: flex;
  gap: 20px;
  margin-bottom: 20px;
  flex-wrap: wrap;
}

.search-bar {
  position: relative;
  flex: 1;
  min-width: 300px;
}

.search-bar i {
  position: absolute;
  left: 15px;
  top: 50%;
  transform: translateY(-50%);
  color: #999;
}

.search-bar input {
  width: 100%;
  padding: 12px 15px 12px 45px;
  border: 1px solid #ddd;
  border-radius: 8px;
  font-size: 0.9rem;
}

.filter-controls {
  display: flex;
  gap: 10px;
}

.filter-select {
  padding: 12px;
  border: 1px solid #ddd;
  border-radius: 8px;
  font-size: 0.9rem;
}

.learners-table-container {
  overflow-x: auto;
}

.learners-table {
  width: 100%;
  border-collapse: collapse;
}

.learners-table th,
.learners-table td {
  padding: 15px;
  text-align: left;
  border-bottom: 1px solid #f0f0f0;
}

.learners-table th {
  background: #f8f9fa;
  font-weight: 600;
  color: #333;
}

.learner-info {
  display: flex;
  align-items: center;
  gap: 12px;
}

.learner-avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: #28a745;
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 600;
}

.learner-name {
  font-weight: 600;
  color: #333;
}

.learner-email {
  font-size: 0.85rem;
  color: #666;
}

.course-badges {
  display: flex;
  gap: 5px;
  flex-wrap: wrap;
}

.course-badge {
  background: #e3f2fd;
  color: #1976d2;
  padding: 2px 8px;
  border-radius: 12px;
  font-size: 0.75rem;
}

.progress-bar {
  width: 100px;
  height: 8px;
  background: #e9ecef;
  border-radius: 4px;
  overflow: hidden;
  margin-right: 10px;
}

.progress-fill {
  height: 100%;
  background: #28a745;
  transition: width 0.3s ease;
}

.progress-text {
  font-size: 0.85rem;
  font-weight: 600;
  color: #333;
}

.action-buttons {
  display: flex;
  gap: 5px;
}

.btn-icon {
  width: 32px;
  height: 32px;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.3s ease;
}

.btn-view {
  background: #17a2b8;
  color: white;
}

.btn-progress {
  background: #ffc107;
  color: #212529;
}

.btn-message {
  background: #6f42c1;
  color: white;
}

/* Progress Tracking Styles */
.progress-overview {
  margin-bottom: 30px;
}

.overview-cards {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 20px;
}

.overview-card {
  background: #f8f9fa;
  border-radius: 10px;
  padding: 20px;
  border: 1px solid #e9ecef;
  text-align: center;
}

.chart-placeholder {
  height: 200px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  color: #666;
}

.chart-placeholder i {
  font-size: 3rem;
  margin-bottom: 10px;
}

.course-progress-list {
  display: grid;
  gap: 15px;
}

.course-progress-card {
  background: #f8f9fa;
  border-radius: 10px;
  padding: 20px;
  border: 1px solid #e9ecef;
}

.course-progress-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 15px;
}

.completion-rate {
  background: #28a745;
  color: white;
  padding: 4px 10px;
  border-radius: 15px;
  font-size: 0.8rem;
  font-weight: 600;
}

.progress-stats {
  display: flex;
  gap: 20px;
  margin-bottom: 15px;
}

.progress-actions {
  display: flex;
  gap: 10px;
}

/* Communication & Support Styles */
.communication-tabs {
  display: flex;
  gap: 10px;
  margin-bottom: 20px;
  border-bottom: 2px solid #f0f0f0;
}

.messages-list,
.discussions-list,
.announcements-list {
  display: grid;
  gap: 15px;
}

.message-card,
.discussion-card,
.announcement-card {
  background: #f8f9fa;
  border-radius: 10px;
  padding: 20px;
  border: 1px solid #e9ecef;
}

.message-header,
.discussion-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 10px;
}

.message-sender {
  display: flex;
  align-items: center;
  gap: 12px;
}

.sender-avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: #17a2b8;
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 600;
}

.sender-name {
  font-weight: 600;
  color: #333;
}

.sender-role {
  font-size: 0.85rem;
  color: #666;
}

.message-time {
  font-size: 0.85rem;
  color: #666;
}

.message-subject {
  font-weight: 600;
  color: #333;
  margin-bottom: 10px;
}

.message-preview,
.discussion-preview {
  color: #666;
  margin-bottom: 15px;
  line-height: 1.5;
}

.discussion-status {
  padding: 4px 10px;
  border-radius: 15px;
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
}

.discussion-status.active {
  background: #d4edda;
  color: #155724;
}

.discussion-status.closed {
  background: #f8d7da;
  color: #721c24;
}

.discussion-meta {
  display: flex;
  gap: 20px;
  margin-bottom: 15px;
  font-size: 0.85rem;
  color: #666;
}

.discussion-actions,
.message-actions,
.announcement-actions {
  display: flex;
  gap: 10px;
}

.announcement-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 10px;
}

.announcement-audience {
  background: #e3f2fd;
  color: #1976d2;
  padding: 4px 10px;
  border-radius: 15px;
  font-size: 0.75rem;
  font-weight: 600;
}

.announcement-content {
  color: #666;
  margin-bottom: 15px;
  line-height: 1.5;
}

.announcement-meta {
  display: flex;
  gap: 20px;
  margin-bottom: 15px;
  font-size: 0.85rem;
  color: #666;
}

/* Course Improvement Styles */
.improvement-cards {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 20px;
}

.improvement-card {
  background: #f8f9fa;
  border-radius: 10px;
  padding: 20px;
  border: 1px solid #e9ecef;
}

.improvement-card h3 {
  color: #333;
  margin-bottom: 20px;
  font-size: 1.2rem;
}

.feedback-summary {
  text-align: center;
  margin-bottom: 20px;
}

.rating {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
  margin-bottom: 10px;
}

.rating-score {
  font-size: 2rem;
  font-weight: 700;
  color: #ffc107;
}

.stars {
  color: #ffc107;
}

.feedback-count {
  color: #666;
  font-size: 0.9rem;
}

.feedback-highlights {
  margin-bottom: 20px;
}

.highlight {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 10px;
  font-size: 0.9rem;
}

.highlight.positive {
  color: #28a745;
}

.highlight.negative {
  color: #dc3545;
}

.insights-list {
  margin-bottom: 20px;
}

.insight {
  display: flex;
  align-items: flex-start;
  gap: 10px;
  margin-bottom: 15px;
}

.insight i {
  color: #17a2b8;
  margin-top: 2px;
}

.insight-title {
  font-weight: 600;
  color: #333;
  margin-bottom: 2px;
}

.insight-desc {
  font-size: 0.85rem;
  color: #666;
}

.recommendations {
  margin-bottom: 20px;
}

.recommendation {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 10px;
  font-size: 0.9rem;
  color: #333;
}

.recommendation i {
  color: #28a745;
}

/* Opportunity Sharing Styles */
.opportunities-shared {
  display: grid;
  gap: 15px;
}

.shared-opportunity-card {
  background: #f8f9fa;
  border-radius: 10px;
  padding: 20px;
  border: 1px solid #e9ecef;
}

.opportunity-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 10px;
}

.opportunity-type {
  padding: 4px 10px;
  border-radius: 15px;
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
}

.opportunity-type.internship {
  background: #e3f2fd;
  color: #1976d2;
}

.opportunity-type.training {
  background: #f3e5f5;
  color: #7b1fa2;
}

.opportunity-description {
  color: #666;
  margin-bottom: 15px;
  line-height: 1.5;
}

.opportunity-meta {
  display: flex;
  gap: 20px;
  margin-bottom: 15px;
  font-size: 0.85rem;
  color: #666;
}

.opportunity-stats {
  display: flex;
  gap: 20px;
  margin-bottom: 15px;
}

.opportunity-actions {
  display: flex;
  gap: 10px;
}

/* Loading Overlay */
.loading-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(255, 255, 255, 0.9);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.loading-spinner {
  text-align: center;
}

.spinner {
  width: 50px;
  height: 50px;
  border: 5px solid #f3f3f3;
  border-top: 5px solid #28a745;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin: 0 auto 20px;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

/* Responsive Design */
@media (max-width: 768px) {
  .instructor-dashboard {
    padding: 10px;
  }

  .dash-top {
    flex-direction: column;
    align-items: flex-start;
  }

  .stats-container {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }

  .dash-grid {
    grid-template-columns: 1fr;
  }

  .my-courses {
    grid-column: auto;
    grid-row: auto;
  }

  .course-grid {
    grid-template-columns: 1fr;
  }
}
</style>
