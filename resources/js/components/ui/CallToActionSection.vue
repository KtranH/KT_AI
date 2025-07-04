<template>
  <section class="py-20 relative overflow-hidden">
    <div class="absolute inset-0 z-0">
      <img 
        src="https://readdy.ai/api/search-image?query=abstract%20gradient%20background%20with%20flowing%20shapes%20in%20purple%2C%20blue%20and%20pink%20colors%2C%20modern%20digital%20art%20with%20smooth%20transitions%20and%20depth%2C%20creative%20fluid%20design%20perfect%20for%20website%20hero%20section%2C%20high%20resolution%20artwork%20with%20dynamic%20composition&width=1440&height=600&seq=4&orientation=landscape" 
        alt="CTA Background" 
        class="w-full h-full object-cover object-top"
      />
    </div>
    <div class="container mx-auto px-6 relative z-10">
      <div class="max-w-3xl mx-auto text-center">
        <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-white mb-6" data-aos="fade-up">
          Sẵn Sàng Tạo Ra Điều Tuyệt Vời?
        </h2>
        <p class="text-xl text-white/90 mb-10 max-w-2xl mx-auto" data-aos="fade-up" data-aos-delay="100">
          Hãy tham gia cùng hàng ngàn nhà sáng tạo đang biến ý tưởng của họ thành hiện thực với công cụ mạnh mẽ và cộng đồng sôi động của chúng tôi.
        </p>
        <div class="flex flex-col sm:flex-row justify-center space-y-4 sm:space-y-0 sm:space-x-4" data-aos="fade-up" data-aos-delay="200">
          <router-link to="/register" class="px-8 py-4 bg-white text-indigo-600 font-bold rounded-lg hover:bg-gray-100 transition-all transform hover:scale-105 shadow-lg">
            Bắt Đầu Sáng Tạo Ngay
          </router-link>
          <button class="px-8 py-4 bg-indigo-600 border-2 border-white text-white font-bold rounded-lg hover:bg-indigo-700 transition-all transform hover:scale-105 shadow-lg">
            <i class="fas fa-play-circle mr-2"></i> Xem Demo
          </button>
        </div>
      </div>
    </div>
    
    <div class="absolute bottom-0 left-0 w-full">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320">
        <path fill="#ffffff" fill-opacity="1" d="M0,224L48,213.3C96,203,192,181,288,181.3C384,181,480,203,576,213.3C672,224,768,224,864,202.7C960,181,1056,139,1152,138.7C1248,139,1344,181,1392,202.7L1440,224L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path>
      </svg>
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