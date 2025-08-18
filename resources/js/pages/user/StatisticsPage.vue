<template>
  <div class="p-6 min-h-screen">
    <!-- Loading overlay -->
    <div v-if="loading && !isDataLoaded" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg p-8 flex flex-col items-center">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mb-4"></div>
        <p class="text-gray-700 font-medium">Đang tải dữ liệu thống kê...</p>
      </div>
    </div>
    <!-- Header -->
    <div class="mb-10 relative">
      <div class="absolute left-0 top-1">
        <ButtonBack customClass="bg-gradient-text hover:from-blue-700 hover:to-purple-800 text-white font-semibold py-3 px-7 rounded-full shadow-xl transition-all duration-200 hover:scale-105"/>
      </div>
      <div class="flex flex-col items-center">
        <h1 class="text-3xl font-extrabold bg-gradient-to-r from-blue-600 via-purple-500 to-pink-500 bg-clip-text text-transparent tracking-tight mb-3 drop-shadow-lg flex items-center gap-3">
          <span>📊</span> Thống Kê Cá Nhân
        </h1>
        <p class="text-lg text-gray-700 bg-white/70 px-6 py-2 rounded-full shadow-md backdrop-blur-sm">
          Theo dõi hoạt động và hiệu suất của bạn trên nền tảng <span class="font-semibold text-blue-600">KT_AI</span>
        </p>
        <!-- Nút làm mới -->
        <button
          @click="refreshStatistics"
          :disabled="loading"
          class="mt-4 flex items-center justify-center w-12 h-12 rounded-full bg-gradient-to-r from-blue-600 to-purple-600 shadow-md transition-all duration-200 hover:scale-110 hover:from-blue-700 hover:to-purple-800 focus:outline-none disabled:opacity-50 disabled:cursor-not-allowed"
          :title="loading ? 'Đang tải...' : 'Làm mới'"
        >
          <svg 
            v-if="!loading"
            xmlns="http://www.w3.org/2000/svg" 
            class="w-6 h-6 text-white" 
            fill="none" 
            viewBox="0 0 24 24" 
            stroke="currentColor" 
            stroke-width="2"
          >
            <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582M20 20v-5h-.581M19.418 9A7.978 7.978 0 0012 4c-3.042 0-5.824 1.721-7.418 4.5M4.582 15A7.978 7.978 0 0012 20c3.042 0 5.824-1.721 7.418-4.5"/>
          </svg>
          <svg 
            v-else
            class="w-6 h-6 text-white animate-spin" 
            xmlns="http://www.w3.org/2000/svg" 
            fill="none" 
            viewBox="0 0 24 24"
          >
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
        </button>
        
        <!-- Thông báo lỗi -->
        <div v-if="hasError" class="mt-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg">
          <div class="flex items-center justify-between">
            <div class="flex items-center">
              <i class="pi pi-exclamation-triangle mr-2"></i>
              <span>{{ error }}</span>
            </div>
            <button 
              @click="clearError"
              class="text-red-500 hover:text-red-700"
            >
              <i class="pi pi-times"></i>
            </button>
          </div>
        </div>
        
        <!-- Thông báo cập nhật cuối -->
        <div v-if="lastUpdated" class="mt-2 text-sm text-gray-500">
          <i class="pi pi-clock mr-1"></i>
          Cập nhật lần cuối: {{ lastUpdated }}
        </div>
      </div>
    </div>

    <!-- Overview Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
      <!-- Tổng Hình Ảnh -->
      <div class="bg-white rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
        <div class="p-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-gray-600">Tổng Hình Ảnh</p>
              <p class="text-3xl font-bold text-blue-600">{{ statistics.overview?.totalImages || 0 }}</p>
            </div>
            <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
              <!-- SVG icon hình ảnh -->
              <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <rect x="3" y="5" width="18" height="14" rx="2" stroke="currentColor" stroke-width="2" fill="none"/>
                <circle cx="8.5" cy="10.5" r="1.5" fill="currentColor"/>
                <path d="M21 19l-5.5-7-4.5 6-3-4-4 5" stroke="currentColor" stroke-width="2" fill="none"/>
              </svg>
            </div>
          </div>
          <div class="mt-4">
            <div class="flex items-center text-sm text-gray-500">
              <i class="pi pi-calendar mr-1"></i>
              <span>Hôm nay: {{ statistics.overview?.today?.images || 0 }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Lượt Thích -->
      <div class="bg-white rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
        <div class="p-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-gray-600">Lượt Thích</p>
              <p class="text-3xl font-bold text-red-600">{{ statistics.overview?.totalLikes || 0 }}</p>
            </div>
            <div class="w-12 h-12 bg-red-100 rounded-xl flex items-center justify-center">
              <!-- SVG icon trái tim -->
              <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-red-600" fill="currentColor" viewBox="0 0 24 24">
                <path d="M12 21C11.7 21 11.4 20.9 11.2 20.7C6.1 16.1 2 12.4 2 8.7C2 6 4.2 3.8 6.9 3.8C8.5 3.8 10 4.7 10.8 6.1C11.6 4.7 13.1 3.8 14.7 3.8C17.4 3.8 19.6 6 19.6 8.7C19.6 12.4 15.5 16.1 12.8 20.7C12.6 20.9 12.3 21 12 21Z"/>
              </svg>
            </div>
          </div>
          <div class="mt-4">
            <div class="flex items-center text-sm text-gray-500">
              <i class="pi pi-calendar mr-1"></i>
              <span>Hôm nay: {{ statistics.overview?.today?.likes || 0 }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Bình Luận -->
      <div class="bg-white rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
        <div class="p-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-gray-600">Bình Luận</p>
              <p class="text-3xl font-bold text-green-600">{{ statistics.overview?.totalComments || 0 }}</p>
            </div>
            <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
              <!-- SVG icon bình luận -->
              <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z" stroke="currentColor" stroke-width="2" fill="none"/>
              </svg>
            </div>
          </div>
          <div class="mt-4">
            <div class="flex items-center text-sm text-gray-500">
              <i class="pi pi-calendar mr-1"></i>
              <span>Hôm nay: {{ statistics.overview?.today?.comments || 0 }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Credits Còn Lại -->
      <div class="bg-white rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
        <div class="p-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-gray-600">Credits Còn Lại</p>
              <p class="text-3xl font-bold text-purple-600">{{ statistics.overview?.remainingCredits || 0 }}</p>
            </div>
            <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center">
              <!-- SVG icon credit card -->
              <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <rect x="2" y="5" width="20" height="14" rx="2" stroke="currentColor" stroke-width="2" fill="none"/>
                <rect x="2" y="10" width="20" height="3" fill="currentColor" opacity="0.1"/>
                <rect x="6" y="15" width="4" height="2" rx="1" fill="currentColor"/>
              </svg>
            </div>
          </div>
          <div class="mt-4">
            <div class="flex items-center text-sm text-gray-500">
              <i class="pi pi-calendar mr-1"></i>
              <span>Tham gia: {{ statistics.overview?.memberSince || 'N/A' }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
      <!-- Biểu đồ hoạt động theo tháng -->
      <div class="bg-white rounded-2xl shadow-lg p-6">
        <h3 class="text-xl font-semibold text-gray-800 mb-4">Hoạt Động Theo Tháng</h3>
        <Chart 
          type="line" 
          :data="monthlyChartData" 
          :options="lineChartOptions" 
          class="h-80" 
        />
      </div>

      <!-- Biểu đồ hoạt động theo tuần -->
      <div class="bg-white rounded-2xl shadow-lg p-6">
        <h3 class="text-xl font-semibold text-gray-800 mb-4">Hoạt Động Theo Tuần</h3>
        <Chart 
          type="bar" 
          :data="weeklyChartData" 
          :options="barChartOptions" 
          class="h-80" 
        />
      </div>
    </div>

    <!-- Additional Charts -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
      <!-- Biểu đồ tròn - Top tính năng AI -->
      <div class="bg-white rounded-2xl shadow-lg p-6">
        <h3 class="text-xl font-semibold text-gray-800 mb-4">Tính Năng AI Yêu Thích</h3>
        <Chart 
          type="doughnut" 
          :data="topFeaturesChartData" 
          :options="doughnutChartOptions" 
          class="h-80" 
        />
      </div>

      <!-- Biểu đồ radar - Hoạt động theo giờ -->
      <div class="bg-white rounded-2xl shadow-lg p-6">
        <h3 class="text-xl font-semibold text-gray-800 mb-4">Hoạt Động Theo Giờ</h3>
        <Chart 
          type="radar" 
          :data="hourlyChartData" 
          :options="radarChartOptions" 
          class="h-80" 
        />
      </div>
    </div>

    <!-- Detailed Stats -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Top Features Table -->
      <div class="bg-white rounded-2xl shadow-lg p-6">
        <h3 class="text-xl font-semibold text-gray-800 mb-4">Top Tính Năng AI</h3>
        <div class="space-y-3">
          <div 
            v-for="(feature, index) in TopFeatures" 
            :key="feature.id"
            class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors"
          >
            <div class="flex items-center">
              <div class="w-8 h-8 bg-gradient-to-r from-blue-500 to-purple-600 rounded-full flex items-center justify-center text-white font-bold text-sm mr-3">
                {{ index + 1 }}
              </div>
              <span class="font-medium text-gray-800">{{ feature.title }}</span>
            </div>
            <span class="text-lg font-bold text-blue-600">{{ feature.count }} lần sử dụng</span>
          </div>
        </div>
      </div>

      <!-- Activity Summary -->
      <div class="bg-white rounded-2xl shadow-lg p-6">
        <h3 class="text-xl font-semibold text-gray-800 mb-4">Tóm Tắt Hoạt Động</h3>
        <div class="space-y-4">
          <div class="flex items-center justify-between p-3 bg-blue-50 rounded-lg">
            <div class="flex items-center">
              <i class="pi pi-image text-blue-600 mr-3"></i>
              <span class="text-gray-700">Hình ảnh trung bình/tháng</span>
            </div>
            <span class="font-bold text-blue-600">{{ averageImagesPerMonth }}</span>
          </div>
          <div class="flex items-center justify-between p-3 bg-green-50 rounded-lg">
            <div class="flex items-center">
              <i class="pi pi-heart text-green-600 mr-3"></i>
              <span class="text-gray-700">Lượt thích trung bình/tháng</span>
            </div>
            <span class="font-bold text-green-600">{{ averageLikesPerMonth }}</span>
          </div>
          <div class="flex items-center justify-between p-3 bg-purple-50 rounded-lg">
            <div class="flex items-center">
              <i class="pi pi-comments text-purple-600 mr-3"></i>
              <span class="text-gray-700">Bình luận trung bình/tháng</span>
            </div>
            <span class="font-bold text-purple-600">{{ averageCommentsPerMonth }}</span>
          </div>
        </div>
      </div>

      <!-- Performance Insights -->
      <div class="bg-white rounded-2xl shadow-lg p-6">
        <h3 class="text-xl font-semibold text-gray-800 mb-4">Thông Tin Hiệu Suất</h3>
        <div class="space-y-4">
          <div class="p-3 bg-gradient-to-r from-blue-500 to-purple-600 rounded-lg text-white">
            <div class="flex items-center justify-between">
              <span class="font-medium">Hiệu suất tạo ảnh</span>
              <span class="text-2xl font-bold">{{ performanceScore }}%</span>
            </div>
            <div class="mt-2">
              <div class="w-full bg-white bg-opacity-20 rounded-full h-2">
                <div class="bg-white h-2 rounded-full" :style="{ width: performanceScore + '%' }"></div>
              </div>
            </div>
          </div>
          <div class="p-3 bg-gradient-to-r from-green-500 to-blue-600 rounded-lg text-white">
            <div class="flex items-center justify-between">
              <span class="font-medium">Mức độ tương tác</span>
              <span class="text-2xl font-bold">{{ engagementScore }}%</span>
            </div>
            <div class="mt-2">
              <div class="w-full bg-white bg-opacity-20 rounded-full h-2">
                <div class="bg-white h-2 rounded-full" :style="{ width: engagementScore + '%' }"></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { onMounted, computed } from 'vue';
import Card from 'primevue/card';
import Chart from 'primevue/chart';
import { ButtonBack } from '@/components/base';
import useStatistics from '@/composables/features/user/useStatistics';
import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  BarElement,
  Title,
  Tooltip,
  Legend,
  ArcElement,
  RadialLinearScale,
  Filler
} from 'chart.js';

// Register Chart.js components
ChartJS.register(
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  BarElement,
  Title,
  Tooltip,
  Legend,
  ArcElement,
  RadialLinearScale,
  Filler
);

export default {
  name: 'StatisticsPage',
  components: {
    Card,
    Chart,
    ButtonBack
  },
  setup() {
    const {
      statistics,
      loading,
      error,
      hasError,
      isDataLoaded,
      lastUpdated,
      monthlyChartData,
      weeklyChartData,
      topFeaturesChartData,
      hourlyChartData,
      lineChartOptions,
      barChartOptions,
      doughnutChartOptions,
      radarChartOptions,
      averageImagesPerMonth,
      averageLikesPerMonth,
      averageCommentsPerMonth,
      performanceScore,
      engagementScore,
      fetchUserStatistics,
      refreshStatistics,
      clearError
    } = useStatistics();

    const TopFeatures = computed(() => {
      return statistics.value.topFeatures.map((feature) => ({
        id: feature.id,
        title: feature.title,
        count: feature.count
      }));
    });

    onMounted(() => {
      // Chỉ gọi API nếu chưa có dữ liệu hoặc dữ liệu cũ quá 5 phút
      if (!isDataLoaded.value) {
        fetchUserStatistics();
      } else {
        // Kiểm tra xem dữ liệu có cũ quá không (5 phút)
        const lastUpdate = new Date(lastUpdated.value);
        const now = new Date();
        const diffInMinutes = (now - lastUpdate) / (1000 * 60);
        
        if (diffInMinutes > 5) {
          console.log('Dữ liệu cũ, đang cập nhật...');
          fetchUserStatistics();
        } else {
          console.log('Sử dụng dữ liệu đã cache');
        }
      }
    });

    return {
      statistics,
      loading,
      error,
      hasError,
      isDataLoaded,
      lastUpdated,
      monthlyChartData,
      weeklyChartData,
      topFeaturesChartData,
      hourlyChartData,
      lineChartOptions,
      barChartOptions,
      doughnutChartOptions,
      radarChartOptions,
      averageImagesPerMonth,
      averageLikesPerMonth,
      averageCommentsPerMonth,
      performanceScore,
      engagementScore,
      refreshStatistics,
      clearError,
      TopFeatures
    };
  }
};
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap');

* {
  font-family: 'Inter', sans-serif;
}

.bg-gradient-to-br {
  background: linear-gradient(135deg, #EBF4FF 0%, #E0E7FF 50%, #F3E8FF 100%);
}

.animate-pulse {
  animation: pulse 1.5s infinite;
}

@keyframes pulse {
  0% { opacity: 1; }
  50% { opacity: 0.7; }
  100% { opacity: 1; }
}

.h-80 {
  height: 20rem;
}
</style>