import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import { statisticsApi } from '@/services/apis/statistics.js';

export const useStatisticsStore = defineStore('statistics', {
  state: () => ({
    statistics: {
      overview: null,
      monthlyStats: null,
      weeklyStats: null,
      topFeatures: [],
      hourlyActivity: []
    },
    loading: false,
    error: null,
    lastUpdated: null
  }),

  getters: {
    isDataLoaded: (state) => {
      return state.statistics.overview !== null;
    },

    hasError: (state) => {
      return state.error !== null;
    },

    getFormattedLastUpdated: (state) => {
      if (!state.lastUpdated) return null;
      return new Date(state.lastUpdated).toLocaleString('vi-VN');
    }
  },

  actions: {
    async fetchUserStatistics() {
      try {
        this.loading = true;
        this.error = null;

        console.log('Fetching user statistics...');
        const response = await statisticsApi.getUserStatistics();
        console.log('Statistics API response:', response);
        
        // Xử lý response từ Laravel API
        if (response.data && response.data.success) {
          // Response từ Laravel API có cấu trúc: { success: true, data: { ... }, message: "..." }
          const apiData = response.data.data;
          this.statistics = {
            overview: apiData.overview || null,
            monthlyStats: apiData.monthlyStats || null,
            weeklyStats: apiData.weeklyStats || null,
            topFeatures: apiData.topFeatures || [],
            hourlyActivity: apiData.hourlyActivity || []
          };
          this.lastUpdated = new Date().toISOString();
          console.log('Statistics updated successfully:', this.statistics);
        } else if (response.data) {
          // Fallback cho trường hợp response trực tiếp
          this.statistics = {
            overview: response.data.overview || null,
            monthlyStats: response.data.monthlyStats || null,
            weeklyStats: response.data.weeklyStats || null,
            topFeatures: response.data.topFeatures || [],
            hourlyActivity: response.data.hourlyActivity || []
          };
          this.lastUpdated = new Date().toISOString();
          console.log('Statistics updated with fallback:', this.statistics);
        } else {
          throw new Error('Dữ liệu thống kê không hợp lệ');
        }
      } catch (err) {
        this.error = err.message || 'Đã xảy ra lỗi khi tải dữ liệu thống kê';
        console.error('Statistics fetch error:', err);
      } finally {
        this.loading = false;
      }
    },

    async refreshStatistics() {
      // Reset data before fetching new data
      this.statistics = {
        overview: null,
        monthlyStats: null,
        weeklyStats: null,
        topFeatures: [],
        hourlyActivity: []
      };
      this.error = null;
      
      await this.fetchUserStatistics();
    },

    clearError() {
      this.error = null;
    },

    resetStore() {
      this.statistics = {
        overview: null,
        monthlyStats: null,
        weeklyStats: null,
        topFeatures: [],
        hourlyActivity: []
      };
      this.loading = false;
      this.error = null;
      this.lastUpdated = null;
    }
  },

  persist: true
}); 