<template>
    <div class="bg-white rounded-lg shadow-sm p-6 mt-6 container mx-auto">
      <h2 class="text-lg font-semibold text-gray-900 mb-4">Danh sách ảnh</h2>
      
      <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4"> 
        <!-- Image cells -->
        <div 
          v-for="(imageGroup, index) in imageGroups" 
          :key="index" 
          class="relative aspect-square border border-gray-200 rounded-lg overflow-hidden"
        >
          <!-- Image display -->
          <div class="h-full w-full">
            <img 
              :src="imageGroup.images[imageGroup.currentIndex].url" 
              class="object-cover w-full h-full cursor-pointer"
              loading="lazy"
              @click="goToImageDetail(imageGroup.images[imageGroup.currentIndex].id)"
            >
          </div>
          <!-- Navigation controls -->
          <div class="absolute inset-0 flex items-center justify-between">
            <!-- Previous button -->
            <button
              v-if="imageGroup.images.length > 1 && imageGroup.currentIndex > 0"
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
              v-if="imageGroup.images.length > 1 && imageGroup.currentIndex < imageGroup.images.length - 1"
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
            v-if="imageGroup.images.length > 1" 
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
              v-if="imageGroup.images.length > 1" 
              class="absolute top-2 right-2 bg-black/60 text-white text-xs px-2 py-1 rounded-full z-20"
            >
              {{ imageGroup.currentIndex + 1 }}/{{ imageGroup.images.length }}
            </div>
            <button class="absolute top-2 left-2 bg-black/60 text-white text-xs px-2 py-1 rounded-full z-20 cursor-pointer hover:bg-black/30 transition" @click="goToImageDetail(imageGroup.images[imageGroup.currentIndex].id)">Xem chi tiết</button>
          </div>
        </div>
      </div>
    </div>
</template>

<script>
import { onMounted, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import ImageGalleryLayout from '../GenImage/ImageGalleryLayout.vue'
import useImage from '@/composables/user/useImage'

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
    const router = useRouter()
    const route = useRoute()
    const imageGroups = ref([])

    // Sử dụng composable cải tiến
    const { 
      imagesCreatedByUser, 
      isLoading,
      hasError,
      fetchImagesCreatedByUser 
    } = useImage()
    
    // Thay đổi currentIndex cho một nhóm hình ảnh
    const setCurrentIndex = (groupIndex, newIndex) => {
      if (imageGroups.value[groupIndex]) {
        imageGroups.value[groupIndex].currentIndex = newIndex
      }
    }
    
    const nextImage = (groupIndex) => {
      if (imageGroups.value[groupIndex]) {
        const group = imageGroups.value[groupIndex]
        if (group.currentIndex < group.images.length - 1) {
          group.currentIndex++
        }
      }
    }
    
    const prevImage = (groupIndex) => {
      if (imageGroups.value[groupIndex]) {
        const group = imageGroups.value[groupIndex]
        if (group.currentIndex > 0) {
          group.currentIndex--
        }
      }
    }
    
    // Điều hướng đến trang chi tiết hình ảnh
    const goToImageDetail = (id) => {
      console.log('Going to image detail:', id)
      const encodedID = btoa(id)
      router.push(`/image/detail/${encodedID}`)
    }
    

    // Lấy và nhóm hình ảnh theo bộ lọc và ID
    const fetchAndGroupImages = () => {
        let allImages = []        
        if (props.filter === 'created') {
            // Lấy từ store thông qua composable
            if (imagesCreatedByUser.value && imagesCreatedByUser.value.length > 0) {
                allImages = imagesCreatedByUser.value.flatMap(item =>
                    item.url.map(url => ({ url, id: item.id }))
                )
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
    }


    watch(() => props.filter, () => {
      fetchAndGroupImages()
    }, { immediate: true })
    
    onMounted(() => {
      if (props.filter === 'created' && imagesCreatedByUser.value.length === 0) {
        fetchImagesCreatedByUser().then(() => {
          fetchAndGroupImages()
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
      goToImageDetail
    }
  }
}
</script>