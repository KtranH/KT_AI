```vue
<template>
  <div class="p-6 bg-gradient-to-br from-gray-50 to-gray-100 min-h-screen">
    <h1 class="text-4xl font-bold mb-8 text-gray-900 tracking-tight">Trang Thống Kê</h1>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
      <!-- Thẻ Tổng Người Dùng -->
      <div class="bg-white p-4 rounded-xl shadow-sm">
        <Card class="shadow-md hover:shadow-xl hover:rounded-xl transition-all duration-300 bg-white rounded-lg">
          <template #title>
            <div class="flex items-center">
              <i class="pi pi-users mr-2 text-xl text-blue-600"></i>
              <span class="text-lg font-semibold text-gray-800">Tổng Người Dùng</span>
            </div>
          </template>
          <template #content>
            <p class="text-4xl font-bold text-blue-700 animate-pulse">{{ statistics.totalUsers }}</p>
          </template>
        </Card>
      </div>

      <!-- Thẻ Người Dùng Mới Hôm Nay -->
      <div class="bg-white p-4 rounded-xl shadow-sm">
        <Card class="shadow-md hover:shadow-xl hover:rounded-xl transition-all duration-300 bg-white rounded-lg">
          <template #title>
            <div class="flex items-center">
              <i class="pi pi-user-plus mr-2 text-xl text-green-600"></i>
              <span class="text-lg font-semibold text-gray-800">Người Dùng Mới Hôm Nay</span>
            </div>
          </template>
          <template #content>
            <p class="text-4xl font-bold text-green-700 animate-pulse">{{ statistics.newUsersToday }}</p>
          </template>
        </Card>
      </div>

      <!-- Thẻ Tổng Hình Ảnh -->
      <div class="bg-white p-4 rounded-xl shadow-sm">
        <Card class="shadow-md hover:shadow-xl hover:rounded-xl transition-all duration-300 bg-white rounded-lg">
          <template #title>
            <div class="flex items-center">
              <i class="pi pi-images mr-2 text-xl text-purple-600"></i>
              <span class="text-lg font-semibold text-gray-800">Tổng Hình Ảnh</span>
            </div>
          </template>
          <template #content>
            <p class="text-4xl font-bold text-purple-700 animate-pulse">{{ statistics.totalImages }}</p>
          </template>
        </Card>
      </div>

      <!-- Thẻ Tổng Bình Luận -->
      <div class="bg-white p-4 rounded-xl shadow-sm">
        <Card class="shadow-md hover:shadow-xl hover:rounded-xl transition-all duration-300 bg-white rounded-lg">
          <template #title>
            <div class="flex items-center">
              <i class="pi pi-comments mr-2 text-xl text-orange-600"></i>
              <span class="text-lg font-semibold text-gray-800">Tổng Bình Luận</span>
            </div>
          </template>
          <template #content>
            <p class="text-4xl font-bold text-orange-700 animate-pulse">{{ statistics.totalComments }}</p>
          </template>
        </Card>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <Card class="shadow-md bg-white rounded-xl">
        <template #title>
          <span class="text-xl font-semibold text-gray-800">Thống Kê Người Dùng & Hình Ảnh</span>
        </template>
        <template #content>
          <Chart type="bar" :data="chartData" :options="chartOptions" class="h-80" />
        </template>
      </Card>
      <Card class="shadow-md bg-white rounded-xl">
        <template #title>
          <span class="text-xl font-semibold text-gray-800">Chi Tiết Hoạt Động Hôm Nay</span>
        </template>
        <template #content>
          <ul class="space-y-3">
            <li class="flex justify-between items-center p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors duration-200">
              <span class="text-gray-600 flex items-center">
                <i class="pi pi-user mr-2 text-blue-500"></i> Người dùng hoạt động:
              </span>
              <span class="font-semibold text-gray-900">{{ statistics.activeUsers }}</span>
            </li>
            <li class="flex justify-between items-center p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors duration-200">
              <span class="text-gray-600 flex items-center">
                <i class="pi pi-image mr-2 text-purple-500"></i> Ảnh tải lên hôm nay:
              </span>
              <span class="font-semibold text-gray-900">{{ statistics.imagesUploadedToday }}</span>
            </li>
            <li class="flex justify-between items-center p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors duration-200">
              <span class="text-gray-600 flex items-center">
                <i class="pi pi-comment mr-2 text-orange-500"></i> Bình luận hôm nay:
              </span>
              <span class="font-semibold text-gray-900">{{ statistics.commentsToday }}</span>
            </li>
            <li class="flex justify-between items-center p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors duration-200">
              <span class="text-gray-600 flex items-center">
                <i class="pi pi-heart mr-2 text-red-500"></i> Lượt thích hôm nay:
              </span>
              <span class="font-semibold text-gray-900">{{ statistics.likesToday }}</span>
            </li>
          </ul>
        </template>
      </Card>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import Card from 'primevue/card';
import Chart from 'primevue/chart';

const statistics = ref({
  totalUsers: 0,
  activeUsers: 0,
  newUsersToday: 0,
  totalImages: 0,
  imagesUploadedToday: 0,
  totalComments: 0,
  commentsToday: 0,
  likesToday: 0,
  chartData: { labels: [], datasets: [] }
});

const chartData = ref(null);
const chartOptions = ref({
  responsive: true,
  maintainAspectRatio: false,
  scales: {
    y: {
      beginAtZero: true,
      grid: {
        color: 'rgba(0, 0, 0, 0.05)'
      }
    },
    x: {
      grid: {
        display: false
      }
    }
  },
  plugins: {
    legend: {
      labels: {
        font: {
          family: 'Inter, sans-serif',
          size: 14
        }
      }
    }
  }
});

const fetchStatistics = async () => {
  try {
    const response = await axios.get('/api/statistics');
    const data = response.data;
    statistics.value = data;
    chartData.value = {
      ...data.chartData,
      datasets: data.chartData.datasets.map(dataset => ({
        ...dataset,
        backgroundColor: ['rgba(59, 130, 246, 0.6)', 'rgba(168, 85, 247, 0.6)'],
        borderColor: ['rgba(59, 130, 246, 1)', 'rgba(168, 85, 247, 1)'],
        borderWidth: 1
      }))
    };
  } catch (error) {
    console.error('Lỗi khi lấy dữ liệu thống kê:', error);
  }
};

onMounted(() => {
  fetchStatistics();
});
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap');

* {
  font-family: 'Inter', sans-serif;
}

.p-card {
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.p-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
}

.p-card .p-card-content {
  padding-top: 0;
}

.p-card .p-card-title {
  margin-bottom: 0.75rem;
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
```