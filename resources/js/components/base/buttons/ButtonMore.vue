<template>
    <!-- Nút Xem thêm -->
    <div v-if="hasMorePages" class="mt-8 flex justify-center">
        <button 
          @click="handleLoadMore"
          :disabled="loading" 
          class="px-6 py-3 rounded-md bg-gradient-text text-white hover:bg-blue-600 disabled:bg-gray-400 disabled:cursor-not-allowed transition duration-150 ease-in-out"
        >
          <span v-if="loading" class="flex items-center">
            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            Đang tải...
          </span>
          <span v-else>Xem thêm</span>
        </button>
    </div>
</template>

<script>
export default {
    props: {
        hasMorePages: {
            type: Boolean,
            required: true,
        },
        loading: {
            type: Boolean,
            required: true,
        },
    },
    setup(props, { emit }) {
        const handleLoadMore = () => {
            emit('load-more');
        };

        return {
            handleLoadMore,
        };
    },
}
</script>