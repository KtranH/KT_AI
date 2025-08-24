<template>
  <div v-if="show" class="fixed inset-0 bg-black/50 backdrop-blur-sm overflow-y-auto h-full w-full z-50">
    <div class="relative top-10 mx-auto p-8 border w-full max-w-md shadow-2xl rounded-2xl bg-white">
      <div class="text-center">
        <div class="w-20 h-20 bg-gradient-to-r from-blue-500 to-purple-600 rounded-2xl flex items-center justify-center mx-auto mb-6">
          <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
          </svg>
        </div>
        <h3 class="text-2xl font-bold text-gray-900 mb-2">Xác thực 2 lớp</h3>
        <p class="text-gray-600 mb-6">
          Vui lòng nhập mã 6 số từ ứng dụng Google Authenticator để tiếp tục đăng nhập
        </p>
        
        <div class="space-y-4">
          <div class="bg-blue-50 rounded-xl p-4 border border-blue-200">
            <div class="flex items-center space-x-3">
              <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
              <span class="text-sm text-blue-800">Mở ứng dụng Google Authenticator trên thiết bị của bạn</span>
            </div>
          </div>
          
          <!-- Error message -->
          <div v-if="errorMessage" class="bg-red-50 rounded-xl p-4 border border-red-200">
            <div class="flex items-center space-x-3">
              <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
              <span class="text-sm text-red-800">{{ errorMessage }}</span>
            </div>
          </div>
          
          <div class="space-y-3">
            <label class="block text-sm font-semibold text-gray-700">Mã xác thực</label>
            <input
              v-model="localVerificationCode"
              type="text"
              maxlength="6"
              class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:outline-none focus:ring-4 focus:ring-blue-500/30 focus:border-blue-500 text-center text-lg font-mono tracking-widest"
              placeholder="000000"
              @keyup.enter="handleVerify"
            />
          </div>
          
          <button
            @click="handleVerify"
            :disabled="!localVerificationCode || localVerificationCode.length !== 6 || isLoading"
            class="w-full bg-gradient-to-r from-blue-500 to-purple-600 text-white py-3 px-6 rounded-xl font-semibold shadow-lg hover:shadow-xl transition-all duration-300 hover:scale-105 focus:outline-none focus:ring-4 focus:ring-blue-500/30 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:scale-100"
          >
            <svg v-if="!isLoading" class="w-5 h-5 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
            </svg>
            <svg v-else class="animate-spin w-5 h-5 mr-2 inline" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            {{ isLoading ? 'Đang xác thực...' : 'Xác nhận đăng nhập' }}
          </button>
          
          <div class="pt-4 border-t border-gray-200">
            <button
              @click="$emit('showRecovery')"
              class="text-sm text-blue-600 hover:text-blue-500 font-medium"
            >
              Sử dụng mã khôi phục thay thế
            </button>
          </div>
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

const emit = defineEmits(['close', 'verification-success', 'showRecovery'])

// Sử dụng useGoogle2FA composable
const { verifyLogin2FA } = useGoogle2FA()

// Local state
const localVerificationCode = ref('')
const isLoading = ref(false)
const errorMessage = ref('')

// Reset local state khi modal mở/đóng
watch(() => props.show, (newValue) => {
  if (newValue) {
    localVerificationCode.value = ''
    errorMessage.value = ''
  }
})

const handleVerify = async () => {
  if (!props.challengeData?.challenge_id) {
    console.error('Challenge ID không hợp lệ')
    return
  }
  
  try {
    isLoading.value = true
    await verifyLogin2FA(props.challengeData.user_id, props.challengeData.challenge_id, localVerificationCode.value)
    emit('verification-success')
  } catch (error) {
    console.error('Lỗi khi xác thực 2FA:', error)
    errorMessage.value = 'Mã xác thực không đúng hoặc đã hết hạn. Vui lòng thử lại.'
  } finally {
    isLoading.value = false
  }
}
</script>
