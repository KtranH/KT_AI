import apiClient from '../../config/apiConfig'

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