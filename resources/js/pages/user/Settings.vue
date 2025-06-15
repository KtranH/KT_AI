<template>
  <div>
    <div class="min-h-screen bg-gradient-to-br from-indigo-50 via-white to-blue-100 flex items-center justify-center py-24 px-4">
      <div class="w-full max-w-[70%]">
        <h1 class="text-3xl font-extrabold text-center mb-10 flex items-center justify-center gap-2">
          <span>⚙️</span> Cài đặt tài khoản
        </h1>
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
import ProfileInfo from '@/components/user/Settings/ProfileInfo.vue'
import ChangePassword from '@/components/user/Settings/ChangePassword.vue'
import ActivityHistory from '@/components/user/Settings/ActivityHistory.vue'

export default {
  name: 'Settings',
  components: {
    ProfileInfo,
    ChangePassword,
    ActivityHistory
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