<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
      <div>
        <router-link to="/" class="flex justify-center">
          <img src="/img/voice.png" alt="KT_AI Logo" class="h-12 w-12">
        </router-link>
        <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
          Đăng ký tài khoản mới
        </h2>
        <p class="mt-2 text-center text-sm text-gray-600">
          Hoặc
          <router-link to="/login" class="font-medium text-purple-600 hover:text-purple-500">
            đăng nhập nếu đã có tài khoản
          </router-link>
        </p>
      </div>

      <div v-if="error" class="bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded relative" role="alert">
        {{ error }}
      </div>

      <form class="mt-8 space-y-6" @submit.prevent="handleSubmit">
        <div class="rounded-md shadow-sm -space-y-px">
          <div>
            <label for="name" class="sr-only">Họ tên</label>
            <input 
              id="name" 
              v-model="form.name"
              type="text" 
              required 
              class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-t-md focus:outline-none focus:ring-purple-500 focus:border-purple-500 focus:z-10 sm:text-sm" 
              placeholder="Họ tên"
            >
          </div>
          <div>
            <label for="email" class="sr-only">Email</label>
            <input 
              id="email" 
              v-model="form.email"
              type="email" 
              required 
              class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-purple-500 focus:border-purple-500 focus:z-10 sm:text-sm" 
              placeholder="Email"
            >
          </div>
          <div class="relative w-full">
            <input 
              id="password"
              v-model="form.password"
              :type="showPassword ? 'text' : 'password'"
              required
              class="appearance-none rounded relative block w-full px-3 py-2 pr-10 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-purple-500 focus:border-purple-500 focus:z-10 sm:text-sm"
              placeholder="Mật khẩu"
            >
            <button 
              type="button"
              @click="togglePassword"
              class="absolute inset-y-0 right-0 flex items-center px-3 text-gray-500 hover:text-gray-700"
            >
              {{ showPassword ? '🙈' : '👁' }}
            </button>
          </div>
          <div>
            <label for="password_confirmation" class="sr-only">Xác nhận mật khẩu</label>
            <input 
              id="password_confirmation" 
              v-model="form.password_confirmation"
              type="password" 
              required 
              class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-b-md focus:outline-none focus:ring-purple-500 focus:border-purple-500 focus:z-10 sm:text-sm" 
              placeholder="Xác nhận mật khẩu"
            >
          </div>
        </div>

        <!-- Thêm Cloudflare Turnstile -->
        <div class="flex justify-center">
          <div 
            ref="turnstileWidget"
            class="cf-turnstile" 
            :data-sitekey="turnstileSiteKey"
            data-callback="handleTurnstileCallback">
          </div>
        </div>
        <div v-if="turnstileError" class="text-red-500 text-sm text-center">
          {{ turnstileError }}
        </div>

        <div>
          <button 
            type="submit" 
            :disabled="loading || !form.turnstileToken"
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
              <span class="px-2 bg-gray-50 text-gray-500">
                Hoặc đăng ký với
              </span>
            </div>
          </div>

          <div class="mt-6">
            <a
              href="/api/auth/google/url"
              class="w-full flex items-center justify-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500"
            >
              <img class="h-5 w-5 mr-2" src="/img/google.png" alt="Google logo">
              Google
            </a>
          </div>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
import { ref, reactive, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'

export default {
  name: 'Register',
  
  setup() {
    //State
    const router = useRouter()
    const loading = ref(false)
    const error = ref(null)
    const showPassword = ref(false)
    const turnstileWidget = ref(null)
    const turnstileError = ref(null)
    const turnstileSiteKey = '0x4AAAAAAAi8ATkfGjc9etVh'

    const form = reactive({
      name: '',
      email: '',
      password: '',
      password_confirmation: '',
      turnstileToken: ''
    })

    //Methods
    // Hàm xử lý callback từ Turnstile
    window.handleTurnstileCallback = (token) => {
      form.turnstileToken = token
      turnstileError.value = null
    }

    const loadTurnstileScript = () => {
      // Nếu script đã tồn tại, không cần load lại
      if (document.querySelector('script[src*="turnstile.js"]')) {
        return
      }
      
      const script = document.createElement('script')
      script.src = 'https://challenges.cloudflare.com/turnstile/v0/api.js?render=explicit'
      script.async = true
      script.defer = true
      document.head.appendChild(script)
      
      script.onload = () => {
        if (window.turnstile && turnstileWidget.value) {
          window.turnstile.render(turnstileWidget.value, {
            sitekey: turnstileSiteKey,
            callback: 'handleTurnstileCallback'
          })
        }
      }
    }

    const togglePassword = () => {
      showPassword.value = !showPassword.value
    }

    const handleSubmit = async () => {
      if (form.password !== form.password_confirmation) {
        error.value = 'Mật khẩu không khớp nhau'
        return
      }

      if (!form.turnstileToken) {
        turnstileError.value = 'Vui lòng xác nhận bạn không phải robot'
        return
      }

      try {
        loading.value = true
        error.value = null
        
        const response = await axios.post('/api/register', {
          ...form,
          'cf-turnstile-response': form.turnstileToken
        }, {
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
          }
        });

        if (response.data.success) {
          // Chuyển hướng sau khi đăng ký thành công
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
        // Reset Turnstile widget nếu xác thực không thành công
        if (window.turnstile) {
          window.turnstile.reset()
        }
      } finally {
        loading.value = false
      }
    }

    // Mounted hooks
    onMounted(() => {
      loadTurnstileScript()
    })

    return {
      form,
      loading,
      error,
      handleSubmit,
      showPassword,
      togglePassword,
      turnstileSiteKey,
      turnstileWidget,
      turnstileError
    }
  }
}
</script> 