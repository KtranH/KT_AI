<template>
    <div class="flex items-center p-4 border-b">
        <!-- Go back -->
        <div class="flex items-center bg-gradient-text mr-2 rounded-xl">
            <button @click="goBack" class="flex-shrink-0 px-4 py-1 text-[12px] font-medium text-white hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Back
            </button>
        </div>
        <img src="https://imagedelivery.net/ZeGtsGSjuQe1P3UP_zk3fQ/ede24b65-497e-4940-ea90-06cc2757a200/storedata" class="w-8 h-8 rounded-full" alt="Profile" />
        <div class="ml-3">
            <div class="flex items-center">
                <span class="font-semibold">Test thử</span>
                <div class="flex items-center ml-1">
                    <span class="text-purple-400 mr-1">🔮</span>
                    <span class="text-pink-500">👡</span>
                </div>
            </div>
        </div>
        <button class="ml-auto">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path d="M6 10a2 2 0 11-4 0 2 2 0 014 0zM12 10a2 2 0 11-4 0 2 2 0 014 0zM16 12a2 2 0 100-4 2 2 0 000 4z" />
            </svg>
        </button>
    </div>

    <!-- Post Title -->
    <div class="px-4 py-2 border-b">
        <div class="flex items-center">
            <h1 class="text-base font-bold">Beach girl</h1>
        </div>
    </div>
</template>

<script>
import { defineProps } from 'vue'
import useNavigation from '@/composables/user/useNavigation'

export default {
    name: 'HeaderSection',
    setup() {
        const { goBack } = useNavigation()

        return {
            goBack
        }
    }
}
</script>

<style scoped>
.bg-gradient-text {
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
}

@keyframes gradient-animation {
  0% { background-position: 0% 50%; }
  50% { background-position: 100% 50%; }
  100% { background-position: 0% 50%; }
}
</style> 