import apiClient from '../config/apiConfig'

// Auth API calls
export const authAPI = {
  login: (credentials) => apiClient.post('/login', credentials),
  logout: () => apiClient.post('/logout'),
  check: () => apiClient.get('/check'),
}

// User verification API calls
export const verificationAPI = {
  resendCode: (email) => apiClient.post('/resend-verification', { email })
}

// Image  Information API calls
export const imageAPI = {
  getImagesByID: (id) => apiClient.get(`/get_images_information/${id}`)
}

// Google API calls
export const googleAPI = {
  getAuthUrl: () => apiClient.get('/auth/google/url'),
  callback: (code) => apiClient.get(`/auth/google/callback?code=${code}`)
}

// Turnstile API calls
export const turnstileAPI = {
  getConfig: () => apiClient.get('/turnstile/config')
}

// Features API calls
export const featuresAPI = {
  getAll: () => apiClient.get('/load_features'),
  getById: (id) => apiClient.get(`/load_features/${id}`)
}

// Generic API - for any other endpoints
export const genericAPI = {
  get: (endpoint, config) => apiClient.get(endpoint, config),
  post: (endpoint, data, config) => apiClient.post(endpoint, data, config),
  put: (endpoint, data, config) => apiClient.put(endpoint, data, config),
  delete: (endpoint, config) => apiClient.delete(endpoint, config)
}
