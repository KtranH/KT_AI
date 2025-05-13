<template>
    <div class="bg-white rounded-xl shadow-lg p-6 mt-8 container mx-auto">
      <h2 class="text-xl font-semibold text-gray-700 mb-6">{{ featureName }} - Thư viện hình ảnh của bạn</h2>
      
      <div v-if="isLoading" class="flex justify-center items-center py-10">
        <svg class="animate-spin h-10 w-10 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        <p class="ml-3 text-gray-600">Đang tải thư viện...</p>
      </div>
      
      <div v-else-if="completedJobs.length === 0" class="text-center py-10">
        <svg class="w-16 h-16 mx-auto text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
        </svg>
        <p class="mt-4 text-gray-600">Bạn chưa có hình ảnh nào trong thư viện</p>
        <p class="text-gray-500">Hãy tạo một vài hình ảnh để xem chúng ở đây!</p>
      </div>
      
      <div v-else class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
        <div v-for="job in completedJobs" :key="job.id" class="group">
          <div class="relative rounded-lg overflow-hidden shadow-md transition-all hover:shadow-xl">
            <img 
              :src="job.result_image_url" 
              :alt="job.prompt.substring(0, 30)" 
              class="w-full h-48 object-cover transition-transform group-hover:scale-105"
              loading="lazy"
            />
            <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/30 to-transparent opacity-0 group-hover:opacity-100 transition-opacity flex flex-col justify-end p-4">
              <p class="text-white text-sm line-clamp-2">{{ job.prompt }}</p>
              <div class="flex justify-between mt-2">
                <span class="text-xs text-gray-300">{{ formattedDate(job.created_at) }}</span>
                <div class="flex space-x-2">
                  <button 
                    @click="downloadImage(job.result_image_url, `image_${job.id}`)" 
                    class="text-white hover:text-blue-300 transition"
                    title="Tải xuống"
                  >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Phân trang nếu cần -->
      <div v-if="completedJobs.length > 0 && totalPages > 1" class="flex justify-center mt-8">
        <div class="flex space-x-2">
          <button 
            v-for="page in totalPages" 
            :key="page" 
            @click="currentPage = page" 
            :class="[
              'px-3 py-1 rounded-md',
              currentPage === page 
                ? 'bg-blue-500 text-white' 
                : 'bg-gray-200 text-gray-700 hover:bg-gray-300'
            ]"
          >
            {{ page }}
          </button>
        </div>
      </div>
    </div>
    
    <!-- Full-screen preview modal -->
    <div 
      v-if="previewVisible" 
      class="fixed inset-0 bg-black/90 z-50 flex items-center justify-center"
      @click="previewVisible = false"
    >
      <div class="relative max-w-4xl max-h-screen p-4">
        <button 
          class="absolute top-4 right-4 text-white z-10"
          @click.stop="previewVisible = false"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
        
        <div class="relative">
          <img 
            :src="previewImages[previewIndex]" 
            loading="lazy"
            class="max-h-screen max-w-full object-contain"
          >
          
          <!-- Preview navigation arrows -->
          <button 
            v-if="previewImages.length > 1" 
            class="absolute left-4 top-1/2 -translate-y-1/2 bg-white/20 rounded-full p-2 hover:bg-white/30 transition"
            @click.stop="navigatePreview('prev')"
            v-show="previewIndex > 0"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
          </button>
          <button 
            v-if="previewImages.length > 1" 
            class="absolute right-4 top-1/2 -translate-y-1/2 bg-white/20 rounded-full p-2 hover:bg-white/30 transition"
            @click.stop="navigatePreview('next')"
            v-show="previewIndex < previewImages.length - 1"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
          </button>

          <!-- Thông tin ảnh -->
          <div v-if="currentPreviewImage" class="absolute bottom-0 left-0 right-0 p-4 bg-black/50 text-white">
            <div class="flex justify-between items-center">
              <div>
                <p v-if="currentPreviewImage.prompt" class="text-sm font-medium mb-1">{{ currentPreviewImage.prompt }}</p>
                <p class="text-xs opacity-80">{{ formatTime(currentPreviewImage.created_at) }}</p>
              </div>
              <div class="flex items-center space-x-4">
                <span class="flex items-center">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                  </svg>
                  {{ currentPreviewImage.sum_like || 0 }}
                </span>
                <span class="flex items-center">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                  </svg>
                  {{ currentPreviewImage.sum_comment || 0 }}
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
</template>

<script>
import { ref, computed, watch, nextTick } from 'vue'
import { useRouter } from 'vue-router'
import useImage from '@/composables/user/useImage'
import useMasonry from '@/composables/user/useMasonry'
import { formatTime } from '@/utils'
import axios from 'axios'
import { format } from 'date-fns'
import { vi } from 'date-fns/locale'

export default {
  name: 'ImageGallery',
  props: {
    featureId: {
      type: Number,
      required: true
    },
    featureName: {
      type: String,
      default: 'Chức năng'
    }
  },
  setup(props) {
    const { 
      fetchImagesByFeature, 
      loadMoreImages, 
      imageUrls, 
      isLoading, 
      currentPage, 
      lastPage ,
      user,
      goToImageDetail
    } = useImage()

    const { masonryContainer, initMasonry, onImageLoaded } = useMasonry()
    
    const completedJobs = ref([])
    const totalItems = ref(0)
    const itemsPerPage = ref(12)
    
    const fetchCompletedJobs = async () => {
      try {
        isLoading.value = true
        const response = await axios.get('/api/image-jobs/completed', {
          params: {
            feature_id: props.featureId,
            page: currentPage.value,
            per_page: itemsPerPage.value
          }
        })
        
        if (response.data.success) {
          completedJobs.value = response.data.completed_jobs
          totalItems.value = response.data.count
        }
      } catch (error) {
        console.error('Lỗi khi lấy danh sách hình ảnh:', error)
      } finally {
        isLoading.value = false
      }
    }
    
    const totalPages = computed(() => {
      return Math.ceil(totalItems.value / itemsPerPage.value)
    })
    
    const formattedDate = (dateString) => {
      try {
        return format(new Date(dateString), 'dd MMM yyyy, HH:mm', { locale: vi })
      } catch (error) {
        return dateString
      }
    }
    
    const downloadImage = (url, filename) => {
      fetch(url)
        .then(response => response.blob())
        .then(blob => {
          const link = document.createElement('a')
          link.href = URL.createObjectURL(blob)
          link.download = `${filename}.png`
          document.body.appendChild(link)
          link.click()
          document.body.removeChild(link)
        })
        .catch(error => console.error('Lỗi khi tải xuống hình ảnh:', error))
    }
    
    // Thiết lập interval để cập nhật gallery
    const updateInterval = ref(null)
    
    onMounted(() => {
      fetchCompletedJobs()
      
      // Cập nhật gallery mỗi 10 giây
      updateInterval.value = setInterval(fetchCompletedJobs, 10000)
    })
    
    // Hủy interval khi component unmount
    onBeforeUnmount(() => {
      if (updateInterval.value) {
        clearInterval(updateInterval.value)
      }
    })
    
    // Theo dõi thay đổi trang và tải lại dữ liệu
    watch(currentPage, () => {
      fetchCompletedJobs()
    })
    
    const router = useRouter()
    const previewVisible = ref(false)
    const previewImages = ref([])
    const previewIndex = ref(0)
    const currentPreviewImage = ref(null)
    
    // Theo dõi imageUrls để cập nhật slide cho mỗi ảnh
    watch(imageUrls, (newImages) => {
      if (newImages && newImages.length) {
        newImages.forEach(image => {
          if (!image.hasOwnProperty('currentSlideIndex')) {
            image.currentSlideIndex = 0;
          }
        });
      }
      nextTick(() => {
        initMasonry()
      })
    }, { immediate: true });

    // Open file selector
    const openFileSelector = () => {
      router.push({ name: 'upload', query: { featureId: props.featureId, featureName: props.featureName } })
    }

    // Handle file upload
    const handleFileUpload = (event) => {
      const files = event.target.files
      if (!files.length) return
      
      // Xử lý upload ảnh đến API (sẽ implement sau)
      console.log('Tải ảnh lên:', files);
      
      // Reset file input để cho phép chọn lại các file tương tự
      event.target.value = ''
    }

    // Điều hướng giữa các slide của một ảnh
    const navigateImageSlide = (imageIndex, direction) => {
      const image = imageUrls.value[imageIndex];
      if (!image || !image.image_url || !image.image_url.length) return;
      
      if (!image.currentSlideIndex) {
        image.currentSlideIndex = 0;
      }
      
      if (direction === 'next' && image.currentSlideIndex < image.image_url.length - 1) {
        image.currentSlideIndex++;
      } else if (direction === 'prev' && image.currentSlideIndex > 0) {
        image.currentSlideIndex--;
      }
    }

    // Open preview mode
    const openPreview = (images, startIndex, imageData) => {
      previewImages.value = images;
      previewIndex.value = startIndex || 0;
      previewVisible.value = true;
      currentPreviewImage.value = imageData;
    }

    // Navigate in preview mode
    const navigatePreview = (direction) => {
      if (direction === 'next' && previewIndex.value < previewImages.value.length - 1) {
        previewIndex.value++
      } else if (direction === 'prev' && previewIndex.value > 0) {
        previewIndex.value--
      }
    }

    // Load more images
    const loadMore = async () => {
      if (props.featureId) {
        await loadMoreImages(props.featureId);
      }
    }
    
    return {
      completedJobs,
      isLoading,
      currentPage,
      totalPages,
      formattedDate,
      downloadImage,
      images: imageUrls,
      openFileSelector,
      handleFileUpload,
      openPreview,
      navigatePreview,
      navigateImageSlide,
      previewVisible,
      previewImages,
      previewIndex,
      currentPreviewImage,
      loadMore,
      formatTime,
      goToImageDetail,
      onImageLoaded,
      masonryContainer
    }
  }
}
</script>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
