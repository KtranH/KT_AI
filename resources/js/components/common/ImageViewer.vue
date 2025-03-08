<template>
  <div class="bg-black flex items-center justify-center">
    <img 
      :src="imageUrl" 
      class="w-full h-full md:h-[600px] md:object-contain cursor-pointer" 
      :alt="imageAlt" 
      @click="$emit('preview')"
    />
  </div>
</template>

<script>
import { defineComponent } from 'vue';

export default defineComponent({
  name: 'ImageViewer',
  props: {
    imageUrl: {
      type: String,
      required: true
    },
    imageAlt: {
      type: String,
      default: 'Image'
    }
  },
  setup() {
    // Không cần thêm logic, chỉ sử dụng props trực tiếp trong template
    return {}; // Return rỗng vì không có biến local nào
  }
})
</script> 