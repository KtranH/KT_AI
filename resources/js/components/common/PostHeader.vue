<template>
  <div class="flex items-center p-4 border-b">
    <!-- Go back button -->
    <div class="flex items-center bg-gradient-text mr-2 rounded-xl">
      <button @click="$emit('go-back')" class="flex-shrink-0 px-4 py-1 text-[12px] font-medium text-white hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
        Back
      </button>
    </div>
    
    <!-- User profile -->
    <img :src="userAvatar" class="w-8 h-8 rounded-full" alt="Profile" />
    <div class="ml-3">
      <div class="flex items-center">
        <span class="font-semibold">{{ username }}</span>
        <div class="flex items-center ml-1">
          <slot name="user-badges"></slot>
        </div>
      </div>
    </div>
    
    <!-- Actions button -->
    <button class="ml-auto">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
        <path d="M6 10a2 2 0 11-4 0 2 2 0 014 0zM12 10a2 2 0 11-4 0 2 2 0 014 0zM16 12a2 2 0 100-4 2 2 0 000 4z" />
      </svg>
    </button>
  </div>
</template>

<script>
import { defineComponent } from 'vue';

export default defineComponent({
  name: 'PostHeader',
  props: {
    username: {
      type: String,
      default: 'User'
    },
    userAvatar: {
      type: String,
      default: 'https://imagedelivery.net/ZeGtsGSjuQe1P3UP_zk3fQ/ede24b65-497e-4940-ea90-06cc2757a200/storedata'
    }
  },
  setup() {
    // Component chỉ sử dụng props mà không có state hoặc methods
    return {};
  }
})
</script> 