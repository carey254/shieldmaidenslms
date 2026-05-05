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
        
        // Mock authentication - accept both email versions
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
        content: '<p>We are excited to announce a free digital skills course starting next month.</p>',
        category: 'opportunity',
        priority: 'high',
        is_featured: true,
        created_at: new Date().toISOString()
      }
    ]));
    console.log('✅ Announcements fetched');
    return;
  }
  
  // 404 for other routes
  res.writeHead(404, { 'Content-Type': 'application/json' });
  res.end(JSON.stringify({
    message: 'Not Found'
  }));
});

const PORT = 3005;
server.listen(PORT, () => {
  console.log(`🚀 Final Backend Server running on http://localhost:${PORT}`);
  console.log('\n📋 Available Admin Logins:');
  console.log('================');
  console.log('🔐 ADMIN LOGIN:');
  console.log('   Email: admin@shieldmaidens.org');
  console.log('   Password: admin123');
  console.log('');
  console.log('👨‍🏫 INSTRUCTOR LOGIN:');
  console.log('   Email: instructor@shieldmaidens.org OR instructor@theshieldmaidens.org');
  console.log('   Password: instructor123');
  console.log('');
  console.log('👨‍🎓 STUDENT LOGIN:');
  console.log('   Email: student@shieldmaidens.org OR student@theshieldmaidens.org');
  console.log('   Password: student123');
  console.log('================');
  console.log('\n✅ Both email versions (with/without "the") now work!');
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
