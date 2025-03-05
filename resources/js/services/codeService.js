import axios from 'axios'

export const resendCode = async (email) => {
  try {
    const response = await axios.post('/api/resend-verification', { email })
    return response.data 
  } catch (err) {
    throw err.response?.data?.message || 'Không thể gửi lại mã xác thực'
  }
}
