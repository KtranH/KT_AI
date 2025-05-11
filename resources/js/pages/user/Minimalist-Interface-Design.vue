<!-- The exported code uses Tailwind CSS. Install Tailwind CSS in your dev environment to ensure all styles work. -->

<template>
  <div class="min-h-screen bg-gradient-to-b from-purple-50 to-white">
    <div class="container mx-auto px-6 py-8 max-w-7xl">
      <!-- Header -->
      <header class="mb-8">
        <div class="flex items-center">
          <button class="w-10 h-10 rounded-full bg-purple-600 text-white flex items-center justify-center shadow-md hover:bg-purple-700 transition-all cursor-pointer !rounded-button whitespace-nowrap">
            <i class="fas fa-arrow-left"></i>
          </button>
          <h1 class="ml-4 text-2xl font-bold text-purple-800">Tạo ảnh theo phong cách ảnh khác</h1>
        </div>
      </header>

      <!-- Main Content -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Left Column -->
        <div class="space-y-6">
          <!-- Guidelines Card -->
          <div class="bg-white rounded-xl shadow-sm p-6">
            <h2 class="text-lg font-semibold text-purple-800 mb-4">Hướng dẫn sử dụng</h2>
            <ul class="space-y-4">
              <li class="flex items-start">
                <div class="flex-shrink-0 w-6 h-6 rounded-full bg-red-100 flex items-center justify-center text-red-500 mr-3">
                  <i class="fas fa-exclamation-circle"></i>
                </div>
                <p>Bạn có thể nhập thông tin mà có nêu có vào trong ô Prompt. Mỗi từ càng chi tiết, ảnh càng chi tiết nhưng tối đa không nên quá 1000 ký.</p>
              </li>
              <li class="flex items-start">
                <div class="flex-shrink-0 w-6 h-6 rounded-full bg-blue-100 flex items-center justify-center text-blue-500 mr-3">
                  <i class="fas fa-info-circle"></i>
                </div>
                <p>Kích thước ảnh càng lớn tạo ảnh càng lâu. Khuyến nghị là chọn cùng 512px và chọn cao là 768px.</p>
              </li>
              <li class="flex items-start">
                <div class="flex-shrink-0 w-6 h-6 rounded-full bg-green-100 flex items-center justify-center text-green-500 mr-3">
                  <i class="fas fa-check-circle"></i>
                </div>
                <p>Bạn có thể chọn thể loại để tạo ảnh nếu trong ô thể loại.</p>
              </li>
              <li class="flex items-start">
                <div class="flex-shrink-0 w-6 h-6 rounded-full bg-purple-100 flex items-center justify-center text-purple-500 mr-3">
                  <i class="fas fa-random"></i>
                </div>
                <p>Thông số Seed. Mỗi lần dùng chung 1 Seed thành ảnh sẽ giống nhau, nếu không có Số Seed. Nhập vào mã tạo random.</p>
              </li>
              <li class="flex items-start">
                <div class="flex-shrink-0 w-6 h-6 rounded-full bg-yellow-100 flex items-center justify-center text-yellow-500 mr-3">
                  <i class="fas fa-ban"></i>
                </div>
                <p>Vui lòng không tạo những hình ảnh dạy như tài ảnh dây dù vào các ô tài ảnh lên.</p>
              </li>
              <li class="flex items-start">
                <div class="flex-shrink-0 w-6 h-6 rounded-full bg-pink-100 flex items-center justify-center text-pink-500 mr-3">
                  <i class="fas fa-heart-broken"></i>
                </div>
                <p>Không được chèn các bộ ngữ ảnh cấm để tạo ảnh.</p>
              </li>
            </ul>
          </div>

          <!-- Image Settings Card -->
          <div class="bg-white rounded-xl shadow-sm p-6">
            <h2 class="text-lg font-semibold text-purple-800 mb-4">Thông số hình ảnh</h2>
            
            <div class="mb-6">
              <h3 class="text-md font-medium text-gray-700 mb-2">Thông số hình ảnh</h3>
              <div class="p-4 bg-gray-50 rounded-lg">
                <div class="mb-4">
                  <label class="block text-sm font-medium text-gray-700 mb-1">Kích thước hình ảnh</label>
                  <div class="flex justify-between mb-2">
                    <span class="text-sm text-gray-600">Chiều rộng: 512px</span>
                    <span class="text-sm text-gray-600">Chiều cao: 768px</span>
                  </div>
                  <div class="relative h-2 bg-gray-200 rounded-full">
                    <div class="absolute left-0 top-0 h-2 bg-purple-500 rounded-full" style="width: 40%"></div>
                    <div class="absolute h-4 w-4 bg-white border-2 border-purple-500 rounded-full -top-1 cursor-pointer" style="left: 40%" @mousedown="startDrag"></div>
                  </div>
                </div>
              </div>
            </div>

            <div class="mb-6">
              <label for="prompt" class="block text-sm font-medium text-gray-700 mb-2">Prompt</label>
              <textarea 
                id="prompt" 
                rows="4" 
                class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent resize-none"
                placeholder="Nhập mô tả chi tiết cho hình ảnh..."
                v-model="prompt"
              ></textarea>
            </div>

            <div class="mb-6">
              <label for="seed" class="block text-sm font-medium text-gray-700 mb-2">Seed</label>
              <div class="flex">
                <input 
                  type="text" 
                  id="seed" 
                  class="flex-1 p-3 border border-gray-300 rounded-l-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                  placeholder="Nhập seed"
                  v-model="seed"
                />
                <button class="bg-purple-100 text-purple-700 px-4 rounded-r-lg border border-l-0 border-gray-300 hover:bg-purple-200 transition-all cursor-pointer !rounded-button whitespace-nowrap">
                  Ngẫu nhiên
                </button>
              </div>
            </div>

            <div class="mb-6">
              <label for="style" class="block text-sm font-medium text-gray-700 mb-2">Phong cách</label>
              <div class="relative">
                <select 
                  id="style" 
                  class="w-full p-3 border border-gray-300 rounded-lg appearance-none bg-white focus:ring-2 focus:ring-purple-500 focus:border-transparent cursor-pointer"
                  v-model="style"
                >
                  <option value="realistic">Chân thực</option>
                  <option value="anime">Anime</option>
                  <option value="cartoon">Hoạt hình</option>
                  <option value="abstract">Trừu tượng</option>
                </select>
                <div class="absolute inset-y-0 right-0 flex items-center px-3 pointer-events-none text-gray-500">
                  <i class="fas fa-chevron-down"></i>
                </div>
              </div>
            </div>

            <button 
              class="w-full py-3 bg-gradient-to-r from-purple-600 to-purple-800 text-white font-medium rounded-lg shadow-md hover:from-purple-700 hover:to-purple-900 transition-all cursor-pointer !rounded-button whitespace-nowrap"
              @click="generateImage"
            >
              Tạo hình ảnh
            </button>
          </div>
        </div>

        <!-- Right Column -->
        <div class="space-y-6">
          <!-- Preview Card -->
          <div class="bg-white rounded-xl shadow-sm p-6">
            <h2 class="text-lg font-semibold text-purple-800 mb-4">Xem trước & Tải lên ảnh chính</h2>
            
            <div class="border border-dashed border-gray-300 rounded-lg p-8 flex flex-col items-center justify-center text-center cursor-pointer" @click="openFileDialog">
              <div v-if="!previewImage" class="mb-4">
                <div class="w-20 h-20 mx-auto bg-gray-100 rounded-full flex items-center justify-center text-gray-400">
                  <i class="fas fa-image text-3xl"></i>
                </div>
                <p class="mt-4 text-gray-500">Kéo thả hình ảnh vào đây</p>
                <p class="text-sm text-gray-400">PNG, JPG, WEBP - tối đa 5MB</p>
              </div>
              <img v-else :src="previewImage" alt="Preview" class="max-w-full max-h-64 rounded-lg object-contain" />
              
              <button class="mt-4 px-4 py-2 bg-purple-100 text-purple-700 rounded-lg hover:bg-purple-200 transition-all cursor-pointer !rounded-button whitespace-nowrap">
                Chọn hình ảnh
              </button>
            </div>
          </div>

          <!-- Generated Image Preview -->
          <div class="bg-white rounded-xl shadow-sm p-6">
            <h2 class="text-lg font-semibold text-purple-800 mb-4">Ảnh đã tạo</h2>
            
            <div v-if="generatedImage" class="rounded-lg overflow-hidden">
              <img :src="generatedImage" alt="Generated Image" class="w-full h-auto" />
              
              <div class="mt-4 flex justify-between">
                <button class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-all cursor-pointer !rounded-button whitespace-nowrap">
                  <i class="fas fa-redo mr-2"></i> Tạo lại
                </button>
                <button class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-all cursor-pointer !rounded-button whitespace-nowrap">
                  <i class="fas fa-download mr-2"></i> Tải xuống
                </button>
              </div>
            </div>
            <div v-else class="border border-gray-200 rounded-lg p-12 flex flex-col items-center justify-center text-center">
              <div class="w-16 h-16 mb-4 bg-purple-100 rounded-full flex items-center justify-center text-purple-500">
                <i class="fas fa-magic text-xl"></i>
              </div>
              <p class="text-gray-500">Chưa có ảnh được tạo</p>
              <p class="text-sm text-gray-400">Điền thông tin và nhấn "Tạo hình ảnh" để bắt đầu</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Gallery Section -->
      <div class="mt-12">
        <div class="bg-white rounded-xl shadow-sm p-6">
          <h2 class="text-lg font-semibold text-purple-800 mb-6">Danh sách ảnh người dùng tải lên</h2>
          
          <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            <!-- Add New Image -->
            <div class="border border-dashed border-gray-300 rounded-lg aspect-square flex flex-col items-center justify-center cursor-pointer hover:bg-gray-50 transition-all">
              <div class="w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center text-gray-400 mb-2">
                <i class="fas fa-plus"></i>
              </div>
              <p class="text-sm text-gray-500">Thêm ảnh</p>
            </div>
            
            <!-- Gallery Item 1 -->
            <div class="rounded-lg overflow-hidden shadow-sm border border-gray-100 group">
              <div class="relative aspect-square">
                <img 
                  src="https://readdy.ai/api/search-image?query=Beautiful%20Asian%20anime%20girl%20with%20short%20black%20hair%20and%20dark%20eyes%2C%20wearing%20a%20black%20dress%20against%20a%20dark%20moody%20background%2C%20detailed%20portrait%20in%20anime%20style%20with%20soft%20lighting%2C%20high%20quality%20digital%20art%20with%20dramatic%20shadows%20and%20highlights&width=400&height=400&seq=1&orientation=squarish" 
                  alt="Gallery image" 
                  class="w-full h-full object-cover"
                />
                <div class="absolute top-2 right-2 bg-white rounded-full w-8 h-8 flex items-center justify-center shadow-sm">
                  <span class="text-blue-500 font-medium text-xs">Nhật</span>
                </div>
              </div>
              <div class="p-3 bg-white">
                <div class="flex justify-between items-center">
                  <div class="flex items-center">
                    <i class="fas fa-heart text-red-500 mr-1"></i>
                    <span class="text-xs text-gray-600">0</span>
                  </div>
                  <div class="flex items-center">
                    <i class="fas fa-comment text-gray-400 mr-1"></i>
                    <span class="text-xs text-gray-600">0</span>
                  </div>
                </div>
              </div>
            </div>
            
            <!-- Gallery Item 2 -->
            <div class="rounded-lg overflow-hidden shadow-sm border border-gray-100 group">
              <div class="relative aspect-square">
                <img 
                  src="https://readdy.ai/api/search-image?query=Beautiful%20young%20Asian%20woman%20with%20long%20dark%20hair%20standing%20on%20a%20tropical%20beach%20with%20crystal%20clear%20blue%20water%20and%20white%20sand%2C%20wearing%20a%20casual%20white%20shirt%2C%20natural%20lighting%20with%20soft%20shadows%2C%20high%20resolution%20photograph&width=400&height=400&seq=2&orientation=squarish" 
                  alt="Gallery image" 
                  class="w-full h-full object-cover"
                />
              </div>
              <div class="p-3 bg-white">
                <div class="flex justify-between items-center">
                  <div class="flex items-center">
                    <i class="fas fa-heart text-red-500 mr-1"></i>
                    <span class="text-xs text-gray-600">1</span>
                  </div>
                  <div class="flex items-center">
                    <i class="fas fa-comment text-gray-400 mr-1"></i>
                    <span class="text-xs text-gray-600">0</span>
                  </div>
                </div>
              </div>
            </div>
            
            <!-- Gallery Item 3 -->
            <div class="rounded-lg overflow-hidden shadow-sm border border-gray-100 group">
              <div class="relative aspect-square">
                <img 
                  src="https://readdy.ai/api/search-image?query=Adorable%20white%20cat%20with%20a%20graduation%20cap%20and%20orange%20monk%20robe%2C%20close%20up%20of%20cute%20cat%20face%20with%20pink%20nose%2C%20golden%20background%20with%20rays%20of%20light%2C%20humorous%20digital%20art%2C%20high%20quality%20render%20with%20soft%20lighting%20and%20detailed%20fur&width=400&height=400&seq=3&orientation=squarish" 
                  alt="Gallery image" 
                  class="w-full h-full object-cover"
                />
              </div>
              <div class="p-3 bg-white">
                <div class="flex justify-between items-center">
                  <div class="flex items-center">
                    <i class="fas fa-heart text-red-500 mr-1"></i>
                    <span class="text-xs text-gray-600">0</span>
                  </div>
                  <div class="flex items-center">
                    <i class="fas fa-comment text-gray-400 mr-1"></i>
                    <span class="text-xs text-gray-600">0</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script lang="ts" setup>
import { ref } from 'vue';

const prompt = ref('');
const seed = ref('809390947');
const style = ref('realistic');
const previewImage = ref('');
const generatedImage = ref('');
const isDragging = ref(false);
const dragStartX = ref(0);
const sliderWidth = ref(0);

const openFileDialog = () => {
  const input = document.createElement('input');
  input.type = 'file';
  input.accept = 'image/*';
  input.onchange = (e) => {
    const file = (e.target as HTMLInputElement).files?.[0];
    if (file) {
      const reader = new FileReader();
      reader.onload = (e) => {
        previewImage.value = e.target?.result as string;
      };
      reader.readAsDataURL(file);
    }
  };
  input.click();
};

const generateImage = () => {
  // Simulate image generation
  const images = [
    'https://readdy.ai/api/search-image?query=Beautiful%20digital%20art%20of%20a%20fantasy%20landscape%20with%20purple%20mountains%2C%20flowing%20waterfalls%20and%20magical%20floating%20islands%20in%20the%20sky%2C%20dreamlike%20atmosphere%20with%20soft%20glowing%20lights%20and%20mist%2C%20highly%20detailed%20environment%20with%20vibrant%20colors%20and%20surreal%20elements&width=600&height=400&seq=4&orientation=landscape',
    'https://readdy.ai/api/search-image?query=Futuristic%20cyberpunk%20city%20at%20night%20with%20neon%20lights%20reflecting%20on%20wet%20streets%2C%20tall%20skyscrapers%20with%20holographic%20advertisements%2C%20flying%20vehicles%20between%20buildings%2C%20atmospheric%20fog%20and%20dramatic%20lighting%2C%20highly%20detailed%20digital%20art%20with%20vibrant%20colors&width=600&height=400&seq=5&orientation=landscape',
    'https://readdy.ai/api/search-image?query=Serene%20Japanese%20garden%20with%20cherry%20blossoms%2C%20traditional%20wooden%20bridge%20over%20calm%20pond%20with%20koi%20fish%2C%20stone%20lanterns%20and%20carefully%20arranged%20rocks%2C%20soft%20morning%20light%20filtering%20through%20trees%2C%20photorealistic%20rendering%20with%20attention%20to%20detail&width=600&height=400&seq=6&orientation=landscape'
  ];
  
  const randomIndex = Math.floor(Math.random() * images.length);
  generatedImage.value = images[randomIndex];
};

const startDrag = (e: MouseEvent) => {
  isDragging.value = true;
  dragStartX.value = e.clientX;
  const slider = e.currentTarget as HTMLElement;
  const sliderRect = slider.parentElement?.getBoundingClientRect();
  sliderWidth.value = sliderRect?.width || 0;
  
  const handleMouseMove = (e: MouseEvent) => {
    if (!isDragging.value) return;
    
    const deltaX = e.clientX - dragStartX.value;
    let newPosition = (parseFloat(slider.style.left) || 40) + (deltaX / sliderWidth.value * 100);
    
    // Constrain to slider bounds
    newPosition = Math.max(0, Math.min(100, newPosition));
    
    slider.style.left = `${newPosition}%`;
    const progressBar = slider.parentElement?.querySelector('div:first-child') as HTMLElement;
    if (progressBar) {
      progressBar.style.width = `${newPosition}%`;
    }
    
    dragStartX.value = e.clientX;
  };
  
  const handleMouseUp = () => {
    isDragging.value = false;
    document.removeEventListener('mousemove', handleMouseMove);
    document.removeEventListener('mouseup', handleMouseUp);
  };
  
  document.addEventListener('mousemove', handleMouseMove);
  document.addEventListener('mouseup', handleMouseUp);
};
</script>

<style scoped>
.container {
  min-height: 1024px;
}

input[type="number"]::-webkit-inner-spin-button,
input[type="number"]::-webkit-outer-spin-button {
  -webkit-appearance: none;
  margin: 0;
}

input[type="number"] {
  -moz-appearance: textfield;
}
</style>

