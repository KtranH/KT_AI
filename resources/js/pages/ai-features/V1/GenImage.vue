<template>
  <div v-if="isLoading" class="min-h-screen">
    <LoadingState />
  </div>
  
  <div v-else class="min-h-screen bg-gray-50">
    <div class="max-w-full mx-auto my-4">
        <div class="min-h-screen bg-gray-50">
            <div class="container mx-auto p-6">
                <!-- Nút quay lại -->
                <ButtonBack customClass="bg-gradient-text hover: text-white font-bold py-2 px-4 rounded-full"/>

                <div v-if="user" class="flex items-center gap-2 mt-4">
                  <span class="inline-flex items-center px-3 py-1 rounded-full bg-gradient-to-r from-blue-400 to-purple-500 text-white font-semibold shadow">
                    <svg class="w-5 h-5 mr-1 text-yellow-300" fill="currentColor" viewBox="0 0 20 20">
                      <path d="M10 2a1 1 0 01.894.553l1.382 2.8 3.09.45a1 1 0 01.554 1.706l-2.236 2.18.528 3.08a1 1 0 01-1.451 1.054L10 12.347l-2.761 1.456a1 1 0 01-1.451-1.054l.528-3.08-2.236-2.18a1 1 0 01.554-1.706l3.09-.45L9.106 2.553A1 1 0 0110 2z"/>
                    </svg>
                    <span>Lượt tạo ảnh còn lại:</span>
                    <span class="ml-2 font-bold text-yellow-300">{{ user.remaining_credits }}</span>
                  </span>
                </div>

                <div class="flex items-center gap-4 mt-4 mb-4 px-4 py-3 rounded-xl bg-gradient-to-r from-blue-50 via-purple-50 to-pink-50 shadow-md">
                    <div class="flex-shrink-0">
                        <img :src="icon_title" loading="lazy" class="w-14 h-14 rounded-full border-2 border-purple-300 shadow" alt="">
                    </div>
                    <div class="flex flex-col">
                        <h1 v-if="feature" class="text-2xl font-extrabold bg-gradient-text-v2 text-transparent bg-clip-text drop-shadow-lg">
                            {{ feature.title }}
                        </h1>
                        <h1 v-else class="text-3xl font-bold text-gray-500 animate-pulse">Đang tải...</h1>
                    </div>
                </div>
        
                <div
                  class="grid gap-10 mb-12 mt-6"
                  :class="feature?.input_requirements === null ? 'grid-cols-1 lg:grid-cols-1' : 'grid-cols-1 lg:grid-cols-2'"
                >
                  <!-- Phần nhập thông tin bên trái -->
                  <div class="bg-white rounded-2xl shadow-2xl p-8 border border-purple-100">
                    <h2 class="text-2xl font-bold text-gradient mb-8 flex items-center gap-2">
                      <svg class="w-6 h-6 text-purple-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                      </svg>
                      Thông số hình ảnh
                    </h2>
                    <div class="space-y-8">
                      <!-- Sử dụng ImageParameters component cho phần kích thước -->
                      <div class="bg-gradient-to-r from-purple-50 to-blue-50 rounded-xl p-4 shadow-inner">
                        <ImageParameters 
                          :width="width" 
                          :height="height"
                          @update:width="width = $event"
                          @update:height="height = $event"
                        />
                      </div>
                      <!-- Sử dụng PromptInput component cho phần nhập prompt -->
                      <div class="bg-gradient-to-r from-blue-50 to-pink-50 rounded-xl p-4 shadow-inner">
                        <PromptInput
                          :prompt="prompt"
                          :seed="randomSeed"
                          :style="selectedOption"
                          :options="options"
                          :isGenerating="isGenerating"
                          @update:prompt="prompt = $event"
                          @update:seed="randomSeed = $event"
                          @update:style="selectedOption = $event"
                          @generate="handleGenerateImage"
                        />
                      </div>
                    </div>
                  </div>
                  
                  <!-- Phần kéo thả/tải ảnh lên bên phải -->
                  <div
                    v-if="feature?.input_requirements != null"
                    :class="[
                      'gap-8',
                      feature.input_requirements.length === 2
                        ? 'flex flex-row items-stretch justify-between'
                        : 'flex flex-col'
                    ]"
                  >
                    <div
                      v-for="(sectionImage, index) in feature.input_requirements"
                      :key="sectionImage"
                      :class="[
                        'bg-white rounded-2xl shadow-2xl p-8 border border-blue-100 flex flex-col transition-transform hover:scale-[1.02]',
                        feature.input_requirements.length === 2 ? 'w-1/2' : '',
                        feature.input_requirements.length === 2 && index === 0 ? 'mr-4' : '',
                        feature.input_requirements.length === 2 && index === 1 ? 'ml-4' : '',
                        feature.input_requirements.length === 1 ? 'w-full' : ''
                      ]"
                    >
                      <div class="mb-4">
                        <span class="inline-block px-4 py-1 rounded-full bg-gradient-to-r from-blue-400 to-purple-500 text-white font-semibold shadow text-sm">
                          {{ sectionImage == 1 ? 'Ảnh chính' : 'Ảnh phụ' }}
                        </span>
                      </div>
                      <!-- Sử dụng ImageUploader component cho phần tải ảnh -->
                      <ImageUploader
                        :title="sectionImage == 1 ? 'Xem trước & Tải lên ảnh chính' : 'Xem trước & Tải lên ảnh phụ'"
                        :imageValue="index === 0 ? mainImage : secondaryImage"
                        @update:image="index === 0 ? mainImage = $event : secondaryImage = $event"
                      />
                    </div>
                  </div>
                </div>
                
                <!-- Preview ảnh khi tạo thành công -->
                <ImageReview 
                    :successfulJob="successfulJob" 
                    @close="handleClosePreview" 
                />
                
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
                                        </span>
                                    </p>
                                </div>
                                <button 
                                    @click="handleCancelJob(job.id)" 
                                    class="bg-red-100 text-red-600 px-4 py-2 rounded-lg hover:bg-red-200 transition"
                                >
                                    Hủy
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Thông báo lỗi -->
                <div v-if="error_message != null" class="flex items-center justify-center mb-4">
                  <div class="flex items-center gap-3 bg-red-100 border border-red-300 rounded-xl px-6 py-4 shadow text-red-700">
                    <svg class="w-7 h-7 text-red-500 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                      <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2" fill="#fee2e2"/>
                      <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01" />
                    </svg>
                    <span class="text-lg font-semibold">{{ error_message }}</span>
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
import { GuideSection, ImageParameters, ImageUploader, PromptInput, ImageGalleryLayout, ImageReview } from '@/components/features/images'
import { ButtonBack, LoadingState } from '@/components/base'
import { useImageGen } from '@/composables/features/images/useImageGen'
import { ref, onMounted, computed, onBeforeUnmount } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { usefeaturesStore } from '@/stores/user/featuresStore'
import { generateRandomSeed } from '@/utils/index'
import { decodedID } from '@/utils'
import { useAuthStore } from '@/stores/auth/authStore'

export default {
    components: {
        GuideSection,
        ImageParameters,
        ImageGalleryLayout,
        PromptInput,
        ImageUploader,
        ImageReview,
        ButtonBack,
        LoadingState
    },
    setup() {
        // State
        const authStore = useAuthStore()
        const user = ref(authStore.user)
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
        const decoded_value = ref(null)
        const error_message = ref(null)
        const isGenerating = ref(false)
        
        // State cho quản lý tiến trình
        const activeJobs = ref([])
        const checkInterval = ref(null)
        const successfulJob = ref(null)
        // Set để theo dõi các job đã hiển thị thông báo
        const notifiedJobIds = ref(new Set())
        
        // Loading state
        const isLoading = ref(true)
        
        // Items cho hướng dẫn
        const guideItems = ref([
            "✍🏻 Bạn có thể nhập thông tin mô tả nếu có vào trong ô Prompt (Mô tả càng chi tiết, ảnh càng chi tiết nhưng tối đa không nên quá 1000 từ).",
            "🎨 Kích thướt ảnh càng lớn tạo ảnh càng lâu. Khuyến nghị là chiều rộng 512px và chiều cao là 768px.",
            "🤹 Bạn có thể chọn thể loại sẽ tạo ảnh nếu có trong ô thể loại.",
            "🖌 Thông số Seed. Nếu bạn dùng chung 1 Seed hình ảnh sẽ giống nhau, nếu không có sẵn Seed. Nhấp vào nút tạo random.",
            "❌ Vui lòng không bỏ trống ô thông tin đầu vào cũng như tải ảnh đầy đủ vào các ô tải ảnh lên.",
            "🚻 Không được nhập các từ ngữ nhạy cảm để tạo ảnh."
        ])
        
        // Composables
        const { fetchActiveJobs, checkCompletedJobs, generateImage, cancelJob } = useImageGen()
        
        // Function để hủy job
        const handleCancelJob = async (jobId) => {
            await cancelJob(jobId, activeJobs)
        }
        
        // Function để tạo ảnh
        const handleGenerateImage = async () => {
            await generateImage({
                user,
                prompt,
                randomSeed,
                mainImage,
                secondaryImage,
                width,
                height,
                selectedOption,
                featureId,
                error_message,
                isGenerating,
                feature,
                activeJobs
            })
            // Khởi động lại interval nếu chưa có
            if (!checkInterval.value && activeJobs.value.length > 0) {
                startCheckingInterval()
            }
        }

        // Gọi API lấy thông tin
        const get_feature = async () => {
            try {
                if (decoded_value.value) {
                    await featureStore.fetchFeatureDetail(decoded_value.value)
                    featureId.value = Number(decoded_value.value)
                } else {
                    error_message.value = 'Không tìm thấy thông tin feature'
                }
                featureName.value = feature.value.title
            } catch (error) {
                error_message.value = 'Không thể kết nối đến máy chủ'
                console.error('Error fetching feature:', error)
            }
        }

        // Function to handle closing the preview
        const handleClosePreview = () => {
            // Đặt lại giá trị successfulJob
            successfulJob.value = null;
        };

        // Function để giới hạn kích thước của Set notifiedJobIds
        const limitNotifiedJobsSet = () => {
            // Nếu số lượng ID vượt quá 100, xóa các ID cũ
            if (notifiedJobIds.value.size > 100) {
                // Chuyển Set thành Array, lấy 50 phần tử mới nhất, rồi chuyển lại thành Set
                const idsArray = Array.from(notifiedJobIds.value)
                notifiedJobIds.value = new Set(idsArray.slice(-50))
            }
        };

        // Function để khởi động interval kiểm tra
        const startCheckingInterval = () => {
            // Dừng interval cũ nếu có
            if (checkInterval.value) {
                clearInterval(checkInterval.value)
            }
            // Thiết lập interval kiểm tra trạng thái các tiến trình
            checkInterval.value = setInterval(async () => {
                await checkCompletedJobs(successfulJob, notifiedJobIds)
                limitNotifiedJobsSet() // Giới hạn kích thước của Set
                // Cập nhật active jobs
                await fetchActiveJobs(activeJobs)
                // Nếu không còn active jobs, dừng interval
                if (activeJobs.value.length === 0) {
                    clearInterval(checkInterval.value)
                    checkInterval.value = null
                }
            }, 15000)
        };

        // Mounted Hook
        onMounted(async () => {
            try {
                const encodedID = route.params.encodedID
                if (encodedID) {
                    decoded_value.value = Number(decodedID(encodedID))
                }
            } catch (error) {
                console.error('Error decoding ID:', error)
            }
            
            // Hiển thị loading trong 0.5s
            setTimeout(async () => {
                await get_feature()
                await fetchActiveJobs(activeJobs)
                // Luôn kiểm tra completed jobs khi component được mount
                await checkCompletedJobs(successfulJob, notifiedJobIds)
                // Khởi động interval nếu có active jobs
                if (activeJobs.value.length > 0) {
                    startCheckingInterval()
                }
                // Ẩn loading
                isLoading.value = false
            }, 500)
        })
    
        // Xóa interval khi component bị hủy
        onBeforeUnmount(() => {
            if (checkInterval.value) {
                clearInterval(checkInterval.value)
            }
        })
        
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
            handleGenerateImage,
            featureId,
            featureName,
            isGenerating,
            activeJobs,
            handleCancelJob,
            successfulJob,
            handleClosePreview,
            user,
            isLoading
        }
    }
}
</script>