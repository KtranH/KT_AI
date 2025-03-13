import { ref, onMounted, onUnmounted } from 'vue'

export function useTurnstile(siteKey = '0x4AAAAAAAi8ATkfGjc9etVh') {
  const turnstileWidget = ref(null)
  const turnstileError = ref(null)
  const turnstileToken = ref('')
  
  // Callback function for Turnstile
  const handleTurnstileCallback = (token) => {
    turnstileToken.value = token
    turnstileError.value = null
  }

  // Error callback function for Turnstile
  const handleTurnstileError = (error) => {
    console.error('Turnstile error:', error)
    turnstileError.value = 'Đã xảy ra lỗi khi xác thực. Vui lòng thử lại.'
    resetTurnstile()
  }

  // Expose the callback globally
  const setupGlobalCallback = () => {
    window.handleTurnstileCallback = handleTurnstileCallback
    window.handleTurnstileError = handleTurnstileError
  }

  // Load Turnstile script
  const loadTurnstileScript = () => {
    // If script is already loaded, render the widget directly
    if (window.turnstile) {
      renderTurnstileWidget()
      return
    }
    
    // If script tag exists but widget not rendered yet
    if (document.querySelector('script[src*="turnstile.js"]')) {
      // Wait for script to load
      const checkTurnstile = setInterval(() => {
        if (window.turnstile) {
          clearInterval(checkTurnstile)
          renderTurnstileWidget()
        }
      }, 100)
      return
    }
    
    // Create and load script
    const script = document.createElement('script')
    script.src = 'https://challenges.cloudflare.com/turnstile/v0/api.js?render=explicit'
    script.async = true
    script.defer = true
    
    script.onload = () => {
      renderTurnstileWidget()
    }
    
    script.onerror = (error) => {
      console.error('Failed to load Turnstile script:', error)
      turnstileError.value = 'Không thể tải bảo mật Cloudflare. Vui lòng làm mới trang.'
    }
    
    document.head.appendChild(script)
  }
  
  // Render the Turnstile widget
  const renderTurnstileWidget = () => {
    // Make sure the DOM element and turnstile object are available
    if (!turnstileWidget.value || !window.turnstile) return
    
    try {
      // Clear any existing widget first
      while (turnstileWidget.value.firstChild) {
        turnstileWidget.value.removeChild(turnstileWidget.value.firstChild)
      }
      
      // Render new widget
      window.turnstile.render(turnstileWidget.value, {
        sitekey: siteKey,
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

  // Reset Turnstile widget
  const resetTurnstile = () => {
    if (!window.turnstile || !turnstileWidget.value) return
    
    try {
      window.turnstile.reset(turnstileWidget.value)
    } catch (err) {
      console.error('Error resetting Turnstile:', err)
      
      // Try re-rendering as fallback
      renderTurnstileWidget()
    }
    
    turnstileToken.value = ''
  }

  // Initialize Turnstile
  const initTurnstile = () => {
    setupGlobalCallback()
    loadTurnstileScript()
  }

  // Clean up on component unmount
  onUnmounted(() => {
    // Remove global references to prevent memory leaks
    if (window.handleTurnstileCallback) {
      window.handleTurnstileCallback = undefined
    }
    if (window.handleTurnstileError) {
      window.handleTurnstileError = undefined
    }
  })

  onMounted(() => {
    initTurnstile()
  })

  return {
    turnstileWidget,
    turnstileError,
    turnstileToken,
    turnstileSiteKey: siteKey,
    resetTurnstile,
    loadTurnstileScript,
    initTurnstile
  }
}
