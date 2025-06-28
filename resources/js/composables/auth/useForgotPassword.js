import { ref, reactive, nextTick } from 'vue'
import { useRouter } from 'vue-router'
import { profileAPI } from '@/services/api'

export function useForgotPassword() {
  const router = useRouter()
  const loading = ref(false)
  const error = ref(null)
  const success = ref(null)
  const currentStep = ref('email') // email, verification, reset
  
  const form = reactive({
    email: '',
    verificationCode: Array(6).fill(''),
    password: '',
    password_confirmation: '',
    token: ''
  })
  
  // Gửi yêu cầu quên mật khẩu
  const sendPasswordResetRequest = async (turnstileToken) => {
    try {
      loading.value = true
      error.value = null
      success.value = null
      
      const response = await profileAPI.forgotPassword({
        email: form.email,
        'cf-turnstile-response': turnstileToken
      })
      
      success.value = response.data.message || 'Chúng tôi đã gửi mã xác nhận đến email của bạn.'
      // Sử dụng nextTick để tránh cập nhật reactive đồng thời
      await nextTick(() => {
        currentStep.value = 'verification'
      })
      
      return { success: true }
    } catch (err) {
      error.value = err.response?.data?.message || 'Không thể gửi email khôi phục. Vui lòng thử lại sau.'
      console.error(err)
      return { success: false, error: error.value }
    } finally {
      loading.value = false
    }
  }
  
  // Xác minh mã xác thực
  const verifyCode = async (code) => {
    try {
      loading.value = true
      error.value = null
      success.value = null
      
      const response = await profileAPI.verifyResetCode({
        email: form.email,
        code
      })
      
      // Lưu token từ response
      const token = response.data.token
      const message = response.data.message || 'Mã xác thực hợp lệ'
      
      // Sử dụng setTimeout để tránh cập nhật liên tục các giá trị reactive
      setTimeout(() => {
        form.token = token
        success.value = message
        currentStep.value = 'reset'
      }, 0)
      
      return { success: true }
    } catch (err) {
      error.value = err.response?.data?.message || 'Mã xác thực không hợp lệ hoặc đã hết hạn'
      console.error(err)
      return { success: false, error: error.value }
    } finally {
      loading.value = false
    }
  }
  
  // Đặt lại mật khẩu
  const resetPassword = async () => {
    try {
      if (form.password !== form.password_confirmation) {
        error.value = 'Mật khẩu xác nhận không khớp'
        return { success: false, error: error.value }
      }
      
      loading.value = true
      error.value = null
      success.value = null
      
      const response = await profileAPI.resetPassword({
        email: form.email,
        password: form.password,
        password_confirmation: form.password_confirmation,
        token: form.token
      })
      
      success.value = response.data.message || 'Mật khẩu đã được đặt lại thành công'
      
      // Chuyển hướng về trang đăng nhập sau 2 giây
      setTimeout(() => {
        router.push('/login')
      }, 2000)
      
      return { success: true }
    } catch (err) {
      error.value = err.response?.data?.message || 'Không thể đặt lại mật khẩu. Vui lòng thử lại sau.'
      console.error(err)
      return { success: false, error: error.value }
    } finally {
      loading.value = false
    }
  }
  
  // Gửi lại mã xác thực
  const resendVerificationCode = async () => {
    try {
      loading.value = true
      error.value = null
      success.value = null
      
      const response = await profileAPI.forgotPassword({
        email: form.email
      })
      
      success.value = response.data.message || 'Mã xác thực đã được gửi lại'
      
      return { success: true }
    } catch (err) {
      error.value = err.response?.data?.message || 'Không thể gửi lại mã xác thực. Vui lòng thử lại sau.'
      console.error(err)
      return { success: false, error: error.value }
    } finally {
      loading.value = false
    }
  }
  
  // Reset form và state
  const resetForm = () => {
    form.email = ''
    form.verificationCode = Array(6).fill('')
    form.password = ''
    form.password_confirmation = ''
    form.token = ''
    
    error.value = null
    success.value = null
    currentStep.value = 'email'
  }
  
  return {
    form,
    loading,
    error,
    success,
    currentStep,
    sendPasswordResetRequest,
    verifyCode,
    resetPassword,
    resendVerificationCode,
    resetForm
  }
} 