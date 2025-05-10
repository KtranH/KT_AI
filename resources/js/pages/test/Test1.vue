<template>
  <div>
    <div class="flex flex-col md:flex-row gap-6">
      <div class="w-full md:w-1/2">
        <h2 class="text-2xl font-bold mb-4">Test 1</h2>
        <p class="mb-4">Đây là trang test số 1 với hiệu ứng GSAP animation.</p>
        <div class="space-y-4">
          <button 
            @click="playAnimation" 
            class="px-4 py-2 bg-gradient-to-r from-purple-600 to-blue-600 text-white rounded-lg hover:from-purple-700 hover:to-blue-700 transition"
          >
            Chạy hiệu ứng
          </button>
        </div>
      </div>
      <div class="w-full md:w-1/2">
        <div ref="animationBox" class="h-64 bg-white/20 backdrop-blur-sm rounded-lg flex items-center justify-center">
          <div ref="animationElement" class="w-16 h-16 bg-indigo-500 rounded-lg"></div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue';
import { gsap } from 'gsap';

export default {
  name: 'Test1',
  setup() {
    const animationElement = ref(null);
    const animationBox = ref(null);
    
    const playAnimation = () => {
      // Khởi tạo lại vị trí
      gsap.set(animationElement.value, {
        x: 0,
        y: 0,
        scale: 1,
        rotation: 0,
        backgroundColor: '#6366f1'
      });
      
      // Tạo timeline animation
      const tl = gsap.timeline();
      
      tl.to(animationElement.value, { 
        x: 100, 
        duration: 0.5, 
        ease: 'power2.out' 
      })
      .to(animationElement.value, { 
        y: 50, 
        duration: 0.5, 
        ease: 'power2.inOut' 
      })
      .to(animationElement.value, { 
        rotation: 360, 
        scale: 1.5, 
        duration: 0.8, 
        ease: 'elastic.out(1, 0.3)' 
      })
      .to(animationElement.value, { 
        backgroundColor: '#ec4899', 
        duration: 0.5 
      })
      .to(animationElement.value, { 
        x: 0, 
        y: 0, 
        scale: 1, 
        duration: 0.5, 
        ease: 'power2.inOut' 
      });
    };
    
    onMounted(() => {
      // Chạy animation nhẹ khi component mount
      gsap.from(animationElement.value, { 
        scale: 0, 
        opacity: 0, 
        duration: 0.5, 
        ease: 'back.out(1.7)' 
      });
    });
    
    return {
      animationElement,
      animationBox,
      playAnimation
    };
  }
};
</script> 