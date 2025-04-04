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
      isLoading,
      hasError,
      fetchImagesCreatedByUser,
      loadMoreUserImages,
      goToImageDetail,
      hasMoreUserImages,
      totalImages,
      lastPage,
      showRefreshButton,
      checkNeedRefreshUserImages,
      refreshUserImages
    } = useImage()
    
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
    
    // Lấy và nhóm hình ảnh theo bộ lọc và ID
    const fetchAndGroupImages = async (isLoadMore = false, forceRefresh = false) => {
        let allImages = []        
        if (props.filter === 'created') {
            try {
                // Tải ảnh từ API với phân trang
                if (!isLoadMore) {
                    // Tải trang đầu tiên hoặc làm mới dữ liệu
                    await fetchImagesCreatedByUser();
                    
                    currentPage.value = 1;
                    
                    // Lấy toàn bộ dữ liệu từ store
                    if (imagesCreatedByUser && 
                        typeof imagesCreatedByUser === 'object' && 
                        imagesCreatedByUser.value && 
                        Array.isArray(imagesCreatedByUser.value) && 
                        imagesCreatedByUser.value.length > 0) {
                        
                        allImages = imagesCreatedByUser.value.flatMap(item => {
                            if (!item || !item.url) return []
                            // Đảm bảo url là mảng
                            const urls = Array.isArray(item.url) ? item.url : [item.url];
                            return urls.filter(Boolean).map(url => ({ url, id: item.id }))
                        }).filter(item => item && item.url && item.id)
                    } else {
                        // Nếu không có dữ liệu
                        allImages = []
                    }
                    
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
                    
                    // Khởi tạo mảng mới
                    imageGroups.value = Object.values(groupedImages)
                } else {
                    // Khi tải thêm, chỉ lấy các ảnh mới
                    // Theo dõi các ID ảnh đã có
                    const existingIds = new Set(imageGroups.value.map(group => group.id));
                    
                    // Đảm bảo imagesCreatedByUser là mảng hợp lệ với kiểm tra tương tự phía trên
                    let createdImages = [];
                    if (imagesCreatedByUser && 
                        typeof imagesCreatedByUser === 'object' && 
                        imagesCreatedByUser.value && 
                        Array.isArray(imagesCreatedByUser.value)) {
                        createdImages = imagesCreatedByUser.value;
                    }
                    
                    // Chỉ xử lý các ảnh mới được thêm vào từ trang mới nhất
                    const newImages = createdImages
                        .filter(item => !existingIds.has(item.id))
                        .flatMap(item => {
                            if (!item || !item.url) return []
                            // Đảm bảo url là mảng
                            const urls = Array.isArray(item.url) ? item.url : [item.url];
                            return urls.filter(Boolean).map(url => ({ url, id: item.id }))
                        }).filter(item => item && item.url && item.id);
                    
                    // Nhóm các ảnh mới theo ID
                    const newGroupedImages = {}
                    newImages.forEach(image => {
                        if (!image || !image.id || !image.url) return
                        
                        if (!newGroupedImages[image.id]) {
                            newGroupedImages[image.id] = {
                                id: image.id,
                                currentIndex: 0,
                                images: []
                            }
                        }
                        newGroupedImages[image.id].images.push(image)
                    })
                    
                    // Thêm các nhóm mới vào mảng hiện có
                    const newGroups = Object.values(newGroupedImages)
                    imageGroups.value = [...imageGroups.value, ...newGroups]
                }
                
                // Kiểm tra xem còn ảnh để tải không
                hasMoreImages.value = hasMoreUserImages && typeof hasMoreUserImages === 'object' ? 
                    (hasMoreUserImages.value || false) : 
                    (lastPage && currentPage.value < lastPage.value);
            } catch (error) {
                console.error('Lỗi khi tải dữ liệu:', error);
                hasError.value = true;
            }
        } else if (props.filter === 'uploaded') {
            allImages = [
                { url: "https://picsum.photos/id/241/400/400", id: 1 }
            ]
            
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
            
            imageGroups.value = Object.values(groupedImages)
        } else if (props.filter === 'liked') {
            allImages = [
                { url: "https://picsum.photos/id/242/400/400", id: 1 },
                { url: "https://picsum.photos/id/243/400/400", id: 1 },
                { url: "https://picsum.photos/id/244/400/400", id: 2 }
            ]
            
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
            
            imageGroups.value = Object.values(groupedImages)
        }
        
        await nextTick()
        initMasonry()
    }
    
    // Làm mới dữ liệu
    const refreshData = async () => {
        if (props.filter === 'created') {
            await fetchAndGroupImages(false, true);
        }
    }
    
    // Tải thêm ảnh khi người dùng nhấn nút "Tải thêm"
    const loadMore = async () => {
        if (isLoadingMore.value || !hasMoreImages.value) return
        
        isLoadingMore.value = true
        try {
            if (props.filter === 'created') {
                // Tăng số trang và tải dữ liệu mới
                currentPage.value++
                
                // Gọi phương thức mới để tải thêm ảnh người dùng
                const success = await loadMoreUserImages(currentPage.value)
                
                if (success) {
                    // Cập nhật giao diện với dữ liệu mới
                    await fetchAndGroupImages(true)
                } else {
                    console.error('Không thể tải thêm ảnh')
                    // Nếu thất bại, giảm lại trang về trạng thái cũ
                    currentPage.value--
                }
            }
        } catch (error) {
            console.error('Lỗi khi tải thêm ảnh:', error)
            // Nếu có lỗi, giảm lại trang về trạng thái cũ
            currentPage.value--
        } finally {
            isLoadingMore.value = false
        }
    }
    
    // Thiết lập công việc định kỳ để kiểm tra cập nhật
    const setupPeriodicCheck = () => {
        refreshInterval.value = setInterval(() => {
            if (props.filter === 'created') {
                checkNeedRefreshUserImages();
            }
        }, 60000); // Kiểm tra mỗi phút
    }

    watch(() => props.filter, () => {
      fetchAndGroupImages()
    }, { immediate: true })
    
    // Theo dõi sự thay đổi của imageGroups để khởi tạo lại Masonry
    watch(() => imageGroups.value, () => {
      nextTick(() => {
        initMasonry()
      })
    }, { deep: true });
    
    // Theo dõi số lượng nhóm hình ảnh
    watch(() => imageGroups.value.length, () => {
      nextTick(() => {
        initMasonry()
      })
    });
    
    onMounted(() => {
      // Fetch data first, then init masonry when data is available
      if (props.filter === 'created' && (!imagesCreatedByUser.value || imagesCreatedByUser.value.length === 0)) {
        fetchImagesCreatedByUser().then(() => {
          fetchAndGroupImages()
        }).catch(error => {
          console.error('Lỗi khi tải hình ảnh:', error)
        })
      } else {
        fetchAndGroupImages()
      }
      
      // Thiết lập kiểm tra định kỳ
      setupPeriodicCheck();
    })
    
    onUnmounted(() => {
        // Dọn dẹp interval khi component bị hủy
        if (refreshInterval.value) {
            clearInterval(refreshInterval.value);
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
