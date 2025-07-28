<template>
  <div>
    <div class="min-h-screen bg-white flex items-center justify-center py-24 px-4">
      <div class="w-full max-w-[70%]">
        <div class="flex items-center mb-10 relative">
          <div class="absolute left-0">
            <ButtonBack customClass="bg-gradient-text hover:from-blue-700 hover:to-purple-800 text-white font-semibold py-3 px-7 rounded-full shadow-xl transition-all duration-200 hover:scale-105"/>
          </div>
          <h1 class="text-3xl bg-gradient-to-r from-blue-600 via-purple-500 to-pink-500 bg-clip-text text-transparent font-extrabold flex items-center gap-2 mx-auto">
            <span>⚙️</span> Cài đặt tài khoản
          </h1>
        </div>
        <div class="flex items-center justify-center gap-8 w-full">
          <ProfileInfo />
          <ChangePassword />
        </div>
        <ActivityHistory :activities="activities" />
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth/authStore'
import { ButtonBack } from '@/components/base'
import { ProfileInfo, ChangePassword, ActivityHistory } from '@/components/features/user-profile'

export default {
  name: 'Settings',
  components: {
    ProfileInfo,
    ChangePassword,
    ActivityHistory,
    ButtonBack
  },
  setup() {
    const auth = useAuthStore()
    const activities = ref([])

    onMounted(() => {
      if (auth.user) {
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

    return {
      activities
    }
  }
}
</script>