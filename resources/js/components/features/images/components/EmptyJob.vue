<template>
    <div class="bg-white rounded-xl shadow-lg p-12 text-center">
        <svg class="w-20 h-20 mx-auto text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
        </svg>
        <h2 class="mt-4 text-xl font-semibold text-gray-700">
          {{ activeTab === 'active' ? 'Không có tiến trình đang thực thi' : activeTab === 'completed' ? 'Không có tiến trình đã hoàn thành' : 'Không có tiến trình thất bại' }}
        </h2>
        <p class="mt-2 text-gray-500">
          {{ activeTab === 'active' 
            ? 'Tất cả các tiến trình tạo ảnh của bạn đã hoàn thành hoặc bạn chưa tạo tiến trình nào.' 
            : activeTab === 'completed' 
              ? 'Bạn chưa có tiến trình tạo ảnh nào đã hoàn thành. Hãy thử tạo một tiến trình mới.' 
              : 'Bạn chưa có tiến trình tạo ảnh nào thất bại. Hãy thử tạo một tiến trình mới.' 
          }}
        </p>
        <router-link 
          to="/features" 
          class="mt-6 inline-block px-6 py-3 bg-gradient-text text-white rounded-lg font-medium hover:bg-blue-700 transition-colors"
        >
          Tạo ảnh mới
        </router-link>
    </div>
</template>

<script>
export default {
    name: 'EmptyJob',
    props: {
        activeTab: {
            type: String,
            required: true
        }
    },
    setup(props) {
        return {}
    },
}
</script>
