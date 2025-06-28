import apiClient from '../../config/apiConfig'

// === PROXY API ===
export const proxyAPI = {
  getR2Image: (url) => apiClient.get(`/proxy/r2-image?url=${encodeURIComponent(url)}`),
  downloadR2Image: (url, filename) => apiClient.get(`/download/r2-image?url=${encodeURIComponent(url)}&filename=${encodeURIComponent(filename)}`),
  downloadFromR2Storage: (url, filename) => apiClient.get(`/r2-download?url=${encodeURIComponent(url)}&filename=${encodeURIComponent(filename)}`),
} 