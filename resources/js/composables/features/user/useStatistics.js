import { computed, ref } from 'vue';
import { useStatisticsStore } from '@/stores/user/statisticsStore.js';

export default function useStatistics() {
  try {
    const statisticsStore = useStatisticsStore();
    
    // Sử dụng reactive data từ store
    const statistics = computed(() => statisticsStore.statistics);

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
    labels: statistics.value.topFeatures?.map(feature => feature.title) || [],
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

  // Sử dụng methods từ store
  const fetchUserStatistics = () => statisticsStore.fetchUserStatistics();
  const refreshStatistics = () => statisticsStore.refreshStatistics();
  const clearError = () => statisticsStore.clearError();
  
  // Expose store state và getters
  const loading = computed(() => statisticsStore.loading);
  const error = computed(() => statisticsStore.error);
  const isDataLoaded = computed(() => statisticsStore.isDataLoaded);
  const hasError = computed(() => statisticsStore.hasError);
  const lastUpdated = computed(() => statisticsStore.getFormattedLastUpdated);

      return {
      // Reactive data
      statistics,
      
      // Store state
      loading,
      error,
      isDataLoaded,
      hasError,
      lastUpdated,
      
      // Chart data
      monthlyChartData,
      weeklyChartData,
      topFeaturesChartData,
      hourlyChartData,
      
      // Chart options
      lineChartOptions,
      barChartOptions,
      doughnutChartOptions,
      radarChartOptions,
      
      // Computed properties
      averageImagesPerMonth,
      averageLikesPerMonth,
      averageCommentsPerMonth,
      performanceScore,
      engagementScore,
      
      // Methods
      fetchUserStatistics,
      refreshStatistics,
      clearError
    };
  } catch (error) {
    console.error('Error in useStatistics:', error);
    // Return fallback values
    return {
      statistics: computed(() => ({})),
      loading: computed(() => false),
      error: computed(() => 'Lỗi khởi tạo store'),
      isDataLoaded: computed(() => false),
      hasError: computed(() => true),
      lastUpdated: computed(() => null),
      monthlyChartData: computed(() => ({})),
      weeklyChartData: computed(() => ({})),
      topFeaturesChartData: computed(() => ({})),
      hourlyChartData: computed(() => ({})),
      lineChartOptions: ref({}),
      barChartOptions: ref({}),
      doughnutChartOptions: ref({}),
      radarChartOptions: ref({}),
      averageImagesPerMonth: computed(() => 0),
      averageLikesPerMonth: computed(() => 0),
      averageCommentsPerMonth: computed(() => 0),
      performanceScore: computed(() => 0),
      engagementScore: computed(() => 0),
      fetchUserStatistics: () => {},
      refreshStatistics: () => {},
      clearError: () => {}
    };
  }
}