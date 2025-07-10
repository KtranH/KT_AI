<template>
  <div class="min-h-screen bg-gray-50 py-8">
    <div class="container mx-auto px-4">
      <ButtonBack customClass="bg-gradient-text hover: text-white font-bold py-2 px-4 mb-8 rounded-full"/>
      <h1 class="text-3xl font-bold text-gray-800 mb-8">Quản lý tiến trình tạo ảnh</h1>
      
      <!-- Tab Navigation -->
      <div class="flex border-b border-gray-200 mb-6">
        <button 
          class="py-3 px-6 font-medium text-lg transition-colors duration-200 relative flex items-center gap-2"
          :class="activeTab === 'active' 
            ? 'text-blue-600 border-b-2 border-blue-600' 
            : 'text-gray-500 hover:text-gray-700'"
          @click="activeTab = 'active'"
        >
          <span>Đang thực thi</span>
          <button 
            v-if="activeTab === 'active'"
            @click.stop="refreshActiveJobs"
            :disabled="isRefreshingActive"
            class="text-xs bg-blue-100 hover:bg-blue-200 disabled:bg-gray-300 px-2 py-1 rounded transition-colors"
            title="Làm mới"
          >
            <span v-if="isRefreshingActive" class="animate-spin">⟳</span>
            <span v-else>↻</span>
          </button>
          <span 
            v-if="activeJobs.length > 0" 
            class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center"
          >
            {{ activeJobs.length }}
          </span>
        </button>
        <button 
          class="py-3 px-6 font-medium text-lg transition-colors duration-200 relative flex items-center gap-2"
          :class="activeTab === 'completed' 
            ? 'text-blue-600 border-b-2 border-blue-600' 
            : 'text-gray-500 hover:text-gray-700'"
          @click="activeTab = 'completed'"
        >
          <span>Đã hoàn thành</span>
          <button 
            v-if="activeTab === 'completed'"
            @click.stop="refreshCompletedJobsManually"
            :disabled="isRefreshingCompleted"
            class="text-xs bg-blue-100 hover:bg-blue-200 disabled:bg-gray-300 px-2 py-1 rounded transition-colors"
            title="Làm mới"
          >
            <span v-if="isRefreshingCompleted" class="animate-spin">⟳</span>
            <span v-else>↻</span>
          </button>
        </button>
        <button 
          class="py-3 px-6 font-medium text-lg transition-colors duration-200 relative flex items-center gap-2"
          :class="activeTab === 'failed' 
            ? 'text-blue-600 border-b-2 border-blue-600' 
            : 'text-gray-500 hover:text-gray-700'"
          @click="activeTab = 'failed'"
        >
          <span>Thất bại</span>
          <button 
            v-if="activeTab === 'failed'"
            @click.stop="refreshFailedJobs"
            :disabled="isRefreshingFailed"
            class="text-xs bg-blue-100 hover:bg-blue-200 disabled:bg-gray-300 px-2 py-1 rounded transition-colors"
            title="Làm mới"
          >
            <span v-if="isRefreshingFailed" class="animate-spin">⟳</span>
            <span v-else>↻</span>
          </button>
          <span 
            v-if="failedJobs.length > 0" 
            class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center"
          >
            {{ failedJobs.length }}
          </span>
        </button>
      </div>
      
      <!-- Loading State -->
      <LoadingState v-if="isLoadingSuccess && activeTab === 'completed' || isLoadingFailed && activeTab === 'failed'" />
      
      <!-- Empty State -->
      <EmptyJob 
        v-else-if="(activeTab === 'active' && activeJobs.length === 0) || (activeTab === 'completed' && completedJobs.length === 0) || (activeTab === 'failed' && failedJobs.length === 0)"
        :active-tab="activeTab"
      />
      
      <!-- Active Jobs Tab Content -->
      <JobsActive 
        v-else-if="activeTab === 'active' && activeJobs.length > 0"
        :active-jobs="activeJobs"
        @job-cancelled="fetchActiveJobs"
      />
      
      <!-- Completed Jobs Tab Content -->
      <JobsCompleted 
        v-else-if="activeTab === 'completed' && completedJobs.length > 0"
        :completed-jobs="completedJobs"
        :has-more-pages="hasMorePagesSuccess"
        :is-loading="isLoadingMoreSuccess"
        @view-image="viewImage"
        @load-more="handleLoadMoreSuccess"
      />
      
      <!-- Failed Jobs Tab Content -->
      <JobsError 
        v-else-if="activeTab === 'failed' && failedJobs.length > 0"
        :failed-jobs="failedJobs"
        :has-more-pages="hasMorePagesFailed"
        :is-loading="isLoadingMoreFailed"
        @load-more="handleLoadMoreFailed"
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
import { ButtonBack, LoadingState } from '@/components/base'
import { JobsActive, JobsCompleted, JobsError, EmptyJob, DetailsJob } from '@/components/features/images'
import { useImageJob } from '@/composables/features/images/useImageJob'
import { ref, onMounted, computed, onErrorCaptured, watch } from 'vue';
import { toast } from 'vue-sonner';

export default {
  name: 'ImageJobsManager',
  components: {
    ButtonBack,
    JobsActive,
    JobsCompleted,
    JobsError,
    EmptyJob,
    LoadingState,
    DetailsJob
  },
  setup() {
    const { fetchActiveJobs, fetchCompletedJobs, fetchFailedJobs, fetchAllJobs, isLoadingSuccess, isLoadingFailed, isLoadingMoreSuccess, isLoadingMoreFailed, hasMorePagesSuccess, hasMorePagesFailed, hasLoadedMoreSuccess, hasLoadedMoreFailed, loadMoreCompletedJobs, loadMoreFailedJobs } = useImageJob();

    const activeTab = ref('active');
    const activeJobs = ref([]);
    const completedJobs = ref([]);
    const failedJobs = ref([]);
    const previewImage = ref(null);
    const isRefreshingActive = ref(false);
    const isRefreshingCompleted = ref(false);
    const isRefreshingFailed = ref(false);
    
    // Computed properties để đảm bảo safe access
    const safeActiveJobs = computed(() => Array.isArray(activeJobs.value) ? activeJobs.value : []);
    const safeCompletedJobs = computed(() => Array.isArray(completedJobs.value) ? completedJobs.value : []);
    const safeFailedJobs = computed(() => Array.isArray(failedJobs.value) ? failedJobs.value : []);
    
        // View image details
    const viewImage = (job) => {
      previewImage.value = job;
    };
     
    // Tải thêm jobs
    const handleLoadMoreSuccess = async () => {
      await loadMoreCompletedJobs(completedJobs);
    };

    // Tải thêm failed jobs
    const handleLoadMoreFailed = async () => {
      await loadMoreFailedJobs(failedJobs);
    };

    // Refresh các loại jobs
    const refreshActiveJobs = async () => {
      if (isRefreshingActive.value) return;
      try {
        isRefreshingActive.value = true;
        await fetchActiveJobs(activeJobs);
        toast.success('Đã làm mới danh sách tiến trình đang thực thi');
      } catch (error) {
        toast.error('Lỗi khi làm mới');
      } finally {
        isRefreshingActive.value = false;
      }
    };

    const refreshCompletedJobsManually = async () => {
      if (isRefreshingCompleted.value) return;
      try {
        isRefreshingCompleted.value = true;
        await fetchCompletedJobs(completedJobs);
        toast.success('Đã làm mới danh sách tiến trình đã hoàn thành');
      } catch (error) {
        toast.error('Lỗi khi làm mới');
      } finally {
        isRefreshingCompleted.value = false;
      }
    };

    const refreshFailedJobs = async () => {
      if (isRefreshingFailed.value) return;
      try {
        isRefreshingFailed.value = true;
        await fetchFailedJobs(failedJobs);
        toast.success('Đã làm mới danh sách tiến trình thất bại');
      } catch (error) {
        toast.error('Lỗi khi làm mới');
      } finally {
        isRefreshingFailed.value = false;
      }
    };

    // Xem lại danh sách tiến trình đã hoàn thành
    watch(activeTab, (newTab, oldTab) => {
      if (newTab === 'completed' && oldTab !== 'completed') {
        // Reset state cho completed jobs
        hasLoadedMoreSuccess.value = false;
      } else if (newTab === 'failed' && oldTab !== 'failed') {
        // Reset state cho failed jobs
        hasLoadedMoreFailed.value = false;
      }
    });

    onMounted(async () => {
      await fetchAllJobs(activeJobs, completedJobs, failedJobs);
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
        isLoadingSuccess,
        isLoadingFailed,
        isLoadingMoreSuccess,
        isLoadingMoreFailed,
        hasMorePagesSuccess,
        hasMorePagesFailed,
        hasLoadedMoreSuccess,
        hasLoadedMoreFailed,
        previewImage,
        isRefreshingActive,
        isRefreshingCompleted,
        isRefreshingFailed,
        viewImage,
        handleLoadMoreSuccess,
        handleLoadMoreFailed,
        refreshActiveJobs,
        refreshCompletedJobsManually,
        refreshFailedJobs,
        fetchActiveJobs,
        fetchCompletedJobs,
        fetchFailedJobs,
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