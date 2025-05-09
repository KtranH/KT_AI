<template>
  <footer class="bg-white py-16">
    <div class="container mx-auto px-6">
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-10">
        <div>
          <a href="#" class="text-2xl font-bold text-indigo-600 flex items-center mb-6">
            <i class="fas fa-camera-retro mr-2"></i>
            <span>KT_AI</span>
          </a>
          <p class="text-gray-600 mb-6">
            Trao quyền cho người sáng tạo biến tầm nhìn của họ thành hiện thực với các công cụ tạo và chỉnh sửa hình ảnh mạnh mẽ.
          </p>
          <div class="flex space-x-4">
            <a href="#" class="w-10 h-10 bg-gray-100 rounded-full flex items-center justify-center text-indigo-600 hover:bg-indigo-100 transition-colors cursor-pointer">
              <i class="fab fa-facebook-f"></i>
            </a>
            <a href="#" class="w-10 h-10 bg-gray-100 rounded-full flex items-center justify-center text-indigo-600 hover:bg-indigo-100 transition-colors cursor-pointer">
              <i class="fab fa-twitter"></i>
            </a>
            <a href="#" class="w-10 h-10 bg-gray-100 rounded-full flex items-center justify-center text-indigo-600 hover:bg-indigo-100 transition-colors cursor-pointer">
              <i class="fab fa-instagram"></i>
            </a>
            <a href="#" class="w-10 h-10 bg-gray-100 rounded-full flex items-center justify-center text-indigo-600 hover:bg-indigo-100 transition-colors cursor-pointer">
              <i class="fab fa-pinterest"></i>
            </a>
          </div>
        </div>
        
        <div>
          <h3 class="text-lg font-bold text-gray-800 mb-6">Sản Phẩm</h3>
          <ul class="space-y-4">
            <li><a href="#" class="text-gray-600 hover:text-indigo-600 transition-colors cursor-pointer">Tính Năng</a></li>
            <li><a href="#" class="text-gray-600 hover:text-indigo-600 transition-colors cursor-pointer">Bảng Giá</a></li>
            <li><a href="#" class="text-gray-600 hover:text-indigo-600 transition-colors cursor-pointer">Mẫu Thiết Kế</a></li>
            <li><a href="#" class="text-gray-600 hover:text-indigo-600 transition-colors cursor-pointer">Hướng Dẫn</a></li>
            <li><a href="#" class="text-gray-600 hover:text-indigo-600 transition-colors cursor-pointer">Có Gì Mới</a></li>
          </ul>
        </div>
        
        <div>
          <h3 class="text-lg font-bold text-gray-800 mb-6">Công Ty</h3>
          <ul class="space-y-4">
            <li><a href="#" class="text-gray-600 hover:text-indigo-600 transition-colors cursor-pointer">Về Chúng Tôi</a></li>
            <li><a href="#" class="text-gray-600 hover:text-indigo-600 transition-colors cursor-pointer">Tuyển Dụng</a></li>
            <li><a href="#" class="text-gray-600 hover:text-indigo-600 transition-colors cursor-pointer">Blog</a></li>
            <li><a href="#" class="text-gray-600 hover:text-indigo-600 transition-colors cursor-pointer">Báo Chí</a></li>
            <li><a href="#" class="text-gray-600 hover:text-indigo-600 transition-colors cursor-pointer">Liên Hệ</a></li>
          </ul>
        </div>
        
        <div>
          <h3 class="text-lg font-bold text-gray-800 mb-6">Đăng Ký</h3>
          <p class="text-gray-600 mb-4">
            Nhận tin tức và cập nhật mới nhất từ đội ngũ của chúng tôi.
          </p>
          <div class="flex">
            <input type="email" placeholder="Email của bạn" class="px-4 py-2 border border-gray-300 rounded-l-lg focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:border-transparent text-sm" />
            <button class="px-4 py-2 bg-indigo-600 text-white rounded-r-lg hover:bg-indigo-700 transition-colors cursor-pointer">
              Đăng Ký
            </button>
          </div>
          <div class="mt-6">
            <h4 class="text-sm font-bold text-gray-800 mb-3">Chúng Tôi Chấp Nhận</h4>
            <div class="flex space-x-3">
              <i class="fab fa-cc-visa text-2xl text-gray-600"></i>
              <i class="fab fa-cc-mastercard text-2xl text-gray-600"></i>
              <i class="fab fa-cc-amex text-2xl text-gray-600"></i>
              <i class="fab fa-cc-paypal text-2xl text-gray-600"></i>
            </div>
          </div>
        </div>
      </div>
      
      <div class="border-t border-gray-200 mt-12 pt-8 flex flex-col md:flex-row justify-between items-center">
        <p class="text-gray-600 mb-4 md:mb-0">
          © 2025 KT_AI. Tất cả các quyền được bảo lưu.
        </p>
        <div class="flex space-x-6">
          <a href="#" class="text-gray-600 hover:text-indigo-600 transition-colors cursor-pointer">Chính Sách Riêng Tư</a>
          <a href="#" class="text-gray-600 hover:text-indigo-600 transition-colors cursor-pointer">Điều Khoản Dịch Vụ</a>
          <a href="#" class="text-gray-600 hover:text-indigo-600 transition-colors cursor-pointer">Chính Sách Cookie</a>
        </div>
      </div>
    </div>
  </footer>
</template>

<script setup>
// Component không cần logic riêng
</script>

<style scoped>
.transition-colors {
  transition: color 0.3s ease, background-color 0.3s ease;
}
</style> 