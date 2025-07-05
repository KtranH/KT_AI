import apiClient from '../../config/apiConfig'

// === IMAGE JOBS API (ComfyUI) ===
export const imageJobsAPI = {
  createJob: (formData) => apiClient.post('/image-jobs/create', formData, {
    headers: {
      'Content-Type': 'multipart/form-data'
    }
  }),
  getActiveJobs: () => apiClient.get('/image-jobs/active'),
  getCompletedJobs: (page = 1, perPage = 10) => apiClient.get('/image-jobs/completed',
    {
      params: {
        paginate: true,
        per_page: perPage,
        page: page
      }
    }
  ),
  getFailedJobs: (page = 1, perPage = 10) => apiClient.get('/image-jobs/failed',
    {
      params: {
        paginate: true,
        per_page: perPage,
        page: page
      }
    }
  ),
  checkJobStatus: (jobId) => apiClient.get(`/image-jobs/${jobId}`),
  cancelJob: (jobId) => apiClient.delete(`/image-jobs/${jobId}`),
  retryJob: (jobId) => apiClient.post(`/image-jobs/${jobId}/retry`),
}

// Có thể gọi comfyui hoặc imageJobsAPI
export const comfyuiAPI = imageJobsAPI 