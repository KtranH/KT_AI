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
  getImages: (id) => apiClient.get(`/get_images_information/${id}`),
  getImagesCreatedByUser: () => apiClient.get('/get_images_created_by_user'),
  getImagesByFeature: (id, page = 1) => apiClient.get(`/get_images_by_feature/${id}?page=${page}`),
  getLikes: (id) => apiClient.get(`/get_likes_information/${id}`),
  likePost: (id) => apiClient.post(`/like_post/${id}`),
  unlikePost: (id) => apiClient.post(`/unlike_post/${id}`)
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
  getById: (id) => apiClient.get(`/load_features/${id}`),
}

// Like API calls
export const likeAPI = {
  getLikesByID: (id) => apiClient.get(`/get_likes_information/${id}`),
  checkLiked: (id) => apiClient.get(`/check_liked/${id}`),
  likePost: async (id) => {
    try {
      const response = await apiClient.post(`/like_post/${id}`)
      return response
    } catch (error) {
      console.error('Lỗi khi thực hiện like post:', error)
      // Ném lỗi để xử lý ở composable
      throw error
    }
  },
  unlikePost: async (id) => {
    try {
      const response = await apiClient.post(`/unlike_post/${id}`)
      return response
    } catch (error) {
      console.error('Lỗi khi thực hiện unlike post:', error)
      // Ném lỗi để xử lý ở composable
      throw error
    }
  },
  toggleCommentLike: async (id) => {
    try {
      const response = await apiClient.post(`/comments/${id}/toggle-like`)
      return response
    } catch (error) {
      console.error('Lỗi khi thực hiện toggle like comment:', error)
      // Ném lỗi để xử lý ở composable
      throw error
    }
  }
}

// Comment API calls
export const commentAPI = {
  getComments: (imageId) => apiClient.get(`/images/${imageId}/comments`),
  createComment: (commentData) => apiClient.post('/comments', commentData),
  updateComment: (commentId, content) => apiClient.put(`/comments/${commentId}`, { content }),
  deleteComment: (commentId) => apiClient.delete(`/comments/${commentId}`),
  toggleLike: (commentId) => apiClient.post(`/comments/${commentId}/toggle-like`)
}

// Generic API - for any other endpoints
export const genericAPI = {
  get: (endpoint, config) => apiClient.get(endpoint, config),
  post: (endpoint, data, config) => apiClient.post(endpoint, data, config),
  put: (endpoint, data, config) => apiClient.put(endpoint, data, config),
  delete: (endpoint, config) => apiClient.delete(endpoint, config)
}
