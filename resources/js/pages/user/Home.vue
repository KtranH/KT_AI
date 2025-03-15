<template>
  <div id="fullpage-container">
    <!-- Hero Section -->
    <div class="section section-1">
      <div class="text-center text-white">
        <h1 class="text-6xl font-bold mb-4"><div data-aos="fade-up">AI Image Generator</div></h1>
        <p class="text-xl mb-8" data-aos="fade-up" data-aos-delay="200">Tạo hình ảnh tuyệt đẹp với các chức năng AI độc đáo</p>
        <div data-aos="fade-up" data-aos-delay="400">
          <button @click="scrollToSection(2)" class="bg-white text-purple-600 px-8 py-3 rounded-full font-bold hover:bg-opacity-90 transition">
            Khám phá ngay
          </button>
        </div>
      </div>
    </div>

    <!-- Features Section -->
    <div class="section section-2">
      <div class="container mx-auto px-4 py-20">
        <h2 class="text-4xl font-bold text-center mb-16">{{ countfeatures }} Chức Năng Tuyệt Vời</h2>
        
        <!-- Loading State -->
        <div v-if="loading" class="text-center">
          <p class="text-gray-600">Đang tải dữ liệu...</p>
        </div>
        
        <!-- Error State -->
        <div v-else-if="error" class="text-center">
          <p class="text-red-600">{{ error }}</p>
          <button @click="loadFeatures" class="mt-4 bg-purple-600 text-white px-6 py-2 rounded-full hover:bg-purple-700 transition">
            Thử lại
          </button>
        </div>
        
        <!-- Features Grid -->
        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
          <div v-for="feature in displayedFeatures" :key="feature.id"
              class="bg-white rounded-lg shadow-lg p-6 transform hover:scale-105 transition duration-300">
            <!-- Ẩn thanh trượt trong phần ảnh -->
            <div class="w-full h-48 bg-gray-200 rounded-lg mb-4 overflow-hidden">
              <img :src="feature.thumbnail_url" :alt="feature.title" loading="lazy" class="w-full h-full object-center object-cover"/>
            </div>
            <h3 class="text-xl font-bold mb-2">{{ feature.title }}</h3>
            <p class="text-gray-600">{{ feature.description }}</p>
            <div class="mt-4 flex justify-between items-center">
              <span class="text-sm text-purple-600 font-semibold">{{ feature.category }}</span>
              <button class="bg-purple-600 text-white px-6 py-2 rounded-full hover:bg-purple-700 transition">
                <span class="font-semibold">Thử ngay</span>
              </button>
            </div>
          </div>
        </div>

        <!-- Button Load more -->
        <div v-if="displayedFeatures.length > 0 && hasMore" class="text-center mt-12">
          <button @click="loadMoreFeatures" class="bg-purple-600 text-white px-8 py-3 rounded-full hover:bg-purple-700 transition">
            <span class="font-bold">Khám phá thêm</span>
          </button>
        </div>
      </div>
    </div>

    <!-- Call to Action -->
    <div class="section section-3">
      <div class="text-center text-white">
        <h2 class="text-4xl font-bold mb-4">Bắt đầu Sáng tạo Ngay</h2>
        <p class="text-xl mb-8">Khám phá sức mạnh của AI trong việc tạo hình ảnh</p>
        <router-link to="/register" class="bg-white text-purple-600 px-8 py-3 rounded-full font-bold hover:bg-opacity-90 transition inline-block">
          Đăng ký Miễn phí
        </router-link>
      </div>
    </div>

    <!-- Navigation dots -->
    <div class="pagination">
      <div class="dots">
        <div 
          v-for="n in 3" 
          :key="n" 
          class="dot" 
          :class="{ active: currentSection === n }"
          @click="scrollToSection(n)">
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue'
import 'aos/dist/aos.css'

//Call API from store 
import { usefeaturesStore } from '@/stores/user/featuresStore'

export default {
  name: 'Home',
  setup() {
    //State
    const displayedFeatures = ref([])
    const countfeatures = ref(4)
    const loading = ref(false)
    const error = ref(null)
    const page = ref(1)
    const perPage = ref(4)
    const hasMore = computed(() => displayedFeatures.value.length < features.value.length)
    const currentSection = ref(1)
    const isScrolling = ref(false)
    const storeFeatures = usefeaturesStore()
    const features = computed(() => storeFeatures.features)

    //Methods
    const scrollToSection = (sectionNumber) => {
      if (isScrolling.value) return
    
      isScrolling.value = true
      currentSection.value = sectionNumber
      
      const section = document.querySelector(`.section-${sectionNumber}`)
      if (section) {
        section.scrollIntoView({ behavior: 'smooth' })
        
        // Reset scrolling flag after animation completes
        setTimeout(() => {
          isScrolling.value = false
        }, 1000)
      }
    }

    const handleWheel = (event) => {
      if (isScrolling.value) {
        event.preventDefault()
        return
      }
      
      // Prevent default only for the section scrolling
      event.preventDefault()
      
      const direction = event.deltaY > 0 ? 1 : -1
      const nextSection = Math.min(Math.max(currentSection.value + direction, 1), 3)
      
      if (nextSection !== currentSection.value) {
        scrollToSection(nextSection)
      }
    }

    const observeSections = () => {
      const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
          if (entry.isIntersecting) {
            const sectionClass = entry.target.classList[1]; // section-1, section-2, etc.
            const sectionNumber = parseInt(sectionClass.split('-')[1])
            if (!isScrolling.value) {
              currentSection.value = sectionNumber
            }
          }
        })
      }, { threshold: 0.6 })
      
      document.querySelectorAll('.section').forEach(section => {
        observer.observe(section)
      })
      
      return observer
    }

    const loadFeatures = async () => {
        loading.value = true
        error.value = null

        try {
          await storeFeatures.fetchFeatures();
          displayedFeatures.value = features.value.slice(0, perPage.value);
        } catch (err) {
          error.value = err.response?.data?.message || 'Không thể lập dữ liệu'
        } finally {
          loading.value = false
        }
    }

    const loadMoreFeatures = () => {
      if (!hasMore.value) {
        return
      }
      
      page.value++;
      const start = (page.value - 1) * perPage.value
      const end = start + perPage.value
      const newItems = features.value.slice(start, end)
      countfeatures.value += 4
      displayedFeatures.value = [...displayedFeatures.value, ...newItems]
    }
    let observer
    
    // Mounted hook
    onMounted(() => { 
      // Load data
      loadFeatures()
      // Thêm event listener cho wheel event
      document.getElementById('fullpage-container').addEventListener('wheel', handleWheel, { passive: false })
      // Set up intersection observer
      observer = observeSections()
    })
  
    onBeforeUnmount(() => {
      // Clean up event listener
      document.getElementById('fullpage-container')?.removeEventListener('wheel', handleWheel)
      // Clean up observer
      if (observer) {
        observer.disconnect()
      }
    })

    return {
      features,
      displayedFeatures,
      countfeatures,
      loading,
      error,
      page,
      perPage,
      hasMore,
      currentSection,
      scrollToSection,
      loadFeatures,
      loadMoreFeatures
    }
  }
}
</script>

<style scoped>
#fullpage-container {
  height: 100vh;
  overflow: hidden;
  position: relative;
}

.section {
  height: 100vh;
  width: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  scroll-snap-align: start;
}

.section-1 {
  background: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('/img/banner.png');
  background-size: cover;
  background-position: center;
}

.section-2 {
  background-color: #f3f4f6;
  overflow-y: auto; /* Cho phép cuộn trong section features nếu nội dung dài */
}

.section-3 {
  background: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('/img/banner3.png');
  background-size: cover;
  background-position: center;
}

.pagination {
  position: fixed;
  right: 20px;
  top: 50%;
  transform: translateY(-50%);
  z-index: 10;
}

.dots {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.dot {
  width: 12px;
  height: 12px;
  border-radius: 50%;
  background-color: rgba(255, 255, 255, 0.5);
  cursor: pointer;
  transition: all 0.3s ease;
}

.dot.active {
  background-color: white;
  transform: scale(1.2);
}

/* Cho phép scroll trong grid features khi cần */
.grid {
  max-height: 60vh;
  overflow-y: auto;
}

/* Hiệu ứng transition mượt mà khi chuyển section */
html {
  scroll-behavior: smooth;
  scroll-snap-type: y mandatory;
}
</style>