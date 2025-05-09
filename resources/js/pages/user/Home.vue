<template>
  <div class="min-h-screen bg-white">
    <!-- Hero Section -->
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
            Biến <span class="text-indigo-400">Ý Tưởng Sáng Tạo</span> Của Bạn Thành Hiện Thực
          </h1>
          <p class="text-xl text-white/90 mb-8 max-w-lg mx-auto md:mx-0" data-aos="fade-up" data-aos-delay="100">
            Tạo hình ảnh tuyệt đẹp, chỉnh sửa với các công cụ mạnh mẽ, và chia sẻ tác phẩm của bạn với thế giới — tất cả trong một nền tảng.
          </p>
          <div class="flex flex-col sm:flex-row justify-center md:justify-start space-y-4 sm:space-y-0 sm:space-x-4" data-aos="fade-up" data-aos-delay="200">
            <router-link to="/create" class="px-8 py-3 bg-indigo-600 text-white font-medium rounded-lg hover:bg-indigo-700 transition-all transform hover:scale-105 shadow-lg cursor-pointer whitespace-nowrap">
              Bắt Đầu Sáng Tạo Ngay
            </router-link>
            <button class="px-8 py-3 bg-white text-indigo-600 font-medium rounded-lg hover:bg-gray-100 transition-all transform hover:scale-105 shadow-lg cursor-pointer whitespace-nowrap">
              <i class="fas fa-play-circle mr-2"></i> Xem Demo
            </button>
          </div>
        </div>
        <div class="md:w-1/2 relative" data-aos="fade-left">
          <img 
            src="https://readdy.ai/api/search-image?query=modern%20UI%20interface%20for%20image%20editing%20software%20with%20sleek%20controls%20and%20tools%2C%20showing%20a%20photo%20being%20edited%20with%20filter%20adjustments%20and%20enhancement%20tools%2C%20professional%20design%20with%20dark%20theme%20and%20colorful%20accents%2C%20creative%20workspace%20visualization&width=600&height=500&seq=2&orientation=landscape" 
            alt="Image Editing Interface" 
            class="rounded-xl shadow-2xl transform hover:scale-105 transition-transform duration-500 mx-auto"
          />
        </div>
      </div>
      <div class="absolute bottom-10 left-0 right-0 flex justify-center animate-bounce">
        <a href="#features" class="text-white bg-white/20 p-3 rounded-full cursor-pointer">
          <i class="fas fa-chevron-down"></i>
        </a>
      </div>
    </section>
    
    <!-- Features Section -->
    <section id="features" class="py-20 bg-gray-50">
      <div class="container mx-auto px-6">
        <div class="text-center mb-16">
          <h2 class="text-3xl md:text-4xl font-bold text-gray-800 mb-4" data-aos="fade-up">{{ countfeatures }} Công Cụ Sáng Tạo Mạnh Mẽ</h2>
          <p class="text-xl text-gray-600 max-w-2xl mx-auto" data-aos="fade-up" data-aos-delay="100">
            Tất cả những gì bạn cần để biến tưởng tượng thành hiện thực với các công cụ trực quan và chuyên nghiệp.
          </p>
        </div>
        
        <!-- Loading State -->
        <div v-if="loading" class="text-center py-12">
          <div class="inline-block animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-indigo-600"></div>
          <p class="mt-4 text-gray-600">Đang tải dữ liệu...</p>
        </div>
        
        <!-- Error State -->
        <div v-else-if="error" class="text-center py-12">
          <div class="inline-block p-3 rounded-full bg-red-100 mb-4">
            <i class="fas fa-exclamation-triangle text-2xl text-red-600"></i>
          </div>
          <p class="text-red-600 mb-4">{{ error }}</p>
          <button @click="loadFeatures" class="px-6 py-2 bg-indigo-600 text-white font-medium rounded-lg hover:bg-indigo-700 transition-all transform hover:scale-105 shadow-lg">
            Thử lại
          </button>
        </div>
        
        <!-- Features Grid -->
        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
          <div v-for="feature in displayedFeatures" :key="feature.id" 
              class="bg-white rounded-xl shadow-lg p-8 transform hover:scale-105 transition-all duration-300 cursor-pointer" 
              data-aos="fade-up" :data-aos-delay="100 * (feature.id % 4)">
            <div class="w-16 h-16 bg-indigo-100 rounded-full flex items-center justify-center mb-6 mx-auto md:mx-0">
              <i class="fas fa-wand-magic-sparkles text-2xl text-indigo-600"></i>
            </div>
            <h3 class="text-xl font-bold text-gray-800 mb-4">{{ feature.title }}</h3>
            <p class="text-gray-600 mb-4">{{ feature.description }}</p>
            <div class="mt-4 flex justify-between items-center">
              <span class="text-sm text-indigo-600 font-semibold">{{ feature.category }}</span>
              <router-link :to="`/features/${feature.id}`" class="px-4 py-2 bg-indigo-600 text-white font-medium rounded-lg hover:bg-indigo-700 transition-all transform hover:scale-105">
                Thử ngay
              </router-link>
            </div>
          </div>
        </div>
        
        <!-- Button Load more -->
        <div v-if="displayedFeatures.length > 0 && hasMore" class="text-center mt-12">
          <button @click="loadMoreFeatures" class="px-6 py-3 bg-indigo-600 text-white font-medium rounded-lg hover:bg-indigo-700 transition-all transform hover:scale-105 shadow-lg cursor-pointer" data-aos="fade-up">
            Khám phá thêm
          </button>
        </div>
      </div>
    </section>
    
    <!-- Creation Tools Preview -->
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
                <span class="font-medium">Điều Chỉnh Bộ Lọc</span>
              </div>
              <div class="absolute top-1/3 -right-6 bg-indigo-100 p-4 rounded-lg shadow-md z-10 transform hover:scale-110 transition-transform cursor-pointer">
                <i class="fas fa-object-group text-indigo-600 mr-2"></i>
                <span class="font-medium">Lựa Chọn Thông Minh</span>
              </div>
              <div class="absolute -bottom-6 left-1/4 bg-indigo-100 p-4 rounded-lg shadow-md z-10 transform hover:scale-110 transition-transform cursor-pointer">
                <i class="fas fa-fill-drip text-indigo-600 mr-2"></i>
                <span class="font-medium">Hiệu Chỉnh Màu Sắc</span>
              </div>
              <img 
                src="https://readdy.ai/api/search-image?query=split%20screen%20before%20and%20after%20photo%20editing%20comparison%20showing%20dramatic%20improvement%2C%20professional%20photo%20retouching%20with%20enhanced%20colors%20and%20clarity%2C%20detailed%20image%20editing%20showcase%20with%20visible%20tool%20interface%2C%20high%20quality%20demonstration%20of%20image%20enhancement&width=600&height=450&seq=3&orientation=landscape" 
                alt="Before and After Editing" 
                class="rounded-xl shadow-xl w-full"
              />
            </div>
          </div>
          
          <div class="lg:w-1/2" data-aos="fade-left">
            <h3 class="text-2xl font-bold text-gray-800 mb-6">Biến Đổi Ảnh Của Bạn Trong Vài Giây</h3>
            <p class="text-gray-600 mb-6">
              Công cụ chỉnh sửa trực quan của chúng tôi giúp bạn dễ dàng nâng cao hình ảnh với kết quả chất lượng chuyên nghiệp. Không cần kinh nghiệm thiết kế.
            </p>
            
            <div class="space-y-4">
              <div class="flex items-start">
                <div class="bg-indigo-100 p-2 rounded-full mr-4">
                  <i class="fas fa-check text-indigo-600"></i>
                </div>
                <div>
                  <h4 class="font-medium text-gray-800">Nâng Cao Một Chạm</h4>
                  <p class="text-gray-600">Áp dụng các bộ hiệu ứng được thiết kế chuyên nghiệp chỉ với một cú nhấp chuột.</p>
                </div>
              </div>
              
              <div class="flex items-start">
                <div class="bg-indigo-100 p-2 rounded-full mr-4">
                  <i class="fas fa-check text-indigo-600"></i>
                </div>
                <div>
                  <h4 class="font-medium text-gray-800">Chỉnh Sửa Nâng Cao</h4>
                  <p class="text-gray-600">Loại bỏ khuyết điểm và nâng cao chi tiết với các công cụ chính xác.</p>
                </div>
              </div>
              
              <div class="flex items-start">
                <div class="bg-indigo-100 p-2 rounded-full mr-4">
                  <i class="fas fa-check text-indigo-600"></i>
                </div>
                <div>
                  <h4 class="font-medium text-gray-800">Xóa Đối Tượng Thông Minh</h4>
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
    
    <!-- Image Gallery -->
    <section class="py-20 bg-gray-50">
      <div class="container mx-auto px-6">
        <div class="text-center mb-16">
          <h2 class="text-3xl md:text-4xl font-bold text-gray-800 mb-4" data-aos="fade-up">Bộ Sưu Tập Truyền Cảm Hứng</h2>
          <p class="text-xl text-gray-600 max-w-2xl mx-auto" data-aos="fade-up" data-aos-delay="100">
            Khám phá những tác phẩm tuyệt vời từ cộng đồng nghệ sĩ và nhà sáng tạo của chúng tôi.
          </p>
          
          <div class="flex flex-wrap justify-center gap-4 mt-8" data-aos="fade-up" data-aos-delay="200">
            <button class="px-4 py-2 bg-indigo-600 text-white font-medium rounded-lg cursor-pointer whitespace-nowrap">Tất cả</button>
            <button class="px-4 py-2 bg-white text-gray-700 font-medium rounded-lg hover:bg-gray-100 cursor-pointer whitespace-nowrap">Nhiếp ảnh</button>
            <button class="px-4 py-2 bg-white text-gray-700 font-medium rounded-lg hover:bg-gray-100 cursor-pointer whitespace-nowrap">Nghệ thuật số</button>
            <button class="px-4 py-2 bg-white text-gray-700 font-medium rounded-lg hover:bg-gray-100 cursor-pointer whitespace-nowrap">Thiết kế đồ họa</button>
            <button class="px-4 py-2 bg-white text-gray-700 font-medium rounded-lg hover:bg-gray-100 cursor-pointer whitespace-nowrap">Minh họa</button>
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
        
        <div class="text-center mt-12">
          <button class="px-6 py-3 bg-indigo-600 text-white font-medium rounded-lg hover:bg-indigo-700 transition-all transform hover:scale-105 shadow-lg cursor-pointer" data-aos="fade-up">
            Xem Thêm
          </button>
        </div>
      </div>
    </section>
    
    <!-- Call-to-Action Section -->
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
    
    <!-- Footer -->
    <footer class="bg-white py-16">
      <div class="container mx-auto px-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-10">
          <div>
            <a href="#" class="text-2xl font-bold text-indigo-600 flex items-center mb-6">
              <i class="fas fa-camera-retro mr-2"></i>
              <span>Imagify</span>
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
            © 2024 Imagify. Tất cả các quyền được bảo lưu.
          </p>
          <div class="flex space-x-6">
            <a href="#" class="text-gray-600 hover:text-indigo-600 transition-colors cursor-pointer">Chính Sách Riêng Tư</a>
            <a href="#" class="text-gray-600 hover:text-indigo-600 transition-colors cursor-pointer">Điều Khoản Dịch Vụ</a>
            <a href="#" class="text-gray-600 hover:text-indigo-600 transition-colors cursor-pointer">Chính Sách Cookie</a>
          </div>
        </div>
      </div>
    </footer>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';

// Sử dụng store cho features
import { usefeaturesStore } from '@/stores/user/featuresStore';

// State
const displayedFeatures = ref([]);
const countfeatures = ref(4);
const loading = ref(false);
const error = ref(null);
const page = ref(1);
const perPage = ref(4);
const scrollY = ref(0);

// Store
const storeFeatures = usefeaturesStore();
const features = computed(() => storeFeatures.features);
const hasMore = computed(() => displayedFeatures.value.length < features.value.length);

// Gallery items
const galleryItems = ref([
  {
    title: 'Bình Minh Bình Yên',
    author: 'Bởi Minh Trang',
    image: '/img/gallery/gallery1.jpg'
  },
  {
    title: 'Khám Phá Đô Thị',
    author: 'Bởi Quang Huy',
    image: '/img/gallery/gallery2.jpg'
  },
  {
    title: 'Giấc Mơ Kỹ Thuật Số',
    author: 'Bởi Thanh Hà',
    image: '/img/gallery/gallery3.jpg'
  },
  {
    title: 'Mẫu Hình Tự Nhiên',
    author: 'Bởi Minh Đức',
    image: '/img/gallery/gallery4.jpg'
  },
  {
    title: 'Chân Dung Hoàn Hảo',
    author: 'Bởi Thu Hương',
    image: '/img/gallery/gallery5.jpg'
  },
  {
    title: 'Cảm Xúc Trừu Tượng',
    author: 'Bởi Anh Tuấn',
    image: '/img/gallery/gallery6.jpg'
  }
]);

// Methods
const loadFeatures = async () => {
  loading.value = true;
  error.value = null;

  try {
    await storeFeatures.fetchFeatures();
    displayedFeatures.value = features.value.slice(0, perPage.value);
  } catch (err) {
    error.value = err.response?.data?.message || 'Không thể tải dữ liệu';
  } finally {
    loading.value = false;
  }
};

const loadMoreFeatures = () => {
  if (!hasMore.value) {
    return;
  }
  
  page.value++;
  const start = (page.value - 1) * perPage.value;
  const end = start + perPage.value;
  const newItems = features.value.slice(start, end);
  countfeatures.value += perPage.value;
  displayedFeatures.value = [...displayedFeatures.value, ...newItems];
};

// Handle scroll events
const handleScroll = () => {
  scrollY.value = window.scrollY;
};

// AOS animation simulation
const initAnimations = () => {
  const elements = document.querySelectorAll('[data-aos]');
  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        const delay = parseInt(entry.target.getAttribute('data-aos-delay') || '0');
        setTimeout(() => {
          entry.target.classList.add('aos-animate');
        }, delay);
      }
    });
  }, { threshold: 0.1 });
  
  elements.forEach(element => {
    observer.observe(element);
  });

  return observer;
};

// Lifecycle hooks
let observer;

onMounted(() => {
  // Load features
  loadFeatures();
  
  // Add scroll event listener
  window.addEventListener('scroll', handleScroll);
  
  // Initialize AOS-like animations
  observer = initAnimations();
});

onUnmounted(() => {
  // Remove scroll event listener
  window.removeEventListener('scroll', handleScroll);
  
  // Cleanup observer
  if (observer) {
    observer.disconnect();
  }
});
</script>

<style scoped>
/* Floating UI elements */
.floating-ui-element {
  transition: all 0.5s ease;
}

/* AOS animations simulation */
[data-aos] {
  opacity: 0;
  transform: translateY(20px);
  transition: opacity 0.6s ease, transform 0.6s ease;
}

[data-aos="fade-left"] {
  transform: translateX(20px);
}

[data-aos="fade-right"] {
  transform: translateX(-20px);
}

.aos-animate {
  opacity: 1;
  transform: translate(0, 0);
}

/* Custom scrollbar */
::-webkit-scrollbar {
  width: 8px;
}

::-webkit-scrollbar-track {
  background: #f1f1f1;
}

::-webkit-scrollbar-thumb {
  background: #6366f1;
  border-radius: 4px;
}

::-webkit-scrollbar-thumb:hover {
  background: #4f46e5;
}

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

/* Hero section styling */
.min-h-screen {
  min-height: 100vh;
}
</style>