<template>
  <div class="admin-facilitators">
    <!-- Header -->
    <div class="facilitators-header">
      <div class="header-content">
        <div class="header-left">
          <h1><i class="fas fa-chalkboard-teacher"></i> Facilitators</h1>
          <p>Manage all facilitators and their assigned courses</p>
        </div>
        <div class="header-actions">
          <button @click="showAddFacilitatorModal = true" class="btn btn-primary">
            <i class="fas fa-user-plus"></i> Add Facilitator
          </button>
        </div>
      </div>
    </div>

    <!-- Filters -->
    <div class="filters-section">
      <div class="filters-left">
        <div class="search-bar">
          <i class="fas fa-search"></i>
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Search facilitators..."
            @input="filterFacilitators"
          >
        </div>
        <select v-model="statusFilter" @change="filterFacilitators" class="filter-select">
          <option value="">All Status</option>
          <option value="active">Active</option>
          <option value="inactive">Inactive</option>
        </select>
      </div>
      <div class="filters-right">
        <button @click="refreshFacilitators" class="btn btn-secondary" :disabled="refreshing">
          <i :class="refreshing ? 'fas fa-spinner fa-spin' : 'fas fa-sync-alt'"></i>
          Refresh
        </button>
      </div>
    </div>

    <!-- Facilitators Table -->
    <div class="facilitators-table-container">
      <!-- Loading State -->
      <div v-if="loading" class="loading-state">
        <div class="spinner"></div>
        <p>Loading facilitators...</p>
      </div>

      <!-- Table Content -->
      <table v-else class="facilitators-table">
        <thead>
          <tr>
            <th>Facilitator</th>
            <th>Email</th>
            <th>Assigned Courses</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="facilitator in filteredFacilitators" :key="facilitator.id">
            <td>
              <div class="facilitator-info">
                <div class="facilitator-avatar">
                  {{ (facilitator.name || '?').charAt(0).toUpperCase() }}
                </div>
                <div class="facilitator-details">
                  <div class="facilitator-name">{{ facilitator.name }}</div>
                  <div class="facilitator-id">ID: {{ facilitator.id }}</div>
                </div>
              </div>
            </td>
            <td>{{ facilitator.email }}</td>
            <td>
              <div class="assigned-courses">
                <span v-if="facilitator.courses && facilitator.courses.length > 0">
                  {{ facilitator.courses.map(c => c.title).join(', ') }}
                </span>
                <span v-else class="no-courses">No courses assigned</span>
              </div>
            </td>
            <td>
              <span class="status-badge" :class="facilitator.status">
                {{ facilitator.status || 'active' }}
              </span>
            </td>
            <td>
              <div class="action-buttons">
                <button @click="viewFacilitator(facilitator)" class="action-btn view" title="View Profile">
                  <i class="fas fa-eye"></i>
                </button>
                <button @click="editFacilitator(facilitator)" class="action-btn edit" title="Edit Facilitator">
                  <i class="fas fa-edit"></i>
                </button>
                <button
                  @click="assignCourses(facilitator)"
                  class="action-btn assign"
                  title="Assign Courses"
                  :disabled="assigningSaving"
                >
                  <i v-if="!assigningSaving" class="fas fa-graduation-cap"></i>
                  <i v-else class="fas fa-spinner fa-spin"></i>
                </button>
                <button
                  @click="removeFacilitator(facilitator)"
                  class="action-btn delete"
                  title="Remove Facilitator"
                  :disabled="removingId === facilitator.id"
                >
                  <i v-if="removingId !== facilitator.id" class="fas fa-trash"></i>
                  <i v-else class="fas fa-spinner fa-spin"></i>
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>

      <!-- Empty State -->
      <div v-if="!loading && filteredFacilitators.length === 0" class="empty-state">
        <i class="fas fa-chalkboard-teacher"></i>
        <h3>No facilitators found</h3>
        <p v-if="searchQuery || statusFilter">Try adjusting your search or filters</p>
        <p v-else>No facilitators have been added yet. Click "Add Facilitator" to get started.</p>
      </div>
    </div>

    <!-- Facilitator capability overview (DB-backed; mirrors what instructors do in-portal) -->
    <section class="workspace-intro">
      <h2 class="workspace-title">Facilitator workspace</h2>
      <p class="workspace-desc">
        Live snapshot from the API: course catalog, assessments, learners, analytics, communication, and platform settings.
        Use the shortcuts to open the full admin tools for each area.
      </p>
    </section>

    <div class="capability-grid">
      <section class="capability-card">
        <div class="capability-header">
          <i class="fas fa-book"></i>
          <h2>Courses</h2>
        </div>
        <div class="capability-body">
          <div class="cap-meta" v-if="coursesSummaryLoading">
            <div class="mini-spinner"></div>
            <span>Loading course overview...</span>
          </div>
          <div v-else>
            <div class="kpis">
              <div class="kpi">
                <div class="kpi-value">{{ coursesTotalCount }}</div>
                <div class="kpi-label">Total courses</div>
              </div>
              <div class="kpi">
                <div class="kpi-value">{{ coursesAssignedCount }}</div>
                <div class="kpi-label">Courses with instructors</div>
              </div>
            </div>

            <div class="subsection">
              <div class="subsection-title">Recent courses</div>
              <div v-if="recentCoursesLoading" class="subtle-muted">
                Loading...
              </div>
              <div v-else>
                <div v-if="recentCourses.length === 0" class="empty-inline">
                  <i class="fas fa-graduation-cap"></i>
                  <span>No recent courses yet.</span>
                </div>
                <ul v-else class="list-compact">
                  <li v-for="c in recentCourses" :key="c.id">
                    <span class="list-title">{{ c.title }}</span>
                    <span class="list-muted">{{ c.category || '—' }} • {{ c.difficulty_level || '—' }}</span>
                  </li>
                </ul>
              </div>
            </div>
          </div>
        </div>
        <div class="capability-footer">
          <router-link class="cap-link" to="/admin/courses">Manage courses</router-link>
          <router-link class="cap-link" to="/admin/categories">Categories</router-link>
        </div>
      </section>

      <section class="capability-card">
        <div class="capability-header">
          <i class="fas fa-clipboard-check"></i>
          <h2>Assessments</h2>
        </div>
        <div class="capability-body">
          <p class="cap-hint">Create and grade work in Assignments &amp; Exams; module quizzes are edited from each course.</p>
          <div class="tab-row">
            <button class="tab" :class="{active: assessmentsTab==='assignments'}" @click="assessmentsTab='assignments'">
              Assignments
            </button>
            <button class="tab" :class="{active: assessmentsTab==='exams'}" @click="assessmentsTab='exams'">
              Exams
            </button>
          </div>

          <div v-if="assessmentsLoading" class="subtle-muted">
            Loading assessments...
          </div>

          <div v-else>
            <div v-if="assessmentsTab==='assignments'">
              <div class="subsection">
                <div class="subsection-title">Assignments</div>
                <div v-if="assignments.length===0" class="empty-inline">
                  <i class="fas fa-file-alt"></i>
                  <span>No assignments found.</span>
                </div>
                <ul v-else class="list-compact">
                  <li v-for="a in assignments" :key="a.id">
                    <span class="list-title">{{ a.title }}</span>
                    <span class="list-muted">
                      {{ a.course_title || '—' }} • due {{ a.due_date ? formatDate(a.due_date) : '—' }}
                      <span v-if="a.submitted_count != null" class="grade-pill">{{ a.graded_count || 0 }}/{{ a.submitted_count || 0 }} graded</span>
                    </span>
                  </li>
                </ul>
              </div>
            </div>

            <div v-else>
              <div class="subsection">
                <div class="subsection-title">Exams</div>
                <div v-if="exams.length===0" class="empty-inline">
                  <i class="fas fa-poll"></i>
                  <span>No exams found.</span>
                </div>
                <ul v-else class="list-compact">
                  <li v-for="e in exams" :key="e.id">
                    <span class="list-title">{{ e.title }}</span>
                    <span class="list-muted">{{ e.course_title || '—' }} • {{ formatDate(e.start_date) }}</span>
                  </li>
                </ul>
              </div>
            </div>
          </div>
        </div>
        <div class="capability-footer">
          <router-link class="cap-link" to="/admin/assignments">Assignments</router-link>
          <router-link class="cap-link" to="/admin/exams">Exams</router-link>
        </div>
      </section>

      <section class="capability-card">
        <div class="capability-header">
          <i class="fas fa-users"></i>
          <h2>Students</h2>
        </div>
        <div class="capability-body">
          <div v-if="!studentsLoading && enrollmentStats" class="kpis kpis-tight">
            <div class="kpi kpi-small">
              <div class="kpi-value">{{ enrollmentStats.total_enrollments ?? 0 }}</div>
              <div class="kpi-label">Enrollments</div>
            </div>
            <div class="kpi kpi-small">
              <div class="kpi-value">{{ enrollmentStats.active_enrollments ?? 0 }}</div>
              <div class="kpi-label">Active</div>
            </div>
            <div class="kpi kpi-small">
              <div class="kpi-value">{{ enrollmentStats.completed_enrollments ?? 0 }}</div>
              <div class="kpi-label">Completed</div>
            </div>
          </div>
          <div class="subsection">
            <div class="subsection-title">Enrolled learners</div>
            <div v-if="studentsLoading" class="subtle-muted">Loading students...</div>
            <div v-else>
              <div v-if="students.length===0" class="empty-inline">
                <i class="fas fa-user-graduate"></i>
                <span>No students available.</span>
              </div>
              <ul v-else class="list-compact">
                <li v-for="s in students.slice(0, 8)" :key="s.id">
                  <span class="list-title">{{ s.name }}</span>
                  <span class="list-muted">{{ s.email }}</span>
                </li>
              </ul>
              <div v-if="students.length>8" class="subtle-muted">Showing first 8 of {{ students.length }}</div>
            </div>
          </div>
        </div>
        <div class="capability-footer">
          <router-link class="cap-link" to="/admin/enrollments">Enrollments</router-link>
          <router-link class="cap-link" to="/admin/users">Users</router-link>
        </div>
      </section>

      <section class="capability-card">
        <div class="capability-header">
          <i class="fas fa-chart-line"></i>
          <h2>Analytics</h2>
        </div>
        <div class="capability-body">
          <div v-if="analyticsLoading" class="subtle-muted">Loading analytics...</div>
          <div v-else>
            <div class="kpis">
              <div class="kpi">
                <div class="kpi-value">{{ stats?.completionRate ?? 0 }}%</div>
                <div class="kpi-label">Completion rate</div>
              </div>
              <div class="kpi">
                <div class="kpi-value">{{ stats?.complianceRate ?? 0 }}%</div>
                <div class="kpi-label">Compliance rate</div>
              </div>
              <div class="kpi">
                <div class="kpi-value">{{ stats?.totalUsers ?? 0 }}</div>
                <div class="kpi-label">Total users</div>
              </div>
              <div class="kpi">
                <div class="kpi-value">{{ stats?.activeCourses ?? 0 }}</div>
                <div class="kpi-label">Published courses</div>
              </div>
            </div>

            <div class="subsection">
              <div class="subsection-title">Recent activity</div>
              <div v-if="recentActivityLoading" class="subtle-muted">Loading activity...</div>
              <div v-else>
                <div v-if="recentActivities.length===0" class="empty-inline">
                  <i class="fas fa-history"></i>
                  <span>No recent activity.</span>
                </div>
                <ul v-else class="list-compact">
                  <li v-for="a in recentActivities" :key="a.id">
                    <span class="list-title">{{ a.title }}</span>
                    <span class="list-muted">{{ a.user }} • {{ formatActivityTime(a.timestamp) }}</span>
                  </li>
                </ul>
              </div>
            </div>
          </div>
        </div>
        <div class="capability-footer">
          <router-link class="cap-link" to="/admin/analytics">Analytics</router-link>
          <router-link class="cap-link" to="/admin/reports">Reports</router-link>
        </div>
      </section>

      <section class="capability-card">
        <div class="capability-header">
          <i class="fas fa-comments"></i>
          <h2>Communication</h2>
        </div>
        <div class="capability-body">
          <div class="tab-row">
            <button class="tab" :class="{active: commTab==='messages'}" @click="commTab='messages'">Messages</button>
            <button class="tab" :class="{active: commTab==='announcements'}" @click="commTab='announcements'">Announcements</button>
            <button class="tab" :class="{active: commTab==='discussions'}" @click="commTab='discussions'">Discussions</button>
          </div>

          <div v-if="communicationLoading" class="subtle-muted">Loading communication...</div>
          <div v-else>
            <div v-if="commTab==='messages'">
              <div class="subsection">
                <div class="subsection-title">Recent messages</div>
                <div v-if="messages.length===0" class="empty-inline">
                  <i class="fas fa-envelope"></i>
                  <span>No messages.</span>
                </div>
                <ul v-else class="list-compact">
                  <li v-for="m in messages.slice(0,8)" :key="m.id">
                    <span class="list-title">{{ m.subject }}</span>
                    <span class="list-muted">{{ m.recipients || '—' }} • {{ formatActivityTime(m.timestamp) }}</span>
                  </li>
                </ul>
              </div>
            </div>

            <div v-else-if="commTab==='announcements'">
              <div class="subsection">
                <div class="subsection-title">Recent announcements</div>
                <div v-if="announcements.length===0" class="empty-inline">
                  <i class="fas fa-bullhorn"></i>
                  <span>No announcements.</span>
                </div>
                <ul v-else class="list-compact">
                  <li v-for="a in announcements.slice(0,8)" :key="a.id">
                    <span class="list-title">{{ a.title }}</span>
                    <span class="list-muted">{{ a.audience || '—' }} • {{ a.starts_at ? formatDate(a.starts_at) : '—' }}</span>
                  </li>
                </ul>
              </div>
            </div>

            <div v-else-if="commTab==='discussions'" class="subsection">
              <div class="subsection-title">Discussions</div>
              <p class="discussions-copy">
                Threaded forums are not a separate API in this build. Use <strong>Announcements</strong> for cohort-wide updates
                and <strong>Notifications</strong> for targeted outreach; course Q&amp;A can be handled inside course content and enrollments.
              </p>
              <div class="cap-inline-links">
                <router-link class="cap-link" to="/admin/announcements">Announcements hub</router-link>
                <router-link class="cap-link" to="/admin/notifications">Notifications</router-link>
              </div>
            </div>
          </div>
        </div>
        <div class="capability-footer">
          <router-link class="cap-link" to="/admin/notifications">Send notification</router-link>
          <router-link class="cap-link" to="/admin/announcements">New announcement</router-link>
        </div>
      </section>

      <section class="capability-card">
        <div class="capability-header">
          <i class="fas fa-cog"></i>
          <h2>Settings</h2>
        </div>
        <div class="capability-body">
          <div v-if="settingsLoading" class="subtle-muted">Loading settings...</div>
          <div v-else>
            <div class="subsection">
              <div class="subsection-title">System settings (group)</div>
              <div class="form-row">
                <label class="label">Group</label>
                <select v-model="settingsGroup" class="input">
                  <option value="">All</option>
                  <option value="portal">portal</option>
                  <option value="content">content</option>
                  <option value="security">security</option>
                </select>
              </div>

              <div class="settings-list">
                <div v-if="settings.length===0" class="empty-inline">
                  <i class="fas fa-sliders-h"></i>
                  <span>No settings returned.</span>
                </div>

                <div v-else v-for="s in settings" :key="s.id" class="setting-row">
                  <div class="setting-key">{{ s.key }}</div>
                  <div class="setting-value">
                    <select v-if="s.type === 'boolean'" v-model="s.value" class="input">
                      <option :value="true">On</option>
                      <option :value="false">Off</option>
                    </select>
                    <input v-else-if="s.type === 'text' || s.type === 'number'" v-model="s.value" class="input" :type="s.type === 'number' ? 'number' : 'text'" />
                    <textarea v-else v-model="s.value" class="input textarea" rows="3" />
                  </div>
                </div>
              </div>

              <div class="form-actions">
                <button class="btn btn-secondary" :disabled="settingsSaving" @click="reloadSettings">
                  <i :class="settingsSaving ? 'fas fa-spinner fa-spin' : 'fas fa-sync-alt'"></i>
                  Reload
                </button>
                <button class="btn btn-primary" :disabled="settingsSaving" @click="saveSettings">
                  <i :class="settingsSaving ? 'fas fa-spinner fa-spin' : 'fas fa-save'"></i>
                  Save
                </button>
              </div>
            </div>

            <div class="subtle-muted" v-if="settingsError">{{ settingsError }}</div>
          </div>
        </div>
        <div class="capability-footer">
          <router-link class="cap-link" to="/admin/settings">Full settings</router-link>
          <router-link class="cap-link" to="/admin/enrollments">Enrollment rules</router-link>
        </div>
      </section>
    </div>

    <!-- Add Facilitator Modal -->
    <div v-if="showAddFacilitatorModal" class="modal-overlay" @click="showAddFacilitatorModal = false">
      <div class="modal" @click.stop>
        <div class="modal-header">
          <h3>Add New Facilitator</h3>
          <button @click="showAddFacilitatorModal = false" class="btn-close">×</button>
        </div>
        <div class="modal-body">
          <form @submit.prevent="addFacilitator">
            <div class="form-group">
              <label>Name</label>
              <input v-model="newFacilitator.name" type="text" required>
            </div>
            <div class="form-group">
              <label>Email</label>
              <input v-model="newFacilitator.email" type="email" required>
            </div>
            <div class="form-group">
              <label>Password</label>
              <input v-model="newFacilitator.password" type="password" required>
            </div>
            <div class="form-group">
              <label>Specialization</label>
              <input v-model="newFacilitator.specialization" type="text" placeholder="e.g. Web Development, Data Science">
            </div>
            <div class="form-group">
              <label>Assign Courses</label>
              <select v-model="newFacilitator.course_ids" multiple size="4">
                <option v-for="course in availableCourses" :key="course.id" :value="course.id">
                  {{ course.title }}
                </option>
              </select>
              <small>Select multiple courses or leave empty to assign later</small>
            </div>
            <div class="form-actions">
              <button type="submit" class="btn btn-primary" :disabled="addingFacilitator">
                <i v-if="!addingFacilitator" class="fas fa-plus"></i>
                <i v-else class="fas fa-spinner fa-spin"></i>
                <span v-if="!addingFacilitator"> Add Facilitator</span>
                <span v-else> Adding...</span>
              </button>
              <button type="button" @click="showAddFacilitatorModal = false" class="btn btn-secondary" :disabled="addingFacilitator">Cancel</button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Assign Courses Modal -->
    <div v-if="showAssignCoursesModal" class="modal-overlay" @click="showAssignCoursesModal = false">
      <div class="modal" @click.stop>
        <div class="modal-header">
          <h3>Assign Courses to {{ selectedFacilitator?.name }}</h3>
          <button @click="showAssignCoursesModal = false" class="btn-close">×</button>
        </div>
        <div class="modal-body">
          <form @submit.prevent="saveCourseAssignments">
            <div class="form-group">
              <label>Select Courses</label>
              <div class="course-checkboxes">
                <label v-for="course in availableCourses" :key="course.id" class="course-checkbox">
                  <input
                    type="checkbox"
                    :value="course.id"
                    v-model="selectedCourseIds"
                  >
                  <span class="course-info">
                    <strong>{{ course.title }}</strong>
                    <small>{{ course.category }} • {{ course.difficulty_level }}</small>
                  </span>
                </label>
              </div>
            </div>
            <div class="form-actions">
              <button type="submit" class="btn btn-primary" :disabled="assigningSaving">
                <i v-if="!assigningSaving" class="fas fa-save"></i>
                <i v-else class="fas fa-spinner fa-spin"></i>
                <span v-if="!assigningSaving"> Save Assignments</span>
                <span v-else> Saving...</span>
              </button>
              <button type="button" @click="showAssignCoursesModal = false" class="btn btn-secondary" :disabled="assigningSaving">Cancel</button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- View Facilitator Modal -->
    <div v-if="showViewFacilitatorModal" class="modal-overlay" @click="showViewFacilitatorModal=false">
      <div class="modal" @click.stop>
        <div class="modal-header">
          <h3>Facilitator Profile</h3>
          <button @click="showViewFacilitatorModal=false" class="btn-close">×</button>
        </div>
        <div class="modal-body">
          <div v-if="viewFacilitatorLoading" class="loading-state" style="padding:0">
            <div class="spinner"></div>
            <p>Loading facilitator...</p>
          </div>
          <div v-else>
            <div class="profile-summary">
              <div class="profile-avatar-big">
                {{ (selectedFacilitatorForView?.name || '?').charAt(0).toUpperCase() }}
              </div>
              <div class="profile-summary-meta">
                <div class="profile-name">{{ selectedFacilitatorForView?.name }}</div>
                <div class="profile-email">{{ selectedFacilitatorForView?.email }}</div>
                <div class="profile-id">ID: {{ selectedFacilitatorForView?.id }}</div>
              </div>
            </div>

            <div class="subsection">
              <div class="subsection-title">Specialization</div>
              <div class="subtle-muted">{{ selectedFacilitatorForView?.department || selectedFacilitatorForView?.specialization || '—' }}</div>
            </div>

            <div class="subsection">
              <div class="subsection-title">Assigned Courses</div>
              <ul v-if="selectedFacilitatorForView?.courses?.length" class="list-compact">
                <li v-for="c in selectedFacilitatorForView.courses" :key="c.id">
                  <span class="list-title">{{ c.title }}</span>
                  <span class="list-muted">{{ c.category || '—' }}</span>
                </li>
              </ul>
              <div v-else class="empty-inline">
                <i class="fas fa-graduation-cap"></i>
                <span>No courses assigned</span>
              </div>
            </div>

            <div class="form-actions">
              <button class="btn btn-secondary" @click="showViewFacilitatorModal=false">Close</button>
              <button
                class="btn btn-primary"
                @click="{
                  showViewFacilitatorModal=false;
                  editFacilitator(selectedFacilitatorForView);
                }"
              >
                <i class="fas fa-edit"></i>
                Edit
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Edit Facilitator Modal -->
    <div v-if="showEditFacilitatorModal" class="modal-overlay" @click="showEditFacilitatorModal=false">
      <div class="modal" @click.stop>
        <div class="modal-header">
          <h3>Edit Facilitator</h3>
          <button @click="showEditFacilitatorModal=false" class="btn-close">×</button>
        </div>
        <div class="modal-body">
          <div v-if="editFacilitatorLoading" class="loading-state" style="padding:0">
            <div class="spinner"></div>
            <p>Loading facilitator...</p>
          </div>
          <div v-else>
            <div class="form-group">
              <label>Name</label>
              <input v-model="editFacilitatorForm.name" type="text" />
            </div>

            <div class="form-group">
              <label>Email</label>
              <input v-model="editFacilitatorForm.email" type="email" />
            </div>

            <div class="form-group">
              <label>Specialization</label>
              <input v-model="editFacilitatorForm.specialization" type="text" placeholder="e.g. Data Science" />
              <small class="subtle-muted">Note: backend stores this as `department` for facilitator user.</small>
            </div>

            <div class="form-group">
              <label>Status</label>
              <select v-model="editFacilitatorForm.status" class="filter-select">
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
              </select>
            </div>

            <div class="form-actions">
              <button class="btn btn-secondary" @click="showEditFacilitatorModal=false">Cancel</button>
              <button
                class="btn btn-primary"
                :disabled="editSaving"
                @click="saveEditedFacilitator"
              >
                <i v-if="!editSaving" class="fas fa-save"></i>
                <i v-else class="fas fa-spinner fa-spin"></i>
                Save
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Success/Error Messages -->
    <div v-if="message" class="message" :class="messageType">
      <i :class="messageType === 'success' ? 'fas fa-check-circle' : 'fas fa-exclamation-circle'"></i>
      {{ message }}
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount, watch } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { API_BASE_URL } from '@/config/api'

const authStore = useAuthStore()

const adminUrl = (path) =>
  `${API_BASE_URL}${path.startsWith('/') ? path : `/${path}`}`

// State
const facilitators = ref([])
const availableCourses = ref([])
const searchQuery = ref('')
const statusFilter = ref('')
const refreshing = ref(false)
const loading = ref(true)
const showAddFacilitatorModal = ref(false)
const showAssignCoursesModal = ref(false)
const selectedFacilitator = ref(null)
const selectedCourseIds = ref([])
const message = ref('')
const messageType = ref('')

// View/Edit modal state
const showViewFacilitatorModal = ref(false)
const showEditFacilitatorModal = ref(false)
const viewFacilitatorLoading = ref(false)
const editFacilitatorLoading = ref(false)
const selectedFacilitatorForView = ref(null)

const editFacilitatorForm = ref({
  id: null,
  name: '',
  email: '',
  specialization: '',
  status: 'active'
})

const editSaving = ref(false)

// UI submit/loading states
const addingFacilitator = ref(false)
const assigningSaving = ref(false)
const removingId = ref(null)

// Capability dashboard state
const assessmentsTab = ref('assignments')
const assessmentsLoading = ref(false)
const assignments = ref([])
const exams = ref([])

const coursesSummaryLoading = ref(false)
const recentCoursesLoading = ref(false)
const coursesTotalCount = ref(0)
const coursesAssignedCount = ref(0)
const recentCourses = ref([])

const studentsLoading = ref(false)
const students = ref([])

const analyticsLoading = ref(false)
const recentActivityLoading = ref(false)
const stats = ref(null)
const recentActivities = ref([])

const commTab = ref('messages')
const communicationLoading = ref(false)
const messages = ref([])
const announcements = ref([])

const enrollmentStats = ref(null)

const settingsLoading = ref(false)
const settingsSaving = ref(false)
const settingsGroup = ref('')
const settingsError = ref('')
const settings = ref([])

// New facilitator form
const newFacilitator = ref({
  name: '',
  email: '',
  password: '',
  specialization: '',
  course_ids: []
})

const authHeaders = (json = true) => {
  const token = authStore.token
  const h = {
    Authorization: `Bearer ${token}`,
    Accept: 'application/json'
  }
  if (json) h['Content-Type'] = 'application/json'
  return h
}

const showMessage = (text, type) => {
  message.value = text
  messageType.value = type
  setTimeout(() => {
    message.value = ''
  }, 4000)
}

// Computed
const filteredFacilitators = computed(() => {
  let filtered = facilitators.value

  // Search filter
  if (searchQuery.value) {
    const q = searchQuery.value.toLowerCase()
    filtered = filtered.filter(
      f =>
        (f.name || '').toLowerCase().includes(q) ||
        (f.email || '').toLowerCase().includes(q)
    )
  }

  // Status filter
  if (statusFilter.value) {
    filtered = filtered.filter(facilitator => facilitator.status === statusFilter.value)
  }

  return filtered
})

const formatActivityTime = (ts) => {
  if (ts === undefined || ts === null || ts === '') return '—'
  const n = typeof ts === 'number' ? ts : Number(ts)
  if (Number.isNaN(n)) {
    const d = new Date(String(ts))
    return Number.isNaN(d.getTime()) ? '—' : d.toLocaleString()
  }
  const ms = n < 1e12 ? n * 1000 : n
  const d = new Date(ms)
  if (Number.isNaN(d.getTime())) return '—'
  return d.toLocaleString(undefined, { month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' })
}

const fetchCourseDashboard = async () => {
  coursesSummaryLoading.value = true
  recentCoursesLoading.value = true
  try {
    const response = await fetch(adminUrl('/admin/courses'), { headers: authHeaders(false) })
    if (!response.ok) throw new Error(String(response.status))
    const data = await response.json()
    const courses = data.data || data.courses || []
    coursesTotalCount.value = courses.length
    coursesAssignedCount.value = courses.filter((c) => c.instructor_id != null).length
    recentCourses.value = [...courses]
      .sort((a, b) => {
        const ta = new Date(a.created_at || 0).getTime()
        const tb = new Date(b.created_at || 0).getTime()
        return tb - ta
      })
      .slice(0, 8)
  } catch (e) {
    console.error('fetchCourseDashboard', e)
    showMessage('Could not load course overview from the API.', 'error')
  } finally {
    coursesSummaryLoading.value = false
    recentCoursesLoading.value = false
  }
}

const fetchAssessments = async () => {
  assessmentsLoading.value = true
  try {
    const [aRes, eRes] = await Promise.all([
      fetch(adminUrl('/admin/assignments'), { headers: authHeaders(false) }),
      fetch(adminUrl('/admin/exams'), { headers: authHeaders(false) })
    ])
    if (aRes.ok) {
      const j = await aRes.json()
      assignments.value = j.data || []
    } else assignments.value = []
    if (eRes.ok) {
      const j = await eRes.json()
      exams.value = j.data || []
    } else exams.value = []
  } catch (e) {
    console.error('fetchAssessments', e)
    showMessage('Could not load assessments.', 'error')
  } finally {
    assessmentsLoading.value = false
  }
}

const fetchStudents = async () => {
  studentsLoading.value = true
  try {
    const [stRes, enRes] = await Promise.all([
      fetch(adminUrl('/admin/students'), { headers: authHeaders(false) }),
      fetch(adminUrl('/admin/enrollments/stats'), { headers: authHeaders(false) })
    ])
    if (stRes.ok) {
      const j = await stRes.json()
      students.value = j.data || []
    } else students.value = []
    if (enRes.ok) {
      enrollmentStats.value = await enRes.json()
    } else enrollmentStats.value = null
  } catch (e) {
    console.error('fetchStudents', e)
    showMessage('Could not load students or enrollment stats.', 'error')
  } finally {
    studentsLoading.value = false
  }
}

const fetchAnalytics = async () => {
  analyticsLoading.value = true
  recentActivityLoading.value = true
  try {
    const [sRes, actRes] = await Promise.all([
      fetch(adminUrl('/admin/stats'), { headers: authHeaders(false) }),
      fetch(adminUrl('/admin/dashboard/recent-activities'), { headers: authHeaders(false) })
    ])
    if (sRes.ok) {
      stats.value = await sRes.json()
    } else stats.value = null
    if (actRes.ok) {
      const j = await actRes.json()
      recentActivities.value = j.activities || []
    } else recentActivities.value = []
  } catch (e) {
    console.error('fetchAnalytics', e)
    showMessage('Could not load analytics.', 'error')
  } finally {
    analyticsLoading.value = false
    recentActivityLoading.value = false
  }
}

const fetchCommunication = async () => {
  communicationLoading.value = true
  try {
    const [mRes, annRes] = await Promise.all([
      fetch(adminUrl('/admin/messages'), { headers: authHeaders(false) }),
      fetch(adminUrl('/admin/announcements'), { headers: authHeaders(false) })
    ])
    if (mRes.ok) {
      messages.value = await mRes.json()
      if (!Array.isArray(messages.value)) messages.value = []
    } else messages.value = []
    if (annRes.ok) {
      const j = await annRes.json()
      announcements.value = j.announcements || []
    } else announcements.value = []
  } catch (e) {
    console.error('fetchCommunication', e)
    showMessage('Could not load messages or announcements.', 'error')
  } finally {
    communicationLoading.value = false
  }
}

const fetchSettings = async () => {
  settingsLoading.value = true
  settingsError.value = ''
  try {
    const q = settingsGroup.value
      ? `?group=${encodeURIComponent(settingsGroup.value)}`
      : ''
    const response = await fetch(adminUrl(`/admin/settings${q}`), { headers: authHeaders(false) })
    if (!response.ok) {
      const err = await response.json().catch(() => null)
      settingsError.value =
        (err && (err.message || err.error)) || `Settings request failed (${response.status})`
      settings.value = []
      return
    }
    const j = await response.json()
    const raw = j.settings || []
    settings.value = raw.map((s) => ({
      ...s,
      value:
        s.type === 'boolean'
          ? s.value === true || s.value === 'true' || s.value === 1 || s.value === '1'
          : s.value
    }))
  } catch (e) {
    console.error('fetchSettings', e)
    settingsError.value = 'Could not load settings.'
    settings.value = []
  } finally {
    settingsLoading.value = false
  }
}

const reloadSettings = () => {
  fetchSettings()
}

const saveSettings = async () => {
  settingsSaving.value = true
  settingsError.value = ''
  try {
    const payload = {
      settings: settings.value.map((s) => {
        let val = s.value
        if (s.type === 'boolean') {
          val = val === true || val === 'true' || val === 1 || val === '1'
        } else if (s.type === 'number' && val !== '' && val != null) {
          val = Number(val)
        }
        return {
          key: s.key,
          value: val,
          type: s.type,
          group: s.group
        }
      })
    }
    const response = await fetch(adminUrl('/admin/settings'), {
      method: 'PUT',
      headers: authHeaders(),
      body: JSON.stringify(payload)
    })
    if (!response.ok) {
      const err = await response.json().catch(() => null)
      settingsError.value =
        (err && (err.message || JSON.stringify(err.errors || err))) || `Save failed (${response.status})`
      return
    }
    showMessage('Settings saved', 'success')
    await fetchSettings()
  } catch (e) {
    console.error('saveSettings', e)
    settingsError.value = 'Could not save settings.'
  } finally {
    settingsSaving.value = false
  }
}

watch(settingsGroup, () => {
  fetchSettings()
})

const formatDate = (dateString) => {
  if (!dateString) return '—'
  const d = new Date(dateString)
  if (Number.isNaN(d.getTime())) return '—'
  return d.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' })
}

const fetchFacilitators = async () => {

  try {
    const response = await fetch(adminUrl('/admin/facilitators'), {
      headers: authHeaders(false)
    })
    
    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`)
    }
    
    const data = await response.json()
    facilitators.value = data.data || []
    console.log('Facilitators loaded:', facilitators.value.length)
  } catch (error) {
    console.error('Error fetching facilitators:', error)
    showMessage('Error loading facilitators. Please check your connection.', 'error')
  } finally {
    loading.value = false
  }
}

const fetchCourses = async () => {
  try {
    const response = await fetch(adminUrl('/admin/courses'), {
      headers: authHeaders(false)
    })
    
    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`)
    }
    
    const data = await response.json()
    availableCourses.value = data.data || []
    console.log('Courses loaded:', availableCourses.value.length)
  } catch (error) {
    console.error('Error fetching courses:', error)
    showMessage('Error loading courses. Please check your connection.', 'error')
  }
}

const filterFacilitators = () => {
  // Filtering is handled by computed property
}

const refreshFacilitators = async () => {
  refreshing.value = true
  try {
    await Promise.all([
      fetchFacilitators(),
      fetchCourses(),
      fetchCourseDashboard(),
      fetchAssessments(),
      fetchStudents(),
      fetchAnalytics(),
      fetchCommunication(),
      fetchSettings()
    ])
    showMessage('Data refreshed successfully', 'success')
  } catch (error) {
    showMessage('Error refreshing data', 'error')
  } finally {
    refreshing.value = false
  }
}

const viewFacilitator = (facilitator) => {
  selectedFacilitatorForView.value = facilitator
  showViewFacilitatorModal.value = true
}

const editFacilitator = (facilitator) => {
  selectedFacilitatorForView.value = facilitator

  editFacilitatorForm.value = {
    id: facilitator?.id ?? null,
    name: facilitator?.name ?? '',
    email: facilitator?.email ?? '',
    specialization: facilitator?.department ?? facilitator?.specialization ?? '',
    status: facilitator?.status === 'inactive' ? 'inactive' : 'active'
  }

  editSaving.value = false
  showEditFacilitatorModal.value = true
}

const saveEditedFacilitator = async () => {
  const id = editFacilitatorForm.value.id
  if (!id) return
  editSaving.value = true
  try {
    const statusApi =
      editFacilitatorForm.value.status === 'inactive' ? 'disabled' : 'active'
    const response = await fetch(adminUrl(`/admin/users/${id}`), {
      method: 'PUT',
      headers: authHeaders(),
      body: JSON.stringify({
        name: editFacilitatorForm.value.name,
        email: editFacilitatorForm.value.email,
        department: editFacilitatorForm.value.specialization,
        status: statusApi
      })
    })
    if (!response.ok) {
      const err = await response.json().catch(() => null)
      const text =
        (err && (err.message || JSON.stringify(err.errors || err))) ||
        `Server returned ${response.status}`
      showMessage(`Error saving facilitator: ${text}`, 'error')
      return
    }
    showMessage('Facilitator updated successfully', 'success')
    showEditFacilitatorModal.value = false
    await fetchFacilitators()
  } catch (e) {
    console.error(e)
    showMessage('Error saving facilitator', 'error')
  } finally {
    editSaving.value = false
  }
}


const assignCourses = (facilitator) => {
  selectedFacilitator.value = facilitator
  selectedCourseIds.value = facilitator.courses ? facilitator.courses.map(c => c.id) : []
  showAssignCoursesModal.value = true
}

const saveCourseAssignments = async () => {
  assigningSaving.value = true
  try {
    const response = await fetch(adminUrl(`/admin/facilitators/${selectedFacilitator.value.id}/courses`), {
      method: 'PUT',
      headers: authHeaders(),
      body: JSON.stringify({ course_ids: selectedCourseIds.value })
    })

    if (response.ok) {
      await fetchFacilitators()
      showAssignCoursesModal.value = false
      selectedFacilitator.value = null
      selectedCourseIds.value = []
      showMessage('Course assignments saved successfully', 'success')
    } else {
      // try to surface server error message
      const err = await response.json().catch(() => null)
      const text = err && err.message ? err.message : `Server returned ${response.status}`
      showMessage(`Error saving assignments: ${text}`, 'error')
    }
  } catch (error) {
    console.error('Error saving course assignments:', error)
    showMessage('Error saving course assignments', 'error')
  } finally {
    assigningSaving.value = false
  }
}

const addFacilitator = async () => {
  addingFacilitator.value = true
  try {
    const response = await fetch(adminUrl('/admin/facilitators'), {
      method: 'POST',
      headers: authHeaders(),
      body: JSON.stringify(newFacilitator.value)
    })

    if (response.ok) {
      await fetchFacilitators()
      showAddFacilitatorModal.value = false
      newFacilitator.value = { 
        name: '', 
        email: '', 
        password: '', 
        specialization: '',
        course_ids: []
      }
      showMessage('Facilitator added successfully', 'success')
    } else {
      const err = await response.json().catch(() => null)
      const text = err && err.message ? err.message : `Server returned ${response.status}`
      showMessage(`Error adding facilitator: ${text}`, 'error')
    }
  } catch (error) {
    console.error('Error adding facilitator:', error)
    showMessage('Error adding facilitator', 'error')
  } finally {
    addingFacilitator.value = false
  }
}

const removeFacilitator = async (facilitator) => {
  if (!confirm(`Are you sure you want to remove ${facilitator.name}?`)) return
  removingId.value = facilitator.id
  try {
    const response = await fetch(adminUrl(`/admin/facilitators/${facilitator.id}`), {
      method: 'DELETE',
      headers: authHeaders(false)
    })

    if (response.ok) {
      facilitators.value = facilitators.value.filter(f => f.id !== facilitator.id)
      showMessage('Facilitator removed successfully', 'success')
    } else {
      const err = await response.json().catch(() => null)
      const text = err && err.message ? err.message : `Server returned ${response.status}`
      showMessage(`Error removing facilitator: ${text}`, 'error')
    }
  } catch (error) {
    console.error('Error removing facilitator:', error)
    showMessage('Error removing facilitator', 'error')
  } finally {
    removingId.value = null
  }
}

onMounted(() => {
  fetchFacilitators()
  fetchCourses()

  // Load capability dashboard data (DB-backed)
  fetchCourseDashboard()
  fetchAssessments()
  fetchStudents()
  fetchAnalytics()
  fetchCommunication()
  fetchSettings()
})

// handle Escape key to close open modals for accessibility
const handleKeydown = (e) => {
  if (e.key === 'Escape') {
    if (showAddFacilitatorModal.value) showAddFacilitatorModal.value = false
    if (showAssignCoursesModal.value) showAssignCoursesModal.value = false
    if (showViewFacilitatorModal.value) showViewFacilitatorModal.value = false
    if (showEditFacilitatorModal.value) showEditFacilitatorModal.value = false
  }
}

window.addEventListener('keydown', handleKeydown)
onBeforeUnmount(() => {
  window.removeEventListener('keydown', handleKeydown)
})
</script>

<style scoped>
.admin-facilitators {
  padding: 0;
}

/* Header */
.facilitators-header {
  background: white;
  padding: 25px;
  border-radius: 12px;
  margin-bottom: 25px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.header-content {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.header-left h1 {
  margin: 0 0 5px 0;
  color: #333;
  font-size: 1.5rem;
}

.header-left p {
  margin: 0;
  color: #666;
  font-size: 0.9rem;
}

/* Filters */
.filters-section {
  background: white;
  padding: 20px 25px;
  border-radius: 12px;
  margin-bottom: 25px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.filters-left {
  display: flex;
  gap: 15px;
  align-items: center;
}

.search-bar {
  display: flex;
  align-items: center;
  gap: 10px;
  background: #f8f9fa;
  border: 1px solid #dee2e6;
  border-radius: 8px;
  padding: 10px 15px;
  min-width: 300px;
}

.search-bar i {
  color: #6c757d;
}

.search-bar input {
  border: none;
  background: none;
  outline: none;
  flex: 1;
  font-size: 0.9rem;
}

.filter-select {
  padding: 10px 15px;
  border: 1px solid #dee2e6;
  border-radius: 8px;
  background: white;
  font-size: 0.9rem;
}

/* Table */
.facilitators-table-container {
  background: white;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.facilitators-table {
  width: 100%;
  border-collapse: collapse;
}

.facilitators-table th {
  background: #f8f9fa;
  padding: 15px;
  text-align: left;
  font-weight: 600;
  color: #495057;
  border-bottom: 2px solid #dee2e6;
}

.facilitators-table td {
  padding: 15px;
  border-bottom: 1px solid #f1f3f5;
}

.facilitator-info {
  display: flex;
  align-items: center;
  gap: 12px;
}

.facilitator-avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: #2196F3;
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 600;
}

.facilitator-name {
  font-weight: 600;
  color: #333;
}

.facilitator-id {
  font-size: 0.8rem;
  color: #6c757d;
}

.assigned-courses {
  max-width: 300px;
}

.no-courses {
  color: #6c757d;
  font-style: italic;
}

.status-badge {
  padding: 4px 12px;
  border-radius: 20px;
  font-size: 0.8rem;
  font-weight: 600;
}

.status-badge.active {
  background: #d4edda;
  color: #155724;
}

.status-badge.inactive {
  background: #f8d7da;
  color: #721c24;
}

.action-buttons {
  display: flex;
  gap: 8px;
}

.action-btn {
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

.action-btn.view {
  background: #fff3e0;
  color: #ff6b35;
}

.action-btn.view:hover {
  background: #ffe0cc;
}

.action-btn.edit {
  background: #fff3e0;
  color: #f57c00;
}

.action-btn.edit:hover {
  background: #ffe0b2;
}

.action-btn.assign {
  background: #e8f5e8;
  color: #4caf50;
}

.action-btn.assign:hover {
  background: #c8e6c9;
}

.action-btn.delete {
  background: #ffebee;
  color: #f44336;
}

.action-btn.delete:hover {
  background: #ffcdd2;
}

.action-btn[disabled],
.btn[disabled] {
  opacity: 0.6;
  cursor: not-allowed;
}

.fa-spinner.fa-spin {
  display: inline-block;
}

/* Course Checkboxes */
.course-checkboxes {
  display: flex;
  flex-direction: column;
  gap: 10px;
  max-height: 300px;
  overflow-y: auto;
}

.course-checkbox {
  display: flex;
  align-items: flex-start;
  gap: 10px;
  padding: 10px;
  border: 1px solid #e9ecef;
  border-radius: 8px;
  cursor: pointer;
  transition: background 0.3s ease;
}

.course-checkbox:hover {
  background: #f8f9fa;
}

.course-info {
  flex: 1;
}

.course-info strong {
  display: block;
  margin-bottom: 5px;
}

.course-info small {
  color: #6c757d;
  font-size: 0.8rem;
}

/* Loading State */
.loading-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 60px 20px;
  color: #6c757d;
}

.spinner {
  width: 40px;
  height: 40px;
  border: 4px solid #f3f3f3;
  border-top: 4px solid #ff6b35;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin-bottom: 20px;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

/* Empty State */
.empty-state {
  text-align: center;
  padding: 60px 20px;
  color: #6c757d;
}

.empty-state i {
  font-size: 3rem;
  margin-bottom: 20px;
  opacity: 0.5;
}

.empty-state h3 {
  margin: 0 0 10px 0;
  color: #495057;
}

/* Modal */
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

.modal {
  background: white;
  border-radius: 12px;
  width: 90%;
  max-width: 600px;
  max-height: 90vh;
  overflow-y: auto;
}

.modal-header {
  padding: 20px 25px;
  border-bottom: 1px solid #dee2e6;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.modal-header h3 {
  margin: 0;
  color: #333;
}

.btn-close {
  background: none;
  border: none;
  font-size: 1.5rem;
  cursor: pointer;
  color: #6c757d;
}

.modal-body {
  padding: 25px;
}

.form-group {
  margin-bottom: 20px;
}

.form-group label {
  display: block;
  margin-bottom: 8px;
  font-weight: 600;
  color: #333;
}

.form-group input,
.form-group select {
  width: 100%;
  padding: 12px;
  border: 1px solid #dee2e6;
  border-radius: 8px;
  font-size: 0.9rem;
}

.form-group select[multiple] {
  height: 120px;
}

.form-actions {
  display: flex;
  gap: 10px;
  justify-content: flex-end;
  margin-top: 20px;
}

.btn {
  padding: 10px 20px;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
}

.btn-primary {
  background: #ff6b35;
  color: white;
}

.btn-primary:hover {
  background: #e55a2b;
}

.btn-secondary {
  background: #6c757d;
  color: white;
}

.btn-secondary:hover {
  background: #5a6268;
}

/* Message */
.message {
  position: fixed;
  top: 20px;
  right: 20px;
  padding: 15px 20px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  gap: 10px;
  z-index: 1001;
  animation: slideIn 0.3s ease;
}

.message.success {
  background: #d4edda;
  color: #155724;
  border: 1px solid #c3e6cb;
}

.message.error {
  background: #f8d7da;
  color: #721c24;
  border: 1px solid #f5c6cb;
}

@keyframes slideIn {
  from {
    transform: translateX(100%);
    opacity: 0;
  }
  to {
    transform: translateX(0);
    opacity: 1;
  }
}

/* Workspace + capability dashboard */
.workspace-intro {
  margin-bottom: 20px;
  padding: 20px 22px;
  border-radius: 14px;
  background: linear-gradient(135deg, #0f172a 0%, #1e293b 55%, #334155 100%);
  color: #e2e8f0;
  box-shadow: 0 10px 40px rgba(15, 23, 42, 0.18);
}

.workspace-title {
  margin: 0 0 8px 0;
  font-size: 1.25rem;
  font-weight: 700;
  letter-spacing: -0.02em;
}

.workspace-desc {
  margin: 0;
  font-size: 0.92rem;
  line-height: 1.55;
  color: #cbd5e1;
  max-width: 900px;
}

.capability-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
  gap: 20px;
  margin-bottom: 32px;
}

.capability-card {
  background: #ffffff;
  border-radius: 14px;
  border: 1px solid #e9ecef;
  box-shadow: 0 4px 18px rgba(0, 0, 0, 0.06);
  display: flex;
  flex-direction: column;
  min-height: 100%;
  overflow: hidden;
  transition: box-shadow 0.2s ease, transform 0.2s ease;
}

.capability-card:hover {
  box-shadow: 0 8px 28px rgba(0, 0, 0, 0.1);
  transform: translateY(-2px);
}

.capability-header {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 16px 18px;
  background: linear-gradient(90deg, #fff7f3 0%, #ffffff 100%);
  border-bottom: 1px solid #f1f3f5;
}

.capability-header i {
  width: 40px;
  height: 40px;
  border-radius: 12px;
  background: #ff6b35;
  color: #fff;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1rem;
}

.capability-header h2 {
  margin: 0;
  font-size: 1.05rem;
  font-weight: 700;
  color: #1a1a1a;
}

.capability-body {
  padding: 16px 18px 12px;
  flex: 1;
}

.capability-footer {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
  padding: 12px 18px 16px;
  border-top: 1px solid #f1f3f5;
  background: #fafbfc;
}

.cap-link {
  font-size: 0.82rem;
  font-weight: 600;
  color: #ff6b35;
  text-decoration: none;
  padding: 6px 10px;
  border-radius: 8px;
  background: rgba(255, 107, 53, 0.08);
  border: 1px solid rgba(255, 107, 53, 0.2);
  transition: background 0.15s ease, color 0.15s ease;
}

.cap-link:hover {
  background: rgba(255, 107, 53, 0.16);
  color: #d94816;
}

.cap-inline-links {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
  margin-top: 10px;
}

.cap-hint {
  margin: 0 0 12px 0;
  font-size: 0.82rem;
  color: #64748b;
  line-height: 1.45;
}

.discussions-copy {
  margin: 0;
  font-size: 0.88rem;
  color: #475569;
  line-height: 1.55;
}

.tab-row {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  margin-bottom: 14px;
}

.tab {
  border: 1px solid #e2e8f0;
  background: #f8fafc;
  color: #475569;
  padding: 8px 14px;
  border-radius: 999px;
  font-size: 0.82rem;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.15s ease, border-color 0.15s ease, color 0.15s ease;
}

.tab:hover {
  background: #f1f5f9;
  border-color: #cbd5e1;
}

.tab.active {
  background: #fff7f3;
  border-color: #ffb399;
  color: #c2410c;
}

.cap-meta {
  display: flex;
  align-items: center;
  gap: 10px;
  color: #64748b;
  font-size: 0.88rem;
}

.mini-spinner {
  width: 18px;
  height: 18px;
  border: 2px solid #e9ecef;
  border-top-color: #ff6b35;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

.subtle-muted {
  color: #64748b;
  font-size: 0.86rem;
}

.kpis {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 12px;
  margin-bottom: 14px;
}

.kpis-tight {
  grid-template-columns: repeat(3, minmax(0, 1fr));
  margin-bottom: 16px;
}

.kpi {
  background: #f8fafc;
  border: 1px solid #e9ecef;
  border-radius: 12px;
  padding: 12px;
  text-align: center;
}

.kpi-small {
  padding: 10px 8px;
}

.kpi-value {
  font-size: 1.35rem;
  font-weight: 800;
  color: #0f172a;
  line-height: 1.1;
}

.kpi-small .kpi-value {
  font-size: 1.1rem;
}

.kpi-label {
  margin-top: 6px;
  font-size: 0.72rem;
  font-weight: 600;
  color: #64748b;
  text-transform: uppercase;
  letter-spacing: 0.04em;
}

.subsection {
  margin-top: 4px;
}

.subsection-title {
  font-size: 0.75rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  color: #94a3b8;
  margin-bottom: 8px;
}

.list-compact {
  list-style: none;
  margin: 0;
  padding: 0;
}

.list-compact li {
  padding: 10px 0;
  border-bottom: 1px solid #f1f5f9;
}

.list-compact li:last-child {
  border-bottom: none;
}

.list-title {
  display: block;
  font-weight: 600;
  color: #1e293b;
  font-size: 0.9rem;
}

.list-muted {
  display: block;
  margin-top: 4px;
  font-size: 0.8rem;
  color: #64748b;
}

.grade-pill {
  display: inline-block;
  margin-left: 8px;
  padding: 2px 8px;
  border-radius: 999px;
  font-size: 0.72rem;
  font-weight: 600;
  background: #ecfdf5;
  color: #047857;
}

.empty-inline {
  display: flex;
  align-items: center;
  gap: 10px;
  color: #94a3b8;
  font-size: 0.88rem;
  padding: 12px 0;
}

.empty-inline i {
  opacity: 0.6;
}

.form-row {
  margin-bottom: 12px;
}

.form-row .label {
  display: block;
  font-size: 0.78rem;
  font-weight: 700;
  color: #64748b;
  margin-bottom: 6px;
  text-transform: uppercase;
  letter-spacing: 0.04em;
}

.input,
.textarea {
  width: 100%;
  padding: 10px 12px;
  border: 1px solid #e2e8f0;
  border-radius: 10px;
  font-size: 0.88rem;
  background: #fff;
}

.textarea {
  resize: vertical;
  min-height: 72px;
}

.settings-list {
  max-height: 220px;
  overflow-y: auto;
  margin-bottom: 12px;
}

.setting-row {
  display: grid;
  grid-template-columns: minmax(0, 1fr) minmax(0, 1.2fr);
  gap: 10px;
  align-items: start;
  padding: 10px 0;
  border-bottom: 1px solid #f1f5f9;
}

.setting-key {
  font-size: 0.8rem;
  font-weight: 600;
  color: #334155;
  word-break: break-word;
}

.profile-summary {
  display: flex;
  gap: 16px;
  align-items: center;
  margin-bottom: 20px;
}

.profile-avatar-big {
  width: 64px;
  height: 64px;
  border-radius: 16px;
  background: linear-gradient(135deg, #ff6b35, #ff8f65);
  color: #fff;
  font-size: 1.5rem;
  font-weight: 800;
  display: flex;
  align-items: center;
  justify-content: center;
}

.profile-summary-meta .profile-name {
  font-size: 1.1rem;
  font-weight: 700;
  color: #1e293b;
}

.profile-summary-meta .profile-email {
  font-size: 0.88rem;
  color: #64748b;
}

.profile-summary-meta .profile-id {
  font-size: 0.78rem;
  color: #94a3b8;
  margin-top: 4px;
}

/* Responsive */
@media (max-width: 768px) {
  .filters-section {
    flex-direction: column;
    gap: 15px;
  }
  
  .filters-left {
    flex-wrap: wrap;
  }
  
  .search-bar {
    min-width: 100%;
  }
  
  .facilitators-table {
    font-size: 0.8rem;
  }
  
  .facilitators-table th,
  .facilitators-table td {
    padding: 10px;
  }
  
  .action-buttons {
    flex-direction: column;
  }

  .capability-grid {
    grid-template-columns: 1fr;
  }

  .setting-row {
    grid-template-columns: 1fr;
  }

  .kpis-tight {
    grid-template-columns: 1fr;
  }
}
</style>
