<template>
  <div class="min-h-screen bg-gray-100 pt-24">
    <div class="max-w-[80%] mx-auto my-4 bg-white rounded-lg shadow-lg" data-aos="fade-up">
        <h1>Features</h1>
    </div>
  </div>
</template>

<script>
import { onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import AOS from 'aos'

export default {
    name: 'Features',
    setup() {
        AOS.init(
            {
                duration: 800,
                deplay: 500,
                once: false,
                offset: 150,
                easing: 'ease-in-sine',
            }
        )

        const router = useRouter()
        const auth = useAuthStore()
        const user = auth.user

    }
}
</script>