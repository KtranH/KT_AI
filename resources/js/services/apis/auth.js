import apiClient from '../../config/apiConfig'

// === AUTH API ===
export const authAPI = {
  // Basic Auth
  login: (credentials) => apiClient.post('/login', credentials),
  // Mobile / API client login: nhận Bearer token
  mobileLogin: (credentials) => apiClient.post('/api-login', credentials),
  logout: () => apiClient.post('/logout'),
  check: () => apiClient.get('/check'),
  register: (userData) => apiClient.post('/register', userData),
  
  // Email Verification
  verifyEmail: (data) => apiClient.post('/verify-email', data),
  resendVerification: (email) => apiClient.post('/resend-verification', { email }),
  
  // Password Reset
  forgotPassword: (data) => apiClient.post('/forgot-password', data),
  verifyResetCode: (data) => apiClient.post('/verify-reset-code', data),
  resetPassword: (data) => apiClient.post('/reset-password', data),
  
  // Password Change Verification (Protected)
  sendPasswordChangeVerification: (data) => apiClient.post('/send-password-change-verification', data),
}

// === GOOGLE OAUTH API ===
export const googleAPI = {
  getAuthUrl: () => apiClient.get('/google/url'),
  callback: (code) => apiClient.get(`/google/callback?code=${code}`)
}

// === TURNSTILE API ===
export const turnstileAPI = {
  getConfig: () => apiClient.get('/turnstile/config')
}

// === BACKWARD COMPATIBILITY ===
export const verificationAPI = {
  resendCode: (email) => authAPI.resendVerification(email)
} 