<template>
  <div v-if="isVisible" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
      <!-- Background overlay -->
      <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="closeModal"></div>

      <!-- Modal panel -->
      <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
          <div class="sm:flex sm:items-start">
            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 sm:mx-0 sm:h-10 sm:w-10">
              <!-- Icon -->
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
              </svg>
            </div>
            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
              <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                {{ title }}
              </h3>
              <div class="mt-4">
                <!-- Upload area -->
                <div 
                  class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:bg-gray-50 transition cursor-pointer"
                  @click="triggerFileInput"
                  @dragover.prevent="isDragging = true"
                  @dragleave.prevent="isDragging = false"
                  @drop.prevent="handleFileDrop"
                  :class="{ 'border-blue-500 bg-blue-50': isDragging }"
                >
                  <input 
                    type="file" 
                    ref="fileInput" 
                    class="hidden" 
                    accept="image/*" 
                    @change="handleFileChange"
                  >
                  
                  <div v-if="!previewImage">
                    <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                    </svg>
                    <p class="mt-2 text-sm text-gray-600">
                      Kéo và thả ảnh vào đây hoặc <span class="text-blue-500 font-medium">chọn ảnh</span>
                    </p>
                    <p class="mt-1 text-xs text-gray-500">
                      PNG, JPG, GIF tối đa 2MB
                    </p>
                  </div>
                  
                  <div v-else class="relative">
                    <img 
                      :src="previewImage" 
                      :class="[
                        'mx-auto object-cover transition-all duration-300',
                        type === 'avatar' ? 'h-40 w-40 rounded-full' : 'h-full w-full rounded-lg'
                      ]"
                      alt="Preview"
                    >
                    <button 
                      @click.stop="removeImage" 
                      class="absolute top-0 right-0 bg-red-500 text-white rounded-full p-1 shadow-lg hover:bg-red-600 transition"
                    >
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                      </svg>
                    </button>
                  </div>
                </div>
                
                <!-- Error message -->
                <p v-if="errorMessage" class="mt-2 text-sm text-red-600">
                  {{ errorMessage }}
                </p>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Modal actions -->
        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
          <button 
            type="button" 
            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-gradient-text from-purple-600 to-indigo-600 text-base font-medium text-white hover:from-purple-700 hover:to-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 sm:ml-3 sm:w-auto sm:text-sm"
            :disabled="!previewImage || isUploading"
            @click="uploadImage"
          >
            <span v-if="isUploading" class="flex items-center">
              <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              Đang tải lên...
            </span>
            <span v-else>Cập nhật</span>
          </button>
          <button 
            type="button" 
            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
            @click="closeModal"
            :disabled="isUploading"
          >
            Hủy
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, watch } from 'vue'
import { toast } from 'vue-sonner'
import { isFileImage, isImageSizeValid } from '@/utils'

export default {
  name: 'UploadImageModal',
  props: {
    isVisible: {
      type: Boolean,
      default: false
    },
    type: {
      type: String,
      required: true,
      validator: (value) => ['avatar', 'cover'].includes(value)
    }
  },
  emits: ['close', 'upload-success'],
  setup(props, { emit }) {
    const fileInput = ref(null)
    const previewImage = ref(null)
    const selectedFile = ref(null)
    const isDragging = ref(false)
    const errorMessage = ref('')
    const isUploading = ref(false)
    
    // Computed title based on type
    const title = props.type === 'avatar' 
      ? 'Cập nhật ảnh đại diện' 
      : 'Cập nhật ảnh bìa'
    
    // Reset state when modal is closed
    watch(() => props.isVisible, (newValue) => {
      if (!newValue) {
        previewImage.value = null
        selectedFile.value = null
        errorMessage.value = ''
        isDragging.value = false
      }
    })
    
    // Trigger file input click
    const triggerFileInput = () => {
      if (!isUploading.value) {
        fileInput.value.click()
      }
    }
    
    // Handle file selection
    const handleFileChange = (event) => {
      const file = event.target.files[0]
      processFile(file)
    }
    
    // Handle file drop
    const handleFileDrop = (event) => {
      isDragging.value = false
      const file = event.dataTransfer.files[0]
      processFile(file)
    }
    
    // Process and validate file
    const processFile = (file) => {
      if (!file) return
      
      // Validate file type
      if (!isFileImage(file)) {
        errorMessage.value = 'Chỉ chấp nhận file ảnh (JPG, PNG)'
        return
      }
      
      // Validate file size (2MB max)
      if (!isImageSizeValid(file)) {
        errorMessage.value = 'Kích thước file không được vượt quá 2MB'
        return
      }
      
      // Clear error and set preview
      errorMessage.value = ''
      selectedFile.value = file
      
      // Create preview URL
      const reader = new FileReader()
      reader.onload = (e) => {
        previewImage.value = e.target.result
      }
      reader.readAsDataURL(file)
    }
    
    // Remove selected image
    const removeImage = (e) => {
      e.stopPropagation()
      previewImage.value = null
      selectedFile.value = null
      if (fileInput.value) {
        fileInput.value.value = ''
      }
    }
    
    // Close modal
    const closeModal = () => {
      if (!isUploading.value) {
        emit('close')
      }
    }
    
    // Upload image
    const uploadImage = async () => {
      if (!selectedFile.value) return
      
      isUploading.value = true
      
      try {
        // Emit success event with the file data
        emit('upload-success', {
          type: props.type,
          file: selectedFile.value,
          previewUrl: previewImage.value
        })
        // Close modal
        emit('close')
      } catch (error) {
        console.error('Upload error:', error)
        errorMessage.value = 'Có lỗi xảy ra khi tải lên ảnh. Vui lòng thử lại.'
        toast.error('Không thể cập nhật ảnh. Vui lòng thử lại sau.')
      } finally {
        isUploading.value = false
      }
    }
    
    return {
      fileInput,
      previewImage,
      isDragging,
      errorMessage,
      isUploading,
      title,
      triggerFileInput,
      handleFileChange,
      handleFileDrop,
      removeImage,
      closeModal,
      uploadImage
    }
  }
}
</script>

<style scoped>
/* Add any additional styling here */
</style>
