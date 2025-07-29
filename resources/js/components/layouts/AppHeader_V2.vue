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
      <div class="flex items-center justify-between h-16">
        <!-- Logo -->
        <router-link to="/" class="flex items-center space-x-2">
          <img 
            src="/img/voice.png" 
            alt="KT_AI Logo" 
            class="h-8 w-8"
            :class="!isHomePage ? 'opacity-0' : ''"
          >
          <span :class="[
            'text-xl font-bold bg-clip-text text-transparent',
            isHomePage 
              ? 'bg-gradient-to-r from-white to-gray-200' 
              : 'bg-white'
          ]">
            KT_AI
          </span>
        </router-link>

        <!-- Right side content -->
        <div class="flex items-center space-x-4">
          <template v-if="auth.isAuthLoading.value">
            <div class="flex items-center space-x-2">
              <div :class="[
                'animate-pulse rounded-lg h-8 w-16',
                isHomePage ? 'bg-white/20' : 'bg-gray-200'
              ]"></div>
              <div :class="[
                'animate-pulse rounded-full h-8 w-16',
                isHomePage ? 'bg-white/20' : 'bg-gray-200'
              ]"></div>
            </div>
          </template>

          <template v-else-if="shouldShowAuthButtons">
            <router-link 
              to="/login" 
              :class="[
                'px-4 py-2 text-sm font-medium transition rounded-lg',
                isHomePage 
                  ? 'text-white hover:text-gray-200 hover:bg-white/10' 
                  : 'text-gray-700 hover:text-indigo-600'
              ]"
            >
              Đăng nhập
            </router-link>
            <router-link 
              to="/register" 
              :class="[
                'px-4 py-2 text-sm font-medium rounded-full transition',
                isHomePage 
                  ? 'text-white bg-white/20 hover:bg-white/30 border border-white/30' 
                  : 'text-white bg-indigo-600 hover:bg-indigo-700'
              ]"
            >
              Đăng ký
            </router-link>
          </template>

          <!-- User Menu khi đã đăng nhập -->
          <template v-else>
            <div class="flex items-center space-x-4">
              <!-- Notification Bell Component -->
              <NotificationBell :isHomePage="isHomePage" />
            </div>

        <!-- User Menu -->
        <div class="relative" v-click-outside="closeUserMenu">
          <button 
            @click="toggleUserMenu"
            data-user-menu
            :class="[
              'flex items-center space-x-3 p-2 rounded-lg transition-all duration-200 group',
              isHomePage 
                ? 'hover:bg-white/10' 
                : 'hover:bg-gray-100'
            ]"
          >
            <div class="relative">
              <img 
                :src="safeUser.avatar_url || '/img/default-avatar.png'" 
                alt="User avatar"
                class="h-10 w-10 rounded-lg object-cover"
              >
              <div class="absolute -bottom-1 -right-1 w-3 h-3 bg-green-400 rounded-full border-2 border-white"></div>
            </div>
            <div class="text-left">
              <p :class="[
                'text-sm font-medium',
                isHomePage ? 'text-white' : 'text-gray-900'
              ]">{{ safeUser.name || 'User' }}</p>
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
              class="user-menu-panel absolute right-0 mt-2 w-64 rounded-lg bg-white shadow-lg ring-1 ring-black ring-opacity-5 overflow-hidden"
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
          </template>
        </div>
      </div>
    </div>
  </header>
</template>

<script>
import { ref, onMounted, watch, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth/authStore'
import NotificationBell from '@/components/ui/NotificationBell.vue'



export default {
  name: 'HeaderV2',
  
  components: {
    NotificationBell
  },
  
  setup() {
    // State
    const router = useRouter()
    const auth = useAuthStore()
    const isUserMenuOpen = ref(false)

    // Computed để kiểm tra có phải trang chủ không
    const isHomePage = computed(() => {
      return router.currentRoute.value.path === '/'
    })

    // Computed để đảm bảo user object an toàn
    const safeUser = computed(() => {
      return auth.user.value || {}
    })

    // Computed để kiểm tra trạng thái loading của auth
    const isAuthLoading = computed(() => {
      return auth.isAuthLoading || false
    })

    // Computed để kiểm tra user đã đăng nhập
    const isUserLoggedIn = computed(() => {
      return auth.isAuthenticated && !auth.isAuthLoading
    })

    // Computed để tránh flash UI - chỉ hiển thị auth buttons sau khi loading hoàn thành
    const shouldShowAuthButtons = computed(() => {
      return !auth.isAuthLoading.value && !auth.isAuthenticated
    })

    // Methods
    const toggleUserMenu = () => {
      isUserMenuOpen.value = !isUserMenuOpen.value
    }

    const closeUserMenu = () => {
      isUserMenuOpen.value = false
    }

    const logout = async () => {
      try {
        isUserMenuOpen.value = false
        await auth.logout()
      } catch (error) {
        console.error('Logout failed:', error)
      }
    }

    // Lifecycle hooks
    onMounted(async () => {
      // Kiểm tra auth trước
      await auth.checkAuth()
    })

    // Theo dõi sự thay đổi của route để đóng user menu khi chuyển trang
    watch(() => router.currentRoute.value, () => {
      isUserMenuOpen.value = false
    })
    return {
      auth,
      isAuthenticated: auth.isAuthenticated,
      isUserMenuOpen,
      isHomePage,
      isUserLoggedIn,
      isAuthLoading,
      shouldShowAuthButtons,
      safeUser,
      toggleUserMenu,
      closeUserMenu,
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