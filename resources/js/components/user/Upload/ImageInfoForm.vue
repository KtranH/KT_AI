<template>
  <div class="bg-white p-6 rounded-xl shadow-lg border border-gray-100">
    <div class="flex items-center justify-between mb-6">
      <h2 class="text-xl font-bold bg-gradient-text-v2 pb-3 border-b border-gray-100">
        Tải ảnh cho chức năng: {{ featureName }}
      </h2>
      <img :src="sticker" alt="Sticker" class="w-12 h-12 rounded-full ml-4" />
    </div>
    
    <form @submit.prevent="handleUpload(featureId)" class="space-y-6">
      <div>
        <label for="title" class="block text-sm font-semibold text-gray-800">Tiêu đề:</label>
        <span
            :class="{
              'text-gray-500': title.length <= 20,
              'text-yellow-500': title.length > 20 && title.length <= 30,
              'text-red-500': title.length > 30
            }"
            class="text-xs">
            {{ title.length }}/30
        </span>
        <input 
          type="text" 
          id="title" 
          v-model="title" 
          required 
          maxlength="30"
          placeholder="Nhập tiêu đề cho hình ảnh của bạn"
          class="mt-2 block w-full border border-gray-200 rounded-lg p-3 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition duration-200" 
        />
      </div>
      
      <div>
        <label for="description" class="block text-sm font-semibold text-gray-800">Mô tả:</label>
        <span
            :class="{
              'text-gray-500': description.length <= 200,
              'text-yellow-500': description.length > 200 && description.length <= 255,
              'text-red-500': description.length > 255
            }"
            class="text-xs">
            {{ description.length }}/255
        </span>
        <textarea 
          id="description" 
          v-model="description" 
          maxlength="255"
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
          class="w-full bg-gradient-text text-white py-3 rounded-lg font-semibold hover:bg-indigo-700 transition duration-300 disabled:bg-gray-400 disabled:cursor-not-allowed flex items-center justify-center"
        >
          <svg v-if="isSubmitting" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          <span><i class="fa-solid fa-image mr-2"></i>{{ isSubmitting ? 'Đang xử lý...' : 'Tải lên hình ảnh' }}</span>
          <ConfirmUpload ref="uploadRef" />
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
import ConfirmUpload from '@/components/common/ConfirmUpload.vue'
import { ref, inject, computed } from 'vue'
import { toast } from 'vue-sonner'
import { encodedID } from '@/utils'
import { useImageStore } from '@/stores/user/imagesStore'
import { useRouter } from 'vue-router'
import { isTextLengthValid } from '@/utils'

export default {
  components: {
    ConfirmUpload,
  },
  props: {
    featureId: {
      type: [Number, String, null],
      required: false,
      default: null
    },
    featureName: {
      type: [String, null],
      required: false,
      default: null
    }
  },
  setup(props) {
    // Lấy instance từ component cha
    const imageUploadInstance = inject('imageUploadInstance')
    const { files, uploadImages } = imageUploadInstance
    const sticker = ref("/img/upload.png")
    const title = ref('')
    const description = ref('')
    const isSubmitting = ref(false)
    const successMessage = ref('')
    const errorMessage = ref('')
    const { featureId, featureName } = props
    const uploadRef = ref(null)
    const storeImg = useImageStore()
    const router = useRouter()

    // Kiểm tra độ dài của title và prompt
    const isTitleValid = computed(() => isTextLengthValid(title.value, 30))
    const isPromptValid = computed(() => isTextLengthValid(description.value, 255))

    const calculateTotalSize = () => {
      const totalBytes = files.value.reduce((total, fileObj) => total + fileObj.file.size, 0)
      
      // Chuyển đổi sang đơn vị thích hợp
      if (totalBytes < 1024) {
        return `${totalBytes} bytes`
      } else if (totalBytes < 1024 * 1024) {
        return `${(totalBytes / 1024).toFixed(2)} KB`
      } else {
        return `${(totalBytes / (1024 * 1024)).toFixed(2)} MB`
      }
    }

    // Thông báo thành công
    const toastSuccess = (message) => {
      toast.success(message, {
        duration: 3000,
        position: 'bottom-right'
      })
    }
    // Thông báo thất bại
    const toastError = (message) => {
      toast.error(message, {
        duration: 3000,
        position: 'bottom-right'
      })
    }

    const handleUpload = async (featureId) => {      
      if (!isTitleValid.value) {
        toast.error('Tiêu đề không được vượt quá 30 ký tự')
        return
      }

      if (!isPromptValid.value) {
        toast.error('Mô tả không được vượt quá 255 ký tự')
        return
      }
      const result = await uploadRef.value.showAlert()
      if (result.isConfirmed) {
        isSubmitting.value = true
        successMessage.value = ''
        errorMessage.value = ''
        try {
          const result = await uploadImages(featureId, title.value, description.value)
          if (result) {
            storeImg.fetchImagesCreatedByUser()
            successMessage.value = 'Tải lên thành công'
            toastSuccess(successMessage.value)
            setTimeout(() => {
              router.push(`/createimage/${encodedID(featureId)}`)
            }, 1000)
          }
        } catch (error) {
          errorMessage.value = 'Tải lên thất bại'
          toastError(errorMessage.value)
        } finally {
          isSubmitting.value = false
        }
      }
    }

    return {
      files,
      title,
      description,
      isSubmitting,
      successMessage,
      errorMessage,
      calculateTotalSize,
      featureId,
      featureName,
      sticker,
      handleUpload,
      uploadRef
    };
  }
}
</script>