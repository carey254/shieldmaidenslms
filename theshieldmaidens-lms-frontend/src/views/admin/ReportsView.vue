<template>
  <div class="admin-reports">
    <!-- Header -->
    <div class="reports-header">
      <div class="header-content">
        <div class="header-left">
          <h1><i class="fas fa-chart-bar"></i> Reports</h1>
          <p>Generate and view comprehensive system reports</p>
        </div>
        <div class="header-actions">
          <button @click="generateReport" class="btn btn-primary">
            <i class="fas fa-file-export"></i> Generate Report
          </button>
        </div>
      </div>
    </div>

    <!-- Report Types -->
    <div class="report-types-grid">
      <div @click="selectReportType('enrollment')" class="report-type-card" :class="{ active: selectedReportType === 'enrollment' }">
        <div class="report-icon">
          <i class="fas fa-user-graduate"></i>
        </div>
        <h3>Enrollment Report</h3>
        <p>Student enrollment trends and statistics</p>
      </div>
      
      <div @click="selectReportType('performance')" class="report-type-card" :class="{ active: selectedReportType === 'performance' }">
        <div class="report-icon">
          <i class="fas fa-chart-line"></i>
        </div>
        <h3>Performance Report</h3>
        <p>Student grades and course completion rates</p>
      </div>
      
      <div @click="selectReportType('financial')" class="report-type-card" :class="{ active: selectedReportType === 'financial' }">
        <div class="report-icon">
          <i class="fas fa-dollar-sign"></i>
        </div>
        <h3>Financial Report</h3>
        <p>Revenue and payment analytics</p>
      </div>
      
      <div @click="selectReportType('engagement')" class="report-type-card" :class="{ active: selectedReportType === 'engagement' }">
        <div class="report-icon">
          <i class="fas fa-users"></i>
        </div>
        <h3>Engagement Report</h3>
        <p>User activity and platform usage metrics</p>
      </div>
    </div>

    <!-- Report Configuration -->
    <div v-if="selectedReportType" class="report-config">
      <div class="config-header">
        <h3>Configure {{ getReportTitle(selectedReportType) }}</h3>
      </div>
      <div class="config-form">
        <div class="form-row">
          <div class="form-group">
            <label>Date Range</label>
            <select v-model="reportConfig.dateRange" @change="updateDateRange">
              <option value="7">Last 7 days</option>
              <option value="30">Last 30 days</option>
              <option value="90">Last 90 days</option>
              <option value="365">Last year</option>
              <option value="custom">Custom range</option>
            </select>
          </div>
          <div class="form-group" v-if="reportConfig.dateRange === 'custom'">
            <label>Start Date</label>
            <input v-model="reportConfig.startDate" type="date">
          </div>
          <div class="form-group" v-if="reportConfig.dateRange === 'custom'">
            <label>End Date</label>
            <input v-model="reportConfig.endDate" type="date">
          </div>
        </div>
        <div class="form-row" v-if="selectedReportType === 'performance'">
          <div class="form-group">
            <label>Course Filter</label>
            <select v-model="reportConfig.courseId">
              <option value="">All Courses</option>
              <option v-for="course in courses" :key="course.id" :value="course.id">
                {{ course.title }}
              </option>
            </select>
          </div>
        </div>
        <div class="form-actions">
          <button @click="generateReport" class="btn btn-primary">
            <i class="fas fa-play"></i> Generate Report
          </button>
          <button @click="exportReport" class="btn btn-secondary" :disabled="!reportData">
            <i class="fas fa-download"></i> Export to PDF
          </button>
        </div>
      </div>
    </div>

    <!-- Report Results -->
    <div v-if="reportData" class="report-results">
      <div class="results-header">
        <h3>{{ getReportTitle(selectedReportType) }} Results</h3>
        <span class="report-date">Generated: {{ formatDate(new Date()) }}</span>
      </div>
      
      <!-- Summary Cards -->
      <div class="summary-cards">
        <div v-for="(metric, index) in reportData.summary" :key="index" class="summary-card">
          <div class="metric-icon" :style="{ backgroundColor: metric.color + '20', color: metric.color }">
            <i :class="metric.icon"></i>
          </div>
          <div class="metric-content">
            <h4>{{ metric.value }}</h4>
            <p>{{ metric.label }}</p>
            <span class="metric-change" :class="metric.changeType">
              <i :class="metric.changeIcon"></i>
              {{ metric.change }}
            </span>
          </div>
        </div>
      </div>

      <!-- Charts Section -->
      <div class="charts-section">
        <div v-for="(chart, index) in reportData.charts" :key="index" class="chart-card">
          <div class="chart-header">
            <h4>{{ chart.title }}</h4>
            <p>{{ chart.description }}</p>
          </div>
          <div class="chart-content">
            <div class="chart-placeholder">
              <i class="fas fa-chart-bar"></i>
              <p>{{ chart.type }} chart will be rendered here</p>
              <small>Data: {{ chart.dataPoints }} data points</small>
            </div>
          </div>
        </div>
      </div>

      <!-- Data Table -->
      <div class="data-table-section">
        <div class="table-header">
          <h4>Detailed Data</h4>
          <button @click="exportToCSV" class="btn btn-sm btn-secondary">
            <i class="fas fa-file-csv"></i> Export CSV
          </button>
        </div>
        <div class="table-container">
          <table class="data-table">
            <thead>
              <tr>
                <th v-for="column in reportData.table.columns" :key="column.key">
                  {{ column.label }}
                </th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(row, index) in reportData.table.rows" :key="index">
                <td v-for="column in reportData.table.columns" :key="column.key">
                  {{ row[column.key] }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="generatingReport" class="loading-state">
      <div class="loading-content">
        <i class="fas fa-spinner fa-spin"></i>
        <h3>Generating Report...</h3>
        <p>Please wait while we compile your report data</p>
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
const selectedReportType = ref('')
const courses = ref([])
const generatingReport = ref(false)
const reportData = ref(null)
const message = ref('')
const messageType = ref('')

// Report Configuration
const reportConfig = ref({
  dateRange: '30',
  startDate: '',
  endDate: '',
  courseId: ''
})

// Methods
const fetchCourses = async () => {
  try {
    const response = await fetch('http://127.0.0.1:8000/api/admin/courses', {
      headers: {
        'Authorization': `Bearer ${authStore.token}`,
        'Accept': 'application/json'
      }
    })
    const data = await response.json()
    courses.value = data.data || []
  } catch (error) {
    console.error('Error fetching courses:', error)
  }
}

const selectReportType = (type) => {
  selectedReportType.value = type
  reportData.value = null
}

const getReportTitle = (type) => {
  const titles = {
    enrollment: 'Enrollment Report',
    performance: 'Performance Report',
    financial: 'Financial Report',
    engagement: 'Engagement Report'
  }
  return titles[type] || 'Report'
}

const updateDateRange = () => {
  if (reportConfig.value.dateRange !== 'custom') {
    const days = parseInt(reportConfig.value.dateRange)
    const endDate = new Date()
    const startDate = new Date()
    startDate.setDate(endDate.getDate() - days)
    
    reportConfig.value.startDate = startDate.toISOString().split('T')[0]
    reportConfig.value.endDate = endDate.toISOString().split('T')[0]
  }
}

const generateReport = async () => {
  if (!selectedReportType.value) {
    showMessage('Please select a report type', 'error')
    return
  }

  generatingReport.value = true
  
  try {
    const response = await fetch(`http://127.0.0.1:8000/api/admin/reports/${selectedReportType.value}`, {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('token')}`,
        'Accept': 'application/json',
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({
        dateRange: reportConfig.value.dateRange,
        startDate: reportConfig.value.startDate,
        endDate: reportConfig.value.endDate,
        courseId: reportConfig.value.courseId
      })
    })
    
    if (response.ok) {
      const data = await response.json()
      reportData.value = data
      showMessage('Report generated successfully', 'success')
    } else {
      throw new Error('Failed to generate report')
    }
  } catch (error) {
    console.error('Error generating report:', error)
    showMessage('Error generating report', 'error')
  } finally {
    generatingReport.value = false
  }
}


const exportReport = () => {
  if (!reportData.value) return
  
  showMessage('Exporting report to PDF...', 'success')
  // TODO: Implement PDF export functionality
}

const exportToCSV = () => {
  if (!reportData.value) return
  
  showMessage('Exporting data to CSV...', 'success')
  // TODO: Implement CSV export functionality
}

const formatDate = (date) => {
  return new Date(date).toLocaleDateString()
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
  fetchCourses()
  updateDateRange()
})
</script>

<style scoped>
.admin-reports {
  padding: 0;
}

/* Header */
.reports-header {
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

/* Report Types Grid */
.report-types-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 20px;
  margin-bottom: 25px;
}

.report-type-card {
  background: white;
  border-radius: 12px;
  padding: 30px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  text-align: center;
  cursor: pointer;
  transition: all 0.3s ease;
  border: 2px solid transparent;
}

.report-type-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
}

.report-type-card.active {
  border-color: #ff6b35;
  background: #fff8f5;
}

.report-icon {
  width: 60px;
  height: 60px;
  border-radius: 50%;
  background: linear-gradient(135deg, #ff6b35 0%, #f7931e 100%);
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
  margin: 0 auto 20px;
}

.report-type-card h3 {
  margin: 0 0 10px 0;
  color: #333;
  font-size: 1.2rem;
}

.report-type-card p {
  margin: 0;
  color: #666;
  font-size: 0.9rem;
}

/* Report Configuration */
.report-config {
  background: white;
  border-radius: 12px;
  padding: 25px;
  margin-bottom: 25px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.config-header h3 {
  margin: 0 0 20px 0;
  color: #333;
}

.config-form {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.form-row {
  display: flex;
  gap: 20px;
  flex-wrap: wrap;
}

.form-group {
  flex: 1;
  min-width: 200px;
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

.form-actions {
  display: flex;
  gap: 10px;
  justify-content: flex-start;
}

/* Report Results */
.report-results {
  background: white;
  border-radius: 12px;
  padding: 25px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.results-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 25px;
}

.results-header h3 {
  margin: 0;
  color: #333;
}

.report-date {
  color: #666;
  font-size: 0.9rem;
}

/* Summary Cards */
.summary-cards {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 20px;
  margin-bottom: 30px;
}

.summary-card {
  display: flex;
  align-items: center;
  gap: 20px;
  padding: 20px;
  background: #f8f9fa;
  border-radius: 12px;
}

.metric-icon {
  width: 50px;
  height: 50px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.2rem;
}

.metric-content h4 {
  margin: 0 0 5px 0;
  font-size: 1.5rem;
  font-weight: 700;
  color: #333;
}

.metric-content p {
  margin: 0 0 5px 0;
  color: #666;
  font-size: 0.9rem;
}

.metric-change {
  font-size: 0.8rem;
  font-weight: 600;
  display: flex;
  align-items: center;
  gap: 5px;
}

.metric-change.positive {
  color: #4CAF50;
}

.metric-change.negative {
  color: #f44336;
}

/* Charts Section */
.charts-section {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
  gap: 25px;
  margin-bottom: 30px;
}

.chart-card {
  background: #f8f9fa;
  border-radius: 12px;
  padding: 25px;
}

.chart-header h4 {
  margin: 0 0 5px 0;
  color: #333;
}

.chart-header p {
  margin: 0 0 20px 0;
  color: #666;
  font-size: 0.9rem;
}

.chart-placeholder {
  height: 200px;
  background: white;
  border-radius: 8px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  color: #6c757d;
}

.chart-placeholder i {
  font-size: 2rem;
  margin-bottom: 10px;
}

/* Data Table */
.data-table-section {
  margin-top: 30px;
}

.table-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.table-header h4 {
  margin: 0;
  color: #333;
}

.table-container {
  overflow-x: auto;
}

.data-table {
  width: 100%;
  border-collapse: collapse;
  background: white;
  border-radius: 8px;
  overflow: hidden;
}

.data-table th {
  background: #f8f9fa;
  padding: 15px;
  text-align: left;
  font-weight: 600;
  color: #495057;
  border-bottom: 2px solid #dee2e6;
}

.data-table td {
  padding: 15px;
  border-bottom: 1px solid #f1f3f5;
}

/* Loading State */
.loading-state {
  background: white;
  border-radius: 12px;
  padding: 60px;
  text-align: center;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.loading-content i {
  font-size: 3rem;
  color: #3498db;
  margin-bottom: 20px;
}

.loading-content h3 {
  margin: 0 0 10px 0;
  color: #333;
}

.loading-content p {
  margin: 0;
  color: #666;
}

/* Buttons */
.btn {
  padding: 10px 20px;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  display: inline-flex;
  align-items: center;
  gap: 8px;
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

.btn-secondary:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.btn-sm {
  padding: 8px 16px;
  font-size: 0.8rem;
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
  .report-types-grid {
    grid-template-columns: 1fr;
  }
  
  .summary-cards {
    grid-template-columns: 1fr;
  }
  
  .charts-section {
    grid-template-columns: 1fr;
  }
  
  .form-row {
    flex-direction: column;
  }
  
  .header-content {
    flex-direction: column;
    gap: 15px;
    align-items: flex-start;
  }
}
</style>
