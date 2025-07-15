<template>
  <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-purple-200 via-indigo-100 to-blue-100 py-12 px-4 sm:px-6 lg:px-8 transition-colors duration-700">
    <div class="w-full max-w-6xl mx-auto flex flex-col md:flex-row items-center justify-center gap-8">
      <!-- Phần giới thiệu -->
      <div class="hidden md:flex flex-col justify-center flex-1 pr-8">
        <router-link to="/" class="flex items-center mb-6 select-none">
          <img src="/img/voice.png" alt="KT_AI Logo" class="h-16 w-16 mr-3 drop-shadow-lg">
          <span class="text-4xl font-extrabold text-indigo-700 tracking-tight">KT_AI</span>
        </router-link>
        <h1 class="text-3xl font-bold text-gray-800 mb-4 leading-tight">Khám phá sức mạnh AI sáng tạo</h1>
        <p class="text-lg text-gray-600 mb-6 max-w-md">Nền tảng tạo ảnh AI, chia sẻ ý tưởng, kết nối cộng đồng sáng tạo. Đăng nhập để trải nghiệm các tính năng vượt trội!</p>
        <div>
          <router-link to="/register" class="inline-block px-6 py-2 rounded-lg bg-gradient-to-r from-indigo-500 to-purple-500 text-white font-semibold shadow-md hover:from-purple-600 hover:to-indigo-600 transition-all duration-200">Đăng ký ngay</router-link>
        </div>
      </div>
      
      <!-- Form đăng nhập -->
      <div class="flex-1 w-full max-w-3xl">
        <AuthCard>
          <AuthFormHeader
            title="Đăng nhập vào tài khoản"
            subtitle="Hoặc"
          >
            <template #subtitle-action>
              <router-link to="/register" class="font-medium text-indigo-600 hover:text-indigo-500 transition-colors duration-200">
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
                class="text-sm font-medium text-yellow-800 underline hover:text-yellow-900 transition-colors duration-200"
              >
                Xác thực ngay
              </button>
            </div>
          </AlertMessage>

          <form class="mt-8 space-y-6" @submit.prevent="handleSubmit" autocomplete="on">
            <div class="rounded-xl shadow-sm bg-white/60 border border-gray-200 p-4 space-y-4">
              <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input 
                  id="email" 
                  v-model="form.email"
                  type="email" 
                  required 
                  class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-400 focus:border-purple-400 transition-all duration-200 text-gray-900 placeholder-gray-400 bg-white"
                  placeholder="Nhập email của bạn"
                  autocomplete="email"
                >
              </div>
              <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Mật khẩu</label>
                <input 
                  id="password" 
                  v-model="form.password"
                  type="password" 
                  required 
                  class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-400 focus:border-purple-400 transition-all duration-200 text-gray-900 placeholder-gray-400 bg-white"
                  placeholder="Nhập mật khẩu"
                  autocomplete="current-password"
                >
              </div>
            </div>

            <div class="flex items-center justify-between mt-2">
              <div class="flex items-center">
                <input 
                  id="remember" 
                  v-model="form.remember"
                  type="checkbox" 
                  class="h-4 w-4 text-purple-600 focus:ring-purple-500 border-gray-300 rounded transition-all duration-200"
                >
                <label for="remember" class="ml-2 block text-sm text-gray-900 select-none">
                  Ghi nhớ đăng nhập
                </label>
              </div>

              <div class="text-sm">
                <router-link to="/forgot-password" class="font-medium text-indigo-600 hover:text-indigo-500 transition-colors duration-200">
                  Quên mật khẩu?
                </router-link>
              </div>
            </div>

            <div class="flex justify-center mt-4">
              <div ref="turnstileWidget" class="cf-turnstile"></div>
            </div>
            <AlertMessage :message="turnstileError" type="error" />

            <div>
              <button 
                type="submit" 
                :disabled="loading || !form.turnstileToken"
                class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-semibold rounded-lg text-white bg-gradient-to-r from-indigo-500 to-purple-500 hover:from-purple-600 hover:to-indigo-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-400 disabled:opacity-50 transition-all duration-200 shadow-lg"
              >
                <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                  <svg 
                    v-if="!loading"
                    class="h-5 w-5 text-indigo-200 group-hover:text-indigo-100 transition-colors duration-200" 
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
                  <span class="px-2 bg-white/80 text-gray-500">
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
                  @click="startHandleLoginByGoogle"
                >
                </SocialLoginButton>
              </div>
            </div>
          </form>
        </AuthCard>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, reactive, onMounted, onBeforeUnmount, watch } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth/authStore'
import { useTurnstile } from '@/composables/features/auth/useTurnstile'
import AuthCard from '@/components/features/auth/components/AuthCard.vue'
import AuthFormHeader from '@/components/features/auth/components/AuthFormHeader.vue'
import AlertMessage from '@/components/features/auth/components/AlertMessage.vue'
import SocialLoginButton from '@/components/features/auth/components/SocialLoginButton.vue'

export default {
  name: 'Login',
  components: {
    AuthCard,
    AuthFormHeader,
    AlertMessage,
    SocialLoginButton,
  },
  setup() {
    const router = useRouter()
    const auth = useAuthStore()
    const loading = ref(false)
    const error = ref(null)
    const needsVerification = ref(false)
    const { 
      turnstileWidget, 
      turnstileError, 
      turnstileSiteKey,
      turnstileToken,
      resetTurnstile,
      initTurnstile
    } = useTurnstile()
    const form = reactive({
      email: '',
      password: '',
      remember: false,
      turnstileToken: ''
    })
    watch(turnstileToken, (newToken) => {
      form.turnstileToken = newToken
    })
    onMounted(async () => {
      if (turnstileWidget.value) {
        await initTurnstile()
      }
    })
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
    const startHandleLoginByGoogle = async () => {
      loading.value = true;
      try{
        await handleLoginByGoogle(() => {
          loading.value = false;
        })
      } catch (err) {
        console.log(err)
      }
    } 
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
      turnstileError,
      startHandleLoginByGoogle
    }
  }
}
</script>