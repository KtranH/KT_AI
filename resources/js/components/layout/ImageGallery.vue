<template>
    <div class="bg-white rounded-lg shadow-sm p-6 mt-6 container mx-auto">
      <h2 class="text-lg font-semibold text-gray-900 mb-4">Danh sách ảnh</h2>
      
      <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
        <!-- Add image button cell -->
        <div 
          class="aspect-square border-2 border-dashed border-gray-300 rounded-lg flex items-center justify-center cursor-pointer hover:bg-gray-50 transition"
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
                @click="openPreview(imageGroup.images, imgIndex)"
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
            :src="previewImages[previewIndex]?.url" 
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
        </div>
      </div>
    </div>
</template>

<script>
import { onMounted, ref } from 'vue'

export default {
  name: 'ImageGallery',
  setup() {
    const imageGroups = ref([
      {
        currentIndex: 0,
        images: [
          { url: "https://picsum.photos/id/237/400/400", id: 1 },
          { url: "https://picsum.photos/id/238/400/400", id: 2 },
          { url: "https://picsum.photos/id/239/400/400", id: 3 }
        ]
      },
      {
        currentIndex: 0,
        images: [
          { url: "https://picsum.photos/id/240/400/400", id: 4 },
          { url: "https://picsum.photos/id/241/400/400", id: 5 }
        ]
      },
      {
        currentIndex: 0,
        images: [
          { url: "https://picsum.photos/id/242/400/400", id: 6 }
        ]
      }
    ])

    const fileInput = ref(null)
    const previewVisible = ref(false)
    const previewImages = ref([])
    const previewIndex = ref(0)

    // Open file selector
    const openFileSelector = () => {
      fileInput.value.click()
    }

    // Handle file upload
    const handleFileUpload = (event) => {
      const files = event.target.files
      if (!files.length) return
      
      // In a real application, you would upload these to your Laravel backend
      // For this demo, we'll create URLs for the selected files
      const newImages = Array.from(files).map(file => ({
        url: URL.createObjectURL(file),
        id: Math.random().toString(36).substring(2, 11)
      }))
      
      // Add as a new image group
      imageGroups.value.push({
        currentIndex: 0,
        images: newImages
      });
      
      // Reset file input to allow selecting the same files again
      event.target.value = ''
    };

    // Navigate between images in a group
    const navigateImages = (groupIndex, direction) => {
      const group = imageGroups.value[groupIndex]
      if (direction === 'next' && group.currentIndex < group.images.length - 1) {
        group.currentIndex++
      } else if (direction === 'prev' && group.currentIndex > 0) {
        group.currentIndex--
      }
    }

    // Open preview mode
    const openPreview = (images, startIndex) => {
      previewImages.value = images
      previewIndex.value = startIndex
      previewVisible.value = true
    }

    // Navigate in preview mode
    const navigatePreview = (direction) => {
      if (direction === 'next' && previewIndex.value < previewImages.value.length - 1) {
        previewIndex.value++
      } else if (direction === 'prev' && previewIndex.value > 0) {
        previewIndex.value--
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
          images: group.images
        }))
      } catch (error) {
        console.error('Error fetching images:', error)
      }
    }
    return{
        imageGroups,
        openFileSelector,
        handleFileUpload,
        navigateImages,
        openPreview,
        navigatePreview,
        previewVisible,
        previewImages,
        previewIndex
    }
  }
}

</script>