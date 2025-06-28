<template>
  <div>
    <h2 class="text-xl font-semibold text-gray-700 mb-6">{{ title }}</h2>   
    <div
      class="flex-1 border-2 border-dashed border-gray-300 rounded-lg flex flex-col items-center justify-center p-6 transition-all duration-150"
      :class="{ 'border-indigo-400 bg-indigo-50': isDragging }"
      @dragover.prevent="isDragging = true"
      @dragleave.prevent="isDragging = false"
      @drop.prevent="onDrop">
        <div v-if="imagePreview" class="w-full h-full flex items-center justify-center">
          <img :src="imagePreview" alt="Preview" class="max-w-full max-h-full rounded-lg shadow-md" />
        </div>
        <div v-else class="text-center">
          <svg class="mx-auto h-16 w-16 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
          </svg>
              
          <h3 class="mt-2 text-sm font-medium text-gray-700">
            Kéo thả hình ảnh vào đây
          </h3>
          <p class="mt-1 text-xs text-gray-500">PNG, JPG hoặc GIF (tối đa 2MB)</p>
          <div class="mt-6">
            <label
              :for="'file-upload-' + uniqueId"
              class="cursor-pointer inline-flex items-center px-4 py-2 border border-indigo-500 rounded-md text-sm font-medium text-indigo-600 bg-white hover:bg-indigo-50"
            >
              <span>Chọn hình ảnh</span>
              <input :id="'file-upload-' + uniqueId" type="file" class="sr-only" accept="image/*" @change="onFileChange" />
            </label>
          </div>
        </div>
    </div>     
    <div v-if="imagePreview" class="flex justify-between mt-4">
      <button
        class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
        @click="clearImage"
      >
        <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
        </svg>
        Xóa
      </button>
    </div>
  </div>
</template>

<script>
import { defineComponent, ref, watch } from 'vue';
import { isFileImage, isImageSizeValid } from '@/utils/index';

export default defineComponent({
  name: 'ImageUploader',
  props: {
    title: {
      type: String,
      default: 'Xem trước & Tải lên ảnh'
    },
    imageValue: {
      type: [String, File],
      default: null
    }
  },
  setup(props, { emit }) {
    // Local state
    const isDragging = ref(false);
    const imagePreview = ref(null);
    // Tạo ID duy nhất cho component
    const uniqueId = ref(`upload-${Date.now()}-${Math.random().toString(36).substring(2, 9)}`);
    
    // Methods
    const onDrop = (event) => {
      isDragging.value = false;
      const files = event.dataTransfer.files;
      
      if (files.length) {
        handleFile(files[0]);
      }
    };
    
    const onFileChange = (event) => {
      const file = event.target.files[0];
      if (file) {
        handleFile(file);
      }
    };
    
    const handleFile = (file) => {
      if (isFileImage(file)) {
        if(isImageSizeValid(file)) {
            const reader = new FileReader();
            reader.onload = (e) => {
                // Lưu URL hình ảnh để xem trước
                imagePreview.value = e.target.result;
                // Gửi đối tượng File về component cha để gửi API
                emit('update:image', file);
            };
            reader.readAsDataURL(file);
        }
        else {
          alert('Vui lòng chỉ tải lên hình ảnh dưới 2MB');
        }
      }
      else {
        alert('Vui lòng chỉ tải lên hình ảnh');
      }
    };
    
    const clearImage = () => {
      imagePreview.value = null;
      emit('update:image', null);
    };
    
    // Watchers
    watch(() => props.imageValue, (val) => {
      if (val instanceof File) {
        // Nếu đã có file, tạo lại preview
        const reader = new FileReader();
        reader.onload = (e) => {
          imagePreview.value = e.target.result;
        };
        reader.readAsDataURL(val);
      } else if (typeof val === 'string') {
        // Nếu là URL hoặc base64, hiển thị trực tiếp
        imagePreview.value = val;
      } else {
        imagePreview.value = null;
      }
    }, { immediate: true });
    
    return {
      isDragging,
      imagePreview,
      onDrop,
      onFileChange,
      clearImage,
      uniqueId
    };
  }
})
</script> 