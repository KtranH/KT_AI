import apiClient from '../../config/apiConfig'

// === USER/PROFILE API ===
export const profileAPI = {
  // User Info
  getUserById: (userId) => apiClient.get(`/user/${userId}`),
  
  // Profile Updates
  updateAvatar: (formData) => apiClient.post('/update-avatar', formData, {
    headers: {
      'Content-Type': 'multipart/form-data',
      'Accept': 'application/json'
    },
    transformRequest: [(data) => data]
  }),
  updateCoverImage: (formData) => apiClient.post('/update-cover-image', formData, {
    headers: {
      'Content-Type': 'multipart/form-data'
    }
  }),
  updateName: (data) => apiClient.patch('/update-name', data),
  updatePassword: (data) => apiClient.patch('/update-password', data),
  
  // Utilities
  checkPassword: (data) => apiClient.post('/check-password', data),
  checkCredits: () => apiClient.get('/check-credits'),
} 