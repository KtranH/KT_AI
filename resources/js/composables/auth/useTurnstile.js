import { ref, onMounted } from 'vue'

export function useTurnstile(siteKey = '0x4AAAAAAAi8ATkfGjc9etVh') {
  const turnstileWidget = ref(null)
  const turnstileError = ref(null)
  const turnstileToken = ref('')
  
  // Callback function for Turnstile
  const handleTurnstileCallback = (token) => {
    turnstileToken.value = token
    turnstileError.value = null
  }

  // Expose the callback globally
  const setupGlobalCallback = () => {
    window.handleTurnstileCallback = handleTurnstileCallback
  }

  // Load Turnstile script
  const loadTurnstileScript = () => {
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
          sitekey: siteKey,
          callback: 'handleTurnstileCallback'
        })
      }
    }
  }

  // Reset Turnstile widget
  const resetTurnstile = () => {
    if (window.turnstile) {
      window.turnstile.reset()
    }
    turnstileToken.value = ''
  }

  // Initialize Turnstile
  const initTurnstile = () => {
    setupGlobalCallback()
    loadTurnstileScript()
  }

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
