<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
      <AuthFormHeader
        title="Xác thực email"
        :subtitle="`Vui lòng nhập mã xác thực đã được gửi đến email ${email}`"
      />

      <AlertMessage :message="error" type="error" />
      <AlertMessage :message="success" type="success" />

      <form class="mt-8 space-y-6" @submit.prevent="handleSubmit">
        <div class="rounded-md shadow-sm">
          <VerificationCodeInput 
            v-model="verificationCode"
            @code-complete="handleComplete"
          />
        </div>

        <div class="flex items-center justify-between">
          <div class="text-sm">
            <button 
              type="button" 
              @click="handleResendCode" 
              :disabled="resendTimer > 0 || resendCount >= 2"
              class="font-medium text-purple-600 hover:text-purple-500 disabled:opacity-50 disabled:cursor-not-allowed"
            >
            {{ resendTimer > 0 ? 'Gửi lại sau ' + resendTimer + 's' : 'Gửi lại mã' }}
            </button>
          </div>
          <div class="text-sm text-gray-500">
            {{ resendCount }}/2 lần gửi lại
          </div>
        </div>

        <div>
          <button 
            type="submit" 
            :disabled="loading || !isCodeComplete"
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
                  d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" 
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
            {{ loading ? 'Đang xác thực...' : 'Xác thực' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { resendCode } from '@/services/verificationService'
import { useCodeVerification } from '@/composables/auth/useCodeVerification'
import AuthFormHeader from '@/components/auth/AuthFormHeader.vue'
import AlertMessage from '@/components/auth/AlertMessage.vue'
import VerificationCodeInput from '@/components/auth/VerificationCodeInput.vue'
import axios from 'axios'

export default {
  name: 'VerifyEmail',
  
  components: {
    AuthFormHeader,
    AlertMessage,
    VerificationCodeInput
  },
  
  setup() {
    // State
    const route = useRoute()
    const router = useRouter()
    const loading = ref(false)
    const error = ref(null)
    const success = ref(null)
    const email = ref(route.query.email || '')
    const resendTimer = ref(0)
    const resendCount = ref(0)
    
    // Sử dụng composable cho việc xác thực mã
    const {
      verificationCode,
      isCodeComplete,
      checkTooManyAttempts,
      incrementAttempts,
      resetCode
    } = useCodeVerification(6)

    // Methods
    const startResendTimer = () => {
      resendTimer.value = 60
      const timer = setInterval(() => {
        if (resendTimer.value > 0) {
          resendTimer.value--
        } else {
          clearInterval(timer)
        }
      }, 1000)
    }

    const handleResendCode = async () => {
      if (resendCount.value >= 2) return

      try {
        loading.value = true
        error.value = null
        
        await resendCode(email.value)
        resendCount.value++
        startResendTimer()
        success.value = 'Mã xác thực mới đã được gửi đến email của bạn'
      } catch (err) {
        error.value = err
      } finally {
        loading.value = false
      }
    }
    
    const handleComplete = (code) => {
      // Mã hoàn thành, có thể tự động submit nếu muốn
    }

    const handleSubmit = async () => {
      if (checkTooManyAttempts()) {
        error.value = 'Bạn đã thử xác thực quá nhiều lần. Vui lòng thử lại sau 30 phút.'
        return
      }

      try {
        loading.value = true
        error.value = null
        success.value = null
        
        const code = verificationCode.value.join('')
        const response = await axios.post('/api/verify-email', {
          email: email.value,
          code
        })

        success.value = 'Xác thực email thành công!'
        setTimeout(() => {
          router.push('/login')
        }, 2000)
      } catch (err) {
        error.value = err.response?.data?.message || 'Mã xác thực không đúng'
        incrementAttempts()
        if (checkTooManyAttempts()) {
          error.value = 'Bạn đã thử xác thực quá nhiều lần. Vui lòng thử lại sau 30 phút.'
        }
      } finally {
        loading.value = false
      }
    }

    // Lifecycle hooks
    onMounted(() => {
      if (!email.value) {
        router.push('/login')
      }
    })

    return {
      email,
      verificationCode,
      loading,
      error,
      success,
      isCodeComplete,
      resendTimer,
      resendCount,
      handleResendCode,
      handleSubmit,
      handleComplete
    }
  }
}
</script>