import apiClient from '../config/apiConfig'

// === AUTH API ===
export const authAPI = {
  // Basic Auth
  login: (credentials) => apiClient.post('/login', credentials),
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

// === FEATURES API ===
export const featuresAPI = {
  getAll: () => apiClient.get('/load_features'),
  getById: (id) => apiClient.get(`/load_features/${id}`),
}

// === IMAGES API ===
export const imageAPI = {
  // Public Routes
  getImages: (id) => apiClient.get(`/get_images_information/${id}`),
  getImagesByFeature: (id, page = 1) => apiClient.get(`/get_images_by_feature/${id}?page=${page}`),
  
  // Protected Routes
  getImagesUploaded: (userId) => {
    const params = userId !== null && userId !== undefined ? { user_id: userId } : {};
    return apiClient.get('/get_images_uploaded', { params });
  },
  getImagesLiked: (userId) => {
    const params = userId !== null && userId !== undefined ? { user_id: userId } : {};
    return apiClient.get('/get_images_liked', { params });
  },
  getImagesLikedPage: (url) => apiClient.get(url),
  getImagesUploadedPage: (url) => apiClient.get(url),
  checkForNewImages: (userId) => {
    const params = userId !== null && userId !== undefined ? { user_id: userId } : {};
    return apiClient.get('/check_new_images', { params });
  },
  
  // Image Management
  delete: (id) => apiClient.delete(`/images/${id}`),
  update: (id, data) => apiClient.patch(`/images/${id}`, data),
}

// === IMAGE UPLOAD API ===
export const imageUploadAPI = {
  store: (formData, featureId) => apiClient.post(`/upload_images/${featureId}`, formData, {
    headers: {
      'Content-Type': 'multipart/form-data'
    }
  })
}

// === LIKES API ===
export const likeAPI = {
  // Public Routes
  getLikesByID: (id) => apiClient.get(`/get_likes_information/${id}`),
  
  // Protected Routes
  checkLiked: (id) => apiClient.get(`/check_liked/${id}`),
  likePost: async (id) => {
    try {
      const response = await apiClient.post(`/like_post/${id}`)
      return response
    } catch (error) {
      console.error('Lỗi khi thực hiện like post:', error)
      throw error
    }
  },
  unlikePost: async (id) => {
    try {
      const response = await apiClient.post(`/unlike_post/${id}`)
      return response
    } catch (error) {
      console.error('Lỗi khi thực hiện unlike post:', error)
      throw error
    }
  }
}

// === COMMENTS API ===
export const commentAPI = {
  getComments: (imageId, page = 1, commentId = null) => {
    let url = `/images/${imageId}/comments?page=${page}`
    if (commentId) url += `&comment_id=${commentId}`
    return apiClient.get(url)
  },
  createComment: (commentData) => apiClient.post('/comments', commentData),
  createReply: (commentId, replyData) => apiClient.post(`/comments/${commentId}/reply`, replyData),
  updateComment: (commentId, content) => apiClient.patch(`/comments/${commentId}`, { content }),
  deleteComment: (commentId) => apiClient.delete(`/comments/${commentId}`),
  toggleLike: (commentId) => apiClient.post(`/comments/${commentId}/toggle-like`)
}

// === NOTIFICATIONS API ===
export const notificationAPI = {
  getNotifications: (page = 1, filter = 'all', append = false) => apiClient.get('/notifications', {
    params: {
      page: page,
      per_page: 10,
      paginate: true,
      filter: filter
    }
  }),
  markAsRead: (notificationId) => apiClient.post(`/notifications/${notificationId}/read`),
  markAllAsRead: () => apiClient.post('/notifications/mark-all-read'),
  getUnreadCount: () => apiClient.get('/notifications/unread-count')
}

// === IMAGE JOBS API (ComfyUI) ===
export const imageJobsAPI = {
  createJob: (formData) => apiClient.post('/image-jobs/create', formData, {
    headers: {
      'Content-Type': 'multipart/form-data'
    }
  }),
  getActiveJobs: () => apiClient.get('/image-jobs/active'),
  getCompletedJobs: () => apiClient.get('/image-jobs/completed'),
  getFailedJobs: () => apiClient.get('/image-jobs/failed'),
  checkJobStatus: (jobId) => apiClient.get(`/image-jobs/${jobId}`),
  cancelJob: (jobId) => apiClient.delete(`/image-jobs/${jobId}`),
  retryJob: (jobId) => apiClient.post(`/image-jobs/${jobId}/retry`),
}

// === STATISTICS API ===
export const statisticsAPI = {
  getStatistics: () => apiClient.get('/statistics'),
}

// === PROXY API ===
export const proxyAPI = {
  getR2Image: (url) => apiClient.get(`/proxy/r2-image?url=${encodeURIComponent(url)}`),
  downloadR2Image: (url, filename) => apiClient.get(`/download/r2-image?url=${encodeURIComponent(url)}&filename=${encodeURIComponent(filename)}`),
  downloadFromR2Storage: (url, filename) => apiClient.get(`/r2-download?url=${encodeURIComponent(url)}&filename=${encodeURIComponent(filename)}`),
}

// === GENERIC API ===
export const genericAPI = {
  get: (endpoint, config) => apiClient.get(endpoint, config),
  post: (endpoint, data, config) => apiClient.post(endpoint, data, config),
  put: (endpoint, data, config) => apiClient.put(endpoint, data, config),
  patch: (endpoint, data, config) => apiClient.patch(endpoint, data, config),
  delete: (endpoint, config) => apiClient.delete(endpoint, config)
}

// === BACKWARD COMPATIBILITY ALIASES ===
// Để đảm bảo code cũ vẫn hoạt động
export const verificationAPI = {
  resendCode: (email) => authAPI.resendVerification(email)
}

export const comfyuiAPI = imageJobsAPI // Alias for backward compatibility
