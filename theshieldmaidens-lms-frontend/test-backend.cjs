// Simple test backend server for authentication testing
// Run with: node test-backend.js

const express = require('express');
const cors = require('cors');
const jwt = require('jsonwebtoken');

const app = express();
const PORT = 3000;
const JWT_SECRET = 'test-secret-key';

// Middleware
app.use(cors());
app.use(express.json());

// Mock users database
const users = {
  admin: {
    id: 1,
    name: 'Admin User',
    email: 'admin@shieldmaidens.org',
    password: 'admin123',
    is_admin: true,
    is_instructor: false
  },
  instructor: {
    id: 2,
    name: 'Instructor User',
    email: 'instructor@shieldmaidens.org',
    password: 'instructor123',
    is_admin: false,
    is_instructor: true
  },
  student: {
    id: 3,
    name: 'Student User',
    email: 'student@shieldmaidens.org',
    password: 'student123',
    is_admin: false,
    is_instructor: false
  }
};

// Mock announcements
let announcements = [
  {
    id: 1,
    title: 'Free Digital Skills Course Available',
    content: '<p>We are excited to announce a free digital skills course starting next month. This course covers web development, cybersecurity basics, and digital literacy.</p><p>Perfect for beginners and intermediate learners.</p>',
    category: 'opportunity',
    priority: 'high',
    is_featured: true,
    application_link: 'https://example.com/apply',
    expiry_date: new Date(Date.now() + 30 * 24 * 60 * 60 * 1000).toISOString(),
    created_at: new Date().toISOString()
  },
  {
    id: 2,
    title: 'Mentorship Program Applications Open',
    content: '<p>Our mentorship program pairs students with industry professionals for career guidance.</p><p>Apply now for this unique opportunity!</p>',
    category: 'mentorship',
    priority: 'urgent',
    is_featured: true,
    application_link: 'https://example.com/mentorship',
    expiry_date: new Date(Date.now() + 15 * 24 * 60 * 60 * 1000).toISOString(),
    created_at: new Date().toISOString()
  }
];

// Routes
app.post('/api/login', (req, res) => {
  const { email, password } = req.body;
  
  console.log('Login attempt:', { email, password });
  
  // Find user
  let user = null;
  if (email === 'admin@shieldmaidens.org' && password === 'admin123') {
    user = users.admin;
  } else if (email === 'instructor@shieldmaidens.org' && password === 'instructor123') {
    user = users.instructor;
  } else if (email === 'student@shieldmaidens.org' && password === 'student123') {
    user = users.student;
  }
  
  if (user) {
    const token = jwt.sign({ userId: user.id }, JWT_SECRET, { expiresIn: '24h' });
    
    res.json({
      access_token: token,
      user: {
        ...user,
        password_change_required: false // Set to true to test password change
      }
    });
    
    console.log('Login successful:', user.email);
  } else {
    res.status(401).json({
      message: 'Invalid credentials'
    });
  }
});

app.post('/api/register', (req, res) => {
  const { name, email, password, password_confirmation } = req.body;
  
  console.log('Registration attempt:', { name, email });
  
  // Simple validation
  if (!name || !email || !password) {
    return res.status(400).json({
      message: 'All fields are required'
    });
  }
  
  if (password !== password_confirmation) {
    return res.status(400).json({
      message: 'Passwords do not match'
    });
  }
  
  // Create new user (mock)
  const newUser = {
    id: Date.now(),
    name,
    email,
    is_admin: false,
    is_instructor: false
  };
  
  const token = jwt.sign({ userId: newUser.id }, JWT_SECRET, { expiresIn: '24h' });
  
  res.json({
    access_token: token,
    user: newUser
  });
  
  console.log('Registration successful:', email);
});

app.post('/api/forgot-password', (req, res) => {
  const { email } = req.body;
  
  console.log('Password reset request:', email);
  
  res.json({
    message: 'If an account exists with this email, you will receive a password reset link.'
  });
});

app.post('/api/change-password', (req, res) => {
  const { current_password, new_password, new_password_confirmation } = req.body;
  
  console.log('Password change attempt');
  
  if (new_password !== new_password_confirmation) {
    return res.status(400).json({
      message: 'New passwords do not match'
    });
  }
  
  res.json({
    message: 'Password changed successfully'
  });
});

// Announcement routes
app.get('/api/student/announcements', (req, res) => {
  console.log('Fetching student announcements');
  res.json(announcements);
});

app.post('/api/admin/announcements', (req, res) => {
  const announcement = {
    id: Date.now(),
    ...req.body,
    created_at: new Date().toISOString()
  };
  
  announcements.push(announcement);
  
  console.log('New announcement created:', announcement.title);
  
  res.json(announcement);
});

app.get('/api/user', (req, res) => {
  // Mock user data - in real app, verify JWT token
  res.json({
    id: 1,
    name: 'Test User',
    email: 'test@example.com',
    is_admin: false,
    is_instructor: false
  });
});

// Test endpoint
app.get('/api/test', (req, res) => {
  res.json({
    message: 'Backend is working!',
    timestamp: new Date().toISOString()
  });
});

// Start server
app.listen(PORT, () => {
  console.log(`🚀 Test Backend Server running on http://localhost:${PORT}`);
  console.log('\n📋 Available Admin Logins:');
  console.log('================');
  console.log('🔐 ADMIN LOGIN:');
  console.log('   Email: admin@shieldmaidens.org');
  console.log('   Password: admin123');
  console.log('');
  console.log('👨‍🏫 INSTRUCTOR LOGIN:');
  console.log('   Email: instructor@shieldmaidens.org');
  console.log('   Password: instructor123');
  console.log('');
  console.log('👨‍🎓 STUDENT LOGIN:');
  console.log('   Email: student@shieldmaidens.org');
  console.log('   Password: student123');
  console.log('================');
  console.log('\n🌐 Frontend URL: http://localhost:5173');
  console.log('\n✅ Now you can test all authentication features!');
});
