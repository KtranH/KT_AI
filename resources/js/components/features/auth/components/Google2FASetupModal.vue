<template>
  <div v-if="show" class="fixed inset-0 bg-black/50 backdrop-blur-sm overflow-y-auto h-full w-full z-50">
    <div class="relative top-10 mx-auto p-8 border w-full max-w-md shadow-2xl rounded-2xl bg-white">
      <div class="text-center">
        <div class="w-16 h-16 bg-gradient-to-r from-blue-500 to-pink-600 rounded-2xl flex items-center justify-center mx-auto mb-6">
          <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
          </svg>
        </div>
        <h3 class="text-2xl font-bold text-gray-900 mb-2">Thiết lập 2FA</h3>
        <p class="text-gray-600 mb-6">Làm theo các bước để bảo mật tài khoản</p>
        
        <!-- Step 1: QR Code -->
        <div v-if="step === 1" class="space-y-6">
          <div class="bg-blue-50 rounded-xl p-4">
            <div class="flex items-center space-x-3 mb-3">
              <div class="w-8 h-8 bg-blue-500 rounded-lg flex items-center justify-center text-white font-bold">1</div>
              <span class="font-semibold text-blue-900">Tải ứng dụng</span>
            </div>
            <p class="text-sm text-blue-800">Tải Google Authenticator hoặc ứng dụng tương tự từ App Store/Google Play</p>
          </div>
          
          <div class="bg-blue-50 rounded-xl p-4">
            <div class="flex items-center space-x-3 mb-3">
              <div class="w-8 h-8 bg-blue-500 rounded-lg flex items-center justify-center text-white font-bold">2</div>
              <span class="font-semibold text-blue-900">Quét mã QR</span>
            </div>
            <p class="text-sm text-blue-800 mb-4">Mở ứng dụng và quét mã QR bên dưới</p>
            
            <div class="flex justify-center">
              <div v-if="qrCodeUrl && secretKey" class="bg-white p-6 rounded-2xl border-2 border-blue-200 shadow-lg">
                <div id="qrcode" class="w-48 h-48 flex items-center justify-center">
                  <!-- QR code sẽ được tạo ở đây bởi JavaScript -->
                </div>
              </div>
              <div v-else class="w-48 h-48 bg-gradient-to-br from-blue-100 to-purple-100 rounded-2xl flex items-center justify-center border-2 border-dashed border-blue-300">
                <div class="text-center">
                  <div class="w-12 h-12 bg-blue-200 rounded-full flex items-center justify-center mx-auto mb-2">
                    <svg class="w-6 h-6 text-blue-600 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                  </div>
                  <div class="text-blue-600 font-medium">Đang tạo mã QR...</div>
                </div>
              </div>
            </div>
            
            <div class="mt-4 text-center">
              <p class="text-xs text-gray-500 mb-2">Hoặc nhập mã thủ công:</p>
              <div class="bg-white px-4 py-3 rounded-lg border border-gray-200">
                <p class="text-sm font-mono text-gray-700 break-all">{{ secretKey || 'Chưa có mã' }}</p>
                <p class="text-xs text-gray-500 mt-1">Debug: qrCodeUrl = {{ qrCodeUrl ? 'Có' : 'Không' }}, secretKey = {{ secretKey ? 'Có' : 'Không' }}</p>
              </div>
            </div>
          </div>
          
          <button
            @click="$emit('nextStep')"
            class="w-full bg-gradient-to-r from-blue-500 to-pink-600 text-white py-3 px-6 rounded-xl font-semibold shadow-lg hover:shadow-xl transition-all duration-300 hover:scale-105 focus:outline-none focus:ring-4 focus:ring-blue-500/30"
          >
            Tiếp theo
            <svg class="w-5 h-5 ml-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
          </button>
        </div>
        
        <!-- Step 2: Verify Code -->
        <div v-if="step === 2" class="space-y-6">
          <div class="bg-green-50 rounded-xl p-4">
            <div class="flex items-center space-x-3 mb-3">
              <div class="w-8 h-8 bg-green-500 rounded-lg flex items-center justify-center text-white font-bold">3</div>
              <span class="font-semibold text-green-900">Xác thực</span>
            </div>
            <p class="text-sm text-green-800">Nhập mã 6 số từ ứng dụng xác thực</p>
          </div>
          
          <div class="space-y-3">
            <label class="block text-sm font-semibold text-gray-700">Mã xác thực</label>
            <input
              v-model="localVerificationCode"
              type="text"
              maxlength="6"
              class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:outline-none focus:ring-4 focus:ring-blue-500/30 focus:border-blue-500 text-center text-lg font-mono tracking-widest"
              placeholder="000000"
            />
          </div>
          
          <div class="flex space-x-3">
            <button
              @click="handlePreviousStep"
              class="flex-1 bg-gray-100 text-gray-700 py-3 px-4 rounded-xl font-semibold hover:bg-gray-200 transition-colors duration-200 focus:outline-none focus:ring-4 focus:ring-gray-500/30"
            >
              <svg class="w-5 h-5 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
              </svg>
              Quay lại
            </button>
            <button
              @click="handleVerify"
              :disabled="!localVerificationCode || localVerificationCode.length !== 6"
              class="flex-1 bg-gradient-to-r from-green-500 to-blue-600 text-white py-3 px-4 rounded-xl font-semibold shadow-lg hover:shadow-xl transition-all duration-300 hover:scale-105 focus:outline-none focus:ring-4 focus:ring-green-500/30 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:scale-100"
            >
              <svg class="w-5 h-5 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
              </svg>
              Xác nhận
            </button>
          </div>
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
import { ref, watch, onMounted } from 'vue'

const props = defineProps({
  show: {
    type: Boolean,
    default: false
  },
  step: {
    type: Number,
    default: 1
  },
  qrCodeUrl: {
    type: String,
    default: ''
  },
  secretKey: {
    type: String,
    default: ''
  },
  verificationCode: {
    type: String,
    default: ''
  }
})

const emit = defineEmits(['nextStep', 'previousStep', 'verify', 'close'])

// Local state cho input
const localVerificationCode = ref('')

// Reset local state khi prop thay đổi
watch(() => props.verificationCode, (newValue) => {
  localVerificationCode.value = newValue
}, { immediate: true })

// Reset local state khi modal mở/đóng hoặc step thay đổi
watch([() => props.show, () => props.step], ([show, step]) => {
  if (show && step === 2) {
    localVerificationCode.value = ''
  }
})

const handlePreviousStep = () => {
  localVerificationCode.value = ''
  emit('previousStep')
}

const handleVerify = () => {
  emit('verify', localVerificationCode.value)
}

const handleClose = () => {
  localVerificationCode.value = ''
  emit('close')
}
</script>
