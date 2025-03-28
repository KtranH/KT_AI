<template>
  <div class="bg-white p-6 rounded-xl shadow-lg border border-gray-100">
    <h2 class="text-xl font-bold bg-gradient-text-v2 mb-6 pb-3 border-b border-gray-100">Thông tin hình ảnh cho chức năng ... </h2>
    
    <form @submit.prevent="submitForm" class="space-y-6">
      <div>
        <label for="title" class="block text-sm font-semibold text-gray-800">Tiêu đề:</label>
        <input 
          type="text" 
          id="title" 
          v-model="title" 
          required 
          placeholder="Nhập tiêu đề cho hình ảnh của bạn"
          class="mt-2 block w-full border border-gray-200 rounded-lg p-3 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition duration-200" 
        />
      </div>
      
      <div>
        <label for="description" class="block text-sm font-semibold text-gray-800">Mô tả:</label>
        <textarea 
          id="description" 
          v-model="description" 
          placeholder="Mô tả thêm về hình ảnh của bạn"
          class="mt-2 block w-full border border-gray-200 rounded-lg p-3 h-32 resize-y focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition duration-200"
        ></textarea>
      </div>
      
      <div class="p-4 bg-gray-50 rounded-lg">
        <h3 class="text-sm font-semibold text-gray-700 mb-2">Tổng quan</h3>
        <div class="flex items-center justify-between text-sm text-gray-600 mb-2">
          <span>Số lượng ảnh:</span>
          <span class="font-medium" :class="{'text-red-500': files.length === 0}">
            {{ files.length }}/5
          </span>
        </div>
        <div v-if="files.length === 0" class="text-red-500 text-xs italic mb-2">
          Vui lòng tải lên ít nhất một hình ảnh
        </div>
        <div v-if="files.length > 0" class="text-xs text-gray-500">
          Tổng dung lượng: {{ calculateTotalSize() }}
        </div>
      </div>
      
      <div class="uploaded-images-preview" v-if="files.length > 0">
        <h3 class="text-sm font-semibold text-gray-700 mb-2">Hình ảnh đã tải lên:</h3>
        <div class="grid grid-cols-5 gap-2">
          <div v-for="(file, index) in files" :key="index" class="relative">
            <img :src="file.url" alt="Thumbnail" class="w-full h-full object-cover rounded-md" />
          </div>
        </div>
      </div>
      
      <div class="pt-4">
        <button 
          type="submit" 
          :disabled="isSubmitting || files.length === 0" 
          class="w-full bg-indigo-600 text-white py-3 rounded-lg font-semibold hover:bg-indigo-700 transition duration-300 disabled:bg-gray-400 disabled:cursor-not-allowed flex items-center justify-center"
        >
          <svg v-if="isSubmitting" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          <span>{{ isSubmitting ? 'Đang xử lý...' : 'Lưu hình ảnh' }}</span>
        </button>
      </div>
      
      <div v-if="successMessage" class="mt-4 p-3 bg-green-100 text-green-700 rounded-lg text-sm">
        {{ successMessage }}
      </div>
      <div v-if="errorMessage" class="mt-4 p-3 bg-red-100 text-red-700 rounded-lg text-sm">
        {{ errorMessage }}
      </div>
    </form>
  </div>
</template>

<script>
import { ref, inject } from 'vue';

export default {
  setup() {
    // Lấy instance từ component cha
    const imageUploadInstance = inject('imageUploadInstance');
    const { files, clearAllFiles } = imageUploadInstance;
    
    const title = ref('');
    const description = ref('');
    const isSubmitting = ref(false);
    const successMessage = ref('');
    const errorMessage = ref('');
    
    const calculateTotalSize = () => {
      const totalBytes = files.value.reduce((total, fileObj) => total + fileObj.file.size, 0);
      
      // Chuyển đổi sang đơn vị thích hợp
      if (totalBytes < 1024) {
        return `${totalBytes} bytes`;
      } else if (totalBytes < 1024 * 1024) {
        return `${(totalBytes / 1024).toFixed(2)} KB`;
      } else {
        return `${(totalBytes / (1024 * 1024)).toFixed(2)} MB`;
      }
    };
    
    const submitForm = async () => {
      if (files.value.length === 0) {
        errorMessage.value = 'Vui lòng tải lên ít nhất một hình ảnh';
        return;
      }
      
      errorMessage.value = '';
      successMessage.value = '';
      isSubmitting.value = true;
      
      const formData = new FormData();
      formData.append('title', title.value);
      formData.append('description', description.value);
      files.value.forEach((fileObj, index) => {
        formData.append(`images[${index}]`, fileObj.file);
      });
    
      try {
        // Giả lập API call vì đây chỉ là mock-up
        await new Promise(resolve => setTimeout(resolve, 1500));
        
        // Giả lập response thành công
        console.log('Dữ liệu đã gửi:', {
          title: title.value,
          description: description.value,
          totalImages: files.value.length
        });
        
        successMessage.value = 'Tải lên thành công! Hình ảnh của bạn đã được lưu.';
        title.value = '';
        description.value = '';
        // Reset files array bằng clearAllFiles từ instance
        clearAllFiles();
      } catch (error) {
        console.error('Lỗi:', error);
        errorMessage.value = 'Có lỗi xảy ra khi tải lên. Vui lòng thử lại sau.';
      } finally {
        isSubmitting.value = false;
      }
    };
    
    return {
      files,
      title,
      description,
      isSubmitting,
      successMessage,
      errorMessage,
      calculateTotalSize,
      submitForm
    };
  }
}
</script>
<style scoped>
/* Gradient text effect */
.bg-gradient-text-v2 {
  background: linear-gradient(
    -45deg,
    #3b82f6,
    #6366f1,
    #8b5cf6,
    #ec4899,
    #3b82f6
  );
  background-size: 400%;
  animation: gradient-animation 8s ease infinite;

  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}
.transition-all {
  transition-property: all;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
  transition-duration: 150ms;
}
@keyframes gradient-animation {
  0% {
    background-position: 0% 50%;
  }
  50% {
    background-position: 100% 50%;
  }
  100% {
    background-position: 0% 50%;
  }
}
</style>