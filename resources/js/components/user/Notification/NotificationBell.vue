<template>
  <div class="relative">
    <!-- Bell Icon with Notification Counter -->
    <button
      class="relative p-2 text-gray-600 hover:text-gray-800 focus:outline-none"
      @click="toggleNotificationPanel"
    >
      <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
      </svg>
      <span
        v-if="unreadCount > 0"
        class="absolute top-0 right-0 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white transform translate-x-1/2 -translate-y-1/2 bg-red-500 rounded-full"
      >
        {{ unreadCount > 99 ? '99+' : unreadCount }}
      </span>
    </button>

    <!-- Notification Panel -->
    <div
      v-if="isOpen"
      class="absolute right-0 mt-2 w-80 bg-white rounded-md shadow-lg overflow-hidden z-50"
      style="max-height: 32rem;"
    >
      <div class="py-2 px-3 bg-gray-100 flex justify-between items-center">
        <h3 class="text-sm font-medium text-gray-700">Thông báo</h3>
        <button
          v-if="unreadCount > 0"
          @click="markAllAsRead"
          class="text-xs bg-gradient-text-v2 hover:text-blue-700"
        >
          Đánh dấu tất cả đã đọc
        </button>
      </div>

      <div v-if="loading" class="py-4 px-3 text-center text-gray-500">
        <svg class="animate-spin h-5 w-5 mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
      </div>

      <div v-else-if="notifications.length === 0" class="py-4 px-3 text-center text-gray-500">
        Không có thông báo nào
      </div>

      <div v-else class="max-h-96 overflow-y-auto">
        <div
          v-for="notification in notifications"
          :key="notification.id"
          :class="{'bg-blue-50': !notification.read_at}"
          class="p-3 border-b hover:bg-gray-50 cursor-pointer transition duration-150 ease-in-out"
          @click="viewNotification(notification)"
        >
          <div class="flex items-start space-x-3">
            <div v-if="notification.data.liker_avatar" class="flex-shrink-0">
              <img
                :src="notification.data.liker_avatar"
                alt="avatar"
                class="h-10 w-10 rounded-full object-cover"
              />
            </div>
            <div v-else class="flex-shrink-0 h-10 w-10 rounded-full bg-gray-200 flex items-center justify-center">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
              </svg>
            </div>
            <div class="flex-1 min-w-0">
              <p class="text-sm text-gray-800 whitespace-normal break-words truncate">
                {{ notification.data.message }}
              </p>
              <p class="text-xs text-gray-500 mt-1">
                {{ formatTime(notification.created_at) }}
              </p>
            </div>
          </div>
        </div>
      </div>

      <div v-if="notifications.length > 0" class="py-2 px-3 bg-gray-100 text-center">
        <router-link
          to="/notifications"
          class="text-sm bg-gradient-text-v2 hover:text-blue-700 flex items-center justify-center"
          @click="isOpen = false"
        >
          Xem tất cả thông báo
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
          </svg>
        </router-link>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted, onUnmounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import { useNotifications } from '@/composables/user/useNotifications'
import { encodedID } from '@/utils'
import dayjs from 'dayjs'
import relativeTime from 'dayjs/plugin/relativeTime'
import 'dayjs/locale/vi'

dayjs.locale('vi')
dayjs.extend(relativeTime)

export default {
  name: 'NotificationBell',

  setup() {
    const isOpen = ref(false)
    const router = useRouter()
    const { notifications, unreadCount, loading, fetchNotifications, markAsRead, markAllAsRead } = useNotifications()

    // Click outside để đóng panel
    const handleClickOutside = (event) => {
      const notificationPanel = document.querySelector('.notification-panel')
      if (notificationPanel && !notificationPanel.contains(event.target) && !event.target.closest('button[aria-label="notifications"]')) {
        isOpen.value = false
      }
    }
    // Toggle panel thông báo
    const toggleNotificationPanel = () => {
      isOpen.value = !isOpen.value
      if (isOpen.value) {
        fetchNotifications()
      }
    }

    // Xem chi tiết thông báo
    const viewNotification = async (notification) => {
      // Đánh dấu là đã đọc nếu chưa
      if (!notification.read_at) {
        await markAsRead(notification.id)
      }

      // Xử lý điều hướng dựa vào loại thông báo
      if (notification.data.image_id) {
        let route = `/image/detail/${encodedID(notification.data.image_id)}`;
        let needHighlight = false;
        let commentId = null;

        // Thích ảnh
        if (notification.data.type === 'like_image') {
          // Không cần thêm tham số
        }
        // Bình luận mới
        else if (notification.data.type === 'add_comment' && notification.data.comment_id) {
          commentId = notification.data.comment_id;
          needHighlight = true;
        }
        // Phản hồi bình luận
        else if (notification.data.type === 'add_reply' && notification.data.comment_id) {
          // Nếu có origin_comment, ưu tiên highlight bình luận gốc
          if (notification.data.origin_comment) {
            commentId = notification.data.origin_comment;
          } else {
            commentId = notification.data.comment_id;
          }
          needHighlight = true;
        }
        // Thích bình luận
        else if (notification.data.type === 'like_comment' && notification.data.comment_id) {
          // Nếu có origin_comment, ưu tiên highlight bình luận gốc
          if (notification.data.origin_comment) {
            commentId = notification.data.origin_comment;
          } else {
            commentId = notification.data.comment_id;
          }
          needHighlight = true;
        }

        // Thêm tham số vào URL nếu cần
        if (needHighlight && commentId) {
          route += `?comment=${commentId}&highlight=true`;
        }

        router.push(route);
        isOpen.value = false;
      }
    }

    // Format thời gian
    const formatTime = (timestamp) => {
      return dayjs(timestamp).fromNow()
    }

    // Thiết lập event listener khi component được mounted
    onMounted(() => {
      document.addEventListener('click', handleClickOutside)
      fetchNotifications()
    })

    // Cleanup khi unmounted
    onUnmounted(() => {
      document.removeEventListener('click', handleClickOutside)
    })

    // Theo dõi sự thay đổi của route để đóng panel khi chuyển trang
    watch(() => router.currentRoute.value, () => {
      isOpen.value = false
    })

    return {
      isOpen,
      notifications,
      unreadCount,
      loading,
      toggleNotificationPanel,
      viewNotification,
      formatTime,
      markAllAsRead,
    }
  }
}
</script>