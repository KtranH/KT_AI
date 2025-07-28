<template>
  <div class="min-h-screen bg-gradient-to-b from-blue-50 via-purple-50 to-pink-50 relative overflow-hidden">
    <!-- Header Section -->
    <div class="relative z-10 pt-6 pb-8">
      <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center">
          <div class="flex items-center justify-center mb-6">
            <div class="relative">
              <div class="absolute inset-0 bg-gradient-to-r from-blue-600 to-purple-600 rounded-full blur-lg opacity-75 animate-pulse"></div>
              <img :src="icon_title" loading="lazy" class="relative w-14 h-14 rounded-full border-4 border-white shadow-2xl" alt="AI Icon">
            </div>
          </div>
          
          <h1 class="text-4xl md:text-5xl font-black text-white mb-4 leading-tight">
            <span class="bg-gradient-to-r from-blue-400 via-purple-400 to-pink-400 bg-clip-text text-transparent">
              Chức năng tạo ảnh
            </span>
            <br>
            <span class="bg-gradient-to-r from-yellow-400 via-orange-400 to-red-400 bg-clip-text text-transparent">
              bằng AI
            </span>
          </h1>
          
          <p class="text-lg text-black max-w-2xl mx-auto leading-relaxed">
            Khám phá các tính năng AI tiên tiến để tạo ra những hình ảnh độc đáo và sáng tạo
          </p>
        </div>
      </div>
    </div>

    <!-- Error Message -->
    <div v-if="error_message" class="relative z-10 mb-6">
      <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-red-500/20 backdrop-blur-sm border border-red-500/30 rounded-xl p-4 text-center">
          <div class="flex items-center justify-center space-x-3">
            <svg class="w-6 h-6 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <span class="text-lg font-semibold text-red-400">Đã có lỗi xảy ra</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Features Carousel -->
    <div class="relative z-10 pb-8">
      <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Main Carousel Container -->
        <div class="relative">
          <!-- Carousel Track -->
          <div 
            ref="carouselTrack"
            class="flex transition-transform duration-700 ease-out"
            :style="{ transform: `translateX(-${currentFeatureIndex * 100}%)` }"
          >
            <div 
              v-for="(feature, index) in features"
              :key="index"
              class="w-full flex-shrink-0 px-4"
            >
              <div class="max-w-5xl mx-auto">
                <div class="grid lg:grid-cols-2 gap-8 items-center">
                  <!-- Feature Content -->
                  <div class="space-y-6" :class="{ 'lg:order-2': index % 2 === 0 }">
                    <div class="space-y-4">
                      <div class="inline-flex items-center px-3 py-1.5 rounded-full bg-gradient-to-r from-blue-500/20 to-purple-500/20 border border-blue-500/30 backdrop-blur-sm">
                        <span class="text-blue-400 font-semibold text-sm">Tính năng {{ index + 1 }}</span>
                      </div>
                      
                      <h2 class="text-3xl md:text-4xl font-bold text-white leading-tight">
                        {{ feature.name }}
                      </h2>
                      
                      <p class="text-lg text-black font-medium leading-relaxed">
                        {{ feature.description }}
                      </p>
                      
                      <div class="flex flex-wrap gap-2">
                        <div 
                          v-for="(tag, tagIndex) in feature.tags || []"
                          :key="tagIndex"
                          class="px-3 py-1.5 rounded-full bg-white/10 backdrop-blur-sm border border-white/20 text-white text-sm font-medium"
                        >
                          {{ tag }}
                        </div>
                      </div>
                    </div>
                    
                    <div class="flex flex-col sm:flex-row gap-3">
                      <router-link
                        :to="{ name: 'createimage', params: { encodedID: encodedID(feature.id) }}"
                        class="group relative inline-flex items-center justify-center px-6 py-3 bg-gradient-to-r from-blue-600 to-purple-600 text-white font-bold rounded-xl text-base transform transition-all duration-300 hover:scale-105 hover:shadow-2xl hover:shadow-blue-500/25"
                      >
                        <span class="relative z-10">Khám phá ngay</span>
                        <div class="absolute inset-0 bg-gradient-to-r from-purple-600 to-pink-600 rounded-xl opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                        <svg class="w-5 h-5 ml-2 group-hover:translate-x-1 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                        </svg>
                      </router-link>
                    </div>
                  </div>
                  
                  <!-- Feature Image -->
                  <div class="relative" :class="{ 'lg:order-1': index % 2 === 0 }">
                    <div class="relative group">
                      <!-- Glow Effect -->
                      <div class="absolute inset-0 bg-gradient-to-r from-blue-500/20 to-purple-500/20 rounded-2xl blur-2xl group-hover:blur-3xl transition-all duration-500"></div>
                      
                      <!-- Image Container -->
                      <div class="relative bg-gradient-to-br from-white/10 to-white/5 backdrop-blur-sm border border-white/20 rounded-2xl p-6 overflow-hidden">
                        <div class="absolute inset-0 bg-gradient-to-br from-blue-500/10 to-purple-500/10"></div>
                        
                        <img 
                          :src="feature.thumbnail_url || 'https://placehold.co/600x400/premium_photo.jpg'" 
                          :alt="feature.name"
                          class="w-full h-64 md:h-80 mx-auto rounded-xl object-cover transform transition-all duration-500 group-hover:scale-105"
                          loading="lazy"
                        />
                        
                        <!-- Overlay Effects -->
                        <div class="absolute inset-0 bg-gradient-to-t from-black/20 to-transparent rounded-xl"></div>
                        
                        <!-- Floating Elements -->
                        <div class="absolute top-3 right-3 w-3 h-3 bg-blue-400 rounded-full animate-ping"></div>
                        <div class="absolute bottom-3 left-3 w-2 h-2 bg-purple-400 rounded-full animate-pulse"></div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          
        </div>
        
        <!-- Progress Indicators -->
        <div class="flex justify-center mt-8 space-x-2">
          <button 
            v-for="(_, index) in features" 
            :key="index" 
            @click="goToFeature(index)"
            class="group relative"
          >
            <div class="w-3 h-3 rounded-full transition-all duration-300"
                 :class="currentFeatureIndex === index 
                   ? 'bg-gradient-to-r from-blue-500 to-purple-500 scale-125' 
                   : 'bg-gray-300 hover:bg-gray-400'">
            </div>
            <div v-if="currentFeatureIndex === index" 
                 class="absolute -inset-1.5 bg-gradient-to-r from-blue-500/20 to-purple-500/20 rounded-full blur-sm animate-pulse">
            </div>
          </button>
        </div>
        
        <!-- Navigation Arrows -->
        <div class="flex justify-center items-center mt-4 space-x-6 mb-8">
          <button 
            @click="prevFeature" 
            class="w-12 h-12 bg-gray-500 backdrop-blur-sm border border-white/20 rounded-full flex items-center justify-center text-white hover:bg-gray-600 transition-all duration-300 group"
            :class="{'opacity-50 cursor-not-allowed': currentFeatureIndex === 0}"
            :disabled="currentFeatureIndex === 0"
          >
            <svg class="w-6 h-6 group-hover:scale-110 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
          </button>
          
          <!-- Feature Counter -->
          <div class="text-center">
            <div class="inline-flex items-center px-4 py-2 bg-gray-500 backdrop-blur-sm border border-white/20 rounded-full">
              <span class="text-white font-semibold text-sm">
                {{ currentFeatureIndex + 1 }} / {{ features.length }}
              </span>
            </div>
          </div>

          <button 
            @click="nextFeature" 
            class="w-12 h-12 bg-gray-500 backdrop-blur-sm border border-white/20 rounded-full flex items-center justify-center text-white hover:bg-gray-600 transition-all duration-300 group"
            :class="{'opacity-50 cursor-not-allowed': currentFeatureIndex === features.length - 1}"
            :disabled="currentFeatureIndex === features.length - 1"
          >
            <svg class="w-6 h-6 group-hover:scale-110 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
            </svg>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted, onBeforeUnmount, computed } from 'vue';
import { useRouter } from 'vue-router';
import { usefeaturesStore } from '@/stores/user/featuresStore'
import { encodedID } from '@/utils'

export default {
  name: 'FeaturesV2',
  
  setup() {
    const router = useRouter()
    const carouselTrack = ref(null)
    const currentFeatureIndex = ref(0)
    const error_message = ref(null)
    const icon_title = ref('/img/ai.png')
    const storeFeatures = usefeaturesStore()

    // Dữ liệu tính năng
    const features = computed(() => storeFeatures.features) 
    
    // Navigation functions
    const goToFeature = (index) => {
      if (index >= 0 && index < features.value.length) {
        currentFeatureIndex.value = index
      }
    }
    
    const nextFeature = () => {
      if (currentFeatureIndex.value < features.value.length - 1) {
        goToFeature(currentFeatureIndex.value + 1)
      }
    }
    
    const prevFeature = () => {
      if (currentFeatureIndex.value > 0) {
        goToFeature(currentFeatureIndex.value - 1)
      }
    }
    
    // Keyboard navigation
    const handleKeydown = (e) => {
      if (e.key === 'ArrowDown' || e.key === 'ArrowRight') {
        nextFeature()
      } else if (e.key === 'ArrowUp' || e.key === 'ArrowLeft') {
        prevFeature()
      }
    }

    // Auto-play functionality
    let autoPlayInterval = null
    const startAutoPlay = () => {
      autoPlayInterval = setInterval(() => {
        if (currentFeatureIndex.value < features.value.length - 1) {
          nextFeature()
        } else {
          goToFeature(0)
        }
      }, 5000)
    }
    
    const stopAutoPlay = () => {
      if (autoPlayInterval) {
        clearInterval(autoPlayInterval)
        autoPlayInterval = null
      }
    }

    onMounted(() => {
      if(storeFeatures.features.length === 0) {
        storeFeatures.fetchFeatures();
      }
      
      window.addEventListener('keydown', handleKeydown)
      
      // Start auto-play
      startAutoPlay()
      
      // Pause auto-play on hover
      if (carouselTrack.value) {
        carouselTrack.value.addEventListener('mouseenter', stopAutoPlay)
        carouselTrack.value.addEventListener('mouseleave', startAutoPlay)
      }
    })
    
    onBeforeUnmount(() => {
      window.removeEventListener('keydown', handleKeydown)
      stopAutoPlay()
      
      if (carouselTrack.value) {
        carouselTrack.value.removeEventListener('mouseenter', stopAutoPlay)
        carouselTrack.value.removeEventListener('mouseleave', startAutoPlay)
      }
    })
    
    return {
      carouselTrack,
      features,
      currentFeatureIndex,
      error_message,
      icon_title,
      goToFeature,
      nextFeature,
      prevFeature,
      encodedID,
    }
  }
}
</script>

<style scoped>
@keyframes blob {
  0% {
    transform: translate(0px, 0px) scale(1);
  }
  33% {
    transform: translate(30px, -50px) scale(1.1);
  }
  66% {
    transform: translate(-20px, 20px) scale(0.9);
  }
  100% {
    transform: translate(0px, 0px) scale(1);
  }
}

.animate-blob {
  animation: blob 7s infinite;
}

.animation-delay-2000 {
  animation-delay: 2s;
}

.animation-delay-4000 {
  animation-delay: 4s;
}

/* Custom scrollbar */
.snap-container::-webkit-scrollbar {
  width: 8px;
}

.snap-container::-webkit-scrollbar-track {
  background: rgba(255, 255, 255, 0.1);
  border-radius: 4px;
}

.snap-container::-webkit-scrollbar-thumb {
  background: linear-gradient(to bottom, #3b82f6, #8b5cf6);
  border-radius: 4px;
}

.snap-container::-webkit-scrollbar-thumb:hover {
  background: linear-gradient(to bottom, #2563eb, #7c3aed);
}

/* Smooth transitions */
* {
  transition-property: all;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
  transition-duration: 300ms;
}

/* Glass morphism effect */
.backdrop-blur-sm {
  backdrop-filter: blur(8px);
  -webkit-backdrop-filter: blur(8px);
}

/* Gradient text animation */
@keyframes gradient-shift {
  0%, 100% {
    background-position: 0% 50%;
  }
  50% {
    background-position: 100% 50%;
  }
}

.bg-gradient-text {
  background-size: 200% 200%;
  animation: gradient-shift 3s ease infinite;
}
</style> 