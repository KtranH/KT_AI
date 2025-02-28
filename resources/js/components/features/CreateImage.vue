<template>
  <div class="min-h-screen bg-gray-100 pt-24" data-aos = "zoom-out">
    <div class="max-w-[90%] mx-auto my-4">
        <h1 v-if="error_message != null" class="text-2xl font-bold text-center mb-2 text-red-600 bg-red-100 p-4 rounded-full">Đã có lỗi xảy ra</h1>
        <div class="min-h-screen bg-gray-50">
            <div class="container mx-auto p-6">
                <!-- Nút quay lại -->
                <div>
                    <button @click="goBack" class="bg-gradient-text hover: text-white font-bold py-2 px-4 rounded-full">Quay lại</button>
                </div>
                <div class="flex items-center justify-center mb-8">
                    <h1 v-if="feature" class="text-3xl font-bold bg-gradient-text-v2 text-center">{{ feature.title }}</h1>
                    <h1 v-else class="text-3xl font-bold text-gray-800 text-center">Đang tải...</h1>
                    <img :src="icon_title" loading = "lazy" class="w-12 h-12 ml-2" alt="">
                </div>
                <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
                    <h1 class="text-xl font-semibold text-gray-700 mb-6">Hướng dẫn sử dụng</h1>
                    <div class="flex flex-col lg:flex-row items-center justify-between mt-[-10px]">
                        <ul class="list-disc list-inside text-gray-600">
                            <li class="mb-2">✍🏻 Bạn có thể nhập thông tin mô tả nếu có vào trong ô Prompt (Mô tả càng chi tiết, ảnh càng chi tiết nhưng tối đa không nên quá 1000 từ).</li>
                            <li class="mb-2">🎨 Kích thướt ảnh càng lớn tạo ảnh càng lâu. Khuyến nghị là chiều rộng 512px và chiều cao là 768px.</li>
                            <li class="mb-2">🤹 Bạn có thể chọn thể loại sẽ tạo ảnh nếu có trong ô thể loại.</li>
                            <li class="mb-2">🖌 Thông số Seed. Nếu bạn dùng chung 1 Seed hình ảnh sẽ giống nhau, nếu không có sẵn Seed. Nhấp vào nút tạo random. </li>
                            <li class="mb-2">❌ Vui lòng không bỏ trống ô thông tin đầu vào.</li>
                            <li class="mb-2">🚻 Không được nhập các từ ngữ nhạy cảm để tạo ảnh.</li>
                        </ul>
                        <img :src="feature?.thumbnail_url ? feature.thumbnail_url : 'https://balico.com.vn/wp-content/uploads/2020/09/loi-404-tren-cyber-panel.jpg'" loading = "lazy" alt="" class="w-64 h-64 ml-4 rounded-lg shadow-md">
                    </div>
                </div>
                <div class="grid gap-8" :class="feature?.input_requirements === null ? 'grid-cols-1 lg:grid-cols-1' : 'grid-cols-1 lg:grid-cols-2'">
                    <!-- Phần nhập thông tin bên trái -->
                    <div class="bg-white rounded-xl shadow-lg p-6">
                    <h2 class="text-xl font-semibold text-gray-700 mb-6">Thông số hình ảnh</h2>
                    
                    <div class="space-y-6">
                        <!-- Chiều dài và rộng với slider -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Kích thước hình ảnh</label>
                            
                            <div class="grid grid-cols-2 gap-4 mb-2">
                                <div>
                                <label class="block text-sm text-gray-500 mb-1">Chiều rộng: {{ width }}px</label>
                                <input
                                    type="range"
                                    v-model="width"
                                    min="512"
                                    max="1024"
                                    step="64"
                                    class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-indigo-600"
                                />
                                </div>
                                
                                <div>
                                <label class="block text-sm text-gray-500 mb-1">Chiều dài: {{ height }}px</label>
                                <input
                                    type="range"
                                    v-model="height"
                                    min="512"
                                    max="1024"
                                    step="64"
                                    class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-indigo-600"
                                />
                                </div>
                            </div>
                            
                            <div class="flex items-center justify-between mt-2">
                                <span class="text-xs text-gray-500">512px</span>
                                <span class="text-xs text-gray-500">1024px</span>
                            </div>
                            </div>
                            
                            <!-- Prompt -->
                            <div>
                            <label for="prompt" class="block text-sm font-medium text-gray-700 mb-2">Prompt</label>
                            <textarea
                                id="prompt"
                                v-model="prompt"
                                rows="4"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                placeholder="Nhập mô tả chi tiết cho hình ảnh..."
                            ></textarea>
                            </div>

                            <!-- Seed -->
                            <div>
                            <label for="seed" class="block text-sm font-medium text-gray-700 mb-2">Seed</label>
                            <div class="flex items-center justify-between">
                                <input
                                    id="seed"
                                    type="text"
                                    class="w-[85%] px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                    placeholder="Nhập seed hoặc tạo ngẫu nhiên"
                                    :value="randomSeed"
                                >
                                <button @click="random" class="bg-gradient-text font-medium text-sm text-white py-2 px-4 rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    Random
                                </button>
                            </div>
                            </div>
                            
                            <!-- Lựa chọn (combobox) -->
                            <div>
                                <label for="option" class="block text-sm font-medium text-gray-700 mb-2">Phong cách</label>
                                <div class="relative">
                                    <select
                                    id="option"
                                    v-model="selectedOption"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg appearance-none focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white"
                                    >
                                    <option v-for="option in options" :key="option.value" :value="option.value">
                                        {{ option.label }}
                                    </option>
                                    </select>
                                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Nút tạo ảnh -->
                            <button
                            class="w-full bg-gradient-text text-white py-3 px-4 rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 font-medium transition duration-150"
                            >
                            Tạo hình ảnh
                            </button>
                        </div>
                    </div>
                    
                    <!-- Phần kéo thả/tải ảnh lên bên phải -->
                    <div v-if="feature?.input_requirements != null">
                        <div v-for="sectionImage in feature.input_requirements" class="bg-white rounded-xl shadow-lg p-6 flex flex-col">
                            <h2 class="text-xl font-semibold text-gray-700 mb-6">Xem trước & Tải lên</h2>   
                            <div
                                class="flex-1 border-2 border-dashed border-gray-300 rounded-lg flex flex-col items-center justify-center p-6 transition-all duration-150"
                                :class="{ 'border-indigo-400 bg-indigo-50': isDragging }"
                                @dragover.prevent="isDragging = true"
                                @dragleave.prevent="isDragging = false"
                                @drop.prevent="onDrop"
                            >
                                <div v-if="imagePreview" class="w-full h-full flex items-center justify-center">
                                    <img :src="imagePreview" alt="Preview" class="max-w-full max-h-full rounded-lg shadow-md" />
                                </div>
                                    <div v-else class="text-center">
                                        <svg class="mx-auto h-16 w-16 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                        
                                        <h3 class="mt-2 text-sm font-medium text-gray-700">
                                            Kéo thả hình ảnh vào đây
                                        </h3>
                                        <p class="mt-1 text-xs text-gray-500">PNG, JPG hoặc GIF (tối đa 2MB)</p>
                                        <div class="mt-6">
                                            <label
                                            for="file-upload"
                                            class="cursor-pointer inline-flex items-center px-4 py-2 border border-indigo-500 rounded-md text-sm font-medium text-indigo-600 bg-white hover:bg-indigo-50"
                                            >
                                            <span>Chọn hình ảnh</span>
                                            <input id="file-upload" type="file" class="sr-only" accept="image/*" @change="onFileChange" />
                                            </label>
                                        </div>
                                    </div>
                                </div>     
                                <div v-if="imagePreview" class="flex justify-between mt-4">
                                    <button
                                    class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                    @click="clearImage"
                                    >
                                    <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                    Xóa
                                    </button>
                                    
                                    <!--<button
                                    class="inline-flex items-center px-3 py-2 border border-transparent shadow-sm text-sm leading-4 font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                    @click="downloadImage"
                                    >
                                    <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                    </svg>
                                    Tải xuống
                                    </button>-->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
  </div>
</template>
<script>
import { ref, onMounted } from 'vue'
import { RouterLink, useRoute, useRouter } from 'vue-router'
export default {
    setup() {
        const randomSeed = ref(Math.floor(Math.random() * 1000000000))
        const icon_title = ref("/img/ai.png")
        const feature = ref(null)
        const width = ref(512)
        const height = ref(512)
        const prompt = ref('')
        const selectedOption = ref('realistic')
        const options = ref([
        { value: 'realistic', label: 'Chân thực' },
        { value: 'cartoon', label: 'Phim hoạt hình' },
        { value: 'sketch', label: 'Phác họa' },
        { value: 'anime', label: 'Anime' },
        { value: 'watercolor', label: 'Màu nước' },
        { value: 'oil-painting', label: 'Sơn dầu' },
        { value: 'digital-art', label: 'Nghệ thuật số' },
        { value: 'abstract', label: 'Trừu tượng' }
        ])
        // Các state cho phần kéo thả ảnh
        const imagePreview = ref(null)
        const isDragging = ref(false)
        const route = useRoute()
        const router = useRouter()
        const decodedID = ref(null)
        const error_message = ref(null)

        const decodeID = (encodedID) => {
            return atob(encodedID);
        };

        // Xử lý sự kiện kéo thả
        const onDrop = (event) => {
            isDragging.value = false
            const files = event.dataTransfer.files
            
            if (files.length) {
                handleFile(files[0])
            }
        }

        // Xử lý khi chọn file
        const onFileChange = (event) => {
            const file = event.target.files[0]
            if (file) {
                handleFile(file)
            }
        }

        // Xử lý file
        const handleFile = (file) => {
            if (file.type.match('image.*')) {
                if(file.size <= 2 * 1024 * 1024) {
                    const reader = new FileReader()
                    reader.onload = (e) => {
                        imagePreview.value = e.target.result
                    }
                    reader.readAsDataURL(file)
                }
                else
                {
                    alert('Vui lòng chỉ tải lên hình ảnh dưới 2MB')
                }
            }
            else
            {
                alert('Vui lòng chỉ tải lên hình ảnh')
            }
        }

        //Xử lý nút quay lại
        const goBack = () => {
            router.go(-1)
        }

        // Xử lý nút random seed
        const random = () => {
            randomSeed.value = Math.floor(Math.random() * 1000000000)
        }
        // Xóa ảnh
        const clearImage = () => {
        imagePreview.value = null
        };

        // Tải ảnh xuống
        /*const downloadImage = () => {
        if (imagePreview.value) {
            const a = document.createElement('a')
            a.href = imagePreview.value
            a.download = 'image.png'
            document.body.appendChild(a)
            a.click()
            document.body.removeChild(a)
        }
        }*/

        //Gọi API lấy thông tin
        const get_feature = async () => {
            try {
                const response = await axios.get(`/api/load_features/${decodedID.value}`,
                    {
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    }
                )
                if(response.data.success) {
                    feature.value = response.data.data
                }
                else
                {
                    error_message.value = response.data.message
                }
            } catch (error) {
                error_message.value = 'Không thể kết nối đến máy chủ'
            }
        }

        onMounted(() => {
            const encodedID = route.params.encodedID;
            decodedID.value = decodeID(encodedID);
            get_feature();
        });
        return {
            randomSeed,
            feature,
            width,
            height,
            prompt,
            selectedOption,
            options,
            imagePreview,
            isDragging,
            onDrop,
            onFileChange,
            clearImage,
            error_message,
            decodedID,
            icon_title,
            goBack,
            random
        }
    }
}
</script>
<style scoped>
    /* Gradient text effect */
    .bg-gradient-text-v2 {
        background: linear-gradient(
            -45deg,
            #3b82f6,
            #6366f1,
            #8b5cf6,
            #ec4899,
            #3b82f6
        );
        background-size: 400%;
        animation: gradient-animation 8s ease infinite;
        
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }
    .transition-all {
        transition-property: all;
        transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
        transition-duration: 150ms;
    }
    /* Gradient background animation */
    .bg-gradient-text {
        background: linear-gradient(
            -45deg,
            #3b82f6,
            #6366f1,
            #8b5cf6,
            #ec4899,
            #3b82f6
        );
        background-size: 400%;
        animation: gradient-animation 8s ease infinite;
    }

    @keyframes gradient-animation {
        0% { background-position: 0% 50%; }
        50% { background-position: 100% 50%; }
        100% { background-position: 0% 50%; }
    }
</style>