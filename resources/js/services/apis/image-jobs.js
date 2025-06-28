import apiClient from '../../config/apiConfig'

// === IMAGE JOBS API (ComfyUI) ===
export const imageJobsAPI = {
  createJob: (formData) => apiClient.post('/image-jobs/create', formData, {
    headers: {
      'Content-Type': 'multipart/form-data'
    }
  }),
  getActiveJobs: () => apiClient.get('/image-jobs/active'),
  getCompletedJobs: () => apiClient.get('/image-jobs/completed'),
  getFailedJobs: () => apiClient.get('/image-jobs/failed'),
  checkJobStatus: (jobId) => apiClient.get(`/image-jobs/${jobId}`),
  cancelJob: (jobId) => apiClient.delete(`/image-jobs/${jobId}`),
  retryJob: (jobId) => apiClient.post(`/image-jobs/${jobId}/retry`),
}

// === BACKWARD COMPATIBILITY ===
export const comfyuiAPI = imageJobsAPI // Alias for backward compatibility 