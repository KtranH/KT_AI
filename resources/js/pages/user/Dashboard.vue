<template>
  <div class="min-h-screen bg-gray-100 pt-24">
    <!-- Ảnh background và avtar -->
    <!-- Container chính -->
    <div class="max-w-[90%] mx-auto my-4 bg-white rounded-lg shadow-lg" data-aos="zoom-out">
        <div class="relative">
          <!-- Cover Image section -->
          <div class="h-[300px] bg-purple-600 rounded-t-lg relative">
            <img 
              :src="coverImage" 
              loading="lazy" 
              class="w-full h-[300px] object-cover rounded-t-lg cursor-pointer" 
              @click="openPreview(coverImage, 'cover')"
            >
          </div>
          
          <!-- Avatar section -->
          <div class="absolute left-1/2 -translate-x-1/2 -translate-y-1/2">
            <div class="relative">
              <img 
                :src="avatar" 
                loading="lazy" 
                class="w-32 h-32 rounded-full border-4 border-white shadow-lg cursor-pointer" 
                @click="openPreview(avatar, 'avatar')"
              >
              <!-- Badge online -->
              <!-- <div class="absolute bottom-2 right-2 w-4 h-4 bg-green-500 rounded-full border-2 border-white"></div> -->
            </div>
          </div>
          
          <!-- Full-screen preview modal -->
          <div 
            v-if="previewVisible" 
            class="fixed inset-0 bg-black/90 z-50 flex items-center justify-center"
            @click="previewVisible = false"
          >
            <div class="relative max-w-6xl max-h-screen p-4">
              <button 
                class="absolute top-4 right-4 text-white z-10"
                @click.stop="previewVisible = false"
              >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
              
              <div class="relative">
                <img 
                  :src="currentPreviewImage" 
                  :class="[
                    'max-h-screen object-contain transition-all duration-300',
                    previewType === 'avatar' ? 'max-w-lg rounded-full' : 'max-w-6xl'
                  ]"
                >
              </div>
            </div>
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
                    <div class="text-xl font-bold bg-gradient-text-v2">{{user.sum_like? user.sum_like : 0}}</div>
                    <div class="text-gray-500 text-sm">Lượt thích</div>
                </div>
                <div class="text-center">
                    <div class="text-xl font-bold bg-gradient-text-v2">{{ user.sum_img? user.sum_img : 0 }}</div>
                    <div class="text-gray-500 text-sm">Số ảnh</div>
                </div>
                <div class="text-center">
                    <div class="text-xl font-bold bg-gradient-text-v2">{{ user.created_at? formatDate(user.created_at): "01/01/2025" }}</div>
                    <div class="text-gray-500 text-sm">Tham gia</div>
                </div>
            </div>

            <!-- Action buttons -->
            <div class="flex justify-center gap-4 mt-6">
                <button class="px-6 py-2 bg-gradient-text text-white rounded-full">
                    Cập nhật ảnh đại diện
                </button>
                <button class="px-6 py-2 border border-gray-300 text-gray-700 rounded-full hover:bg-gray-50 transition">
                    Cập nhật ảnh bìa
                </button>
            </div>
        </div>
      <div class="container mx-auto px-4" data-aos="zoom-in" data-aos-delay="300">
        <!-- Xin chào -->
        <div class="bg-gradient-text rounded-lg shadow-md p-6 flex justify-between">
          <div>
            <h1 class="text-2xl font-bold text-white mb-4">
              Xin chào, {{ user?.name || 'Người dùng' }}!
            </h1>
            <p class="text-gray-100">
              Chào mừng bạn đến với bảng điều khiển của KT_AI. Tại đây bạn có thể quản lý tài khoản và sử dụng các tính năng tạo ảnh AI.
            </p>
          </div>
          <img :src="logo_fun" class="w-24 h-24 rounded-full border-4 border-white shadow-lg">
        </div>
      </div>
      <hr class="mt-4 mb-4">
      <!-- Tabs navigation -->
      <div class="flex justify-center space-x-4 mb-6"  data-aos="zoom-out">
        <button 
          v-for="tab in tabs" 
          :key="tab.id"
          @click="activeTab = tab.id"
          class="px-6 py-2 rounded-full transition-all duration-300"
          :class="[
            activeTab === tab.id 
              ? 'bg-gradient-text text-white shadow-lg' 
              : 'bg-gray-100 text-gray-600 hover:bg-gray-200'
          ]"
        >
          {{ tab.name }}
        </button>
      </div>
      <!-- Danh sách ảnh -->
      <div data-aos="zoom-in-down" data-aos-delay="200">
        <ImageListVue :filter="activeTab" />
      </div>
    </div>
  </div>
</template>

<script>
import { onMounted, ref, onActivated } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth/authStore'
import ImageListVue from '@/components/user/Dashboard/ImageListLayout.vue'
import AOS from 'aos'

export default {
  name: 'Dashboard',
  components: {
    ImageListVue
  },
  setup() {
    //State
    const router = useRouter()
    const auth = useAuthStore()
    const user = auth.user.value
    const active = ref([])
    const avatar = user.avatar_url
    const coverImage = user.cover_image_url
    const logo_fun = ref("/img/humanoid.png")
    // Preview state
    const previewVisible = ref(false);
    const currentPreviewImage = ref("");
    const previewType = ref("");
    const activeTab = ref('created'); // Default active tab

    // Fetch data
    const tabs = [
      { id: 'created', name: 'Ảnh đã tạo' },
      { id: 'uploaded', name: 'Ảnh tải lên' },
      { id: 'liked', name: 'Ảnh đã thích' }
    ];

    // Methods
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

    // Open preview mode
    const openPreview = (imageUrl, type) => {
      currentPreviewImage.value = imageUrl;
      previewType.value = type;
      previewVisible.value = true;
    };

    // Mounted hook
    onMounted(() => {
      auth.checkAuth()
      setTimeout(() => {
        AOS.refresh()
      }, 100)
    })

    return {
      user,
      formatDate,
      logout,
      active,
      avatar,
      coverImage,
      logo_fun,
      openPreview,
      previewVisible,
      currentPreviewImage,
      previewType,
      activeTab,
      tabs
    }
  }
}
</script> 
<style scoped>
/* Gradient text effect */
.bg-gradient-text-v2 {
  background: linear-gradient(
    -45deg,
    #3b82f6,
    #6366f1,
    #8b5cf6,
    #ec4899,
    #3b82f6
  );
  background-size: 400%;
  animation: gradient-animation 8s ease infinite;
  
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}

/* Gradient background effect */
.bg-gradient-text {
  background: linear-gradient(
    -45deg,
    #3b82f6,
    #6366f1,
    #8b5cf6,
    #ec4899,
    #3b82f6
  );
  background-size: 400%;
  animation: gradient-animation 8s ease infinite;
}

@keyframes gradient-animation {
  0% { background-position: 0% 50%; }
  50% { background-position: 100% 50%; }
  100% { background-position: 0% 50%; }
}
</style>