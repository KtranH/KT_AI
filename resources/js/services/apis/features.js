import apiClient from '../../config/apiConfig'

// === FEATURES API ===
export const featuresAPI = {
  getAll: () => apiClient.get('/load_features'),
  getById: (id) => apiClient.get(`/load_features/${id}`),
} 