import apiClient from '../../config/apiConfig'

// === IMAGES API ===
export const imageAPI = {
  // Public Routes
  getImages: (id) => apiClient.get(`/get_images_information/${id}`),
  getImagesByFeature: (id, page = 1, sort = 'newest') => apiClient.get(`/get_images_by_feature/${id}?page=${page}&sort=${sort}`),
  
  // Protected Routes
  getImagesUploaded: (userId, perPage = 10) => {
    const params = { per_page: perPage };
    if (userId !== null && userId !== undefined) {
      params.user_id = userId;
    }
    return apiClient.get('/get_images_uploaded', { params });
  },
  getImagesLiked: (userId, perPage = 10) => {
    const params = { per_page: perPage };
    if (userId !== null && userId !== undefined) {
      params.user_id = userId;
    }
    return apiClient.get('/get_images_liked', { params });
  },
  getImagesLikedPage: (url) => apiClient.get(url),
  getImagesUploadedPage: (url) => apiClient.get(url),
  checkForNewImages: (userId) => {
    const params = userId !== null && userId !== undefined ? { user_id: userId } : {};
    return apiClient.get('/check_new_images', { params });
  },
  
  // Image Management
  delete: (id) => apiClient.delete(`/images/${id}`),
  update: (id, data) => apiClient.patch(`/images/${id}`, data),
}

// === IMAGE UPLOAD API ===
export const imageUploadAPI = {
  store: (formData, featureId) => apiClient.post(`/upload_images/${featureId}`, formData, {
    headers: {
      'Content-Type': 'multipart/form-data'
    }
  })
} 