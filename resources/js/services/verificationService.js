import { authAPI } from './api'

export const resendCode = async (email) => {
  try {
    const response = await authAPI.resendVerification(email)
    return response.data 
  } catch (err) {
    throw err.response?.data?.message || 'Không thể gửi lại mã xác thực'
  }
}
