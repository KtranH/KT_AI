import { ref, computed } from 'vue'
import { useImageStore } from '@/stores/user/imagesStore'
import { useRouter } from 'vue-router'
import { encodedID } from '@/utils'
import { imageAPI } from '@/services/api'
import { toast } from 'vue-sonner'

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

    const hasMoreUserImages = computed(() => {
        return currentPage.value < lastPage.value;
    });

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
            console.log(`Đang tải thêm ảnh cho feature ${id}, trang ${nextPage}...`);
            return await fetchImagesByFeature(id, nextPage);
        }
    }

    // Phương thức mới để tải thêm ảnh người dùng đã tạo
    const loadMoreUserImages = async (page) => {
        if (page) {
            console.log(`Đang tải thêm ảnh người dùng trang ${page}...`);
            isLoading.value = true;
            hasError.value = false;

            try {
                // Tạo URL API với tham số phân trang
                const apiUrl = `/get_images_created_by_user?page=${page}`;
                // Dùng store để tải dữ liệu theo trang
                await store.fetchImagesCreatedByUserPage(apiUrl, page);
                return true;
            } catch (error) {
                console.error("Lỗi khi tải thêm ảnh người dùng:", error);
                hasError.value = true;
                return false;
            } finally {
                isLoading.value = false;
            }
        }
    }

    // Phương thức xóa bài viết
    const deleteImage = async (imageId) => {
        try {
            isLoading.value = true
            const response = await imageAPI.delete(imageId)

            if (response.data.success) {
                toast.success('Xóa bài viết thành công!')
                // Chuyển hướng về trang chủ hoặc trang profile
                router.push('/dashboard')
                return true
            } else {
                toast.error(response.data.message || 'Không thể xóa bài viết')
                return false
            }
        } catch (error) {
            console.error('Lỗi khi xóa bài viết:', error)
            toast.error('Không thể xóa bài viết. Vui lòng thử lại sau.')
            return false
        } finally {
            isLoading.value = false
        }
    }

    // Phương thức sửa bài viết
    const updateImage = async (imageId, data) => {
        try {
            isLoading.value = true
            const response = await imageAPI.update(imageId, data)

            if (response.data.success) {
                toast.success('Cập nhật bài viết thành công!')
                // Cập nhật dữ liệu trong store
                if (store.data) {
                    store.data.title = data.title
                    store.data.prompt = data.prompt
                }
                return true
            } else {
                toast.error(response.data.message || 'Không thể cập nhật bài viết')
                return false
            }
        } catch (error) {
            console.error('Lỗi khi cập nhật bài viết:', error)
            toast.error('Không thể cập nhật bài viết. Vui lòng thử lại sau.')
            return false
        } finally {
            isLoading.value = false
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
        hasMoreUserImages,

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
        goToImageDetail,
        loadMoreUserImages,
        deleteImage,
        updateImage
    }
}