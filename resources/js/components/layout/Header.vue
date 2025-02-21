<template>
  <!--<header class="fixed top-0 left-0 right-0 z-50 bg-gradient-to-r from-purple-500/20 to-blue-500/20 backdrop-blur-xl shadow-sm transition-all duration-300"> -->  
  <header class="fixed top-0 left-0 right-0 z-50 shadow-sm" style="backdrop-filter: blur(50px);" data-aos="fade-down">
    <div class="container mx-auto px-2">
      <div class="flex items-center justify-between h-16">
        <!-- Logo -->
        <div class="flex items-center">
          <a href="/" class="flex items-center space-x-2">
            <img :src="logo" alt="KT_AI Logo" class="h-8 w-8">
            <span class="text-xl font-bold bg-gradient-to-r from-purple-600 to-blue-600 bg-clip-text text-transparent">
              KT_AI
            </span>
          </a>
        </div>

        <!-- Navigation -->
        <nav class="hidden md:flex items-center ml-24 space-x-6" v-if="shouldShowHeader">
          <router-link 
            v-for="item in menuItems" 
            :key="item.path" 
            :to="item.path"
            class="px-3 py-2 rounded-md text-sm font-medium transition"
          >
            {{ item.name }}
          </router-link>
        </nav>

        <!-- Auth Buttons -->
        <div class="flex items-center space-x-4">
          <template v-if="!isAuthenticated">
            <router-link 
              to="/login" 
              class="px-4 py-2 text-sm font-medium text-gray-700 hover:text-purple-600 transition"
            >
              Đăng nhập
            </router-link>
            <router-link 
              to="/register" 
              class="px-4 py-2 text-sm font-medium text-white bg-purple-600 rounded-full hover:bg-purple-700 transition"
            >
              Đăng ký
            </router-link>
          </template>
          <template v-else>
            <div class="relative" v-click-outside="closeUserMenu">
              <button 
                @click="toggleUserMenu"
                class="flex items-center space-x-2 focus:outline-none"
              >
                <img 
                  :src="user.avatar_url || '/img/default-avatar.png'" 
                  alt="User avatar"
                  class="h-8 w-8 rounded-full object-cover"
                >
                <span class="text-sm font-medium text-gray-700">{{ user.name }}</span>
              </button>

              <!-- Dropdown Menu -->
              <div 
                v-show="isUserMenuOpen"
                class="absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5"
              >
                <div class="py-1">
                  <router-link 
                    to="/dashboard" 
                    class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                  >
                    Bảng điều khiển
                  </router-link>
                  <router-link 
                    to="/settings" 
                    class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                  >
                    Tùy chỉnh
                  </router-link>
                  <button 
                    @click="logout"
                    class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                  >
                    Đăng xuất
                  </button>
                </div>
              </div>
            </div>
          </template>
        </div>
      </div>
    </div>
  </header>
</template>

<script>
import { ref, onMounted, computed } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '../../stores/auth'

export default {
  name: 'Header',
  
  setup() {
    const router = useRouter()
    const route = useRoute()
    const auth = useAuthStore()
    const isUserMenuOpen = ref(false)
    const logo = ref('/img/voice.png')
    const currentSection = ref(1)
    const color = ref('white')

    const shouldShowHeader = computed(() => {
      return route.path !== '/' && route.path !== '/register' && route.path !== '/login'
    })

    const menuItems = [
      { name: 'Trang chủ', path: '/dashboard' },
      { name: 'Tạo ảnh', path: '/features' },
      { name: 'Thông tin', path: '/contact' },
    ]

    const toggleUserMenu = () => {
      isUserMenuOpen.value = !isUserMenuOpen.value
    }

    const closeUserMenu = () => {
      isUserMenuOpen.value = false
    }

    const logout = async () => {
      try {
        await auth.logout()
      } catch (error) {
        console.error('Logout failed:', error)
      }
    }

    onMounted(() => {
      auth.checkAuth()
      
      // Listen for section change event from other components
      document.addEventListener('sectionChange', (event) => {
        currentSection.value = event.detail.section;
        if(currentSection.value === 2) color.value = "#7c3aed"
        else color.value = "white"
      })
    })

    return {
      menuItems,
      isAuthenticated: auth.isAuthenticated,
      user: auth.user,
      isUserMenuOpen,
      toggleUserMenu,
      closeUserMenu,
      logout,
      logo,
      currentSection,
      color,
      shouldShowHeader,
    }
  }
}
</script>

<style scoped>
.router-link-active {
  background-color: #7c3aed;
  border-radius: 20px;
  border: none;
  color: white;
}
</style> 