<template>
  <div>
    <VueSonner position="top-right" theme="light" />
    <div class="min-h-screen bg-gradient-to-br from-indigo-50 via-white to-blue-100 flex items-center justify-center py-24 px-4">
    <div class="w-full max-w-[70%]">
      <h1 class="text-3xl font-extrabold text-center mb-10 flex items-center justify-center gap-2">
        <span>⚙️</span> Cài đặt tài khoản
      </h1>
      <div class="flex items-center justify-center gap-8 w-full">
        <div class="bg-white/90 w-full h-full shadow-2xl rounded-2xl border border-indigo-100 transition-transform hover:scale-[1.01] duration-200 flex-1 flex flex-col justify-center min-h-[500px]">
          <div class="p-16 flex-1 flex flex-col justify-center">
            <h2 class="text-xl font-bold mb-5 flex items-center gap-2 bg-gradient-text-v2">
              <span>👤</span> Thông tin cá nhân
            </h2>

            <form @submit.prevent="updateProfile" class="space-y-5">
              <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Họ và tên</label>
                <input type="text" id="name" v-model="profile.name" class="mt-1 block w-full rounded-lg border border-indigo-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-base px-4 py-2 bg-white/80">
              </div>

              <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input type="email" id="email" v-model="profile.email" disabled class="mt-1 block w-full rounded-lg border border-gray-200 shadow-sm bg-gray-100/80 sm:text-base px-4 py-2">
                <p class="mt-1 text-xs text-gray-400">Email không thể thay đổi</p>
              </div>

              <div>
                <label for="created_at" class="block text-sm font-medium text-gray-700 mb-1">Ngày tham gia</label>
                <input type="text" id="created_at" disabled class="mt-1 block w-full rounded-lg border border-gray-200 shadow-sm bg-gray-100/80 sm:text-base px-4 py-2" :value="formatDate(profile.created_at)">
              </div>

              <div class="flex items-center gap-3">
                <button type="submit" class="inline-flex items-center justify-center py-2 px-6 border-0 shadow-lg text-base font-semibold rounded-lg text-white bg-gradient-text from-indigo-500 to-blue-500 hover:from-indigo-600 hover:to-blue-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-400 transition-transform hover:scale-105">
                  <span>💾</span> Cập nhật thông tin
                </button>
                <span v-if="updateStatus" class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium border border-green-200 bg-green-50 text-green-700 gap-1 animate-fade-in">
                  <span>✔️</span> {{ updateStatus }}
                </span>
              </div>
            </form>
          </div>
        </div>

        <div class="bg-white/90 w-full h-full shadow-2xl rounded-2xl border border-indigo-100 transition-transform hover:scale-[1.01] duration-200 flex-1 flex flex-col justify-center min-h-[500px]">
          <div class="p-16 flex-1 flex flex-col justify-center">
            <h2 class="text-xl font-bold mb-5 flex items-center gap-2 bg-gradient-text-v2">
              <span>🔒</span> Đổi mật khẩu
            </h2>

            <div v-if="passwordChangeStep === 1">
              <form @submit.prevent="requestPasswordChange" class="space-y-5">
                <div>
                  <label for="current_password" class="block text-sm font-medium text-gray-700 mb-1">Mật khẩu hiện tại</label>
                  <input type="password" id="current_password" v-model="passwords.current" class="mt-1 block w-full rounded-lg border border-indigo-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-base px-4 py-2 bg-white/80">
                </div>

                <div>
                  <label for="new_password" class="block text-sm font-medium text-gray-700 mb-1">Mật khẩu mới</label>
                  <input type="password" id="new_password" v-model="passwords.new" class="mt-1 block w-full rounded-lg border border-indigo-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-base px-4 py-2 bg-white/80">
                  <p class="mt-1 text-xs text-gray-400">Mật khẩu mới phải có ít nhất 8 ký tự</p>
                </div>

                <div>
                  <label for="confirm_password" class="block text-sm font-medium text-gray-700 mb-1">Xác nhận mật khẩu mới</label>
                  <input type="password" id="confirm_password" v-model="passwords.confirm" class="mt-1 block w-full rounded-lg border border-indigo-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-base px-4 py-2 bg-white/80">
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
      </div>

      <div class="bg-white/90 shadow-2xl rounded-2xl border border-indigo-100 mt-8 transition-transform hover:scale-[1.01] duration-200">
        <div class="p-8">
          <h2 class="text-xl font-bold mb-5 flex items-center gap-2 bg-gradient-text-v2">
            <span>📜</span> Lịch sử hoạt động
          </h2>
          <div v-if="activities.length" class="divide-y divide-indigo-50">
            <div v-for="(activity, idx) in activitiesSorted" :key="idx" class="py-3 flex items-center gap-3">
              <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-indigo-100 text-indigo-600 font-bold">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3"/></svg>
              </span>
              <div class="flex-1">
                <div class="font-medium text-gray-800">{{ activity.action }}</div>
                <div class="text-xs text-gray-400">{{ formatTime(activity.timestamp) }}</div>
              </div>
            </div>
          </div>
          <div v-else class="text-gray-400 text-center py-6">
            Không có hoạt động nào gần đây.
          </div>
        </div>
      </div>
    </div>
  </div>
  <ConfirmUpdate ref="updateRef" />
</div>
</template>

<script>
import ConfirmUpdate from '@/components/common/ConfirmUpdate.vue'
import { ref, reactive, onMounted, computed } from 'vue'
import { useAuthStore } from '@/stores/auth/authStore'
import { profileAPI } from '@/services/api'
import { formatDate, formatTime, isActionTooQuick } from '@/utils'
import { toast, Toaster as VueSonner } from 'vue-sonner'

export default {
  name: 'Settings',
  components: {
    VueSonner,
    ConfirmUpdate
  },
  setup() {
    const auth = useAuthStore()

    const profile = reactive({
      name: '',
      email: '',
      created_at: ''
    })

    const passwords = reactive({
      current: '',
      new: '',
      confirm: ''
    })

    const updateStatus = ref('')
    const passwordStatus = ref('')
    const passwordError = ref(false)
    const lastActionTime = ref(null)
    const isLoading = ref(false)
    const passwordChangeStep = ref(1)
    const verificationCode = ref(['', '', '', '', '', ''])
    const codeInputRefs = ref([])
    const updateRef = ref(null)

    // Kiểm tra giá trị có khác với ban đầu không
    const isProfileChanged = computed(() => {
      return profile.name !== auth.user.value.name
    })

    // Activities
    const activities = ref([])
    onMounted(() => {
      // Khởi tạo dữ liệu từ thông tin người dùng hiện tại
      if (auth.user) {
        profile.name = auth.user.value.name || ''
        profile.email = auth.user.value.email || ''
        profile.created_at = auth.user.value.created_at || ''
        let acts = auth.user.value.activities
        if (typeof acts === 'string') {
          try {
            acts = JSON.parse(acts)
          } catch(e) {
            acts = []
          }
        }
        activities.value = Array.isArray(acts) ? acts : []
      }
    })

    const activitiesSorted = computed(() => {
      return [...activities.value].sort((a, b) => (b.timestamp || '').localeCompare(a.timestamp || ''))
    })

    const updateProfile = async () => {
      if (isActionTooQuick(lastActionTime.value)) {
        toast.info('Vui lòng đợi trước khi thực hiện thao tác này')
        return
      }
      if (!isProfileChanged.value) {
        toast.info('Thông tin không thay đổi')
        return
      }
      // Xác nhận thay đổi
      const result = await updateRef.value.showAlert()
      if (!result.isConfirmed) return

      // Gửi API thay đổi
      const formData = new FormData()
      formData.append('name', profile.name)
      try {
        await profileAPI.updateName(formData)
        updateStatus.value = 'Cập nhật thông tin thành công!'
        // Cập nhật thông tin trong store
        if (auth.user) {
          auth.user.value.name = profile.name
        }
        lastActionTime.value = Date.now()
        toast.success('Cập nhật thông tin thành công!')
        // Reset status sau 3 giây
        setTimeout(() => {
          updateStatus.value = ''
        }, 3000)
      } catch (error) {
        updateStatus.value = 'Có lỗi xảy ra: ' + (error.response?.data?.message || error.message)
      }
    }

    // Kiểm tra mã xác thực đã điền đủ chưa
    const isVerificationCodeComplete = computed(() => {
      return verificationCode.value.every(digit => digit.length === 1)
    })

    // Xử lý khi nhập mã xác thực
    const onVerificationCodeInput = (index) => {
      // Tự động chuyển đến ô tiếp theo sau khi nhập
      if (verificationCode.value[index].length === 1 && index < 5) {
        codeInputRefs.value[index + 1].focus()
      }
    }

    // Xử lý khi nhấn phím khi nhập mã xác thực
    const onVerificationCodeKeydown = (event, index) => {
      // Xử lý khi nhấn Backspace
      if (event.key === 'Backspace') {
        if (verificationCode.value[index].length === 0 && index > 0) {
          // Nếu ô hiện tại trống, quay lại ô trước đó
          codeInputRefs.value[index - 1].focus()
        }
      }
    }

    // Yêu cầu đổi mật khẩu và gửi mã xác thực
    const requestPasswordChange = async () => {
      passwordError.value = false
      passwordStatus.value = ''

      // Kiểm tra xác nhận mật khẩu
      if (passwords.new !== passwords.confirm) {
        passwordStatus.value = 'Mật khẩu xác nhận không khớp!'
        passwordError.value = true
        toast.error('Mật khẩu xác nhận không khớp!')
        return
      }

      // Kiểm tra độ dài mật khẩu
      if (passwords.new.length < 8) {
        passwordStatus.value = 'Mật khẩu mới phải có ít nhất 8 ký tự!'
        passwordError.value = true
        toast.error('Mật khẩu mới phải có ít nhất 8 ký tự!')
        return
      }
      
      // Kiểm tra mẩu khẩu hiện tại
      const formData = new FormData()
      formData.append('current_password', passwords.current)

      // Xác nhận thay đổi
      const result = await updateRef.value.showAlert()
      if (!result.isConfirmed) return

      // Gửi API thay đổi
      const checkPasswordResponse = await profileAPI.checkPassword(formData)
      if (!checkPasswordResponse.data.success) {
        passwordStatus.value = checkPasswordResponse.data.message
        passwordError.value = true
        toast.error(checkPasswordResponse.data.message)
        return
      }
      try {
        isLoading.value = true

        // Gửi yêu cầu gửi mã xác thực
        const response = await profileAPI.sendPasswordChangeVerification()

        // Chuyển sang bước 2: nhập mã xác thực
        passwordChangeStep.value = 2
        toast.success('Mã xác thực đã được gửi đến email của bạn')

        // Focus vào ô đầu tiên của mã xác thực
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

    // Hoàn tất đổi mật khẩu với mã xác thực
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
          // Reset form
          passwords.current = ''
          passwords.new = ''
          passwords.confirm = ''
          verificationCode.value = ['', '', '', '', '', '']

          // Quay lại bước 1
          passwordChangeStep.value = 1

          toast.success('Đổi mật khẩu thành công!')

          // Reset status sau 3 giây
          setTimeout(() => {
            passwordStatus.value = ''
          }, 3000)
        }
        else
        {
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
      profile,
      passwords,
      updateStatus,
      passwordStatus,
      passwordError,
      activities,
      activitiesSorted,
      formatDate,
      formatTime,
      updateProfile,
      changePassword,
      isProfileChanged,
      isLoading,
      passwordChangeStep,
      verificationCode,
      codeInputRefs,
      isVerificationCodeComplete,
      onVerificationCodeInput,
      onVerificationCodeKeydown,
      requestPasswordChange,
      updateRef
    }
  }
}
</script>