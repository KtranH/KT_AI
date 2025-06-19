import { ref, computed } from 'vue'
import { imageUploadAPI } from '@/services/api'

export function useImageUpload() {
  const files = ref([])
  const uploadErrors = ref([])

  // Tính toán các thuộc tính
  const totalFiles = computed(() => files.value.length)
  const remainingSlots = computed(() => 5 - files.value.length)
  const totalSize = computed(() => {
    return files.value.reduce((total, fileObj) => total + fileObj.file.size, 0)
  })
  
  const isLimitReached = computed(() => files.value.length >= 5)

  // Thêm nhiều file
  const addFiles = (newFiles) => {
    uploadErrors.value = []
    
    // Kiểm tra giới hạn và lấy chỉ số lượng tối đa có thể thêm
    const availableSlots = 5 - files.value.length;
    if (availableSlots <= 0) {
      uploadErrors.value.push('Đã đạt giới hạn tối đa 5 ảnh.')
      return
    }
    
    const filesToAdd = Array.from(newFiles).slice(0, availableSlots)
    
    // Hiển thị cảnh báo nếu số lượng vượt quá
    if (filesToAdd.length < newFiles.length) {
      uploadErrors.value.push(`Chỉ thêm được ${filesToAdd.length}/${newFiles.length} ảnh do giới hạn tối đa 5 ảnh.`)
    }
    
    // Thêm các file hợp lệ
    filesToAdd.forEach(file => {
      // Kiểm tra kích thước tệp
      if (file.size > 2 * 1024 * 1024) {
        uploadErrors.value.push(`Ảnh "${file.name}" vượt quá 2MB.`)
        return
      }
      
      // Kiểm tra định dạng
      if (!file.type.startsWith('image/')) {
        uploadErrors.value.push(`Tệp "${file.name}" không phải là ảnh.`)
        return
      }
      
      const url = URL.createObjectURL(file)
      files.value.push({ 
        file, 
        url,
        id: `file-${Date.now()}-${Math.random().toString(36).substring(2, 9)}`,
        name: file.name,
        size: file.size,
        type: file.type,
        lastModified: file.lastModified
      })
    })
  }

  // Xóa một file
  const removeFile = (index) => {
    if (index >= 0 && index < files.value.length) {
      URL.revokeObjectURL(files.value[index].url)
      files.value.splice(index, 1)
    }
  }
  
  // Xóa file theo id
  const removeFileById = (fileId) => {
    const index = files.value.findIndex(f => f.id === fileId);
    if (index !== -1) {
      removeFile(index)
    }
  }
  
  // Xóa tất cả file
  const clearAllFiles = () => {
    files.value.forEach(fileObj => {
      URL.revokeObjectURL(fileObj.url)
    })
    files.value = []
  }
  
  // Format kích thước file
  const formatFileSize = (bytes) => {
    if (bytes < 1024) return `${bytes} bytes`
    if (bytes < 1024 * 1024) return `${(bytes / 1024).toFixed(1)} KB`
    return `${(bytes / (1024 * 1024)).toFixed(1)} MB`
  }

  // Gửi API tải lên
  const uploadImages = async (featureId, title, description) => {
    if (files.value.length === 0) {
      uploadErrors.value.push('Vui lòng tải lên ít nhất một ảnh.')
      return
    }
    
    const formData = new FormData()
    formData.append('title', title)
    formData.append('description', description)
    files.value.forEach((fileObj, index) => {
      formData.append(`images[${index}]`, fileObj.file)
    })
    
    try {
      const response = await imageUploadAPI.store(formData, featureId)
      if (response.data.success) {
        clearAllFiles()
        return true
      }
      return false
    } catch (error) {
      console.error('Lỗi tải lên:', error)
      uploadErrors.value.push('Lỗi tải lên, vui lòng thử lại.')
      return false
    }
  }
  
  return {
    files,
    addFiles,
    removeFile,
    removeFileById,
    clearAllFiles,
    uploadErrors,
    totalFiles,
    remainingSlots,
    totalSize,
    isLimitReached,
    formatFileSize,
    uploadImages
  };
}