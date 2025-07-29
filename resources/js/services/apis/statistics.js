import apiClient from '../../config/apiConfig'

// === STATISTICS API ===
export const statisticsApi = {
  getStatistics: () => apiClient.get('/statistics'),
  getUserStatistics: () => apiClient.get('/user-statistics'),
} 