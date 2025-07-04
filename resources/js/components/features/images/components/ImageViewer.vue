<template>
    <div class="md:w-3/5 bg-black flex items-center justify-center relative h-full">
        <!-- Image Slider -->
        <div class="w-full h-full max-w-full relative overflow-hidden">
            <div class="flex transition-transform duration-300 ease-in-out h-full" :style="{ transform: `translateX(-${currentIndex * 100}%)` }">
                <div v-for="(image, index) in displayImages" :key="index" class="flex items-center justify-center w-full h-full min-w-full flex-shrink-0">
                    <div v-if="isLoading">
                        <div class="w-full h-full flex items-center justify-center">
                            <div class="w-10 h-10 border-t-transparent border-b-transparent border-r-transparent border-l-transparent border-t-white border-r-white border-l-white border-b-white rounded-full animate-spin"></div>
                        </div>
                    </div>
                    <div v-else>
                        <img 
                            :src="image"
                            class="h-full object-cover cursor-pointer" 
                            :alt="altText" 
                            @click="previewImage"
                        />
                    </div>
                </div>
            </div>
            
            <!-- Navigation Arrows (only show if multiple images) -->
            <template v-if="displayImages.length > 1">
                <button 
                    @click="prevImage" 
                    class="absolute left-2 top-1/2 transform -translate-y-1/2 bg-black bg-opacity-50 text-white p-2 rounded-full z-10 hover:bg-opacity-70 transition-all"
                    aria-label="Previous image"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </button>
                <button 
                    @click="nextImage" 
                    class="absolute right-2 top-1/2 transform -translate-y-1/2 bg-black bg-opacity-50 text-white p-2 rounded-full z-10 hover:bg-opacity-70 transition-all"
                    aria-label="Next image"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </button>
                
                <!-- Pagination Indicators -->
                <div class="absolute bottom-4 left-0 right-0 flex justify-center gap-2">
                    <button 
                        v-for="(_, index) in displayImages" 
                        :key="index"
                        @click="setImage(index)"
                        class="w-2 h-2 rounded-full transition-all duration-300"
                        :class="index === currentIndex ? 'bg-white scale-125' : 'bg-gray-400'"
                        :aria-label="`Go to image ${index + 1}`"
                    ></button>
                </div>
            </template>
        </div>
    </div>
</template>

<script>
import { watch, ref, computed, onMounted } from 'vue'
import useImage from '@/composables/features/images/useImage'
import useNavigation from '@/composables/core/useNavigation'

export default {
    name: 'ImageViewer',
    props: {
        imageUrl: {
            type: String,
            default: 'https://images2.thanhnien.vn/528068263637045248/2024/1/25/e093e9cfc9027d6a142358d24d2ee350-65a11ac2af785880-17061562929701875684912.jpg'
        },
        altText: {
            type: String,
            default: 'Fashion outfit'
        },
        imageID: {
            type: [Number, String],
            required: true
        }
    },
    setup(props) {
        const { previewImage } = useNavigation()
        const currentIndex = ref(0)
        
        // Sử dụng composable cải tiến
        const { 
            imageDetail,
            isLoading, 
            hasError, 
            fetchImages, 
            
        } = useImage()
        
        // Tải dữ liệu khi component được khởi tạo
        onMounted(async () => {
            if (props.imageID) {
                await fetchImages(props.imageID)
            }
        })
        
        // Theo dõi sự thay đổi của imageID và tải lại dữ liệu
        watch(() => props.imageID, async (newId, oldId) => {
            if (newId && newId !== oldId) {
                await fetchImages(newId)
            }
        }, { immediate: true })

        const displayImages = computed(() => {       
            if (imageDetail.value && imageDetail.value.length > 0) {
                return imageDetail.value
            } else {
                return [props.imageUrl]
            }
        })
        
        // Navigation functions
        const nextImage = () => {
            if (currentIndex.value < displayImages.value.length - 1) {
                currentIndex.value++
            } else {
                // Loop back to the first image
                currentIndex.value = 0
            }
        }
        
        const prevImage = () => {
            if (currentIndex.value > 0) {
                currentIndex.value--
            } else {
                // Loop to the last image
                currentIndex.value = displayImages.value.length - 1
            }
        }
        
        const setImage = (index) => {
            currentIndex.value = index
        }

        return {
            previewImage,
            imageDetail,
            isLoading,
            hasError,
            currentIndex,
            displayImages,
            nextImage,
            prevImage,
            setImage
        }
    }
}
</script>
