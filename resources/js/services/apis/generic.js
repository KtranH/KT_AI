import apiClient from '../../config/apiConfig'

// === GENERIC API ===
export const genericAPI = {
  get: (endpoint, config) => apiClient.get(endpoint, config),
  post: (endpoint, data, config) => apiClient.post(endpoint, data, config),
  put: (endpoint, data, config) => apiClient.put(endpoint, data, config),
  patch: (endpoint, data, config) => apiClient.patch(endpoint, data, config),
  delete: (endpoint, config) => apiClient.delete(endpoint, config)
} 