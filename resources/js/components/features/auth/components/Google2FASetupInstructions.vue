<template>
  <div class="bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 rounded-2xl p-8 mb-8">
    <div class="flex items-start space-x-4">
      <div class="flex-shrink-0">
        <div class="w-12 h-12 bg-blue-500 rounded-xl flex items-center justify-center">
          <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
        </div>
      </div>
      <div class="flex-1">
        <h3 class="text-lg font-bold text-blue-900 mb-3">Lợi ích của 2FA</h3>
        <div class="grid md:grid-cols-2 gap-4">
          <div class="flex items-center space-x-3">
            <div class="w-2 h-2 bg-blue-500 rounded-full"></div>
            <span class="text-blue-800">Bảo vệ tài khoản khỏi truy cập trái phép</span>
          </div>
          <div class="flex items-center space-x-3">
            <div class="w-2 h-2 bg-blue-500 rounded-full"></div>
            <span class="text-blue-800">Yêu cầu mã xác thực từ ứng dụng di động</span>
          </div>
          <div class="flex items-center space-x-3">
            <div class="w-2 h-2 bg-blue-500 rounded-full"></div>
            <span class="text-blue-800">Tăng cường bảo mật cho thông tin cá nhân</span>
          </div>
          <div class="flex items-center space-x-3">
            <div class="w-2 h-2 bg-blue-500 rounded-full"></div>
            <span class="text-blue-800">Tuân thủ tiêu chuẩn bảo mật quốc tế</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
// Component này không cần props hoặc emits
</script>
