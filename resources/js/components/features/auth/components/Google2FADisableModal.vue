<template>
  <div v-if="show" class="fixed inset-0 bg-black/50 backdrop-blur-sm overflow-y-auto h-full w-full z-50">
    <div class="relative top-10 mx-auto p-8 border w-full max-w-md shadow-2xl rounded-2xl bg-white">
      <div class="text-center">
        <div class="w-16 h-16 bg-gradient-to-r from-red-500 to-orange-500 rounded-2xl flex items-center justify-center mx-auto mb-6">
          <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"/>
          </svg>
        </div>
        <h3 class="text-2xl font-bold text-gray-900 mb-2">Tắt 2FA</h3>
        <p class="text-gray-600 mb-6">
          Bạn có chắc chắn muốn tắt xác thực 2 yếu tố? Điều này sẽ làm giảm tính bảo mật của tài khoản.
        </p>
        
        <div class="space-y-3 mb-6">
          <label class="block text-sm font-semibold text-gray-700">Mã xác thực</label>
          <input
            v-model="localVerificationCode"
            type="text"
            maxlength="6"
            class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:outline-none focus:ring-4 focus:ring-red-500/30 focus:border-red-500 text-center text-lg font-mono tracking-widest"
            placeholder="000000"
          />
        </div>
        
        <div class="flex space-x-3">
          <button
            @click="handleClose"
            class="flex-1 bg-gray-100 text-gray-700 py-3 px-4 rounded-xl font-semibold hover:bg-gray-200 transition-colors duration-200 focus:outline-none focus:ring-4 focus:ring-gray-500/30"
          >
            Hủy
          </button>
          <button
            @click="handleDisable"
            :disabled="!localVerificationCode || localVerificationCode.length !== 6"
            class="flex-1 bg-gradient-to-r from-red-500 to-orange-500 text-white py-3 px-4 rounded-xl font-semibold shadow-lg hover:shadow-xl transition-all duration-300 hover:scale-105 focus:outline-none focus:ring-4 focus:ring-red-500/30 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:scale-100"
          >
            Tắt 2FA
          </button>
        </div>
        
        <button
          @click="handleClose"
          class="absolute top-4 right-4 w-8 h-8 bg-gray-100 rounded-full flex items-center justify-center text-gray-400 hover:text-gray-600 hover:bg-gray-200 transition-colors duration-200"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
          </svg>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue'

const props = defineProps({
  show: {
    type: Boolean,
    default: false
  },
  verificationCode: {
    type: String,
    default: ''
  }
})

const emit = defineEmits(['close', 'disable'])

// Local state cho input
const localVerificationCode = ref('')

// Reset local state khi prop thay đổi
watch(() => props.verificationCode, (newValue) => {
  localVerificationCode.value = newValue
}, { immediate: true })

// Reset local state khi modal mở/đóng
watch(() => props.show, (newValue) => {
  if (newValue) {
    localVerificationCode.value = ''
  }
})

const handleClose = () => {
  localVerificationCode.value = ''
  emit('close')
}

const handleDisable = () => {
  emit('disable', localVerificationCode.value)
}
</script>
