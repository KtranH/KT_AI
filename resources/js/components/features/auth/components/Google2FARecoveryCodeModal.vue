<template>
  <div v-if="show" class="fixed inset-0 bg-black/50 backdrop-blur-sm overflow-y-auto h-full w-full z-50">
    <div class="relative top-10 mx-auto p-8 border w-full max-w-md shadow-2xl rounded-2xl bg-white">
      <div class="text-center">
        <div class="w-16 h-16 bg-gradient-to-r from-green-500 to-blue-600 rounded-2xl flex items-center justify-center mx-auto mb-6">
          <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
          </svg>
        </div>
        <h3 class="text-2xl font-bold text-gray-900 mb-2">Mã khôi phục</h3>
        <p class="text-gray-600 mb-6">
          Nhập một trong các mã khôi phục của bạn để truy cập tài khoản
        </p>
        
        <div class="space-y-3">
          <label class="block text-sm font-semibold text-gray-700">Mã khôi phục</label>
          <input
            v-model="localRecoveryCode"
            type="text"
            class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:outline-none focus:ring-4 focus:ring-green-500/30 focus:border-green-500 text-center text-lg font-mono"
            placeholder="Nhập mã khôi phục"
            @keyup.enter="handleVerify"
          />
          
          <!-- Error message -->
          <div v-if="errorMessage" class="bg-red-50 rounded-xl p-4 border border-red-200">
            <div class="flex items-center space-x-3">
              <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
              <span class="text-sm text-red-800">{{ errorMessage }}</span>
            </div>
          </div>
        </div>
        
        <div class="flex space-x-3 mt-6">
          <button
            @click="handleClose"
            class="flex-1 bg-gray-100 text-gray-700 py-3 px-4 rounded-xl font-semibold hover:bg-gray-200 transition-colors duration-200 focus:outline-none focus:ring-4 focus:ring-gray-500/30"
          >
            Quay lại
          </button>
          <button
            @click="handleVerify"
            :disabled="!localRecoveryCode || isLoading"
            class="flex-1 bg-gradient-to-r from-green-500 to-blue-600 text-white py-3 px-4 rounded-xl font-semibold shadow-lg hover:shadow-xl transition-all duration-300 hover:scale-105 focus:outline-none focus:ring-4 focus:ring-green-500/30 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:scale-100"
          >
            <svg v-if="!isLoading" class="w-5 h-5 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
            </svg>
            <svg v-else class="animate-spin w-5 h-5 mr-2 inline" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            {{ isLoading ? 'Đang xác thực...' : 'Xác nhận' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue'
import { useGoogle2FA } from '@/composables/features/auth/useGoogle2FA'

const props = defineProps({
  show: {
    type: Boolean,
    default: false
  },
  challengeData: {
    type: Object,
    default: () => ({})
  }
})

const emit = defineEmits(['close', 'verification-success'])

// Sử dụng useGoogle2FA composable
const { verifyRecoveryCode } = useGoogle2FA()

// Local state
const localRecoveryCode = ref('')
const isLoading = ref(false)
const errorMessage = ref('')

// Reset local state khi modal mở/đóng
watch(() => props.show, (newValue) => {
  if (newValue) {
    localRecoveryCode.value = ''
    errorMessage.value = ''
  }
})

const handleClose = () => {
  localRecoveryCode.value = ''
  errorMessage.value = ''
  emit('close')
}

const handleVerify = async () => {
  if (!props.challengeData?.challenge_id) {
    console.error('Challenge ID không hợp lệ')
    return
  }
  
  try {
    isLoading.value = true
    await verifyRecoveryCode(props.challengeData.challenge_id, localRecoveryCode.value)
    emit('verification-success')
  } catch (error) {
    console.error('Lỗi khi xác thực mã khôi phục:', error)
    errorMessage.value = error.message || 'Đã xảy ra lỗi khi xác thực mã khôi phục.'
  } finally {
    isLoading.value = false
  }
}
</script>
