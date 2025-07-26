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
    const imagesLikedByUser = computed(() => store.imagesLikedByUser)
    const imagesUploadedByUser = computed(() => store.imagesUploadedByUser)
    const errorMessage = computed(() => store.error_message)
    const isLoadingStore = computed(() => store.isLoading)

    // Thông tin phân trang
    const currentPage = computed(() => store.currentPage)
    const lastPage = computed(() => store.lastPage)
    const totalImages = computed(() => store.totalImages)

    const hasMoreUserImages = computed(() => {
        return currentPage.value < lastPage.value;
    });

    const hasMoreUploadedImages = computed(() => {
        return currentPage.value < lastPage.value;
    });

    const hasMoreLikedImages = computed(() => {
        return currentPage.value < lastPage.value;
    });

    const hasMoreCreatedImages = computed(() => {
        return currentPage.value < lastPage.value;
    });

    // Biến kiểm soát nút làm mới
    const showRefreshButton = ref(true);

    // Phương thức fetch dữ liệu
    const fetchImages = async (id) => {
        if (!id) {
            console.error('ID không hợp lệ:', id)
            hasError.value = true
            return
        }

        isLoading.value = true
        hasError.value = false

        try {
            await store.fetchImages(id)
            if (store.error_message) {
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
          return
        }
        router.push(`/image/detail/${encodedID(id)}`)
    }

    // Phương thức fetch dữ liệu ảnh đã tải lên
    const fetchImagesUploaded = async (userId) => {
        isLoading.value = true
        hasError.value = false
        try {
            await store.fetchImagesUploaded(userId)
        } catch (error) {
            hasError.value = true
            console.error('API Error:', error)
        } finally {
            isLoading.value = false
        }
    }
    
    // Phương thức fetch dữ liệu ảnh đã thích
    const fetchImagesLiked = async (userId) => {
        isLoading.value = true
        hasError.value = false
        try {
            await store.fetchImagesLiked(userId)
        } catch (error) {
            hasError.value = true
            console.error('API Error:', error)
        } finally {
            isLoading.value = false
        }
    }

    // Phương thức fetch dữ liệu ảnh theo feature
    const fetchImagesByFeature = async (id, page = 1, sort = 'newest') => {
        if (!id) return;

        isLoading.value = true
        hasError.value = false

        try {
            const response = await store.fetchImagesByFeature(id, page, sort)
            return response;
        } catch (error) {
            hasError.value = true
            console.error('API Error khi tải ảnh:', error)
        } finally {
            isLoading.value = false
        }
    }

    // Phương thức load thêm ảnh theo feature
    const loadMoreImages = async (id, sort = 'newest') => {
        if (currentPage.value < lastPage.value) {
            const nextPage = currentPage.value + 1;
            return await fetchImagesByFeature(id, nextPage, sort);
        }
    }

    // Phương thức mới để tải thêm ảnh người dùng đã tạo
    const loadMoreUserImages = async (userId, page = 1) => {
        if (page) {
            isLoading.value = true;
            hasError.value = false;

            try {
                // Tạo URL API với tham số phân trang và userId (chỉ thêm user_id nếu có giá trị hợp lệ)
                let apiUrl = `/get_images_created_by_user?page=${page}`;
                if (userId !== null && userId !== undefined && userId !== 'null' && userId !== '') {
                    apiUrl += `&user_id=${userId}`;
                }                
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

    // Phương thức tải thêm ảnh người dùng đã thích
    const loadMoreLikedImages = async (userId, page = 1) => {
        if (page) {
            isLoading.value = true;
            hasError.value = false;

            try {
                // Tạo URL API với tham số phân trang và userId (chỉ thêm user_id nếu có giá trị hợp lệ)
                let apiUrl = `/get_images_liked?page=${page}`;
                if (userId !== null && userId !== undefined && userId !== 'null' && userId !== '') {
                    apiUrl += `&user_id=${userId}`;
                }
                
                // Dùng store để tải dữ liệu theo trang
                await store.fetchImagesLikedPage(apiUrl, page);
                return true;
            } catch (error) {
                console.error("Lỗi khi tải thêm ảnh đã thích:", error);
                hasError.value = true;
                return false;
            } finally {
                isLoading.value = false;
            }
        }
    }

    // Phương thức tải thêm ảnh người dùng đã tạo
    const loadMoreCreatedImages = async (userId, page = 1) => {
        if (page) {
            isLoading.value = true;
            hasError.value = false;

            try {
                // Tạo URL API với tham số phân trang và userId (chỉ thêm user_id nếu có giá trị hợp lệ)
                let apiUrl = `/get_images_created_by_user?page=${page}`;
                if (userId !== null && userId !== undefined && userId !== 'null' && userId !== '') {
                    apiUrl += `&user_id=${userId}`;
                }                
                // Dùng store để tải dữ liệu theo trang
                await store.fetchImagesCreatedByUserPage(apiUrl, page);
                return true;
            } catch (error) {
                console.error("Lỗi khi tải thêm ảnh đã tạo:", error);
                hasError.value = true;
                return false;
            } finally {
                isLoading.value = false;
            }
        }
    }

    // Phương thức tải thêm ảnh người dùng đã tải lên
    const loadMoreUploadedImages = async (userId, page = 1) => {
        if (page) {
            isLoading.value = true;
            hasError.value = false;

            try {
                // Tạo URL API với tham số phân trang và userId (chỉ thêm user_id nếu có giá trị hợp lệ)
                let apiUrl = `/get_images_uploaded?page=${page}`;
                if (userId !== null && userId !== undefined && userId !== 'null' && userId !== '') {
                    apiUrl += `&user_id=${userId}`;
                }                
                // Dùng store để tải dữ liệu theo trang
                await store.fetchImagesUploadedPage(apiUrl, page);
                return true;
            } catch (error) {
                console.error("Lỗi khi tải thêm ảnh đã tải lên:", error);
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

    // Thêm hàm checkNeedRefreshUserImages 
    const checkNeedRefreshUserImages = async (userId) => {
        try {
            const response = await imageAPI.checkForNewImages(userId);
            if (response.data && response.data.success && response.data.has_new_data) {
                console.log('Phát hiện dữ liệu mới, tự động làm mới...');
                await fetchImagesCreatedByUser(userId);
                return true;
            }
            return false;
        } catch (error) {
            console.error('Lỗi khi kiểm tra dữ liệu mới:', error);
            return false;
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
        hasMoreUploadedImages,
        hasMoreLikedImages,
        hasMoreCreatedImages,
        showRefreshButton,

        // Computed properties
        imageUrls,
        dataImage,
        userImage,
        imagesCreatedByUser,
        imagesLikedByUser,
        imagesUploadedByUser,
        errorMessage,
        imageDetail,

        // Methods
        fetchImages,
        fetchImagesLiked,
        fetchImagesUploaded,
        fetchImagesByFeature,
        loadMoreImages,
        goToImageDetail,
        loadMoreUserImages,
        loadMoreLikedImages,
        loadMoreCreatedImages,
        loadMoreUploadedImages,
        deleteImage,
        updateImage,
        checkNeedRefreshUserImages
    }
}