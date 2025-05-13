<template>
  <div class="min-h-screen bg-gray-50">
    <div class="max-w-full mx-auto my-4">
        <h1 v-if="error_message != null" class="text-2xl font-bold text-center mb-2 text-red-600 bg-red-100 p-4 rounded-full">{{ error_message }}</h1>
        <div class="min-h-screen bg-gray-50">
            <div class="container mx-auto p-6">
                <!-- Nút quay lại -->
                <ButtonBackVue customClass="bg-gradient-text hover: text-white font-bold py-2 px-4 rounded-full"/>
                <div class="flex items-center justify-left mt-8 mb-8">
                    <h1 v-if="feature" class="text-3xl font-bold bg-gradient-text-v2">{{ feature.title }}</h1>
                    <h1 v-else class="text-3xl font-bold text-gray-800 text-center">Đang tải...</h1>
                    <img :src="icon_title" loading="lazy" class="w-12 h-12 ml-2" alt="">
                </div>
                
                <div class="grid gap-8 mb-8" :class="feature?.input_requirements === null ? 'grid-cols-1 lg:grid-cols-1' : 'grid-cols-1 lg:grid-cols-2'">
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
                              :isGenerating="isGenerating"
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

                <!-- Phần tiến trình đang xử lý -->
                <div v-if="activeJobs.length > 0" class="bg-white rounded-xl shadow-lg p-6 mb-8">
                    <h2 class="text-xl font-semibold text-gray-700 mb-4">Tiến trình đang xử lý ({{ activeJobs.length }}/5)</h2>
                    
                    <div class="space-y-4">
                        <div v-for="job in activeJobs" :key="job.id" class="border border-gray-200 rounded-lg p-4">
                            <div class="flex justify-between items-center">
                                <div>
                                    <h3 class="font-medium">{{ job.prompt.substring(0, 50) }}{{ job.prompt.length > 50 ? '...' : '' }}</h3>
                                    <p class="text-sm text-gray-600">Kích thước: {{ job.width }}x{{ job.height }}</p>
                                    <p class="text-sm text-gray-600">Trạng thái: 
                                        <span 
                                            :class="{
                                                'text-blue-600': job.status === 'pending',
                                                'text-green-600': job.status === 'processing',
                                            }"
                                        >
                                            {{ job.status === 'pending' ? 'Đang chờ' : 'Đang xử lý' }}
                                            {{ job.status === 'processing' && job.progress ? ` (${job.progress}%)` : '' }}
                                        </span>
                                    </p>
                                    <!-- Thanh tiến độ -->
                                    <div v-if="job.progress && job.progress > 0" class="w-full mt-2 bg-gray-200 rounded-full h-2.5">
                                        <div class="bg-green-600 h-2.5 rounded-full" :style="{ width: `${job.progress}%` }"></div>
                                    </div>
                                </div>
                                <button 
                                    @click="cancelJob(job.id)" 
                                    class="bg-red-100 text-red-600 px-4 py-2 rounded-lg hover:bg-red-200 transition"
                                >
                                    Hủy
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Phần hướng dẫn sử dụng -->
                <GuideSection 
                  title="Hướng dẫn sử dụng"
                  :guideItems="guideItems"
                  :thumbnailUrl="feature?.thumbnail_url || 'https://balico.com.vn/wp-content/uploads/2020/09/loi-404-tren-cyber-panel.jpg'"
                />

                <ImageGalleryLayout 
                    v-if="featureId !== null"
                    :featureId="featureId"
                    :featureName="featureName"
                />
            </div>
        </div>
    </div>
</div>
</template>
<script>
import GuideSection  from '@/components/user/GenImage/GuideSection.vue'
import ImageParameters  from '@/components/user/GenImage/ImageParameters.vue'
import ImageUploader  from '@/components/user/GenImage/ImageUploader.vue'
import PromptInput  from '@/components/user/GenImage/PromptInput.vue'
import ImageGalleryLayout  from '@/components/user/GenImage/ImageGalleryLayout.vue'
import ButtonBackVue from '../../components/common/ButtonBack.vue'
import { ref, onMounted, computed, onBeforeUnmount } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { usefeaturesStore } from '@/stores/user/featuresStore'
import { generateRandomSeed } from '@/utils/index'
import { decodedID } from '@/utils'
import { toast } from 'vue-sonner'
import axios from 'axios'
 
export default {
    components: {
        GuideSection,
        ImageParameters,
        ImageGalleryLayout,
        PromptInput,
        ImageUploader,
        ButtonBackVue
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
        const featureId = ref(0)
        const featureName = ref(null)
        // Các state cho phần ảnh
        const mainImage = ref(null)
        const secondaryImage = ref(null)
        const route = useRoute()
        const router = useRouter()
        const decoded_value = ref(null)
        const error_message = ref(null)
        const isGenerating = ref(false)
        
        // State cho quản lý tiến trình
        const activeJobs = ref([])
        const checkInterval = ref(null)
        
        // Items cho hướng dẫn
        const guideItems = ref([
            "✍🏻 Bạn có thể nhập thông tin mô tả nếu có vào trong ô Prompt (Mô tả càng chi tiết, ảnh càng chi tiết nhưng tối đa không nên quá 1000 từ).",
            "🎨 Kích thướt ảnh càng lớn tạo ảnh càng lâu. Khuyến nghị là chiều rộng 512px và chiều cao là 768px.",
            "🤹 Bạn có thể chọn thể loại sẽ tạo ảnh nếu có trong ô thể loại.",
            "🖌 Thông số Seed. Nếu bạn dùng chung 1 Seed hình ảnh sẽ giống nhau, nếu không có sẵn Seed. Nhấp vào nút tạo random.",
            "❌ Vui lòng không bỏ trống ô thông tin đầu vào cũng như tải ảnh đầy đủ vào các ô tải ảnh lên.",
            "🚻 Không được nhập các từ ngữ nhạy cảm để tạo ảnh."
        ])

        // Lấy danh sách tiến trình đang hoạt động
        const fetchActiveJobs = async () => {
            try {
                const response = await axios.get('/api/image-jobs/active');
                if (response.data.success) {
                    activeJobs.value = response.data.active_jobs;
                }
            } catch (error) {
                console.error('Lỗi khi lấy tiến trình:', error);
            }
        };

        // Hủy tiến trình
        const cancelJob = async (jobId) => {
            try {
                const response = await axios.delete(`/api/image-jobs/${jobId}`);
                if (response.data.success) {
                    toast.success('Đã hủy tiến trình thành công');
                    fetchActiveJobs(); // Cập nhật lại danh sách
                }
            } catch (error) {
                toast.error('Lỗi khi hủy tiến trình');
                console.error('Lỗi khi hủy tiến trình:', error);
            }
        };

        // Tạo ảnh
        const generateImage = async () => {
            // Kiểm tra các điều kiện
            if (!prompt.value) {
                error_message.value = "Vui lòng nhập mô tả để tạo ảnh";
                toast.error("Vui lòng nhập mô tả để tạo ảnh");
                return;
            }
            
            if (feature.value?.input_requirements && !mainImage.value) {
                error_message.value = "Vui lòng tải lên ảnh chính";
                toast.error("Vui lòng tải lên ảnh chính");
                return;
            }
            
            if (feature.value?.input_requirements === 2 && !secondaryImage.value) {
                error_message.value = "Vui lòng tải lên ảnh phụ";
                toast.error("Vui lòng tải lên ảnh phụ");
                return;
            }
            
            // Kiểm tra số lượng tiến trình
            if (activeJobs.value.length >= 5) {
                error_message.value = "Bạn đã đạt đến giới hạn 5 tiến trình cùng lúc. Vui lòng đợi cho đến khi một số tiến trình hoàn thành.";
                toast.error("Đã đạt giới hạn tiến trình");
                return;
            }

            try {
                // Đánh dấu đang tạo ảnh
                isGenerating.value = true;
                error_message.value = null;
                
                // Gửi API tới server
                const formData = new FormData();
                formData.append('prompt', prompt.value);
                formData.append('width', width.value);
                formData.append('height', height.value);
                formData.append('seed', randomSeed.value);
                formData.append('style', selectedOption.value);
                formData.append('feature_id', featureId.value);
                
                if (mainImage.value) {
                    formData.append('main_image', mainImage.value);
                }
                
                if (secondaryImage.value) {
                    formData.append('secondary_image', secondaryImage.value);
                }
                
                const response = await axios.post('/api/image-jobs/create', formData, {
                    headers: {
                        'Content-Type': 'multipart/form-data'
                    }
                });
                
                if (response.data.success) {
                    toast.success('Tiến trình tạo ảnh đã được khởi tạo');
                    
                    // Reset form sau khi tạo thành công
                    prompt.value = '';
                    randomSeed.value = generateRandomSeed();
                    mainImage.value = null;
                    secondaryImage.value = null;
                    
                    // Cập nhật danh sách tiến trình
                    fetchActiveJobs();
                } else {
                    toast.error(response.data.message || 'Lỗi khi tạo ảnh');
                }
            } catch (error) {
                console.error('Lỗi khi tạo ảnh:', error);
                
                // Hiển thị lỗi từ server nếu có
                if (error.response && error.response.data && error.response.data.message) {
                    error_message.value = error.response.data.message;
                    toast.error(error.response.data.message);
                } else {
                    error_message.value = 'Lỗi khi gửi yêu cầu tạo ảnh';
                    toast.error('Lỗi khi gửi yêu cầu tạo ảnh');
                }
            } finally {
                isGenerating.value = false;
            }
        }

        // Gọi API lấy thông tin
        const get_feature = async () => {
            try {
                if (decoded_value.value) {
                    await featureStore.fetchFeatureDetail(decoded_value.value);
                    featureId.value = Number(decoded_value.value);
                } else {
                    if (lastId) {
                        featureId.value = Number(lastId);
                        await featureStore.fetchFeatureDetail(lastId);
                    } else {
                        error_message.value = 'Không tìm thấy thông tin feature';
                    }
                }
                featureName.value = feature.value.title;
            } catch (error) {
                error_message.value = 'Không thể kết nối đến máy chủ';
                console.error('Error fetching feature:', error);
            }
        }

        // Mounted Hook
        onMounted(() => {
            try {
                const encodedID = route.params.encodedID;
                if (encodedID) {
                    decoded_value.value = Number(decodedID(encodedID));
                }
            } catch (error) {
                console.error('Error decoding ID:', error);
            }
            
            get_feature();
            fetchActiveJobs();
            
            // Thiết lập interval kiểm tra trạng thái tiến trình
            checkInterval.value = setInterval(fetchActiveJobs, 3000);
        });
        
        // Xóa interval khi component bị hủy
        onBeforeUnmount(() => {
            if (checkInterval.value) {
                clearInterval(checkInterval.value);
            }
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
            generateImage,
            featureId,
            featureName,
            isGenerating,
            activeJobs,
            cancelJob
        }
    }
}
</script>