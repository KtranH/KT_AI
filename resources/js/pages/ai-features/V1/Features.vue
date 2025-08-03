<template>
  <div class="min-h-screen bg-gray-100 pt-4" data-aos="zoom-out">
    <div class="max-w-[90%] mx-auto my-4">
      <h1 class="text-3xl font-extrabold text-center w-full">
        <div class="flex items-center justify-center">
          <span class="text-3xl feature-title bg-gradient-text-v2 from-blue-600 to-purple-600 text-transparent bg-clip-text transform transition-all duration-500 mr-2">Chức năng tạo ảnh </span>
          <span class="bg-gradient-text rounded-full flex items-center justify-center p-2">
            <span class="text-white text-3xl ml-2 mr-2">bằng AI</span>
          </span>
          <img :src="icon_title" loading="lazy" class="w-12 h-12 ml-2" alt="">
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
            <FeatureCard :feature="feature" :isActive="currentFeatureIndex === index">
              <template #feature-info>
                <FeatureInfo 
                  :info="feature"
                  :hasCTA="true"
                >
                  <template #cta>
                    <router-link
                      :to="{ name: 'createimage', params: { encodedID: encodedID(feature.id) }}"
                      class="inline-block mt-8 px-6 py-3 bg-gradient-to-r from-blue-500 to-purple-600 text-white font-semibold rounded-xl transform transition-all duration-500 hover:scale-105 hover:shadow-lg"
                    >
                      Xem chi tiết
                    </router-link>
                  </template>
                </FeatureInfo>
              </template>
              
              <template #feature-image>
                <FeatureImage 
                  :feature="feature" 
                  :imageUrl="feature.thumbnail_url || 'https://placehold.co/600x400/premium_photo.jpg'" 
                >
                  <template #animation-elements>
                    <!-- Animation elements if needed -->
                    <div class="absolute -bottom-10 -left-10 w-32 h-32 bg-blue-500/10 rounded-full filter blur-xl animate-pulse"></div>
                    <div class="absolute -top-10 -right-10 w-32 h-32 bg-purple-500/10 rounded-full filter blur-xl animate-pulse delay-700"></div>
                  </template>
                </FeatureImage>
              </template>
            </FeatureCard>
          </div>
        </div>
        
        <!-- Navigation arrows -->
        <div class="absolute top-1/2 -left-12 transform -translate-y-1/2">
          <button 
            @click="prevFeature" 
            class="w-10 h-10 rounded-full bg-white/80 shadow-lg flex items-center justify-center hover:bg-white transition-all duration-300"
            :class="{'opacity-50 cursor-not-allowed': currentFeatureIndex === 0}"
            :disabled="currentFeatureIndex === 0"
          >
            <svg class="w-6 h-6 text-gray-800" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
          </button>
        </div>
        
        <div class="absolute top-1/2 -right-12 transform -translate-y-1/2">
          <button 
            @click="nextFeature" 
            class="w-10 h-10 rounded-full bg-white/80 shadow-lg flex items-center justify-center hover:bg-white transition-all duration-300"
            :class="{'opacity-50 cursor-not-allowed': currentFeatureIndex === features.length - 1}"
            :disabled="currentFeatureIndex === features.length - 1"
          >
            <svg class="w-6 h-6 text-gray-800" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
          </button>
        </div>
        
        <!-- Progress indicators -->
        <div class="absolute bottom-4 left-1/2 transform -translate-x-1/2 flex space-x-2">
          <button 
            v-for="(_, index) in features" 
            :key="index" 
            @click="goToFeature(index)"
            class="w-3 h-3 rounded-full transition-all duration-300"
            :class="currentFeatureIndex === index ? 'bg-blue-600 scale-125' : 'bg-gray-300 hover:bg-gray-400'"
          ></button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { FeatureCard, FeatureImage, FeatureInfo } from '@/components/features/images'
import { ref, onMounted, onBeforeUnmount, computed } from 'vue';
import { useRouter } from 'vue-router';
import { usefeaturesStore } from '@/stores/user/featuresStore'
import { encodedID } from '@/utils'

export default {
  name: 'Features',
  components: {
    FeatureCard,
    FeatureImage,
    FeatureInfo
  },
  
  setup() {
    const router = useRouter()
    const snapContainer = ref(null)
    const currentFeatureIndex = ref(0)
    const isScrolling = ref(false)
    const error_message = ref(null)
    const icon_title = ref('/img/ai.png')
    const storeFeatures = usefeaturesStore()

    // Dữ liệu tính năng (nên được lấy từ API)
    const features = computed(() => storeFeatures.features) 
    
    // Xử lý scroll và navigation
    const handleScroll = () => {
      if (!snapContainer.value) return
      
      const containerHeight = snapContainer.value.clientHeight
      const scrollPosition = snapContainer.value.scrollTop
      const newIndex = Math.round(scrollPosition / containerHeight)
      
      if (newIndex !== currentFeatureIndex.value) {
        currentFeatureIndex.value = newIndex
      }
    }
    
    const goToFeature = (index) => {
      if (!snapContainer.value) return
      
      isScrolling.value = true
      currentFeatureIndex.value = index
      
      snapContainer.value.scrollTo({
        top: index * snapContainer.value.clientHeight,
        behavior: 'smooth'
      });
      
      setTimeout(() => {
        isScrolling.value = false
      }, 700)
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
    
    const handleKeydown = (e) => {
      if (e.key === 'ArrowDown' || e.key === 'ArrowRight') {
        nextFeature()
      } else if (e.key === 'ArrowUp' || e.key === 'ArrowLeft') {
        prevFeature()
      }
    }

    onMounted(() => {
      if(storeFeatures.features.length === 0)
      {
        storeFeatures.fetchFeatures();
      }
      if (snapContainer.value) {
        snapContainer.value.addEventListener('scroll', handleScroll)
      }
      window.addEventListener('keydown', handleKeydown)
      
      // Kích hoạt animation cho feature đầu tiên
      setTimeout(() => {
        const firstFeature = document.querySelector('.feature-card')
        if (firstFeature) {
          firstFeature.classList.add('feature-active')
        }
      }, 500)
    })
    
    onBeforeUnmount(() => {
      if (snapContainer.value) {
        snapContainer.value.removeEventListener('scroll', handleScroll)
      }
      window.removeEventListener('keydown', handleKeydown)
    })
    
    return {
      snapContainer,
      features,
      currentFeatureIndex,
      isScrolling,
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