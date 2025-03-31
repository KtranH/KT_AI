import apiClient from '../config/apiConfig'

// Các gọi API xác thực
export const authAPI = {
  login: (credentials) => apiClient.post('/login', credentials),
  logout: () => apiClient.post('/logout'),
  check: () => apiClient.get('/check'),
  
}

// Các gọi API xác minh người dùng
export const verificationAPI = {
  resendCode: (email) => apiClient.post('/resend-verification', { email })
}

// Các gọi API thông tin hình ảnh
export const imageAPI = {
  getImages: (id) => apiClient.get(`/get_images_information/${id}`),
  getImagesCreatedByUser: () => apiClient.get('/get_images_created_by_user'),
  getImagesByFeature: (id, page = 1) => apiClient.get(`/get_images_by_feature/${id}?page=${page}`),
  getLikes: (id) => apiClient.get(`/get_likes_information/${id}`),
  likePost: (id) => apiClient.post(`/like_post/${id}`),
  unlikePost: (id) => apiClient.post(`/unlike_post/${id}`)
}

// Gọi API tải lên hình ảnh
export const imageUploadAPI = {
  store: (formData, featureId) => apiClient.post(`/upload_images/${featureId}`, formData, {
    headers: {
      'Content-Type': 'multipart/form-data'
    }
  })
}

// Các gọi API Google
export const googleAPI = {
  getAuthUrl: () => apiClient.get('/auth/google/url'),
  callback: (code) => apiClient.get(`/auth/google/callback?code=${code}`)
}

// Các gọi API Turnstile
export const turnstileAPI = {
  getConfig: () => apiClient.get('/turnstile/config')
}

// Các gọi API tính năng
export const featuresAPI = {
  getAll: () => apiClient.get('/load_features'),
  getById: (id) => apiClient.get(`/load_features/${id}`),
}

// Các gọi API thích
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

// Các gọi API bình luận
export const commentAPI = {
  getComments: (imageId, page = 1, commentId = null) => {
    let url = `/images/${imageId}/comments?page=${page}`
    if (commentId) url += `&comment_id=${commentId}`
    return apiClient.get(url)
  },
  createComment: (commentData) => apiClient.post('/comments', commentData),
  updateComment: (commentId, content) => apiClient.put(`/comments/${commentId}`, { content }),
  deleteComment: (commentId) => apiClient.delete(`/comments/${commentId}`),
  toggleLike: (commentId) => apiClient.post(`/comments/${commentId}/toggle-like`)
}

// API tổng quát - cho các endpoint khác
export const genericAPI = {
  get: (endpoint, config) => apiClient.get(endpoint, config),
  post: (endpoint, data, config) => apiClient.post(endpoint, data, config),
  put: (endpoint, data, config) => apiClient.put(endpoint, data, config),
  delete: (endpoint, config) => apiClient.delete(endpoint, config)
}
