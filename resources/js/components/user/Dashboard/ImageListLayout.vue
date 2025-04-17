<template>
    <div class="bg-white rounded-lg p-6 mt-6 container mx-auto">
      <div class="flex justify-between items-center mb-4">
        <h2 class="text-lg font-semibold text-gray-900">Danh sách ảnh</h2>
        
        <!-- Nút làm mới dữ liệu -->
        <button 
          v-if="showRefreshButton"
          @click="refreshData" 
          class="flex items-center px-3 py-1 bg-blue-500 hover:bg-blue-600 text-white rounded-md transition"
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
                v-if="imageGroup && imageGroup.images && imageGroup.images.length > 0 && imageGroup.images[imageGroup.currentIndex]"
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
                v-if="imageGroup && imageGroup.images && imageGroup.images.length > 1 && imageGroup.currentIndex > 0"
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
                v-if="imageGroup && imageGroup.images && imageGroup.images.length > 1 && imageGroup.currentIndex < imageGroup.images.length - 1"
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
              v-if="imageGroup && imageGroup.images && imageGroup.images.length > 1" 
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
                v-if="imageGroup && imageGroup.images && imageGroup.images.length > 1" 
                class="absolute top-2 right-2 bg-black/60 text-white text-xs px-2 py-1 rounded-full z-20"
              >
                {{ imageGroup.currentIndex + 1 }}/{{ imageGroup.images.length }}
              </div>
              <button 
                v-if="imageGroup && imageGroup.images && imageGroup.images.length > 0 && imageGroup.images[imageGroup.currentIndex]"
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
import { onMounted, ref, watch, nextTick, onUnmounted } from 'vue'
import ImageGalleryLayout from '../GenImage/ImageGalleryLayout.vue'
import useImage from '@/composables/user/useImage'
import useMasonry from '@/composables/user/useMasonry'

export default {
  name: 'ImageList',
  components: {
    ImageGalleryLayout
  },
  props: {
    filter: {
      type: String,
      default: 'created'
    }
  },
  setup(props) {
    const imageGroups = ref([])
    const { masonryContainer, initMasonry, onImageLoaded } = useMasonry()
    const isLoadingMore = ref(false)
    const currentPage = ref(1)
    const hasMoreImages = ref(false)
    const refreshInterval = ref(null)

    // Sử dụng composable cải tiến
    const { 
      imagesCreatedByUser, 
      imagesLikedByUser,
      isLoading,
      hasError,
      fetchImagesCreatedByUser,
      fetchImagesLiked,
      fetchImagesUploaded,
      loadMoreUserImages,
      loadMoreLikedImages,
      loadMoreCreatedImages,
      goToImageDetail,
      hasMoreUserImages,
      hasMoreLikedImages,
      hasMoreCreatedImages,
      lastPage,
      showRefreshButton,
      checkNeedRefreshUserImages: importedCheckNeedRefresh,
    } = useImage()
    
    // Tạo hàm check refresh giả lập nếu không được cung cấp từ composable
    const checkNeedRefreshUserImages = typeof importedCheckNeedRefresh === 'function' 
      ? importedCheckNeedRefresh 
      : () => console.log('Tính năng tự động làm mới không khả dụng')
    
    // Thay đổi currentIndex cho một nhóm hình ảnh
    const setCurrentIndex = (groupIndex, newIndex) => {
      if (imageGroups.value[groupIndex]) {
        imageGroups.value[groupIndex].currentIndex = newIndex
      }
    }
    
    const nextImage = (groupIndex) => {
      if (!imageGroups.value[groupIndex]) return
      
      const group = imageGroups.value[groupIndex]
      if (!group.images) return
      
      if (group.currentIndex < group.images.length - 1) {
        group.currentIndex++
      }
    }
    
    const prevImage = (groupIndex) => {
      if (!imageGroups.value[groupIndex]) return
      
      const group = imageGroups.value[groupIndex]
      if (!group.images) return
      
      if (group.currentIndex > 0) {
        group.currentIndex--
      }
    }
    
    // Hàm chung để xử lý và nhóm hình ảnh
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
        const urls = Array.isArray(imageUrls) ? imageUrls : [imageUrls]
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
    
    // Hàm chung để lấy dữ liệu hình ảnh theo filter
    const fetchImagesByFilter = async (filter) => {
      try {
        let images = []
        
        switch(filter) {
          case 'uploaded':
            await fetchImagesUploaded()
            if (imagesCreatedByUser && typeof imagesCreatedByUser === 'object' && imagesCreatedByUser.value) {
              images = imagesCreatedByUser.value
            }
            break
          case 'created':
            await fetchImagesCreatedByUser()
            if (imagesCreatedByUser && typeof imagesCreatedByUser === 'object' && imagesCreatedByUser.value) {
              images = imagesCreatedByUser.value
            }
            break
          case 'liked':
            await fetchImagesLiked()
            if (imagesLikedByUser && typeof imagesLikedByUser === 'object' && imagesLikedByUser.value) {
              images = imagesLikedByUser.value
            }
            
            // Log để debug
            console.log('Dữ liệu ảnh đã thích:', images)
            break
        }
        
        return images || []
      } catch (error) {
        console.error(`Lỗi khi lấy dữ liệu cho filter "${filter}":`, error)
        return []
      }
    }
    
    // Hàm chung để tải thêm hình ảnh theo filter
    const loadMoreImagesByFilter = async (filter, page) => {
      switch(filter) {
        case 'uploaded':
          return await loadMoreUserImages(page)
        case 'created':
          return await loadMoreCreatedImages(page)
        case 'liked':
          return await loadMoreLikedImages(page)
        default:
          return false
      }
    }
    
    // Kiểm tra xem có thêm hình ảnh để tải không theo filter
    const checkHasMoreImages = (filter) => {
      try {
        switch(filter) {
          case 'uploaded':
            return hasMoreUserImages && typeof hasMoreUserImages === 'object' && hasMoreUserImages.value
          case 'created':
            return hasMoreCreatedImages && typeof hasMoreCreatedImages === 'object' && hasMoreCreatedImages.value
          case 'liked':
            return hasMoreLikedImages && typeof hasMoreLikedImages === 'object' && hasMoreLikedImages.value
          default:
            return false
        }
      } catch (error) {
        console.error(`Lỗi khi kiểm tra có thêm hình ảnh cho filter "${filter}":`, error)
        return false
      }
    }
    
    // Lấy và nhóm hình ảnh theo bộ lọc và ID
    const fetchAndGroupImages = async (isLoadMore = false, forceRefresh = false) => {
      try {
        // Nếu không phải tải thêm, thiết lập lại trang hiện tại
        if (!isLoadMore) {
          currentPage.value = 1
          
          // Tải dữ liệu ban đầu theo filter
          const images = await fetchImagesByFilter(props.filter)
          
          // Xử lý và nhóm hình ảnh
          imageGroups.value = processAndGroupImages(images)
        } else {
          // Khi tải thêm, lấy các ID đã có
          const existingIds = new Set(imageGroups.value.map(group => group.id))
          
          // Lấy dữ liệu hiện tại theo filter
          const currentImages = await fetchImagesByFilter(props.filter)
          
          // Tìm các hình ảnh mới chưa có trong danh sách hiện tại
          const newImages = currentImages.filter(item => !existingIds.has(item.id))
          
          // Xử lý và nhóm các hình ảnh mới
          const newGroups = processAndGroupImages(newImages)
          
          // Thêm vào danh sách hiện có
          imageGroups.value = [...imageGroups.value, ...newGroups]
        }
        
        // Cập nhật trạng thái còn hình ảnh để tải không
        hasMoreImages.value = checkHasMoreImages(props.filter)
      } catch (error) {
        console.error('Lỗi khi tải dữ liệu:', error)
        hasError.value = true
      }
      
      await nextTick()
      initMasonry()
    }
    
    // Làm mới dữ liệu
    const refreshData = async () => {
      await fetchAndGroupImages(false, true)
    }
    
    // Tải thêm ảnh khi người dùng nhấn nút "Tải thêm"
    const loadMore = async () => {
      if (isLoadingMore.value || !hasMoreImages.value) return
      isLoadingMore.value = true
      
      try {
        // Tăng số trang
        currentPage.value++
        
        // Gọi API tải thêm theo filter
        const success = await loadMoreImagesByFilter(props.filter, currentPage.value)
        
        if (success) {
          // Cập nhật giao diện với dữ liệu mới
          await fetchAndGroupImages(true)
        } else {
          console.error('Không thể tải thêm ảnh')
          // Nếu thất bại, giảm lại trang về trạng thái cũ
          currentPage.value--
        }
      } catch (error) {
        console.error('Lỗi khi tải thêm ảnh:', error)
        currentPage.value--
      } finally {
        isLoadingMore.value = false
      }
    }
    
    // Thiết lập công việc định kỳ để kiểm tra cập nhật
    const setupPeriodicCheck = () => {
      // Kiểm tra mỗi phút
      refreshInterval.value = setInterval(() => {
        // Lúc này checkNeedRefreshUserImages luôn là hàm hợp lệ
        checkNeedRefreshUserImages()
      }, 60000)
    }

    watch(() => props.filter, () => {
      fetchAndGroupImages()
    }, { immediate: true })
    
    // Theo dõi sự thay đổi của imageGroups để khởi tạo lại Masonry
    watch(() => imageGroups.value, () => {
      nextTick(() => {
        initMasonry()
      })
    }, { deep: true })
    
    // Theo dõi số lượng nhóm hình ảnh
    watch(() => imageGroups.value.length, () => {
      nextTick(() => {
        initMasonry()
      })
    })
    
    onMounted(() => {
      fetchAndGroupImages()
      setupPeriodicCheck()
    })
    
    onUnmounted(() => {
      // Dọn dẹp interval khi component bị hủy
      if (refreshInterval.value) {
        clearInterval(refreshInterval.value)
      }
    })
    
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
      refreshData
    }
  }
}
</script>
