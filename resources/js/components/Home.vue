<template>
  <full-page ref="fullpage" :options="options" id="fullpage">
    <!-- Hero Section -->
    <div class="section" style="background: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('/img/banner.png'); background-size: cover; background-position: center;">
      <div class="text-center text-white">
        <h1 class="text-6xl font-bold mb-4"><div data-aos="fade-up">AI Image Generator</div></h1>
        <p class="text-xl mb-8" data-aos="fade-up" data-aos-delay="200">Tạo hình ảnh tuyệt đẹp với các chức năng AI độc đáo</p>
        <div data-aos="fade-up" data-aos-delay="400">
          <button @click="moveTo(2)" class="bg-white text-purple-600 px-8 py-3 rounded-full font-bold hover:bg-opacity-90 transition">
            Khám phá ngay
          </button>
        </div>
      </div>
    </div>

    <!-- Features Section -->
    <div class="section bg-gray-100">
      <div class="container mx-auto px-4 py-20">
        <h2 class="text-4xl font-bold text-center mb-16">{{ countfeatures }} Chức Năng Tuyệt Vời</h2>
        
        <!-- Loading State -->
        <div v-if="loading" class="text-center">
          <p class="text-gray-600">Đang tải dữ liệu...</p>
        </div>
        
        <!-- Error State -->
        <div v-else-if="error" class="text-center">
          <p class="text-red-600">{{ error }}</p>
          <button @click="fetchFeatures" class="mt-4 bg-purple-600 text-white px-6 py-2 rounded-full hover:bg-purple-700 transition">
            Thử lại
          </button>
        </div>
        
        <!-- Features Grid -->
        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
          <div v-for="feature in displayedFeatures" :key="feature.id"
              class="bg-white rounded-lg shadow-lg p-6 transform hover:scale-105 transition duration-300">
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
    <div class="section" style="background: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('/img/banner3.png'); background-size: cover; background-position: center;">
      <div class="text-center text-white">
        <h2 class="text-4xl font-bold mb-4">Bắt đầu Sáng tạo Ngay</h2>
        <p class="text-xl mb-8">Khám phá sức mạnh của AI trong việc tạo hình ảnh</p>
        <router-link to="/register" class="bg-white text-purple-600 px-8 py-3 rounded-full font-bold hover:bg-opacity-90 transition inline-block">
          Đăng ký Miễn phí
        </router-link>
      </div>
    </div>
  </full-page>
</template>

<script>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';
import AOS from 'aos';
import 'aos/dist/aos.css';
import 'fullpage.js/dist/fullpage.css';

export default {
  name: 'Home',
  setup() {
    const options = ref({
      licenseKey: "",
      scrollingSpeed: 1000,
      navigation: true,
      anchors: ['home', 'features', 'cta']
    });
    
    const features = ref([]); // Tất cả các features được tải từ API
    const displayedFeatures = ref([]); // Các features hiện đang hiển thị
    const countfeatures = ref(0); // Tổng số features
    const loading = ref(false);
    const error = ref(null);
    const page = ref(1); // Trang hiện tại
    const perPage = ref(4); // Số lượng items mỗi trang
    const hasMore = computed(() => displayedFeatures.value.length < features.value.length);
    
    const moveTo = (section) => {
      document.querySelector(`#${section}`).scrollIntoView({ behavior: 'smooth' });
    };

    const fetchFeatures = async () => {
      loading.value = true;
      error.value = null;
      
      try {
        console.log('Đang tải dữ liệu chức năng...');
        const response = await axios.get('/api/features', {
          headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
          }
        });
        
        console.log('Phản hồi từ API:', response.data);
        
        if (response.data && response.data.success) {
          features.value = response.data.data;
          console.log('Đã tải thành công', features.value.length, 'chức năng');
          
          // Hiển thị chỉ 4 features đầu tiên
          displayedFeatures.value = features.value.slice(0, perPage.value);
          countfeatures.value = 4;
        } else {
          console.error('Dữ liệu không hợp lệ:', response.data);
          error.value = response.data?.message || 'Dữ liệu không hợp lệ';
        }
      } catch (err) {
        console.error('Lỗi khi tải dữ liệu:', err);
        error.value = err.response?.data?.message || 'Không thể kết nối đến máy chủ';
      } finally {
        loading.value = false;
      }
    };

    const loadMoreFeatures = () => {
      if (!hasMore.value)
      {
        return;
      }
      
      page.value++;
      const start = (page.value - 1) * perPage.value;
      const end = start + perPage.value;
      const newItems = features.value.slice(start, end);

      countfeatures.value += 4;
      
      displayedFeatures.value = [...displayedFeatures.value, ...newItems];
    };
    
    onMounted(() => {
      AOS.init({
        duration: 800,
        delay: 500,
        once: false,
        offset: 150,
        easing: 'ease-in-sine',
      });
      fetchFeatures();
    });

    return {
      options,
      features,
      displayedFeatures,
      countfeatures,
      loading,
      error,
      page,
      perPage,
      hasMore,
      moveTo,
      fetchFeatures,
      loadMoreFeatures
    };
  }
};
</script>

<style scoped>
.section {
  display: flex;
  align-items: center;
  justify-content: center;
}

#fp-nav ul li a span {
  background: white !important;
}

#fp-nav ul li a.active span {
  background: #7c3aed !important;
}
</style> 