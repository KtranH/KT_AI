<template>
  <div class="container mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold mb-6">Cài đặt tài khoản</h1>
    
    <div class="bg-white shadow rounded-lg mb-6">
      <div class="p-6">
        <h2 class="text-lg font-medium mb-4">Thông tin cá nhân</h2>
        
        <form @submit.prevent="updateProfile" class="space-y-4">
          <div>
            <label for="name" class="block text-sm font-medium text-gray-700">Họ và tên</label>
            <input type="text" id="name" v-model="profile.name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
          </div>
          
          <div>
            <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
            <input type="email" id="email" v-model="profile.email" disabled class="mt-1 block w-full rounded-md border-gray-300 shadow-sm bg-gray-100 sm:text-sm">
            <p class="mt-1 text-xs text-gray-500">Email không thể thay đổi</p>
          </div>
          
          <div class="flex items-center">
            <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
              Cập nhật thông tin
            </button>
            <span v-if="updateStatus" class="ml-3 text-sm text-green-600">{{ updateStatus }}</span>
          </div>
        </form>
      </div>
    </div>
    
    <div class="bg-white shadow rounded-lg">
      <div class="p-6">
        <h2 class="text-lg font-medium mb-4">Đổi mật khẩu</h2>
        
        <form @submit.prevent="changePassword" class="space-y-4">
          <div>
            <label for="current_password" class="block text-sm font-medium text-gray-700">Mật khẩu hiện tại</label>
            <input type="password" id="current_password" v-model="passwords.current" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
          </div>
          
          <div>
            <label for="new_password" class="block text-sm font-medium text-gray-700">Mật khẩu mới</label>
            <input type="password" id="new_password" v-model="passwords.new" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
          </div>
          
          <div>
            <label for="confirm_password" class="block text-sm font-medium text-gray-700">Xác nhận mật khẩu mới</label>
            <input type="password" id="confirm_password" v-model="passwords.confirm" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
          </div>
          
          <div class="flex items-center">
            <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
              Đổi mật khẩu
            </button>
            <span v-if="passwordStatus" class="ml-3 text-sm" :class="{'text-green-600': !passwordError, 'text-red-600': passwordError}">{{ passwordStatus }}</span>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, reactive, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth/authStore'
import axios from 'axios'

export default {
  name: 'Settings',
  setup() {
    const auth = useAuthStore()
    
    const profile = reactive({
      name: '',
      email: ''
    })
    
    const passwords = reactive({
      current: '',
      new: '',
      confirm: ''
    })
    
    const updateStatus = ref('')
    const passwordStatus = ref('')
    const passwordError = ref(false)
    
    onMounted(() => {
      // Khởi tạo dữ liệu từ thông tin người dùng hiện tại
      if (auth.user) {
        profile.name = auth.user.name || ''
        profile.email = auth.user.email || ''
      }
    })
    
    const updateProfile = async () => {
      try {
        const response = await axios.post('/api/profile/update', {
          name: profile.name
        })
        
        updateStatus.value = 'Cập nhật thông tin thành công!'
        
        // Cập nhật thông tin trong store
        if (auth.user) {
          auth.user.name = profile.name
        }
        
        // Reset status sau 3 giây
        setTimeout(() => {
          updateStatus.value = ''
        }, 3000)
      } catch (error) {
        updateStatus.value = 'Có lỗi xảy ra: ' + (error.response?.data?.message || error.message)
      }
    }
    
    const changePassword = async () => {
      passwordError.value = false
      
      // Kiểm tra xác nhận mật khẩu
      if (passwords.new !== passwords.confirm) {
        passwordStatus.value = 'Mật khẩu xác nhận không khớp!'
        passwordError.value = true
        return
      }
      
      try {
        const response = await axios.post('/api/profile/change-password', {
          current_password: passwords.current,
          password: passwords.new,
          password_confirmation: passwords.confirm
        })
        
        passwordStatus.value = 'Đổi mật khẩu thành công!'
        
        // Reset form
        passwords.current = ''
        passwords.new = ''
        passwords.confirm = ''
        
        // Reset status sau 3 giây
        setTimeout(() => {
          passwordStatus.value = ''
        }, 3000)
      } catch (error) {
        passwordStatus.value = 'Có lỗi xảy ra: ' + (error.response?.data?.message || error.message)
        passwordError.value = true
      }
    }
    
    return {
      profile,
      passwords,
      updateStatus,
      passwordStatus,
      passwordError,
      updateProfile,
      changePassword
    }
  }
}
</script> 