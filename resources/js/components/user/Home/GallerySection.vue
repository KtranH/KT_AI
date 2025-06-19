<template>
  <section class="py-20 bg-gray-50">
    <div class="container mx-auto px-6">
      <div class="text-center mb-16">
        <h2 class="text-3xl md:text-4xl font-bold text-gray-800 mb-4" data-aos="fade-up">Bộ Sưu Tập Truyền Cảm Hứng</h2>
        <p class="text-xl text-gray-600 max-w-2xl mx-auto" data-aos="fade-up" data-aos-delay="100">
          Khám phá những tác phẩm tuyệt vời từ cộng đồng nghệ sĩ và nhà sáng tạo của chúng tôi.
        </p>
        
        <div class="flex flex-wrap justify-center gap-4 mt-8" data-aos="fade-up" data-aos-delay="200">
          <button class="px-4 py-2 bg-indigo-600 text-white font-medium rounded-lg cursor-pointer whitespace-nowrap">Tất cả</button>
          <!--<button class="px-4 py-2 bg-white text-gray-700 font-medium rounded-lg hover:bg-gray-100 cursor-pointer whitespace-nowrap">Nhiếp ảnh</button>
          <button class="px-4 py-2 bg-white text-gray-700 font-medium rounded-lg hover:bg-gray-100 cursor-pointer whitespace-nowrap">Nghệ thuật số</button>
          <button class="px-4 py-2 bg-white text-gray-700 font-medium rounded-lg hover:bg-gray-100 cursor-pointer whitespace-nowrap">Thiết kế đồ họa</button>
          <button class="px-4 py-2 bg-white text-gray-700 font-medium rounded-lg hover:bg-gray-100 cursor-pointer whitespace-nowrap">Minh họa</button>-->
        </div>
      </div>
      
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        <div v-for="(item, index) in galleryItems" :key="index" class="group relative overflow-hidden rounded-xl shadow-lg" :data-aos="'fade-up'" :data-aos-delay="100 + (index * 50)">
          <img :src="item.image" :alt="item.title" class="w-full h-80 object-cover object-top transition-transform duration-500 group-hover:scale-110" />
          <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/30 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex flex-col justify-end p-6">
            <h3 class="text-xl font-bold text-white mb-2">{{ item.title }}</h3>
            <p class="text-white/80 mb-4">{{ item.author }}</p>
            <div class="flex space-x-3">
              <button class="p-2 bg-white/20 rounded-full text-white hover:bg-white/30 transition-colors cursor-pointer">
                <i class="fas fa-heart"></i>
              </button>
              <button class="p-2 bg-white/20 rounded-full text-white hover:bg-white/30 transition-colors cursor-pointer">
                <i class="fas fa-share-alt"></i>
              </button>
              <button class="p-2 bg-white/20 rounded-full text-white hover:bg-white/30 transition-colors cursor-pointer">
                <i class="fas fa-bookmark"></i>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { ref } from 'vue';

// Gallery items
const galleryItems = ref([
  {
    title: 'Bình Minh Bình Yên',
    author: 'Bởi Minh Trang',
    image: '/img/Gallery1.webp'
  },
  {
    title: 'Khám Phá Đô Thị',
    author: 'Bởi Quang Huy',
    image: '/img/Gallery2.webp'
  },
  {
    title: 'Giấc Mơ Kỹ Thuật Số',
    author: 'Bởi Thanh Hà',
    image: '/img/Gallery3.webp'
  },
  {
    title: 'Mẫu Hình Tự Nhiên',
    author: 'Bởi Minh Đức',
    image: '/img/Gallery4.webp'
  },
  {
    title: 'Chân Dung Hoàn Hảo',
    author: 'Bởi Thu Hương',
    image: '/img/Gallery5.webp'
  },
  {
    title: 'Cảm Xúc Trừu Tượng',
    author: 'Bởi Anh Tuấn',
    image: '/img/Gallery6.webp'
  }
]);
</script>

<style scoped>
.group:hover .group-hover\:opacity-100 {
  opacity: 1;
}

.group:hover .group-hover\:scale-110 {
  transform: scale(1.1);
}
</style> 