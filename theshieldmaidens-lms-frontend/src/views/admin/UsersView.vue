<template>
  <div class="admin-users">
    <!-- Header -->
    <div class="page-header">
      <div class="header-content">
        <div class="header-left">
          <h1><i class="fas fa-users"></i> Users (Students)</h1>
          <p>Manage all student accounts and their enrollment status</p>
        </div>
        <div class="header-actions">
          <button @click="showAddUserModal = true" class="btn btn-primary">
            <i class="fas fa-user-plus"></i> Add User
          </button>
        </div>
      </div>
    </div>

    <!-- Filters and Search -->
    <div class="filters-section">
      <div class="search-bar">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="11" cy="11" r="8"></circle>
          <path d="m21 21-4.35-4.35"></path>
        </svg>
        <input 
          v-model="searchQuery" 
          type="text" 
          placeholder="Search users by name, email..."
          @input="filterUsers"
        >
      </div>
      
      <div class="filters">
        <select v-model="roleFilter" @change="filterUsers" class="filter-select">
          <option value="">All Roles</option>
          <option value="admin">Admin</option>
          <option value="instructor">Facilitator</option>
          <option value="student">Student</option>
        </select>
        
        <select v-model="statusFilter" @change="filterUsers" class="filter-select">
          <option value="">All Status</option>
          <option value="active">Active</option>
          <option value="pending_setup">Pending setup</option>
          <option value="disabled">Disabled</option>
        </select>
      </div>
    </div>

    <!-- Users Table -->
    <div class="users-table-container">
      <table class="users-table">
        <thead>
          <tr>
            <th>User</th>
            <th>Role</th>
            <th>Status</th>
            <th>Joined</th>
            <th>Last Active</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="user in paginatedUsers" :key="user.id">
            <td>
              <div class="user-info">
                <div class="user-avatar">
                  {{ (user.name || '?').charAt(0).toUpperCase() }}
                </div>
                <div class="user-details">
                  <div class="user-name">{{ user.name }}</div>
                  <div class="user-email">{{ user.email }}</div>
                </div>
              </div>
            </td>
            <td>
              <span class="role-badge" :class="roleBadgeClass(user.role)">
                {{ formatRoleLabel(user.role) }}
              </span>
            </td>
            <td>
              <span class="status-badge" :class="statusBadgeClass(user.status)">
                {{ formatStatusLabel(user.status) }}
              </span>
            </td>
            <td>{{ formatDate(user.created_at) }}</td>
            <td>{{ formatRelativeTime(user.last_active) }}</td>
            <td>
              <div class="actions">
                <button @click="editUser(user)" class="action-btn edit">
                  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                  </svg>
                </button>
                <button @click="deleteUser(user)" class="action-btn delete">
                  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="3,6 5,6 21,6"></polyline>
                    <path d="m19,6v14a2,2 0 0,1 -2,2H7a2,2 0 0,1 -2,-2V6m3,0V4a2,2 0 0,1 2,-2h4a2,2 0 0,1 2,2v2"></path>
                  </svg>
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <div class="pagination">
      <button 
        @click="currentPage > 1 && currentPage--" 
        :disabled="currentPage === 1"
        class="pagination-btn"
      >
        Previous
      </button>
      <span class="page-info">
        Page {{ currentPage }} of {{ totalPages }}
      </span>
      <button 
        @click="currentPage < totalPages && currentPage++" 
        :disabled="currentPage === totalPages"
        class="pagination-btn"
      >
        Next
      </button>
    </div>

    <!-- Add/Edit User Modal -->
    <div v-if="showAddUserModal || showEditUserModal" class="modal-overlay" @click="closeModals">
      <div class="modal-content" @click.stop>
        <div class="modal-header">
          <h2>{{ showEditUserModal ? 'Edit User' : 'Add New User' }}</h2>
          <button @click="closeModals" class="close-btn">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <line x1="18" y1="6" x2="6" y2="18"></line>
              <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
          </button>
        </div>
        
        <form @submit.prevent="saveUser" class="user-form">
          <div class="form-group">
            <label for="name">Full Name</label>
            <input 
              id="name"
              v-model="userForm.name" 
              type="text" 
              required
              placeholder="Enter full name"
            >
          </div>
          
          <div class="form-group">
            <label for="email">Email Address</label>
            <input 
              id="email"
              v-model="userForm.email" 
              type="email" 
              required
              placeholder="Enter email address"
            >
          </div>
          
          <div class="form-group">
            <label for="role">Role</label>
            <select id="role" v-model="userForm.role" required>
              <option value="">Select Role</option>
              <option value="admin">Admin</option>
              <option value="instructor">Facilitator</option>
              <option value="student">Student</option>
            </select>
          </div>
          
          <div class="form-group">
            <label for="status">Status</label>
            <select id="status" v-model="userForm.status" required>
              <option value="active">Active</option>
              <option value="pending_setup">Pending setup</option>
              <option value="disabled">Disabled</option>
            </select>
          </div>
          
          <div class="form-group" v-if="!showEditUserModal">
            <label for="password">Password</label>
            <input 
              id="password"
              v-model="userForm.password" 
              type="password" 
              required
              minlength="8"
              autocomplete="new-password"
              placeholder="At least 8 characters"
            >
          </div>

          <div class="form-group" v-else>
            <label for="password-edit">New password</label>
            <input 
              id="password-edit"
              v-model="userForm.password" 
              type="password" 
              minlength="8"
              autocomplete="new-password"
              placeholder="Leave blank to keep current password"
            >
          </div>
          
          <div class="form-actions">
            <button type="button" @click="closeModals" class="btn-cancel">
              Cancel
            </button>
            <button type="submit" class="btn-save" :disabled="isSaving">
              {{ isSaving ? 'Saving...' : (showEditUserModal ? 'Update User' : 'Create User') }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div v-if="showDeleteModal" class="modal-overlay" @click="showDeleteModal = false">
      <div class="modal-content modal-sm" @click.stop>
        <div class="modal-header">
          <h2>Delete User</h2>
          <button @click="showDeleteModal = false" class="close-btn">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <line x1="18" y1="6" x2="6" y2="18"></line>
              <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
          </button>
        </div>
        
        <div class="modal-body">
          <p>Are you sure you want to delete <strong>{{ userToDelete?.name }}</strong>?</p>
          <p class="warning-text">This action cannot be undone.</p>
        </div>
        
        <div class="modal-actions">
          <button @click="showDeleteModal = false" class="btn-cancel">
            Cancel
          </button>
          <button @click="confirmDelete" class="btn-delete" :disabled="isDeleting">
            {{ isDeleting ? 'Deleting...' : 'Delete User' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed, watch } from 'vue'
import { API_BASE_URL } from '@/config/api'

const users = ref([])
const searchQuery = ref('')
const roleFilter = ref('')
const statusFilter = ref('')
const currentPage = ref(1)
const itemsPerPage = ref(10)
const refreshInterval = ref(null)

const showAddUserModal = ref(false)
const showEditUserModal = ref(false)
const showDeleteModal = ref(false)
const isSaving = ref(false)
const isDeleting = ref(false)

const userForm = ref({
  id: null,
  name: '',
  email: '',
  role: '',
  status: 'active',
  password: ''
})

const userToDelete = ref(null)

const authHeaders = (json = false) => {
  const h = {
    Authorization: `Bearer ${localStorage.getItem('token')}`,
    Accept: 'application/json'
  }
  if (json) h['Content-Type'] = 'application/json'
  return h
}

const normalizeStatus = (status) => {
  if (status === 'inactive') return 'pending_setup'
  if (status === 'suspended') return 'disabled'
  return status || 'active'
}

const formatStatusLabel = (status) => {
  const s = normalizeStatus(status)
  const map = {
    active: 'Active',
    pending_setup: 'Pending setup',
    disabled: 'Disabled'
  }
  return map[s] || String(s || '')
}

const formatRoleLabel = (role) => {
  if (role === 'instructor' || role === 'facilitator') return 'Facilitator'
  if (!role) return ''
  return role.charAt(0).toUpperCase() + role.slice(1)
}

const roleBadgeClass = (role) => {
  if (role === 'facilitator' || role === 'instructor') return 'instructor'
  return role || 'student'
}

const statusBadgeClass = (status) => normalizeStatus(status)

const filteredUsers = computed(() => {
  let filtered = users.value

  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    filtered = filtered.filter(
      (user) =>
        (user.name || '').toLowerCase().includes(query) ||
        (user.email || '').toLowerCase().includes(query)
    )
  }

  if (roleFilter.value) {
    if (roleFilter.value === 'instructor') {
      filtered = filtered.filter((u) => u.role === 'instructor' || u.role === 'facilitator')
    } else {
      filtered = filtered.filter((u) => u.role === roleFilter.value)
    }
  }

  if (statusFilter.value) {
    filtered = filtered.filter((u) => normalizeStatus(u.status) === statusFilter.value)
  }

  return filtered
})

const totalPages = computed(() => {
  const n = filteredUsers.value.length
  return Math.max(1, Math.ceil(n / itemsPerPage.value))
})

const paginatedUsers = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage.value
  const end = start + itemsPerPage.value
  return filteredUsers.value.slice(start, end)
})

watch([filteredUsers, itemsPerPage], () => {
  const max = Math.max(1, Math.ceil(filteredUsers.value.length / itemsPerPage.value) || 1)
  if (currentPage.value > max) currentPage.value = max
})

const loadUsers = async () => {
  try {
    const response = await fetch(`${API_BASE_URL}/admin/users`, {
      headers: authHeaders(false)
    })
    if (response.ok) {
      const data = await response.json()
      users.value = data.data || data.users || []
    } else {
      console.error('Failed to load users', response.status)
      users.value = []
    }
  } catch (error) {
    console.error('Error loading users:', error)
    users.value = []
  }
}

const filterUsers = () => {
  currentPage.value = 1
}

const editUser = (user) => {
  userForm.value = {
    id: user.id,
    name: user.name,
    email: user.email,
    role: user.role === 'facilitator' ? 'instructor' : user.role,
    status: normalizeStatus(user.status),
    password: ''
  }
  showEditUserModal.value = true
}

const deleteUser = (user) => {
  userToDelete.value = user
  showDeleteModal.value = true
}

const readErrorMessage = async (response) => {
  try {
    const j = await response.json()
    if (j.message) return j.message
    if (j.errors) {
      const first = Object.values(j.errors)[0]
      return Array.isArray(first) ? first[0] : String(first)
    }
    return response.statusText
  } catch {
    return response.statusText
  }
}

const saveUser = async () => {
  isSaving.value = true
  try {
    if (showEditUserModal.value) {
      const body = {
        name: userForm.value.name,
        email: userForm.value.email,
        role: userForm.value.role,
        status: userForm.value.status
      }
      if (userForm.value.password && userForm.value.password.length >= 8) {
        body.password = userForm.value.password
      }
      const response = await fetch(`${API_BASE_URL}/admin/users/${userForm.value.id}`, {
        method: 'PUT',
        headers: authHeaders(true),
        body: JSON.stringify(body)
      })
      if (!response.ok) {
        window.alert(await readErrorMessage(response))
        return
      }
    } else {
      const response = await fetch(`${API_BASE_URL}/admin/users`, {
        method: 'POST',
        headers: authHeaders(true),
        body: JSON.stringify({
          name: userForm.value.name,
          email: userForm.value.email,
          role: userForm.value.role,
          status: userForm.value.status,
          password: userForm.value.password
        })
      })
      if (!response.ok) {
        window.alert(await readErrorMessage(response))
        return
      }
    }
    await loadUsers()
    closeModals()
  } catch (error) {
    console.error('Error saving user:', error)
    window.alert('Could not save user. Please try again.')
  } finally {
    isSaving.value = false
  }
}

const confirmDelete = async () => {
  if (!userToDelete.value) return
  isDeleting.value = true
  try {
    const response = await fetch(`${API_BASE_URL}/admin/users/${userToDelete.value.id}`, {
      method: 'DELETE',
      headers: authHeaders(false)
    })
    if (response.ok) {
      await loadUsers()
      showDeleteModal.value = false
      userToDelete.value = null
    } else {
      window.alert(await readErrorMessage(response))
    }
  } catch (error) {
    console.error('Error deleting user:', error)
    window.alert('Could not delete user.')
  } finally {
    isDeleting.value = false
  }
}

const closeModals = () => {
  showAddUserModal.value = false
  showEditUserModal.value = false
  userForm.value = {
    id: null,
    name: '',
    email: '',
    role: '',
    status: 'active',
    password: ''
  }
}

const formatDate = (dateString) => {
  if (!dateString) return '—'
  const date = new Date(dateString)
  return date.toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

const formatRelativeTime = (dateString) => {
  if (!dateString) return '—'
  const date = new Date(dateString)
  const now = new Date()
  const diff = now - date
  if (Number.isNaN(diff)) return '—'
  if (diff < 60000) return 'Just now'
  if (diff < 3600000) return `${Math.floor(diff / 60000)} min ago`
  if (diff < 86400000) return `${Math.floor(diff / 3600000)} hours ago`
  if (diff < 604800000) return `${Math.floor(diff / 86400000)} days ago`
  return formatDate(dateString)
}

onMounted(() => {
  loadUsers()
  refreshInterval.value = setInterval(() => {
    loadUsers()
  }, 30000)
})

onUnmounted(() => {
  if (refreshInterval.value) {
    clearInterval(refreshInterval.value)
  }
})
</script>

<style scoped>
.admin-users {
  padding: 2rem;
  max-width: 1400px;
  margin: 0 auto;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 2rem;
}

.header-content {
  display: flex;
  flex-wrap: wrap;
  align-items: flex-start;
  justify-content: space-between;
  gap: 1rem;
  width: 100%;
}

.header-left h1 {
  color: #1F2937;
  font-size: 2rem;
  font-weight: 700;
  margin: 0 0 0.5rem 0;
}

.header-left p {
  color: #6B7280;
  margin: 0;
}

.header-actions {
  display: flex;
  gap: 1rem;
  align-items: center;
}

.refresh-btn {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1.5rem;
  background-color: #6B7280;
  color: white;
  border: none;
  border-radius: 0.5rem;
  cursor: pointer;
  transition: background-color 0.2s;
}

.refresh-btn:hover {
  background-color: #4B5563;
}

.add-user-btn {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1.5rem;
  background-color: #10B981;
  color: white;
  border: none;
  border-radius: 0.5rem;
  cursor: pointer;
  transition: background-color 0.2s;
}

.add-user-btn:hover {
  background-color: #059669;
}

.filters-section {
  display: flex;
  gap: 1rem;
  margin-bottom: 2rem;
  flex-wrap: wrap;
}

.search-bar {
  flex: 1;
  min-width: 300px;
  position: relative;
  display: flex;
  align-items: center;
}

.search-bar svg {
  position: absolute;
  left: 1rem;
  color: #9CA3AF;
  pointer-events: none;
}

.search-bar input {
  width: 100%;
  padding: 0.75rem 1rem 0.75rem 3rem;
  border: 1px solid #E5E7EB;
  border-radius: 0.5rem;
  font-size: 0.875rem;
}

.search-bar input:focus {
  outline: none;
  border-color: #3B82F6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.filters {
  display: flex;
  gap: 1rem;
}

.filter-select {
  padding: 0.75rem 1rem;
  border: 1px solid #E5E7EB;
  border-radius: 0.5rem;
  font-size: 0.875rem;
  background-color: white;
  cursor: pointer;
}

.filter-select:focus {
  outline: none;
  border-color: #3B82F6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.users-table-container {
  background: white;
  border-radius: 0.75rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  overflow-x: auto;
  -webkit-overflow-scrolling: touch;
  margin-bottom: 2rem;
}

.users-table {
  width: 100%;
  min-width: 720px;
  border-collapse: collapse;
}

.users-table th {
  background-color: #F9FAFB;
  padding: 1rem;
  text-align: left;
  font-weight: 600;
  color: #374151;
  border-bottom: 1px solid #E5E7EB;
}

.users-table td {
  padding: 1rem;
  border-bottom: 1px solid #F3F4F6;
}

.user-info {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.user-avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background-color: #3B82F6;
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 600;
  font-size: 0.875rem;
}

.user-name {
  font-weight: 600;
  color: #1F2937;
}

.user-email {
  font-size: 0.8125rem;
  color: #6B7280;
}

.role-badge {
  padding: 0.25rem 0.75rem;
  border-radius: 9999px;
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
}

.role-badge.admin { background-color: #EF4444; color: white; }
.role-badge.instructor { background-color: #3B82F6; color: white; }
.role-badge.student { background-color: #10B981; color: white; }
.role-badge.parent { background-color: #F59E0B; color: white; }

.status-badge {
  padding: 0.25rem 0.75rem;
  border-radius: 9999px;
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
}

.status-badge.active { background-color: #10B981; color: white; }
.status-badge.pending_setup { background-color: #6B7280; color: white; }
.status-badge.disabled { background-color: #EF4444; color: white; }
.status-badge.inactive { background-color: #6B7280; color: white; }
.status-badge.suspended { background-color: #EF4444; color: white; }

.actions {
  display: flex;
  gap: 0.5rem;
}

.action-btn {
  width: 32px;
  height: 32px;
  border: none;
  border-radius: 0.375rem;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s;
}

.action-btn.edit {
  background-color: #FFF2CC;
  color: #FF9900;
}

.action-btn.edit:hover {
  background-color: #FFD7BE;
}

.action-btn.delete {
  background-color: #FFC107;
  color: white;
}

.action-btn.delete:hover {
  background-color: #FCA5A5;
}

.pagination {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 1rem;
}

.pagination-btn {
  padding: 0.5rem 1rem;
  border: 1px solid #E5E7EB;
  background-color: white;
  border-radius: 0.375rem;
  cursor: pointer;
  transition: all 0.2s;
}

.pagination-btn:hover:not(:disabled) {
  background-color: #F9FAFB;
}

.pagination-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.page-info {
  color: #6B7280;
  font-size: 0.875rem;
}

/* Modal Styles */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.modal-content {
  background: white;
  border-radius: 0.75rem;
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
  max-width: 500px;
  width: 90%;
  max-height: 90vh;
  overflow-y: auto;
}

.modal-content.modal-sm {
  max-width: 400px;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.5rem;
  border-bottom: 1px solid #E5E7EB;
}

.modal-header h2 {
  color: #1F2937;
  font-size: 1.25rem;
  font-weight: 600;
  margin: 0;
}

.close-btn {
  background: none;
  border: none;
  color: #6B7280;
  cursor: pointer;
  padding: 0.25rem;
  border-radius: 0.25rem;
}

.close-btn:hover {
  background-color: #F3F4F6;
}

.user-form {
  padding: 1.5rem;
}

.form-group {
  margin-bottom: 1.5rem;
}

.form-group label {
  display: block;
  margin-bottom: 0.5rem;
  color: #374151;
  font-weight: 500;
  font-size: 0.875rem;
}

.form-group input,
.form-group select {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid #E5E7EB;
  border-radius: 0.5rem;
  font-size: 0.875rem;
}

.form-group input:focus,
.form-group select:focus {
  outline: none;
  border-color: #3B82F6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.form-actions {
  display: flex;
  gap: 1rem;
  justify-content: flex-end;
  margin-top: 2rem;
}

.btn-cancel {
  padding: 0.75rem 1.5rem;
  border: 1px solid #E5E7EB;
  background-color: white;
  color: #6B7280;
  border-radius: 0.5rem;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-cancel:hover {
  background-color: #F3F4F6;
}

.btn-save {
  padding: 0.75rem 1.5rem;
  border: none;
  background-color: #10B981;
  color: white;
  border-radius: 0.5rem;
  cursor: pointer;
  transition: background-color 0.2s;
}

.btn-save:hover:not(:disabled) {
  background-color: #059669;
}

.btn-save:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.modal-body {
  padding: 1.5rem;
}

.modal-body p {
  color: #374151;
  margin: 0 0 0.5rem 0;
}

.warning-text {
  color: #EF4444 !important;
  font-size: 0.875rem;
}

.modal-actions {
  display: flex;
  gap: 1rem;
  justify-content: flex-end;
  padding: 1.5rem;
  border-top: 1px solid #E5E7EB;
}

.btn-delete {
  padding: 0.75rem 1.5rem;
  border: none;
  background-color: #EF4444;
  color: white;
  border-radius: 0.5rem;
  cursor: pointer;
  transition: background-color 0.2s;
}

.btn-delete:hover:not(:disabled) {
  background-color: #DC2626;
}

.btn-delete:disabled {
  opacity: 0.5;
  cursor: not-allowed;
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

@media (max-width: 768px) {
  .admin-users {
    padding: 1rem;
  }
  
  .page-header {
    flex-direction: column;
    gap: 1rem;
  }
  
  .filters-section {
    flex-direction: column;
  }
  
  .search-bar {
    min-width: auto;
  }
  
  .filters {
    width: 100%;
  }
  
  .filter-select {
    flex: 1;
  }
  
  .users-table-container {
    overflow-x: auto;
  }
  
  .modal-content {
    width: 95%;
    margin: 1rem;
  }
}
</style>
