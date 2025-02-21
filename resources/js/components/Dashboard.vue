<template>
  <div class="min-h-screen bg-gray-100 pt-24">
    <!-- Ảnh background và avtar -->
    <!-- Container chính -->
    <div class="max-w-[80%] mx-auto my-4 bg-white rounded-lg shadow-lg" data-aos="fade-up">
        <!-- Ảnh bìa -->
        <div class="h-[300px] bg-purple-600 rounded-t-lg relative">
            <!-- Có thể thay bg-blue-600 bằng ảnh thật -->
            <img :src="coverImage" loading="lazy" class="w-full h-[300px] object-cover rounded-t-lg">
        </div>

        <!-- Avatar section -->
        <div class="absolute left-1/2 -translate-x-1/2 -translate-y-1/2">
            <div class="relative">
                <img :src="avatar" loading="lazy" class="w-32 h-32 rounded-full border-4 border-white shadow-lg">
                <!-- Badge online -->
                <!--<div class="absolute bottom-2 right-2 w-4 h-4 bg-green-500 rounded-full border-2 border-white"></div>-->
            </div>
        </div>

        <!-- Content section -->
        <div class="pt-20 pb-8 px-6">
            <!-- Thông tin user -->
            <div class="text-center">
                <h2 class="text-2xl font-bold text-gray-800">{{ user.name ? user.name : "Người dùng" }}</h2>
                <p class="text-gray-600">{{ user.email ? user.email : "Email" }}</p>
                <p class="text-gray-500 mt-2">Số lượt tạo ảnh: {{ user.remaining_creadits ? user.remaining_creadits : 0 }}</p>
            </div>

            <!-- Stats -->
            <div class="flex justify-center gap-8 mt-6">
                <div class="text-center">
                    <div class="text-xl font-bold text-purple-600">{{user.sum_like? user.sum_like : 0}}</div>
                    <div class="text-gray-500 text-sm">Lượt thích</div>
                </div>
                <div class="text-center">
                    <div class="text-xl font-bold text-purple-600">{{ user.sum_img? user.sum_img : 0 }}</div>
                    <div class="text-gray-500 text-sm">Số ảnh</div>
                </div>
                <div class="text-center">
                    <div class="text-xl font-bold text-purple-600">{{ user.created_at? formatDate(user.created_at): "01/01/2025" }}</div>
                    <div class="text-gray-500 text-sm">Tham gia</div>
                </div>
            </div>

            <!-- Action buttons -->
            <div class="flex justify-center gap-4 mt-6">
                <button class="px-6 py-2 bg-purple-600 text-white rounded-full hover:bg-blue-700 transition">
                    Cập nhật ảnh đại diện
                </button>
                <button class="px-6 py-2 border border-gray-300 text-gray-700 rounded-full hover:bg-gray-50 transition">
                    Cập nhật ảnh bìa
                </button>
            </div>
        </div>
      <div class="container mx-auto px-4">
        <!-- Xin chào -->
        <div class="bg-white rounded-lg shadow-md p-6">
          <h1 class="text-2xl font-bold text-gray-900 mb-4">
            Xin chào, {{ user?.name || 'Người dùng' }}!
          </h1>
          <p class="text-gray-600">
            Chào mừng bạn đến với bảng điều khiển của KT_AI. Tại đây bạn có thể quản lý tài khoản và sử dụng các tính năng tạo ảnh AI.
          </p>
        </div>

        <!--
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-6 mt-6">
          <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Hoạt động gần đây</h2>
            <div class="space-y-4">
              <template v-if="active.length > 0">
                <div v-for="action in active" :key="action.id" class="flex justify-between items-center">
                  <span class="text-gray-600"><span class="font-medium">Thời gian: </span>{{ formatDate(action.timestamp) }}</span>
                  <span class="font-medium">{{ action.action }}</span>
                </div>
              </template>
              <div v-else class="text-gray-600 text-center py-8">
                  Chưa có hoạt động nào
              </div>
            </div>
          </div>
          <div class="bg-white rounded-lg shadow-md p-6">
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
        </div>-->
      </div>
      <!-- Ảnh nổi bật -->
      <div class="bg-white rounded-lg shadow-sm p-6 mt-6">
          <h2 class="text-lg font-semibold text-gray-900 mb-4">Ảnh nổi bật</h2>
      </div>
      <!-- Danh sách ảnh -->
      <div class="bg-white rounded-lg shadow-sm p-6 mt-6">
          <h2 class="text-lg font-semibold text-gray-900 mb-4">Danh sách ảnh</h2>
      </div>
    </div>
  </div>
</template>

<script>
import { onMounted, ref } from 'vue'
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
    const user = auth.user.value
    const active = ref([])
    const avatar = user.avatar_url
    const coverImage = user.cover_image_url

    const loadActivities = async () => {
      let actions = JSON.parse(user.activities)
      active.value = actions
    }
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
      loadActivities()
    })

    return {
      user,
      formatDate,
      logout,
      active,
      avatar,
      coverImage
    }
  }
}
</script> 