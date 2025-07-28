<template>
  <header 
    :class="[
      'fixed top-0 left-0 right-0 z-50 transition-all duration-300',
      isHomePage 
        ? 'bg-transparent shadow-none border-none' 
        : 'bg-white shadow-sm border-b border-gray-200'
    ]"
  >
    <div class="container mx-auto px-4 lg:px-6">
      <div class="flex items-center justify-end h-16">
        <!-- Notification Bell -->
        <div class="relative mr-4">
          <button 
            @click="toggleNotifications"
            :class="[
              'p-2 rounded-lg transition-all duration-200 group relative',
              isHomePage 
                ? 'text-white hover:text-gray-200 hover:bg-white/10' 
                : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100'
            ]"
          >
            <!-- SVG chuông thông báo -->
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 15V11a6 6 0 10-12 0v4c0 .386-.146.735-.405 1.005L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
            </svg>
            <span 
              v-if="unreadCount > 0"
              class="absolute -top-2 -right-2 bg-red-500 text-white text-xs font-bold px-1.5 py-0.5 rounded-full animate-pulse"
            >
              {{ unreadCount }}
            </span>
          </button>

          <!-- Notification Panel -->
          <div
            v-if="isNotificationsOpen"
            class="absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-lg overflow-hidden z-50 border border-gray-200"
            style="max-height: 32rem;"
          >
            <div class="py-3 px-4 bg-gray-50 flex justify-between items-center border-b border-gray-200">
              <h3 class="text-sm font-medium text-gray-700">Thông báo</h3>
              <button
                v-if="unreadCount > 0"
                @click="markAllAsRead"
                class="text-xs text-blue-600 hover:text-blue-700 font-medium"
              >
                Đánh dấu tất cả đã đọc
              </button>
            </div>

            <div v-if="loading" class="py-8 px-4 text-center text-gray-500">
              <svg class="animate-spin h-6 w-6 mx-auto mb-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              <p class="text-sm">Đang tải...</p>
            </div>

            <div v-else-if="notifications.length === 0" class="py-8 px-4 text-center text-gray-500">
              <svg class="h-12 w-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
              </svg>
              <p class="text-sm">Không có thông báo nào</p>
            </div>

            <div v-else class="max-h-96 overflow-y-auto">
              <div
                v-for="notification in notifications"
                :key="notification.id"
                :class="{'bg-blue-50': !notification.read_at}"
                class="p-4 border-b border-gray-100 hover:bg-gray-50 cursor-pointer transition duration-150 ease-in-out"
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
                    <p class="text-sm text-gray-800 whitespace-normal break-words">
                      {{ notification.data.message }}
                    </p>
                    <p class="text-xs text-gray-500 mt-1">
                      {{ formatTime(notification.created_at) }}
                    </p>
                  </div>
                </div>
              </div>
            </div>

            <div v-if="notifications.length > 0" class="py-3 px-4 bg-gray-50 text-center border-t border-gray-200">
              <router-link
                to="/notifications"
                class="text-sm text-blue-600 hover:text-blue-700 font-medium flex items-center justify-center"
                @click="isNotificationsOpen = false"
              >
                Xem tất cả thông báo
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
              </router-link>
            </div>
          </div>
        </div>

        <!-- User Menu -->
        <div class="relative" v-click-outside="closeUserMenu">
          <button 
            @click="toggleUserMenu"
            :class="[
              'flex items-center space-x-3 p-2 rounded-lg transition-all duration-200 group',
              isHomePage 
                ? 'hover:bg-white/10' 
                : 'hover:bg-gray-100'
            ]"
          >
            <div class="relative">
              <img 
                :src="user.avatar_url || '/img/default-avatar.png'" 
                alt="User avatar"
                class="h-10 w-10 rounded-lg object-cover"
              >
              <div class="absolute -bottom-1 -right-1 w-3 h-3 bg-green-400 rounded-full border-2 border-white"></div>
            </div>
            <div class="text-left">
              <p :class="[
                'text-sm font-medium',
                isHomePage ? 'text-white' : 'text-gray-900'
              ]">{{ user.name }}</p>
              <p :class="[
                'text-xs',
                isHomePage ? 'text-gray-200' : 'text-gray-500'
              ]">Online</p>
            </div>
            <svg 
              :class="[
                'w-4 h-4 transition-all duration-200',
                isHomePage 
                  ? 'text-gray-200 group-hover:text-white' 
                  : 'text-gray-400 group-hover:text-gray-600',
                isUserMenuOpen ? 'rotate-180' : ''
              ]"
              fill="none" 
              stroke="currentColor" 
              viewBox="0 0 24 24"
            >
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
          </button>

          <!-- Dropdown Menu -->
          <transition
            enter-active-class="transition ease-out duration-200"
            enter-from-class="transform opacity-0 scale-95"
            enter-to-class="transform opacity-100 scale-100"
            leave-active-class="transition ease-in duration-150"
            leave-from-class="transform opacity-100 scale-100"
            leave-to-class="transform opacity-0 scale-95"
          >
            <div 
              v-show="isUserMenuOpen"
              class="absolute right-0 mt-2 w-64 rounded-lg bg-white shadow-lg ring-1 ring-black ring-opacity-5 overflow-hidden"
            >
              <div class="py-1">
                <router-link 
                  to="/dashboard" 
                  class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                >
                  <svg class="w-4 h-4 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5a2 2 0 012-2h4a2 2 0 012 2v6H8V5z"/>
                  </svg>
                  Trang chủ
                </router-link>
                <router-link 
                  to="/settings" 
                  class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                >
                  <svg class="w-4 h-4 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                  </svg>
                  Tùy chỉnh
                </router-link>
                <button 
                  @click="logout"
                  class="flex items-center w-full px-4 py-2 text-sm text-red-600 hover:bg-red-50"
                >
                  <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                  </svg>
                  Đăng xuất
                </button>
              </div>
            </div>
          </transition>
        </div>
      </div>
    </div>
  </header>
</template>

<script>
import { ref, onMounted, onUnmounted, watch, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth/authStore'
import { useNotifications } from '@/composables/features/social/useNotifications'
import { encodedID } from '@/utils'
import dayjs from 'dayjs'
import relativeTime from 'dayjs/plugin/relativeTime'
import 'dayjs/locale/vi'

dayjs.locale('vi')
dayjs.extend(relativeTime)

export default {
  name: 'HeaderV2',
  
  setup() {
    // State
    const router = useRouter()
    const auth = useAuthStore()
    const { notifications, unreadCount, loading, fetchNotifications, markAsRead, markAllAsRead } = useNotifications()
    const isUserMenuOpen = ref(false)
    const isNotificationsOpen = ref(false)

    // Computed để kiểm tra có phải trang chủ không
    const isHomePage = computed(() => {
      return router.currentRoute.value.path === '/'
    })

    // Click outside để đóng notification panel
    const handleClickOutside = (event) => {
      const notificationPanel = document.querySelector('.notification-panel')
      if (notificationPanel && !notificationPanel.contains(event.target) && !event.target.closest('button[aria-label="notifications"]')) {
        isNotificationsOpen.value = false
      }
    }

    // Methods
    const toggleUserMenu = () => {
      isUserMenuOpen.value = !isUserMenuOpen.value
    }

    const closeUserMenu = () => {
      isUserMenuOpen.value = false
    }

    const toggleNotifications = () => {
      isNotificationsOpen.value = !isNotificationsOpen.value
      if (isNotificationsOpen.value) {
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
        isNotificationsOpen.value = false;
      }
    }

    // Format thời gian
    const formatTime = (timestamp) => {
      return dayjs(timestamp).fromNow()
    }

    const logout = async () => {
      try {
        await auth.logout()
        isUserMenuOpen.value = false
      } catch (error) {
        console.error('Logout failed:', error)
      }
    }

    // Lifecycle hooks
    onMounted(() => {
      auth.checkAuth()
      document.addEventListener('click', handleClickOutside)
      fetchNotifications()
    })

    onUnmounted(() => {
      document.removeEventListener('click', handleClickOutside)
    })

    // Theo dõi sự thay đổi của route để đóng panel khi chuyển trang
    watch(() => router.currentRoute.value, () => {
      isNotificationsOpen.value = false
    })

    return {
      auth,
      notifications,
      unreadCount,
      loading,
      isAuthenticated: auth.isAuthenticated,
      user: auth.user,
      isUserMenuOpen,
      isNotificationsOpen,
      isHomePage,
      toggleUserMenu,
      closeUserMenu,
      toggleNotifications,
      viewNotification,
      formatTime,
      markAllAsRead,
      logout
    }
  }
}
</script>

<style scoped>
/* Smooth transitions */
* {
  transition-property: all;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
}

/* Thêm backdrop blur cho header khi ở trang chủ */
header {
  backdrop-filter: blur(8px);
  -webkit-backdrop-filter: blur(8px);
}

/* Đảm bảo header luôn có độ trong suốt nhất định khi ở trang chủ */
header.bg-transparent {
  background-color: rgba(0, 0, 0, 0.1) !important;
}
</style> 