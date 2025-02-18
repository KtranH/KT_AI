<template>
  <div class="min-h-screen bg-gray-100 pt-24">
    <div class="container mx-auto px-4">
      <div class="bg-white rounded-lg shadow-sm p-6" data-aos="fade-up">
        <h1 class="text-2xl font-bold text-gray-900 mb-4">
          Xin chào, {{ user?.name || 'Người dùng' }}!
        </h1>
        <p class="text-gray-600">
          Chào mừng bạn đến với bảng điều khiển của KT_AI. Tại đây bạn có thể quản lý tài khoản và sử dụng các tính năng tạo ảnh AI.
        </p>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mt-6">
        <div class="bg-white rounded-lg shadow-sm p-6" data-aos="fade-right" data-aos-delay="400">
          <h2 class="text-lg font-semibold text-gray-900 mb-4">Thống kê</h2>
          <div class="space-y-4">
            <div class="flex justify-between items-center">
              <span class="text-gray-600">Số ảnh đã tạo:</span>
              <span class="font-medium">{{ user?.sum_img || 0 }}</span>
            </div>
            <div class="flex justify-between items-center">
              <span class="text-gray-600">Credit còn lại:</span>
              <span class="font-medium">{{ user?.remaining_credits || 0 }}</span>
            </div>
            <div class="flex justify-between items-center">
              <span class="text-gray-600">Gói dịch vụ:</span>
              <span class="font-medium">{{ user?.status_user === 1 ? 'Premium' : 'Miễn phí' }}</span>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6" data-aos="fade-right" data-aos-delay="200">
          <h2 class="text-lg font-semibold text-gray-900 mb-4">Hoạt động gần đây</h2>
          <div class="space-y-4">
            <div class="text-gray-600 text-center py-8">
              Chưa có hoạt động nào
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6" data-aos="fade-right">
          <h2 class="text-lg font-semibold text-gray-900 mb-4">Tài khoản</h2>
          <div class="space-y-4">
            <div class="flex justify-between items-center">
              <span class="text-gray-600">Email:</span>
              <span class="font-medium">{{ user?.email }}</span>
            </div>
            <div class="flex justify-between items-center">
              <span class="text-gray-600">Ngày tham gia:</span>
              <span class="font-medium">{{ formatDate(user?.created_at) }}</span>
            </div>
            <div class="mt-6">
              <button 
                @click="logout"
                class="w-full bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700 transition"
              >
                Đăng xuất
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import AOS from 'aos'

export default {
  name: 'Dashboard',
  
  setup() {
    AOS.init(
      {
        duration: 800,
        deplay: 500,
        once: false,
        offset: 150,
        easing: 'ease-in-sine',
      }
    )
    const router = useRouter()
    const auth = useAuthStore()
    
    const formatDate = (date) => {
      if (!date) return 'N/A'
      return new Date(date).toLocaleDateString('vi-VN')
    }

    const logout = async () => {
      try {
        const response = await auth.logout()
        if (response.success) {
          router.push('/login')
        }
      } catch (error) {
        console.error('Logout failed:', error)
      }
    }

    onMounted(() => {
      auth.checkAuth()
    })

    return {
      user: auth.user,
      formatDate,
      logout
    }
  }
}
</script> 