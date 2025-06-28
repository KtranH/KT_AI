import apiClient from '../../config/apiConfig'

// === STATISTICS API ===
export const statisticsAPI = {
  getStatistics: () => apiClient.get('/statistics'),
} 