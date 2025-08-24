<template>
  <div class="min-h-screen bg-gradient-to-br from-blue-50 via-indigo-50 to-purple-50 py-8">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Header với gradient đẹp -->
      <div class="mb-10 text-center">
        <div class="inline-flex items-center justify-center w-20 h-20 bg-gradient-to-r from-blue-500 to-pink-600 rounded-full shadow-xl mb-6">
          <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
          </svg>
        </div>
        <h1 class="text-4xl font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent mb-3">
          Bảo mật 2 lớp
        </h1>
        <p class="text-lg text-gray-600 max-w-2xl mx-auto">
          Bảo vệ tài khoản của bạn với xác thực 2 yếu tố để tăng cường bảo mật
        </p>
      </div>

      <!-- Status Card -->
      <Google2FAStatusCard 
        :is2FAEnabled="is2FAEnabled"
        @showSetup="showSetupModal = true"
        @showDisable="showDisableModal = true"
      />

      <!-- Setup Instructions -->
      <Google2FASetupInstructions v-if="!is2FAEnabled" />

      <!-- Recovery Codes Section -->
      <Google2FARecoveryCodes 
        v-if="is2FAEnabled"
        :recoveryCodes="recoveryCodes"
        @generateNew="generateRecoveryCodes"
      />

      <!-- Setup Modal -->
      <Google2FASetupModal
        :show="showSetupModal"
        :step="setupStep"
        :qrCodeUrl="qrCodeUrl"
        :secretKey="secretKey"
        :verificationCode="verificationCode"
        @nextStep="nextStep"
        @previousStep="previousStep"
        @verify="verifyAndEnable2FA"
        @close="closeSetupModal"
      />

      <!-- Disable Modal -->
      <Google2FADisableModal
        :show="showDisableModal"
        :verificationCode="disableVerificationCode"
        @close="closeDisableModal"
        @disable="disable2FA"
      />

      <!-- Login 2FA Modal -->
      <Google2FALoginModal
        :show="showLogin2FAModal"
        :verificationCode="loginVerificationCode"
        @verify="verifyLogin2FA"
        @showRecovery="showRecoveryCodeInput = true"
      />

      <!-- Recovery Code Input Modal -->
      <Google2FARecoveryCodeModal
        :show="showRecoveryCodeInput"
        :recoveryCode="recoveryCodeInput"
        @close="showRecoveryCodeInput = false"
        @verify="verifyRecoveryCode"
      />
    </div>
  </div>
</template>

<script setup>
import { onMounted, watch } from 'vue'
import Google2FAStatusCard from '@/components/features/auth/components/Google2FAStatusCard.vue'
import Google2FASetupInstructions from '@/components/features/auth/components/Google2FASetupInstructions.vue'
import Google2FARecoveryCodes from '@/components/features/auth/components/Google2FARecoveryCodes.vue'
import Google2FASetupModal from '@/components/features/auth/components/Google2FASetupModal.vue'
import Google2FADisableModal from '@/components/features/auth/components/Google2FADisableModal.vue'
import Google2FALoginModal from '@/components/features/auth/components/Google2FALoginModal.vue'
import Google2FARecoveryCodeModal from '@/components/features/auth/components/Google2FARecoveryCodeModal.vue'
import { useGoogle2FA } from '@/composables/features/auth/useGoogle2FA.js'

// Sử dụng composable
const {
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
} = useGoogle2FA()

// Expose method để component khác có thể gọi
defineExpose({
  showLogin2FAVerification
})

// Lifecycle
onMounted(() => {
  check2FAStatus()
})

// Watch for modal changes
watch(showSetupModal, (newVal) => {
  if (newVal) {
    generateQRCode()
  }
})
</script>
