import { ref, computed } from 'vue'
import { useImageStore } from '@/stores/user/imagesStore'
import { useRouter } from 'vue-router'

export default function useImage() {
    const store = useImageStore()
    const router = useRouter()
    const isLoading = ref(false)
    const hasError = ref(false)
    
    // Computed properties từ store
    const imageUrls = computed(() => {
        // Xử lý trả về dữ liệu từ store.images hoặc store.imagesByFeature
        const images = store.imagesByFeature && store.imagesByFeature.length > 0 
            ? store.imagesByFeature 
            : store.images;
        
        // Đảm bảo format đúng để hiển thị slide ảnh
        return images;
    })
    
    const dataImage = computed(() => store.data)
    const userImage = computed(() => store.currentUser)
    const imagesCreatedByUser = computed(() => store.imagesCreatedByUser)
    const errorMessage = computed(() => store.error_message)
    const isLoadingStore = computed(() => store.isLoading)
    
    // Thông tin phân trang
    const currentPage = computed(() => store.currentPage)
    const lastPage = computed(() => store.lastPage)
    const totalImages = computed(() => store.totalImages)
    
    // Phương thức fetch dữ liệu
    const fetchImages = async (id) => {
        isLoading.value = true
        hasError.value = false
        
        try {
            await store.fetchImages(id)
            if (store.error_message) {
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

    const fetchImagesByFeature = async (id, page = 1) => {
        if (!id) return;
        
        isLoading.value = true
        hasError.value = false
        
        try {
            console.log(`Đang tải ảnh cho feature ${id}, trang ${page}...`);
            const response = await store.fetchImagesByFeature(id, page)
            return response;
        } catch (error) {
            hasError.value = true
            console.error('API Error khi tải ảnh:', error)
        } finally {
            isLoading.value = false
        }
    }

    const loadMoreImages = async (id) => {
        if (currentPage.value < lastPage.value) {
            const nextPage = currentPage.value + 1;
            console.log(`Đang tải thêm ảnh trang ${nextPage}...`);
            return await fetchImagesByFeature(id, nextPage);
        }
    }
    
    return {
        // States
        isLoading,
        hasError,
        isLoadingStore,
        currentPage,
        lastPage,
        totalImages,
        
        // Computed properties
        imageUrls,
        dataImage,
        userImage,
        imagesCreatedByUser,
        errorMessage,
        
        // Methods
        fetchImages,
        fetchImagesCreatedByUser,
        fetchImagesByFeature,
        loadMoreImages
    }
}