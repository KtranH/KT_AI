<template>
  <div>
    <h2 class="text-2xl font-bold mb-4">Test 3</h2>
    <p class="mb-4">Đây là trang test số 3 với hiệu ứng animation cards sử dụng GSAP.</p>
    
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-8">
      <div 
        v-for="(card, index) in cards" 
        :key="index"
        class="relative overflow-hidden rounded-xl shadow-lg transition-all duration-300 hover:shadow-xl"
        :ref="el => { if (el) cardRefs[index] = el }"
        @mouseenter="animateCard(index, true)"
        @mouseleave="animateCard(index, false)"
      >
        <div class="aspect-w-3 aspect-h-4 bg-gradient-to-br" :class="card.gradient">
          <div class="absolute inset-0 p-6 flex flex-col justify-between">
            <div class="card-icon text-white/80 text-3xl">
              <i :class="card.icon"></i>
            </div>
            
            <div>
              <h3 class="text-xl font-bold text-white mb-2">{{ card.title }}</h3>
              <p class="text-white/80 text-sm">{{ card.description }}</p>
            </div>
          </div>
          
          <!-- Animated background elements -->
          <div class="absolute inset-0 z-0 overflow-hidden">
            <div 
              :ref="el => { if (el) bgElements[index] = el }"
              class="absolute -bottom-16 -right-16 w-32 h-32 bg-white/10 rounded-full"
            ></div>
            <div 
              :ref="el => { if (el) bgElements2[index] = el }"
              class="absolute -top-8 -left-8 w-24 h-24 bg-white/10 rounded-full"
            ></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted, reactive } from 'vue';
import { gsap } from 'gsap';

export default {
  name: 'Test3',
  setup() {
    const cards = [
      {
        title: 'Card 1',
        description: 'Di chuột vào để thấy hiệu ứng animation trên card.',
        gradient: 'from-blue-600 to-indigo-700',
        icon: 'fas fa-star'
      },
      {
        title: 'Card 2',
        description: 'Mỗi card có hiệu ứng animation khác nhau khi hover.',
        gradient: 'from-purple-600 to-pink-600',
        icon: 'fas fa-heart'
      },
      {
        title: 'Card 3',
        description: 'Sử dụng GSAP để tạo hiệu ứng mượt mà và chuyên nghiệp.',
        gradient: 'from-emerald-600 to-cyan-700',
        icon: 'fas fa-rocket'
      }
    ];
    
    const cardRefs = reactive([]);
    const bgElements = reactive([]);
    const bgElements2 = reactive([]);
    
    const animateCard = (index, isEnter) => {
      const card = cardRefs[index];
      const bg1 = bgElements[index];
      const bg2 = bgElements2[index];
      
      if (isEnter) {
        // Animation khi hover vào
        gsap.to(card, {
          y: -5,
          scale: 1.03,
          duration: 0.3,
          ease: 'power2.out',
        });
        
        gsap.to(bg1, {
          scale: 1.2,
          x: -10,
          y: -10,
          opacity: 0.8,
          duration: 5,
          ease: 'sine.inOut',
          repeat: -1,
          yoyo: true
        });
        
        gsap.to(bg2, {
          scale: 1.3,
          x: 15,
          y: 15,
          opacity: 0.6,
          duration: 4,
          ease: 'sine.inOut',
          repeat: -1,
          yoyo: true,
          delay: 0.2
        });
      } else {
        // Animation khi không hover
        gsap.to(card, {
          y: 0,
          scale: 1,
          duration: 0.3,
          ease: 'power2.out',
        });
        
        // Dừng animation của các phần tử nền
        gsap.killTweensOf(bg1);
        gsap.killTweensOf(bg2);
        
        gsap.to(bg1, {
          scale: 1,
          x: 0,
          y: 0,
          opacity: 0.3,
          duration: 0.3,
        });
        
        gsap.to(bg2, {
          scale: 1,
          x: 0,
          y: 0,
          opacity: 0.3,
          duration: 0.3,
        });
      }
    };
    
    onMounted(() => {
      // Animation ban đầu khi component render
      cardRefs.forEach((card, index) => {
        gsap.from(card, {
          y: 30,
          opacity: 0,
          duration: 0.6,
          delay: index * 0.1,
          ease: 'power3.out'
        });
      });
    });
    
    return {
      cards,
      cardRefs,
      bgElements,
      bgElements2,
      animateCard
    };
  }
};
</script>

<style>
/* Đảm bảo aspect ratio của card */
.aspect-w-3 {
  position: relative;
  padding-bottom: calc(4 / 3 * 100%);
}

.aspect-w-3 > * {
  position: absolute;
  height: 100%;
  width: 100%;
  top: 0;
  right: 0;
  bottom: 0;
  left: 0;
}
</style> 