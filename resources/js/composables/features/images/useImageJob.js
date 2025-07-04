import { comfyuiAPI } from '@/services/api'
import { toast } from 'vue-sonner'
import { ref } from 'vue'

export function useImageJob()
{
    const isLoading = ref(true);
    const isLoadingMore = ref(false);
    const hasMorePages = ref(false);
    const currentPage = ref(1);
    const hasLoadedMore = ref(false); // Track if user has loaded more data
    // Fetch active jobs
    const fetchActiveJobs = async (activeJobs) => {
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
      const fetchCompletedJobs = async (completedJobs) => {
        try {
          // Reset về trang 1 khi fetch lại
          currentPage.value = 1;
          hasLoadedMore.value = false; // Reset load more state
          const response = await comfyuiAPI.getCompletedJobs(1);
          
          if (response.data.success) {
            const jobs = response.data.data?.jobs || [];
            completedJobs.value = jobs;
            currentPage.value = response.data.data?.pagination?.current_page || 1;
            hasMorePages.value = response.data.data?.pagination?.has_more_pages || false;
          } else {
            completedJobs.value = [];
            hasMorePages.value = false;
          }
        } catch (error) {
          console.error('Lỗi khi lấy tiến trình đã hoàn thành:', error);
          toast.error('Không thể tải danh sách tiến trình đã hoàn thành');
          completedJobs.value = [];
          hasMorePages.value = false;
        } finally {
          isLoading.value = false;
        }
      };

        // Load more jobs
       const loadMoreCompletedJobs = async (completedJobs) => {
         if (isLoadingMore.value || !hasMorePages.value) {
           return;
         }
         
         try {
           isLoadingMore.value = true;
           const response = await comfyuiAPI.getCompletedJobs(currentPage.value + 1);
           
           if (response.data.success) {
             const newJobs = response.data.data?.jobs || [];
             completedJobs.value = [...completedJobs.value, ...newJobs];
             currentPage.value = response.data.data?.pagination?.current_page || 1;
             hasMorePages.value = response.data.data?.pagination?.has_more_pages || false;
             hasLoadedMore.value = true; // Đánh dấu đã load thêm
           } else {
             toast.error('Không thể tải thêm tiến trình đã hoàn thành');
           }
         } catch (error) {
           console.error('Lỗi khi tải thêm tiến trình đã hoàn thành:', error);
           toast.error('Lỗi khi tải thêm tiến trình đã hoàn thành');
         } finally {
           isLoadingMore.value = false;
         }
       };
      
      // Fetch failed jobs
      const fetchFailedJobs = async (failedJobs) => {
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
      const fetchAllJobs = async (activeJobs, completedJobs, failedJobs) => {
        try {
          isLoading.value = true;
          await Promise.all([fetchActiveJobs(activeJobs), fetchCompletedJobs(completedJobs), fetchFailedJobs(failedJobs)]);
        } catch (error) {
          console.error('Error fetching all jobs:', error);
          toast.error('Không thể tải danh sách công việc');
        } finally {
          isLoading.value = false;
        }
      };

      // Return tất cả các function và biến cần thiết
      return {
        isLoading,
        isLoadingMore,
        hasMorePages,
        hasLoadedMore,
        fetchActiveJobs,
        fetchCompletedJobs,
        loadMoreCompletedJobs,
        fetchFailedJobs,
        fetchAllJobs
      };
}