<template>
  <div>
    <h2 class="text-2xl font-bold mb-4">Test 4</h2>
    <p class="mb-4">Đây là trang test số 4 với hiệu ứng mưa sử dụng Tailwind và GSAP.</p>
    
    <div class="relative h-96 bg-gradient-to-b from-indigo-900 to-blue-900 rounded-lg overflow-hidden">
      <!-- Hiệu ứng sao lấp lánh -->
      <div class="absolute inset-0" ref="starsContainer">
        <div v-for="(star, index) in stars" :key="index" 
          class="absolute rounded-full bg-white" 
          :style="{
            width: star.size + 'px',
            height: star.size + 'px',
            top: star.top + '%',
            left: star.left + '%',
            opacity: star.opacity
          }"
          :ref="el => { if (el) starRefs[index] = el }"
        ></div>
      </div>
      
      <!-- Hiệu ứng mưa -->
      <div class="absolute inset-0" ref="rainContainer"></div>
      
      <!-- Overlay gradient -->
      <div class="absolute inset-0 bg-gradient-to-t from-indigo-900/80 to-transparent"></div>
      
      <!-- Nội dung -->
      <div class="absolute inset-0 flex items-center justify-center">
        <div class="text-center p-6 bg-white/5 backdrop-blur-md rounded-xl w-64">
          <h3 class="text-xl font-bold text-white mb-3">Hiệu ứng mưa</h3>
          <p class="text-white/80 text-sm mb-4">Kết hợp Tailwind và GSAP để tạo hiệu ứng mưa sao</p>
          <button 
            @click="toggleRain" 
            class="px-4 py-2 bg-white/20 hover:bg-white/30 text-white rounded-lg transition"
          >
            {{ isRaining ? 'Dừng mưa' : 'Bắt đầu mưa' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, reactive, onMounted, onUnmounted } from 'vue';
import { gsap } from 'gsap';

export default {
  name: 'Test4',
  setup() {
    const rainContainer = ref(null);
    const starsContainer = ref(null);
    const raindrops = ref([]);
    const isRaining = ref(false);
    const stars = reactive([]);
    const starRefs = reactive([]);
    const animationFrameId = ref(null);
    
    // Tạo dữ liệu cho các ngôi sao
    const createStars = () => {
      for (let i = 0; i < 50; i++) {
        stars.push({
          size: Math.random() * 2 + 1,
          top: Math.random() * 100,
          left: Math.random() * 100,
          opacity: Math.random() * 0.5 + 0.1
        });
      }
    };
    
    // Animation cho các ngôi sao
    const animateStars = () => {
      starRefs.forEach((star, index) => {
        if (star) {
          gsap.to(star, {
            opacity: Math.random() * 0.5 + 0.1,
            duration: 1 + Math.random() * 3,
            repeat: -1,
            yoyo: true,
            ease: 'sine.inOut',
            delay: Math.random() * 2
          });
        }
      });
    };
    
    // Tạo một giọt mưa mới
    const createRaindrop = () => {
      if (!rainContainer.value || !isRaining.value) return;
      
      const raindrop = document.createElement('div');
      const size = Math.random() * 1 + 0.5;
      const startX = Math.random() * 100;
      const speed = 0.5 + Math.random() * 1;
      
      raindrop.className = 'absolute bg-blue-400/30 rounded-full';
      raindrop.style.width = `${size}px`;
      raindrop.style.height = `${size * 15}px`;
      raindrop.style.left = `${startX}%`;
      raindrop.style.top = '-20px';
      
      rainContainer.value.appendChild(raindrop);
      raindrops.value.push(raindrop);
      
      gsap.to(raindrop, {
        y: '120vh',
        x: 20 * (Math.random() - 0.5),
        duration: 2 / speed,
        ease: 'none',
        onComplete: () => {
          if (raindrop.parentNode) {
            raindrop.parentNode.removeChild(raindrop);
          }
          raindrops.value = raindrops.value.filter(drop => drop !== raindrop);
        }
      });
    };
    
    // Tạo hiệu ứng mưa
    const startRain = () => {
      if (isRaining.value) return;
      
      isRaining.value = true;
      
      const rainLoop = () => {
        // Tạo ngẫu nhiên từ 1-3 giọt mưa mỗi frame
        for (let i = 0; i < Math.floor(Math.random() * 3) + 1; i++) {
          createRaindrop();
        }
        
        if (isRaining.value) {
          animationFrameId.value = requestAnimationFrame(rainLoop);
        }
      };
      
      rainLoop();
    };
    
    const stopRain = () => {
      isRaining.value = false;
      
      if (animationFrameId.value) {
        cancelAnimationFrame(animationFrameId.value);
      }
      
      // Xóa tất cả các giọt mưa hiện tại
      raindrops.value.forEach(drop => {
        if (drop.parentNode) {
          gsap.to(drop, {
            opacity: 0,
            duration: 0.5,
            onComplete: () => {
              if (drop.parentNode) {
                drop.parentNode.removeChild(drop);
              }
            }
          });
        }
      });
      
      raindrops.value = [];
    };
    
    const toggleRain = () => {
      if (isRaining.value) {
        stopRain();
      } else {
        startRain();
      }
    };
    
    onMounted(() => {
      createStars();
      setTimeout(() => {
        animateStars();
      }, 100);
    });
    
    onUnmounted(() => {
      stopRain();
      gsap.killTweensOf(starRefs);
    });
    
    return {
      rainContainer,
      starsContainer,
      stars,
      starRefs,
      isRaining,
      toggleRain
    };
  }
};
</script>

<style>
/* Thêm hiệu ứng đuôi mờ cho giọt mưa */
.rain-drop {
  filter: blur(0.5px);
  box-shadow: 0 0 5px rgba(255, 255, 255, 0.3);
}
</style> 