<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
      <AuthFormHeader
        :title="pageTitle"
        subtitle="Hoặc"
      >
        <template #subtitle-action>
          <router-link to="/login" class="font-medium text-purple-600 hover:text-purple-500">
            quay lại đăng nhập
          </router-link>
        </template>
      </AuthFormHeader>

      <AlertMessage :message="error" type="error" />
      <AlertMessage :message="success" type="success" />

      <!-- Step 1: Email Input -->
      <form v-if="currentStep === 'email'" class="mt-8 space-y-6" @submit.prevent="handleEmailSubmit" data-aos="zoom-in" data-aos-delay="300">
        <div class="rounded-md shadow-sm">
          <div>
            <label for="email" class="sr-only">Email</label>
            <input 
              id="email" 
              v-model="form.email"
              type="email" 
              required 
              class="appearance-none rounded-md relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-purple-500 focus:border-purple-500 focus:z-10 sm:text-sm" 
              placeholder="Nhập địa chỉ email của bạn"
            >
          </div>
        </div>

        <div>
          <div class="flex justify-center">
            <div 
              ref="turnstileWidget"
              class="cf-turnstile">
            </div>
          </div>
          <AlertMessage :message="turnstileError" type="error" />
          <button 
            type="submit" 
            :disabled="loading"
            class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 disabled:opacity-50"
          >
            <span class="absolute left-0 inset-y-0 flex items-center pl-3">
              <svg 
                v-if="!loading"
                class="h-5 w-5 text-purple-500 group-hover:text-purple-400" 
                xmlns="http://www.w3.org/2000/svg" 
                viewBox="0 0 20 20" 
                fill="currentColor" 
                aria-hidden="true"
              >
                <path 
                  fill-rule="evenodd" 
                  d="M2.94 6.412A2 2 0 002 8.108V16a2 2 0 002 2h12a2 2 0 002-2V8.108a2 2 0 00-.94-1.696l-6-3.75a2 2 0 00-2.12 0l-6 3.75zm2.615 2.423a1 1 0 10-1.11 1.664l5 3.333a1 1 0 001.11 0l5-3.333a1 1 0 00-1.11-1.664L10 11.798 5.555 8.835z" 
                  clip-rule="evenodd" 
                />
              </svg>
              <svg 
                v-else
                class="animate-spin h-5 w-5 text-white" 
                xmlns="http://www.w3.org/2000/svg" 
                fill="none" 
                viewBox="0 0 24 24"
              >
                <circle 
                  class="opacity-25" 
                  cx="12" 
                  cy="12" 
                  r="10" 
                  stroke="currentColor" 
                  stroke-width="4"
                ></circle>
                <path 
                  class="opacity-75" 
                  fill="currentColor" 
                  d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                ></path>
              </svg>
            </span>
            {{ loading ? 'Đang gửi...' : 'Gửi mã xác nhận' }}
          </button>
        </div>
      </form>

      <!-- Step 2: Verification Code Input -->
      <form v-if="currentStep === 'verification'" class="mt-8 space-y-6" @submit.prevent="handleVerificationSubmit" data-aos="zoom-in" data-aos-delay="300">
        <div class="text-center mb-4">
          <p class="text-sm text-gray-600">
            Chúng tôi đã gửi mã xác nhận đến email <span class="font-medium">{{ form.email }}</span>
          </p>
        </div>

        <div class="rounded-md shadow-sm">
          <VerificationCodeInput
            v-model="form.verificationCode"
            :length="6"
            @code-complete="onCodeComplete"
          />
        </div>

        <div class="flex items-center justify-between">
          <div class="text-sm">
            <span class="text-gray-600">Chưa nhận được mã? </span>
            <button 
              type="button"
              @click="resendCode"
              :disabled="resendLoading || resendCountdown > 0"
              class="font-medium text-purple-600 hover:text-purple-500 disabled:opacity-50"
            >
              {{ resendCountdown > 0 ? `Gửi lại sau (${resendCountdown}s)` : 'Gửi lại mã' }}
            </button>
          </div>
        </div>

        <div>
          <button 
            type="submit" 
            :disabled="loading || form.verificationCode.join('').length !== 6"
            class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 disabled:opacity-50"
          >
            <span class="absolute left-0 inset-y-0 flex items-center pl-3">
              <svg 
                v-if="!loading"
                class="h-5 w-5 text-purple-500 group-hover:text-purple-400" 
                xmlns="http://www.w3.org/2000/svg" 
                viewBox="0 0 20 20" 
                fill="currentColor" 
                aria-hidden="true"
              >
                <path 
                  fill-rule="evenodd" 
                  d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" 
                  clip-rule="evenodd" 
                />
              </svg>
              <svg 
                v-else
                class="animate-spin h-5 w-5 text-white" 
                xmlns="http://www.w3.org/2000/svg" 
                fill="none" 
                viewBox="0 0 24 24"
              >
                <circle 
                  class="opacity-25" 
                  cx="12" 
                  cy="12" 
                  r="10" 
                  stroke="currentColor" 
                  stroke-width="4"
                ></circle>
                <path 
                  class="opacity-75" 
                  fill="currentColor" 
                  d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                ></path>
              </svg>
            </span>
            {{ loading ? 'Đang xác thực...' : 'Xác thực mã' }}
          </button>
        </div>
      </form>

      <!-- Step 3: New Password Input -->
      <form v-if="currentStep === 'reset'" class="mt-8 space-y-6" @submit.prevent="handleResetSubmit" data-aos="zoom-in" data-aos-delay="300">
        <div class="rounded-md shadow-sm -space-y-px">
          <div>
            <label for="password" class="sr-only">Mật khẩu mới</label>
            <input 
              id="password" 
              v-model="form.password"
              type="password" 
              required 
              class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-t-md focus:outline-none focus:ring-purple-500 focus:border-purple-500 focus:z-10 sm:text-sm" 
              placeholder="Mật khẩu mới"
            >
          </div>
          <div>
            <label for="password_confirmation" class="sr-only">Xác nhận mật khẩu</label>
            <input 
              id="password_confirmation" 
              v-model="form.password_confirmation"
              type="password" 
              required 
              class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-b-md focus:outline-none focus:ring-purple-500 focus:border-purple-500 focus:z-10 sm:text-sm" 
              placeholder="Xác nhận mật khẩu mới"
            >
          </div>
        </div>

        <div>
          <button 
            type="submit" 
            :disabled="loading || form.password !== form.password_confirmation"
            class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 disabled:opacity-50"
          >
            <span class="absolute left-0 inset-y-0 flex items-center pl-3">
              <svg 
                v-if="!loading"
                class="h-5 w-5 text-purple-500 group-hover:text-purple-400" 
                xmlns="http://www.w3.org/2000/svg" 
                viewBox="0 0 20 20" 
                fill="currentColor" 
                aria-hidden="true"
              >
                <path 
                  fill-rule="evenodd" 
                  d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" 
                  clip-rule="evenodd" 
                />
              </svg>
              <svg 
                v-else
                class="animate-spin h-5 w-5 text-white" 
                xmlns="http://www.w3.org/2000/svg" 
                fill="none" 
                viewBox="0 0 24 24"
              >
                <circle 
                  class="opacity-25" 
                  cx="12" 
                  cy="12" 
                  r="10" 
                  stroke="currentColor" 
                  stroke-width="4"
                ></circle>
                <path 
                  class="opacity-75" 
                  fill="currentColor" 
                  d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                ></path>
              </svg>
            </span>
            {{ loading ? 'Đang đặt lại mật khẩu...' : 'Đặt lại mật khẩu' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
import AuthFormHeader from '@/components/auth/AuthFormHeader.vue'
import AlertMessage from '@/components/auth/AlertMessage.vue'
import VerificationCodeInput from '@/components/auth/VerificationCodeInput.vue'
import { ref, computed, onMounted, onBeforeUnmount } from 'vue'
import { useForgotPassword } from '@/composables/auth/useForgotPassword'
import { useTurnstile } from '@/composables/auth/useTurnstile'
import { toast} from 'vue-sonner'

export default {
  name: 'ForgotPassword',
  
  components: {
    AuthFormHeader,
    AlertMessage,
    VerificationCodeInput,
  },
  
  setup() {
    // Sử dụng composable useForgotPassword
    const {
      form,
      loading,
      error,
      success,
      currentStep,
      sendPasswordResetRequest,
      verifyCode,
      resetPassword,
      resendVerificationCode
    } = useForgotPassword()

    // Sử dụng composable Turnstile
    const { 
      turnstileWidget, 
      turnstileError, 
      turnstileSiteKey,
      turnstileToken,
      resetTurnstile
    } = useTurnstile()
    
    // Đếm ngược gửi lại
    const resendLoading = ref(false)
    const resendCountdown = ref(0)
    const countdownInterval = ref(null) // Thêm ref để lưu trữ interval
    
    // Tính toán tiêu đề trang
    const pageTitle = computed(() => {
      switch(currentStep.value) {
        case 'email': return 'Quên mật khẩu'
        case 'verification': return 'Nhập mã xác nhận'
        case 'reset': return 'Đặt lại mật khẩu'
        default: return 'Quên mật khẩu'
      }
    })
    
    // Xử lý khi hoàn thành nhập email
    const handleEmailSubmit = async () => {
      if (!turnstileToken.value) {
        toast.error("Vui lòng xác nhận bạn không phải robot")
        turnstileError.value = 'Vui lòng xác nhận bạn không phải robot'
        return
      }
      const result = await sendPasswordResetRequest(turnstileToken.value)
      
      if (result.success) {
        startResendCountdown()
      }
    }
    
    // Xử lý khi hoàn thành mã xác thực
    const onCodeComplete = (code) => {
      form.verificationCode = code.split('')
    }
    
    // Xử lý khi submit mã xác thực
    const handleVerificationSubmit = async () => {
      const code = form.verificationCode.join('')
      await verifyCode(code)
    }
    
    // Xử lý khi đặt lại mật khẩu
    const handleResetSubmit = async () => {
      await resetPassword()
    }
    
    // Khởi động đếm ngược cho nút gửi lại mã
    const startResendCountdown = () => {
      // Dừng interval hiện tại nếu có
      if (countdownInterval.value) {
        clearInterval(countdownInterval.value)
        countdownInterval.value = null
      }
      
      // Khởi tạo lại giá trị đếm ngược
      resendCountdown.value = 60
      
      // Tạo interval mới
      countdownInterval.value = setInterval(() => {
        if (resendCountdown.value <= 0) {
          clearInterval(countdownInterval.value)
          countdownInterval.value = null
        } else {
          resendCountdown.value--
        }
      }, 1000)
    }
    
    // Gửi lại mã xác thực
    const resendCode = async () => {
      if (resendCountdown.value > 0) return
      
      try {
        resendLoading.value = true
        const result = await resendVerificationCode()
        
        if (result.success) {
          startResendCountdown()
        }
      } finally {
        resendLoading.value = false
      }
    }
    
    // Đồng bộ hóa token turnstile với form
    onMounted(() => {
      const originalCallback = window.handleTurnstileCallback
      window.handleTurnstileCallback = (token) => {
        if (originalCallback) {
          originalCallback(token)
        }
        
        // Update our form
        form.turnstileToken = token
      }
    })
    
    // Dọn dẹp khi component unmount
    onBeforeUnmount(() => {
      // Dọn dẹp interval khi component bị unmount
      if (countdownInterval.value) {
        clearInterval(countdownInterval.value)
        countdownInterval.value = null
      }
      resetTurnstile()
    })
    
    return {
      form,
      loading,
      resendLoading,
      resendCountdown,
      error,
      success,
      turnstileError,
      turnstileWidget,
      currentStep,
      pageTitle,
      handleEmailSubmit,
      handleVerificationSubmit,
      handleResetSubmit,
      onCodeComplete,
      resendCode
    }
  }
}
</script> 