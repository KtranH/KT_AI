<template>
  <div 
    class="image-uploader p-8 border-2 border-dashed border-indigo-400 bg-indigo-50 rounded-xl text-center cursor-pointer transition duration-300 hover:border-indigo-600 hover:bg-indigo-100 relative overflow-hidden" 
    @dragover.prevent="dragOver" 
    @dragleave.prevent="dragLeave" 
    @drop.prevent="drop"
    :class="{'border-indigo-600 bg-indigo-100': isDragging}"
  >
    <input type="file" multiple accept="image/*" @change="onFileChange" ref="fileInput" class="hidden" />
    <div v-if="!isDragging" @click="triggerFileInput" class="py-8">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto text-indigo-500 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
      </svg>
      <h3 class="text-lg font-semibold text-indigo-700 mb-2">Kéo thả hình ảnh vào đây</h3>
      <p class="text-sm text-indigo-600">hoặc nhấp để chọn file từ máy</p>
      <p class="mt-4 text-xs text-gray-500">Đã tải lên: {{ files.length }}/5 ảnh (tối đa 5 ảnh, mỗi ảnh tối đa 2MB)</p>
    </div>
    <div v-else class="py-12">
      <div class="text-indigo-700 font-semibold text-xl mb-2">Thả hình ảnh vào đây nào!</div>
      <p class="text-sm text-indigo-600">Chúng tôi sẵn sàng nhận ảnh của bạn</p>
    </div>
    <div v-if="uploadError" class="text-red-500 text-sm mt-2 font-semibold">{{ uploadError }}</div>
  </div>
  
  <h3 class="font-semibold text-lg text-gray-700 mt-8 mb-4" v-if="files.length > 0">
    Ảnh đã tải lên ({{ files.length }}/5)
  </h3>
  
  <div class="image-preview grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-4">
    <div v-for="(file, index) in files" :key="index" class="image-item relative group">
      <div class="rounded-lg overflow-hidden shadow-md bg-white p-2 transition-all duration-300 transform group-hover:shadow-lg group-hover:-translate-y-1">
        <img 
          :src="file.url" 
          alt="Preview" 
          class="w-full h-32 object-cover rounded-md transition duration-300" 
        />
        <div class="mt-2 text-xs text-gray-500 truncate px-1">{{ file.file.name }}</div>
      </div>
      <button 
        @click.stop="removeFile(index)" 
        class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-7 h-7 flex items-center justify-center shadow-lg hover:bg-red-600 transition duration-200 transform hover:scale-110"
        title="Xóa ảnh này"
      >
        ×
      </button>
    </div>
  </div>
</template>

<script>
import { ref, inject } from 'vue';
import { isFileImage, isImageSizeValid } from '@/utils';
import { toast } from 'vue-sonner'

export default {
  setup() {
    // Lấy instance từ component cha
    const imageUploadInstance = inject('imageUploadInstance');
    const { files, addFiles, removeFile } = imageUploadInstance;
    
    const isDragging = ref(false);
    const fileInput = ref(null);
    const uploadError = ref('');
    
    const toastError = (message) => {
      // Hiển thị thông báo lỗi
      toast.error('Có lỗi xảy ra ' + message, {
                    duration: 3000,            
                    position: 'bottom-right'
                })
    };

    const validateFiles = (fileList) => {
      uploadError.value = '';
      
      // Kiểm tra số lượng
      if (files.value.length + fileList.length > 5) {
        uploadError.value = 'Bạn chỉ có thể tải lên tối đa 5 hình ảnh.';
        toastError(uploadError.value);
        return false;
      }
      
      // Kiểm tra định dạng và kích thước
      for (let i = 0; i < fileList.length; i++) {
        const file = fileList[i];
        
        // Kiểm tra định dạng
        if (!isFileImage(file)) {
          uploadError.value = 'Chỉ chấp nhận tệp hình ảnh.';
          toastError(uploadError.value);
          return false;
        }
        
        // Kiểm tra kích thước (2MB = 2 * 1024 * 1024 bytes)
        if (!isImageSizeValid(file)) {
          uploadError.value = 'Mỗi ảnh không được vượt quá 2MB.';
          toastError(uploadError.value);
          return false;
        }
      }
      
      return true;
    };
    
    const dragOver = () => { isDragging.value = true; };
    const dragLeave = () => { isDragging.value = false; };
    const drop = (event) => {
      isDragging.value = false;
      const droppedFiles = event.dataTransfer.files;
      
      if (validateFiles(droppedFiles)) {
        addFiles(droppedFiles);
      }
    };
    
    const triggerFileInput = () => { fileInput.value.click(); };
    const onFileChange = (event) => { 
      const selectedFiles = event.target.files;
      if (validateFiles(selectedFiles)) {
        addFiles(selectedFiles);
      }
      // Reset input để có thể chọn lại cùng file nếu cần
      event.target.value = '';
    };
    
    return {
      files,
      removeFile,
      isDragging,
      fileInput,
      uploadError,
      dragOver,
      dragLeave,
      drop,
      triggerFileInput,
      onFileChange
    };
  }
}
</script>