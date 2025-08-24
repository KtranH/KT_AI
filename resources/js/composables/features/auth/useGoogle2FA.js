import { ref, nextTick } from 'vue'
import { useAuthStore } from '@/stores/auth/authStore'
import { google2FAService } from '@/services/apis/google2fa'
import { toast } from 'vue-sonner'
import QRCode from 'qrcode'

export function useGoogle2FA() {
  const authStore = useAuthStore()
  
  // Reactive state
  const is2FAEnabled = ref(false)
  const showSetupModal = ref(false)
  const showDisableModal = ref(false)
  const showLogin2FAModal = ref(false)
  const showRecoveryCodeInput = ref(false)
  const setupStep = ref(1)
  const qrCodeUrl = ref('')
  const secretKey = ref('')
  const verificationCode = ref('')
  const disableVerificationCode = ref('')
  const loginVerificationCode = ref('')
  const recoveryCodeInput = ref('')
  const recoveryCodes = ref([])

  // Methods
  const check2FAStatus = async () => {
    try {
      console.log('Đang kiểm tra trạng thái 2FA...')
      const response = await google2FAService.getStatus()
      console.log('Response từ API 2FA status:', response.data)
      
      // Cập nhật state dựa trên response
      if (response.data && response.data.data) {
        is2FAEnabled.value = response.data.data.is_enabled || false
        if (response.data.data.is_enabled) {
          recoveryCodes.value = response.data.data.recovery_codes.map(code => code.code) || []
        }
      } else {
        // Fallback nếu response format khác
        is2FAEnabled.value = response.data?.is_enabled || false
        if (response.data?.is_enabled) {
          recoveryCodes.value = response.data.recovery_codes.map(code => code.code) || []
        }
      }
      
      console.log('Trạng thái 2FA đã được cập nhật:', is2FAEnabled.value)
    } catch (error) {
      console.error('Lỗi khi kiểm tra trạng thái 2FA:', error)
      // Không thay đổi state nếu có lỗi
    }
  }

  const generateQRCode = async () => {
    try {
      const response = await google2FAService.generateQRCode()      
      qrCodeUrl.value = response.data.data.qr_code_url
      secretKey.value = response.data.data.secret_key
      
      // Tạo QR code bằng JavaScript
      nextTick(async () => {
        if (qrCodeUrl.value) {
          await createQRCode(qrCodeUrl.value)
        }
      })
    } catch (error) {
      console.error('Lỗi khi tạo mã QR:', error)
    }
  }

  const createQRCode = async (url) => {
    const qrContainer = document.getElementById('qrcode')    
    if (qrContainer) {
      qrContainer.innerHTML = ''
      
      try {
        // Sử dụng thư viện qrcode để tạo QR code
        const qrDataUrl = await QRCode.toDataURL(url, {
          width: 192,
          height: 192,
          margin: 2,
          color: {
            dark: '#000000',
            light: '#ffffff'
          }
        })
        
        // Tạo img element để hiển thị QR code
        const img = document.createElement('img')
        img.src = qrDataUrl
        img.alt = 'QR Code'
        img.className = 'w-full h-full'
        qrContainer.appendChild(img)
      } catch (error) {
        console.error('Lỗi khi tạo QR code:', error)
        // Fallback: hiển thị URL dưới dạng text
        qrContainer.innerHTML = `
          <div class="text-center p-4">
            <div class="text-xs text-gray-500 mb-2">QR Code URL:</div>
            <div class="text-xs font-mono break-all bg-gray-100 p-2 rounded">
              ${url}
            </div>
          </div>
        `
      }
    } else {
      console.error('QR container not found!')
    }
  }

  const nextStep = () => {
    if (setupStep.value === 1) {
      setupStep.value = 2
    }
  }

  const previousStep = () => {
    if (setupStep.value === 2) {
      setupStep.value = 1
    }
  }

  const verifyAndEnable2FA = async (code) => {
    try {      
      // Validate code trước khi gửi
      if (!code || code.length !== 6) {
        toast.error('Vui lòng nhập mã xác thực 6 số')
        return
      }
      
      console.log('Đang gửi request bật 2FA với code:', code)
      const response = await google2FAService.enable({
        code: code
      })
      
      console.log('Response từ API enable 2FA:', response.data)
      
      if (response.data && response.data.success) {
        // Cập nhật state ngay lập tức
        is2FAEnabled.value = true
        recoveryCodes.value = response.data.data?.recovery_codes.map(code => code.code) || []
        
        console.log('2FA đã được bật thành công, state đã cập nhật:', is2FAEnabled.value)
        
        closeSetupModal()
        // Hiển thị thông báo thành công
        toast.success('Đã bật 2FA thành công!')
        
        // Gọi lại check2FAStatus để đảm bảo state đồng bộ
        await check2FAStatus()
      }
    } catch (error) {
      console.error('Lỗi khi bật 2FA:', error)
      toast.error('Mã xác thực không đúng. Vui lòng thử lại.')
    }
  }

  const disable2FA = async (code) => {
    try {      
      // Validate code trước khi gửi
      if (!code || code.length !== 6) {
        toast.error('Vui lòng nhập mã xác thực 6 số')
        return
      }
      
      console.log('Đang gửi request tắt 2FA với code:', code)
      const response = await google2FAService.disable({
        code: code
      })
      
      console.log('Response từ API disable 2FA:', response.data)
      
      if (response.data && response.data.success) {
        // Cập nhật state ngay lập tức
        is2FAEnabled.value = false
        recoveryCodes.value = []
        
        console.log('2FA đã được tắt thành công, state đã cập nhật:', is2FAEnabled.value)
        
        closeDisableModal()
        // Hiển thị thông báo thành công
        toast.success('Đã tắt 2FA thành công!')
        
        // Gọi lại check2FAStatus để đảm bảo state đồng bộ
        await check2FAStatus()
      }
    } catch (error) {
      console.error('Lỗi khi tắt 2FA:', error)
      toast.error('Mã xác thực không đúng. Vui lòng thử lại.')
    }
  }

  const generateRecoveryCodes = async () => {
    try {
      const response = await google2FAService.generateRecoveryCodes()
      recoveryCodes.value = response.data.recovery_codes.map(code => code.code)
      toast.success('Đã tạo mã khôi phục mới!')
    } catch (error) {
      console.error('Lỗi khi tạo mã khôi phục:', error)
    }
  }

  // Các method cho xác thực đăng nhập
  const showLogin2FAVerification = () => {
    showLogin2FAModal.value = true
  }

  const verifyLogin2FA = async (user_id, challengeId, code) => {
    try {
      const response = await google2FAService.verifyLogin2FA({
        user_id: user_id,
        challenge_id: challengeId,
        code: code
      })
      
      if (response.data && response.data.success) {
        showLogin2FAModal.value = false
        loginVerificationCode.value = ''
        
        // Cập nhật auth store với user mới
        if (response.data.data && response.data.data.user) {
          authStore.saveAuthData(response.data.data.user, null, true)
        }
        
        // Redirect to dashboard hoặc trang chính
        window.location.href = '/dashboard'
      } else {
        // Nếu response không thành công, throw error
        const errorMessage = response.data?.message || 'Mã xác thực không đúng'
        throw new Error(errorMessage)
      }
    } catch (error) {
      console.error('Lỗi khi xác thực 2FA đăng nhập:', error)
      // Throw error để component có thể xử lý
      throw error
    }
  }



  const verifyRecoveryCode = async (challengeId, code) => {
    try {
      const response = await google2FAService.useRecoveryCode({
        challenge_id: challengeId,
        code: code
      })
      
      if (response.data.success) {
        showRecoveryCodeInput.value = false
        showLogin2FAModal.value = false
        recoveryCodeInput.value = ''
        // Chuyển hướng hoặc cập nhật trạng thái đăng nhập
        if (response.data.data?.user) {
          authStore.saveAuthData(response.data.data.user, response.data.data.token || null, true)
        }
        // Redirect to dashboard hoặc trang chính
        window.location.href = '/dashboard'
      } else {
        // Nếu response không thành công, throw error
        const errorMessage = response.data?.message || 'Mã khôi phục không đúng'
        throw new Error(errorMessage)
      }
    } catch (error) {
      console.error('Lỗi khi xác thực mã khôi phục:', error)
      // Throw error để component có thể xử lý
      throw error
    }
  }

  const closeSetupModal = () => {
    showSetupModal.value = false
    setupStep.value = 1
    qrCodeUrl.value = ''
    secretKey.value = ''
    verificationCode.value = ''
  }

  const closeDisableModal = () => {
    showDisableModal.value = false
    disableVerificationCode.value = ''
  }

  return {
    // State
    is2FAEnabled,
    showSetupModal,
    showDisableModal,
    showLogin2FAModal,
    showRecoveryCodeInput,
    setupStep,
    qrCodeUrl,
    secretKey,
    verificationCode,
    disableVerificationCode,
    loginVerificationCode,
    recoveryCodeInput,
    recoveryCodes,
    
    // Methods
    check2FAStatus,
    generateQRCode,
    createQRCode,
    nextStep,
    previousStep,
    verifyAndEnable2FA,
    disable2FA,
    generateRecoveryCodes,
    showLogin2FAVerification,
    verifyLogin2FA,
    verifyRecoveryCode,
    closeSetupModal,
    closeDisableModal
  }
}
