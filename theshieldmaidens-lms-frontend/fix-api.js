// Run this in browser console to fix API URL immediately
console.log('🔧 Fixing API URL to port 3000...');

// Clear all storage
localStorage.clear();
sessionStorage.clear();

// Set correct API URL
localStorage.setItem('apiBaseUrl', 'http://localhost:3000/api');

// Clear any existing axios defaults
if (window.axios) {
  window.axios.defaults.baseURL = 'http://localhost:3000/api';
}

console.log('✅ API URL fixed! Refreshing page...');

// Refresh the page to apply changes
setTimeout(() => {
  window.location.reload();
}, 1000);
