<template>
    <div 
        v-if="job" 
        class="fixed inset-0 bg-black/90 z-50 flex items-center justify-center p-4"
        @click="$emit('close')"
    >
        <div @click.stop class="relative max-w-4xl max-h-screen bg-white rounded-lg overflow-hidden">
            <button 
                @click="$emit('close')" 
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
                        :src="job.result_image_url" 
                        :alt="job.prompt" 
                        class="w-full h-auto max-h-[70vh] object-contain"
                    />
                </div>
                
                <!-- Details -->
                <div class="md:w-1/3 p-6 bg-gray-50">
                    <h3 class="text-xl font-semibold text-gray-800 mb-4">Chi tiết tiến trình</h3>
                    
                    <div class="space-y-4">
                        <div>
                            <h4 class="text-sm font-medium text-gray-500">Prompt</h4>
                            <p class="text-gray-800">{{ job.prompt }}</p>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <h4 class="text-sm font-medium text-gray-500">Kích thước</h4>
                                <p class="text-gray-800">{{ job.width }}x{{ job.height }}</p>
                            </div>
                            
                            <div>
                                <h4 class="text-sm font-medium text-gray-500">Seed</h4>
                                <p class="text-gray-800">{{ job.seed }}</p>
                            </div>
                        </div>
                        
                        <div>
                            <h4 class="text-sm font-medium text-gray-500">Phong cách</h4>
                            <p class="text-gray-800 capitalize">{{ job.style || 'Mặc định' }}</p>
                        </div>
                        
                        <div>
                            <h4 class="text-sm font-medium text-gray-500">Thời gian tạo</h4>
                            <p class="text-gray-800">{{ formatDate(job.created_at) }}</p>
                        </div>
                        
                        <button 
                            @click="downloadImage" 
                            class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors mt-4"
                        >
                            Tải xuống
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { formatDate, downloadImage } from '@/utils';

export default {
    name: 'DetailsJob',
    props: {
        job: {
            type: Object,
            required: true
        }
    },
    emits: ['close'],
    setup(props) {
        return {
            formatDate,
            downloadImage
        };
    }
}
</script>
