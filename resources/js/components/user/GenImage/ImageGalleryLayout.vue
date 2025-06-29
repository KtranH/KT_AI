<template>
  <div class="bg-white rounded-xl shadow-lg p-6 mt-8 container mx-auto">
    <h2 class="text-xl font-semibold text-gray-900 mb-4">Danh sách ảnh người dùng tải lên</h2>
    
    <div ref="masonryContainer" class="masonry-grid">
      <!-- Add image button cell -->
      <div 
        class="masonry-item w-[300px] h-[300px] border-2 border-dashed border-gray-300 rounded-lg flex items-center justify-center cursor-pointer hover:bg-gray-50 transition"
        @click="openFileSelector"
      >
        <div class="text-center">
          <div class="flex justify-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
            </svg>
          </div>
          <p class="mt-2 text-sm text-gray-500">Thêm ảnh</p>
        </div>
        <input 
          type="file" 
          ref="fileInput" 
          multiple 
          accept="image/*" 
          class="hidden" 
          @change="handleFileUpload"
        >
      </div>
      
      <div ref="masonryContainer" class="masonry-grid">
        <!-- Image cells -->
        <div 
          v-for="(image, index) in images" 
          :key="index" 
          class="masonry-item relative border border-gray-200 rounded-lg overflow-hidden group"
        >
        <!-- Phần hiển thị chủ bài viết -->
          <div class="p-2 flex items-center justify-between">
            <div class="flex items-center">
              <img :src="image.user.avatar_url" class="w-8 h-8 rounded-full mx-2" alt="User Avatar">
              <p class="text-sm font-medium text-gray-900 truncate w-40">{{ image.user.name }}</p>
            </div>
            <div class="ml-2 flex gap-2">
              <p class="text-sm font-medium text-gray-900"><i class="fa-solid fa-heart text-red-500"></i> {{ image.sum_like }}</p>
              <p class="text-sm font-medium text-gray-900"><i class="fa-solid fa-comment text-gray-500"></i> {{ image.sum_comment }}</p>
            </div>
          </div> 
          
          <!-- Image carousel - Hiển thị slide ảnh cho mỗi image -->
          <div class="w-full h-full relative">
            <div 
              v-for="(imageUrl, imgIndex) in image.image_url" 
              :key="imgIndex"
              :class="[
                imgIndex === (image.currentSlideIndex || 0) ? 'opacity-100 z-10' : 'opacity-0 z-0',
                image.image_url.length > 1 ? 'absolute inset-0 h-full transition-opacity duration-300 ease-in-out' : ''
              ]"
            >
              <!-- Thêm sự kiện nhấp vào ảnh -->
              <img 
                :src="imageUrl" 
                class="object-cover w-full h-full cursor-pointer"
                @click="goToImageDetail(image.id)"
                @load="onImageLoaded"
                loading="lazy"
              >
            </div>
            
            <!-- Navigation arrows (chỉ hiển thị khi có nhiều ảnh) -->
            <template v-if="image.image_url && image.image_url.length > 1">
              <button 
                class="absolute left-1 top-1/2 -translate-y-1/2 bg-white/70 rounded-full p-1 shadow opacity-0 group-hover:opacity-100 transition z-20"
                @click.stop="navigateImageSlide(index, 'prev')"
                v-show="(image.currentSlideIndex || 0) > 0"
              >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
              </button>
              <button 
                class="absolute right-1 top-1/2 -translate-y-1/2 bg-white/70 rounded-full p-1 shadow opacity-0 group-hover:opacity-100 transition z-20"
                @click.stop="navigateImageSlide(index, 'next')"
                v-show="(image.currentSlideIndex || 0) < image.image_url.length - 1"
              >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
              </button>
            </template>
            
            <!-- Indicator dots nếu có nhiều ảnh -->
            <div 
              v-if="image.image_url && image.image_url.length > 1" 
              class="absolute bottom-1 left-0 right-0 flex justify-center space-x-1 z-20"
            >
              <div 
                v-for="(_, dotIndex) in image.image_url" 
                :key="dotIndex" 
                class="w-1.5 h-1.5 rounded-full transition-colors duration-200"
                :class="dotIndex === (image.currentSlideIndex || 0) ? 'bg-white' : 'bg-white/50'"
              ></div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Load More Button -->
    <div v-if="currentPage < lastPage" class="text-center mt-8">
      <button 
        @click="loadMore" 
        class="px-6 py-2 bg-gradient-text text-white rounded-lg hover:bg-blue-700 transition-colors"
        :disabled="isLoading"
      >
        {{ isLoading ? 'Đang tải...' : 'Xem thêm' }}
      </button>
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

export default {
name: 'ImageGallery',
props: {
  featureId: {
    type: [Number, String, null],
    required: false,
    default: null,
    validator(value) {
      return value === null || value === undefined || !isNaN(Number(value));
    }
  },
  featureName: {
    type: [String, null],
    required: false,
    default: null
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
  
  const featureId = computed(() => {
    if (props.featureId) {
      return Number(props.featureId);
    }
    return null;
  });

  const featureName = computed(() => {
    if (props.featureName) {
      return props.featureName;
    }
    return null;
  });

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

  // Theo dõi featureId để tải lại dữ liệu khi thay đổi
  watch(featureId, async (newId, oldId) => {
    if (newId && newId !== oldId) {
      await fetchImagesByFeature(newId);
    }
    nextTick(() => {
      initMasonry()
    })
  }, { immediate: true });

  // Open file selector
  const openFileSelector = () => {
    router.push({ name: 'upload', query: { featureId: featureId.value, featureName: featureName.value } })
  }

  // Handle file upload
  const handleFileUpload = (event) => {
    const files = event.target.files
    if (!files.length) return
        
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
    if (featureId.value) {
      await loadMoreImages(featureId.value);
    }
  }
  
  return {
    images: imageUrls,
    isLoading,
    currentPage,
    lastPage,
    user,
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