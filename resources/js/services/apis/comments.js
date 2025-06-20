import apiClient from '../../config/apiConfig'

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