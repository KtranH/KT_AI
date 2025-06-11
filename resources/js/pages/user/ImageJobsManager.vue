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
      <div v-if="isLoading" class="flex justify-center items-center py-16">
        <svg class="animate-spin h-12 w-12 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        <p class="ml-4 text-xl text-gray-600">Đang tải...</p>
      </div>
      
      <!-- Empty State -->
      <div v-else-if="(activeTab === 'active' && activeJobs.length === 0) || (activeTab === 'completed' && completedJobs.length === 0)" 
        class="bg-white rounded-xl shadow-lg p-12 text-center"
      >
        <svg class="w-20 h-20 mx-auto text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
        </svg>
        <h2 class="mt-4 text-xl font-semibold text-gray-700">
          {{ activeTab === 'active' ? 'Không có tiến trình đang thực thi' : 'Không có tiến trình đã hoàn thành' }}
        </h2>
        <p class="mt-2 text-gray-500">
          {{ activeTab === 'active' 
            ? 'Tất cả các tiến trình tạo ảnh của bạn đã hoàn thành hoặc bạn chưa tạo tiến trình nào.' 
            : 'Bạn chưa có tiến trình tạo ảnh nào đã hoàn thành. Hãy thử tạo một tiến trình mới.' 
          }}
        </p>
        <router-link 
          to="/features" 
          class="mt-6 inline-block px-6 py-3 bg-gradient-text text-white rounded-lg font-medium hover:bg-blue-700 transition-colors"
        >
          Tạo ảnh mới
        </router-link>
      </div>
      
      <!-- Active Jobs Tab Content -->
      <div v-else-if="activeTab === 'active' && activeJobs.length > 0" class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
          <div v-for="job in activeJobs" :key="job.id" class="bg-white rounded-xl shadow-lg overflow-hidden">
            <!-- Job Placeholder or Preview -->
            <div class="bg-gray-100 w-full h-48 flex items-center justify-center">
              <div v-if="job.status === 'pending'" class="text-center p-4">
                <svg class="w-16 h-16 mx-auto text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <p class="mt-2 text-blue-600 font-medium">Đang chờ xử lý</p>
              </div>
              <div v-else-if="job.status === 'processing'" class="text-center p-4">
                <svg class="w-16 h-16 mx-auto text-blue-500 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <p class="mt-2 text-blue-600 font-medium">Đang xử lý</p>
              </div>
              <div v-else-if="job.status === 'failed'" class="text-center p-4">
                <svg class="w-16 h-16 mx-auto text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
                <p class="mt-2 text-red-600 font-medium">Xử lý thất bại</p>
              </div>
            </div>
            
            <!-- Job Info -->
            <div class="p-4">
              <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-medium text-gray-500">ID: {{ job.id }}</span>
                <span 
                  :class="{
                    'bg-yellow-100 text-yellow-800': job.status === 'pending',
                    'bg-blue-100 text-blue-800': job.status === 'processing',
                    'bg-red-100 text-red-800': job.status === 'failed'
                  }"
                  class="px-2 py-1 text-xs rounded-full"
                >
                  {{ 
                    job.status === 'pending' ? 'Đang chờ' : 
                    job.status === 'processing' ? 'Đang xử lý' : 
                    'Thất bại'
                  }}
                </span>
              </div>
              
              <h3 class="text-md font-semibold text-gray-800 line-clamp-2 mb-2">{{ job.prompt }}</h3>
              
              <div class="text-sm text-gray-500 space-y-1 mb-4">
                <p>Kích thước: {{ job.width }}x{{ job.height }}</p>
                <p>Seed: {{ job.seed }}</p>
                <p>Thời gian: {{ formatDate(job.created_at) }}</p>
              </div>
              
              <div class="flex justify-end">
                <button 
                  @click="cancelJob(job.id)" 
                  class="px-4 py-2 bg-red-100 text-red-600 rounded-lg hover:bg-red-200 transition-colors"
                >
                  Hủy tiến trình
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Completed Jobs Tab Content -->
      <div v-else-if="activeTab === 'completed' && completedJobs.length > 0" class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
          <div v-for="job in completedJobs" :key="job.id" class="bg-white rounded-xl shadow-lg overflow-hidden group">
            <!-- Job Preview Image -->
            <div class="relative w-full h-48 overflow-hidden">
              <img 
                v-if="job.result_image_url" 
                :src="job.result_image_url" 
                :alt="job.prompt.substring(0, 20)" 
                class="w-full h-full object-cover transition-transform group-hover:scale-105"
              />
              
              <!-- Overlay with options -->
              <div class="absolute inset-0 bg-black bg-opacity-60 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center space-x-4">
                <button 
                  @click="viewImage(job)" 
                  class="p-2 bg-white bg-opacity-20 rounded-full hover:bg-opacity-30 transition-colors"
                >
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                  </svg>
                </button>
                <button 
                  @click="downloadImage(job.result_image_url, `image_${job.id}`)" 
                  class="p-2 bg-white bg-opacity-20 rounded-full hover:bg-opacity-30 transition-colors"
                >
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                  </svg>
                </button>
              </div>
            </div>
            
            <!-- Job Info -->
            <div class="p-4">
              <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-medium text-gray-500">ID: {{ job.id }}</span>
                <span class="bg-green-100 text-green-800 px-2 py-1 text-xs rounded-full">
                  Hoàn thành
                </span>
              </div>
              
              <h3 class="text-md font-semibold text-gray-800 line-clamp-2 mb-2">{{ job.prompt }}</h3>
              
              <div class="text-sm text-gray-500 space-y-1">
                <p>Kích thước: {{ job.width }}x{{ job.height }}</p>
                <p>Seed: {{ job.seed }}</p>
                <p>Thời gian: {{ formatDate(job.created_at) }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Failed Jobs Tab Content -->
      <div v-else-if="activeTab === 'failed' && failedJobs.length > 0" class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
          <div v-for="job in failedJobs" :key="job.id" class="bg-white rounded-xl shadow-lg overflow-hidden">
            <!-- Job Placeholder -->
            <div class="bg-gray-100 w-full h-48 flex items-center justify-center">
              <div class="text-center p-4">
                <svg class="w-16 h-16 mx-auto text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
                <p class="mt-2 text-red-600 font-medium">Xử lý thất bại</p>
              </div>
            </div>
            
            <!-- Job Info -->
            <div class="p-4">
              <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-medium text-gray-500">ID: {{ job.id }}</span>
                <span class="bg-red-100 text-red-800 px-2 py-1 text-xs rounded-full">
                  Thất bại
                </span>
              </div>
              
              <h3 class="text-md font-semibold text-gray-800 line-clamp-2 mb-2">{{ job.prompt }}</h3>
              
              <div class="text-sm text-gray-500 space-y-1 mb-4">
                <p>Kích thước: {{ job.width }}x{{ job.height }}</p>
                <p>Seed: {{ job.seed }}</p>
                <p>Thời gian: {{ formatDate(job.created_at) }}</p>
                <p v-if="job.error_message" class="text-red-500">Lỗi: {{ job.error_message }}</p>
              </div>
              
              <div class="flex justify-end">
                <button 
                  @click="retryJob(job.id)" 
                  class="px-4 py-2 bg-blue-100 text-blue-600 rounded-lg hover:bg-blue-200 transition-colors"
                >
                  Thử lại
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Image Preview Modal -->
      <div 
        v-if="previewImage" 
        class="fixed inset-0 bg-black/90 z-50 flex items-center justify-center p-4"
        @click="previewImage = null"
      >
        <div @click.stop class="relative max-w-4xl max-h-screen bg-white rounded-lg overflow-hidden">
          <button 
            @click="previewImage = null" 
            class="absolute top-2 right-2 p-1 bg-black/20 rounded-full text-white hover:bg-black/40 transition-colors z-10"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
          
          <div class="flex flex-col md:flex-row">
            <!-- Image -->
            <div class="md:w-2/3">
              <img 
                :src="previewImage.result_image_url" 
                :alt="previewImage.prompt" 
                class="w-full h-auto max-h-[70vh] object-contain"
              />
            </div>
            
            <!-- Details -->
            <div class="md:w-1/3 p-6 bg-gray-50">
              <h3 class="text-xl font-semibold text-gray-800 mb-4">Chi tiết tiến trình</h3>
              
              <div class="space-y-4">
                <div>
                  <h4 class="text-sm font-medium text-gray-500">Prompt</h4>
                  <p class="text-gray-800">{{ previewImage.prompt }}</p>
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                  <div>
                    <h4 class="text-sm font-medium text-gray-500">Kích thước</h4>
                    <p class="text-gray-800">{{ previewImage.width }}x{{ previewImage.height }}</p>
                  </div>
                  
                  <div>
                    <h4 class="text-sm font-medium text-gray-500">Seed</h4>
                    <p class="text-gray-800">{{ previewImage.seed }}</p>
                  </div>
                </div>
                
                <div>
                  <h4 class="text-sm font-medium text-gray-500">Phong cách</h4>
                  <p class="text-gray-800 capitalize">{{ previewImage.style || 'Mặc định' }}</p>
                </div>
                
                <div>
                  <h4 class="text-sm font-medium text-gray-500">Thời gian tạo</h4>
                  <p class="text-gray-800">{{ formatDate(previewImage.created_at) }}</p>
                </div>
                
                <button 
                  @click="downloadImage(previewImage.result_image_url, `image_${previewImage.id}`)" 
                  class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors mt-4"
                >
                  Tải xuống
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import ButtonBackVue from '../../components/common/ButtonBack.vue'
import { ref, onMounted, onBeforeUnmount } from 'vue';
import { format } from 'date-fns';
import { vi } from 'date-fns/locale';
import { toast } from 'vue-sonner';
import { proxyAPI, comfyuiAPI } from '@/services/api';

export default {
  name: 'ImageJobsManager',
  components: {
    ButtonBackVue
  },
  setup() {
    const activeTab = ref('active');
    const activeJobs = ref([]);
    const completedJobs = ref([]);
    const failedJobs = ref([]);
    const isLoading = ref(true);
    const previewImage = ref(null);
    const refreshInterval = ref(null);
    
    // Fetch active jobs
    const fetchActiveJobs = async () => {
      try {
        const response = await comfyuiAPI.getActiveJobs();
        if (response.data.success) {
          activeJobs.value = response.data.active_jobs;
        }
      } catch (error) {
        console.error('Lỗi khi lấy tiến trình đang hoạt động:', error);
        toast.error('Không thể tải danh sách tiến trình đang hoạt động');
      }
    };
    
    // Fetch completed jobs
    const fetchCompletedJobs = async () => {
      try {
        const response = await comfyuiAPI.getCompletedJobs();
        if (response.data.success) {
          completedJobs.value = response.data.completed_jobs;
        }
      } catch (error) {
        console.error('Lỗi khi lấy tiến trình đã hoàn thành:', error);
        toast.error('Không thể tải danh sách tiến trình đã hoàn thành');
      } finally {
        isLoading.value = false;
      }
    };
    
    // Fetch failed jobs
    const fetchFailedJobs = async () => {
      try {
        const response = await comfyuiAPI.getFailedJobs();
        if (response.data.success) {
          failedJobs.value = response.data.failed_jobs;
        }
      } catch (error) {
        console.error('Lỗi khi lấy tiến trình thất bại:', error);
        toast.error('Không thể tải danh sách tiến trình thất bại');
      }
    };
    
    // Fetch all jobs
    const fetchAllJobs = async () => {
      isLoading.value = true;
      await Promise.all([fetchActiveJobs(), fetchCompletedJobs(), fetchFailedJobs()]);
      isLoading.value = false;
    };
    
    // Cancel a job
    const cancelJob = async (jobId) => {
      try {
        const response = await comfyuiAPI.cancelJob(jobId);
        if (response.data.success) {
          toast.success('Đã hủy tiến trình thành công');
          await fetchActiveJobs();
        }
      } catch (error) {
        console.error('Lỗi khi hủy tiến trình:', error);
        toast.error('Không thể hủy tiến trình');
      }
    };
    
    // Retry a job
    const retryJob = async (jobId) => {
      try {
        const response = await comfyuiAPI.retryJob(jobId);
        if (response.data.success) {
          toast.success('Đã thử lại tiến trình thành công');
          await fetchFailedJobs();
        }
      } catch (error) {
        console.error('Lỗi khi thử lại tiến trình:', error);
        toast.error('Không thể thử lại tiến trình');
      }
    };
    
    // Format date
    const formatDate = (dateString) => {
      try {
        return format(new Date(dateString), 'dd MMM yyyy, HH:mm', { locale: vi });
      } catch (error) {
        return dateString;
      }
    };
    
    // View image details
    const viewImage = (job) => {
      previewImage.value = job;
    };
    
    // Download image
    const downloadImage = (url, filename) => {
      // Sử dụng API proxy để tránh vấn đề CORS
      const proxyUrl = proxyAPI.getR2Image(url);
      
      fetch(proxyUrl)
        .then(response => {
          if (!response.ok) {
            throw new Error('Không thể tải xuống hình ảnh');
          }
          return response.blob();
        })
        .then(blob => {
          const link = document.createElement('a');
          link.href = URL.createObjectURL(blob);
          link.download = `${filename}.png`;
          document.body.appendChild(link);
          link.click();
          document.body.removeChild(link);
        })
        .catch(error => {
          console.error('Lỗi khi tải xuống hình ảnh:', error);
          toast.error('Không thể tải xuống hình ảnh');
        });
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
    
    return {
      activeTab,
      activeJobs,
      completedJobs,
      failedJobs,
      isLoading,
      previewImage,
      cancelJob,
      retryJob,
      formatDate,
      viewImage,
      downloadImage
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