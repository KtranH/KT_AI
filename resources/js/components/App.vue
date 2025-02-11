<template>
  <full-page ref="fullpage" :options="options" id="fullpage">
    <!-- Hero Section -->
    <div class="section bg-gradient-to-r from-purple-600 to-blue-600">
      <div class="text-center text-white">
        <h1 class="text-6xl font-bold mb-4">AI Image Generator</h1>
        <p class="text-xl mb-8">Tạo hình ảnh tuyệt đẹp với 20 chức năng AI độc đáo</p>
        <button @click="moveTo(2)" class="bg-white text-purple-600 px-8 py-3 rounded-full font-bold hover:bg-opacity-90 transition">
          Khám phá ngay
        </button>
      </div>
    </div>

    <!-- Features Section -->
    <div class="section bg-gray-100">
      <div class="container mx-auto px-4 py-20">
        <h2 class="text-4xl font-bold text-center mb-16">20 Chức năng Tuyệt vời</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
          <template v-for="(feature, index) in features" :key="index">
            <div class="bg-white rounded-lg shadow-lg p-6 transform hover:scale-105 transition">
              <div class="w-full h-48 bg-gray-200 rounded-lg mb-4">
                <!-- Placeholder for feature image -->
                <div class="w-full h-full flex items-center justify-center text-gray-400">
                  [Image {{ index + 1 }}]
                </div>
              </div>
              <h3 class="text-xl font-bold mb-2">{{ feature.title }}</h3>
              <p class="text-gray-600">{{ feature.description }}</p>
            </div>
          </template>
        </div>
      </div>
    </div>

    <!-- Call to Action -->
    <div class="section bg-gradient-to-r from-blue-600 to-purple-600">
      <div class="text-center text-white">
        <h2 class="text-4xl font-bold mb-4">Bắt đầu Sáng tạo Ngay</h2>
        <p class="text-xl mb-8">Khám phá sức mạnh của AI trong việc tạo hình ảnh</p>
        <button class="bg-white text-purple-600 px-8 py-3 rounded-full font-bold hover:bg-opacity-90 transition">
          Đăng ký Miễn phí
        </button>
      </div>
    </div>
  </full-page>
</template>

<script>
import 'fullpage.js/dist/fullpage.css'
import VueFullPage from 'vue-fullpage.js'

export default {
  name: 'App',
  data() {
    return {
      options: {
        licenseKey: 'null',
        navigation: true,
        navigationPosition: 'right',
        scrollingSpeed: 1000,
        autoScrolling: true,
        scrollHorizontally: true,
        fitToSection: true,
        animateAnchor: true,
        anchors: ['home', 'features', 'cta'],
      },
      features: [
        {
          title: 'Tạo Chân Dung AI',
          description: 'Tạo chân dung nghệ thuật với nhiều phong cách khác nhau'
        },
        {
          title: 'Chuyển Đổi Phong Cách',
          description: 'Biến đổi hình ảnh theo phong cách nghệ thuật mong muốn'
        },
        {
          title: 'Tạo Cảnh Quan',
          description: 'Tạo phong cảnh tuyệt đẹp từ mô tả văn bản'
        },
        {
          title: 'Chỉnh Sửa Thông Minh',
          description: 'Tự động chỉnh sửa và làm đẹp hình ảnh'
        },
        {
          title: 'Tạo Logo AI',
          description: 'Thiết kế logo chuyên nghiệp với AI'
        },
        {
          title: 'Phục Hồi Ảnh Cũ',
          description: 'Khôi phục và làm mới ảnh cũ'
        },
        {
          title: 'Tạo Ảnh Hoạt Hình',
          description: 'Chuyển đổi ảnh thành phong cách hoạt hình'
        },
        {
          title: 'Xóa Phông Nền',
          description: 'Tự động xóa và thay đổi phông nền'
        },
        {
          title: 'Tạo Ảnh 3D',
          description: 'Chuyển đổi ảnh 2D thành 3D'
        },
        {
          title: 'Ghép Ảnh AI',
          description: 'Ghép ảnh thông minh với AI'
        },
        {
          title: 'Tạo Ảnh Sản Phẩm',
          description: 'Tạo ảnh sản phẩm chuyên nghiệp'
        },
        {
          title: 'Hiệu Ứng Đặc Biệt',
          description: 'Thêm hiệu ứng nghệ thuật độc đáo'
        },
        {
          title: 'Tối Ưu Hóa Ảnh',
          description: 'Tự động tối ưu chất lượng ảnh'
        },
        {
          title: 'Tạo Ảnh Nhân Vật',
          description: 'Thiết kế nhân vật độc đáo'
        },
        {
          title: 'Chuyển Văn Bản Thành Ảnh',
          description: 'Tạo ảnh từ mô tả văn bản'
        },
        {
          title: 'Phân Tích Ảnh AI',
          description: 'Phân tích và đề xuất cải thiện'
        },
        {
          title: 'Tạo Ảnh Nghệ Thuật',
          description: 'Tạo tác phẩm nghệ thuật số'
        },
        {
          title: 'Chỉnh Màu Thông Minh',
          description: 'Tự động cân bằng và điều chỉnh màu sắc'
        },
        {
          title: 'Tạo Ảnh Quảng Cáo',
          description: 'Thiết kế ảnh quảng cáo chuyên nghiệp'
        },
        {
          title: 'Tùy Chỉnh Kích Thước',
          description: 'Thay đổi kích thước thông minh'
        }
      ]
    }
  },
  methods: {
    moveTo(index) {
      this.$refs.fullpage.api.moveTo(index);
    }
  }
}
</script>

<style>
#app {
  height: 100vh;
}

.section {
  display: flex;
  align-items: center;
  justify-content: center;
}

/* Tùy chỉnh màu sắc cho navigation dots */
#fp-nav ul li a span {
  background: white;
}

#fp-nav ul li a.active span {
  background: #7c3aed;
}

/* Ẩn thanh cuộn */
::-webkit-scrollbar {
  width: 0px;
  background: transparent;
}
</style> 