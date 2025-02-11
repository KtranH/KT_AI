<template>
  <header class="fixed top-0 left-0 right-0 z-50 bg-white/80 backdrop-blur-sm shadow-sm">
    <div class="container mx-auto px-4">
      <div class="flex items-center justify-between h-16">
        <!-- Logo -->
        <div class="flex items-center">
          <router-link to="/" class="flex items-center space-x-2">
            <img src="/images/logo.svg" alt="KT_AI Logo" class="h-8 w-8">
            <span class="text-xl font-bold bg-gradient-to-r from-purple-600 to-blue-600 bg-clip-text text-transparent">
              KT_AI
            </span>
          </router-link>
        </div>

        <!-- Navigation -->
        <nav class="hidden md:flex items-center space-x-4">
          <router-link 
            v-for="item in menuItems" 
            :key="item.path" 
            :to="item.path"
            class="px-3 py-2 rounded-md text-sm font-medium text-gray-700 hover:text-purple-600 transition"
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
                  :src="user.avatar || '/images/default-avatar.png'" 
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
                    to="/profile" 
                    class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                  >
                    Hồ sơ
                  </router-link>
                  <router-link 
                    to="/dashboard" 
                    class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                  >
                    Bảng điều khiển
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
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'

export default {
  name: 'Header',
  
  setup() {
    const router = useRouter()
    const isUserMenuOpen = ref(false)
    const isAuthenticated = ref(false)
    const user = ref(null)

    const menuItems = [
      { name: 'Trang chủ', path: '/' },
      { name: 'Dịch vụ', path: '/services' },
      { name: 'Giá cả', path: '/pricing' },
      { name: 'Liên hệ', path: '/contact' },
    ]

    const checkAuth = async () => {
      try {
        const response = await fetch('/api/auth/check')
        const data = await response.json()
        
        isAuthenticated.value = data.authenticated
        user.value = data.user
      } catch (error) {
        console.error('Error checking auth status:', error)
      }
    }

    const toggleUserMenu = () => {
      isUserMenuOpen.value = !isUserMenuOpen.value
    }

    const closeUserMenu = () => {
      isUserMenuOpen.value = false
    }

    const logout = async () => {
      try {
        const response = await fetch('/api/auth/logout', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
          }
        })

        if (!response.ok) {
          throw new Error('Đăng xuất thất bại')
        }

        await checkAuth()
        router.push('/login')
      } catch (error) {
        console.error('Logout failed:', error)
      }
    }

    onMounted(() => {
      checkAuth()
    })

    return {
      menuItems,
      isAuthenticated,
      user,
      isUserMenuOpen,
      toggleUserMenu,
      closeUserMenu,
      logout
    }
  }
}
</script>

<style scoped>
.router-link-active {
  color: #7c3aed;
}
</style> 