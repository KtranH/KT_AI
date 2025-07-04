<template>
  <div class="bg-white/90 w-full h-full shadow-2xl rounded-2xl border border-indigo-100 transition-transform hover:scale-[1.01] duration-200 flex-1 flex flex-col justify-center min-h-[500px]">
    <div class="p-16 flex-1 flex flex-col justify-center">
      <h2 class="text-xl font-bold mb-5 flex items-center gap-2 bg-gradient-text-v2">
        <span>🔒</span> Đổi mật khẩu
      </h2>

      <div v-if="passwordChangeStep === 1">
        <form @submit.prevent="requestPasswordChange" class="space-y-5">
          <div>
            <label for="current_password" class="block text-sm font-medium text-gray-700 mb-1">Mật khẩu hiện tại</label>
            <input type="password" id="current_password" v-model="passwords.current" class="mt-1 block w-full rounded-lg border border-indigo-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-base px-4 py-2 bg-white/80" required>
          </div>

          <div>
            <label for="new_password" class="block text-sm font-medium text-gray-700 mb-1">Mật khẩu mới</label>
            <input type="password" id="new_password" v-model="passwords.new" class="mt-1 block w-full rounded-lg border border-indigo-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-base px-4 py-2 bg-white/80" required>
            <p class="mt-1 text-xs text-gray-400">Mật khẩu mới phải có ít nhất 8 ký tự</p>
          </div>

          <div>
            <label for="confirm_password" class="block text-sm font-medium text-gray-700 mb-1">Xác nhận mật khẩu mới</label>
            <input type="password" id="confirm_password" v-model="passwords.confirm" class="mt-1 block w-full rounded-lg border border-indigo-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-base px-4 py-2 bg-white/80" required>
          </div>

          <div class="flex items-center gap-3">
            <button type="submit" class="inline-flex items-center justify-center py-2 px-6 border-0 shadow-lg text-base font-semibold rounded-lg text-white bg-gradient-text from-indigo-500 to-blue-500 hover:from-indigo-600 hover:to-blue-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-400 transition-transform hover:scale-105" :disabled="isLoading">
              <span v-if="!isLoading">🔑</span>
              <span v-else class="animate-spin mr-2">↻</span>
              {{ isLoading ? 'Đang gửi mã...' : 'Tiếp tục' }}
            </button>
            <span v-if="passwordStatus" class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium border gap-1 animate-fade-in"
              :class="{'border-green-200 bg-green-50 text-green-700': !passwordError, 'border-red-200 bg-red-50 text-red-600': passwordError}">
              <span v-if="!passwordError">✔️</span>
              <span v-else>❌</span>
              {{ passwordStatus }}
            </span>
          </div>
        </form>
      </div>

      <div v-if="passwordChangeStep === 2" class="space-y-5">
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
          <p class="text-blue-700 text-sm">
            Chúng tôi đã gửi mã xác thực đến email của bạn. Vui lòng nhập mã 6 số để hoàn tất việc đổi mật khẩu.
          </p>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-3">Mã xác thực</label>
          <div class="flex justify-center gap-2">
            <input
              v-for="(digit, index) in verificationCode"
              :key="index"
              type="text"
              v-model="verificationCode[index]"
              maxlength="1"
              class="w-12 h-12 text-center text-xl font-bold rounded-lg border border-indigo-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 bg-white/80"
              @input="onVerificationCodeInput(index)"
              @keydown="onVerificationCodeKeydown($event, index)"
              :ref="el => { if (el) codeInputRefs[index] = el }"
            />
          </div>
        </div>

        <div class="flex items-center justify-between mt-4">
          <button
            type="button"
            class="text-indigo-600 hover:text-indigo-800 text-sm font-medium"
            @click="passwordChangeStep = 1"
          >
            &larr; Quay lại
          </button>

          <div class="flex items-center gap-3">
            <button
              type="button"
              class="inline-flex items-center justify-center py-2 px-6 border-0 shadow-lg text-base font-semibold rounded-lg text-white bg-gradient-text from-indigo-500 to-blue-500 hover:from-indigo-600 hover:to-blue-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-400 transition-transform hover:scale-105"
              @click="changePassword"
              :disabled="isLoading || !isVerificationCodeComplete"
            >
              <span v-if="!isLoading">🔑</span>
              <span v-else class="animate-spin mr-2">↻</span>
              {{ isLoading ? 'Đang xử lý...' : 'Đổi mật khẩu' }}
            </button>
            <span v-if="passwordStatus" class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium border gap-1 animate-fade-in"
              :class="{'border-green-200 bg-green-50 text-green-700': !passwordError, 'border-red-200 bg-red-50 text-red-600': passwordError}">
              <span v-if="!passwordError">✔️</span>
              <span v-else>❌</span>
              {{ passwordStatus }}
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, reactive, computed } from 'vue'
import { profileAPI } from '@/services/api'
import { toast } from 'vue-sonner'
import { useAuthStore } from '@/stores/auth/authStore'
import { ConfirmUpdate } from '@/components/base'

export default {
  name: 'ChangePassword',
  components: {
    ConfirmUpdate
  },
  setup() {
    const passwords = reactive({
      current: '',
      new: '',
      confirm: ''
    })

    const passwordStatus = ref('')
    const passwordError = ref(false)
    const isLoading = ref(false)
    const passwordChangeStep = ref(1)
    const verificationCode = ref(['', '', '', '', '', ''])
    const codeInputRefs = ref([])
    const updateRef = ref(null)

    const isVerificationCodeComplete = computed(() => {
      return verificationCode.value.every(digit => digit.length === 1)
    })

    const onVerificationCodeInput = (index) => {
      if (verificationCode.value[index].length === 1 && index < 5) {
        codeInputRefs.value[index + 1].focus()
      }
    }

    const onVerificationCodeKeydown = (event, index) => {
      if (event.key === 'Backspace') {
        if (verificationCode.value[index].length === 0 && index > 0) {
          codeInputRefs.value[index - 1].focus()
        }
      }
    }

    const requestPasswordChange = async () => {
      passwordError.value = false
      passwordStatus.value = ''

      // Kiểm tra thời gian từ lần đổi mật khẩu cuối
      const lastChange = auth.user.value?.last_password_change
      if (lastChange) {
        const lastChangeDate = new Date(lastChange)
        const now = new Date()
        const diffTime = Math.abs(now - lastChangeDate)
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24))
        
        if (diffDays < 7) {
          const remainingDays = 7 - diffDays
          toast.error(`Bạn chỉ có thể đổi mật khẩu sau ${remainingDays} ngày nữa`)
          return
        }
      }

      if (passwords.new !== passwords.confirm) {
        passwordStatus.value = 'Mật khẩu xác nhận không khớp!'
        passwordError.value = true
        toast.error('Mật khẩu xác nhận không khớp!')
        return
      }

      if (passwords.new.length < 8) {
        passwordStatus.value = 'Mật khẩu mới phải có ít nhất 8 ký tự!'
        passwordError.value = true
        toast.error('Mật khẩu mới phải có ít nhất 8 ký tự!')
        return
      }
      
      const formData = new FormData()
      formData.append('current_password', passwords.current)

      const result = await updateRef.value.showAlert()
      if (!result.isConfirmed) return

      const checkPasswordResponse = await profileAPI.checkPassword(formData)
      if (!checkPasswordResponse.data.success) {
        passwordStatus.value = checkPasswordResponse.data.message
        passwordError.value = true
        toast.error(checkPasswordResponse.data.message)
        return
      }
      try {
        isLoading.value = true
        await profileAPI.sendPasswordChangeVerification()
        passwordChangeStep.value = 2
        toast.success('Mã xác thực đã được gửi đến email của bạn')
        setTimeout(() => {
          if (codeInputRefs.value[0]) {
            codeInputRefs.value[0].focus()
          }
        }, 100)
      } catch (error) {
        passwordStatus.value = 'Có lỗi xảy ra: ' + (error.response?.data?.message || error.message)
        passwordError.value = true
      } finally {
        isLoading.value = false
      }
    }

    const changePassword = async () => {
      passwordError.value = false

      if (!isVerificationCodeComplete.value) {
        passwordStatus.value = 'Vui lòng nhập đủ mã xác thực!'
        passwordError.value = true
        return
      }

      const formData = new FormData()
      formData.append('current_password', passwords.current)
      formData.append('password', passwords.new)
      formData.append('password_confirmation', passwords.confirm)
      formData.append('verification_code', verificationCode.value.join(''))

      try {
        isLoading.value = true
        const response = await profileAPI.updatePassword(formData)

        if (response.data && response.data.success) {
          passwordStatus.value = 'Đổi mật khẩu thành công!'
          passwordError.value = false
          passwords.current = ''
          passwords.new = ''
          passwords.confirm = ''
          verificationCode.value = ['', '', '', '', '', '']
          passwordChangeStep.value = 1
          
          // Cập nhật thời gian đổi mật khẩu
          if (auth.user) {
            auth.user.value.last_password_change = new Date().toISOString()
          }
          
          toast.success('Đổi mật khẩu thành công!')
          setTimeout(() => {
            passwordStatus.value = ''
          }, 3000)
        } else {
          passwordStatus.value = response.data && response.data.message ? response.data.message : 'Có lỗi xảy ra khi đổi mật khẩu'
          passwordError.value = true
          toast.error(response.data && response.data.message ? response.data.message : 'Có lỗi xảy ra khi đổi mật khẩu')
        }
      } catch (error) {
        passwordStatus.value = 'Có lỗi xảy ra: ' + (error.response?.data?.message || error.message)
        passwordError.value = true
        toast.error(error.response?.data?.message || error.message)
      } finally {
        isLoading.value = false
      }
    }

    return {
      passwords,
      passwordStatus,
      passwordError,
      isLoading,
      passwordChangeStep,
      verificationCode,
      codeInputRefs,
      isVerificationCodeComplete,
      onVerificationCodeInput,
      onVerificationCodeKeydown,
      requestPasswordChange,
      changePassword,
      updateRef
    }
  }
}
</script> 