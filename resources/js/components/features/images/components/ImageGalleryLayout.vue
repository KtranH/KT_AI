<template>
  <div class="bg-white rounded-xl shadow-lg p-6 mt-8 container mx-auto relative">
    <!-- Overlay Loading -->
    <OverlayLoading 
      :visible="isRefreshing" 
      title="Đang làm mới bộ sưu tập"
      message="Vui lòng chờ trong giây lát..."
    />
    
    <div class="flex items-center justify-between">
      <div class="flex items-center gap-4 mb-4">
        <span class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-gradient-to-tr from-indigo-400 via-purple-400 to-pink-400 shadow-lg">
          <BookImage class="w-6 h-6 text-white" />
        </span>
        <h2 class="text-2xl font-bold bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-500 text-transparent bg-clip-text drop-shadow flex items-center">
          Ảnh người dùng đã tải lên
           <button
             @click="refreshGallery()"
             :disabled="isRefreshing || isLoading"
             class="ml-3 flex items-center justify-center w-9 h-9 rounded-full bg-white/80 hover:bg-white shadow transition-all duration-200 hover:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-300 disabled:opacity-50 disabled:cursor-not-allowed"
             title="Làm mới bộ sưu tập"
           >
             <RefreshCw 
               v-if="!isRefreshing && !isLoading"
               class="w-5 h-5 text-indigo-600 transition-transform duration-200 hover:rotate-90" 
             />
             <div 
               v-else
               class="w-5 h-5 border-2 border-indigo-600 border-t-transparent rounded-full animate-spin"
             ></div>
           </button>
        </h2>
      </div>

      <!-- Arrange by time-->
      <div class="flex items-center gap-4 mb-4">
        <label for="sort-select" class="text-sm font-medium text-gray-700 mr-2">Sắp xếp:</label>
        <div class="relative w-64">
          <button
            type="button"
            class="w-full flex justify-between items-center px-4 py-2 border border-indigo-300 rounded-full bg-white text-gray-800 shadow-md focus:outline-none focus:ring-2 focus:ring-indigo-400 transition-all duration-200 hover:border-indigo-500 hover:shadow-lg"
            @click="isSortOpen = !isSortOpen"
            @blur="isSortOpen = false"
            :class="{'ring-2 ring-indigo-400': isSortOpen}"
            tabindex="0"
          >
            <span>
              <template v-if="sortValue === 'newest'"> <i class="fa-solid fa-clock text-indigo-500"></i> Mới nhất</template>
              <template v-else-if="sortValue === 'oldest'"> <i class="fa-solid fa-clock text-yellow-500"></i> Cũ nhất</template>
              <template v-else-if="sortValue === 'most_liked'"> <i class="fa-solid fa-heart text-pink-500"></i> Được thích nhiều nhất</template>
            </span>
            <svg class="h-5 w-5 text-indigo-400 transition-transform duration-200" :class="{'rotate-180': isSortOpen}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
            </svg>
          </button>
          <transition name="fade">
            <ul
              v-if="isSortOpen"
              class="absolute z-50 mt-2 w-full bg-white border border-indigo-200 rounded-xl shadow-lg py-1"
              @mousedown.prevent
            >
              <li
                v-for="option in sortOptions"
                :key="option.value"
                @click="selectSort(option.value)"
                class="px-4 py-2 cursor-pointer hover:bg-indigo-50 flex items-center gap-2"
                :class="{'bg-indigo-100 font-semibold': sortValue === option.value}"
              >
                <span v-html="option.icon"></span>
                <span>{{ option.label }}</span>
              </li>
            </ul>
          </transition>
        </div>
      </div>
    </div>
    
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

    <!-- Load More Button -->
    <div v-if="currentPage < lastPage" class="text-center mt-8">
      <button 
        @click="loadMore" 
        class="px-6 py-2 bg-gradient-text text-white rounded-lg hover:from-indigo-700 hover:to-purple-700 transition-all duration-200 shadow-md hover:shadow-lg disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2 mx-auto"
        :disabled="isLoading"
      >
        <InlineLoading 
          v-if="isLoading" 
          :visible="isLoading" 
          text="Đang tải..." 
          size="sm" 
          variant="primary"
        />
        <template v-else>
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
          </svg>
          Xem thêm
        </template>
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
import useImage from '@/composables/features/images/useImage'
import useMasonry from '@/composables/features/ui/useMasonry'
import { formatTime } from '@/utils'
import { RefreshCw, BookImage } from 'lucide-vue-next'
import InlineLoading from '@/components/base/feedback/InlineLoading.vue'
import OverlayLoading from '@/components/base/feedback/OverlayLoading.vue'

export default {
name: 'ImageGallery',
components: {
  RefreshCw,
  InlineLoading,
  OverlayLoading,
  BookImage
},
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

  const sortOptions = [
    { 
      value: 'newest', 
      label: 'Mới nhất', 
      icon: `<svg class="inline h-5 w-5 text-indigo-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2" fill="none"/><path d="M12 6v6l4 2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>`
    },
    { 
      value: 'oldest', 
      label: 'Cũ nhất', 
      icon: `<svg class="inline h-5 w-5 text-yellow-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2" fill="none"/><path d="M12 12h4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="M12 7v5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>`
    },
    { 
      value: 'most_liked', 
      label: 'Được thích nhiều nhất', 
      icon: `<svg class="inline h-5 w-5 text-pink-500" fill="currentColor" viewBox="0 0 20 20"><path d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z"/></svg>`
    }
  ]
  
  const sortValue = ref('newest')
  const isSortOpen = ref(false)

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
  const isRefreshing = ref(false)
  
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

  // Debug pagination values
  watch([currentPage, lastPage], ([newCurrentPage, newLastPage]) => {
    console.log('Pagination values in component:', {
      currentPage: newCurrentPage,
      lastPage: newLastPage,
      shouldShowLoadMore: newCurrentPage < newLastPage
    });
  }, { immediate: true });

  // Theo dõi featureId để tải lại dữ liệu khi thay đổi
  watch(featureId, async (newId, oldId) => {
    if (newId && newId !== oldId) {
      await fetchImagesByFeature(newId, 1, sortValue.value);
    }
    nextTick(() => {
      initMasonry()
    })
  }, { immediate: true });

  // Theo dõi sortValue để tải lại dữ liệu khi thay đổi sorting
  watch(sortValue, async (newSort, oldSort) => {
    if (newSort !== oldSort && featureId.value) {
      await fetchImagesByFeature(featureId.value, 1, newSort);
    }
    nextTick(() => {
      initMasonry()
    })
  });

  // Làm mới dữ liệu
  const refreshGallery = async () => {
    isRefreshing.value = true
    try {
      await fetchImagesByFeature(featureId.value, 1, sortValue.value);
      nextTick(() => {
        initMasonry()
      })
    } finally {
      isRefreshing.value = false
    }
  }

  const selectSort = async (value) => {
    sortValue.value = value
    isSortOpen.value = false
    
    // Tải lại dữ liệu với sorting mới
    if (featureId.value) {
      await fetchImagesByFeature(featureId.value, 1, value);
    }
  }

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
      await loadMoreImages(featureId.value, sortValue.value);
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
    masonryContainer,
    sortOptions,
    sortValue,
    isSortOpen,
    selectSort,
    refreshGallery,
    isRefreshing
  }
}
}
</script>