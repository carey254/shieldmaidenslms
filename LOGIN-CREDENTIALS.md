# Login Credentials - The Shield Maidens LMS

## Working Test Credentials

### Student Login
- **Email:** jane.student@theshieldmaidens.org
- **Password:** student123

### Alternative Student Login
- **Email:** test@example.com
- **Password:** password

### Admin Login
- **Email:** admin@theshieldmaidens.org
- **Password:** admin123
- **Redirects to:** Admin Dashboard

### Instructor/Facilitator Login
- **Email:** instructor@theshieldmaidens.org
- **Password:** instructor123
- **Redirects to:** Instructor Dashboard

## Access URLs

### Frontend Development Server
- **URL:** http://127.0.0.1:5184
- **Unified Login Page:** http://127.0.0.1:5184/login
- **All users use the same login form and are redirected to their respective dashboards**

### Backend API
- **URL:** http://localhost:8000
- **Login Endpoint:** http://localhost:8000/api/login

## Status

✅ Backend API is running and responding correctly
✅ Frontend development server is running
✅ CORS is properly configured
✅ Test users are created and working
✅ Login API endpoint is functional

## Testing

Both backend and frontend servers are currently running. You can:

1. Test the API directly using curl/Postman with the credentials above
2. Test the frontend login forms at the provided URLs
3. Use the browser preview to interact with the login interface

The login system should now be working properly with these credentials.
