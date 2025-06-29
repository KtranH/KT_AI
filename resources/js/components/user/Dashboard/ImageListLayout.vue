<template>
    <div class="bg-white rounded-lg p-6 mt-6 container mx-auto">
      <div class="flex justify-between items-center mb-4">
        <h2 class="text-lg font-semibold text-gray-900">Danh sách ảnh</h2>
        
        <!-- Nút làm mới dữ liệu -->
        <button 
          v-if="showRefreshButton"
          @click="refreshData" 
          class="flex items-center px-3 py-1 bg-gradient-text rounded-2xl hover:bg-blue-600 text-white transition"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd" />
          </svg>
          <span>Làm mới</span>
        </button>
      </div>
      
      <div v-if="isLoading && !isLoadingMore" class="text-center py-8">
        <p>Đang tải dữ liệu...</p>
      </div>
      
      <div v-else-if="hasError" class="text-center py-8">
        <p class="text-red-500">Đã xảy ra lỗi khi tải dữ liệu</p>
      </div>
      
      <div v-else-if="imageGroups.length === 0" class="text-center py-8">
        <p>Không có ảnh nào</p>
      </div>
      
      <div v-else>
        <div ref="masonryContainer" class="masonry-grid"> 
          <!-- Image cells -->
          <div 
            v-for="(imageGroup, index) in imageGroups" 
            :key="index" 
            class="masonry-item relative border border-gray-200 rounded-lg overflow-hidden"
          >
            <!-- Image display -->
            <div class="h-full w-full">
              <img 
                v-if="hasValidImage(imageGroup)"
                :src="imageGroup.images[imageGroup.currentIndex].url" 
                class="object-cover w-full h-full cursor-pointer"
                loading="lazy"
                @click="goToImageDetail(imageGroup.images[imageGroup.currentIndex].id)"
                @load="onImageLoaded"
                alt="Image"
              >
              <div v-else class="w-full h-full bg-gray-200 flex items-center justify-center">
                <span class="text-gray-500">Hình ảnh không khả dụng</span>
              </div>
            </div>
            <!-- Navigation controls -->
            <div class="absolute inset-0 flex items-center justify-between">
              <!-- Previous button -->
              <button
                v-if="canNavigatePrev(imageGroup)"
                @click.stop="prevImage(index)"
                class="h-full flex items-center px-1 z-30"
              >
                <div class="bg-white/70 rounded-full p-2 shadow hover:bg-white/90 transition cursor-pointer">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                  </svg>
                </div>
              </button>
              
              <!-- Empty middle space to allow image clicking -->
              <div class="flex-grow h-full"></div>
              
              <!-- Next button -->
              <button
                v-if="canNavigateNext(imageGroup)"
                @click.stop="nextImage(index)"
                class="h-full flex items-center px-1 z-30"
              >
                <div class="bg-white/70 rounded-full p-2 shadow hover:bg-white/90 transition cursor-pointer">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                  </svg>
                </div>
              </button>
            </div>
            
            <!-- Indicator dots -->
            <div 
              v-if="hasMultipleImages(imageGroup)" 
              class="absolute bottom-2 left-0 right-0 flex justify-center space-x-2 z-20"
            >
              <button
                v-for="(_, dotIndex) in imageGroup.images" 
                :key="dotIndex" 
                class="w-2 h-2 rounded-full transition-colors duration-200 hover:scale-125"
                :class="dotIndex === imageGroup.currentIndex ? 'bg-white' : 'bg-white/50'"
                @click.stop="setCurrentIndex(index, dotIndex)"
              ></button>
            </div>
            
            <!-- Image count badge -->
            <div class="flex items-center justify-between">
              <div 
                v-if="hasMultipleImages(imageGroup)" 
                class="absolute top-2 right-2 bg-black/60 text-white text-xs px-2 py-1 rounded-full z-20"
              >
                {{ imageGroup.currentIndex + 1 }}/{{ imageGroup.images.length }}
              </div>
              <button 
                v-if="hasValidImage(imageGroup)"
                class="absolute top-2 left-2 bg-black/60 text-white text-xs px-2 py-1 rounded-full z-20 cursor-pointer hover:bg-black/30 transition" 
                @click="goToImageDetail(imageGroup.images[imageGroup.currentIndex].id)"
              >
                Xem chi tiết
              </button>
            </div>
          </div>
        </div>
        
        <!-- Load more button -->
        <div v-if="hasMoreImages" class="flex justify-center mt-8">
          <button 
            @click="loadMore" 
            class="px-4 py-2 bg-gradient-text text-white rounded-md hover:bg-indigo-700 transition"
            :disabled="isLoadingMore"
          >
            <span v-if="isLoadingMore">Đang tải...</span>
            <span v-else>Tải thêm ảnh</span>
          </button>
        </div>
      </div>
    </div>
</template>

<script>
import { onMounted, ref, watch, nextTick, onUnmounted, computed } from 'vue'
import { useRoute } from 'vue-router'
import ImageGalleryLayout from '../GenImage/ImageGalleryLayout.vue'
import useImage from '@/composables/user/useImage'
import useMasonry from '@/composables/user/useMasonry'
import { useImageStore } from '@/stores/user/imagesStore'

export default {
  name: 'ImageList',
  components: {
    ImageGalleryLayout
  },
  props: {
    filter: {
      type: String,
      default: 'uploaded'
    },
    userId: {
      type: [String, Number],
      default: null
    }
  },
  setup(props) {
    const imageGroups = ref([])
    const route = useRoute()
    const { masonryContainer, initMasonry, onImageLoaded } = useMasonry()
    const isLoadingMore = ref(false)
    const hasMoreImages = ref(false)
    const refreshInterval = ref(null)
    const imageStore = useImageStore()

    // Lấy userId từ props hoặc route
    const userId = computed(() => {
      if (props.userId) {
        return parseInt(props.userId) || null;
      }
      
      const routeUserId = route.params.id || route.query.userId;
      return routeUserId ? (parseInt(routeUserId) || null) : null;
    });
    
    // Sử dụng composable
    const { 
      imagesLikedByUser,
      imagesUploadedByUser,
      isLoading,
      hasError,
      fetchImagesLiked,
      fetchImagesUploaded,
      loadMoreLikedImages,
      loadMoreUploadedImages,
      goToImageDetail,
      showRefreshButton,
      checkNeedRefreshUserImages: importedCheckNeedRefresh,
    } = useImage()
    
    // Helper để kiểm tra refresh
    const checkNeedRefreshUserImages = typeof importedCheckNeedRefresh === 'function' 
      ? importedCheckNeedRefresh 
      : () => {}
    
    // Helper functions for image navigation and validation
    const hasValidImage = (imageGroup) => {
      return imageGroup && 
             imageGroup.images && 
             imageGroup.images.length > 0 && 
             imageGroup.images[imageGroup.currentIndex];
    }
    
    const hasMultipleImages = (imageGroup) => {
      return imageGroup && 
             imageGroup.images && 
             imageGroup.images.length > 1;
    }
    
    const canNavigatePrev = (imageGroup) => {
      return hasMultipleImages(imageGroup) && imageGroup.currentIndex > 0;
    }
    
    const canNavigateNext = (imageGroup) => {
      return hasMultipleImages(imageGroup) && 
             imageGroup.currentIndex < imageGroup.images.length - 1;
    }
    
    // Thay đổi currentIndex cho một nhóm hình ảnh
    const setCurrentIndex = (groupIndex, newIndex) => {
      if (imageGroups.value[groupIndex]) {
        imageGroups.value[groupIndex].currentIndex = newIndex
      }
    }
    
    const nextImage = (groupIndex) => {
      const group = imageGroups.value[groupIndex];
      if (group?.images && group.currentIndex < group.images.length - 1) {
        group.currentIndex++;
      }
    }
    
    const prevImage = (groupIndex) => {
      const group = imageGroups.value[groupIndex];
      if (group?.images && group.currentIndex > 0) {
        group.currentIndex--;
      }
    }
    
    // Xử lý và nhóm hình ảnh
    const processAndGroupImages = (images) => {
      if (!images || !Array.isArray(images) || images.length === 0) {
        return []
      }
      
      const allImages = images.flatMap(item => {
        if (!item) return []
        
        // Xử lý trường hợp image_url hoặc url
        const imageUrls = item.image_url || item.url
        if (!imageUrls) return []
        
        // Đảm bảo url là mảng
        let urls = []
        
        if (Array.isArray(imageUrls)) {
          urls = imageUrls
        } else if (typeof imageUrls === 'string') {
          try {
            const parsed = JSON.parse(imageUrls)
            urls = Array.isArray(parsed) ? parsed : [imageUrls]
          } catch (e) {
            urls = [imageUrls]
          }
        }
        
        return urls.filter(Boolean).map(url => ({ url, id: item.id }))
      }).filter(item => item && item.url && item.id)
      
      // Nhóm hình ảnh theo ID
      const groupedImages = {}
      allImages.forEach(image => {
        if (!image || !image.id || !image.url) return
        
        if (!groupedImages[image.id]) {
          groupedImages[image.id] = {
            id: image.id,
            currentIndex: 0,
            images: []
          }
        }
        groupedImages[image.id].images.push(image)
      })
      
      return Object.values(groupedImages)
    }
    
    // Lấy dữ liệu hình ảnh theo filter
    const fetchImagesByFilter = async (filter, targetUserId) => {
      try {
        const userId = parseInt(targetUserId) || null;
        
        let images = [];
        switch(filter) {
          case 'uploaded':
            await fetchImagesUploaded(userId)
            images = imagesUploadedByUser?.value || []
            break
          case 'liked':
            await fetchImagesLiked(userId)
            images = imagesLikedByUser?.value || []
            break
        }
        
        return images
      } catch (error) {
        return []
      }
    }
    
    // Tải thêm hình ảnh theo filter
    const loadMoreImagesByFilter = async (filter, page) => {
      try {
        switch(filter) {
          case 'uploaded':
            return await loadMoreUploadedImages(userId.value, page)
          case 'liked':
            return typeof loadMoreLikedImages === 'function' 
              ? await loadMoreLikedImages(userId.value, page)
              : false
          default:
            return false
        }
      } catch {
        return false
      }
    }
    
    // Kiểm tra có thêm hình ảnh không
    const checkHasMoreImages = (filter) => {
      try {
        // Sử dụng pagination từ store
        const canLoadMore = (imageStore.currentPage || 1) < (imageStore.lastPage || 1)
        return canLoadMore
      } catch {
        return false
      }
    }
    
    // Lấy và nhóm hình ảnh
    const fetchAndGroupImages = async (isLoadMore = false, forceRefresh = false, refreshUserId = null) => {
      try {
        const targetUserId = refreshUserId !== undefined ? refreshUserId : userId.value;
        
        if (!isLoadMore) {
          // Kiểm tra cache trước khi fetch
          if (!forceRefresh && imageStore.hasCachedDashboardData(targetUserId, props.filter)) {
            const cached = imageStore.getDashboardCache(targetUserId, props.filter);
            if (cached) {
              imageGroups.value = processAndGroupImages(cached.data);
              imageStore.currentPage = cached.page;
              imageStore.lastPage = cached.lastPage;
              imageStore.totalImages = cached.total;
              hasMoreImages.value = checkHasMoreImages(props.filter);
              
              await nextTick();
              initMasonry();
              return; // Không cần fetch mới
            }
          }
          
          // Reset store pagination khi không phải load more
          imageStore.currentPage = 1
          imageStore.lastPage = 1
          imageStore.totalImages = 0
          
          if (forceRefresh) {
            imageGroups.value = []
          }
          
          // Clear imageGroups trước khi fetch để tránh hiển thị data cũ
          imageGroups.value = []
          
          const images = await fetchImagesByFilter(props.filter, targetUserId)
          const newGroups = processAndGroupImages(images);
          
          // Luôn cập nhật imageGroups khi không phải load more (filter change hoặc initial load)
          imageGroups.value = newGroups;
          
          // Lưu vào cache
          imageStore.setDashboardCache(
            targetUserId, 
            props.filter, 
            images, 
            imageStore.currentPage, 
            imageStore.lastPage, 
            imageStore.totalImages
          );
        } else {
          let newImagesData = []
          
          switch(props.filter) {
            case 'uploaded':
              if (imagesUploadedByUser?.value) {
                const currentIds = new Set(imageGroups.value.map(group => group.id))
                newImagesData = imagesUploadedByUser.value.filter(img => !currentIds.has(img.id))
              }
              break
            case 'liked':
              if (imagesLikedByUser?.value) {
                const currentIds = new Set(imageGroups.value.map(group => group.id))
                newImagesData = imagesLikedByUser.value.filter(img => !currentIds.has(img.id))
              }
              break
          }
          
          const newGroups = processAndGroupImages(newImagesData)
          imageGroups.value = [...imageGroups.value, ...newGroups]
          
          // Cập nhật cache cho load more
          const allData = props.filter === 'uploaded' 
            ? imagesUploadedByUser?.value || []
            : imagesLikedByUser?.value || [];
          
          imageStore.setDashboardCache(
            userId.value,
            props.filter,
            allData,
            imageStore.currentPage,
            imageStore.lastPage,
            imageStore.totalImages
          );
        }
        
        hasMoreImages.value = checkHasMoreImages(props.filter)
      } catch (error) {
        hasError.value = true
      }
      
      await nextTick()
      initMasonry()
    }
    
    // Làm mới dữ liệu
    const refreshData = async () => {
      imageStore.clearAllUserImages()
      imageStore.clearDashboardCache(userId.value, props.filter)
      imageGroups.value = []
      await fetchAndGroupImages(false, true, userId.value)
    }
    
    // Tải thêm ảnh
    const loadMore = async () => {
      if (isLoadingMore.value || !hasMoreImages.value) return
      isLoadingMore.value = true
      
      try {
        // Sử dụng currentPage từ store thay vì currentPage cục bộ
        const nextPage = (imageStore.currentPage || 1) + 1
        const success = await loadMoreImagesByFilter(props.filter, nextPage)
        
        if (success) {
          await fetchAndGroupImages(true, false, userId.value)
        }
      } catch (error) {
        console.error('Lỗi khi tải thêm:', error)
      } finally {
        isLoadingMore.value = false
      }
    }
    
    // Thiết lập kiểm tra định kỳ
    const setupPeriodicCheck = () => {
      refreshInterval.value = setInterval(() => {
        if (userId.value) {
          checkNeedRefreshUserImages(userId.value);
        }
      }, 60000);
    }
    
    // Tải dữ liệu ban đầu
    const loadInitialData = async () => {
      // Không clear cache khi load initial data
      await fetchAndGroupImages(false, false, userId.value);
    };
    
    // Watchers
    watch(() => props.filter, (newFilter, oldFilter) => {
      if (newFilter !== oldFilter) {
        // Không clear cache khi chỉ thay đổi filter, để có thể switch nhanh giữa uploaded/liked
        fetchAndGroupImages(false, false, userId.value);
      }
    }, { immediate: true });
    
    watch(() => userId.value, (newVal, oldVal) => {
      if (newVal !== oldVal) {
        imageStore.clearAllUserImages();
        imageStore.clearDashboardCache(); // Clear all cache khi đổi user
        imageGroups.value = [];
        fetchAndGroupImages(false, true, newVal);
      }
    });
    
    watch(() => imageGroups.value, () => {
      nextTick(() => initMasonry());
    }, { deep: true });
    
    watch(() => imageGroups.value.length, () => {
      nextTick(() => initMasonry());
    });
    
    // Lifecycle hooks
    onMounted(() => {
      loadInitialData();
      setupPeriodicCheck();

      // Thêm event listener cho sự kiện refresh trang (F5)
      const handleBeforeUnload = () => {
        imageStore.clearAllUserImages();
      };
      
      window.addEventListener('beforeunload', handleBeforeUnload);
      
      // Cleanup khi unmount
      onUnmounted(() => {
        if (refreshInterval.value) {
          clearInterval(refreshInterval.value);
        }
        
        // Xóa event listener khi unmount
        window.removeEventListener('beforeunload', handleBeforeUnload);
        
        // Xóa dữ liệu khi component bị hủy
        imageStore.clearAllUserImages();
      });
    });
    
    return {
      imageGroups,
      setCurrentIndex,
      nextImage,
      prevImage,
      goToImageDetail,
      isLoading,
      hasError,
      masonryContainer,
      onImageLoaded,
      loadMore,
      isLoadingMore,
      hasMoreImages,
      showRefreshButton,
      refreshData,
      hasValidImage,
      hasMultipleImages,
      canNavigatePrev,
      canNavigateNext
    }
  }
}
</script>
