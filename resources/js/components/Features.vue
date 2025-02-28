<template>
  <!-- Template code remains the same until the feature card div -->
  <div class="min-h-screen bg-gray-100 pt-24" data-aos = "zoom-out">
    <div class="max-w-[80%] mx-auto my-4">
      <h1 
        class="text-4xl font-bold text-center animate-gradient-text w-full"
      >
      <div class="flex items-center justify-center">
        <span class="text-3xl font-bold feature-title bg-gradient-text-v2 from-blue-600 to-purple-600 text-transparent bg-clip-text transform transition-all duration-500 mr-2">Chức năng tạo ảnh </span>
        <span class="bg-gradient-text rounded-full flex items-center justify-center p-2">
          <span class="text-white text-3xl ml-2 mr-2">bằng AI</span>
        </span>
        <img :src="icon_title" loading = "lazy" class="w-12 h-12 ml-2" alt="">
      </div>
      </h1>
      <h1 v-if="error_message === true" class="text-2xl font-bold text-center mb-2 text-red-600 bg-red-100 p-4 rounded-full">Đã có lỗi xảy ra</h1>
      
      <div class="relative">
        <div 
          class="snap-container h-[calc(100vh-200px)] overflow-y-auto snap-y snap-mandatory scroll-smooth mt-[-30px]" 
          ref="snapContainer"
        >
          <div 
            v-for="(feature, index) in features"
            :key="index"
            class="snap-start h-full flex items-center justify-center"
            :class="{'opacity-0': isScrolling}"
          >
            <div 
              class="feature-card bg-white rounded-xl shadow-xl overflow-hidden flex flex-col md:flex-row transform transition-all duration-700 hover:scale-[1.02] relative h-[600px] w-[1200px] group"
              :class="{'feature-active': currentFeatureIndex === index}"
            >
              <!-- Animated gradient background -->
              <div class="absolute inset-0 bg-gradient-to-r from-blue-500/0 via-purple-500/0 to-pink-500/0 opacity-0 group-hover:opacity-5 transition-all duration-700"></div>
              
              <!-- Animated particles background -->
              <div class="absolute inset-0 overflow-hidden opacity-0 group-hover:opacity-10 transition-all duration-700">
                <div class="particles-bg"></div>
              </div>

              <!-- Gradient border effect -->
              <div class="absolute -inset-[2px] bg-gradient-to-r from-blue-500 via-purple-500 to-pink-500 rounded-xl opacity-0 group-hover:opacity-30 blur-xl transition-all duration-700"></div>
              
              <!-- Glass effect overlay -->
              <div class="absolute inset-0 bg-white/80 backdrop-blur-sm opacity-0 group-hover:opacity-20 transition-all duration-700"></div>

              <!-- Main content remains the same -->
              <div class="md:w-1/2 p-12 flex flex-col justify-center relative z-10 transform transition-transform duration-700 hover:translate-x-2 bg-gradient-to-r from-transparent via-white/50 to-transparent group-hover:from-blue-50/50 group-hover:via-purple-50/50 group-hover:to-pink-50/50">
                <!-- Content remains the same -->
                <span class="inline-block feature-number bg-gradient-to-r from-blue-600 to-purple-600 text-transparent bg-clip-text font-semibold mb-2 transform transition-all duration-500">
                  #{{ feature.id }} - Tổng số ảnh đã tạo ({{ feature.sum_img }})
                </span>
                <h2 class="text-3xl font-bold feature-title bg-gradient-to-r from-blue-600 to-purple-600 text-transparent bg-clip-text mb-6 transform transition-all duration-500">
                  {{ feature.title }}
                </h2>
                <p class="text-gray-600 mb-4 leading-relaxed text-lg feature-description opacity-0 transform translate-y-4 transition-all duration-700">
                  {{ feature.description }}.
                </p>
                <hr class="mb-4">
                <h2 class="text-3xl font-bold feature-title bg-gradient-to-r from-blue-600 to-purple-600 text-transparent bg-clip-text mb-6 transform transition-all duration-500">Thông tin</h2>
                <ul class="text-gray-600 mb-8 leading-relaxed text-lg feature-description opacity-0 transform translate-y-4 transition-all duration-700 list-disc pl-6">
                  <li class="mb-2"><span class="font-semibold">Lời nhắc khuyến nghị:</span> {{ feature.prompt_template }}</li>
                  <li class="mb-2"><span class="font-semibold">Yêu cầu đầu vào: </span>{{ feature.input_requirements ? feature.input_requirements : 'Không' }}.</li>
                  <li class="mb-2"><span class="font-semibold">Thể loại: </span>{{ feature.category }}.</li>
                  <li class="mb-2"><span class="font-semibold">Thời gian tạo ảnh: </span>{{ feature.average_processing_time }}.</li>
                  <li class="mb-2"><span class="font-semibold">Chi phí: </span>{{ feature.creadit_cost }}.</li>
                </ul>
                <button class="relative overflow-hidden w-fit px-8 py-3 bg-gradient-to-r from-blue-600 to-purple-600 text-white rounded-full transition-all duration-500 hover:scale-105 shadow-lg hover:shadow-xl hover:shadow-purple-500/20 group">
                  <router-link :to="{ name: 'createimage', params: { encodedID: encodeID(feature.id) }}" class="relative z-10 font-semibold">Thử ngay</router-link>
                  <div class="absolute inset-0 bg-gradient-to-r from-purple-600 to-blue-600 opacity-0 group-hover:opacity-100 transition-all duration-500"></div>
                  <div class="absolute -inset-1 bg-gradient-to-r from-blue-500 to-purple-500 opacity-0 group-hover:opacity-20 blur-lg transition-all duration-500"></div>
                </button>
              </div>
              <!-- Image section with enhanced background -->
              <div class="md:w-1/2 relative h-96 md:h-auto overflow-hidden bg-gradient-to-r from-transparent via-white/50 to-transparent group-hover:from-blue-50/30 group-hover:via-purple-50/30 group-hover:to-pink-50/30">
                <img 
                  :src="feature.thumbnail_url"
                  class="absolute inset-0 w-full h-full object-cover"
                  loading="lazy"
                />
              </div>
            </div>
          </div>
        </div>

        <!-- Enhanced feature navigation dots -->
        <div class="fixed bottom-6 left-1/2 transform -translate-x-1/2 flex space-x-2 z-20 flex-wrap justify-center max-w-[300px]">
          <button
            v-for="(feature, index) in features"
            :key="index"
            @click="scrollToFeature(index)"
            class="w-2 h-2 rounded-full transition-all duration-500 group relative mb-2"
          >
            <span 
              class="absolute inset-0 rounded-full transition-all duration-300"
              :class="currentFeatureIndex === index ? 'bg-gradient-to-r from-blue-600 to-purple-600 scale-125' : 'bg-gray-300 group-hover:bg-gray-400'"
            ></span>
            <span 
              class="absolute inset-0 rounded-full bg-gradient-to-r from-blue-600 to-purple-600 transform scale-0 opacity-0 transition-all duration-300 group-hover:scale-150 group-hover:opacity-30 blur"
            ></span>
          </button>
        </div>

        <!-- Enhanced progress bar -->
        <div 
          class="progress-bar" 
          :style="{ width: `${scrollProgress}%` }"
        ></div>

        <!-- Enhanced back to top button -->
        <button 
          v-if="showBackToTop" 
          @click="scrollToTop" 
          class="fixed bottom-16 right-4 bg-gradient-to-r from-blue-600 to-purple-600 text-white rounded-full p-3 shadow-lg transition-all duration-500 hover:scale-110 hover:shadow-xl hover:shadow-purple-500/20 group"
        >
          <div class="relative">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 transition-transform duration-500 group-hover:-translate-y-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" />
            </svg>
            <div class="absolute inset-0 bg-white opacity-0 group-hover:opacity-20 rounded-full blur transition-all duration-500"></div>
          </div>
        </button>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted, onUnmounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import { usefeaturesStore } from '@/stores/features'
import AOS from 'aos'

export default {
  name: 'Features',
  setup() {
    const router = useRouter()
    const storeFeatures = usefeaturesStore()
    const error_message = ref(false)
    const icon_title = ref("/img/ai.png")
    const features = computed(() => storeFeatures.features);
    const currentFeatureIndex = ref(0)
    const scrollProgress = ref(0)
    const showBackToTop = ref(false)
    const snapContainer = ref(null)
    const isScrolling = ref(false)
    let scrollTimeout

    const scrollToFeature = (index) => {
      currentFeatureIndex.value = index
      const featureElement = document.querySelector(`.snap-start:nth-child(${index + 1})`)
      featureElement.scrollIntoView({ behavior: 'smooth' })
    }

    const scrollToTop = () => {
      snapContainer.value.scrollTo({ top: 0, behavior: 'smooth' })
    }

    const updateScrollProgress = () => {
      const container = snapContainer.value
      if (!container) return
      
      const scrollTop = container.scrollTop
      const scrollHeight = container.scrollHeight - container.clientHeight
      scrollProgress.value = scrollHeight > 0 ? (scrollTop / scrollHeight) * 100 : 0
      showBackToTop.value = scrollTop > 100

      // Cập nhật currentFeatureIndex dựa trên vị trí cuộn
      const featureHeight = container.scrollHeight / features.value.length
      const newIndex = Math.round(scrollTop / featureHeight)
      if (newIndex !== currentFeatureIndex.value && newIndex >= 0 && newIndex < features.value.length) {
        currentFeatureIndex.value = newIndex
      }
    }
    
    const handleScroll = () => {
      isScrolling.value = true
      updateScrollProgress()
      
      clearTimeout(scrollTimeout)
      scrollTimeout = setTimeout(() => {
        isScrolling.value = false
      }, 150)
    }

    //Encode ID
    const encodeID = (id) => {
      return btoa(id)
    }
    
    onMounted(() => {
      if (storeFeatures.features.length === 0) {
        storeFeatures.fetchFeatures();
      }
      AOS.refresh()
      if (snapContainer.value) {
        snapContainer.value.addEventListener('scroll', handleScroll)
      }
    })
    onUnmounted(() => {
      if (snapContainer.value) {
        snapContainer.value.removeEventListener('scroll', handleScroll)
      }
      clearTimeout(scrollTimeout)
    })

    return {
      features,
      currentFeatureIndex,
      scrollToFeature,
      scrollToTop,
      scrollProgress,
      showBackToTop,
      snapContainer,
      router,
      isScrolling,
      error_message,
      icon_title,
      encodeID
    }
  }
}
</script>

<style scoped>
/* Enhanced feature card animations */
.feature-card {
  transform-style: preserve-3d;
  perspective: 1000px;
}

.feature-active .feature-number {
  transform: translateY(0);
  opacity: 1;
}

.feature-active .feature-title {
  transform: translateY(0);
  opacity: 1;
}

.feature-active .feature-description {
  transform: translateY(0);
  opacity: 1;
}

/* Enhanced image transitions */
.slide-fade-enter-active,
.slide-fade-leave-active {
  transition: all 1s cubic-bezier(0.4, 0, 0.2, 1);
}

.slide-fade-enter-from {
  opacity: 0;
  transform: scale(1.1);
}

.slide-fade-leave-to {
  opacity: 0;
  transform: scale(0.9);
}

/* Enhanced progress bar */
.progress-bar {
  position: fixed;
  top: 0;
  left: 0;
  height: 4px;
  background: linear-gradient(90deg, #3b82f6, #8b5cf6, #3b82f6);
  background-size: 200% 100%;
  animation: gradient-move 3s ease infinite;
  transition: width 0.3s ease;
  z-index: 30;
}

@keyframes gradient-move {
  0% { background-position: 100% 0%; }
  100% { background-position: -100% 0%; }
}

/* Smooth scrolling */
.scroll-smooth {
  scroll-behavior: smooth;
  -webkit-overflow-scrolling: touch;
}

/* Snap scrolling */
.snap-container {
  scroll-snap-type: y mandatory;
  -webkit-overflow-scrolling: touch;
}

.snap-start {
  scroll-snap-align: start;
  scroll-snap-stop: always;
}
/* Enhanced background animations */
.feature-card {
  transform-style: preserve-3d;
  perspective: 1000px;
  isolation: isolate;
}

.feature-card::before {
  content: '';
  position: absolute;
  inset: 0;
  background: linear-gradient(
    45deg,
    transparent 0%,
    rgba(59, 130, 246, 0.1) 25%,
    rgba(139, 92, 246, 0.1) 50%,
    rgba(236, 72, 153, 0.1) 75%,
    transparent 100%
  );
  opacity: 0;
  transition: opacity 0.7s ease;
  z-index: 1;
}

.feature-card:hover::before {
  opacity: 1;
}

/* Particles background animation */
.particles-bg {
  position: absolute;
  inset: -100%;
  background-image: 
    radial-gradient(circle, rgba(59, 130, 246, 0.1) 2px, transparent 2px),
    radial-gradient(circle, rgba(139, 92, 246, 0.1) 2px, transparent 2px),
    radial-gradient(circle, rgba(236, 72, 153, 0.1) 2px, transparent 2px);
  background-size: 40px 40px;
  background-position: 0 0, 20px 20px, 40px 40px;
  animation: particleMove 20s linear infinite;
}

@keyframes particleMove {
  0% {
    transform: translateY(0) rotate(0deg);
  }
  100% {
    transform: translateY(100%) rotate(45deg);
  }
}

/* Gradient text effect */
.bg-gradient-text-v2 {
  background: linear-gradient(
    -45deg,
    #3b82f6,
    #6366f1,
    #8b5cf6,
    #ec4899,
    #3b82f6
  );
  background-size: 400%;
  animation: gradient-animation 8s ease infinite;
  
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}

/* Enhanced gradient animation */
.bg-gradient-text {
  background: linear-gradient(
    -45deg,
    #3b82f6,
    #6366f1,
    #8b5cf6,
    #ec4899,
    #3b82f6
  );
  background-size: 400%;
  animation: gradient-animation 8s ease infinite;
}

@keyframes gradient-animation {
  0% { background-position: 0% 50%; }
  50% { background-position: 100% 50%; }
  100% { background-position: 0% 50%; }
}

/* Previous styles remain */
</style>