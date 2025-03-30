import { ref, onMounted, onUnmounted } from 'vue'
import axios from 'axios'
import { turnstileAPI } from '@/services/api'

export function useTurnstile(siteKeyParam = null) {
  const turnstileWidget = ref(null)
  const turnstileError = ref(null)
  const turnstileToken = ref('')
  const turnstileSiteKey = ref(siteKeyParam)
  
  // Hàm callback cho Turnstile
  const handleTurnstileCallback = (token) => {
    turnstileToken.value = token
    turnstileError.value = null
  }

  // Hàm callback xử lý lỗi cho Turnstile
  const handleTurnstileError = (error) => {
    console.error('Turnstile error:', error)
    turnstileError.value = 'Đã xảy ra lỗi khi xác thực. Vui lòng thử lại.'
    resetTurnstile()
  }

  // Đưa callback ra toàn cục
  const setupGlobalCallback = () => {
    window.handleTurnstileCallback = handleTurnstileCallback
    window.handleTurnstileError = handleTurnstileError
  }

  // Lấy siteKey từ server nếu không được cung cấp
  const fetchSiteKey = async () => {
    if (turnstileSiteKey.value) return
    
    try {
      const response = await turnstileAPI.getConfig()
      turnstileSiteKey.value = response.data.siteKey
    } catch (error) {
      console.error('Failed to fetch Turnstile siteKey:', error)
      turnstileError.value = 'Không thể lấy cấu hình bảo mật. Vui lòng làm mới trang.'
    }
  }

  // Tải script Turnstile
  const loadTurnstileScript = async () => {
    // Đảm bảo có siteKey trước
    await fetchSiteKey()
    if (!turnstileSiteKey.value) {
      return // Không thể tải nếu không có siteKey
    }
    
    // Nếu script đã được tải, hiển thị widget trực tiếp
    if (window.turnstile) {
      renderTurnstileWidget()
      return
    }
    
    // Nếu thẻ script tồn tại nhưng widget chưa được hiển thị
    if (document.querySelector('script[src*="turnstile.js"]')) {
      // Đợi script tải xong
      const checkTurnstile = setInterval(() => {
        if (window.turnstile) {
          clearInterval(checkTurnstile)
          renderTurnstileWidget()
        }
      }, 100)
      return
    }
    
    // Tạo và tải script
    const script = document.createElement('script')
    script.src = 'https://challenges.cloudflare.com/turnstile/v0/api.js?onload=onloadTurnstileCallback'
    script.async = true
    script.defer = true
    
    // Định nghĩa hàm callback khi script tải xong
    window.onloadTurnstileCallback = () => {
      renderTurnstileWidget()
    }
    
    script.onload = () => {
      // Script đã tải xong, nhưng chờ callback để hiển thị widget
      console.log('Turnstile script loaded')
    }
    
    script.onerror = (error) => {
      console.error('Failed to load Turnstile script:', error)
      turnstileError.value = 'Không thể tải bảo mật Cloudflare. Vui lòng làm mới trang.'
    }
    
    document.head.appendChild(script)
  }
  
  // Hiển thị widget Turnstile
  const renderTurnstileWidget = () => {
    // Đảm bảo phần tử DOM và đối tượng turnstile có sẵn
    if (!turnstileWidget.value || !window.turnstile || !turnstileSiteKey.value) return
    
    try {
      // Xóa widget hiện có trước
      while (turnstileWidget.value.firstChild) {
        turnstileWidget.value.removeChild(turnstileWidget.value.firstChild)
      }
      
      // Hiển thị widget mới
      window.turnstile.render(turnstileWidget.value, {
        sitekey: turnstileSiteKey.value,
        callback: function(token) {
          if (window.handleTurnstileCallback) {
            window.handleTurnstileCallback(token)
          }
        },
        'error-callback': function(error) {
          if (window.handleTurnstileError) {
            window.handleTurnstileError(error)
          }
        }
      })
    } catch (err) {
      console.error('Failed to render Turnstile widget:', err)
      turnstileError.value = 'Không thể khởi tạo Cloudflare Turnstile. Vui lòng thử lại sau.'
    }
  }

  // Đặt lại widget Turnstile
  const resetTurnstile = () => {
    if (!window.turnstile || !turnstileWidget.value) return
    
    try {
      window.turnstile.reset(turnstileWidget.value)
    } catch (err) {
      console.error('Error resetting Turnstile:', err)
      
      // Thử hiển thị lại như một giải pháp dự phòng
      renderTurnstileWidget()
    }
    
    turnstileToken.value = ''
  }

  // Khởi tạo Turnstile
  const initTurnstile = async () => {
    setupGlobalCallback()
    await loadTurnstileScript()
  }

  // Dọn dẹp khi component bị hủy
  onUnmounted(() => {
    // Xóa tham chiếu toàn cục để tránh rò rỉ bộ nhớ
    if (window.handleTurnstileCallback) {
      window.handleTurnstileCallback = undefined
    }
    if (window.handleTurnstileError) {
      window.handleTurnstileError = undefined
    }
  })

  onMounted(async () => {
    await initTurnstile()
  })

  return {
    turnstileWidget,
    turnstileError,
    turnstileToken,
    turnstileSiteKey,
    resetTurnstile,
    loadTurnstileScript,
    initTurnstile
  }
}
