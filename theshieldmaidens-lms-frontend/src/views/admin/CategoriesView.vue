<template>
  <div class="admin-categories">
    <!-- Header -->
    <div class="categories-header">
      <div class="header-content">
        <div class="header-left">
          <h1><i class="fas fa-tags"></i> Categories</h1>
          <p>Manage course categories and organization</p>
        </div>
        <div class="header-actions">
          <button @click="showAddCategoryModal = true" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add Category
          </button>
        </div>
      </div>
    </div>

    <!-- Categories Grid -->
    <div class="categories-grid">
      <div v-for="category in categories" :key="category.id" class="category-card">
        <div class="category-header">
          <div class="category-icon">
            <i :class="category.icon || 'fas fa-folder'"></i>
          </div>
          <div class="category-actions">
            <button @click="editCategory(category)" class="action-btn edit" title="Edit Category">
              <i class="fas fa-edit"></i>
            </button>
            <button @click="deleteCategory(category)" class="action-btn delete" title="Delete Category">
              <i class="fas fa-trash"></i>
            </button>
          </div>
        </div>
        <div class="category-content">
          <h3>{{ category.name }}</h3>
          <p>{{ category.description }}</p>
          <div class="category-stats">
            <span class="stat">
              <i class="fas fa-graduation-cap"></i>
              {{ category.course_count || 0 }} Courses
            </span>
            <span class="stat">
              <i class="fas fa-users"></i>
              {{ category.student_count || 0 }} Students
            </span>
          </div>
        </div>
      </div>

      <!-- Add Category Card -->
      <div @click="showAddCategoryModal = true" class="category-card add-card">
        <div class="add-content">
          <i class="fas fa-plus"></i>
          <h3>Add Category</h3>
          <p>Create a new course category</p>
        </div>
      </div>
    </div>

    <!-- Add/Edit Category Modal -->
    <div v-if="showAddCategoryModal" class="modal-overlay" @click="showAddCategoryModal = false">
      <div class="modal" @click.stop>
        <div class="modal-header">
          <h3>{{ editingCategory ? 'Edit Category' : 'Add New Category' }}</h3>
          <button @click="closeCategoryModal" class="btn-close">×</button>
        </div>
        <div class="modal-body">
          <form @submit.prevent="saveCategory">
            <div class="form-group">
              <label>Category Name</label>
              <input v-model="categoryForm.name" type="text" required>
            </div>
            <div class="form-group">
              <label>Description</label>
              <textarea v-model="categoryForm.description" rows="3"></textarea>
            </div>
            <div class="form-group">
              <label>Icon</label>
              <select v-model="categoryForm.icon">
                <option value="fas fa-folder">Folder</option>
                <option value="fas fa-code">Programming</option>
                <option value="fas fa-paint-brush">Design</option>
                <option value="fas fa-chart-line">Business</option>
                <option value="fas fa-heartbeat">Health</option>
                <option value="fas fa-camera">Photography</option>
                <option value="fas fa-music">Music</option>
                <option value="fas fa-language">Language</option>
              </select>
            </div>
            <div class="form-actions">
              <button type="submit" class="btn btn-primary">
                {{ editingCategory ? 'Update' : 'Add' }} Category
              </button>
              <button type="button" @click="closeCategoryModal" class="btn btn-secondary">Cancel</button>
            </div>
          </form>
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
import { ref, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'

const authStore = useAuthStore()

// State
const categories = ref([])
const showAddCategoryModal = ref(false)
const editingCategory = ref(null)
const message = ref('')
const messageType = ref('')

// Form
const categoryForm = ref({
  name: '',
  description: '',
  icon: 'fas fa-folder'
})

// Methods
const fetchCategories = async () => {
  try {
    const response = await fetch('http://127.0.0.1:8000/api/admin/categories', {
      headers: {
        'Authorization': `Bearer ${authStore.token}`,
        'Accept': 'application/json'
      }
    })
    const data = await response.json()
    categories.value = data.data || []
  } catch (error) {
    console.error('Error fetching categories:', error)
    showMessage('Error fetching categories', 'error')
  }
}

const editCategory = (category) => {
  editingCategory.value = category
  categoryForm.value = {
    name: category.name,
    description: category.description,
    icon: category.icon || 'fas fa-folder'
  }
  showAddCategoryModal.value = true
}

const deleteCategory = async (category) => {
  if (!confirm(`Are you sure you want to delete "${category.name}"?`)) return
  
  try {
    const response = await fetch(`http://127.0.0.1:8000/api/admin/categories/${category.id}`, {
      method: 'DELETE',
      headers: {
        'Authorization': `Bearer ${authStore.token}`,
        'Accept': 'application/json'
      }
    })
    
    if (response.ok) {
      categories.value = categories.value.filter(c => c.id !== category.id)
      showMessage('Category deleted successfully', 'success')
    }
  } catch (error) {
    console.error('Error deleting category:', error)
    showMessage('Error deleting category', 'error')
  }
}

const saveCategory = async () => {
  try {
    const url = editingCategory.value 
      ? `http://127.0.0.1:8000/api/admin/categories/${editingCategory.value.id}`
      : 'http://127.0.0.1:8000/api/admin/categories'
    
    const method = editingCategory.value ? 'PUT' : 'POST'
    
    const response = await fetch(url, {
      method,
      headers: {
        'Authorization': `Bearer ${authStore.token}`,
        'Accept': 'application/json',
        'Content-Type': 'application/json'
      },
      body: JSON.stringify(categoryForm.value)
    })
    
    if (response.ok) {
      await fetchCategories()
      closeCategoryModal()
      showMessage(`Category ${editingCategory.value ? 'updated' : 'added'} successfully`, 'success')
    }
  } catch (error) {
    console.error('Error saving category:', error)
    showMessage('Error saving category', 'error')
  }
}

const closeCategoryModal = () => {
  showAddCategoryModal.value = false
  editingCategory.value = null
  categoryForm.value = {
    name: '',
    description: '',
    icon: 'fas fa-folder'
  }
}

const showMessage = (text, type) => {
  message.value = text
  messageType.value = type
  setTimeout(() => {
    message.value = ''
  }, 3000)
}

// Lifecycle
onMounted(() => {
  fetchCategories()
})
</script>

<style scoped>
.admin-categories {
  padding: 0;
}

/* Header */
.categories-header {
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

/* Categories Grid */
.categories-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 25px;
}

.category-card {
  background: white;
  border-radius: 12px;
  padding: 25px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  transition: transform 0.3s ease, box-shadow 0.3s ease;
  cursor: pointer;
}

.category-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
}

.category-card.add-card {
  background: linear-gradient(135deg, #ff6b35 0%, #f7931e 100%);
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  text-align: center;
}

.category-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 20px;
}

.category-icon {
  width: 50px;
  height: 50px;
  border-radius: 10px;
  background: #f8f9fa;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
  color: #3498db;
}

.add-card .category-icon {
  background: rgba(255, 255, 255, 0.2);
  color: white;
}

.category-actions {
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

.action-btn.edit {
  background: #fff3e0;
  color: #f57c00;
}

.action-btn.edit:hover {
  background: #ffe0b2;
}

.action-btn.delete {
  background: #ffebee;
  color: #f44336;
}

.action-btn.delete:hover {
  background: #ffcdd2;
}

.category-content h3 {
  margin: 0 0 10px 0;
  color: #333;
  font-size: 1.2rem;
}

.add-card h3 {
  color: white;
}

.category-content p {
  margin: 0 0 20px 0;
  color: #666;
  font-size: 0.9rem;
  line-height: 1.5;
}

.add-card p {
  color: rgba(255, 255, 255, 0.9);
}

.category-stats {
  display: flex;
  gap: 20px;
}

.stat {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 0.9rem;
  color: #6c757d;
}

.add-card .stat {
  color: rgba(255, 255, 255, 0.9);
}

.add-content {
  text-align: center;
}

.add-content i {
  font-size: 3rem;
  margin-bottom: 20px;
  opacity: 0.8;
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
  max-width: 500px;
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
.form-group textarea,
.form-group select {
  width: 100%;
  padding: 12px;
  border: 1px solid #dee2e6;
  border-radius: 8px;
  font-size: 0.9rem;
}

.form-group textarea {
  resize: vertical;
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

/* Responsive */
@media (max-width: 768px) {
  .categories-grid {
    grid-template-columns: 1fr;
  }
  
  .header-content {
    flex-direction: column;
    gap: 15px;
    align-items: flex-start;
  }
}
</style>
