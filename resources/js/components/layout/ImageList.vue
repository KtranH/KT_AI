<template>
    <div class="bg-white rounded-lg shadow-sm p-6 mt-6 container mx-auto">
      <h2 class="text-lg font-semibold text-gray-900 mb-4">Danh sách ảnh</h2>
      
      <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4"> 
        <!-- Image cells -->
        <div 
          v-for="(imageGroup, index) in imageGroups" 
          :key="index" 
          class="relative aspect-square border border-gray-200 rounded-lg overflow-hidden group"
        >
          <!-- Image carousel - Fixed with absolute positioning for all images -->
          <div class="h-full relative">
            <div 
              v-for="(image, imgIndex) in imageGroup.images" 
              :key="imgIndex"
              class="absolute inset-0 w-full h-full transition-opacity duration-300 ease-in-out"
              :class="imgIndex === imageGroup.currentIndex ? 'opacity-100 z-10' : 'opacity-0 z-0'"
            >
              <img 
                :src="image.url" 
                class="object-cover w-full h-full cursor-pointer"
                loading = "lazy"
              >
            </div>
            
            <!-- Navigation arrows (only shown if multiple images) -->
            <template v-if="imageGroup.images.length > 1">
              <button 
                class="absolute left-1 top-1/2 -translate-y-1/2 bg-white/70 rounded-full p-1 shadow opacity-0 group-hover:opacity-100 transition z-20"
                @click.stop="navigateImages(index, 'prev')"
                v-show="imageGroup.currentIndex > 0"
              >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
              </button>
              <button 
                class="absolute right-1 top-1/2 -translate-y-1/2 bg-white/70 rounded-full p-1 shadow opacity-0 group-hover:opacity-100 transition z-20"
                @click.stop="navigateImages(index, 'next')"
                v-show="imageGroup.currentIndex < imageGroup.images.length - 1"
              >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
              </button>
            </template>
            
            <!-- Indicator dots -->
            <div 
              v-if="imageGroup.images.length > 1" 
              class="absolute bottom-1 left-0 right-0 flex justify-center space-x-1 z-20"
            >
              <div 
                v-for="(_, dotIndex) in imageGroup.images" 
                :key="dotIndex" 
                class="w-1.5 h-1.5 rounded-full transition-colors duration-200"
                :class="dotIndex === imageGroup.currentIndex ? 'bg-white' : 'bg-white/50'"
              ></div>
            </div>
          </div>
        </div>
      </div>
    </div>
</template>

<script>
import { onMounted, ref, computed } from 'vue'
import { usefeaturesStore } from '@/stores/features'

export default {
  name: 'ImageList',
  props: {
    filter: {
      type: String,
      default: 'created'
    }
  },
  setup(props) {
    const imageGroups = ref([
      {
        currentIndex: 0,
        type: 'created',
        images: [
          { url: "https://picsum.photos/id/237/400/400", id: 1 },
          { url: "https://picsum.photos/id/238/400/400", id: 2 },
          { url: "https://picsum.photos/id/239/400/400", id: 3 }
        ]
      },
      {
        currentIndex: 0,
        type: 'uploaded',
        images: [
          { url: "https://picsum.photos/id/240/400/400", id: 4 },
          { url: "https://picsum.photos/id/241/400/400", id: 5 }
        ]
      },
      {
        currentIndex: 0,
        type: 'liked',
        images: [
          { url: "https://picsum.photos/id/242/400/400", id: 6 }
        ]
      }
    ])

    // Computed property for filtered images
    const filteredImageGroups = computed(() => {
      return imageGroups.value.filter(group => group.type === props.filter)
    })

    const fileInput = ref(null)
    const previewVisible = ref(false)
    const previewImages = ref([])
    const previewIndex = ref(0)

    // Open file selector
    const openFileSelector = () => {
      fileInput.value.click()
    }

    // Navigate between images in a group
    const navigateImages = (groupIndex, direction) => {
      const group = imageGroups.value[groupIndex]
      if (direction === 'next' && group.currentIndex < group.images.length - 1) {
        group.currentIndex++
      } else if (direction === 'prev' && group.currentIndex > 0) {
        group.currentIndex--
      }
    }
    
    // Example of how you would fetch images from your Laravel backend
    const fetchImages = async () => {
      try {
        const response = await fetch('/api/images')
        const data = await response.json()
        
        // Transform the data to match our component structure
        imageGroups.value = data.map(group => ({
          currentIndex: 0,
          type: group.type,
          images: group.images
        }))
      } catch (error) {
        console.error('Error fetching images:', error)
      }
    }
    return{
        imageGroups: filteredImageGroups,
        fileInput,
        openFileSelector,
        navigateImages,
        previewVisible,
        previewImages,
        previewIndex
    }
  }
}

</script>