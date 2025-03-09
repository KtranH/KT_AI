import { verificationAPI } from './api'

export const resendCode = async (email) => {
  try {
    const response = await verificationAPI.resendCode(email)
    return response.data 
  } catch (err) {
    throw err.response?.data?.message || 'Không thể gửi lại mã xác thực'
  }
}
