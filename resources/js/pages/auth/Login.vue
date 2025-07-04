<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
      <AuthFormHeader
        title="Đăng nhập vào tài khoản"
        subtitle="Hoặc"
      >
        <template #subtitle-action>
          <router-link to="/register" class="font-medium text-indigo-600 hover:text-indigo-500">
            đăng ký tài khoản mới
          </router-link>
        </template>
      </AuthFormHeader>

      <AlertMessage :message="error" type="error" />

      <AlertMessage v-if="needsVerification" type="warning">
        <p>Tài khoản của bạn chưa được xác thực.</p>
        <div class="mt-2">
          <button 
            @click="goToVerification" 
            class="text-sm font-medium text-yellow-800 underline hover:text-yellow-900"
          >
            Xác thực ngay
          </button>
        </div>
      </AlertMessage>

      <form class="mt-8 space-y-6" @submit.prevent="handleSubmit" data-aos="zoom-in" data-aos-delay="600">
        <div class="rounded-md shadow-sm -space-y-px">
          <div>
            <label for="email" class="sr-only">Email</label>
            <input 
              id="email" 
              v-model="form.email"
              type="email" 
              required 
              class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-t-md focus:outline-none focus:ring-purple-500 focus:border-purple-500 focus:z-10 sm:text-sm" 
              placeholder="Email"
            >
          </div>
          <div>
            <label for="password" class="sr-only">Mật khẩu</label>
            <input 
              id="password" 
              v-model="form.password"
              type="password" 
              required 
              class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-b-md focus:outline-none focus:ring-purple-500 focus:border-purple-500 focus:z-10 sm:text-sm" 
              placeholder="Mật khẩu"
            >
          </div>
        </div>

        <div class="flex items-center justify-between">
          <div class="flex items-center">
            <input 
              id="remember" 
              v-model="form.remember"
              type="checkbox" 
              class="h-4 w-4 text-purple-600 focus:ring-purple-500 border-gray-300 rounded"
            >
            <label for="remember" class="ml-2 block text-sm text-gray-900">
              Ghi nhớ đăng nhập
            </label>
          </div>

          <div class="text-sm">
            <router-link to="/forgot-password" class="font-medium text-indigo-600 hover:text-indigo-500">
              Quên mật khẩu??
            </router-link>
          </div>
        </div>

        <div class="flex justify-center">
          <div 
            ref="turnstileWidget"
            class="cf-turnstile">
          </div>
        </div>
        <AlertMessage :message="turnstileError" type="error" />

        <div>
          <button 
            type="submit" 
            :disabled="loading || !form.turnstileToken"
            class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50"
          >
            <span class="absolute left-0 inset-y-0 flex items-center pl-3">
              <svg 
                v-if="!loading"
                class="h-5 w-5 text-indigo-500 group-hover:text-indigo-400" 
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
            {{ loading ? 'Đang đăng nhập...' : 'Đăng nhập' }}
          </button>
        </div>

        <div class="mt-6">
          <div class="relative">
            <div class="absolute inset-0 flex items-center">
              <div class="w-full border-t border-gray-300"></div>
            </div>
            <div class="relative flex justify-center text-sm">
              <span class="px-2 bg-gray-50 text-gray-500">
                Hoặc đăng nhập với
              </span>
            </div>
          </div>

          <div class="mt-6">
            <SocialLoginButton 
              provider="Google" 
              icon="/img/google.png"
              :loading="loading"
              :formTurnstileToken="form.turnstileToken"
              @click="handleLoginByGoogle"
            >
            </SocialLoginButton>
          </div>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
import { ref, reactive, onMounted, onBeforeUnmount, watch } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth/authStore'
import { useTurnstile } from '@/composables/features/auth/useTurnstile'
import { AuthFormHeader, AlertMessage, SocialLoginButton } from '@/components/features/auth'

export default {
  name: 'Login',
  
  components: {
    AuthFormHeader,
    AlertMessage,
    SocialLoginButton,
  },
  
  setup() {
    // State
    const router = useRouter()
    const auth = useAuthStore()
    const loading = ref(false)
    const error = ref(null)
    const needsVerification = ref(false)
    
    // Sử dụng composable Turnstile
    const { 
      turnstileWidget, 
      turnstileError, 
      turnstileSiteKey,
      turnstileToken,
      resetTurnstile,
      initTurnstile
    } = useTurnstile()

    // Methods
    const form = reactive({
      email: '',
      password: '',
      remember: false,
      turnstileToken: ''
    })

    // Watch for turnstile token changes và đồng bộ với form
    watch(turnstileToken, (newToken) => {
      form.turnstileToken = newToken
    })

    onMounted(async () => {
      // Đảm bảo DOM ref đã được thiết lập
      if (turnstileWidget.value) {
        await initTurnstile()
      }
    })

    // Clean up any modifications we made
    onBeforeUnmount(() => {
      resetTurnstile()
    })

    const goToVerification = () => {
      router.push({
        name: 'verify-email',
        query: { email: form.email }
      })
    }
    
    const authStore = useAuthStore();
    const { handleLoginByGoogle } = authStore;
    
    const handleSubmit = async () => {
      if (!form.turnstileToken) {
        turnstileError.value = 'Vui lòng xác nhận bạn không phải robot'
        return
      }
      
      try {
        loading.value = true
        error.value = null
        needsVerification.value = false

        const loginData = {
          ...form,
          'cf-turnstile-response': form.turnstileToken
        }

        const response = await auth.login(loginData)
        
        if (response?.needs_verification) {
          needsVerification.value = true
          error.value = response.message
        }
      } catch (err) {
        error.value = "Thông tin đăng nhập không đúng"
        console.log(err)
        resetTurnstile()
      } finally {
        loading.value = false
      }
    }

    return {
      form,
      loading,
      error,
      needsVerification,
      handleSubmit,
      goToVerification,
      handleLoginByGoogle,
      turnstileSiteKey,
      turnstileWidget,
      turnstileError
    }
  }
}
</script>