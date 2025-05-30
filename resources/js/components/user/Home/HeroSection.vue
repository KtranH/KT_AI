<template>
  <section class="relative min-h-screen flex items-center overflow-hidden">
    <div class="absolute inset-0 z-0">
      <img 
        src="https://readdy.ai/api/search-image?query=abstract%20digital%20art%20with%20flowing%20gradients%20in%20purple%20and%20blue%20tones%2C%20creative%20fluid%20design%20with%20soft%20light%20effects%2C%20modern%20minimalist%20aesthetic%20perfect%20for%20a%20creative%20platform%20background%2C%20high%20resolution%20artwork%20with%20smooth%20transitions%20and%20depth&width=1440&height=800&seq=1&orientation=landscape" 
        alt="Creative background" 
        class="w-full h-full object-cover object-top"
      />
    </div>
    <div class="container mx-auto px-6 relative z-10 flex flex-col md:flex-row items-center">
      <div class="md:w-1/2 text-center md:text-left mb-12 md:mb-0">
        <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold text-white leading-tight mb-6" data-aos="fade-up">
          Biến <span class="bg-gradient-text-v2">Ý Tưởng Sáng Tạo</span> Của Bạn Thành Hiện Thực
        </h1>
        <p class="text-xl text-white/90 mb-8 max-w-lg mx-auto md:mx-0" data-aos="fade-up" data-aos-delay="100">
          Tạo hình ảnh tuyệt đẹp, chỉnh sửa với các công cụ mạnh mẽ, và chia sẻ tác phẩm của bạn với thế giới — tất cả trong một nền tảng.
        </p>
        <div class="flex flex-col sm:flex-row justify-center md:justify-start space-y-4 sm:space-y-0 sm:space-x-4" data-aos="fade-up" data-aos-delay="200">
          <router-link to="/dashboard" class="px-8 py-3 bg-gradient-text text-white font-medium rounded-lg hover:bg-indigo-700 transition-all transform hover:scale-105 shadow-lg cursor-pointer whitespace-nowrap">
            Bắt Đầu Sáng Tạo Ngay
          </router-link>
          <button class="px-8 py-3 bg-white text-indigo-600 font-medium rounded-lg hover:bg-gray-100 transition-all transform hover:scale-105 shadow-lg cursor-pointer whitespace-nowrap">
            <i class="fas fa-play-circle mr-2"></i> Xem Demo
          </button>
        </div>
      </div>
      <div class="md:w-1/2 relative" data-aos="fade-left">
        <img 
          src="https://pub-ed515111f589440fb333ebcd308ee890.r2.dev/img/banner2.png" 
          alt="Image Editing Interface" 
          class="mt-16"
        />
      </div>
    </div>
    <div class="absolute bottom-10 left-0 right-0 flex justify-center animate-bounce">
      <a href="#features" class="text-white bg-white/20 p-3 rounded-full cursor-pointer">
        <i class="fas fa-chevron-down"></i>
      </a>
    </div>
  </section>
</template>

<script setup>
// Component không cần logic riêng
</script>

<style scoped>
/* Animated bounce */
@keyframes bounce {
  0%, 100% {
    transform: translateY(0);
  }
  50% {
    transform: translateY(-10px);
  }
}

.animate-bounce {
  animation: bounce 1s infinite;
}
</style> 