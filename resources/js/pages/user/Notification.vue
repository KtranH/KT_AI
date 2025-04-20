<template>
  <div class="container mx-auto py-8 px-4 md:px-6">
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
              ? 'bg-blue-500 text-white' 
              : 'bg-gray-200 text-gray-700 hover:bg-gray-300'
          ]"
        >
          Chưa đọc
        </button>
        <div class="flex-grow"></div>
        <button 
          v-if="unreadCount > 0"
          @click="markAllAsRead"
          class="px-4 py-2 text-sm text-blue-600 hover:text-blue-800"
        >
          Đánh dấu tất cả đã đọc
        </button>
      </div>
      
      <!-- Danh sách thông báo -->
      <div v-if="loading" class="py-8 text-center text-gray-500">
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
            <div v-if="notification.data.liker_avatar" class="flex-shrink-0">
              <img 
                :src="notification.data.liker_avatar" 
                alt="avatar"
                class="h-12 w-12 rounded-full object-cover"
              />
            </div>
            <div v-else class="flex-shrink-0 h-12 w-12 rounded-full bg-gray-200 flex items-center justify-center">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
              </svg>
            </div>
            <div class="flex-1 min-w-0">
              <p class="text-base text-gray-800 whitespace-normal break-words">
                {{ notification.data.message }}
              </p>
              <p class="text-sm text-gray-500 mt-1 flex items-center">
                {{ formatTime(notification.created_at) }}
                <span v-if="!notification.read_at" class="ml-2 inline-block w-2 h-2 bg-blue-500 rounded-full"></span>
              </p>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Phân trang -->
      <div v-if="filteredNotifications.length > 0 && pagination.last_page > 1" class="mt-8 flex justify-center">
        <div class="flex space-x-1">
          <button 
            @click="changePage(pagination.current_page - 1)" 
            :disabled="pagination.current_page === 1"
            :class="[
              'px-4 py-2 rounded-md',
              pagination.current_page === 1 
                ? 'bg-gray-100 text-gray-400 cursor-not-allowed' 
                : 'bg-gray-200 text-gray-700 hover:bg-gray-300'
            ]"
          >
            &laquo; Trước
          </button>
          
          <button 
            v-for="page in pageNumbers" 
            :key="page"
            @click="changePage(page)"
            :class="[
              'px-4 py-2 rounded-md',
              pagination.current_page === page 
                ? 'bg-blue-500 text-white' 
                : 'bg-gray-200 text-gray-700 hover:bg-gray-300'
            ]"
          >
            {{ page }}
          </button>
          
          <button 
            @click="changePage(pagination.current_page + 1)" 
            :disabled="pagination.current_page === pagination.last_page"
            :class="[
              'px-4 py-2 rounded-md',
              pagination.current_page === pagination.last_page 
                ? 'bg-gray-100 text-gray-400 cursor-not-allowed' 
                : 'bg-gray-200 text-gray-700 hover:bg-gray-300'
            ]"
          >
            Sau &raquo;
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import { useNotifications } from '@/composables/user/useNotifications'
import useImage from '@/composables/user/useImage'
import dayjs from 'dayjs'
import relativeTime from 'dayjs/plugin/relativeTime'
import 'dayjs/locale/vi'

dayjs.locale('vi')
dayjs.extend(relativeTime)

export default {
  name: 'NotificationsPage',
  
  setup() {
    const router = useRouter()
    const currentFilter = ref('all')
    const {
      goToImageDetail
    } = useImage()
    const { 
      notifications, 
      unreadCount, 
      loading, 
      pagination,
      fetchNotifications, 
      markAsRead, 
      markAllAsRead 
    } = useNotifications(true) // true để lấy tất cả thông báo với phân trang
    
    // Lọc thông báo theo trạng thái
    const filteredNotifications = computed(() => {
      if (currentFilter.value === 'unread') {
        return notifications.value.filter(notification => !notification.read_at)
      }
      return notifications.value
    })
    
    // Tính toán các số trang cần hiển thị
    const pageNumbers = computed(() => {
      const currentPage = pagination.value.current_page
      const lastPage = pagination.value.last_page
      const pages = []
      
      // Luôn hiển thị trang đầu, trang hiện tại và trang cuối
      // Cộng thêm 1 trang trước và 1 trang sau trang hiện tại
      
      for (let i = 1; i <= lastPage; i++) {
        if (
          i === 1 || // Trang đầu
          i === lastPage || // Trang cuối
          (i >= currentPage - 1 && i <= currentPage + 1) // Trang hiện tại và lân cận
        ) {
          pages.push(i)
        }
      }
      
      return pages
    })
    
    // Đổi trang
    const changePage = (page) => {
      if (page < 1 || page > pagination.value.last_page) return
      fetchNotifications(page)
    }
    
    // Xem chi tiết thông báo
    const viewNotification = async (notification) => {
      // Đánh dấu là đã đọc nếu chưa
      if (!notification.read_at) {
        await markAsRead(notification.id)
      }
      
      // Xử lý điều hướng dựa vào loại thông báo
      if (notification.data.type === 'like_image' && notification.data.image_id) {
        const convertInt = parseInt(notification.data.image_id)
        goToImageDetail(convertInt)
      }
    }
    
    // Format thời gian
    const formatTime = (timestamp) => {
      const date = dayjs(timestamp)
      
      // Nếu thông báo trong vòng 24 giờ, hiển thị "x phút/giờ trước"
      // Nếu không, hiển thị ngày tháng đầy đủ
      if (dayjs().diff(date, 'day') < 1) {
        return date.fromNow()
      } else {
        return date.format('HH:mm - DD/MM/YYYY')
      }
    }
    
    // Lấy thông báo khi component được mount
    onMounted(async () => {
      await fetchNotifications()
    })
    
    // Cập nhật lại danh sách khi thay đổi bộ lọc
    watch(currentFilter, () => {
      fetchNotifications(1) // Reset về trang 1 khi đổi bộ lọc
    })
    
    return {
      notifications,
      filteredNotifications,
      unreadCount,
      loading,
      currentFilter,
      pagination,
      pageNumbers,
      viewNotification,
      formatTime,
      markAllAsRead,
      changePage
    }
  }
}
</script> 