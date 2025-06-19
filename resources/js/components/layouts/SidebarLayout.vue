<template>
  <div class="flex h-screen">
    <!-- Overlay when sidebar is open on mobile -->
    <div 
      v-if="isOpen" 
      @click="toggleSidebar" 
      class="fixed inset-0 bg-black/50 z-40 lg:hidden transition-opacity duration-300"
    ></div>
    
    <!-- Sidebar -->
    <aside 
      class="fixed inset-y-0 left-0 z-50 flex flex-col w-64 overflow-y-auto bg-gradient-to-b from-purple-500/20 to-blue-500/20 backdrop-blur-xl shadow-lg transition-all duration-300 ease-in-out transform"
      :class="isOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
    >
      <!-- Logo -->
      <div class="flex items-center justify-between px-4 py-5">
        <a href="/" class="flex items-center space-x-2">
          <img :src="logo" alt="KT_AI Logo" class="h-8 w-8">
          <span class="text-xl font-bold bg-gradient-to-r from-purple-600 to-blue-600 bg-clip-text text-transparent">
            KT_AI
          </span>
        </a>
        <!-- Close button for mobile -->
        <button 
          @click="toggleSidebar" 
          class="lg:hidden text-gray-500 hover:text-gray-700 focus:outline-none p-2"
        >
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
          </svg>
        </button>
      </div>

      <!-- Navigation -->
      <nav class="flex-1 px-4 py-4 space-y-1">
        <!-- Main Menu -->
        <div v-for="item in menuItems" :key="item.path" class="mb-3">
          <router-link 
            :to="item.path"
            class="flex items-center px-3 py-2 rounded-md text-sm font-medium transition hover:bg-white/20"
            :class="$route.path === item.path ? 'bg-white/30 text-purple-700' : 'text-gray-700'"
          >
            <i :class="item.icon" class="mr-3 text-lg"></i>
            {{ item.name }}
          </router-link>
        </div>

        <!-- Notification -->
        <div class="mb-3">
          <router-link 
            to="/notifications" 
            class="flex items-center px-3 py-2 rounded-md text-sm font-medium transition hover:bg-white/20"
            :class="$route.path === '/notifications' ? 'bg-white/30 text-purple-700' : 'text-gray-700'"
          >
            <div class="relative">
              <i class="fas fa-bell text-lg"></i>
              <span
                v-if="unreadCount > 0"
                class="absolute -top-4 -right-4 bg-red-500 text-white text-xs font-bold px-1.5 py-0.5 rounded-full"
              >
                {{ unreadCount }}
              </span>
            </div>
            <span class="ml-3">Thông báo</span>
          </router-link>
        </div>

        <!-- Test Menu with Submenu -->
        <div class="mb-3">
          <button 
            @click="toggleTestMenu" 
            class="flex items-center justify-between w-full px-3 py-2 rounded-md text-sm font-medium transition hover:bg-white/20 text-gray-700"
          >
            <div class="flex items-center">
              <i class="fa-solid fa-screwdriver-wrench text-lg mr-3"></i>
              Chức năng
            </div>
            <i 
              class="fas transition-transform duration-200" 
              :class="isTestMenuOpen ? 'fa-chevron-down' : 'fa-chevron-right'"
            ></i>
          </button>

          <!-- Submenu -->
          <div 
            v-show="isTestMenuOpen" 
            class="mt-1 ml-8 space-y-1"
            ref="testSubmenu"
          >
            <router-link 
              v-for="(subItem, index) in testSubMenu" 
              :key="index" 
              :to="subItem.path"
              class="block px-3 py-2 rounded-md text-sm font-medium transition hover:bg-white/20"
              :class="$route.path === subItem.path ? 'bg-white/30 text-purple-700' : 'text-gray-700'"
            >
              {{ subItem.name }}
            </router-link>
          </div>
        </div>
      </nav>

      <!-- User Profile Section -->
      <div class="px-4 py-4 border-t border-gray-200/30">
        <template v-if="isAuthenticated">
          <div class="flex items-center space-x-3">
            <img 
              :src="user.avatar_url || '/img/default-avatar.png'" 
              alt="User avatar"
              class="h-8 w-8 rounded-full object-cover"
            >
            <div>
              <p class="text-sm font-medium text-gray-700">{{ user.name }}</p>
              <button 
                @click="logout"
                class="text-xs text-gray-500 hover:text-gray-700"
              >
                Đăng xuất
              </button>
            </div>
          </div>
        </template>
        <template v-else>
          <div class="flex flex-col space-y-2">
            <router-link 
              to="/login" 
              class="px-4 py-2 text-sm font-medium text-center text-gray-700 hover:text-indigo-600 transition"
            >
              Đăng nhập
            </router-link>
            <router-link 
              to="/register" 
              class="px-4 py-2 text-sm font-medium text-center text-white bg-indigo-600 rounded-full hover:bg-indigo-700 transition"
            >
              Đăng ký
            </router-link>
          </div>
        </template>
      </div>
    </aside>

    <!-- Main Content -->
    <div class="flex-1 ml-0 lg:ml-64 transition-all duration-300">
      <!-- Top Bar with Hamburger -->
      <div class="fixed top-0 left-0 right-0 z-40 flex items-center justify-between p-4 bg-white shadow-sm lg:ml-64">
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
        <div class="flex items-center">
          <img :src="logo" alt="KT_AI Logo" class="h-8 w-8 mr-2">
          <span class="text-xl font-bold bg-gradient-to-r from-purple-600 to-blue-600 bg-clip-text text-transparent">
            KT_AI
          </span>
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
import { ref, onMounted, onBeforeUnmount, nextTick } from 'vue'
import { useAuthStore } from '@/stores/auth/authStore'
import { gsap } from 'gsap'
import { useNotifications } from '@/composables/user/useNotifications'

export default {
  name: 'SidebarLayout',

  setup() {
    // State
    const { unreadCount } = useNotifications()
    const auth = useAuthStore()
    const isOpen = ref(false) // Default closed on mobile, will adjust in mounted
    const isTestMenuOpen = ref(false)
    const logo = ref('/img/voice.png')
    const testSubmenu = ref(null)
    
    // Data
    const menuItems = [
      { name: 'Trang chủ', path: '/dashboard', icon: 'fas fa-home' },
      { name: 'Tạo ảnh', path: '/features', icon: 'fas fa-image' },
      { name: 'Tiến trình', path: '/image-jobs', icon: 'fas fa-tasks' },
    ]
    
    const testSubMenu = [
      { name: 'Thống kê', path: '/statistics'},
      { name: 'Tùy chỉnh', path: '/settings' },
      { name: 'Lịch sử hoạt động', path: '/test/2' },
      { name: 'Thanh toán', path: '/test/3' },
      { name: 'Hỗ trợ', path: '/test/4' },
    ]

    // Methods
    const toggleSidebar = () => {
      // Nếu màn hình lớn, luôn mở sidebar
      if (window.innerWidth >= 1024) {
        isOpen.value = true;
      } else {
        // Trên mobile, toggle sidebar
        isOpen.value = !isOpen.value;
        
        // Thêm class để ngăn scroll trên mobile khi sidebar mở
        if (isOpen.value) {
          document.body.classList.add('overflow-hidden');
        } else {
          document.body.classList.remove('overflow-hidden');
        }
      }
    }
    
    const toggleTestMenu = () => {
      isTestMenuOpen.value = !isTestMenuOpen.value
      
      // Đảm bảo DOM đã cập nhật trước khi áp dụng animation
      nextTick(() => {
        // Use GSAP for animation
        if (testSubmenu.value) {
          if (isTestMenuOpen.value) {
            gsap.fromTo(
              testSubmenu.value,
              { height: 0, opacity: 0 },
              { height: 'auto', opacity: 1, duration: 0.3, ease: 'power2.out' }
            )
          } else {
            gsap.to(
              testSubmenu.value,
              { height: 0, opacity: 0, duration: 0.3, ease: 'power2.in' }
            )
          }
        }
      })
    }

    const logout = async () => {
      try {
        await auth.logout()
        isOpen.value = false // Close sidebar after logout on mobile
      } catch (error) {
        console.error('Logout failed:', error)
      }
    }
    
    // Handle window resize
    const handleResize = () => {
      isOpen.value = window.innerWidth >= 1024
      
      // Xóa class overflow-hidden khi resize
      if (window.innerWidth >= 1024) {
        document.body.classList.remove('overflow-hidden')
      }
    }

    // Lifecycle hooks
    onMounted(() => {
      auth.checkAuth()
      window.addEventListener('resize', handleResize)
      
      // Thiết lập trạng thái ban đầu dựa trên kích thước màn hình
      isOpen.value = window.innerWidth >= 1024
    })
    
    onBeforeUnmount(() => {
      window.removeEventListener('resize', handleResize)
      // Đảm bảo xóa lớp overflow-hidden khi component unmount
      document.body.classList.remove('overflow-hidden')
    })

    return {
      menuItems,
      unreadCount,
      testSubMenu,
      isAuthenticated: auth.isAuthenticated,
      user: auth.user,
      isOpen,
      toggleSidebar,
      isTestMenuOpen,
      toggleTestMenu,
      logout,
      logo,
      testSubmenu,
    }
  }
}
</script>

<style scoped>
/* Animation for router links */
.router-link-active {
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
  color: white !important;
}

/* Custom animations for sidebar and submenu */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

/* Tăng vùng tương tác cho nút hamburger */
button {
  touch-action: manipulation;
}

/* Custom styles for hamburger button */
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
  border-radius: 0.375rem;
}

.hamburger-btn:hover {
  background-color: rgba(0, 0, 0, 0.05);
}

@media (min-width: 1024px) {
  .hamburger-btn {
    display: none;
  }
}
</style>