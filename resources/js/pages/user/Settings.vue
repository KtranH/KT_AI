<template>
  <VueSonner position="top-right" theme="light" />
  <div class="min-h-screen bg-gradient-to-br from-indigo-50 via-white to-blue-100 flex items-center justify-center py-24 px-4">
    <div class="w-full max-w-2xl">
      <h1 class="text-3xl font-extrabold text-center mb-10 flex items-center justify-center gap-2">
        <span>⚙️</span> Cài đặt tài khoản
      </h1>
      
      <div class="bg-white/90 shadow-2xl rounded-2xl border border-indigo-100 mb-8 transition-transform hover:scale-[1.01] duration-200">
        <div class="p-8">
          <h2 class="text-xl font-bold mb-5 flex items-center gap-2 bg-gradient-text-v2"><span>👤</span> Thông tin cá nhân</h2>
          
          <form @submit.prevent="updateProfile" class="space-y-5">
            <div>
              <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Họ và tên</label>
              <input type="text" id="name" v-model="profile.name" class="mt-1 block w-full rounded-lg border border-indigo-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-base px-4 py-2 bg-white/80">
            </div>
            
            <div>
              <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
              <input type="email" id="email" v-model="profile.email" disabled class="mt-1 block w-full rounded-lg border border-gray-200 shadow-sm bg-gray-100/80 sm:text-base px-4 py-2">
              <p class="mt-1 text-xs text-gray-400">Email không thể thay đổi</p>
            </div>
            
            <div class="flex items-center gap-3">
              <button type="submit" class="inline-flex items-center justify-center py-2 px-6 border-0 shadow-lg text-base font-semibold rounded-lg text-white bg-gradient-text from-indigo-500 to-blue-500 hover:from-indigo-600 hover:to-blue-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-400 transition-transform hover:scale-105">
                <span>💾</span> Cập nhật thông tin
              </button>
              <span v-if="updateStatus" class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium border border-green-200 bg-green-50 text-green-700 gap-1 animate-fade-in">
                <span>✔️</span> {{ updateStatus }}
              </span>
            </div>
          </form>
        </div>
      </div>
      
      <div class="bg-white/90 shadow-2xl rounded-2xl border border-indigo-100 transition-transform hover:scale-[1.01] duration-200">
        <div class="p-8">
          <h2 class="text-xl font-bold mb-5 flex items-center gap-2 bg-gradient-text-v2"><span>🔒</span> Đổi mật khẩu</h2>
          
          <form @submit.prevent="changePassword" class="space-y-5">
            <div>
              <label for="current_password" class="block text-sm font-medium text-gray-700 mb-1">Mật khẩu hiện tại</label>
              <input type="password" id="current_password" v-model="passwords.current" class="mt-1 block w-full rounded-lg border border-indigo-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-base px-4 py-2 bg-white/80">
            </div>
            
            <div>
              <label for="new_password" class="block text-sm font-medium text-gray-700 mb-1">Mật khẩu mới</label>
              <input type="password" id="new_password" v-model="passwords.new" class="mt-1 block w-full rounded-lg border border-indigo-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-base px-4 py-2 bg-white/80">
            </div>
            
            <div>
              <label for="confirm_password" class="block text-sm font-medium text-gray-700 mb-1">Xác nhận mật khẩu mới</label>
              <input type="password" id="confirm_password" v-model="passwords.confirm" class="mt-1 block w-full rounded-lg border border-indigo-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-base px-4 py-2 bg-white/80">
            </div>
            
            <div class="flex items-center gap-3">
              <button type="submit" class="inline-flex items-center justify-center py-2 px-6 border-0 shadow-lg text-base font-semibold rounded-lg text-white bg-gradient-text from-indigo-500 to-blue-500 hover:from-indigo-600 hover:to-blue-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-400 transition-transform hover:scale-105">
                <span>🔑</span> Đổi mật khẩu
              </button>
              <span v-if="passwordStatus" class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium border gap-1 animate-fade-in"
                :class="{'border-green-200 bg-green-50 text-green-700': !passwordError, 'border-red-200 bg-red-50 text-red-600': passwordError}">
                <span v-if="!passwordError">✔️</span>
                <span v-else>❌</span>
                {{ passwordStatus }}
              </span>
            </div>
          </form>
        </div>
      </div>
      
      <div class="bg-white/90 shadow-2xl rounded-2xl border border-indigo-100 mt-8 transition-transform hover:scale-[1.01] duration-200">
        <div class="p-8">
          <h2 class="text-xl font-bold mb-5 flex items-center gap-2 bg-gradient-text-v2">
            <span>📜</span> Lịch sử hoạt động
          </h2>
          <div v-if="activities.length" class="divide-y divide-indigo-50">
            <div v-for="(activity, idx) in activitiesSorted" :key="idx" class="py-3 flex items-center gap-3">
              <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-indigo-100 text-indigo-600 font-bold">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3"/></svg>
              </span>
              <div class="flex-1">
                <div class="font-medium text-gray-800">{{ activity.action }}</div>
                <div class="text-xs text-gray-400">{{ formatTime(activity.timestamp) }}</div>
              </div>
            </div>
          </div>
          <div v-else class="text-gray-400 text-center py-6">
            Không có hoạt động nào gần đây.
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, reactive, onMounted, computed } from 'vue'
import { useAuthStore } from '@/stores/auth/authStore'
import { profileAPI } from '@/services/api'
import { formatTime, isActionTooQuick } from '@/utils'
import { toast, Toaster as VueSonner } from 'vue-sonner'

export default {
  name: 'Settings',
  components: {
    VueSonner
  },
  setup() {
    const auth = useAuthStore()
    
    const profile = reactive({
      name: '',
      email: ''
    })
    
    const passwords = reactive({
      current: '',
      new: '',
      confirm: ''
    })
    
    const updateStatus = ref('')
    const passwordStatus = ref('')
    const passwordError = ref(false)
    const lastActionTime = ref(null)

    // Kiểm tra giá trị có khác với ban đầu không
    const isProfileChanged = computed(() => {
      return profile.name !== auth.user.value.name
    })

    // Activities
    const activities = ref([])
    onMounted(() => {
      // Khởi tạo dữ liệu từ thông tin người dùng hiện tại
      if (auth.user) {
        profile.name = auth.user.value.name || ''
        profile.email = auth.user.value.email || ''
        let acts = auth.user.value.activities
        if (typeof acts === 'string') {
          try {
            acts = JSON.parse(acts)
          } catch(e) {
            acts = []
          }
        }
        activities.value = Array.isArray(acts) ? acts : []
      }
    })
    
    const activitiesSorted = computed(() => {
      return [...activities.value].sort((a, b) => (b.timestamp || '').localeCompare(a.timestamp || ''))
    })
    
    const updateProfile = async () => {
      if (isActionTooQuick(lastActionTime.value)) {
        toast.info('Vui lòng đợi trước khi thực hiện thao tác này')
        return
      }
      if (!isProfileChanged.value) {
        toast.info('Thông tin không thay đổi')
        return
      }
      const formData = new FormData()
      formData.append('name', profile.name)
      try {
        await profileAPI.updateName(formData)
        updateStatus.value = 'Cập nhật thông tin thành công!'    
        // Cập nhật thông tin trong store
        if (auth.user) {
          auth.user.value.name = profile.name
        }
        lastActionTime.value = Date.now()
        toast.success('Cập nhật thông tin thành công!')
        // Reset status sau 3 giây
        setTimeout(() => {
          updateStatus.value = ''
        }, 3000)
      } catch (error) {
        updateStatus.value = 'Có lỗi xảy ra: ' + (error.response?.data?.message || error.message)
      }
    }
    
    const changePassword = async () => {
      passwordError.value = false
      // Kiểm tra xác nhận mật khẩu
      if (passwords.new !== passwords.confirm) {
        passwordStatus.value = 'Mật khẩu xác nhận không khớp!'
        passwordError.value = true
        return
      }
      const formData = new FormData()
      formData.append('current_password', passwords.current)
      formData.append('password', passwords.new)
      formData.append('password_confirmation', passwords.confirm)
      try {
        await profileAPI.updatePassword(formData)
        passwordStatus.value = 'Đổi mật khẩu thành công!'
        
        // Reset form
        passwords.current = ''
        passwords.new = ''
        passwords.confirm = ''
        
        toast.success('Đổi mật khẩu thành công!')
        // Reset status sau 3 giây
        setTimeout(() => {
          passwordStatus.value = ''
        }, 3000)
      } catch (error) {
        passwordStatus.value = 'Có lỗi xảy ra: ' + (error.response?.data?.message || error.message)
        passwordError.value = true
      }
    }
    
    return {
      profile,
      passwords,
      updateStatus,
      passwordStatus,
      passwordError,
      activities,
      activitiesSorted,
      formatTime,
      updateProfile,
      changePassword,
      isProfileChanged
    }
  }
}
</script>