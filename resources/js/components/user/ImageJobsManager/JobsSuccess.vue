<template>
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
</template>

<script>
import { formatDate, downloadImage } from '@/utils';

export default {
    name: 'JobsSuccess',
    props: {
        completedJobs: {
            type: Array,
            required: true
        }
    },
    emits: ['view-image'],
    setup(props, { emit }) {

        const viewImage = (job) => {
            emit('view-image', job);
        };

        return {
            formatDate,
            viewImage,
            downloadImage
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