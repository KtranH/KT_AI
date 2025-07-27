<template>
  <div class="p-6 min-h-screen">
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
            v-for="(feature, index) in statistics.topFeatures" 
            :key="feature.id"
            class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors"
          >
            <div class="flex items-center">
              <div class="w-8 h-8 bg-gradient-to-r from-blue-500 to-purple-600 rounded-full flex items-center justify-center text-white font-bold text-sm mr-3">
                {{ index + 1 }}
              </div>
              <span class="font-medium text-gray-800">{{ feature.name }}</span>
            </div>
            <span class="text-lg font-bold text-blue-600">{{ feature.count }}</span>
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
import { ref, computed, onMounted } from 'vue';
import { statisticsAPI } from '@/services/apis/statistics';
import Card from 'primevue/card';
import Chart from 'primevue/chart';
import { ButtonBack } from '@/components/base'

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
    const statistics = ref({
      overview: {
        totalImages: 0,
        totalLikes: 0,
        totalComments: 0,
        remainingCredits: 0,
        memberSince: 'N/A',
        today: {
          images: 0,
          likes: 0,
          comments: 0
        }
      },
      monthlyStats: { labels: [], data: [] },
      weeklyStats: { labels: [], data: [] },
      topFeatures: [],
      hourlyActivity: []
    });

    // Chart Data Computed Properties
    const monthlyChartData = computed(() => ({
      labels: statistics.value.monthlyStats?.labels || [],
      datasets: [
        {
          label: 'Hình Ảnh',
          data: statistics.value.monthlyStats?.data?.map(item => item.images) || [],
          borderColor: '#3B82F6',
          backgroundColor: 'rgba(59, 130, 246, 0.1)',
          tension: 0.4,
          fill: true
        },
        {
          label: 'Lượt Thích',
          data: statistics.value.monthlyStats?.data?.map(item => item.likes) || [],
          borderColor: '#EF4444',
          backgroundColor: 'rgba(239, 68, 68, 0.1)',
          tension: 0.4,
          fill: true
        },
        {
          label: 'Bình Luận',
          data: statistics.value.monthlyStats?.data?.map(item => item.comments) || [],
          borderColor: '#10B981',
          backgroundColor: 'rgba(16, 185, 129, 0.1)',
          tension: 0.4,
          fill: true
        }
      ]
    }));

    const weeklyChartData = computed(() => ({
      labels: statistics.value.weeklyStats?.labels || [],
      datasets: [
        {
          label: 'Hình Ảnh',
          data: statistics.value.weeklyStats?.data?.map(item => item.images) || [],
          backgroundColor: 'rgba(59, 130, 246, 0.8)',
          borderColor: '#3B82F6',
          borderWidth: 1
        },
        {
          label: 'Lượt Thích',
          data: statistics.value.weeklyStats?.data?.map(item => item.likes) || [],
          backgroundColor: 'rgba(239, 68, 68, 0.8)',
          borderColor: '#EF4444',
          borderWidth: 1
        },
        {
          label: 'Bình Luận',
          data: statistics.value.weeklyStats?.data?.map(item => item.comments) || [],
          backgroundColor: 'rgba(16, 185, 129, 0.8)',
          borderColor: '#10B981',
          borderWidth: 1
        }
      ]
    }));

    const topFeaturesChartData = computed(() => ({
      labels: statistics.value.topFeatures?.map(feature => feature.name) || [],
      datasets: [{
        data: statistics.value.topFeatures?.map(feature => feature.count) || [],
        backgroundColor: [
          '#3B82F6',
          '#8B5CF6',
          '#EF4444',
          '#10B981',
          '#F59E0B'
        ],
        borderWidth: 2,
        borderColor: '#ffffff'
      }]
    }));

    const hourlyChartData = computed(() => ({
      labels: statistics.value.hourlyActivity?.map(item => `${item.hour}:00`) || [],
      datasets: [
        {
          label: 'Hình Ảnh',
          data: statistics.value.hourlyActivity?.map(item => item.images) || [],
          borderColor: '#3B82F6',
          backgroundColor: 'rgba(59, 130, 246, 0.2)',
          pointBackgroundColor: '#3B82F6',
          pointBorderColor: '#ffffff',
          pointBorderWidth: 2
        },
        {
          label: 'Lượt Thích',
          data: statistics.value.hourlyActivity?.map(item => item.likes) || [],
          borderColor: '#EF4444',
          backgroundColor: 'rgba(239, 68, 68, 0.2)',
          pointBackgroundColor: '#EF4444',
          pointBorderColor: '#ffffff',
          pointBorderWidth: 2
        },
        {
          label: 'Bình Luận',
          data: statistics.value.hourlyActivity?.map(item => item.comments) || [],
          borderColor: '#10B981',
          backgroundColor: 'rgba(16, 185, 129, 0.2)',
          pointBackgroundColor: '#10B981',
          pointBorderColor: '#ffffff',
          pointBorderWidth: 2
        }
      ]
    }));

    // Chart Options
    const lineChartOptions = ref({
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          position: 'top',
          labels: {
            font: { size: 12 },
            usePointStyle: true
          }
        }
      },
      scales: {
        y: {
          beginAtZero: true,
          grid: { color: 'rgba(0, 0, 0, 0.05)' }
        },
        x: {
          grid: { display: false }
        }
      }
    });

    const barChartOptions = ref({
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          position: 'top',
          labels: {
            font: { size: 12 },
            usePointStyle: true
          }
        }
      },
      scales: {
        y: {
          beginAtZero: true,
          grid: { color: 'rgba(0, 0, 0, 0.05)' }
        },
        x: {
          grid: { display: false }
        }
      }
    });

    const doughnutChartOptions = ref({
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          position: 'bottom',
          labels: {
            font: { size: 12 },
            usePointStyle: true
          }
        }
      }
    });

    const radarChartOptions = ref({
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          position: 'top',
          labels: {
            font: { size: 12 },
            usePointStyle: true
          }
        }
      },
      scales: {
        r: {
          beginAtZero: true,
          grid: { color: 'rgba(0, 0, 0, 0.05)' },
          pointLabels: { font: { size: 10 } }
        }
      }
    });

    // Tính toán trung bình
    const averageImagesPerMonth = computed(() => {
      if (!statistics.value.monthlyStats?.data || statistics.value.monthlyStats.data.length === 0) return 0;
      const total = statistics.value.monthlyStats.data.reduce((sum, item) => sum + (item.images || 0), 0);
      return Math.round(total / statistics.value.monthlyStats.data.length);
    });

    const averageLikesPerMonth = computed(() => {
      if (!statistics.value.monthlyStats?.data || statistics.value.monthlyStats.data.length === 0) return 0;
      const total = statistics.value.monthlyStats.data.reduce((sum, item) => sum + (item.likes || 0), 0);
      return Math.round(total / statistics.value.monthlyStats.data.length);
    });

    const averageCommentsPerMonth = computed(() => {
      if (!statistics.value.monthlyStats?.data || statistics.value.monthlyStats.data.length === 0) return 0;
      const total = statistics.value.monthlyStats.data.reduce((sum, item) => sum + (item.comments || 0), 0);
      return Math.round(total / statistics.value.monthlyStats.data.length);
    });

    const performanceScore = computed(() => {
      const totalImages = statistics.value.overview?.totalImages || 0;
      const remainingCredits = statistics.value.overview?.remainingCredits || 0;
      const usedCredits = 100 - remainingCredits; // Tính toán số lượng credit đã sử dụng
      if (usedCredits === 0) return 0;
      return Math.min(100, Math.round((totalImages / usedCredits) * 100));
    });

    const engagementScore = computed(() => {
      const totalLikes = statistics.value.overview?.totalLikes || 0;
      const totalComments = statistics.value.overview?.totalComments || 0;
      const totalImages = statistics.value.overview?.totalImages || 0;
      
      if (totalImages === 0) return 0;
      const engagement = (totalLikes + totalComments) / totalImages;
      return Math.min(100, Math.round(engagement * 10));
    });

    const fetchUserStatistics = async () => {
      try {
        console.log('Đang gọi API thống kê...');
        const response = await statisticsAPI.getUserStatistics();
        console.log('Response từ API:', response);
        
        if (response.data && response.data.data) {
          statistics.value = response.data.data;
          console.log('Dữ liệu thống kê đã được cập nhật:', statistics.value);
        } else {
          console.warn('Dữ liệu thống kê không đúng định dạng:', response);
          // Sử dụng dữ liệu mẫu để test
          statistics.value = {
            overview: {
              totalImages: 25,
              totalLikes: 150,
              totalComments: 45,
              remainingCredits: 75,
              memberSince: '15/01/2025',
              today: {
                images: 3,
                likes: 12,
                comments: 5
              }
            },
            monthlyStats: {
              labels: ['Tháng 1', 'Tháng 2', 'Tháng 3', 'Tháng 4', 'Tháng 5', 'Tháng 6'],
              data: [
                { images: 5, likes: 20, comments: 8 },
                { images: 8, likes: 35, comments: 12 },
                { images: 12, likes: 45, comments: 15 },
                { images: 15, likes: 60, comments: 20 },
                { images: 18, likes: 75, comments: 25 },
                { images: 25, likes: 150, comments: 45 }
              ]
            },
            weeklyStats: {
              labels: ['Tuần 1', 'Tuần 2', 'Tuần 3', 'Tuần 4'],
              data: [
                { images: 3, likes: 15, comments: 5 },
                { images: 5, likes: 25, comments: 8 },
                { images: 7, likes: 35, comments: 12 },
                { images: 10, likes: 75, comments: 20 }
              ]
            },
            topFeatures: [
              { id: 1, name: 'Text to Image', count: 15 },
              { id: 2, name: 'Image to Image', count: 8 },
              { id: 3, name: 'Style Transfer', count: 5 },
              { id: 4, name: 'Face Swap', count: 3 },
              { id: 5, name: 'Background Remove', count: 2 }
            ],
            hourlyActivity: Array.from({ length: 24 }, (_, i) => ({
              hour: i,
              images: Math.floor(Math.random() * 5),
              likes: Math.floor(Math.random() * 10),
              comments: Math.floor(Math.random() * 3)
            }))
          };
        }
      } catch (error) {
        console.error('Lỗi khi lấy dữ liệu thống kê:', error);
        // Sử dụng dữ liệu mẫu khi có lỗi
        statistics.value = {
          overview: {
            totalImages: 25,
            totalLikes: 150,
            totalComments: 45,
            remainingCredits: 75,
            memberSince: '15/01/2025',
            today: {
              images: 3,
              likes: 12,
              comments: 5
            }
          },
          monthlyStats: {
            labels: ['Tháng 1', 'Tháng 2', 'Tháng 3', 'Tháng 4', 'Tháng 5', 'Tháng 6'],
            data: [
              { images: 5, likes: 20, comments: 8 },
              { images: 8, likes: 35, comments: 12 },
              { images: 12, likes: 45, comments: 15 },
              { images: 15, likes: 60, comments: 20 },
              { images: 18, likes: 75, comments: 25 },
              { images: 25, likes: 150, comments: 45 }
            ]
          },
          weeklyStats: {
            labels: ['Tuần 1', 'Tuần 2', 'Tuần 3', 'Tuần 4'],
            data: [
              { images: 3, likes: 15, comments: 5 },
              { images: 5, likes: 25, comments: 8 },
              { images: 7, likes: 35, comments: 12 },
              { images: 10, likes: 75, comments: 20 }
            ]
          },
          topFeatures: [
            { id: 1, name: 'Text to Image', count: 15 },
            { id: 2, name: 'Image to Image', count: 8 },
            { id: 3, name: 'Style Transfer', count: 5 },
            { id: 4, name: 'Face Swap', count: 3 },
            { id: 5, name: 'Background Remove', count: 2 }
          ],
          hourlyActivity: Array.from({ length: 24 }, (_, i) => ({
            hour: i,
            images: Math.floor(Math.random() * 5),
            likes: Math.floor(Math.random() * 10),
            comments: Math.floor(Math.random() * 3)
          }))
        };
      }
    };

    onMounted(() => {
      fetchUserStatistics();
    });

    return {
      statistics,
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
      fetchUserStatistics
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