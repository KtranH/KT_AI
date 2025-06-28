import apiClient from '../../config/apiConfig'

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