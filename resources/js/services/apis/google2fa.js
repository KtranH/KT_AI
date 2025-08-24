import apiClient from '../../config/apiConfig'

export const google2FAService = {
  /**
   * Lấy trạng thái 2FA của user
   */
  getStatus() {
    return apiClient.get('/2fa/status')
  },

  /**
   * Tạo mã QR và secret key để setup 2FA
   */
  generateQRCode() {
    return apiClient.post('/2fa/generate')
  },

  /**
   * Bật 2FA với mã xác thực
   */
  enable(data) {
    return apiClient.post('/2fa/enable', data)
  },

  /**
   * Tắt 2FA với mã xác thực
   */
  disable(data) {
    return apiClient.post('/2fa/disable', data)
  },

  /**
   * Tạo mã khôi phục mới
   */
  generateRecoveryCodes() {
    return apiClient.post('/2fa/recovery-codes')
  },

  /**
   * Xác thực 2FA khi đăng nhập
   */
  verify(data) {
    return apiClient.post('/2fa/verify', data)
  },

  /**
   * Sử dụng mã khôi phục
   */
  useRecoveryCode(data) {
    return apiClient.post('/2fa/recovery', data)
  },

  /**
   * Xác thực 2FA khi đăng nhập (alias cho verify)
   */
  verifyLogin(data) {
    return apiClient.post('/2fa/verify', data)
  },

  /**
   * Xác thực 2FA khi đăng nhập và hoàn tất quá trình đăng nhập
   */
  verifyLogin2FA(data) {
    return apiClient.post('/2fa/verify-login', data)
  },



  /**
   * Xác thực mã khôi phục khi đăng nhập (alias cho useRecoveryCode)
   */
  verifyRecoveryCode(data) {
    return apiClient.post('/2fa/recovery', data)
  }
}
