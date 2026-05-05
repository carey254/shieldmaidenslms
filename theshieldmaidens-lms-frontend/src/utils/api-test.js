// Simple API connection test
export const testApiConnection = async () => {
  try {
    const response = await fetch('http://127.0.0.1:8000/api/test', {
      method: 'GET',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      }
    });
    
    if (response.ok) {
      const data = await response.json();
      console.log('API Connection test successful:', data);
      return true;
    } else {
      console.error('API Connection test failed:', response.status, response.statusText);
      return false;
    }
  } catch (error) {
    console.error('API Connection test error:', error);
    return false;
  }
};

// Test login endpoint directly
export const testLoginEndpoint = async () => {
  try {
    const response = await fetch('http://127.0.0.1:8000/api/login', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify({
        email: 'test@example.com',
        password: 'testpassword',
        role: 'student'
      })
    });
    
    console.log('Login endpoint test response:', response.status);
    
    if (response.ok) {
      const data = await response.json();
      console.log('Login endpoint test successful:', data);
      return { success: true, data };
    } else {
      const errorData = await response.json().catch(() => ({}));
      console.error('Login endpoint test failed:', response.status, errorData);
      return { success: false, error: errorData };
    }
  } catch (error) {
    console.error('Login endpoint test error:', error);
    return { success: false, error: error.message };
  }
};
