<template>
  <div class="min-h-screen bg-gray-50 py-8">
    <div class="container mx-auto px-4">
      <ButtonBackVue customClass="bg-gradient-text hover: text-white font-bold py-2 px-4 mb-8 rounded-full"/>
      <h1 class="text-3xl font-bold text-gray-800 mb-8">Quản lý tiến trình tạo ảnh</h1>
      
      <!-- Tab Navigation -->
      <div class="flex border-b border-gray-200 mb-6">
        <button 
          class="py-3 px-6 font-medium text-lg transition-colors duration-200 relative"
          :class="activeTab === 'active' 
            ? 'text-blue-600 border-b-2 border-blue-600' 
            : 'text-gray-500 hover:text-gray-700'"
          @click="activeTab = 'active'"
        >
          Đang thực thi
          <span 
            v-if="activeJobs.length > 0" 
            class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center"
          >
            {{ activeJobs.length }}
          </span>
        </button>
        <button 
          class="py-3 px-6 font-medium text-lg transition-colors duration-200"
          :class="activeTab === 'completed' 
            ? 'text-blue-600 border-b-2 border-blue-600' 
            : 'text-gray-500 hover:text-gray-700'"
          @click="activeTab = 'completed'"
        >
          Đã hoàn thành
        </button>
        <button 
          class="py-3 px-6 font-medium text-lg transition-colors duration-200 relative"
          :class="activeTab === 'failed' 
            ? 'text-blue-600 border-b-2 border-blue-600' 
            : 'text-gray-500 hover:text-gray-700'"
          @click="activeTab = 'failed'"
        >
          Thất bại
          <span 
            v-if="failedJobs.length > 0" 
            class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center"
          >
            {{ failedJobs.length }}
          </span>
        </button>
      </div>
      
      <!-- Loading State -->
      <LoadingState v-if="isLoading" />
      
      <!-- Empty State -->
      <EmptyJob 
        v-else-if="(activeTab === 'active' && activeJobs.length === 0) || (activeTab === 'completed' && completedJobs.length === 0)"
        :active-tab="activeTab"
      />
      
      <!-- Active Jobs Tab Content -->
      <JobsActive 
        v-else-if="activeTab === 'active' && activeJobs.length > 0"
        :active-jobs="activeJobs"
        @job-cancelled="fetchActiveJobs"
      />
      
      <!-- Completed Jobs Tab Content -->
      <JobsSuccess 
        v-else-if="activeTab === 'completed' && completedJobs.length > 0"
        :completed-jobs="completedJobs"
        @view-image="viewImage"
      />
      
      <!-- Failed Jobs Tab Content -->
      <JobsFailed 
        v-else-if="activeTab === 'failed' && failedJobs.length > 0"
        :failed-jobs="failedJobs"
        @job-retried="fetchFailedJobs"
      />
      
      <!-- Image Preview Modal -->
      <DetailsJob 
        v-if="previewImage" 
        :job="previewImage"
        @close="previewImage = null"
      />
    </div>
  </div>
</template>

<script>
import ButtonBackVue from '../../components/common/ButtonBack.vue'
import JobsActive from '../../components/user/ImageJobsManager/JobsActive.vue'
import JobsSuccess from '../../components/user/ImageJobsManager/JobsSuccess.vue'
import JobsFailed from '../../components/user/ImageJobsManager/JobsFailed.vue'
import EmptyJob from '../../components/user/ImageJobsManager/EmptyJob.vue'
import LoadingState from '../../components/common/LoadingState.vue'
import DetailsJob from '../../components/user/ImageJobsManager/DetailsJob.vue'
import { ref, onMounted, onBeforeUnmount, computed, onErrorCaptured } from 'vue';
import { toast } from 'vue-sonner';
import { comfyuiAPI } from '@/services/api';

export default {
  name: 'ImageJobsManager',
  components: {
    ButtonBackVue,
    JobsActive,
    JobsSuccess,
    JobsFailed,
    EmptyJob,
    LoadingState,
    DetailsJob
  },
  setup() {
    const activeTab = ref('active');
    const activeJobs = ref([]);
    const completedJobs = ref([]);
    const failedJobs = ref([]);
    const isLoading = ref(true);
    const previewImage = ref(null);
    const refreshInterval = ref(null);
    
    // Computed properties để đảm bảo safe access
    const safeActiveJobs = computed(() => Array.isArray(activeJobs.value) ? activeJobs.value : []);
    const safeCompletedJobs = computed(() => Array.isArray(completedJobs.value) ? completedJobs.value : []);
    const safeFailedJobs = computed(() => Array.isArray(failedJobs.value) ? failedJobs.value : []);
    
    // Fetch active jobs
    const fetchActiveJobs = async () => {
      try {
        const response = await comfyuiAPI.getActiveJobs();
        if (response.data.success) {
          activeJobs.value = response.data.data?.active_jobs || [];
        } else {
          activeJobs.value = [];
        }
      } catch (error) {
        console.error('Lỗi khi lấy tiến trình đang hoạt động:', error);
        toast.error('Không thể tải danh sách tiến trình đang hoạt động');
        activeJobs.value = [];
      }
    };
    
    // Fetch completed jobs
    const fetchCompletedJobs = async () => {
      try {
        const response = await comfyuiAPI.getCompletedJobs();
        if (response.data.success) {
          completedJobs.value = response.data.data?.completed_jobs || [];
        } else {
          completedJobs.value = [];
        }
      } catch (error) {
        console.error('Lỗi khi lấy tiến trình đã hoàn thành:', error);
        toast.error('Không thể tải danh sách tiến trình đã hoàn thành');
        completedJobs.value = [];
      } finally {
        isLoading.value = false;
      }
    };
    
    // Fetch failed jobs
    const fetchFailedJobs = async () => {
      try {
        const response = await comfyuiAPI.getFailedJobs();
        if (response.data.success) {
          failedJobs.value = response.data.data?.failed_jobs || [];
        } else {
          failedJobs.value = [];
        }
      } catch (error) {
        console.error('Lỗi khi lấy tiến trình thất bại:', error);
        toast.error('Không thể tải danh sách tiến trình thất bại');
        failedJobs.value = [];
      }
    };
    
    // Fetch all jobs
    const fetchAllJobs = async () => {
      try {
        isLoading.value = true;
        await Promise.all([fetchActiveJobs(), fetchCompletedJobs(), fetchFailedJobs()]);
      } catch (error) {
        console.error('Error fetching all jobs:', error);
        toast.error('Không thể tải danh sách công việc');
      } finally {
        isLoading.value = false;
      }
    };    

    // View image details
    const viewImage = (job) => {
      previewImage.value = job;
    };
    
    onMounted(() => {
      fetchAllJobs();
      
      // Set up interval to refresh jobs status every 10 seconds
      refreshInterval.value = setInterval(() => {
        if (activeTab.value === 'active') {
          fetchActiveJobs();
        } else if (activeTab.value === 'completed') {
          fetchCompletedJobs();
        } else if (activeTab.value === 'failed') {
          fetchFailedJobs();
        }
      }, 10000);
    });
    
    onBeforeUnmount(() => {
      // Clear interval when component is unmounted
      if (refreshInterval.value) {
        clearInterval(refreshInterval.value);
      }
    });
    
    // Error boundary để bắt lỗi unhandled
    onErrorCaptured((error, instance, info) => {
      console.error('Error captured in ImageJobsManager:', error, info);
      
      // Không hiển thị toast cho runtime errors từ browser extension
      if (!error.message?.includes('runtime.lastError') && 
          !error.message?.includes('Could not establish connection')) {
        toast.error('Có lỗi xảy ra trong ứng dụng. Vui lòng thử lại.');
      }
      
      // Trả về false để ngăn error bubble up
      return false;
    });
    
    return {
      activeTab,
      activeJobs: safeActiveJobs,
      completedJobs: safeCompletedJobs,
      failedJobs: safeFailedJobs,
      isLoading,
      previewImage,
      fetchActiveJobs,
      fetchFailedJobs,
      viewImage,
    };
  }
};
</script>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style> 