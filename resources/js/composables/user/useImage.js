import { ref, computed } from 'vue'
import { useImageStore } from '@/stores/user/imagesStore'
import { useRouter } from 'vue-router'

export default function useImage() {
    const store = useImageStore()
    const router = useRouter()
    const isLoading = ref(false)
    const hasError = ref(false)
    
    // Computed properties
    const imageUrls = computed(() => store.images)
    const dataImage = computed(() => store.data)
    const userImage = computed(() => store.currentUser)
    const imagesCreatedByUser = computed(() => store.imagesCreatedByUser)
    const errorMessage = computed(() => store.error_message)
    const isLoadingStore = computed(() => store.isLoading)
    
    // Phương thức fetch dữ liệu
    const fetchImages = async (id) => {
        isLoading.value = true
        hasError.value = false
        
        try {
            await store.fetchImages(id)
            if (errorMessage.value) {
                router.push('/error/404')
            }
        } catch (error) {
            hasError.value = true
            console.error('API Error:', error)
        } finally {
            isLoading.value = false
        }
    }
    
    const fetchImagesCreatedByUser = async () => {
        isLoading.value = true
        hasError.value = false
        
        try {
            await store.fetchImagesCreatedByUser()
        } catch (error) {
            hasError.value = true
            console.error('API Error:', error)
        } finally {
            isLoading.value = false
        }
    }
    
    return {
        // States
        isLoading,
        hasError,
        isLoadingStore,
        
        // Computed properties
        imageUrls,
        dataImage,
        userImage,
        imagesCreatedByUser,
        errorMessage,
        
        // Methods
        fetchImages,
        fetchImagesCreatedByUser,
    }
}