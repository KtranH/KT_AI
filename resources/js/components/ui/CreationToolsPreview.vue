<template>
  <section class="py-20 bg-white">
    <div class="container mx-auto px-6">
      <div class="text-center mb-16">
        <h2 class="text-3xl md:text-4xl font-bold text-gray-800 mb-4" data-aos="fade-up">Xem Sức Mạnh Trong Hành Động</h2>
        <p class="text-xl text-gray-600 max-w-2xl mx-auto" data-aos="fade-up" data-aos-delay="100">
          Khám phá cách công cụ của chúng tôi biến đổi những hình ảnh bình thường thành tác phẩm phi thường.
        </p>
      </div>
      
      <div class="flex flex-col lg:flex-row items-center gap-10">
        <div class="lg:w-1/2" data-aos="fade-right">
          <div class="relative">
            <div class="absolute -top-6 -left-6 bg-indigo-100 p-4 rounded-lg shadow-md z-10 transform hover:scale-110 transition-transform cursor-pointer">
              <i class="fas fa-sliders text-indigo-600 mr-2"></i>
              <span class="font-medium">Thử quần áo</span>
            </div>
            <div class="absolute top-1/3 -right-6 bg-indigo-100 p-4 rounded-lg shadow-md z-10 transform hover:scale-110 transition-transform cursor-pointer">
              <i class="fas fa-object-group text-indigo-600 mr-2"></i>
              <span class="font-medium">Sao chép khuôn mặt</span>
            </div>
            <div class="absolute -bottom-6 left-1/4 bg-indigo-100 p-4 rounded-lg shadow-md z-10 transform hover:scale-110 transition-transform cursor-pointer">
              <i class="fas fa-fill-drip text-indigo-600 mr-2"></i>
              <span class="font-medium">Tạo ảnh theo ý muốn</span>
            </div>
            <img 
              src="https://pub-ed515111f589440fb333ebcd308ee890.r2.dev/img/banner4.png" 
              alt="Before and After Editing" 
              class="rounded-xl shadow-xl w-full "
            />
          </div>
        </div>
        
        <div class="lg:w-1/2" data-aos="fade-left">
          <h3 class="text-2xl font-bold text-gray-800 mb-6">Biến Đổi Ảnh Của Bạn Trong Vài Giây</h3>
          <p class="text-gray-600 mb-6">
            Đến với KT_AI. Cho phép bạn thỏa sức sáng tạo hình ảnh theo trí tưởng tượng của bạn. Mà không phải mất quá nhiều thời gian để thiết kế thủ công.
          </p>
          
          <div class="space-y-4">
            <div class="flex items-start">
              <div class="bg-indigo-100 p-2 rounded-full mr-4">
                <i class="fas fa-check text-indigo-600"></i>
              </div>
              <div>
                <h4 class="font-medium text-gray-800">Tạo ảnh từ văn bảng</h4>
                <p class="text-gray-600">Áp dụng các bộ hiệu ứng được thiết kế chuyên nghiệp chỉ với một cú nhấp chuột.</p>
              </div>
            </div>
            
            <div class="flex items-start">
              <div class="bg-indigo-100 p-2 rounded-full mr-4">
                <i class="fas fa-check text-indigo-600"></i>
              </div>
              <div>
                <h4 class="font-medium text-gray-800">Tạo ảnh từ hình ảnh</h4>
                <p class="text-gray-600">Loại bỏ khuyết điểm và nâng cao chi tiết với các công cụ chính xác.</p>
              </div>
            </div>
            
            <div class="flex items-start">
              <div class="bg-indigo-100 p-2 rounded-full mr-4">
                <i class="fas fa-check text-indigo-600"></i>
              </div>
              <div>
                <h4 class="font-medium text-gray-800">Tạo ảnh từ nhiều hình ảnh</h4>
                <p class="text-gray-600">Công cụ AI giúp xóa các đối tượng không mong muốn khỏi hình ảnh của bạn.</p>
              </div>
            </div>
          </div>
          
          <router-link to="/tools" class="inline-block mt-8 px-6 py-3 bg-indigo-600 text-white font-medium rounded-lg hover:bg-indigo-700 transition-all transform hover:scale-105 shadow-lg">
            Thử Ngay
          </router-link>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
// Component không cần logic riêng
</script>

<style scoped>
.transform {
  transition: transform 0.3s ease;
}
</style> 