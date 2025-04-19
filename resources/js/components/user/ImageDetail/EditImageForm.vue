<template>
  <div class="p-4 bg-white rounded-lg shadow">
    <h2 class="text-lg font-semibold mb-4">Chỉnh sửa bài viết</h2>

    <form @submit.prevent="handleSubmit">
      <div class="mb-4">
        <div class="flex justify-between items-center mb-1">
          <label for="title" class="block text-sm font-medium text-gray-700">Tiêu đề</label>
          <span
            :class="{
              'text-gray-500': formData.title.length <= 20,
              'text-yellow-500': formData.title.length > 20 && formData.title.length <= 30,
              'text-red-500': formData.title.length > 30
            }"
            class="text-xs">
            {{ formData.title.length }}/30
          </span>
        </div>
        <input
          type="text"
          id="title"
          v-model="formData.title"
          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          placeholder="Nhập tiêu đề bài viết"
          maxlength="30"
          required
        />
      </div>

      <div class="mb-4">
        <div class="flex justify-between items-center mb-1">
          <label for="prompt" class="block text-sm font-medium text-gray-700">Mô tả</label>
          <span
            :class="{
              'text-gray-500': formData.prompt.length <= 200,
              'text-yellow-500': formData.prompt.length > 200 && formData.prompt.length <= 255,
              'text-red-500': formData.prompt.length > 255
            }"
            class="text-xs">
            {{ formData.prompt.length }}/255
          </span>
        </div>
        <textarea
          id="prompt"
          v-model="formData.prompt"
          rows="4"
          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          placeholder="Nhập mô tả bài viết"
          maxlength="255"
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
import { ref, onMounted, computed } from 'vue'
import useImage from '@/composables/user/useImage'
import { isTextLengthValid } from '@/utils'
import { toast } from 'vue-sonner'

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

    // Kiểm tra độ dài của title và prompt
    const isTitleValid = computed(() => isTextLengthValid(formData.value.title, 30))
    const isPromptValid = computed(() => isTextLengthValid(formData.value.prompt, 255))

    // Xử lý submit form
    const handleSubmit = async () => {
      if (!formData.value.title || !formData.value.prompt) {
        toast.error('Vui lòng nhập đầy đủ thông tin')
        return
      }

      if (!isTitleValid.value) {
        toast.error('Tiêu đề không được vượt quá 30 ký tự')
        return
      }

      if (!isPromptValid.value) {
        toast.error('Mô tả không được vượt quá 255 ký tự')
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
