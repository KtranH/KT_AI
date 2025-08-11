<template>
  <div class="flex h-screen">
    <!-- Overlay when sidebar is open on mobile -->
    <transition
      enter-active-class="transition-opacity duration-300"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition-opacity duration-300"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div 
        v-if="isOpen" 
        @click="toggleSidebar" 
        class="fixed inset-0 bg-black/50 z-40 lg:hidden backdrop-blur-sm"
      ></div>
    </transition>
    
    <!-- Sidebar -->
    <aside 
      class="fixed inset-y-0 left-0 z-50 flex flex-col w-64 overflow-hidden transition-all duration-500 ease-out transform"
      :class="sidebarClasses"
    >
      <!-- Background with gradient -->
      <div class="absolute inset-0 bg-gradient-to-b from-purple-500 via-blue-500 to-indigo-500"></div>
      
      <!-- Content -->
      <div class="relative flex flex-col h-full">
        <!-- Logo Section -->
        <div class="flex items-center justify-between px-4 py-6 border-b border-white/10">
          <div class="flex items-center space-x-3">
            <div class="relative">
              <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center backdrop-blur-sm ring-1 ring-white/30">
                <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 24 24">
                  <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                </svg>
              </div>
              <div class="absolute -top-1 -right-1 w-3 h-3 bg-green-400 rounded-full animate-pulse ring-2 ring-white"></div>
            </div>
            <div class="flex flex-col">
              <span class="text-xl font-bold text-white cursor-pointer" @click="router.push('/')">KT_AI</span>
              <span class="text-xs text-white/70">Trí tuệ nhân tạo</span>
            </div>
          </div>
          
          <!-- Close button for mobile -->
          <button 
            @click="toggleSidebar" 
            class="lg:hidden p-2 rounded-lg text-white/70 hover:text-white hover:bg-white/10 transition-all duration-300 group"
          >
            <svg class="w-5 h-5 group-hover:scale-110 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
          </button>
        </div>

        <!-- Navigation -->
        <nav class="flex-1 px-4 py-4 space-y-1 overflow-y-auto">
          <!-- Main Menu -->
          <div v-for="item in menuItems" :key="item.path" class="mb-1">
            <router-link 
              :to="item.path"
              class="group flex items-center px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-300 relative overflow-hidden"
              :class="$route.path === item.path ? 'text-white bg-white/20 shadow-md' : 'text-white/80 hover:text-white hover:bg-white/10'"
            >
              <div class="flex items-center space-x-3">
                <component :is="item.icon" class="w-4 h-4" />
                <span>{{ item.name }}</span>
              </div>
              
              <!-- Active indicator -->
              <div 
                v-if="$route.path === item.path"
                class="absolute right-2 w-1.5 h-1.5 bg-white rounded-full animate-pulse"
              ></div>
            </router-link>
          </div>

          <!-- Notification -->
          <div class="mb-1">
            <router-link 
              to="/notifications" 
              class="group flex items-center px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-300 relative overflow-hidden"
              :class="$route.path === '/notifications' ? 'text-white bg-white/20 shadow-md' : 'text-white/80 hover:text-white hover:bg-white/10'"
            >
              <div class="flex items-center space-x-3">
                <div class="relative flex items-center">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 15V11a6 6 0 10-12 0v4c0 .386-.146.735-.405 1.005L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                  </svg>
                  <span
                    v-if="unreadCount > 0"
                    class="absolute -top-2 -right-2 min-w-[18px] h-[18px] flex items-center justify-center bg-red-500 text-white text-xs font-bold px-1 rounded-full animate-pulse border-2 border-white shadow"
                    style="font-size: 11px;"
                  >
                    {{ unreadCount > 9 ? '9+' : unreadCount }}
                  </span>
                </div>
                <span>Thông báo</span>
              </div>
              
              <!-- Active indicator -->
              <div 
                v-if="$route.path === '/notifications'"
                class="absolute right-2 w-1.5 h-1.5 bg-white rounded-full animate-pulse"
              ></div>
            </router-link>
          </div>

          <!-- Features Menu with Submenu -->
          <div class="mb-1">
            <button 
              @click="toggleFeaturesMenu" 
              class="group flex items-center justify-between w-full px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-300 text-white/80 hover:text-white hover:bg-white/10"
            >
              <div class="flex items-center space-x-3">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                <span>Chức năng</span>
              </div>
              <svg 
                class="w-3 h-3 transition-transform duration-300 group-hover:scale-110" 
                :class="isFeaturesMenuOpen ? 'rotate-180' : ''"
                fill="none" 
                stroke="currentColor" 
                viewBox="0 0 24 24"
              >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
              </svg>
            </button>

            <!-- Submenu -->
            <transition
              enter-active-class="transition-all duration-300 ease-out"
              enter-from-class="opacity-0 max-h-0"
              enter-to-class="opacity-100 max-h-96"
              leave-active-class="transition-all duration-300 ease-in"
              leave-from-class="opacity-100 max-h-96"
              leave-to-class="opacity-0 max-h-0"
            >
              <div 
                v-show="isFeaturesMenuOpen" 
                class="mt-1 ml-6 space-y-1 overflow-hidden"
                ref="featuresSubmenu"
              >
                <router-link 
                  v-for="(subItem, index) in featuresSubMenu" 
                  :key="index" 
                  :to="subItem.path"
                  class="group flex items-center px-3 py-2 rounded-lg text-sm font-medium transition-all duration-300 text-white/70 hover:text-white hover:bg-white/10"
                  :class="$route.path === subItem.path ? 'text-white bg-white/20' : ''"
                >
                  <component :is="subItem.icon" class="w-3 h-3 mr-2" />
                  <span>{{ subItem.name }}</span>
                </router-link>
              </div>
            </transition>
          </div>
        </nav>

        <!-- User Profile Section -->
        <div class="px-4 py-4 border-t border-white/10">
          <template v-if="isAuthenticated">
            <div class="flex items-center space-x-3 p-3 rounded-lg bg-white/10 backdrop-blur-sm">
              <div class="relative">
                <img 
                  :src="user.avatar_url || '/img/default-avatar.png'" 
                  alt="User avatar"
                  class="h-10 w-10 rounded-lg object-cover ring-2 ring-white/30"
                >
                <div class="absolute -bottom-1 -right-1 w-3 h-3 bg-green-400 rounded-full border-2 border-white"></div>
              </div>
              <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-white truncate">{{ user?.name || 'User' }}</p>
                <p class="text-xs text-white/70">Online</p>
              </div>
              <button 
                @click="logout"
                class="p-1.5 rounded-lg text-white/70 hover:text-white hover:bg-white/10 transition-all duration-300 group"
              >
                <svg class="w-4 h-4 group-hover:scale-110 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                </svg>
              </button>
            </div>
          </template>
          <template v-else>
            <div class="space-y-2">
              <router-link 
                to="/login" 
                class="flex items-center justify-center px-3 py-2 text-sm font-medium text-white/80 hover:text-white hover:bg-white/10 rounded-lg transition-all duration-300"
              >
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                </svg>
                Đăng nhập
              </router-link>
              <router-link 
                to="/register" 
                class="flex items-center justify-center px-3 py-2 text-sm font-medium text-white bg-white/20 rounded-lg hover:bg-white/30 transition-all duration-300 shadow-lg hover:shadow-xl transform hover:scale-105"
              >
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                </svg>
                Đăng ký
              </router-link>
            </div>
          </template>
        </div>
      </div>
    </aside>

    <!-- Main Content -->
    <div class="flex-1 ml-0 lg:ml-64 transition-all duration-500">
      <!-- Top Bar with Hamburger -->
      <div class="fixed top-0 left-0 right-0 z-40 flex items-center justify-between p-4 bg-white/95 backdrop-blur-xl shadow-sm lg:ml-64">
        <button 
          @click="toggleSidebar" 
          class="hamburger-btn"
          aria-expanded="false"
        >
          <span class="sr-only">Mở menu</span>
          <svg class="block h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
          </svg>
        </button>
        <div class="flex items-center space-x-3">
          <div class="w-9 h-9 bg-gradient-to-br from-purple-500 to-blue-600 rounded-lg flex items-center justify-center shadow-lg">
            <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24">
              <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
            </svg>
          </div>
          <div class="flex flex-col">
            <span class="text-lg font-bold bg-gradient-to-r from-purple-600 to-blue-600 bg-clip-text text-transparent">
              KT_AI
            </span>
            <span class="text-xs text-gray-500">Trí tuệ nhân tạo</span>
          </div>
        </div>
      </div>

      <!-- Page Content -->
      <main class="px-4 py-6 mt-16">
        <slot></slot>
      </main>
    </div>
  </div>
</template>

<script>
import { ref, onMounted, onBeforeUnmount, computed } from 'vue'
import { useAuthStore } from '@/stores/auth/authStore'
import { useNotifications } from '@/composables/features/social/useNotifications'
import { useRouter } from 'vue-router'

export default {
  name: 'SidebarLayoutV2',

  setup() {
    // State
    const { unreadCount } = useNotifications()
    const auth = useAuthStore()
    const isOpen = ref(false)
    const isFeaturesMenuOpen = ref(false)
    const featuresSubmenu = ref(null)
    const router = useRouter()

    // Computed
    const sidebarClasses = computed(() => {
      return isOpen.value ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'
    })
    
    // Data
    const menuItems = [
      { 
        name: 'Trang chủ', 
        path: '/dashboard', 
        icon: 'HomeIcon' 
      },
      { 
        name: 'Tạo ảnh', 
        path: '/features-v2', 
        icon: 'ImageIcon' 
      },
      { 
        name: 'Tiến trình', 
        path: '/image-jobs', 
        icon: 'TaskIcon' 
      },
    ]
    
    const featuresSubMenu = [
      { 
        name: 'Thống kê', 
        path: '/statistics',
        icon: 'StatsIcon'
      },
      { 
        name: 'Tùy chỉnh', 
        path: '/settings',
        icon: 'SettingsIcon'
      },
      { 
        name: 'Lịch sử hoạt động', 
        path: '/test/2',
        icon: 'HistoryIcon'
      },
      { 
        name: 'Thanh toán', 
        path: '/test/3',
        icon: 'PaymentIcon'
      },
      { 
        name: 'Hỗ trợ', 
        path: '/test/4',
        icon: 'SupportIcon'
      },
    ]

    // SVG Icon Components
    const HomeIcon = {
      template: `
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
        </svg>
      `
    }

    const ImageIcon = {
      template: `
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
        </svg>
      `
    }

    const TaskIcon = {
      template: `
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
        </svg>
      `
    }

    const StatsIcon = {
      template: `
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
        </svg>
      `
    }

    const SettingsIcon = {
      template: `
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
        </svg>
      `
    }

    const HistoryIcon = {
      template: `
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
      `
    }

    const PaymentIcon = {
      template: `
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
        </svg>
      `
    }

    const SupportIcon = {
      template: `
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192L5.636 18.364M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/>
        </svg>
      `
    }

    // Methods
    const toggleSidebar = () => {
      if (window.innerWidth >= 1024) {
        isOpen.value = true
      } else {
        isOpen.value = !isOpen.value
        
        if (isOpen.value) {
          document.body.classList.add('overflow-hidden')
        } else {
          document.body.classList.remove('overflow-hidden')
        }
      }
    }
    
    const toggleFeaturesMenu = () => {
      isFeaturesMenuOpen.value = !isFeaturesMenuOpen.value
    }

    const logout = async () => {
      try {
        await auth.logout()
        isOpen.value = false
      } catch (error) {
        console.error('Logout failed:', error)
      }
    }
    
    const handleResize = () => {
      isOpen.value = window.innerWidth >= 1024
      
      if (window.innerWidth >= 1024) {
        document.body.classList.remove('overflow-hidden')
      }
    }

    // Lifecycle hooks
    onMounted(() => {
      auth.checkAuth()
      window.addEventListener('resize', handleResize)
      isOpen.value = window.innerWidth >= 1024
    })
    
    onBeforeUnmount(() => {
      window.removeEventListener('resize', handleResize)
      document.body.classList.remove('overflow-hidden')
    })

    return {
      menuItems,
      featuresSubMenu,
      unreadCount,
      isAuthenticated: computed(() => auth.isAuthenticated.value),
      user: computed(() => auth.user.value),
      isOpen,
      isFeaturesMenuOpen,
      toggleSidebar,
      toggleFeaturesMenu,
      logout,
      featuresSubmenu,
      sidebarClasses,
      HomeIcon,
      ImageIcon,
      TaskIcon,
      StatsIcon,
      SettingsIcon,
      HistoryIcon,
      PaymentIcon,
      SupportIcon,
      router
    }
  }
}
</script>

<style scoped>
/* Custom scrollbar for sidebar */
.overflow-y-auto::-webkit-scrollbar {
  width: 4px;
}

.overflow-y-auto::-webkit-scrollbar-track {
  background: transparent;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
  background: rgba(255, 255, 255, 0.3);
  border-radius: 2px;
}

.overflow-y-auto::-webkit-scrollbar-thumb:hover {
  background: rgba(255, 255, 255, 0.5);
}

/* Smooth transitions */
* {
  transition-property: all;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
}

/* Glass morphism effect */
.backdrop-blur-xl {
  backdrop-filter: blur(20px);
  -webkit-backdrop-filter: blur(20px);
}

/* Hamburger button styles */
.hamburger-btn {
  position: relative;
  z-index: 60;
  width: 40px;
  height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.3s ease;
  border-radius: 10px;
  background: rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(10px);
}

.hamburger-btn:hover {
  background: rgba(255, 255, 255, 0.2);
  transform: scale(1.05);
}

@media (min-width: 1024px) {
  .hamburger-btn {
    display: none;
  }
}
</style> 