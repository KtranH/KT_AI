import { ref, computed } from 'vue'

export function useCodeVerification(length = 6) {
  const verificationCode = ref(Array(length).fill(''))
  const codeInputs = ref([])
  const verifyAttempts = ref(0)
  const lastVerifyTime = ref(0)
  
  // Kiểm tra xem mã có đầy đủ không
  const isCodeComplete = computed(() => {
    return verificationCode.value.every(digit => digit.length === 1)
  })
  
  // Lấy chuỗi mã hoàn chỉnh
  const getCompleteCode = () => {
    return verificationCode.value.join('')
  }
  
  // Xử lý sự kiện input
  const handleInput = (event, index) => {
    const value = event.target.value
    
    // Chỉ chấp nhận ký tự số
    if (value && !/^\d*$/.test(value)) {
      verificationCode.value[index] = ''
      return
    }
    
    // Nếu nhập giá trị và không phải là index cuối cùng, tự động focus vào ô tiếp theo
    if (value && index < length - 1) {
      codeInputs.value[index + 1].focus()
    }
  }
  
  // Xử lý sự kiện keydown
  const handleKeydown = (event, index) => {
    // Nếu nhấn Backspace và ô hiện tại trống, focus vào ô trước đó
    if (event.key === 'Backspace' && !verificationCode.value[index] && index > 0) {
      codeInputs.value[index - 1].focus()
    }
    
    // Nếu nhấn mũi tên trái, focus vào ô trước đó
    if (event.key === 'ArrowLeft' && index > 0) {
      codeInputs.value[index - 1].focus()
    }
    
    // Nếu nhấn mũi tên phải, focus vào ô sau đó
    if (event.key === 'ArrowRight' && index < length - 1) {
      codeInputs.value[index + 1].focus()
    }
  }
  
  // Xử lý kiểm tra quá nhiều lần
  const checkTooManyAttempts = () => {
    const now = Date.now()
    if (verifyAttempts.value >= 5 && now - lastVerifyTime.value < 1800000) {
      return true
    }
    return false
  }
  
  // Tăng số lần thử
  const incrementAttempts = () => {
    verifyAttempts.value++
    lastVerifyTime.value = Date.now()
  }
  
  // Reset mã xác thực
  const resetCode = () => {
    verificationCode.value = Array(length).fill('')
    // Focus vào ô đầu tiên
    if (codeInputs.value[0]) {
      codeInputs.value[0].focus()
    }
  }
  
  return {
    verificationCode,
    codeInputs,
    isCodeComplete,
    handleInput,
    handleKeydown,
    getCompleteCode,
    checkTooManyAttempts,
    incrementAttempts,
    resetCode,
    verifyAttempts,
    lastVerifyTime
  }
}
