<template>
    <div 
        v-if="job" 
        class="fixed inset-0 bg-black/70 backdrop-blur-sm z-50 flex items-center justify-center p-4"
        @click="$emit('close')"
    >
        <div @click.stop class="relative max-w-6xl w-full max-h-[90vh] bg-white rounded-2xl shadow-2xl overflow-hidden border border-gray-200 animate-fadeIn">
            <button 
                @click="$emit('close')" 
                class="absolute top-4 right-4 p-2 bg-white/80 rounded-full text-gray-700 hover:bg-red-500 hover:text-white shadow transition-colors z-20 border border-gray-200"
                aria-label="Đóng"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
            
            <div class="flex flex-col md:flex-row">
                <!-- Image -->
                <div class="md:w-2/3 flex items-center justify-center bg-gray-100">
                    <img 
                        :src="job.result_image_url" 
                        :alt="job.prompt" 
                        class="w-full h-auto max-h-[65vh] object-contain rounded-xl shadow-lg border border-gray-200"
                    />
                </div>
                
                <!-- Details -->
                <div class="md:w-1/3 p-8 bg-white flex flex-col justify-between min-h-[350px]">
                    <div>
                        <h3 class="text-2xl font-bold text-gray-800 mb-6 flex items-center gap-2">
                            <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536M9 11l6 6M3 21h6v-6H3v6z"/></svg>
                            Chi tiết tiến trình
                        </h3>
                        <div class="space-y-5">
                            <div>
                                <h4 class="text-xs font-semibold text-gray-500 flex items-center gap-1">
                                    <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m2 0a8 8 0 11-16 0 8 8 0 0116 0z"/></svg>
                                    Prompt
                                </h4>
                                <p class="text-gray-800 text-sm mt-1 break-words">{{ job.prompt }}</p>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <h4 class="text-xs font-semibold text-gray-500 flex items-center gap-1">
                                        <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect width="18" height="14" x="3" y="5" rx="2"/><path d="M3 7h18"/></svg>
                                        Kích thước
                                    </h4>
                                    <p class="text-gray-800 text-sm mt-1">{{ job.width }}x{{ job.height }}</p>
                                </div>
                                <div>
                                    <h4 class="text-xs font-semibold text-gray-500 flex items-center gap-1">
                                        <svg class="w-4 h-4 text-yellow-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
                                        Seed
                                    </h4>
                                    <p class="text-gray-800 text-sm mt-1">{{ job.seed }}</p>
                                </div>
                            </div>
                            <div>
                                <h4 class="text-xs font-semibold text-gray-500 flex items-center gap-1">
                                    <svg class="w-4 h-4 text-pink-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
                                    Phong cách
                                </h4>
                                <p class="text-gray-800 text-sm mt-1 capitalize">{{ job.style || 'Mặc định' }}</p>
                            </div>
                            <div>
                                <h4 class="text-xs font-semibold text-gray-500 flex items-center gap-1">
                                    <svg class="w-4 h-4 text-cyan-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10m-7 4h4"/></svg>
                                    Thời gian tạo
                                </h4>
                                <p class="text-gray-800 text-sm mt-1">{{ formatDate(job.created_at) }}</p>
                            </div>
                        </div>
                    </div>
                    <button 
                        @click="downloadImage" 
                        class="w-full flex items-center justify-center gap-2 px-4 py-2 bg-gradient-text text-white rounded-xl font-semibold shadow hover:from-blue-700 hover:to-blue-600 transition-colors mt-8 text-base"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5 5-5M12 15V3"/></svg>
                        Tải xuống
                    </button>
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

<style scoped>
@keyframes fadeIn {
  from { opacity: 0; transform: scale(0.98); }
  to { opacity: 1; transform: scale(1); }
}
.animate-fadeIn {
  animation: fadeIn 0.25s cubic-bezier(0.4,0,0.2,1);
}
</style>
