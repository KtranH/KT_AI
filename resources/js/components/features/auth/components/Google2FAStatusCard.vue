<template>
  <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-xl border border-white/20 p-8 mb-8">
    <div class="flex items-center justify-between">
      <div class="flex items-center space-x-4">
        <div class="flex-shrink-0">
          <div class="w-16 h-16 bg-gradient-to-r from-blue-500 to-pink-600 rounded-2xl flex items-center justify-center shadow-lg">
            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
          </div>
        </div>
        <div>
          <h3 class="text-xl font-bold text-gray-900">Trạng thái 2FA</h3>
          <p class="text-gray-600">
            {{ is2FAEnabled ? 'Đã bật bảo mật 2 lớp' : 'Chưa bật bảo mật 2 lớp' }}
          </p>
        </div>
      </div>
      <div class="flex items-center space-x-4">
        <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-semibold shadow-sm"
              :class="is2FAEnabled ? 'bg-green-100 text-green-800 border border-green-200' : 'bg-gray-100 text-gray-800 border border-gray-200'">
          <div class="w-2 h-2 rounded-full mr-2" :class="is2FAEnabled ? 'bg-green-500' : 'bg-gray-500'"></div>
          {{ is2FAEnabled ? 'Bảo mật' : 'Không bảo mật' }}
        </span>
        <button
          v-if="!is2FAEnabled"
          @click="$emit('showSetup')"
          class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-500 to-pink-600 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 hover:scale-105 focus:outline-none focus:ring-4 focus:ring-blue-500/30"
        >
          <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
          </svg>
          Bật 2FA
        </button>
        <button
          v-else
          @click="$emit('showDisable')"
          class="inline-flex items-center px-6 py-3 border-2 border-gray-300 text-gray-700 font-semibold rounded-xl hover:bg-gray-50 transition-all duration-300 hover:scale-105 focus:outline-none focus:ring-4 focus:ring-gray-500/30"
        >
          <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
          </svg>
          Tắt 2FA
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  is2FAEnabled: {
    type: Boolean,
    default: false
  }
})

defineEmits(['showSetup', 'showDisable'])
</script>
