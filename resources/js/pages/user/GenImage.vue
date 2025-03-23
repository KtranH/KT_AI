<template>
  <div class="min-h-screen bg-gray-100 pt-24" data-aos="zoom-out">
    <div class="max-w-[90%] mx-auto my-4">
        <h1 v-if="error_message != null" class="text-2xl font-bold text-center mb-2 text-red-600 bg-red-100 p-4 rounded-full">{{ error_message }}</h1>
        <div class="min-h-screen bg-gray-50">
            <div class="container mx-auto p-6">
                <!-- Nút quay lại -->
                <div>
                    <button @click="goBack" class="bg-gradient-text hover: text-white font-bold py-2 px-4 rounded-full">Quay lại</button>
                </div>
                <div class="flex items-center justify-left mt-8 mb-8">
                    <h1 v-if="feature" class="text-3xl font-bold bg-gradient-text-v2">{{ feature.title }}</h1>
                    <h1 v-else class="text-3xl font-bold text-gray-800 text-center">Đang tải...</h1>
                    <img :src="icon_title" loading="lazy" class="w-12 h-12 ml-2" alt="">
                </div>
                
                <!-- Phần hướng dẫn sử dụng -->
                <GuideSection 
                  title="Hướng dẫn sử dụng"
                  :guideItems="guideItems"
                  :thumbnailUrl="feature?.thumbnail_url || 'https://balico.com.vn/wp-content/uploads/2020/09/loi-404-tren-cyber-panel.jpg'"
                />
                
                <div class="grid gap-8" :class="feature?.input_requirements === null ? 'grid-cols-1 lg:grid-cols-1' : 'grid-cols-1 lg:grid-cols-2'">
                    <!-- Phần nhập thông tin bên trái -->
                    <div class="bg-white rounded-xl shadow-lg p-6">
                        <h2 class="text-xl font-semibold text-gray-700 mb-6">Thông số hình ảnh</h2>
                        
                        <div class="space-y-6">
                            <!-- Sử dụng ImageParameters component cho phần kích thước -->
                            <ImageParameters 
                              :width="width" 
                              :height="height"
                              @update:width="width = $event"
                              @update:height="height = $event"
                            />
                            
                            <!-- Sử dụng PromptInput component cho phần nhập prompt -->
                            <PromptInput
                              :prompt="prompt"
                              :seed="randomSeed"
                              :style="selectedOption"
                              :options="options"
                              @update:prompt="prompt = $event"
                              @update:seed="randomSeed = $event"
                              @update:style="selectedOption = $event"
                              @generate="generateImage"
                            />
                        </div>
                    </div>
                    
                    <!-- Phần kéo thả/tải ảnh lên bên phải -->
                    <div v-if="feature?.input_requirements != null">
                        <div v-for="(sectionImage, index) in feature.input_requirements" :key="sectionImage" 
                             class="bg-white rounded-xl shadow-lg p-6 flex flex-col" 
                             :class="sectionImage == 2 || feature.input_requirements == 2? 'mt-8' : ''">
                            <!-- Sử dụng ImageUploader component cho phần tải ảnh -->
                            <ImageUploader
                              :title="sectionImage == 1 ? 'Xem trước & Tải lên ảnh chính' : 'Xem trước & Tải lên ảnh phụ'"
                              :imageValue="index === 0 ? mainImage : secondaryImage"
                              @update:image="index === 0 ? mainImage = $event : secondaryImage = $event"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</template>
<script>
import { ref, reactive, onMounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import  GuideSection  from '@/components/user/GenImage/GuideSection.vue'
import  ImageParameters  from '@/components/user/GenImage/ImageParameters.vue'
import  ImageUploader  from '@/components/user/GenImage/ImageUploader.vue'
import  PromptInput  from '@/components/user/GenImage/PromptInput.vue'
import { usefeaturesStore } from '@/stores/user/featuresStore'
import { generateRandomSeed } from '@/utils/index';
import { decodedID } from '@/utils'

export default {
    components: {
        GuideSection,
        ImageParameters,
        PromptInput,
        ImageUploader
    },
    setup() {
        // State
        const randomSeed = ref(generateRandomSeed())
        const icon_title = ref("/img/ai.png")
        const featureStore = usefeaturesStore()
        const feature = computed(() => featureStore.feature)
        const width = ref(512)
        const height = ref(768)
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
        
        // Các state cho phần ảnh
        const mainImage = ref(null)
        const secondaryImage = ref(null)
        const route = useRoute()
        const router = useRouter()
        const decoded_value = ref(null)
        const error_message = ref(null)
        
        // Items cho hướng dẫn
        const guideItems = ref([
            "✍🏻 Bạn có thể nhập thông tin mô tả nếu có vào trong ô Prompt (Mô tả càng chi tiết, ảnh càng chi tiết nhưng tối đa không nên quá 1000 từ).",
            "🎨 Kích thướt ảnh càng lớn tạo ảnh càng lâu. Khuyến nghị là chiều rộng 512px và chiều cao là 768px.",
            "🤹 Bạn có thể chọn thể loại sẽ tạo ảnh nếu có trong ô thể loại.",
            "🖌 Thông số Seed. Nếu bạn dùng chung 1 Seed hình ảnh sẽ giống nhau, nếu không có sẵn Seed. Nhấp vào nút tạo random.",
            "❌ Vui lòng không bỏ trống ô thông tin đầu vào cũng như tải ảnh đầy đủ vào các ô tải ảnh lên.",
            "🚻 Không được nhập các từ ngữ nhạy cảm để tạo ảnh."
        ])

        // Xử lý nút quay lại
        const goBack = () => {
            router.go(-1)
        }

        // Tạo ảnh (giả lập)
        const generateImage = () => {
            // Kiểm tra các điều kiện
            if (!prompt.value) {
                error_message.value = "Vui lòng nhập mô tả để tạo ảnh";
                return;
            }
            
            if (feature.value?.input_requirements && !mainImage.value) {
                error_message.value = "Vui lòng tải lên ảnh chính";
                return;
            }
            
            if (feature.value?.input_requirements === 2 && !secondaryImage.value) {
                error_message.value = "Vui lòng tải lên ảnh phụ";
                return;
            }
            
            // Đoạn này nên gửi dữ liệu lên server, nhưng tạm thời chỉ hiển thị thông báo
            alert(`Sẽ tạo ảnh với:
                - Prompt: ${prompt.value}
                - Kích thước: ${width.value}x${height.value}
                - Seed: ${randomSeed.value}
                - Phong cách: ${selectedOption.value}
            `);
            
            error_message.value = null;
        }

        // Gọi API lấy thông tin
        const get_feature = async () => {
            try {
                featureStore.fetchFeatureDetail(decoded_value.value)
            } catch (error) {
                error_message.value = 'Không thể kết nối đến máy chủ'
            }
        }

        // Mounted Hook
        onMounted(() => {
            const encodedID = route.params.encodedID;
            decoded_value.value = decodedID(encodedID);
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
            mainImage,
            secondaryImage,
            error_message,
            decoded_value,
            icon_title,
            guideItems,
            goBack,
            generateImage
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