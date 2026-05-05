// Simple test script to check backend connection
// Run this in browser console: node test-connection.js

const axios = require('axios');

async function testConnection() {
  const ports = [3000, 8000, 5000];
  
  for (const port of ports) {
    try {
      console.log(`Testing port ${port}...`);
      const response = await axios.get(`http://localhost:${port}/api/test`, { timeout: 3000 });
      console.log(`✅ Port ${port} is working!`);
      console.log('Response:', response.data);
      return port;
    } catch (error) {
      console.log(`❌ Port ${port} failed:`, error.message);
    }
  }
}

testConnection();
