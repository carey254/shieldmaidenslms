<template>
  <div class="profile-view">
    <div class="page-header">
      <h1>Profile Settings</h1>
      <p>Manage your personal information and account details</p>
    </div>

    <div class="profile-content">
      <div class="profile-form-card">
        <h2>Personal Information</h2>
        <form @submit.prevent="updateProfile" class="profile-form">
          <div class="form-group">
            <label for="name">Full Name</label>
            <input 
              type="text" 
              id="name" 
              v-model="profile.name" 
              required
              class="form-control"
            />
          </div>

          <div class="form-group">
            <label for="email">Email Address</label>
            <input 
              type="email" 
              id="email" 
              v-model="profile.email" 
              required
              class="form-control"
            />
          </div>

          <div class="form-group">
            <label for="phone">Phone Number</label>
            <input 
              type="tel" 
              id="phone" 
              v-model="profile.phone" 
              class="form-control"
            />
          </div>

          <div class="form-group">
            <label for="bio">Bio</label>
            <textarea 
              id="bio" 
              v-model="profile.bio" 
              rows="4"
              class="form-control"
              placeholder="Tell us about yourself..."
            ></textarea>
          </div>

          <div class="form-actions">
            <button type="submit" class="btn btn-primary" :disabled="loading">
              <i v-if="loading" class="fas fa-spinner fa-spin"></i>
              {{ loading ? 'Saving...' : 'Save Changes' }}
            </button>
          </div>
        </form>
      </div>

      <div class="avatar-card">
        <h2>Profile Picture</h2>
        <div class="avatar-upload">
          <div class="avatar-preview">
            <img v-if="profile.avatar" :src="profile.avatar" alt="Profile Avatar" />
            <div v-else class="avatar-placeholder">
              {{ profile.name?.charAt(0)?.toUpperCase() || 'A' }}
            </div>
          </div>
          <div class="avatar-actions">
            <button @click="triggerFileUpload" class="btn btn-secondary">
              <i class="fas fa-camera"></i> Change Photo
            </button>
            <input 
              type="file" 
              ref="fileInput" 
              @change="handleFileUpload" 
              accept="image/*"
              style="display: none"
            />
            <button 
              v-if="profile.avatar" 
              @click="removeAvatar" 
              class="btn btn-danger"
            >
              <i class="fas fa-trash"></i> Remove
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'

const authStore = useAuthStore()
const loading = ref(false)
const fileInput = ref(null)

const profile = ref({
  name: '',
  email: '',
  phone: '',
  bio: '',
  avatar: ''
})

const updateProfile = async () => {
  loading.value = true
  try {
    await authStore.updateProfile(profile.value)
    
    // Show success message
    alert('Profile updated successfully!')
  } catch (error) {
    console.error('Error updating profile:', error)
    alert('Error updating profile. Please try again.')
  } finally {
    loading.value = false
  }
}

const triggerFileUpload = () => {
  fileInput.value?.click()
}

const handleFileUpload = async (event) => {
  const file = event.target.files[0]
  if (file) {
    try {
      loading.value = true
      const response = await authStore.uploadAvatar(file)
      profile.value.avatar = response.avatar
      alert('Profile picture updated successfully!')
    } catch (error) {
      console.error('Error uploading avatar:', error)
      alert('Error uploading profile picture. Please try again.')
    } finally {
      loading.value = false
    }
  }
}

const removeAvatar = () => {
  profile.value.avatar = ''
}

onMounted(() => {
  // Load current user data
  if (authStore.user) {
    profile.value = {
      name: authStore.user.name || '',
      email: authStore.user.email || '',
      phone: authStore.user.phone || '',
      bio: authStore.user.bio || '',
      avatar: authStore.user.avatar || ''
    }
  }
})
</script>

<style scoped>
.profile-view {
  padding: 20px;
  max-width: 1200px;
  margin: 0 auto;
}

.page-header {
  margin-bottom: 30px;
}

.page-header h1 {
  color: #333;
  font-size: 2rem;
  margin-bottom: 10px;
}

.page-header p {
  color: #666;
  font-size: 1rem;
}

.profile-content {
  display: grid;
  grid-template-columns: 2fr 1fr;
  gap: 30px;
}

.profile-form-card,
.avatar-card {
  background: white;
  padding: 30px;
  border-radius: 12px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

.profile-form-card h2,
.avatar-card h2 {
  color: #333;
  font-size: 1.3rem;
  margin-bottom: 20px;
}

.form-group {
  margin-bottom: 20px;
}

.form-group label {
  display: block;
  margin-bottom: 8px;
  color: #333;
  font-weight: 500;
}

.form-control {
  width: 100%;
  padding: 12px;
  border: 1px solid #ddd;
  border-radius: 6px;
  font-size: 1rem;
  transition: border-color 0.3s ease;
}

.form-control:focus {
  outline: none;
  border-color: #007bff;
  box-shadow: 0 0 0 2px rgba(0, 123, 255, 0.25);
}

.form-actions {
  display: flex;
  gap: 10px;
  margin-top: 30px;
}

.btn {
  padding: 12px 24px;
  border: none;
  border-radius: 6px;
  font-size: 1rem;
  cursor: pointer;
  transition: all 0.3s ease;
  display: inline-flex;
  align-items: center;
  gap: 8px;
}

.btn-primary {
  background: #007bff;
  color: white;
}

.btn-primary:hover:not(:disabled) {
  background: #0056b3;
}

.btn-primary:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.btn-secondary {
  background: #6c757d;
  color: white;
}

.btn-secondary:hover {
  background: #545b62;
}

.btn-danger {
  background: #dc3545;
  color: white;
}

.btn-danger:hover {
  background: #c82333;
}

.avatar-upload {
  text-align: center;
}

.avatar-preview {
  width: 120px;
  height: 120px;
  border-radius: 50%;
  margin: 0 auto 20px;
  overflow: hidden;
  border: 4px solid #f8f9fa;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

.avatar-preview img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.avatar-placeholder {
  width: 100%;
  height: 100%;
  background: #007bff;
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 3rem;
  font-weight: bold;
}

.avatar-actions {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

@media (max-width: 768px) {
  .profile-content {
    grid-template-columns: 1fr;
  }
  
  .profile-form-card,
  .avatar-card {
    padding: 20px;
  }
}
</style>
