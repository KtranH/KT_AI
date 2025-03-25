import { ref, computed } from 'vue'
import { useImageStore } from '@/stores/user/imagesStore'
import { useRouter } from 'vue-router'
import { encodedID } from '@/utils'

export default function useImage() {
    const store = useImageStore()
    const router = useRouter()
    const isLoading = ref(false)
    const hasError = ref(false)
    
    // Computed properties từ store
    const imageDetail = computed(() => store.images)
    const imageUrls = computed(() => {
        const images = store.imagesByFeature && store.imagesByFeature.length > 0 
            ? store.imagesByFeature 
            : null;
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
        if (!id) {
            console.error('ID không hợp lệ:', id)
            hasError.value = true
            return
        }

        isLoading.value = true
        hasError.value = false
        console.log('Đang tải dữ liệu cho ID:', id)
        
        try {
            await store.fetchImages(id)
            if (store.error_message) {
                console.error('Lỗi từ API:', store.error_message)
                hasError.value = true
                router.push('/error/404')
            }
        } catch (error) {
            hasError.value = true
            console.error('API Error:', error)
        } finally {
            isLoading.value = false
        }
    }

    // Điều hướng đến trang chi tiết hình ảnh
    const goToImageDetail = (id) => {
        if (!id) {
          router.push('/error/404')
          console.error('Không tìm thấy ID hình ảnh')
          return
        }
        console.log('Going to image detail:', id)
        router.push(`/image/detail/${encodedID(id)}`)
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
        imageDetail,
        
        // Methods
        fetchImages,
        fetchImagesCreatedByUser,
        fetchImagesByFeature,
        loadMoreImages,
        goToImageDetail
    }
}