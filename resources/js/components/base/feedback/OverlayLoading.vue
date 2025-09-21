<template>
  <div 
    v-if="visible" 
    class="absolute inset-0 bg-white/80 backdrop-blur-sm flex items-center justify-center z-40 rounded-xl"
  >
    <div class="text-center">
      <!-- Spinner -->
      <div class="relative mb-4">
        <div class="w-12 h-12 mx-auto border-4 border-indigo-200 border-t-indigo-600 rounded-full animate-spin"></div>
        <div class="absolute inset-0 w-12 h-12 mx-auto border-4 border-transparent border-t-purple-600 rounded-full animate-spin" style="animation-delay: 0.1s;"></div>
      </div>
      
      <!-- Loading text -->
      <div class="space-y-1">
        <h3 class="text-lg font-semibold text-gray-800">{{ title }}</h3>
        <p class="text-sm text-gray-600">{{ message }}</p>
      </div>
      
      <!-- Progress dots -->
      <div class="flex justify-center gap-1 mt-4">
        <div class="w-2 h-2 bg-indigo-500 rounded-full animate-bounce"></div>
        <div class="w-2 h-2 bg-purple-500 rounded-full animate-bounce" style="animation-delay: 0.1s;"></div>
        <div class="w-2 h-2 bg-pink-500 rounded-full animate-bounce" style="animation-delay: 0.2s;"></div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'OverlayLoading',
  props: {
    visible: {
      type: Boolean,
      default: false
    },
    title: {
      type: String,
      default: 'Đang làm mới'
    },
    message: {
      type: String,
      default: 'Vui lòng chờ trong giây lát...'
    }
  }
}
</script>
