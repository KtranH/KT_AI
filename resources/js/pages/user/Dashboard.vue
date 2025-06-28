<template>
  <div class="min-h-screen bg-gray-100">
    <!-- Toast notifications are handled globally in App.vue -->
    <!-- Ảnh background và avtar -->
    <!-- Container chính -->
    <div class="max-w-[100%] mx-auto my-4 bg-white rounded-lg shadow-lg" data-aos="zoom-out">
        <div class="relative">
          <!-- Cover Image section -->
          <div class="h-[300px] bg-purple-600 rounded-t-lg relative overflow-hidden group">
            <img
              :src="coverImage"
              loading="lazy"
              class="w-full h-[300px] object-cover rounded-t-lg cursor-pointer transition-transform duration-500 group-hover:scale-105"
              @click="openPreview(coverImage, 'cover')"
            >
            <div class="absolute inset-0 bg-black bg-opacity-30 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center">
              <button
                v-if="!isOtherUserProfile"
                @click.stop="openUploadModal('cover')"
                class="px-4 py-2 bg-white bg-opacity-90 rounded-full text-gray-800 transform translate-y-4 group-hover:translate-y-0 transition-all duration-300"
              >
                <i class="fa-solid fa-camera mr-2"></i>Cập nhật ảnh bìa
              </button>
            </div>
          </div>

          <!-- Avatar section -->
          <div class="absolute left-1/2 -translate-x-1/2 -translate-y-1/2">
            <div class="relative group">
              <img
                :src="avatar"
                loading="lazy"
                class="w-32 h-32 object-cover rounded-full border-4 border-white shadow-lg cursor-pointer transition-all duration-300 group-hover:shadow-xl"
                @click="openPreview(avatar, 'avatar')"
              >
              <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                <button
                  v-if="!isOtherUserProfile"
                  @click.stop="openUploadModal('avatar')"
                  class="w-8 h-8 rounded-full bg-white bg-opacity-90 flex items-center justify-center text-gray-800 shadow-lg"
                >
                  <i class="fa-solid fa-camera"></i>
                </button>
              </div>
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
                <h2 class="text-2xl font-bold text-gray-800">{{ user?.name ? user.name : "Người dùng" }}</h2>
                <p class="text-gray-600">{{ user?.email ? user.email : "Email" }}</p>
                <p class="text-gray-500 mt-2">Số lượt tạo ảnh: {{ user?.remaining_credits ? user.remaining_credits : 0 }}</p>
            </div>

            <!-- Stats -->
            <div class="flex justify-center gap-8 mt-6">
                <div class="text-center">
                    <div class="text-xl font-bold bg-gradient-text-v2">{{user?.sum_like? user.sum_like : 0}}</div>
                    <div class="text-gray-500 text-sm">Lượt thích</div>
                </div>
                <div class="text-center">
                    <div class="text-xl font-bold bg-gradient-text-v2">{{ user?.sum_img? user.sum_img : 0 }}</div>
                    <div class="text-gray-500 text-sm">Số ảnh</div>
                </div>
                <div class="text-center">
                    <div class="text-xl font-bold bg-gradient-text-v2">{{ user?.created_at? dayjs(user.created_at).format('DD/MM/YYYY'): "01/01/2025" }}</div>
                    <div class="text-gray-500 text-sm">Tham gia</div>
                </div>
            </div>

            <!-- Action buttons - Chỉ hiển thị khi xem profile chính mình -->
            <div v-if="!isOtherUserProfile" class="flex justify-center gap-4 mt-6">
                <button
                  @click="openUploadModal('avatar')"
                  class="px-6 py-2 bg-gradient-text text-white rounded-full hover:opacity-90 transition-all duration-300 transform hover:scale-105"
                >
                    <i class="fa-solid fa-user-pen mr-2"></i>Cập nhật ảnh đại diện
                </button>
                <button
                  @click="openUploadModal('cover')"
                  class="px-6 py-2 border border-gray-300 text-gray-700 rounded-full hover:bg-gray-50 transition-all duration-300 transform hover:scale-105"
                >
                    <i class="fa-solid fa-image mr-2"></i>Cập nhật ảnh bìa
                </button>
            </div>

            <!-- Upload Image Modal -->
            <UploadImageModal
              :isVisible="isUploadModalVisible"
              :type="uploadImageType"
              @close="closeUploadModal"
              @upload-success="handleImageUploadSuccess"
            />

            <!-- Loading Overlay -->
            <div v-if="isLoading" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
              <div class="bg-white p-6 rounded-lg shadow-xl flex flex-col items-center">
                <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-indigo-500 mb-4"></div>
                <p class="text-gray-700 font-medium">Đang xử lý ảnh...</p>
              </div>
            </div>
        </div>
      <div class="container mx-auto px-4" data-aos="zoom-in" data-aos-delay="300">
        <!-- Xin chào -->
        <div class="bg-gradient-text rounded-lg shadow-md p-6 flex justify-between">
          <div>
            <h1 v-if="!isOtherUserProfile" class="text-2xl font-bold text-white mb-4">
              Xin chào, {{ user?.name || 'Người dùng' }}!
            </h1>
            <h1 v-else class="text-2xl font-bold text-white mb-4">
              Trang cá nhân của {{ user?.name || 'Người dùng' }}
            </h1>
            <p v-if="!isOtherUserProfile" class="text-gray-100">
              Chào mừng bạn đến với bảng điều khiển của KT_AI. Tại đây bạn có thể quản lý tài khoản và sử dụng các tính năng tạo ảnh AI.
            </p>
            <p v-else class="text-gray-100">
              Đây là trang cá nhân của {{ user?.name || 'Người dùng' }}. Bạn có thể xem ảnh và các thông tin công khai.
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
        <div v-if="!isInitialized" class="text-center py-12">
          <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-indigo-500 mx-auto mb-4"></div>
          <p class="text-gray-700">Đang tải dữ liệu...</p>
        </div>
        <template v-else>
          <ImageListVue 
            :filter="activeTab" 
            :user-id="otherUserId" 
            v-if="otherUserId !== undefined"
          />
          <ImageListVue 
            :filter="activeTab" 
            v-else
          />
        </template>
      </div>
    </div>
  </div>
</template>

<script>
import AOS from 'aos'
import ImageListVue from '@/components/user/Dashboard/ImageListLayout.vue'
import UploadImageModal from '@/components/user/Dashboard/UploadImageModal.vue'
import dayjs from 'dayjs'
import { onMounted, ref, onBeforeUnmount } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth/authStore'
import { useImageStore } from '@/stores/user/imagesStore'
import { toast } from 'vue-sonner'
import { profileAPI } from '@/services/api'

export default {
  name: 'Dashboard',
  components: {
    ImageListVue,
    UploadImageModal
  },
  setup() {
    //State
    const router = useRouter()
    const route = useRoute()
    const auth = useAuthStore()
    const imageStore = useImageStore()
    const user = ref(auth.user.value) // Chuyển sang ref để có thể cập nhật
    
    // Lấy userId từ cả route.params và route.query
    const otherUserId = ref(route.params.id || route.query.userId)

    // Chuyển đổi sang số nếu có thể
    if (otherUserId.value) {
      const parsedId = parseInt(otherUserId.value)
      if (!isNaN(parsedId)) {
        otherUserId.value = parsedId
      }
    }
    
    const isOtherUserProfile = ref(false) // Flag để biết đang xem profile người khác hay không
    const active = ref([])
    const avatar = ref(user.value.avatar_url)
    const coverImage = ref(user.value.cover_image_url)
    const logo_fun = ref("/img/humanoid.png")

    // Preview state
    const previewVisible = ref(false)
    const currentPreviewImage = ref("")
    const previewType = ref("")
    const activeTab = ref('uploaded') // Mặc định là 'uploaded'
    
    // Thêm state để kiểm soát việc render ImageListVue
    const isInitialized = ref(false)

    // Upload modal state
    const isUploadModalVisible = ref(false)
    const uploadImageType = ref('avatar')
    const isLoading = ref(false) // Trạng thái loading khi đang tải lên ảnh

    // Hàm xử lý khi refresh trang (F5) hoặc đóng trang
    const handleBeforeUnload = () => {
      imageStore.clearAllUserImages()
    }

    // Tải thông tin người dùng theo ID
    const loadUserProfile = async (userId) => {
      try {
        isLoading.value = true
        const response = await profileAPI.getUserById(userId)
        if (response.data && response.data.success) {
          // Cập nhật thông tin người dùng từ API
          user.value = response.data.data
          avatar.value = response.data.data.avatar_url
          coverImage.value = response.data.data.cover_image_url
          isOtherUserProfile.value = true
        } else {
          toast.error('Không thể tải thông tin người dùng')
        }
      } catch (error) {
        console.error('Lỗi khi tải thông tin người dùng:', error)
        toast.error('Có lỗi xảy ra khi tải thông tin người dùng')
        // Chuyển về dashboard của người dùng hiện tại
        router.push({ name: 'dashboard' })
      } finally {
        isLoading.value = false
      }
    }

    // Fetch data
    const tabs = [
      { id: 'uploaded', name: 'Ảnh tải lên' },
      { id: 'liked', name: 'Ảnh đã thích' }
    ]

    // Methods
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
      currentPreviewImage.value = imageUrl
      previewType.value = type
      previewVisible.value = true
    }

    // Open upload modal
    const openUploadModal = (type) => {
      uploadImageType.value = type;
      isUploadModalVisible.value = true
    }

    // Close upload modal
    const closeUploadModal = () => {
      isUploadModalVisible.value = false
    }

    // Handle successful image upload
    const handleImageUploadSuccess = async (data) => {
      try {
        // Bật trạng thái loading
        isLoading.value = true

        // Create form data
        const formData = new FormData()
        formData.append('image', data.file)

        // Call appropriate API based on image type
        if (data.type === 'avatar') {
          // Hiển thị thông báo đang tải
          toast.loading('Đang cập nhật ảnh đại diện...')

          const response = await profileAPI.updateAvatar(formData)
          if (response.data.success) {
            // Update local avatar
            avatar.value = response.data.data.avatar_url
            // Update auth store
            if (auth.user.value) {
              auth.user.value.avatar_url = response.data.data.avatar_url
            }
            toast.dismiss()
            toast.success('Cập nhật ảnh đại diện thành công!')
          } else {
            throw new Error(response.data.message || 'Cập nhật ảnh đại diện thất bại')
          }
        } else {
          // Hiển thị thông báo đang tải
          toast.loading('Đang cập nhật ảnh bìa...')

          const response = await profileAPI.updateCoverImage(formData)
          if (response.data.success) {
            // Update local cover image
            coverImage.value = response.data.data.cover_image_url
            // Update auth store
            if (auth.user.value) {
              auth.user.value.cover_image_url = response.data.data.cover_image_url
            }
            toast.dismiss()
            toast.success('Cập nhật ảnh bìa thành công!')
          } else {
            throw new Error(response.data.message || 'Cập nhật ảnh bìa thất bại')
          }
        }
      } catch (error) {
        // Tắt thông báo đang tải
        toast.dismiss()
        console.error('Error updating profile image:', error)
        if (error.response) {
          console.error('Error response:', error.response.data)
        }
        toast.error(error.message || 'Có lỗi xảy ra khi cập nhật ảnh. Vui lòng thử lại sau.')
      } finally {
        // Tắt trạng thái loading
        isLoading.value = false
      }
    };

    // Mounted hook
    onMounted(async () => {
      await auth.checkAuth()      
      // Kiểm tra nếu có userId trong params hoặc query parameter
      if (otherUserId.value && otherUserId.value !== auth.user.value.id.toString()) {
        await loadUserProfile(otherUserId.value)
      } else {
        user.value = auth.user.value
        avatar.value = user.value.avatar_url
        coverImage.value = user.value.cover_image_url
      }
      
      // Đánh dấu đã khởi tạo xong để render ImageListVue
      isInitialized.value = true
      
      setTimeout(() => {
        AOS.refresh()
      }, 100)

      // Thêm sự kiện khi refresh trang (F5)
      window.addEventListener('beforeunload', handleBeforeUnload)
    })

    // Before unmount hook
    onBeforeUnmount(() => {
      // Xóa dữ liệu trong store khi component bị hủy
      imageStore.clearAllUserImages()
      
      // Xóa event listener
      window.removeEventListener('beforeunload', handleBeforeUnload)
    })

    return {
      user,
      dayjs,
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
      tabs,
      // Upload modal
      isUploadModalVisible,
      uploadImageType,
      openUploadModal,
      closeUploadModal,
      handleImageUploadSuccess,
      isLoading,
      isOtherUserProfile,
      otherUserId,
      isInitialized
    }
  }
}
</script>