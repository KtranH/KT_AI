<template>
    <!-- Preview ảnh khi tạo thành công -->
    <div v-if="successfulJob" class="bg-green-50 border border-green-200 rounded-xl p-6 mb-8 shadow-lg">
        <div class="flex flex-col md:flex-row items-start space-y-4 md:space-y-0 md:space-x-6">
            <div class="w-full md:w-1/3">
                <h2 class="text-xl font-semibold text-green-700 mb-4">Tạo ảnh thành công!</h2>
                <p class="text-gray-700 mb-4">Prompt: {{ successfulJob.prompt }}</p>
                <div class="flex space-x-2 mb-2">
                    <span class="text-sm text-gray-500">{{ successfulJob.width }}x{{ successfulJob.height }}</span>
                    <span class="text-sm text-gray-500">Seed: {{ successfulJob.seed }}</span>
                </div>
                <div class="flex space-x-2">
                    <router-link 
                        to="/image-jobs" 
                        class="px-4 py-2 bg-gradient-text text-white rounded-lg hover:bg-blue-700 transition-colors"
                    >
                        Xem tất cả hình ảnh
                    </router-link>
                    <button 
                        @click="closePreview" 
                        class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors"
                    >
                        Đóng
                    </button>
                </div>
            </div>
            <div class="w-full md:w-2/3 rounded-lg overflow-hidden shadow-lg">
                <img 
                    :src="successfulJob.result_image_url" 
                    :alt="successfulJob.prompt" 
                    class="w-full h-auto object-contain max-h-96 rounded-lg"
                />
            </div>
        </div>
    </div>
</template>

<script>
export default {
    props: {
        successfulJob: {
            type: Object,
            default: null,
            required: false
        }
    },
    emits: ['close'],
    setup(props, { emit }) {
        const closePreview = () => {
            emit('close');
        };

        return {
            closePreview
        }
    },
}
</script>
