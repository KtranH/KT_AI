<template>
  <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-xl border border-white/20 p-8 mb-8">
    <div class="flex items-center justify-between mb-6">
      <div class="flex items-center space-x-3">
        <div class="w-10 h-10 bg-green-100 rounded-xl flex items-center justify-center">
          <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
        </div>
        <h3 class="text-xl font-bold text-gray-900">Mã khôi phục</h3>
      </div>
      <button
        @click="$emit('generateNew')"
        :disabled="isGenerating"
        class="inline-flex items-center px-4 py-2 bg-green-100 text-green-700 font-medium rounded-lg hover:bg-green-200 transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
      >
        <svg v-if="isGenerating" class="w-4 h-4 mr-2 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
        </svg>
        <svg v-else class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
        </svg>
        {{ isGenerating ? 'Đang tạo...' : 'Tạo mã mới' }}
      </button>
    </div>
    <p class="text-gray-600 mb-6 leading-relaxed">
      Lưu trữ các mã này ở nơi an toàn. Bạn có thể sử dụng chúng để truy cập tài khoản nếu mất thiết bị hoặc ứng dụng xác thực.
    </p>
    <div v-if="isGenerating" class="text-center py-8">
      <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
        <svg class="w-8 h-8 text-green-600 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
        </svg>
      </div>
      <p class="text-green-600 font-medium">Đang tạo mã khôi phục mới...</p>
    </div>
    <div v-else-if="recoveryCodes.length > 0" class="grid grid-cols-2 md:grid-cols-4 gap-4">
      <div
        v-for="code in recoveryCodes"
        :key="code"
        class="bg-gradient-to-r from-gray-50 to-gray-100 px-4 py-3 rounded-xl text-sm font-mono text-gray-700 text-center border border-gray-200 shadow-sm hover:shadow-md transition-shadow duration-200"
      >
        {{ code }}
      </div>
    </div>
    <div v-else class="text-center py-8">
      <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"/>
        </svg>
      </div>
      <p class="text-gray-500">Chưa có mã khôi phục</p>
    </div>
  </div>
</template>

<script setup>
defineProps({
  recoveryCodes: {
    type: Array,
    default: () => []
  },
  isGenerating: {
    type: Boolean,
    default: false
  }
})

defineEmits(['generateNew'])
</script>
