<template>
  <div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-purple-50 flex items-center justify-center">
    <div class="text-center">
      <!-- Animated Logo/Icon -->
      <div class="relative mb-8">
        <div class="w-24 h-24 mx-auto rounded-3xl bg-gradient-to-r from-purple-600 via-blue-600 to-pink-600 flex items-center justify-center shadow-2xl animate-pulse">
          <svg class="w-12 h-12 text-white" fill="currentColor" viewBox="0 0 20 20">
            <path d="M10 2a1 1 0 01.894.553l1.382 2.8 3.09.45a1 1 0 01.554 1.706l-2.236 2.18.528 3.08a1 1 0 01-1.451 1.054L10 12.347l-2.761 1.456a1 1 0 01-1.451-1.054l.528-3.08-2.236-2.18a1 1 0 01.554-1.706l3.09-.45L9.106 2.553A1 1 0 0110 2z"/>
          </svg>
        </div>
        <!-- Decorative circles -->
        <div class="absolute -top-2 -right-2 w-8 h-8 bg-gradient-to-r from-yellow-400 to-orange-400 rounded-full animate-bounce"></div>
        <div class="absolute -bottom-2 -left-2 w-6 h-6 bg-gradient-to-r from-green-400 to-blue-400 rounded-full animate-bounce" style="animation-delay: 0.2s;"></div>
      </div>

      <!-- Loading Spinner -->
      <div class="relative mb-6">
        <div class="w-16 h-16 mx-auto border-4 border-purple-200 border-t-purple-600 rounded-full animate-spin"></div>
        <div class="absolute inset-0 w-16 h-16 mx-auto border-4 border-transparent border-t-blue-600 rounded-full animate-spin" style="animation-delay: 0.1s;"></div>
        <div class="absolute inset-0 w-16 h-16 mx-auto border-4 border-transparent border-t-pink-600 rounded-full animate-spin" style="animation-delay: 0.2s;"></div>
      </div>

      <!-- Loading Text -->
      <div class="space-y-2">
        <h2 class="text-2xl font-bold bg-gradient-to-r from-purple-600 to-blue-600 bg-clip-text text-transparent">
          Đang tải thông tin
        </h2>
        <p class="text-gray-600 text-lg">Vui lòng chờ trong giây lát...</p>
      </div>

      <!-- Progress Dots -->
      <div class="flex justify-center gap-2 mt-6">
        <div class="w-3 h-3 bg-purple-500 rounded-full animate-bounce"></div>
        <div class="w-3 h-3 bg-blue-500 rounded-full animate-bounce" style="animation-delay: 0.1s;"></div>
        <div class="w-3 h-3 bg-pink-500 rounded-full animate-bounce" style="animation-delay: 0.2s;"></div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
    name: 'LoadingState',
    setup() {
        return {}
    },
}
</script>
