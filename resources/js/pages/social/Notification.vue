<template>
  <div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-purple-50">
    <div class="container mx-auto py-8 px-4 md:px-6">
      <!-- Header với gradient -->
      <div class="mb-8">        
        <div class="text-center mb-8">
          <h1 class="text-4xl h-[50px] font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent mb-2">
            Thông báo của bạn
          </h1>
          <p class="text-gray-600 text-lg">Cập nhật những hoạt động mới nhất</p>
        </div>
      </div>
      
      <div class="max-w-6xl mx-auto">
        <!-- Bộ lọc với thiết kế mới -->
        <div class="mb-8 bg-white rounded-3xl shadow-xl p-8 border border-gray-100">
          <div class="flex flex-wrap gap-4 items-center">
            <!-- Nút quay lại -->
            <div class="flex gap-4 items-center">
              <ButtonBack customClass="bg-gradient-text hover:from-blue-700 hover:to-purple-800 text-white font-semibold py-3 px-7 rounded-full shadow-xl transition-all duration-200 hover:scale-105"/>
            </div>
            <!-- Bộ lọc -->
            <div class="flex gap-2 items-center bg-gray-50 rounded-full px-2 py-1 shadow-inner border border-gray-200">
              <button 
                @click="currentFilter = 'all'"
                :class="[
                  'flex items-center gap-2 px-6 py-2 text-base font-semibold rounded-full transition-all duration-200',
                  currentFilter === 'all' 
                    ? 'bg-gradient-to-r from-blue-500 to-purple-600 text-white shadow-lg scale-105' 
                    : 'bg-white text-gray-700 hover:bg-blue-50 hover:shadow-md'
                ]"
              >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                </svg>
                <span>Tất cả</span>
              </button>
              <button 
                @click="currentFilter = 'unread'"
                :class="[
                  'flex items-center gap-2 px-6 py-2 text-base font-semibold rounded-full transition-all duration-200',
                  currentFilter === 'unread' 
                    ? 'bg-gradient-to-r from-pink-500 to-purple-600 text-white shadow-lg scale-105' 
                    : 'bg-white text-gray-700 hover:bg-purple-50 hover:shadow-md'
                ]"
              >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                </svg>
                <span>Chưa đọc</span>
                <span v-if="unreadCount > 0" class="bg-red-500 text-white text-xs rounded-full px-2 py-0.5 min-w-[20px] text-center font-bold shadow">
                  {{ unreadCount }}
                </span>
              </button>
            </div>
            <div class="flex-grow"></div>
            <!-- Nút đánh dấu đã đọc -->
            <transition name="fade">
              <button 
                v-if="unreadCount > 0"
                @click="markAllAsRead"
                class="flex items-center gap-2 px-6 py-2 text-base font-semibold bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 text-white rounded-full shadow-xl transition-all duration-200 hover:scale-105"
              >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <span>Đánh dấu tất cả đã đọc</span>
              </button>
            </transition>
          </div>
        </div>
        
        <!-- Loading state -->
        <div v-if="loading && !isLoadingMore" class="py-12 text-center">
          <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-r from-blue-500 to-purple-600 rounded-full">
            <svg class="animate-spin h-8 w-8 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
          </div>
          <p class="mt-4 text-gray-600 font-medium">Đang tải thông báo...</p>
        </div>
        
        <!-- Empty state -->
        <div v-else-if="filteredNotifications.length === 0" class="py-16 text-center bg-white rounded-2xl shadow-lg border border-gray-100">
          <div class="inline-flex items-center justify-center w-20 h-20 bg-gradient-to-r from-blue-100 to-purple-100 rounded-full mb-6">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
            </svg>
          </div>
          <h3 class="text-xl font-semibold text-gray-800 mb-2">Không có thông báo nào</h3>
          <p class="text-gray-600 mb-4">Bạn sẽ nhận được thông báo khi có người thích ảnh của bạn hoặc bình luận về hoạt động của bạn</p>
          <div class="inline-flex items-center gap-2 text-sm text-gray-500">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            Thông báo sẽ xuất hiện ở đây
          </div>
        </div>
        
        <!-- Danh sách thông báo -->
        <div v-else class="space-y-4">
          <div 
            v-for="(notification, index) in filteredNotifications" 
            :key="notification.id"
            :class="{
              'bg-gradient-to-r from-blue-50 to-purple-50 border-blue-200': !notification.read_at,
              'bg-white border-gray-200': notification.read_at
            }"
            class="p-6 border-2 rounded-2xl hover:shadow-lg cursor-pointer transition-all duration-300 transform hover:scale-[1.02] hover:border-blue-300"
            :style="{ animationDelay: `${index * 100}ms` }"
            @click="viewNotification(notification)"
          >
            <div class="flex items-start space-x-4">
              <!-- Avatar -->
              <div v-if="notification.data && notification.data.liker_avatar" class="flex-shrink-0">
                <div class="relative">
                  <img 
                    :src="notification.data.liker_avatar" 
                    alt="avatar"
                    class="h-14 w-14 rounded-full object-cover border-2 border-white shadow-md"
                  />
                  <div v-if="!notification.read_at" class="absolute -top-1 -right-1 w-4 h-4 bg-red-500 rounded-full border-2 border-white animate-pulse"></div>
                </div>
              </div>
              <div v-else class="flex-shrink-0 relative">
                <div class="h-14 w-14 rounded-full bg-gradient-to-r from-gray-200 to-gray-300 flex items-center justify-center shadow-md">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                  </svg>
                </div>
                <div v-if="!notification.read_at" class="absolute -top-1 -right-1 w-4 h-4 bg-red-500 rounded-full border-2 border-white animate-pulse"></div>
              </div>
              
              <!-- Content -->
              <div class="flex-1 min-w-0">
                <div class="flex items-start justify-between">
                  <div class="flex-1">
                    <p class="text-base text-gray-800 whitespace-normal break-words font-medium leading-relaxed">
                      {{ notification.data && notification.data.message ? notification.data.message : 'Thông báo mới' }}
                    </p>
                    
                    <!-- Thông tin bổ sung -->
                    <div v-if="notification.data" class="mt-2 flex items-center gap-2">
                      <span
                        v-if="notification.type || notification.data.type"
                        class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium"
                        :class="{
                          // Thích ảnh: nền đỏ nhạt, chữ đỏ
                          'bg-red-100 text-red-700': (notification.type && (notification.type === 'like_image' || notification.type.includes('LikeImage') || notification.type.includes('like'))) || (notification.data.type && (notification.data.type === 'like_image' || notification.data.type.includes('LikeImage') || notification.data.type.includes('like'))),
                          // Bình luận: nền xanh lá nhạt, chữ xanh lá
                          'bg-green-100 text-green-800': (notification.type && (notification.type === 'add_comment' || notification.type.includes('AddComment') || notification.type.includes('comment'))) || (notification.data.type && (notification.data.type === 'add_comment' || notification.data.type.includes('AddComment') || notification.data.type.includes('comment'))),
                          // Trả lời: nền tím nhạt, chữ tím
                          'bg-purple-100 text-purple-800': (notification.type && (notification.type === 'add_reply' || notification.type.includes('AddReply') || notification.type.includes('reply'))) || (notification.data.type && (notification.data.type === 'add_reply' || notification.data.type.includes('AddReply') || notification.data.type.includes('reply'))),
                          // Ảnh đã tạo: nền cam nhạt, chữ cam
                          'bg-orange-100 text-orange-800': (notification.type && (notification.type === 'image_generated' || notification.type.includes('ImageGenerated') || notification.type.includes('generated'))) || (notification.data.type && (notification.data.type === 'image_generated' || notification.data.type.includes('ImageGenerated') || notification.data.type.includes('generated'))),
                          // Ảnh hoàn thành: nền xanh ngọc nhạt, chữ xanh ngọc
                          'bg-emerald-100 text-emerald-800': (notification.type && (notification.type.includes('ImageJobCompleted') || notification.type.includes('completed'))) || (notification.data.type && (notification.data.type.includes('ImageJobCompleted') || notification.data.type.includes('completed'))),
                          // Ảnh thất bại: nền đỏ nhạt, chữ đỏ
                          'bg-red-100 text-red-800': (notification.type && (notification.type.includes('ImageJobFailed') || notification.type.includes('failed'))) || (notification.data.type && (notification.data.type.includes('ImageJobFailed') || notification.data.type.includes('failed'))),
                          // fallback
                          'bg-gray-100 text-gray-800': true
                        }"
                      >
                        <!-- Thích ảnh: icon màu đỏ -->
                        <svg
                          v-if="(notification.type && (notification.type === 'like_image' || notification.type.includes('LikeImage') || notification.type.includes('like'))) || (notification.data.type && (notification.data.type === 'like_image' || notification.data.type.includes('LikeImage') || notification.data.type.includes('like')))"
                          xmlns="http://www.w3.org/2000/svg"
                          class="h-3 w-3 mr-1 text-red-600"
                          fill="currentColor"
                          viewBox="0 0 24 24"
                        >
                          <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                        </svg>
                        <!-- Bình luận: icon màu xanh lá -->
                        <svg
                          v-else-if="(notification.type && (notification.type === 'add_comment' || notification.type.includes('AddComment') || notification.type.includes('comment'))) || (notification.data.type && (notification.data.type === 'add_comment' || notification.data.type.includes('AddComment') || notification.data.type.includes('comment')))"
                          xmlns="http://www.w3.org/2000/svg"
                          class="h-3 w-3 mr-1 text-green-600"
                          fill="none"
                          viewBox="0 0 24 24"
                          stroke="currentColor"
                        >
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                        </svg>
                        <!-- Trả lời: icon màu tím -->
                        <svg
                          v-else-if="(notification.type && (notification.type === 'add_reply' || notification.type.includes('AddReply') || notification.type.includes('reply'))) || (notification.data.type && (notification.data.type === 'add_reply' || notification.data.type.includes('AddReply') || notification.data.type.includes('reply')))"
                          xmlns="http://www.w3.org/2000/svg"
                          class="h-3 w-3 mr-1 text-purple-600"
                          fill="none"
                          viewBox="0 0 24 24"
                          stroke="currentColor"
                        >
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
                        </svg>
                        <!-- Ảnh đã tạo/hoàn thành: icon màu cam hoặc xanh ngọc -->
                        <svg
                          v-else-if="(notification.type && (notification.type === 'image_generated' || notification.type.includes('ImageGenerated') || notification.type.includes('generated') || notification.type.includes('ImageJobCompleted') || notification.type.includes('completed'))) || (notification.data.type && (notification.data.type === 'image_generated' || notification.data.type.includes('ImageGenerated') || notification.data.type.includes('generated') || notification.data.type.includes('ImageJobCompleted') || notification.data.type.includes('completed')))"
                          xmlns="http://www.w3.org/2000/svg"
                          class="h-3 w-3 mr-1 text-orange-500"
                          fill="none"
                          viewBox="0 0 24 24"
                          stroke="currentColor"
                        >
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <!-- Ảnh thất bại: icon màu đỏ -->
                        <svg
                          v-else-if="(notification.type && (notification.type.includes('ImageJobFailed') || notification.type.includes('failed'))) || (notification.data.type && (notification.data.type.includes('ImageJobFailed') || notification.data.type.includes('failed')))"
                          xmlns="http://www.w3.org/2000/svg"
                          class="h-3 w-3 mr-1 text-red-600"
                          fill="none"
                          viewBox="0 0 24 24"
                          stroke="currentColor"
                        >
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <!-- fallback: icon xám -->
                        <svg
                          v-else
                          xmlns="http://www.w3.org/2000/svg"
                          class="h-3 w-3 mr-1 text-gray-400"
                          fill="none"
                          viewBox="0 0 24 24"
                          stroke="currentColor"
                        >
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                        {{ getNotificationTypeText(notification.type || notification.data.type) }}
                      </span>
                    </div>
                  </div>
                  
                  <!-- Time và status -->
                  <div class="flex flex-col items-end gap-2">
                    <p class="text-sm text-gray-500 flex items-center gap-2">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                      </svg>
                      {{ formatTime(notification.created_at) }}
                    </p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Nút Xem thêm -->
        <div class="mt-8">
          <ButtonMore 
            v-if="hasMorePages" 
            :has-more-pages="hasMorePages" 
            :loading="loading" 
            @load-more="handleLoadMore"
          />
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import { useNotifications } from '@/composables/features/social/useNotifications'
import { formatTimev2 } from '@/utils'
import useImage from '@/composables/features/images/useImage'
import { ButtonBack, ButtonMore } from '@/components/base'

export default {
  name: 'NotificationsPage',
  components: {
    ButtonBack,
    ButtonMore
  },
  setup() {
    const router = useRouter()
    const currentFilter = ref('all')
    const isLoadingMore = ref(false)
    const {
      goToImageDetail
    } = useImage()
    const { 
      notifications, 
      unreadCount, 
      loading, 
      hasMorePages, 
      fetchNotifications, 
      loadMoreNotifications, 
      markAsRead, 
      markAllAsRead 
    } = useNotifications(true) // true để kích hoạt chế độ tải thêm
    
    // filteredNotifications trả về danh sách từ API (đã được lọc/phân trang bởi backend)
    const filteredNotifications = computed(() => notifications.value)
    
    // Xử lý tải thêm thông báo
    const handleLoadMore = async () => {
      if (loading.value || !hasMorePages.value) return
      
      isLoadingMore.value = true
      await loadMoreNotifications(currentFilter.value)
      isLoadingMore.value = false
    }
    
    // Xem chi tiết thông báo
    const viewNotification = async (notification) => {
      // Đánh dấu là đã đọc nếu chưa
      if (!notification.read_at) {
        await markAsRead(notification.id)
      }
      
      // Xử lý điều hướng dựa vào loại thông báo
      if (notification.data && notification.data.image_id) {
        const type = notification.data.type
        const shouldNavigate = 
          type === 'like_image' || 
          type === 'add_comment' ||
          type.includes('LikeImage') ||
          type.includes('AddComment') ||
          type.includes('AddReply') ||
          type.includes('ImageGenerated') ||
          type.includes('ImageJobCompleted') ||
          type.includes('ImageJobFailed')
        
        if (shouldNavigate) {
          const convertInt = parseInt(notification.data.image_id)
          goToImageDetail(convertInt)
        }
      }
    }
    
    // Format thời gian
    const formatTime = (timestamp) => formatTimev2(timestamp)
    
    // Lấy text cho loại thông báo
    const getNotificationTypeText = (type) => {
      // Xử lý type dạng "App\\Notifications\\ImageGeneratedNotification"
      let cleanType = type
      
      // Nếu type chứa namespace, extract tên class cuối cùng
      if (type.includes('\\')) {
        cleanType = type.split('\\').pop()
      }
      
      // Map các type phổ biến
      const typeMap = {
        // Các type đơn giản
        'like_image': 'Thích ảnh',
        'add_comment': 'Bình luận', 
        'add_reply': 'Trả lời',
        'image_generated': 'Ảnh đã tạo',
        
        // Các type từ class names
        'ImageGeneratedNotification': 'Ảnh đã tạo',
        'AddCommentNotification': 'Bình luận mới',
        'AddReplyNotification': 'Trả lời mới',
        'LikeImageNotification': 'Thích ảnh',
        'ImageJobCompletedNotification': 'Ảnh đã hoàn thành',
        'ImageJobFailedNotification': 'Ảnh tạo thất bại',
        
        // Fallback cho các type khác
        'default': 'Thông báo mới'
      }
      
      // Kiểm tra nếu type chứa các từ khóa
      const lowerType = cleanType.toLowerCase()
      
      if (lowerType.includes('like') || lowerType.includes('thích')) {
        return 'Thích ảnh'
      }
      if (lowerType.includes('comment') || lowerType.includes('bình luận')) {
        return 'Bình luận mới'
      }
      if (lowerType.includes('reply') || lowerType.includes('trả lời')) {
        return 'Trả lời mới'
      }
      if (lowerType.includes('generated') || lowerType.includes('completed') || lowerType.includes('hoàn thành')) {
        return 'Ảnh đã tạo'
      }
      if (lowerType.includes('failed') || lowerType.includes('thất bại')) {
        return 'Ảnh tạo thất bại'
      }
      
      return typeMap[cleanType] || typeMap['default']
    }
    
    // Lấy thông báo khi component được mount
    onMounted(async () => {
      await fetchNotifications()
    })
    
    // Cập nhật lại danh sách khi thay đổi bộ lọc
    watch(currentFilter, (newFilter) => {
      // Gọi fetchNotifications với trang 1 và bộ lọc mới, không append
      fetchNotifications(1, newFilter, false)
    })
    
    return {
      notifications,
      filteredNotifications,
      unreadCount,
      loading,
      currentFilter,
      hasMorePages,
      isLoadingMore,
      viewNotification,
      formatTime,
      getNotificationTypeText,
      markAllAsRead,
      loadMoreNotifications,
      handleLoadMore
    }
  }
}
</script>

<style scoped>
/* Animation cho các notification items */
.notification-item {
  animation: slideInUp 0.5s ease-out forwards;
  opacity: 0;
  transform: translateY(20px);
}

@keyframes slideInUp {
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Hover effects */
.hover-lift {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.hover-lift:hover {
  transform: translateY(-2px);
  box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
}
</style>