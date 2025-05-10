<template>
  <div>
    <h2 class="text-2xl font-bold mb-4">Test 2</h2>
    <p class="mb-4">Đây là trang test số 2 với hiệu ứng parallax Tailwind.</p>
    
    <div 
      class="relative h-96 overflow-hidden rounded-lg shadow-lg" 
      @mousemove="handleMouseMove"
      ref="parallaxContainer"
    >
      <!-- Background layer -->
      <div 
        class="absolute inset-0 bg-gradient-to-br from-blue-500 to-purple-600"
        :style="{ transform: `translateX(${offsetX * 0.01}px) translateY(${offsetY * 0.01}px)` }"
      ></div>
      
      <!-- Middle layers with different movement speeds -->
      <div 
        class="absolute inset-0 opacity-70"
        :style="{ transform: `translateX(${offsetX * 0.03}px) translateY(${offsetY * 0.03}px)` }"
      >
        <div class="absolute top-20 left-20 w-32 h-32 bg-white/20 rounded-full blur-xl"></div>
        <div class="absolute bottom-20 right-20 w-40 h-40 bg-pink-300/20 rounded-full blur-xl"></div>
      </div>
      
      <div 
        class="absolute inset-0"
        :style="{ transform: `translateX(${offsetX * 0.05}px) translateY(${offsetY * 0.05}px)` }"
      >
        <div class="absolute top-40 right-40 w-24 h-24 bg-yellow-300/30 rounded-full blur-lg"></div>
        <div class="absolute bottom-40 left-40 w-28 h-28 bg-indigo-400/30 rounded-full blur-lg"></div>
      </div>
      
      <!-- Content layer (moves in opposite direction) -->
      <div 
        class="absolute inset-0 flex items-center justify-center"
        :style="{ transform: `translateX(${-offsetX * 0.08}px) translateY(${-offsetY * 0.08}px)` }"
      >
        <div class="text-center p-8 bg-white/10 backdrop-blur-md rounded-xl shadow-lg">
          <h3 class="text-xl font-bold text-white mb-2">Hiệu ứng Parallax</h3>
          <p class="text-white/90">Di chuyển chuột qua khu vực này để thấy hiệu ứng</p>
        </div>
      </div>
      
      <!-- Floating elements (greatest movement) -->
      <div 
        class="absolute w-12 h-12 bg-white/40 rounded-lg top-1/4 left-1/4"
        :style="{ transform: `translateX(${offsetX * 0.12}px) translateY(${offsetY * 0.12}px) rotate(${offsetX * 0.05}deg)` }"
      ></div>
      
      <div 
        class="absolute w-8 h-8 bg-white/40 rounded-full top-2/3 right-1/3"
        :style="{ transform: `translateX(${offsetX * 0.14}px) translateY(${offsetY * 0.14}px)` }"
      ></div>
    </div>
  </div>
</template>

<script>
import { ref } from 'vue';

export default {
  name: 'Test2',
  setup() {
    const parallaxContainer = ref(null);
    const offsetX = ref(0);
    const offsetY = ref(0);
    
    const handleMouseMove = (e) => {
      if (!parallaxContainer.value) return;
      
      // Tính toán vị trí chuột so với vị trí trung tâm của container
      const rect = parallaxContainer.value.getBoundingClientRect();
      const centerX = rect.width / 2;
      const centerY = rect.height / 2;
      
      // Tính offset từ tâm (giá trị từ -centerX đến +centerX)
      offsetX.value = (e.clientX - rect.left - centerX) / 3;
      offsetY.value = (e.clientY - rect.top - centerY) / 3;
    };
    
    return {
      parallaxContainer,
      offsetX,
      offsetY,
      handleMouseMove
    };
  }
};
</script> 