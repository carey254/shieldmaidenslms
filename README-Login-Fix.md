# Login Form Debugging Guide

## Issues Fixed

1. **Unified Login Form**: Consolidated admin, instructor, and student login into one form with role selection
2. **Authentication Guard**: Added authentication check to course enrollment
3. **Better Error Handling**: Improved error messages and debugging information
4. **API Connection Testing**: Added automatic API connection testing

## How the Login Flow Works Now

### 1. Course Enrollment Flow
- When user clicks "Enroll Now" on any course
- System checks if user is authenticated
- If not authenticated -> redirects to login page with return URL
- If authenticated -> enrolls in course

### 2. Login Process
- User selects role (Student/Instructor/Admin)
- User enters email and password
- Form validates inputs
- API call to backend: `POST http://localhost:8000/api/login`
- Backend validates credentials and role
- Successful login -> redirect to appropriate dashboard
- Failed login -> show error message

## Required Backend Setup

### Backend Server Must Be Running
```bash
# The backend should be running on:
http://localhost:8000
```

### Required API Endpoints
```
GET  http://localhost:8000/api/test     (for connection testing)
POST http://localhost:8000/api/login    (for authentication)
POST http://localhost:8000/api/register (for registration)
GET  http://localhost:8000/api/user    (for user data)
```

### CORS Configuration
The backend must allow requests from: `http://localhost:3000` (or whatever port your frontend is running on)

### Expected Login Response Format
```json
{
  "access_token": "jwt_token_here",
  "user": {
    "id": 1,
    "name": "Student Name",
    "email": "student@example.com",
    "role": "student",
    "is_admin": false,
    "is_instructor": false
  }
}
```

## Testing the Login

### 1. Check Browser Console
Open browser dev tools (F12) and check console for:
- API connection test results
- Login attempt logs
- Error messages

### 2. Test API Connection
The login form automatically tests the API connection when loaded. You can also manually test:
```javascript
// In browser console
fetch('http://localhost:8000/api/test')
  .then(r => r.json())
  .then(d => console.log('API Test:', d))
  .catch(e => console.error('API Error:', e))
```

### 3. Common Issues and Solutions

#### Network Error
**Problem**: "Network error. Please check your internet connection"
**Solution**: 
- Ensure backend server is running on localhost:8000
- Check no firewall blocking the connection
- Verify CORS is configured correctly

#### Invalid Credentials
**Problem**: "Invalid credentials. Please check your email and password"
**Solution**: 
- Check if user exists in database
- Verify password is correct
- Ensure user has the correct role

#### Access Denied
**Problem**: "Access denied. This account may not have the required role for this portal"
**Solution**: 
- Check user role in database matches selected role
- Ensure `is_admin`, `is_instructor` flags are correct

## Student Registration Flow

1. User goes to `/register`
2. Fills out registration form
3. API call to `POST /api/register`
4. Successful registration -> auto-login and redirect to student dashboard
5. Failed registration -> show validation errors

## Next Steps

1. **Start Backend Server**: Make sure your Laravel/Node.js backend is running
2. **Test Connection**: Visit login page and check console for API test results
3. **Create Test Account**: Use register form to create a student account
4. **Test Login**: Try logging in with the test account
5. **Test Enrollment**: Try enrolling in a course to verify the authentication guard

## Debug Information

The login form now includes:
- Automatic API connection testing
- Detailed error messages
- Network troubleshooting tips
- Console logging for debugging

Check the browser console for detailed information about any issues.
