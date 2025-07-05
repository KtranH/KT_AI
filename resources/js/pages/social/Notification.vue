<template>
  <div class="container mx-auto py-8 px-4 md:px-6">
    <ButtonBack customClass="bg-gradient-text hover: text-white font-bold py-2 px-4 mb-8 rounded-full"/>
    <div class="max-w-4xl mx-auto">
      <h1 class="text-2xl font-bold text-gray-800 mb-6">Thông báo của bạn</h1>
      
      <!-- Bộ lọc -->
      <div class="mb-6 flex flex-wrap gap-3">
        <button 
          @click="currentFilter = 'all'"
          :class="[
            'px-4 py-2 text-sm rounded-md',
            currentFilter === 'all' 
              ? 'bg-gradient-text text-white' 
              : 'bg-gray-200 text-gray-700 hover:bg-gray-300'
          ]"
        >
          Tất cả
        </button>
        <button 
          @click="currentFilter = 'unread'"
          :class="[
            'px-4 py-2 text-sm rounded-md',
            currentFilter === 'unread' 
              ? 'bg-gradient-text text-white' 
              : 'bg-gray-200 text-gray-700 hover:bg-gray-300'
          ]"
        >
          Chưa đọc
        </button>
        <div class="flex-grow"></div>
        <button 
          v-if="unreadCount > 0"
          @click="markAllAsRead"
          class="px-4 py-2 text-sm bg-gradient-text-v2 hover:text-blue-800"
        >
          Đánh dấu tất cả đã đọc
        </button>
      </div>
      
      <!-- Danh sách thông báo -->
      <div v-if="loading && !isLoadingMore" class="py-8 text-center text-gray-500">
        <svg class="animate-spin h-8 w-8 mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
      </div>
      
      <div v-else-if="filteredNotifications.length === 0" class="py-12 text-center bg-gray-50 rounded-lg">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
        </svg>
        <p class="text-gray-600 text-lg">Không có thông báo nào</p>
        <p class="text-gray-500 mt-2">Bạn sẽ nhận được thông báo khi có người thích ảnh của bạn hoặc bình luận về hoạt động của bạn</p>
      </div>
      
      <div v-else class="space-y-4">
        <div 
          v-for="notification in filteredNotifications" 
          :key="notification.id"
          :class="{'bg-blue-50': !notification.read_at}"
          class="p-4 border rounded-lg hover:bg-gray-50 cursor-pointer transition duration-150 ease-in-out"
          @click="viewNotification(notification)"
        >
          <div class="flex items-start space-x-4">
            <div v-if="notification.data && notification.data.liker_avatar" class="flex-shrink-0">
              <img 
                :src="notification.data.liker_avatar" 
                alt="avatar"
                class="h-12 w-12 rounded-full object-cover"
              />
            </div>
            <div v-else class="flex-shrink-0 h-12 w-12 rounded-full bg-gray-200 flex items-center justify-center">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
              </svg>
            </div>
            <div class="flex-1 min-w-0">
              <p class="text-base text-gray-800 whitespace-normal break-words">
                {{ notification.data && notification.data.message ? notification.data.message : 'Thông báo mới' }}
              </p>
              <p class="text-sm text-gray-500 mt-1 flex items-center">
                {{ formatTime(notification.created_at) }}
                <span v-if="!notification.read_at" class="ml-2 inline-block w-2 h-2 bg-blue-500 rounded-full"></span>
              </p>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Nút Xem thêm -->
      <ButtonMore 
        v-if="hasMorePages" 
        :has-more-pages="hasMorePages" 
        :loading="loading" 
        @load-more="handleLoadMore"
      />
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import { useNotifications } from '@/composables/core/useNotifications'
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
      if (notification.data && notification.data.image_id && (notification.data.type === 'like_image' || notification.data.type === 'add_comment')) {
        const convertInt = parseInt(notification.data.image_id)
        goToImageDetail(convertInt)
      }
    }
    
    // Format thời gian
    const formatTime = (timestamp) => formatTimev2(timestamp)
    
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
      markAllAsRead,
      loadMoreNotifications,
      handleLoadMore
    }
  }
}
</script>