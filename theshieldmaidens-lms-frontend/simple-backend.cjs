const http = require('http');
const url = require('url');

// Simple HTTP server (no dependencies needed)
const server = http.createServer((req, res) => {
  // Enable CORS
  res.setHeader('Access-Control-Allow-Origin', '*');
  res.setHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
  res.setHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization');
  
  // Handle preflight requests
  if (req.method === 'OPTIONS') {
    res.writeHead(200);
    res.end();
    return;
  }
  
  const parsedUrl = url.parse(req.url, true);
  const path = parsedUrl.pathname;
  
  console.log(`${req.method} ${path}`);
  
  // Routes
  if (path === '/api/test') {
    res.writeHead(200, { 'Content-Type': 'application/json' });
    res.end(JSON.stringify({
      message: 'Backend is working!',
      timestamp: new Date().toISOString()
    }));
    return;
  }
  
  if (path === '/api/login' && req.method === 'POST') {
    let body = '';
    req.on('data', chunk => {
      body += chunk.toString();
    });
    req.on('end', () => {
      try {
        const data = JSON.parse(body);
        console.log('Login attempt:', data.email);
        
        // Mock authentication
        let user = null;
        if (data.email === 'admin@shieldmaidens.org' && data.password === 'admin123') {
          user = {
            id: 1,
            name: 'Admin User',
            email: 'admin@shieldmaidens.org',
            is_admin: true,
            is_instructor: false
          };
        } else if ((data.email === 'instructor@shieldmaidens.org' || data.email === 'instructor@theshieldmaidens.org') && data.password === 'instructor123') {
          user = {
            id: 2,
            name: 'Instructor User',
            email: 'instructor@shieldmaidens.org',
            is_admin: false,
            is_instructor: true
          };
        } else if ((data.email === 'student@shieldmaidens.org' || data.email === 'student@theshieldmaidens.org') && data.password === 'student123') {
          user = {
            id: 3,
            name: 'Student User',
            email: 'student@shieldmaidens.org',
            is_admin: false,
            is_instructor: false
          };
        }
        
        if (user) {
          res.writeHead(200, { 'Content-Type': 'application/json' });
          res.end(JSON.stringify({
            access_token: 'mock-jwt-token-' + Date.now(),
            user: {
              ...user,
              password_change_required: false
            }
          }));
          console.log('✅ Login successful:', data.email);
        } else {
          res.writeHead(401, { 'Content-Type': 'application/json' });
          res.end(JSON.stringify({
            message: 'Invalid credentials'
          }));
          console.log('❌ Login failed:', data.email);
        }
      } catch (error) {
        res.writeHead(400, { 'Content-Type': 'application/json' });
        res.end(JSON.stringify({
          message: 'Invalid JSON'
        }));
      }
    });
    return;
  }
  
  if (path === '/api/student/announcements' && req.method === 'GET') {
    res.writeHead(200, { 'Content-Type': 'application/json' });
    res.end(JSON.stringify([
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
    ]));
    console.log('✅ Announcements fetched');
    return;
  }
  
  if (path === '/api/admin/announcements' && req.method === 'POST') {
    let body = '';
    req.on('data', chunk => {
      body += chunk.toString();
    });
    req.on('end', () => {
      try {
        const data = JSON.parse(body);
        const announcement = {
          id: Date.now(),
          ...data,
          created_at: new Date().toISOString()
        };
        
        res.writeHead(200, { 'Content-Type': 'application/json' });
        res.end(JSON.stringify(announcement));
        console.log('✅ Announcement created:', announcement.title);
      } catch (error) {
        res.writeHead(400, { 'Content-Type': 'application/json' });
        res.end(JSON.stringify({
          message: 'Invalid JSON'
        }));
      }
    });
    return;
  }
  
  if (path === '/api/register' && req.method === 'POST') {
    let body = '';
    req.on('data', chunk => {
      body += chunk.toString();
    });
    req.on('end', () => {
      try {
        const data = JSON.parse(body);
        console.log('Registration attempt:', data.email);
        
        // Mock registration
        const newUser = {
          id: Date.now(),
          name: data.name,
          email: data.email,
          is_admin: false,
          is_instructor: false
        };
        
        res.writeHead(200, { 'Content-Type': 'application/json' });
        res.end(JSON.stringify({
          access_token: 'mock-jwt-token-' + Date.now(),
          user: newUser
        }));
        console.log('✅ Registration successful:', data.email);
      } catch (error) {
        res.writeHead(400, { 'Content-Type': 'application/json' });
        res.end(JSON.stringify({
          message: 'Invalid JSON'
        }));
      }
    });
    return;
  }
  
  if (path === '/api/forgot-password' && req.method === 'POST') {
    let body = '';
    req.on('data', chunk => {
      body += chunk.toString();
    });
    req.on('end', () => {
      try {
        const data = JSON.parse(body);
        console.log('Password reset request:', data.email);
        
        res.writeHead(200, { 'Content-Type': 'application/json' });
        res.end(JSON.stringify({
          message: 'If an account exists with this email, you will receive a password reset link.'
        }));
      } catch (error) {
        res.writeHead(400, { 'Content-Type': 'application/json' });
        res.end(JSON.stringify({
          message: 'Invalid JSON'
        }));
      }
    });
    return;
  }
  
  if (path === '/api/change-password' && req.method === 'POST') {
    let body = '';
    req.on('data', chunk => {
      body += chunk.toString();
    });
    req.on('end', () => {
      try {
        const data = JSON.parse(body);
        console.log('Password change attempt');
        
        if (data.new_password !== data.new_password_confirmation) {
          res.writeHead(400, { 'Content-Type': 'application/json' });
          res.end(JSON.stringify({
            message: 'New passwords do not match'
          }));
          return;
        }
        
        res.writeHead(200, { 'Content-Type': 'application/json' });
        res.end(JSON.stringify({
          message: 'Password changed successfully'
        }));
      } catch (error) {
        res.writeHead(400, { 'Content-Type': 'application/json' });
        res.end(JSON.stringify({
          message: 'Invalid JSON'
        }));
      }
    });
    return;
  }
  
  if (path === '/api/user' && req.method === 'GET') {
    res.writeHead(200, { 'Content-Type': 'application/json' });
    res.end(JSON.stringify({
      id: 1,
      name: 'Test User',
      email: 'test@example.com',
      is_admin: false,
      is_instructor: false
    }));
    return;
  }
  
  // 404 for other routes
  res.writeHead(404, { 'Content-Type': 'application/json' });
  res.end(JSON.stringify({
    message: 'Not Found'
  }));
});

const PORT = 3001;
server.listen(PORT, () => {
  console.log(`🚀 Simple Backend Server running on http://localhost:${PORT}`);
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
  console.log('\n✅ Now you can test all authentication features!');
});

server.on('error', (err) => {
  console.error('Server error:', err);
});

process.on('uncaughtException', (err) => {
  console.error('Uncaught exception:', err);
  process.exit(1);
});

process.on('unhandledRejection', (err) => {
  console.error('Unhandled rejection:', err);
  process.exit(1);
});
