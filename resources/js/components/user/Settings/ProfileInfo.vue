<template>
  <div class="bg-white/90 w-full h-full shadow-2xl rounded-2xl border border-indigo-100 transition-transform hover:scale-[1.01] duration-200 flex-1 flex flex-col justify-center min-h-[500px]">
    <div class="p-16 flex-1 flex flex-col justify-center">
      <h2 class="text-xl font-bold mb-5 flex items-center gap-2 bg-gradient-text-v2">
        <span>👤</span> Thông tin cá nhân
      </h2>

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

        <div>
          <label for="created_at" class="block text-sm font-medium text-gray-700 mb-1">Ngày tham gia</label>
          <input type="text" id="created_at" disabled class="mt-1 block w-full rounded-lg border border-gray-200 shadow-sm bg-gray-100/80 sm:text-base px-4 py-2" :value="formatDate(profile.created_at)">
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
</template>

<script>
import { ref, reactive, computed } from 'vue'
import { useAuthStore } from '@/stores/auth/authStore'
import { profileAPI } from '@/services/api'
import { formatDate, isActionTooQuick } from '@/utils'
import { toast } from 'vue-sonner'
import ConfirmUpdate from '@/components/common/ConfirmUpdate.vue'

export default {
  name: 'ProfileInfo',
  components: {
    ConfirmUpdate
  },
  setup() {
    const auth = useAuthStore()
    const updateRef = ref(null)
    const updateStatus = ref('')
    const lastActionTime = ref(null)

    const profile = reactive({
      name: auth.user.value?.name || '',
      email: auth.user.value?.email || '',
      created_at: auth.user.value?.created_at || ''
    })

    const isProfileChanged = computed(() => {
      return profile.name !== auth.user.value?.name
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

      const result = await updateRef.value.showAlert()
      if (!result.isConfirmed) return

      const formData = new FormData()
      formData.append('name', profile.name)
      try {
        await profileAPI.updateName(formData)
        updateStatus.value = 'Cập nhật thông tin thành công!'
        if (auth.user) {
          auth.user.value.name = profile.name
        }
        lastActionTime.value = Date.now()
        toast.success('Cập nhật thông tin thành công!')
        setTimeout(() => {
          updateStatus.value = ''
        }, 3000)
      } catch (error) {
        updateStatus.value = 'Có lỗi xảy ra: ' + (error.response?.data?.message || error.message)
      }
    }

    return {
      profile,
      updateStatus,
      updateProfile,
      isProfileChanged,
      formatDate,
      updateRef
    }
  }
}
</script> 