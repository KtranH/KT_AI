<template>
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
            </div>
            
            <!-- Job Info -->
            <div class="p-4">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-medium text-gray-500">ID: {{ job.id }}</span>
                    <span 
                        :class="{
                            'bg-yellow-100 text-yellow-800': job.status === 'pending',
                            'bg-blue-100 text-blue-800': job.status === 'processing'
                        }"
                        class="px-2 py-1 text-xs rounded-full"
                    >
                        {{ job.status === 'pending' ? 'Đang chờ' : 'Đang xử lý' }}
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
</template>

<script>
import { format } from 'date-fns';
import { vi } from 'date-fns/locale';
import { toast } from 'vue-sonner';
import { comfyuiAPI } from '@/services/api';

export default {
    name: 'JobsActive',
    props: {
        activeJobs: {
            type: Array,
            required: true
        }
    },
    emits: ['job-cancelled'],
    setup(props, { emit }) {
        const formatDate = (dateString) => {
            try {
                return format(new Date(dateString), 'dd MMM yyyy, HH:mm', { locale: vi });
            } catch (error) {
                return dateString;
            }
        };

        const cancelJob = async (jobId) => {
            try {
                const response = await comfyuiAPI.cancelJob(jobId);
                if (response.data.success) {
                    toast.success('Đã hủy tiến trình thành công');
                    emit('job-cancelled');
                }
            } catch (error) {
                console.error('Lỗi khi hủy tiến trình:', error);
                toast.error('Không thể hủy tiến trình');
            }
        };

        return {
            formatDate,
            cancelJob
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