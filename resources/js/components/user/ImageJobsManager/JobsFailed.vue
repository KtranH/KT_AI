<template>
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
</template>

<script>
import { toast } from 'vue-sonner';
import { comfyuiAPI } from '@/services/api';
import { formatDate } from '@/utils';

export default {
    name: 'JobsFailed',
    props: {
        failedJobs: {
            type: Array,
            required: true
        }
    },
    emits: ['job-retried'],
    setup(props, { emit }) {
        const retryJob = async (jobId) => {
            try {
                const response = await comfyuiAPI.retryJob(jobId);
                if (response.data.success) {
                    toast.success('Đã thử lại tiến trình thành công');
                    emit('job-retried');
                }
            } catch (error) {
                console.error('Lỗi khi thử lại tiến trình:', error);
                toast.error('Không thể thử lại tiến trình');
            }
        };

        return {
            formatDate,
            retryJob
        };
    }
}
</script>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>