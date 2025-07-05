import { imageJobsAPI } from '@/services/api'
import { toast } from 'vue-sonner'
import { ref } from 'vue'

export function useImageJob()
{
    const isLoadingSuccess = ref(true);
    const isLoadingFailed = ref(true);
    const isLoadingMoreSuccess = ref(false);
    const isLoadingMoreFailed = ref(false);
    const hasMorePagesSuccess = ref(false);
    const hasMorePagesFailed = ref(false);
    const currentPageSuccess = ref(1);
    const currentPageFailed = ref(1);
    const hasLoadedMoreSuccess = ref(false); // Track if user has loaded more data
    const hasLoadedMoreFailed = ref(false); // Track if user has loaded more data
    // Fetch active jobs
    const fetchActiveJobs = async (activeJobs) => {
        try {
          const response = await imageJobsAPI.getActiveJobs();
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
          currentPageSuccess.value = 1;
          hasLoadedMoreSuccess.value = false; // Reset load more state
          const response = await imageJobsAPI.getCompletedJobs(1);
          
          if (response.data.success) {
            const jobs = response.data.data?.jobs || [];
            completedJobs.value = jobs;
            currentPageSuccess.value = response.data.data?.pagination?.current_page || 1;
            hasMorePagesSuccess.value = response.data.data?.pagination?.has_more_pages || false;
          } else {
            completedJobs.value = [];
            hasMorePagesSuccess.value = false;
          }
        } catch (error) {
          console.error('Lỗi khi lấy tiến trình đã hoàn thành:', error);
          toast.error('Không thể tải danh sách tiến trình đã hoàn thành');
          completedJobs.value = [];
          hasMorePagesSuccess.value = false;
        } finally {
          isLoadingSuccess.value = false;
        }
      };

        // Load more jobs
       const loadMoreCompletedJobs = async (completedJobs) => {
         if (isLoadingMoreSuccess.value || !hasMorePagesSuccess.value) {
           return;
         }
         
         try {
           isLoadingMoreSuccess.value = true;
           const response = await imageJobsAPI.getCompletedJobs(currentPageSuccess.value + 1);
           
           if (response.data.success) {
             const newJobs = response.data.data?.jobs || [];
             completedJobs.value = [...completedJobs.value, ...newJobs];
             currentPageSuccess.value = response.data.data?.pagination?.current_page || 1;
             hasMorePagesSuccess.value = response.data.data?.pagination?.has_more_pages || false;
             hasLoadedMoreSuccess.value = true; // Đánh dấu đã load thêm
           } else {
             toast.error('Không thể tải thêm tiến trình đã hoàn thành');
           }
         } catch (error) {
           console.error('Lỗi khi tải thêm tiến trình đã hoàn thành:', error);
           toast.error('Lỗi khi tải thêm tiến trình đã hoàn thành');
         } finally {
           isLoadingMoreSuccess.value = false;
         }
       };
      
      // Fetch failed jobs
      const fetchFailedJobs = async (failedJobs) => {
        try {
          // Reset về trang 1 khi fetch lại
          currentPageFailed.value = 1;
          hasLoadedMoreFailed.value = false; // Reset load more state
          const response = await imageJobsAPI.getFailedJobs(1);
          
          if (response.data.success) {
            const jobs = response.data.data?.jobs || [];
            failedJobs.value = jobs;
            currentPageFailed.value = response.data.data?.pagination?.current_page || 1;
            hasMorePagesFailed.value = response.data.data?.pagination?.has_more_pages || false;
          } else {
            failedJobs.value = [];
            hasMorePagesFailed.value = false;
          }
        } catch (error) {
          console.error('Lỗi khi lấy tiến trình thất bại:', error);
          toast.error('Không thể tải danh sách tiến trình thất bại');
          failedJobs.value = [];
          hasMorePagesFailed.value = false;
        } finally {
          isLoadingFailed.value = false;
        }
      };

      // Load more failed jobs

      const loadMoreFailedJobs = async (failedJobs) => {
        if (isLoadingMoreFailed.value || !hasMorePagesFailed.value) {
          return;
        }
        
        try {
          isLoadingMoreFailed.value = true;
          const response = await imageJobsAPI.getFailedJobs(currentPageFailed.value + 1);
          
          if (response.data.success) {
            const newJobs = response.data.data?.jobs || [];
            failedJobs.value = [...failedJobs.value, ...newJobs];
            currentPageFailed.value = response.data.data?.pagination?.current_page || 1;
            hasMorePagesFailed.value = response.data.data?.pagination?.has_more_pages || false;
            hasLoadedMoreFailed.value = true;
          } else {
            toast.error('Không thể tải thêm tiến trình thất bại');
          }
        } catch (error) {
          console.error('Lỗi khi tải thêm tiến trình thất bại:', error);
          toast.error('Lỗi khi tải thêm tiến trình thất bại');
        } finally {
          isLoadingMoreFailed.value = false;
        }
      }
      
      // Fetch all jobs
      const fetchAllJobs = async (activeJobs, completedJobs, failedJobs) => {
        try {
          isLoadingSuccess.value = true;
          isLoadingFailed.value = true;
          await Promise.all([fetchActiveJobs(activeJobs), fetchCompletedJobs(completedJobs), fetchFailedJobs(failedJobs)]);
        } catch (error) {
          console.error('Error fetching all jobs:', error);
          toast.error('Không thể tải danh sách công việc');
        } finally {
          isLoadingSuccess.value = false;
          isLoadingFailed.value = false;
        }
      };

      // Return tất cả các function và biến cần thiết
      return {
        isLoadingSuccess,
        isLoadingFailed,
        isLoadingMoreSuccess,
        isLoadingMoreFailed,
        hasMorePagesSuccess,
        hasMorePagesFailed,
        hasLoadedMoreSuccess,
        hasLoadedMoreFailed,
        fetchActiveJobs,
        fetchCompletedJobs,
        loadMoreCompletedJobs,
        fetchFailedJobs,
        loadMoreFailedJobs,
        fetchAllJobs
      };
}