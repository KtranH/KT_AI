<template>
  <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
    <h1 class="text-xl font-semibold text-gray-700 mb-6">{{ title }}</h1>
    <div class="flex flex-col lg:flex-row items-center justify-between mt-[-10px]">
      <ul class="list-disc list-inside text-gray-600">
        <li v-for="(item, index) in guideItems" :key="index" class="mb-2">{{ item }}</li>
      </ul>
      <img :src="thumbnailUrl" loading="lazy" alt="" class="w-64 h-64 ml-4 rounded-lg shadow-md object-cover">
    </div>
  </div>
</template>

<script>
import { defineComponent } from 'vue';

export default defineComponent({
  name: 'GuideSection',
  props: {
    title: {
      type: String,
      default: 'Hướng dẫn sử dụng'
    },
    guideItems: {
      type: Array,
      required: true
    },
    thumbnailUrl: {
      type: String,
      default: 'https://balico.com.vn/wp-content/uploads/2020/09/loi-404-tren-cyber-panel.jpg'
    }
  },
  setup() {
    // Component sử dụng props trực tiếp trong template
    return {};
  }
})
</script> 