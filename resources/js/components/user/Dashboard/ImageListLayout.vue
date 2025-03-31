<template>
    <div class="bg-white rounded-lg p-6 mt-6 container mx-auto">
      <h2 class="text-lg font-semibold text-gray-900 mb-4">Danh sách ảnh</h2>
      
      <div v-if="isLoading" class="text-center py-8">
        <p>Đang tải dữ liệu...</p>
      </div>
      
      <div v-else-if="hasError" class="text-center py-8">
        <p class="text-red-500">Đã xảy ra lỗi khi tải dữ liệu</p>
      </div>
      
      <div v-else-if="imageGroups.length === 0" class="text-center py-8">
        <p>Không có ảnh nào</p>
      </div>
      
      <div v-else ref="masonryContainer" class="masonry-grid"> 
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
    </div>
</template>

<script>
import { onMounted, ref, watch, nextTick } from 'vue'
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

    // Sử dụng composable cải tiến
    const { 
      imagesCreatedByUser, 
      isLoading,
      hasError,
      fetchImagesCreatedByUser,
      goToImageDetail
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
    const fetchAndGroupImages = async () => {
        let allImages = []        
        if (props.filter === 'created') {
            
            // Lấy từ store thông qua composable
            if (imagesCreatedByUser.value && imagesCreatedByUser.value.length > 0) {
                allImages = imagesCreatedByUser.value.flatMap(item => {
                    if (!item || !item.url) return []
                    return Array.isArray(item.url) 
                        ? item.url.map(url => ({ url, id: item.id }))
                        : [{ url: item.url, id: item.id }]
                }).filter(item => item && item.url && item.id)
            } else {
                // Dữ liệu mẫu
                allImages = [
                    { url: "https://picsum.photos/id/237/400/400", id: 1 },
                    { url: "https://picsum.photos/id/238/400/400", id: 1 },
                    { url: "https://picsum.photos/id/239/400/400", id: 1 },
                    { url: "https://picsum.photos/id/240/400/400", id: 2 }
                ]
            }
        } else if (props.filter === 'uploaded') {
            allImages = [
                { url: "https://picsum.photos/id/241/400/400", id: 1 }
            ]
        } else if (props.filter === 'liked') {
            allImages = [
                { url: "https://picsum.photos/id/242/400/400", id: 1 },
                { url: "https://picsum.photos/id/243/400/400", id: 1 },
                { url: "https://picsum.photos/id/244/400/400", id: 2 }
            ]
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

        // Chuyển đổi thành mảng
        imageGroups.value = Object.values(groupedImages)
        
        await nextTick()
        initMasonry()
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
      onImageLoaded
    }
  }
}
</script>
