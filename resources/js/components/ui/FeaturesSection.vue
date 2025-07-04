<template>
  <section id="features" class="py-20 bg-gray-50">
    <div class="container mx-auto px-6">
      <div class="text-center mb-16">
        <h2 class="text-3xl md:text-4xl font-bold text-gray-800 mb-4" data-aos="fade-up">{{ countfeatures }} Công Cụ Sáng Tạo Mạnh Mẽ</h2>
        <p class="text-xl text-gray-600 max-w-2xl mx-auto" data-aos="fade-up" data-aos-delay="100">
          Tất cả những gì bạn cần để biến tưởng tượng thành hiện thực với các công cụ trực quan và chuyên nghiệp.
        </p>
      </div>
      
      <!-- Loading State -->
      <div v-if="loading" class="text-center py-12">
        <div class="inline-block animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-indigo-600"></div>
        <p class="mt-4 text-gray-600">Đang tải dữ liệu...</p>
      </div>
      
      <!-- Error State -->
      <div v-else-if="error" class="text-center py-12">
        <div class="inline-block p-3 rounded-full bg-red-100 mb-4">
          <i class="fas fa-exclamation-triangle text-2xl text-red-600"></i>
        </div>
        <p class="text-red-600 mb-4">{{ error }}</p>
        <button @click="loadFeatures" class="px-6 py-2 bg-indigo-600 text-white font-medium rounded-lg hover:bg-indigo-700 transition-all transform hover:scale-105 shadow-lg">
          Thử lại
        </button>
      </div>
      
      <!-- Features Grid -->
      <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        <div v-for="feature in displayedFeatures" :key="feature.id" 
            class="bg-white rounded-xl shadow-lg p-8 transform hover:scale-105 transition-all duration-300 cursor-pointer" 
            data-aos="fade-up" :data-aos-delay="100 * (feature.id % 4)">
          <div class="w-16 h-16 bg-indigo-100 rounded-full flex items-center justify-center mb-6 mx-auto md:mx-0">
            <img :src="feature.thumbnail_url" alt="Feature Image" class="w-full h-full object-cover rounded-full">
          </div>
          <h3 class="text-xl font-bold text-gray-800">{{ feature.title }}</h3>
          <span class="text-sm text-indigo-600 font-semibold">{{ feature.category }}</span>
          <p class="text-gray-600 mb-4">{{ feature.description }}</p>
          <div class="mt-4 flex justify-between items-center">
            <router-link :to="`/features/${feature.id}`" class="absolute top-2 right-2 px-4 py-2 bg-indigo-600 text-white font-medium rounded-lg hover:bg-indigo-700 transition-all transform hover:scale-105">
              Thử ngay
            </router-link>
          </div>
        </div>
      </div>
      
      <!-- Button Load more -->
      <div v-if="displayedFeatures.length > 0 && hasMore" class="text-center mt-12">
        <button @click="loadMoreFeatures" class="px-6 py-3 bg-indigo-600 text-white font-medium rounded-lg hover:bg-indigo-700 transition-all transform hover:scale-105 shadow-lg cursor-pointer" data-aos="fade-up">
          Khám phá thêm
        </button>
      </div>
    </div>
  </section>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { usefeaturesStore } from '@/stores/user/featuresStore';

// State
const displayedFeatures = ref([]);
const countfeatures = ref(6);
const loading = ref(false);
const error = ref(null);
const page = ref(1);
const perPage = ref(6);

// Store
const storeFeatures = usefeaturesStore();
const features = computed(() => storeFeatures.features);
const hasMore = computed(() => displayedFeatures.value.length < features.value.length);

// Methods
const loadFeatures = async () => {
  loading.value = true;
  error.value = null;

  try {
    await storeFeatures.fetchFeatures();
    displayedFeatures.value = features.value.slice(0, perPage.value);
  } catch (err) {
    error.value = err.response?.data?.message || 'Không thể tải dữ liệu';
  } finally {
    loading.value = false;
  }
};

const loadMoreFeatures = () => {
  if (!hasMore.value) {
    return;
  }
  
  page.value++;
  const start = (page.value - 1) * perPage.value;
  const end = start + perPage.value;
  const newItems = features.value.slice(start, end);
  countfeatures.value += perPage.value;
  displayedFeatures.value = [...displayedFeatures.value, ...newItems];
};

// Lifecycle hooks
onMounted(() => {
  // Load features
  loadFeatures();
});
</script>

<style scoped>
/* Animation styling */
@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.animate-spin {
  animation: spin 1s linear infinite;
}
</style> 