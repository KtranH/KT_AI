import { defineStore } from 'pinia'
import { imageAPI } from '@/services/api'
import { toRaw } from 'vue'

export const useImageStore = defineStore('image',
    {
        state: () => ({
            images: [],
            data: null,
            user: null,
            lastUser: null,
            error_message: null,
            imagesLikedByUser: [],
            imagesUploadedByUser: [],
            imagesByFeature: [],
            lastFetchedId: null,
            isLoading: false,
            currentPage: 1,
            lastPage: 1,
            totalImages: 0
        }),
        getters: {
            currentUser: (state) => state.user || state.lastUser || null
        },
        actions: {
            async fetchImages(id) {

                if (!id) {
                    this.error_message = "ID không hợp lệ hoặc trống";
                    return;
                }
                
                // Đảm bảo ID được xử lý đúng cách
                const processedId = String(id).trim();

                if (this.lastFetchedId === processedId && this.data !== null && this.images.length > 0) {
                    return;
                }
                
                this.error_message = null;
                this.isLoading = true;
                
                try {
                    if (this.user) {
                        this.lastUser = { ...toRaw(this.user) };
                    }
                    
                    this.images = [];
                    this.data = null;

                    const response = await imageAPI.getImages(processedId)
                    if (response.data && response.data.success) {
                        this.images = response.data.images
                        this.data = toRaw(response.data.data)
                        if (response.data.user) {
                            this.user = toRaw(response.data.user)
                            this.lastUser = { ...toRaw(response.data.user) }
                        }
                        
                        this.lastFetchedId = processedId;
                    } else {
                        console.error('Error:', response.data?.message || response.statusText)
                        this.error_message = response.data?.message || 'Không thể tải dữ liệu'
                    }
                } catch (error) {
                    console.error('Error fetching images:', error)
                    this.error_message = error.message || 'Đã xảy ra lỗi khi tải dữ liệu'
                    this.images = [];
                    this.data = null;
                } finally {
                    this.isLoading = false;
                }
            },            
            clearImages() {
                if (this.user) {
                    this.lastUser = { ...toRaw(this.user) };
                }

                this.images = [];
                this.imageUrls = [];
                this.data = null;
                this.feature = null;
                this.user = null;
                this.error_message = null;
            },
            // Hàm tiện ích chung để xử lý việc tải hình ảnh
            async _fetchImages(apiMethod, targetArray = 'imagesCreatedByUser', page = 1, appendData = false) {
                this.error_message = null
                this.isLoading = true
                
                // Reset data trước khi fetch nếu không phải append
                if (!appendData) {
                    this[targetArray] = []
                }
                
                try {
                    const response = await apiMethod
                    
                    if (response.data && response.data.success) {
                        // Kiểm tra dữ liệu trong response
                        let dataToProcess = []
                        
                        if (Array.isArray(response.data.data)) {
                            dataToProcess = response.data.data
                        } else if (response.data.images && Array.isArray(response.data.images)) {
                            dataToProcess = response.data.images
                        } else if (response.data.data && Array.isArray(response.data.data)) {
                            dataToProcess = response.data.data
                        } else {
                            console.warn(`Không tìm thấy mảng dữ liệu trong response cho ${targetArray}`, response.data)
                            dataToProcess = []
                        }
                        
                        if (dataToProcess.length > 0) {
                            const processedImages = dataToProcess.map(item => {
                                // Xử lý trường hợp image_url có thể là string (JSON) hoặc array
                                let imageUrls = []
                                
                                if (item.image_url) {
                                    if (typeof item.image_url === 'string') {
                                        try {
                                            // Thử parse JSON
                                            const parsed = JSON.parse(item.image_url)
                                            imageUrls = Array.isArray(parsed) ? parsed : [item.image_url]
                                        } catch (e) {
                                            // Nếu không parse được, coi như là URL đơn
                                            imageUrls = [item.image_url]
                                        }
                                    } else if (Array.isArray(item.image_url)) {
                                        imageUrls = item.image_url
                                    }
                                }
                                
                                return {
                                    id: item.id,
                                    prompt: item.prompt,
                                    sum_like: item.sum_like || 0,
                                    sum_comment: item.sum_comment || 0,
                                    created_at: item.created_at,
                                    updated_at: item.updated_at,
                                    user: item.user || null,
                                    url: imageUrls,
                                    image_url: imageUrls
                                };
                            });
                            
                            // Cập nhật mảng đích, thêm vào mảng hiện có nếu appendData = true và page > 1
                            if (appendData && page > 1) {
                                this[targetArray] = [...this[targetArray], ...processedImages];
                            } else {
                                this[targetArray] = processedImages;
                            }
                            
                        } else {
                            // Nếu không có dữ liệu, đặt mảng trống
                            if (!appendData) {
                                this[targetArray] = []
                            }
                        }
                        
                        // Cập nhật thông tin phân trang
                        if (response.data.pagination) {
                            this.currentPage = response.data.pagination.current_page;
                            this.lastPage = response.data.pagination.last_page;
                            this.totalImages = response.data.pagination.total;
                        }
                        
                        return true;
                    } else {
                        console.error('Error:', response.statusText || 'Unknown error')
                        this.error_message = response.data?.message || 'Không thể tải dữ liệu'
                        if (!appendData) {
                            this[targetArray] = [];
                        }
                        return false;
                    }
                } catch (error) {
                    console.error('Error fetching images:', error)
                    this.error_message = error.message || 'Đã xảy ra lỗi'
                    if (!appendData) {
                        this[targetArray] = [];
                    }
                    return false;
                } finally {
                    this.isLoading = false
                }
            },
            
            // Tải hình ảnh do người dùng tạo
            async fetchImagesCreatedByUser(userId) {
                // Reset dữ liệu trước khi fetch
                this.imagesCreatedByUser = [];
                return await this._fetchImages(imageAPI.getImagesCreatedByUser(userId), 'imagesCreatedByUser');
            },
            
            // Tải hình ảnh người dùng đã thích
            async fetchImagesLiked(userId) {
                // Reset dữ liệu trước khi fetch
                this.imagesLikedByUser = [];
                return await this._fetchImages(imageAPI.getImagesLiked(userId), 'imagesLikedByUser');
            },
            
            // Tải hình ảnh người dùng đã tải lên
            async fetchImagesUploaded(userId) {
                // Reset dữ liệu trước khi fetch
                this.imagesUploadedByUser = [];
                return await this._fetchImages(imageAPI.getImagesUploaded(userId), 'imagesUploadedByUser');
            },

            // Tải thêm hình ảnh người dùng đã thích theo trang (phân trang)
            async fetchImagesLikedPage(url, page) {
                return await this._fetchImages(imageAPI.getImagesLikedPage(url), 'imagesLikedByUser', page, page > 1);
            },

            // Tải thêm hình ảnh người dùng đã tải lên theo trang (phân trang)
            async fetchImagesUploadedPage(url, page) {
                return await this._fetchImages(imageAPI.getImagesUploadedPage(url), 'imagesUploadedByUser', page, page > 1);
            },

            // Tải thêm hình ảnh theo tính năng (phân trang)
            async fetchImagesByFeature(id, page = 1) {
                this.error_message = null
                this.isLoading = true
                
                try {
                    const response = await imageAPI.getImagesByFeature(id, page)             
                    if (response.data && response.data.success) {
                        // Xử lý dữ liệu đặc biệt cho tính năng này (thêm currentSlideIndex)
                        const processedData = response.data.data.map(image => {
                            return {
                                ...image,
                                currentSlideIndex: 0
                            };
                        });
                        
                        // Cập nhật mảng đích
                        if (page === 1) {
                            this.imagesByFeature = processedData;
                        } else {
                            this.imagesByFeature = [...this.imagesByFeature, ...processedData];
                        }
                        
                        // Cập nhật thông tin phân trang
                        if (response.data.pagination) {
                            this.currentPage = response.data.pagination.current_page;
                            this.lastPage = response.data.pagination.last_page;
                            this.totalImages = response.data.pagination.total;
                        }
                    } else {
                        console.error('Error:', response.statusText)
                        this.error_message = response.data.message
                    }
                } catch (error) {
                    console.error('Error fetching images:', error)
                    this.error_message = 'An error occurred'
                } finally {
                    this.isLoading = false
                }
            },
            clearImagesCreatedByUser() {
                this.imagesCreatedByUser = []
                this.error_message = null
            },
            updateLikeCount(increment = true) {
                if (this.data) {
                    if (increment) {
                        this.data.sum_like++;
                    } else if (this.data.sum_like > 0) {
                        this.data.sum_like--;
                    }
                }
            },
            // Xóa toàn bộ dữ liệu trong store
            clearAllUserImages() {
                this.imagesCreatedByUser = [];
                this.imagesLikedByUser = [];
                this.imagesUploadedByUser = [];
                this.error_message = null;
                this.currentPage = 1;
                this.lastPage = 1;
                this.totalImages = 0;
            }
        },
        persist: {
            enabled: true,
            strategies: [
                {
                    key: 'image-store',
                    storage: localStorage,
                    paths: ['lastUser']
                }
            ]
        }
    },
)