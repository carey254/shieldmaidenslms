<template>
  <div class="admin-dashboard">
    <!-- Loading Overlay -->
    <div v-if="loading" class="loading-overlay">
      <div class="loading-spinner">
        <div class="spinner"></div>
        <p>Loading dashboard...</p>
      </div>
    </div>

    <!-- Quick Stats -->
    <section class="quick-stats">
      <div class="stats-container">
        <div v-for="(stat, index) in animatedStats" :key="index" class="stat-card">
          <div class="stat-header">
            <div class="stat-icon" :style="{ backgroundColor: stat.color + '20', color: stat.color }">
              <i :class="stat.icon"></i>
            </div>
            <div class="stat-trend" :class="stat.trend">
              <i :class="stat.trendIcon"></i>
              <span>{{ stat.change }}</span>
            </div>
          </div>
          <div class="stat-content">
            <div class="stat-number">
              <span>{{ formatNumber(stat.value) }}</span>
              <span class="stat-unit">{{ stat.unit }}</span>
            </div>
            <div class="stat-label">{{ stat.label }}</div>
          </div>
        </div>
      </div>
    </section>

    <!-- Quick Actions -->
    <section class="quick-actions">
      <div class="actions-header">
        <h3>Quick Actions</h3>
        <p>Common administrative tasks</p>
      </div>
      <div class="action-buttons">
        <button @click="showAddCourseModal = true" class="action-btn primary">
          <i class="fas fa-plus"></i>
          Add Course
        </button>
        <button @click="showAddFacilitatorModal = true" class="action-btn secondary">
          <i class="fas fa-user-plus"></i>
          Add Facilitator
        </button>
      </div>
    </section>

    <!-- Dashboard Content -->
    <div class="dashboard-content">
      <div class="dashboard-header">
        <h1>Dashboard</h1>
        <p>Welcome back! Here's what's happening with your platform today.</p>
      </div>

      <!-- Charts Section -->
      <div class="charts-section">
        <!-- Enrollments Overview -->
        <div class="chart-card">
          <div class="chart-header">
            <h3>Enrollments Overview</h3>
            <p>Last 30 days</p>
          </div>
          <div class="chart-placeholder">
            <i class="fas fa-chart-line"></i>
            <p>Enrollment trends will appear here</p>
          </div>
        </div>

        <!-- Top Courses -->
        <div class="chart-card">
          <div class="chart-header">
            <h3>Top Courses</h3>
            <p>Most enrolled courses</p>
          </div>
          <div class="top-courses-list">
            <div v-for="course in topCourses" :key="course.id" class="course-item">
              <div class="course-info">
                <h4>{{ course.title }}</h4>
                <span class="enrollment-count">{{ course.enrollments }} students</span>
              </div>
              <div class="course-progress">
                <div class="progress-bar" :style="{ width: course.progress + '%' }"></div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Recent Activity Section -->
      <div class="activity-section">
        <!-- Recent Enrollments -->
        <div class="activity-card">
          <div class="activity-header">
            <h3>Recent Enrollments</h3>
            <button class="view-all-btn">View All</button>
          </div>
          <div class="activity-list">
            <div v-for="enrollment in recentEnrollments" :key="enrollment.id" class="activity-item">
              <div class="activity-icon">
                <i class="fas fa-user-graduate"></i>
              </div>
              <div class="activity-details">
                <h4>{{ enrollment.studentName }}</h4>
                <p>enrolled in {{ enrollment.courseName }}</p>
                <span class="activity-time">{{ formatDate(enrollment.date) }}</span>
              </div>
              <div class="activity-status" :class="enrollment.status">
                {{ enrollment.status }}
              </div>
            </div>
          </div>
        </div>

        <!-- Recent Activities -->
        <div class="activity-card">
          <div class="activity-header">
            <h3>Recent Activities</h3>
            <button class="view-all-btn">View All</button>
          </div>
          <div class="activity-list">
            <div v-for="activity in recentActivities" :key="activity.id" class="activity-item">
              <div class="activity-icon" :class="activity.type">
                <i :class="activity.icon"></i>
              </div>
              <div class="activity-details">
                <h4>{{ activity.title }}</h4>
                <p>{{ activity.description }}</p>
                <span class="activity-time">{{ formatDate(activity.date) }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Management Grid -->
    <div class="management-grid">
      
      <!-- 1. User Management -->
      <section class="management-section user-management">
        <div class="section-header">
          <h2><i class="fas fa-users-cog"></i> User Management</h2>
          <div class="header-actions">
            <button @click="showAddUserModal = true" class="btn btn-primary">
              <i class="fas fa-user-plus"></i> Add User
            </button>
            <button @click="refreshUsers" class="btn btn-secondary">
              <i class="fas fa-sync-alt" :class="{ 'fa-spin': refreshingUsers }"></i> Refresh
            </button>
          </div>
        </div>

        <div class="user-controls">
          <div class="search-bar">
            <i class="fas fa-search"></i>
            <input v-model="userSearchQuery" type="text" placeholder="Search users by name, email, or role...">
          </div>
          <div class="filter-controls">
            <select v-model="roleFilter" class="filter-select">
              <option value="">All Roles</option>
              <option value="admin">Administrators</option>
              <option value="instructor">Instructors</option>
              <option value="student">Students</option>
              <option value="parent">Parents</option>
            </select>
            <select v-model="statusFilter" class="filter-select">
              <option value="">All Status</option>
              <option value="active">Active</option>
              <option value="suspended">Suspended</option>
              <option value="inactive">Inactive</option>
            </select>
          </div>
        </div>

        <div class="users-table-container">
          <table class="users-table">
            <thead>
              <tr>
                <th>User</th>
                <th>Role</th>
                <th>Status</th>
                <th>Last Login</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="user in filteredUsers" :key="user.id">
                <td>
                  <div class="user-info">
                    <div class="user-avatar">{{ user.name.charAt(0) }}</div>
                    <div>
                      <div class="user-name">{{ user.name }}</div>
                      <div class="user-email">{{ user.email }}</div>
                    </div>
                  </div>
                </td>
                <td>
                  <span class="role-badge" :class="user.role">{{ user.role }}</span>
                </td>
                <td>
                  <span class="status-badge" :class="user.status">{{ user.status }}</span>
                </td>
                <td>{{ formatDate(user.last_login_at) }}</td>
                <td>
                  <div class="action-buttons">
                    <button @click="editUser(user)" class="btn-icon btn-edit" title="Edit">
                      <i class="fas fa-edit"></i>
                    </button>
                    <button @click="toggleUserStatus(user)" class="btn-icon" :class="user.status === 'active' ? 'btn-suspend' : 'btn-activate'" :title="user.status === 'active' ? 'Suspend' : 'Activate'">
                      <i :class="user.status === 'active' ? 'fas fa-ban' : 'fas fa-check'"></i>
                    </button>
                    <button @click="deleteUser(user)" class="btn-icon btn-delete" title="Delete">
                      <i class="fas fa-trash"></i>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </section>

      <!-- 2. Course Management -->
      <section class="management-section course-management">
        <div class="section-header">
          <h2><i class="fas fa-graduation-cap"></i> Course Management</h2>
          <div class="header-actions">
            <button @click="console.log('Course button clicked'); showAddCourseModal = true" class="btn btn-primary">
              <i class="fas fa-plus"></i> Create Course
            </button>
            <button @click="refreshCourses" class="btn btn-secondary">
              <i class="fas fa-sync-alt" :class="{ 'fa-spin': refreshingCourses }"></i> Refresh
            </button>
          </div>
        </div>

        <div class="courses-grid">
          <div v-for="course in courses" :key="course.id" class="course-card">
            <div class="course-header">
              <h3>{{ course.title }}</h3>
              <span class="course-status" :class="course.status">{{ course.status }}</span>
            </div>
            <div class="course-instructor">
              <i class="fas fa-user-tie"></i>
              {{ course.instructor || 'Unassigned' }}
            </div>
            <div class="course-stats">
              <div class="stat">
                <span class="stat-label">Students</span>
                <span class="stat-value">{{ course.enrolled_count || 0 }}</span>
              </div>
              <div class="stat">
                <span class="stat-label">Modules</span>
                <span class="stat-value">{{ course.modules_count || 0 }}</span>
              </div>
            </div>
            <div class="course-actions">
              <button @click="editCourse(course)" class="btn btn-sm btn-secondary">
                <i class="fas fa-edit"></i> Edit
              </button>
              <button @click="manageCourseContent(course)" class="btn btn-sm btn-primary">
                <i class="fas fa-book"></i> Content
              </button>
              <button @click="assignInstructor(course)" class="btn btn-sm btn-info">
                <i class="fas fa-user-plus"></i> Assign
              </button>
            </div>
          </div>
        </div>
      </section>

      <!-- 3. Opportunities & Announcements Management -->
      <section class="management-section opportunities-management">
        <div class="section-header">
          <h2><i class="fas fa-bullhorn"></i> Opportunities & Announcements</h2>
          <div class="header-actions">
            <button @click="console.log('Opportunity button clicked'); showAddOpportunityModal = true" class="btn btn-primary">
              <i class="fas fa-plus"></i> Add Opportunity
            </button>
            <button @click="console.log('Announcement button clicked'); showAddAnnouncementModal = true" class="btn btn-success">
              <i class="fas fa-bullhorn"></i> Add Announcement
            </button>
          </div>
        </div>

        <div class="tabs">
          <button @click="activeTab = 'opportunities'" class="tab-btn" :class="{ active: activeTab === 'opportunities' }">
            Opportunities
          </button>
          <button @click="activeTab = 'announcements'" class="tab-btn" :class="{ active: activeTab === 'announcements' }">
            Announcements
          </button>
        </div>

        <div v-if="activeTab === 'opportunities'" class="opportunities-list">
          <div v-for="opportunity in opportunities" :key="opportunity.id" class="opportunity-card">
            <div class="opportunity-header">
              <h4>{{ opportunity.title }}</h4>
              <span class="opportunity-type" :class="opportunity.type">{{ opportunity.type }}</span>
            </div>
            <p class="opportunity-description">{{ opportunity.description }}</p>
            <div class="opportunity-meta">
              <span class="deadline">
                <i class="fas fa-calendar"></i>
                Deadline: {{ formatDate(opportunity.deadline) }}
              </span>
              <span class="visibility">
                <i class="fas fa-eye"></i>
                {{ opportunity.visibility }}
              </span>
            </div>
            <div class="opportunity-actions">
              <button @click="editOpportunity(opportunity)" class="btn btn-sm btn-secondary">
                <i class="fas fa-edit"></i> Edit
              </button>
              <button @click="toggleOpportunityVisibility(opportunity)" class="btn btn-sm btn-info">
                <i class="fas fa-eye"></i> Visibility
              </button>
              <button @click="deleteOpportunity(opportunity)" class="btn btn-sm btn-danger">
                <i class="fas fa-trash"></i> Delete
              </button>
            </div>
          </div>
        </div>

        <div v-if="activeTab === 'announcements'" class="announcements-list">
          <div v-for="announcement in announcements" :key="announcement.id" class="announcement-card">
            <div class="announcement-header">
              <h4>{{ announcement.title }}</h4>
              <span class="announcement-priority" :class="announcement.priority">{{ announcement.priority }}</span>
            </div>
            <p class="announcement-content">{{ announcement.content }}</p>
            <div class="announcement-meta">
              <span class="date">
                <i class="fas fa-calendar"></i>
                {{ formatDate(announcement.created_at) }}
              </span>
              <span class="audience">
                <i class="fas fa-users"></i>
                {{ announcement.audience }}
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

      <!-- 4. Reports & Analytics -->
      <section class="management-section reports-analytics">
        <div class="section-header">
          <h2><i class="fas fa-chart-line"></i> Reports & Analytics</h2>
          <div class="header-actions">
            <select v-model="reportPeriod" class="filter-select">
              <option value="7">Last 7 Days</option>
              <option value="30">Last 30 Days</option>
              <option value="90">Last 90 Days</option>
              <option value="365">Last Year</option>
            </select>
            <button @click="generateReport" class="btn btn-primary">
              <i class="fas fa-download"></i> Generate Report
            </button>
          </div>
        </div>

        <div class="analytics-grid">
          <div class="analytics-card">
            <h3>User Engagement</h3>
            <div class="chart-placeholder">
              <i class="fas fa-chart-area"></i>
              <p>User activity over time</p>
            </div>
          </div>
          <div class="analytics-card">
            <h3>Course Performance</h3>
            <div class="chart-placeholder">
              <i class="fas fa-chart-bar"></i>
              <p>Completion rates by course</p>
            </div>
          </div>
          <div class="analytics-card">
            <h3>Platform Usage</h3>
            <div class="chart-placeholder">
              <i class="fas fa-chart-pie"></i>
              <p>Feature usage statistics</p>
            </div>
          </div>
        </div>

        <div class="reports-table">
          <h3>Recent Reports</h3>
          <table>
            <thead>
              <tr>
                <th>Report Name</th>
                <th>Type</th>
                <th>Generated</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="report in reports" :key="report.id">
                <td>{{ report.name }}</td>
                <td>{{ report.type }}</td>
                <td>{{ formatDate(report.generated_at) }}</td>
                <td>
                  <button @click="downloadReport(report)" class="btn btn-sm btn-primary">
                    <i class="fas fa-download"></i> Download
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </section>

      <!-- 5. System Configuration -->
      <section class="management-section system-config">
        <div class="section-header">
          <h2><i class="fas fa-cogs"></i> System Configuration</h2>
        </div>

        <div class="config-grid">
          <div class="config-card">
            <h3>Platform Settings</h3>
            <div class="config-item">
              <label>Platform Name</label>
              <input v-model="config.platformName" type="text" class="config-input">
            </div>
            <div class="config-item">
              <label>Default Language</label>
              <select v-model="config.defaultLanguage" class="config-select">
                <option value="en">English</option>
                <option value="sw">Swahili</option>
                <option value="ar">Arabic</option>
              </select>
            </div>
            <div class="config-item">
              <label>Maintenance Mode</label>
              <label class="switch">
                <input v-model="config.maintenanceMode" type="checkbox">
                <span class="slider"></span>
              </label>
            </div>
          </div>

          <div class="config-card">
            <h3>Security Settings</h3>
            <div class="config-item">
              <label>Session Timeout (minutes)</label>
              <input v-model="config.sessionTimeout" type="number" class="config-input">
            </div>
            <div class="config-item">
              <label>Two-Factor Authentication</label>
              <label class="switch">
                <input v-model="config.twoFactorAuth" type="checkbox">
                <span class="slider"></span>
              </label>
            </div>
            <div class="config-item">
              <label>Password Complexity</label>
              <select v-model="config.passwordComplexity" class="config-select">
                <option value="low">Low</option>
                <option value="medium">Medium</option>
                <option value="high">High</option>
              </select>
            </div>
          </div>

          <div class="config-card">
            <h3>Backup Settings</h3>
            <div class="config-item">
              <label>Auto Backup</label>
              <label class="switch">
                <input v-model="config.autoBackup" type="checkbox">
                <span class="slider"></span>
              </label>
            </div>
            <div class="config-item">
              <label>Backup Frequency</label>
              <select v-model="config.backupFrequency" class="config-select">
                <option value="daily">Daily</option>
                <option value="weekly">Weekly</option>
                <option value="monthly">Monthly</option>
              </select>
            </div>
            <button @click="performBackup" class="btn btn-primary">
              <i class="fas fa-download"></i> Backup Now
            </button>
          </div>
        </div>

        <div class="config-actions">
          <button @click="saveConfig" class="btn btn-success">
            <i class="fas fa-save"></i> Save Configuration
          </button>
          <button @click="resetConfig" class="btn btn-secondary">
            <i class="fas fa-undo"></i> Reset to Defaults
          </button>
        </div>
      </section>

      <!-- 6. System Monitoring -->
      <section class="management-section system-monitoring">
        <div class="section-header">
          <h2><i class="fas fa-heartbeat"></i> System Monitoring</h2>
          <div class="header-actions">
            <button @click="refreshSystemStatus" class="btn btn-secondary">
              <i class="fas fa-sync-alt" :class="{ 'fa-spin': refreshingSystem }"></i> Refresh
            </button>
          </div>
        </div>

        <div class="system-status-grid">
          <div class="status-card">
            <h3>Server Status</h3>
            <div class="status-indicator" :class="systemStatus.server.status">
              <i :class="systemStatus.server.icon"></i>
              {{ systemStatus.server.message }}
            </div>
            <div class="status-details">
              <div class="detail">
                <span>CPU Usage:</span>
                <span>{{ systemStatus.server.cpu }}%</span>
              </div>
              <div class="detail">
                <span>Memory:</span>
                <span>{{ systemStatus.server.memory }}%</span>
              </div>
              <div class="detail">
                <span>Disk:</span>
                <span>{{ systemStatus.server.disk }}%</span>
              </div>
            </div>
          </div>

          <div class="status-card">
            <h3>Database Status</h3>
            <div class="status-indicator" :class="systemStatus.database.status">
              <i :class="systemStatus.database.icon"></i>
              {{ systemStatus.database.message }}
            </div>
            <div class="status-details">
              <div class="detail">
                <span>Connections:</span>
                <span>{{ systemStatus.database.connections }}</span>
              </div>
              <div class="detail">
                <span>Query Time:</span>
                <span>{{ systemStatus.database.queryTime }}ms</span>
              </div>
            </div>
          </div>

          <div class="status-card">
            <h3>Application Status</h3>
            <div class="status-indicator" :class="systemStatus.application.status">
              <i :class="systemStatus.application.icon"></i>
              {{ systemStatus.application.message }}
            </div>
            <div class="status-details">
              <div class="detail">
                <span>Active Users:</span>
                <span>{{ systemStatus.application.activeUsers }}</span>
              </div>
              <div class="detail">
                <span>Response Time:</span>
                <span>{{ systemStatus.application.responseTime }}ms</span>
              </div>
            </div>
          </div>
        </div>

        <div class="system-logs">
          <h3>System Logs</h3>
          <div class="log-container">
            <div v-for="log in systemLogs" :key="log.id" class="log-entry" :class="log.level">
              <span class="log-time">{{ formatDate(log.timestamp) }}</span>
              <span class="log-level">{{ log.level }}</span>
              <span class="log-message">{{ log.message }}</span>
            </div>
          </div>
        </div>
      </section>
    </div>

    <!-- Add User Modal -->
    <div v-if="showAddUserModal" class="modal-overlay" @click="showAddUserModal = false">
      <div class="modal" @click.stop>
        <div class="modal-header">
          <h3>Add New User</h3>
          <button @click="showAddUserModal = false" class="btn-close">×</button>
        </div>
        <div class="modal-body">
          <form @submit.prevent="addUser">
            <div class="form-group">
              <label>Name</label>
              <input v-model="newUser.name" type="text" required>
            </div>
            <div class="form-group">
              <label>Email</label>
              <input v-model="newUser.email" type="email" required>
            </div>
            <div class="form-group">
              <label>Role</label>
              <select v-model="newUser.role" required>
                <option value="">Select Role</option>
                <option value="admin">Administrator</option>
                <option value="instructor">Instructor</option>
                <option value="student">Student</option>
                <option value="parent">Parent</option>
              </select>
            </div>
            <div class="form-group">
              <label>Password</label>
              <input v-model="newUser.password" type="password" required>
            </div>
            <div class="form-actions">
              <button type="submit" class="btn btn-primary">Add User</button>
              <button type="button" @click="showAddUserModal = false" class="btn btn-secondary">Cancel</button>
            </div>
          </form>
        </div>
      </div>

      <!-- Add Course Modal -->
      <div v-if="showAddCourseModal" class="modal-overlay" @click="showAddCourseModal = false">
        <div class="modal-content" @click.stop>
          <div class="modal-header">
            <h3>Create New Course</h3>
            <button @click="showAddCourseModal = false" class="close-btn">&times;</button>
          </div>
          <div class="modal-body">
            <form @submit.prevent="addCourse">
              <div class="form-group">
                <label>Course Title</label>
                <input v-model="newCourse.title" type="text" required>
              </div>
              <div class="form-group">
                <label>Description</label>
                <textarea v-model="newCourse.description" rows="3" required></textarea>
              </div>
              <div class="form-group">
                <label>Category</label>
                <select v-model="newCourse.category" required>
                  <option value="">Select Category</option>
                  <option value="Technology">Technology</option>
                  <option value="Business">Business</option>
                  <option value="Arts">Arts</option>
                  <option value="Science">Science</option>
                  <option value="Language">Language</option>
                </select>
              </div>
              <div class="form-group">
                <label>Difficulty Level</label>
                <select v-model="newCourse.difficulty_level" required>
                  <option value="beginner">Beginner</option>
                  <option value="intermediate">Intermediate</option>
                  <option value="advanced">Advanced</option>
                </select>
              </div>
              <div class="form-group">
                <label>Duration (hours)</label>
                <input v-model="newCourse.duration_hours" type="number" min="1" required>
              </div>
              <div class="form-group">
                <label>Price ($)</label>
                <input v-model="newCourse.price" type="number" min="0" step="0.01" required>
              </div>
              <div class="form-group">
                <label>Max Students</label>
                <input v-model="newCourse.max_students" type="number" min="1" required>
              </div>
              <div class="form-group">
                <label>Start Date</label>
                <input v-model="newCourse.start_date" type="date" required>
              </div>
              <div class="form-group">
                <label>End Date</label>
                <input v-model="newCourse.end_date" type="date" required>
              </div>
              <div class="form-actions">
                <button type="submit" class="btn btn-primary">Create Course</button>
                <button type="button" @click="showAddCourseModal = false" class="btn btn-secondary">Cancel</button>
              </div>
            </form>
          </div>
        </div>
      </div>

      <!-- Add Opportunity Modal -->
      <div v-if="showAddOpportunityModal" class="modal-overlay" @click="showAddOpportunityModal = false">
        <div class="modal-content" @click.stop>
          <div class="modal-header">
            <h3>Create New Opportunity</h3>
            <button @click="showAddOpportunityModal = false" class="close-btn">&times;</button>
          </div>
          <div class="modal-body">
            <form @submit.prevent="addOpportunity">
              <div class="form-group">
                <label>Opportunity Title</label>
                <input v-model="newOpportunity.title" type="text" required>
              </div>
              <div class="form-group">
                <label>Description</label>
                <textarea v-model="newOpportunity.description" rows="3" required></textarea>
              </div>
              <div class="form-group">
                <label>Type</label>
                <select v-model="newOpportunity.type" required>
                  <option value="internship">Internship</option>
                  <option value="training">Training</option>
                  <option value="job">Job</option>
                  <option value="scholarship">Scholarship</option>
                </select>
              </div>
              <div class="form-group">
                <label>Organization</label>
                <input v-model="newOpportunity.organization" type="text" required>
              </div>
              <div class="form-group">
                <label>Location</label>
                <input v-model="newOpportunity.location" type="text" required>
              </div>
              <div class="form-group">
                <label>Deadline</label>
                <input v-model="newOpportunity.deadline" type="date" required>
              </div>
              <div class="form-group">
                <label>Requirements</label>
                <textarea v-model="newOpportunity.requirements" rows="3" required></textarea>
              </div>
              <div class="form-group">
                <label>Benefits</label>
                <textarea v-model="newOpportunity.benefits" rows="3" required></textarea>
              </div>
              <div class="form-group">
                <label>Contact Email</label>
                <input v-model="newOpportunity.contact_email" type="email" required>
              </div>
              <div class="form-group">
                <label>Application Link (optional)</label>
                <input v-model="newOpportunity.application_link" type="url" placeholder="https://...">
              </div>
              <div class="form-group">
                <label>Visibility</label>
                <select v-model="newOpportunity.visibility" required>
                  <option value="all">All Users</option>
                  <option value="students">Students Only</option>
                  <option value="instructors">Instructors Only</option>
                </select>
              </div>
              <div class="form-actions">
                <button type="submit" class="btn btn-primary">Create Opportunity</button>
                <button type="button" @click="showAddOpportunityModal = false" class="btn btn-secondary">Cancel</button>
              </div>
            </form>
          </div>
        </div>
      </div>

      <!-- Add Announcement Modal -->
      <div v-if="showAddAnnouncementModal" class="modal-overlay" @click="showAddAnnouncementModal = false">
        <div class="modal-content" @click.stop>
          <div class="modal-header">
            <h3>Send Announcement</h3>
            <button @click="showAddAnnouncementModal = false" class="close-btn">&times;</button>
          </div>
          <div class="modal-body">
            <form @submit.prevent="sendAnnouncement">
              <div class="form-group">
                <label>Announcement Title</label>
                <input v-model="newAnnouncement.title" type="text" required>
              </div>
              <div class="form-group">
                <label>Message</label>
                <textarea v-model="newAnnouncement.message" rows="4" required></textarea>
              </div>
              <div class="form-group">
                <label>Priority</label>
                <select v-model="newAnnouncement.priority" required>
                  <option value="low">Low</option>
                  <option value="medium">Medium</option>
                  <option value="high">High</option>
                </select>
              </div>
              <div class="form-group">
                <label>Send To</label>
                <div class="checkbox-group">
                  <label>
                    <input type="checkbox" value="all" v-model="newAnnouncement.recipients">
                    All Users
                  </label>
                  <label>
                    <input type="checkbox" value="students" v-model="newAnnouncement.recipients">
                    Students Only
                  </label>
                  <label>
                    <input type="checkbox" value="instructors" v-model="newAnnouncement.recipients">
                    Instructors Only
                  </label>
                </div>
              </div>
              <div class="form-actions">
                <button type="submit" class="btn btn-primary">Send Announcement</button>
                <button type="button" @click="showAddAnnouncementModal = false" class="btn btn-secondary">Cancel</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
</div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useAuthStore } from '@/stores/auth';

const authStore = useAuthStore();
const user = computed(() => authStore.user);

// Loading state
const loading = ref(true);

// Current date/time
const currentDateTime = ref(new Date().toLocaleString());

// Real-time data from API
const animatedStats = ref([]);
const topCourses = ref([]);
const recentEnrollments = ref([]);
const recentActivities = ref([]);

// User Management - will be fetched from API
const users = ref([]);
const userSearchQuery = ref('');
const roleFilter = ref('');
const statusFilter = ref('');
const refreshingUsers = ref(false);
const showAddUserModal = ref(false);
const showAddFacilitatorModal = ref(false);

const newUser = ref({
  name: '',
  email: '',
  role: '',
  password: ''
});

// Course Management - will be fetched from API
const courses = ref([]);
const refreshingCourses = ref(false);
const showAddCourseModal = ref(false);

// Opportunities & Announcements - will be fetched from API
const activeTab = ref('opportunities');
const opportunities = ref([]);
const announcements = ref([]);
const showAddOpportunityModal = ref(false);
const showAddAnnouncementModal = ref(false);
const newOpportunity = ref({
  title: '',
  description: '',
  type: 'internship',
  organization: '',
  location: '',
  deadline: '',
  requirements: '',
  benefits: '',
  contact_email: '',
  visibility: 'all',
  application_link: '' 
});
const newAnnouncement = ref({
  title: '',
  message: '',
  priority: 'medium',
  recipients: []
});

// Reports & Analytics - will be fetched from API
const reportPeriod = ref('30');
const reports = ref([]);

// System Configuration - will be fetched from API
const config = ref({
  platformName: 'Shield Maidens LMS',
  defaultLanguage: 'en',
  maintenanceMode: false,
  sessionTimeout: 120,
  twoFactorAuth: false,
  passwordComplexity: 'medium',
  autoBackup: true,
  backupFrequency: 'daily'
});

// System Monitoring - will be fetched from API
const refreshingSystem = ref(false);
const systemStatus = ref({
  server: {
    status: 'healthy',
    icon: 'fas fa-check-circle',
    message: 'Loading...',
    cpu: 0,
    memory: 0,
    disk: 0
  },
  database: {
    status: 'healthy',
    icon: 'fas fa-check-circle',
    message: 'Loading...',
    connections: 0,
    queryTime: 0
  },
  application: {
    status: 'healthy',
    icon: 'fas fa-check-circle',
    message: 'Loading...',
    activeUsers: 0,
    responseTime: 0
  }
});

const systemLogs = ref([]);

// Computed properties
const filteredUsers = computed(() => {
  return users.value.filter(user => {
    const matchesSearch = user.name.toLowerCase().includes(userSearchQuery.value.toLowerCase()) ||
                         user.email.toLowerCase().includes(userSearchQuery.value.toLowerCase()) ||
                         user.role.toLowerCase().includes(userSearchQuery.value.toLowerCase());
    const matchesRole = !roleFilter.value || user.role === roleFilter.value;
    const matchesStatus = !statusFilter.value || user.status === statusFilter.value;
    return matchesSearch && matchesRole && matchesStatus;
  });
});

// Methods
const formatNumber = (num) => {
  return num.toLocaleString();
};

const formatDate = (date) => {
  if (!date) return 'Never';
  return new Date(date).toLocaleDateString();
};

// Update trend icons
animatedStats.value.forEach(stat => {
  if (stat.trend === 'up') {
    stat.trendIcon = 'fas fa-arrow-up';
  } else if (stat.trend === 'down') {
    stat.trendIcon = 'fas fa-arrow-down';
  } else {
    stat.trendIcon = 'fas fa-minus';
  }
});

// API Data Fetching Methods
const fetchDashboardStats = async () => {
  try {
    const response = await fetch('http://127.0.0.1:8000/api/admin/stats', {
      headers: {
        'Authorization': `Bearer ${authStore.token}`,
        'Content-Type': 'application/json'
      }
    });
    
    if (response.ok) {
      const data = await response.json();
      animatedStats.value = data.stats || [];
    }
  } catch (error) {
    console.error('Error fetching dashboard stats:', error);
  }
};

const fetchTopCourses = async () => {
  try {
    const response = await fetch('http://127.0.0.1:8000/api/admin/dashboard/top-courses', {
      headers: {
        'Authorization': `Bearer ${authStore.token}`,
        'Accept': 'application/json'
      }
    });
    const data = await response.json();
    topCourses.value = data.courses || [];
  } catch (error) {
    console.error('Error fetching top courses:', error);
  }
};

const fetchRecentEnrollments = async () => {
  try {
    const response = await fetch('http://127.0.0.1:8000/api/admin/dashboard/recent-enrollments', {
      headers: {
        'Authorization': `Bearer ${authStore.token}`,
        'Accept': 'application/json'
      }
    });
    const data = await response.json();
    recentEnrollments.value = data.enrollments || [];
  } catch (error) {
    console.error('Error fetching recent enrollments:', error);
  }
};

const fetchRecentActivities = async () => {
  try {
    const response = await fetch('http://127.0.0.1:8000/api/admin/dashboard/recent-activities', {
      headers: {
        'Authorization': `Bearer ${authStore.token}`,
        'Accept': 'application/json'
      }
    });
    const data = await response.json();
    recentActivities.value = data.activities || [];
  } catch (error) {
    console.error('Error fetching recent activities:', error);
  }
};

// Fetch all dashboard data
const fetchDashboardData = async () => {
  loading.value = true;
  try {
    await Promise.all([
      fetchDashboardStats(),
      fetchTopCourses(),
      fetchRecentEnrollments(),
      fetchRecentActivities()
    ]);
  } catch (error) {
    console.error('Error fetching dashboard data:', error);
  } finally {
    loading.value = false;
  }
};

const fetchUsers = async () => {
  try {
    const response = await fetch('http://127.0.0.1:8000/api/admin/users', {
      headers: {
        'Authorization': `Bearer ${authStore.token}`,
        'Accept': 'application/json'
      }
    });
    const data = await response.json();
    users.value = data.users || [];
  } catch (error) {
    console.error('Error fetching users:', error);
  }
};

const fetchCourses = async () => {
  try {
    const response = await fetch('http://127.0.0.1:8000/api/admin/courses', {
      headers: {
        'Authorization': `Bearer ${authStore.token}`,
        'Content-Type': 'application/json'
      }
    });
    
    if (response.ok) {
      const data = await response.json();
      courses.value = data.courses || [];
    }
  } catch (error) {
    console.error('Error fetching courses:', error);
  }
};

const fetchOpportunities = async () => {
  try {
    const response = await fetch('/api/admin/opportunities', {
      headers: {
        'Authorization': `Bearer ${authStore.token}`,
        'Content-Type': 'application/json'
      }
    });
    
    if (response.ok) {
      const data = await response.json();
      opportunities.value = data.opportunities || [];
    }
  } catch (error) {
    console.error('Error fetching opportunities:', error);
  }
};

const fetchAnnouncements = async () => {
  try {
    const response = await fetch('/api/admin/announcements', {
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

const fetchReports = async () => {
  try {
    const response = await fetch(`/api/admin/reports?period=${reportPeriod.value}`, {
      headers: {
        'Authorization': `Bearer ${authStore.token}`,
        'Content-Type': 'application/json'
      }
    });
    
    if (response.ok) {
      const data = await response.json();
      reports.value = data.reports || [];
    }
  } catch (error) {
    console.error('Error fetching reports:', error);
  }
};

const fetchSystemStatus = async () => {
  try {
    const response = await fetch('/api/admin/system/status', {
      headers: {
        'Authorization': `Bearer ${authStore.token}`,
        'Content-Type': 'application/json'
      }
    });
    
    if (response.ok) {
      const data = await response.json();
      systemStatus.value = data.status || systemStatus.value;
    }
  } catch (error) {
    console.error('Error fetching system status:', error);
  }
};

const fetchSystemLogs = async () => {
  try {
    const response = await fetch('/api/admin/system/logs', {
      headers: {
        'Authorization': `Bearer ${authStore.token}`,
        'Content-Type': 'application/json'
      }
    });
    
    if (response.ok) {
      const data = await response.json();
      systemLogs.value = data.logs || [];
    }
  } catch (error) {
    console.error('Error fetching system logs:', error);
  }
};

const fetchConfig = async () => {
  try {
    const response = await fetch('/api/admin/config', {
      headers: {
        'Authorization': `Bearer ${authStore.token}`,
        'Content-Type': 'application/json'
      }
    });
    
    if (response.ok) {
      const data = await response.json();
      config.value = data.config || config.value;
    }
  } catch (error) {
    console.error('Error fetching config:', error);
  }
};

const fetchNotifications = async () => {
  try {
    const response = await fetch('/api/admin/notifications', {
      headers: {
        'Authorization': `Bearer ${authStore.token}`,
        'Content-Type': 'application/json'
      }
    });
    
    if (response.ok) {
      const data = await response.json();
      notifications.value = data.notifications || [];
    }
  } catch (error) {
    console.error('Error fetching notifications:', error);
  }
};

// Load all data on mount
const loadAllData = async () => {
  await Promise.all([
    fetchDashboardStats(),
    fetchUsers(),
    fetchCourses(),
    fetchOpportunities(),
    fetchAnnouncements(),
    fetchReports(),
    fetchSystemStatus(),
    fetchSystemLogs(),
    fetchConfig(),
    fetchNotifications()
  ]);
};

// Refresh methods
const refreshUsers = async () => {
  refreshingUsers.value = true;
  await fetchUsers();
  refreshingUsers.value = false;
};

const refreshCourses = async () => {
  refreshingCourses.value = true;
  await fetchCourses();
  refreshingCourses.value = false;
};

const refreshSystemStatus = async () => {
  refreshingSystem.value = true;
  await fetchSystemStatus();
  refreshingSystem.value = false;
};

// Action methods
const addUser = async () => {
  try {
    const response = await fetch('http://127.0.0.1:8000/api/admin/users', {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${authStore.token}`,
        'Content-Type': 'application/json'
      },
      body: JSON.stringify(newUser.value)
    });
    
    if (response.ok) {
      await fetchUsers();
      showAddUserModal.value = false;
      newUser.value = { name: '', email: '', role: '', password: '' };
      alert('User added successfully!');
    } else {
      alert('Error adding user');
    }
  } catch (error) {
    console.error('Error adding user:', error);
    alert('Error adding user');
  }
};

const addCourse = async () => {
  try {
    const response = await fetch('http://127.0.0.1:8000/api/admin/courses', {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${authStore.token}`,
        'Content-Type': 'application/json'
      },
      body: JSON.stringify(newCourse.value)
    });
    
    if (response.ok) {
      await fetchCourses();
      showAddCourseModal.value = false;
      newCourse.value = { title: '', description: '', category: '', difficulty_level: 'beginner', duration_hours: 0, price: 0, max_students: 0, start_date: '', end_date: '', instructor_id: '' };
      alert('Course created successfully!');
    } else {
      alert('Error creating course');
    }
  } catch (error) {
    console.error('Error adding course:', error);
    alert('Error creating course');
  }
};

const editUser = (user) => {
  // Populate edit form with user data
  newUser.value = {
    name: user.name,
    email: user.email,
    role: user.role,
    password: '' // Don't pre-fill password
  };
  showAddUserModal.value = true;
};

const toggleUserStatus = async (user) => {
  try {
    const newStatus = user.status === 'active' ? 'suspended' : 'active';
    const response = await fetch(`/api/admin/users/${user.id}/status`, {
      method: 'PUT',
      headers: {
        'Authorization': `Bearer ${authStore.token}`,
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({ status: newStatus })
    });
    
    if (response.ok) {
      user.status = newStatus;
    }
  } catch (error) {
    console.error('Error toggling user status:', error);
  }
};

const deleteUser = async (user) => {
  if (confirm(`Are you sure you want to delete ${user.name}?`)) {
    try {
      const response = await fetch(`/api/admin/users/${user.id}`, {
        method: 'DELETE',
        headers: {
          'Authorization': `Bearer ${authStore.token}`,
          'Content-Type': 'application/json'
        }
      });
      
      if (response.ok) {
        await fetchUsers();
      }
    } catch (error) {
      console.error('Error deleting user:', error);
    }
  }
};

const editCourse = (course) => {
  // Populate edit form with course data
  newCourse.value = {
    title: course.title,
    description: course.description,
    category: course.category,
    difficulty_level: course.difficulty_level || 'beginner',
    duration_hours: course.duration_hours || 0,
    price: course.price || 0,
    max_students: course.max_students || 0,
    start_date: course.start_date || '',
    end_date: course.end_date || '',
    instructor_id: course.instructor_id || ''
  };
  showAddCourseModal.value = true;
};

const manageCourseContent = (course) => {
  alert(`Managing content for: ${course.title}`);
  // TODO: Navigate to course content management
};

const assignInstructor = (course) => {
  const instructorName = prompt('Enter instructor name:', course.instructor || '');
  if (instructorName) {
    alert(`Instructor "${instructorName}" assigned to "${course.title}"`);
  }
};

const editOpportunity = (opportunity) => {
  // Populate edit form with opportunity data
  newOpportunity.value = {
    title: opportunity.title,
    description: opportunity.description,
    type: opportunity.type,
    organization: opportunity.organization,
    location: opportunity.location,
    deadline: opportunity.deadline,
    requirements: opportunity.requirements,
    benefits: opportunity.benefits,
    contact_email: opportunity.contact_email,
    visibility: opportunity.visibility,
    application_link: opportunity.application_link
  };
  showAddOpportunityModal.value = true;
};

const toggleOpportunityVisibility = async (opportunity) => {
  try {
    const newVisibility = opportunity.visibility === 'all' ? 'students' : 'all';
    const response = await fetch(`/api/admin/opportunities/${opportunity.id}/visibility`, {
      method: 'PUT',
      headers: {
        'Authorization': `Bearer ${authStore.token}`,
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({ visibility: newVisibility })
    });
    
    if (response.ok) {
      opportunity.visibility = newVisibility;
    }
  } catch (error) {
    console.error('Error toggling opportunity visibility:', error);
  }
};

const addOpportunity = async () => {
  try {
    const response = await fetch('/api/admin/opportunities', {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${authStore.token}`,
        'Content-Type': 'application/json'
      },
      body: JSON.stringify(newOpportunity.value)
    });
    
    if (response.ok) {
      await fetchOpportunities();
      showAddOpportunityModal.value = false;
      newOpportunity.value = { title: '', description: '', type: 'internship', organization: '', location: '', deadline: '', requirements: '', benefits: '', contact_email: '', visibility: 'all', application_link: '' };
    }
  } catch (error) {
    console.error('Error adding opportunity:', error);
  }
};

const sendAnnouncement = async () => {
  try {
    const recipients = Array.isArray(newAnnouncement.value.recipients)
      ? newAnnouncement.value.recipients
      : [];
    const audience =
      recipients.includes('all') || recipients.length === 0
        ? 'all'
        : recipients.includes('students') && recipients.includes('instructors')
          ? 'all'
          : recipients.includes('students')
            ? 'students'
            : recipients.includes('instructors')
              ? 'facilitators'
              : 'all';

    const response = await fetch('/api/admin/announcements', {
      method: 'POST',
      headers: {
        Authorization: `Bearer ${authStore.token}`,
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        title: newAnnouncement.value.title,
        content: newAnnouncement.value.message,
        audience,
        priority: newAnnouncement.value.priority || 'medium',
        category: 'general',
        show_on_home: true,
        show_in_portals: true,
      }),
    });

    if (response.ok) {
      showAddAnnouncementModal.value = false;
      newAnnouncement.value = { title: '', message: '', priority: 'medium', recipients: [] };
      await fetchAnnouncements();
    }
  } catch (error) {
    console.error('Error sending announcement:', error);
  }
};

const deleteOpportunity = async (opportunity) => {
  if (confirm('Delete this opportunity?')) {
    try {
      const response = await fetch(`/api/admin/opportunities/${opportunity.id}`, {
        method: 'DELETE',
        headers: {
          'Authorization': `Bearer ${authStore.token}`,
          'Content-Type': 'application/json'
        }
      });
      
      if (response.ok) {
        await fetchOpportunities();
      }
    } catch (error) {
      console.error('Error deleting opportunity:', error);
    }
  }
};

const editAnnouncement = (announcement) => {
  // Populate edit form with announcement data
  newAnnouncement.value = {
    title: announcement.title,
    message: announcement.content,
    priority: announcement.priority,
    recipients: [announcement.audience]
  };
  showAddAnnouncementModal.value = true;
};

const deleteAnnouncement = async (announcement) => {
  if (confirm('Delete this announcement?')) {
    try {
      const response = await fetch(`/api/admin/announcements/${announcement.id}`, {
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

const generateReport = async () => {
  try {
    const response = await fetch('/api/admin/reports/generate', {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${authStore.token}`,
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({ period: reportPeriod.value })
    });
    
    if (response.ok) {
      await fetchReports();
    }
  } catch (error) {
    console.error('Error generating report:', error);
  }
};

const downloadReport = (report) => {
  console.log('Downloading report:', report);
  // TODO: Implement file download
};

const saveConfig = async () => {
  try {
    const response = await fetch('/api/admin/config', {
      method: 'PUT',
      headers: {
        'Authorization': `Bearer ${authStore.token}`,
        'Content-Type': 'application/json'
      },
      body: JSON.stringify(config.value)
    });
    
    if (response.ok) {
      console.log('Configuration saved successfully');
    }
  } catch (error) {
    console.error('Error saving configuration:', error);
  }
};

const resetConfig = () => {
  if (confirm('Reset all configuration to defaults?')) {
    console.log('Resetting configuration');
    // TODO: Implement reset functionality
  }
};

const performBackup = () => {
  console.log('Performing backup...');
  // TODO: Implement backup functionality
};

// Lifecycle
onMounted(() => {
  // Fetch real dashboard data
  fetchDashboardData();
  
  // Update current time every second
  setInterval(() => {
    currentDateTime.value = new Date().toLocaleString();
  }, 1000);
});
</script>

<style scoped>
.admin-dashboard {
  padding: 0;
  background-color: #f8f9fa;
  min-height: 100vh;
}

/* Dashboard Content */
.dashboard-content {
  padding: 15px;
  height: 100%;
  overflow-y: auto;
}

.dashboard-header {
  margin-bottom: 15px;
}

.dashboard-header h1 {
  font-size: 1.5rem;
  font-weight: 700;
  color: #333;
  margin-bottom: 5px;
}

.dashboard-header p {
  color: #6c757d;
  font-size: 0.9rem;
  margin: 0;
}

/* Charts Section */
.charts-section {
  display: grid;
  grid-template-columns: 2fr 1fr;
  gap: 15px;
  margin-bottom: 15px;
}

.chart-card {
  background: white;
  border-radius: 8px;
  padding: 15px;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
}

.chart-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 10px;
}

.chart-header h3 {
  font-size: 1rem;
  font-weight: 600;
  color: #333;
  margin: 0;
}

.chart-header p {
  color: #6c757d;
  font-size: 0.8rem;
  margin: 0;
}

.chart-placeholder {
  height: 200px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  background: #f8f9fa;
  border-radius: 6px;
  color: #6c757d;
}

.chart-placeholder i {
  font-size: 2rem;
  margin-bottom: 10px;
  color: #dee2e6;
}

/* Top Courses */
.top-courses-list {
  display: flex;
  flex-direction: column;
  gap: 15px;
}

.course-item {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.course-info h4 {
  font-size: 0.95rem;
  font-weight: 600;
  color: #333;
  margin: 0;
}

.enrollment-count {
  font-size: 0.85rem;
  color: #6c757d;
}

.course-progress {
  width: 100%;
  height: 6px;
  background: #e9ecef;
  border-radius: 3px;
  overflow: hidden;
}

.progress-bar {
  height: 100%;
  background: linear-gradient(90deg, #4CAF50, #45a049);
  border-radius: 3px;
  transition: width 0.3s ease;
}

/* Activity Section */
.activity-section {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 30px;
}

.activity-card {
  background: white;
  border-radius: 12px;
  padding: 25px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

.activity-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.activity-header h3 {
  font-size: 1.2rem;
  font-weight: 600;
  color: #333;
  margin: 0;
}

.view-all-btn {
  background: none;
  border: none;
  color: #3498db;
  font-size: 0.9rem;
  cursor: pointer;
  font-weight: 500;
}

.view-all-btn:hover {
  text-decoration: underline;
}

.activity-list {
  display: flex;
  flex-direction: column;
  gap: 15px;
}

.activity-item {
  display: flex;
  align-items: center;
  gap: 15px;
  padding: 15px;
  background: #f8f9fa;
  border-radius: 8px;
  transition: background 0.3s ease;
}

.activity-item:hover {
  background: #e9ecef;
}

.activity-icon {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1rem;
  background: white;
  color: #6c757d;
}

.activity-icon.user {
  background: #e3f2fd;
  color: #1976d2;
}

.activity-icon.course {
  background: #e8f5e8;
  color: #4caf50;
}

.activity-icon.assignment {
  background: #fff3e0;
  color: #ff9800;
}

.activity-icon.payment {
  background: #f3e5f5;
  color: #9c27b0;
}

.activity-icon.role {
  background: #e8eaf6;
  color: #3f51b5;
}

.activity-details {
  flex: 1;
}

.activity-details h4 {
  font-size: 0.95rem;
  font-weight: 600;
  color: #333;
  margin: 0 0 5px 0;
}

.activity-details p {
  font-size: 0.85rem;
  color: #6c757d;
  margin: 0 0 5px 0;
}

.activity-time {
  font-size: 0.75rem;
  color: #6c757d;
}

.activity-status {
  padding: 4px 8px;
  border-radius: 12px;
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: capitalize;
}

.activity-status.completed {
  background: #e8f5e8;
  color: #4caf50;
}

.activity-status.active {
  background: #e3f2fd;
  color: #1976d2;
}

.activity-status.pending {
  background: #fff3e0;
  color: #ff9800;
}

.quick-stats {
  margin-bottom: 30px;
}

.quick-actions {
  background: white;
  border-radius: 12px;
  padding: 25px;
  margin-bottom: 30px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.actions-header {
  margin-bottom: 20px;
}

.actions-header h3 {
  margin: 0 0 5px 0;
  font-size: 1.2rem;
  color: #333;
}

.actions-header p {
  margin: 0;
  color: #666;
  font-size: 0.9rem;
}

.action-buttons {
  display: flex;
  gap: 15px;
  flex-wrap: wrap;
}

.action-btn {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 12px 20px;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  font-size: 0.9rem;
}

.action-btn.primary {
  background: linear-gradient(135deg, #ff6b35 0%, #f7931e 100%);
  color: white;
}

.action-btn.primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(255, 107, 53, 0.3);
}

.action-btn.secondary {
  background: #f8f9fa;
  color: #495057;
  border: 1px solid #dee2e6;
}

.action-btn.secondary:hover {
  background: #e9ecef;
  transform: translateY(-2px);
}

.stats-container {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 20px;
}

.stat-card {
  background: white;
  padding: 25px;
  border-radius: 12px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
  transition: transform 0.3s ease;
}

.stat-card:hover {
  transform: translateY(-5px);
}

.stat-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 15px;
}

.stat-icon {
  width: 50px;
  height: 50px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
}

.stat-trend {
  display: flex;
  align-items: center;
  gap: 5px;
  font-size: 0.9rem;
  font-weight: 600;
}

.stat-trend.up {
  color: #4CAF50;
}

.stat-trend.down {
  color: #f44336;
}

.stat-trend.stable {
  color: #FF9800;
}

.stat-number {
  font-size: 2.5rem;
  font-weight: 700;
  margin-bottom: 5px;
}

.stat-label {
  color: #666;
  font-size: 0.9rem;
}

.management-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 15px;
  margin-bottom: 15px;
}

.management-section {
  background: white;
  border-radius: 8px;
  padding: 15px;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
}

.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 15px;
  padding-bottom: 10px;
  border-bottom: 1px solid #e9ecef;
}

.section-header h2 {
  font-size: 1.1rem;
  font-weight: 600;
  gap: 10px;
  color: #333;
  font-size: 1.5rem;
}

.header-actions {
  display: flex;
  gap: 10px;
}

.btn {
  padding: 10px 20px;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 600;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  gap: 8px;
}

.btn-primary {
  background: #ff9900;
  color: white;
}

.btn-primary:hover {
  background: #e68600;
}

.btn-secondary {
  background: #6c757d;
  color: white;
}

.btn-secondary:hover {
  background: #5a6268;
}

.btn-success {
  background: #28a745;
  color: white;
}

.btn-danger {
  background: #dc3545;
  color: white;
}

.btn-info {
  background: #17a2b8;
  color: white;
}

.btn-sm {
  padding: 6px 12px;
  font-size: 0.875rem;
}

/* User Management Styles */
.user-controls {
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

.users-table-container {
  overflow-x: auto;
}

.users-table {
  width: 100%;
  border-collapse: collapse;
}

.users-table th,
.users-table td {
  padding: 15px;
  text-align: left;
  border-bottom: 1px solid #f0f0f0;
}

.users-table th {
  background: #f8f9fa;
  font-weight: 600;
  color: #333;
}

.user-info {
  display: flex;
  align-items: center;
  gap: 12px;
}

.user-avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: #ff9900;
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 600;
}

.user-name {
  font-weight: 600;
  color: #333;
}

.user-email {
  font-size: 0.85rem;
  color: #666;
}

.role-badge,
.status-badge {
  padding: 4px 12px;
  border-radius: 20px;
  font-size: 0.8rem;
  font-weight: 600;
  text-transform: uppercase;
}

.role-badge.admin {
  background: #dc3545;
  color: white;
}

.role-badge.instructor {
  background: #28a745;
  color: white;
}

.role-badge.student {
  background: #17a2b8;
  color: white;
}

.status-badge.active {
  background: #28a745;
  color: white;
}

.status-badge.suspended {
  background: #dc3545;
  color: white;
}

.status-badge.inactive {
  background: #6c757d;
  color: white;
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

.btn-edit {
  background: #17a2b8;
  color: white;
}

.btn-suspend {
  background: #ffc107;
  color: white;
}

.btn-activate {
  background: #28a745;
  color: white;
}

.btn-delete {
  background: #dc3545;
  color: white;
}

/* Course Management Styles */
.courses-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 20px;
}

.course-card {
  background: #f8f9fa;
  border-radius: 10px;
  padding: 20px;
  border: 1px solid #e9ecef;
}

.course-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 15px;
}

.course-header h3 {
  color: #333;
  font-size: 1.1rem;
}

.course-status {
  padding: 4px 10px;
  border-radius: 15px;
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
}

.course-status.active {
  background: #d4edda;
  color: #155724;
}

.course-status.draft {
  background: #fff3cd;
  color: #856404;
}

.course-instructor {
  color: #666;
  font-size: 0.9rem;
  margin-bottom: 15px;
  display: flex;
  align-items: center;
  gap: 8px;
}

.course-stats {
  display: flex;
  gap: 20px;
  margin-bottom: 15px;
}

.course-stats .stat {
  display: flex;
  flex-direction: column;
}

.stat-label {
  font-size: 0.8rem;
  color: #666;
}

.stat-value {
  font-weight: 600;
  color: #333;
}

.course-actions {
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
}

/* Opportunities & Announcements Styles */
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
  color: #ff9900;
  border-bottom-color: #ff9900;
}

.opportunities-list,
.announcements-list {
  display: grid;
  gap: 15px;
}

.opportunity-card,
.announcement-card {
  background: #f8f9fa;
  border-radius: 10px;
  padding: 20px;
  border: 1px solid #e9ecef;
}

.opportunity-header,
.announcement-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 10px;
}

.opportunity-type,
.announcement-priority {
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

.announcement-priority.high {
  background: #ffebee;
  color: #c62828;
}

.announcement-priority.medium {
  background: #fff3e0;
  color: #f57c00;
}

.opportunity-description,
.announcement-content {
  color: #666;
  margin-bottom: 15px;
  line-height: 1.5;
}

.opportunity-meta,
.announcement-meta {
  display: flex;
  gap: 20px;
  margin-bottom: 15px;
  font-size: 0.85rem;
  color: #666;
}

.opportunity-actions,
.announcement-actions {
  display: flex;
  gap: 10px;
}

/* Reports & Analytics Styles */
.analytics-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 20px;
  margin-bottom: 30px;
}

.analytics-card {
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

.reports-table {
  margin-top: 30px;
}

.reports-table table {
  width: 100%;
  border-collapse: collapse;
}

.reports-table th,
.reports-table td {
  padding: 12px;
  text-align: left;
  border-bottom: 1px solid #f0f0f0;
}

.reports-table th {
  background: #f8f9fa;
  font-weight: 600;
}

/* System Configuration Styles */
.config-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 20px;
  margin-bottom: 30px;
}

.config-card {
  background: #f8f9fa;
  border-radius: 10px;
  padding: 20px;
  border: 1px solid #e9ecef;
}

.config-card h3 {
  color: #333;
  margin-bottom: 20px;
  font-size: 1.2rem;
}

.config-item {
  margin-bottom: 20px;
}

.config-item label {
  display: block;
  margin-bottom: 8px;
  font-weight: 600;
  color: #333;
}

.config-input,
.config-select {
  width: 100%;
  padding: 10px;
  border: 1px solid #ddd;
  border-radius: 6px;
  font-size: 0.9rem;
}

.switch {
  position: relative;
  display: inline-block;
  width: 60px;
  height: 34px;
}

.switch input {
  opacity: 0;
  width: 0;
  height: 0;
}

.slider {
  position: absolute;
  cursor: pointer;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: #ccc;
  transition: .4s;
  border-radius: 34px;
}

.slider:before {
  position: absolute;
  content: "";
  height: 26px;
  width: 26px;
  left: 4px;
  bottom: 4px;
  background-color: white;
  transition: .4s;
  border-radius: 50%;
}

input:checked + .slider {
  background-color: #ff9900;
}

input:checked + .slider:before {
  transform: translateX(26px);
}

.config-actions {
  display: flex;
  gap: 15px;
}

/* System Monitoring Styles */
.system-status-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 20px;
  margin-bottom: 30px;
}

.status-card {
  background: #f8f9fa;
  border-radius: 10px;
  padding: 20px;
  border: 1px solid #e9ecef;
}

.status-card h3 {
  color: #333;
  margin-bottom: 15px;
  font-size: 1.2rem;
}

.status-indicator {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 15px;
  font-weight: 600;
}

.status-indicator.healthy {
  color: #28a745;
}

.status-indicator.warning {
  color: #ffc107;
}

.status-indicator.critical {
  color: #dc3545;
}

.status-details {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.status-details .detail {
  display: flex;
  justify-content: space-between;
  font-size: 0.9rem;
}

.system-logs {
  margin-top: 30px;
}

.system-logs h3 {
  color: #333;
  margin-bottom: 15px;
}

.log-container {
  background: #1a1a1a;
  color: #fff;
  border-radius: 8px;
  padding: 15px;
  max-height: 300px;
  overflow-y: auto;
  font-family: 'Courier New', monospace;
  font-size: 0.85rem;
}

.log-entry {
  display: flex;
  gap: 15px;
  padding: 5px 0;
  border-bottom: 1px solid #333;
}

.log-time {
  color: #999;
  min-width: 120px;
}

.log-level {
  min-width: 60px;
  font-weight: 600;
}

.log-entry.info .log-level {
  color: #17a2b8;
}

.log-entry.warning .log-level {
  color: #ffc107;
}

.log-entry.error .log-level {
  color: #dc3545;
}

.log-message {
  flex: 1;
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
  z-index: 9999;
}

.modal {
  background: white;
  border-radius: 12px;
  width: 90%;
  max-width: 500px;
  max-height: 90vh;
  overflow-y: auto;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px;
  border-bottom: 1px solid #f0f0f0;
}

.modal-header h3 {
  color: #333;
  margin: 0;
}

.btn-close {
  background: none;
  border: none;
  font-size: 2rem;
  cursor: pointer;
  color: #999;
}

.modal-body {
  padding: 20px;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px;
  border-bottom: 1px solid #eee;
}

.modal-header h3 {
  margin: 0;
  font-size: 1.2rem;
  color: #333;
}

.close-btn {
  background: none;
  border: none;
  font-size: 1.5rem;
  cursor: pointer;
  color: #999;
  padding: 0;
  width: 30px;
  height: 30px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.close-btn:hover {
  color: #333;
}

.form-group {
  margin-bottom: 20px;
}

.form-group label {
  display: block;
  margin-bottom: 5px;
  font-weight: 600;
  color: #333;
}

.form-group input,
.form-group select,
.form-group textarea {
  width: 100%;
  padding: 10px;
  border: 1px solid #ddd;
  border-radius: 6px;
  font-size: 0.9rem;
}

.form-group textarea {
  resize: vertical;
  min-height: 80px;
}

.checkbox-group {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.checkbox-group label {
  display: flex;
  align-items: center;
  gap: 8px;
  font-weight: normal;
  color: #333;
}

.checkbox-group input[type="checkbox"] {
  width: auto;
  margin: 0;
}

.form-actions {
  display: flex;
  gap: 10px;
  justify-content: flex-end;
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
  border-top: 5px solid #ff9900;
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
  .admin-dashboard {
    padding: 10px;
  }
  
  .header-content {
    flex-direction: column;
    gap: 20px;
    text-align: center;
  }
  
  .header-right {
    flex-direction: column;
    gap: 15px;
  }
  
  .stats-container {
    grid-template-columns: 1fr;
  }
  
  .user-controls {
    flex-direction: column;
  }
  
  .filter-controls {
    flex-direction: column;
  }
  
  .courses-grid {
    grid-template-columns: 1fr;
  }
  
  .analytics-grid {
    grid-template-columns: 1fr;
  }
  
  .config-grid {
    grid-template-columns: 1fr;
  }
  
  .system-status-grid {
    grid-template-columns: 1fr;
  }
  
  .action-buttons {
    flex-direction: column;
  }
  
  .course-actions {
    flex-direction: column;
  }
  
  .opportunity-actions,
  .announcement-actions {
    flex-direction: column;
  }
}
</style>
