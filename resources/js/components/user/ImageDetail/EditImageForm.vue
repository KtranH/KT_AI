<template>
  <div class="p-4 bg-white rounded-lg shadow">
    <h2 class="text-lg font-semibold mb-4">Chỉnh sửa bài viết</h2>
    
    <form @submit.prevent="handleSubmit">
      <div class="mb-4">
        <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Tiêu đề</label>
        <input
          type="text"
          id="title"
          v-model="formData.title"
          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          placeholder="Nhập tiêu đề bài viết"
          required
        />
      </div>
      
      <div class="mb-4">
        <label for="prompt" class="block text-sm font-medium text-gray-700 mb-1">Mô tả</label>
        <textarea
          id="prompt"
          v-model="formData.prompt"
          rows="4"
          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          placeholder="Nhập mô tả bài viết"
          required
        ></textarea>
      </div>
      
      <div class="flex justify-end space-x-2">
        <button
          type="button"
          @click="$emit('cancel')"
          class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500"
        >
          Hủy
        </button>
        <button
          type="submit"
          class="px-4 py-2 bg-gradient-text text-white rounded-md hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500"
          :disabled="isSubmitting"
        >
          {{ isSubmitting ? 'Đang xử lý...' : 'Lưu thay đổi' }}
        </button>
      </div>
    </form>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue'
import useImage from '@/composables/user/useImage'

export default {
  name: 'EditImageForm',
  props: {
    imageData: {
      type: Object,
      required: true
    }
  },
  emits: ['update:success', 'cancel'],
  setup(props, { emit }) {
    const { updateImage } = useImage()
    const isSubmitting = ref(false)
    
    // Form data
    const formData = ref({
      title: '',
      prompt: ''
    })
    
    // Khởi tạo form với dữ liệu hiện tại
    onMounted(() => {
      formData.value.title = props.imageData.title || ''
      formData.value.prompt = props.imageData.prompt || ''
    })
    
    // Xử lý submit form
    const handleSubmit = async () => {
      if (!formData.value.title || !formData.value.prompt) {
        return
      }
      
      isSubmitting.value = true
      
      try {
        const success = await updateImage(props.imageData.id, {
          title: formData.value.title,
          prompt: formData.value.prompt
        })
        
        if (success) {
          emit('update:success')
        }
      } catch (error) {
        console.error('Lỗi khi cập nhật bài viết:', error)
      } finally {
        isSubmitting.value = false
      }
    }
    
    return {
      formData,
      isSubmitting,
      handleSubmit
    }
  }
}
</script>
