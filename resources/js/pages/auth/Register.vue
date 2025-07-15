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
        <p class="text-lg text-gray-600 mb-6 max-w-md">Nền tảng tạo ảnh AI, chia sẻ ý tưởng, kết nối cộng đồng sáng tạo. Đăng ký để trải nghiệm các tính năng vượt trội!</p>
        <div>
          <router-link to="/login" class="inline-block px-6 py-2 rounded-lg bg-gradient-to-r from-indigo-500 to-purple-500 text-white font-semibold shadow-md hover:from-purple-600 hover:to-indigo-600 transition-all duration-200">Đăng nhập ngay</router-link>
        </div>
      </div>
      <!-- Form đăng ký -->
      <div class="flex-1 w-full max-w-3xl">
        <AuthCard class="mt-10">
          <AuthFormHeader
            title="Đăng ký tài khoản mới"
            subtitle="Hoặc"
          >
            <template #subtitle-action>
              <router-link to="/login" class="font-medium text-indigo-600 hover:text-indigo-500 transition-colors duration-200">
                đăng nhập nếu đã có tài khoản
              </router-link>
            </template>
          </AuthFormHeader>

          <AlertMessage :message="error" type="error" />

          <form class="mt-8 space-y-6" @submit.prevent="handleSubmit" autocomplete="on">
            <div class="rounded-xl shadow-sm bg-white/60 border border-gray-200 p-4 space-y-4">
              <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Họ tên</label>
                <input 
                  id="name" 
                  v-model="form.name"
                  type="text" 
                  required 
                  class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-400 focus:border-purple-400 transition-all duration-200 text-gray-900 placeholder-gray-400 bg-white"
                  placeholder="Nhập họ tên"
                  autocomplete="name"
                >
              </div>
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
              <div class="relative w-full">
                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Mật khẩu</label>
                <input 
                  id="password"
                  v-model="form.password"
                  :type="showPassword ? 'text' : 'password'"
                  required
                  class="block w-full px-3 py-2 pr-10 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-400 focus:border-purple-400 transition-all duration-200 text-gray-900 placeholder-gray-400 bg-white"
                  placeholder="Nhập mật khẩu"
                  autocomplete="new-password"
                >
                <button 
                  type="button"
                  @click="togglePassword"
                  class="absolute inset-y-0 right-0 flex items-center px-3 text-gray-500 hover:text-indigo-600 transition-colors duration-200 focus:outline-none"
                  :title="showPassword ? 'Ẩn mật khẩu' : 'Hiện mật khẩu'"
                  tabindex="-1"
                >
                  <svg v-if="showPassword" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-5.523 0-10-4.477-10-10 0-1.657.336-3.236.938-4.675M15 12a3 3 0 11-6 0 3 3 0 016 0zm6.062-4.675A9.956 9.956 0 0122 9c0 5.523-4.477 10-10 10a9.956 9.956 0 01-4.675-.938" /></svg>
                  <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zm2.121-2.121A9.969 9.969 0 0122 12c0 5.523-4.477 10-10 10S2 17.523 2 12c0-2.21.717-4.253 1.929-5.879M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                </button>
              </div>
              <div>
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Xác nhận mật khẩu</label>
                <input 
                  id="password_confirmation" 
                  v-model="form.password_confirmation"
                  type="password" 
                  required 
                  class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-400 focus:border-purple-400 transition-all duration-200 text-gray-900 placeholder-gray-400 bg-white"
                  placeholder="Xác nhận mật khẩu"
                  autocomplete="new-password"
                >
              </div>
            </div>

            <div class="flex justify-center mt-4">
              <div ref="turnstileWidget" class="cf-turnstile"></div>
            </div>
            <AlertMessage :message="turnstileError" type="error" />

            <div>
              <button 
                type="submit" 
                :disabled="loading || !turnstileToken"
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
                      d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" 
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
                {{ loading ? 'Đang đăng ký...' : 'Đăng ký' }}
              </button>
            </div>

            <div class="mt-6">
              <div class="relative">
                <div class="absolute inset-0 flex items-center">
                  <div class="w-full border-t border-gray-300"></div>
                </div>
                <div class="relative flex justify-center text-sm">
                  <span class="px-2 bg-white/80 text-gray-500">
                    Hoặc đăng ký với
                  </span>
                </div>
              </div>

              <div class="mt-6">
                <SocialLoginButton 
                  provider="Google" 
                  icon="/img/google.png"
                  :loading="loading"
                  :formTurnstileToken="turnstileToken"
                  @click="startHandleLoginByGoogle"
                />
              </div>
            </div>
          </form>
        </AuthCard>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, reactive, onMounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import { useTurnstile } from '@/composables/features/auth/useTurnstile'
import AuthCard from '@/components/features/auth/components/AuthCard.vue'
import AuthFormHeader from '@/components/features/auth/components/AuthFormHeader.vue'
import AlertMessage from '@/components/features/auth/components/AlertMessage.vue'
import SocialLoginButton from '@/components/features/auth/components/SocialLoginButton.vue'
import { useAuthStore } from '@/stores/auth/authStore'
import axios from 'axios'

export default {
  name: 'Register',
  components: {
    AuthCard,
    AuthFormHeader,
    AlertMessage,
    SocialLoginButton
  },
  setup() {
    const router = useRouter()
    const loading = ref(false)
    const error = ref(null)
    const showPassword = ref(false)
    const form = reactive({
      name: '',
      email: '',
      password: '',
      password_confirmation: ''
    })
    const {
      turnstileWidget,
      turnstileToken,
      turnstileError,
      initTurnstile,
      resetTurnstile
    } = useTurnstile()
    onMounted(async () => {
      if (turnstileWidget.value) {
        await initTurnstile()
      }
    })
    const togglePassword = () => {
      showPassword.value = !showPassword.value
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
      if (form.password !== form.password_confirmation) {
        error.value = 'Mật khẩu không khớp nhau'
        return
      }
      if (!turnstileToken.value) {
        turnstileError.value = 'Vui lòng xác nhận bạn không phải robot'
        return
      }
      try {
        loading.value = true
        error.value = null
        const response = await axios.post('/api/register', {
          ...form,
          'cf-turnstile-response': turnstileToken.value
        }, {
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
          }
        });
        if (response.data.success) {
          router.push({
            path: '/verify-email',
            query: {
              email: form.email,
              message: response.data.message
            }
          })
        } else {
          throw new Error(response.data.message || 'Đã có lỗi xảy ra')
        }
      } catch (err) {
        error.value = err.response?.data?.message || err.message || 'Đã có lỗi xảy ra'
        resetTurnstile()
      } finally {
        loading.value = false
      }
    }
    return {
      form,
      loading,
      error,
      handleSubmit,
      showPassword,
      togglePassword,
      turnstileWidget,
      turnstileToken,
      turnstileError,
      startHandleLoginByGoogle
    }
  }
}
</script>