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
            imagesCreatedByUser: [],
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
            async fetchImagesCreatedByUser() {
                this.error_message = null
                this.isLoading = true
                try {
                    const response = await imageAPI.getImagesCreatedByUser()
                    if (response.data && response.data.success) {
                        if (Array.isArray(response.data.data)) {
                            this.imagesCreatedByUser = response.data.data.map(item => {
                                const imageUrls = item.image_url || [];
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
                        } else {
                            console.error('Dữ liệu trả về không phải là mảng:', response.data.data);
                            this.imagesCreatedByUser = [];
                        }
                        
                        // Cập nhật thông tin phân trang
                        if (response.data.pagination) {
                            this.currentPage = response.data.pagination.current_page;
                            this.lastPage = response.data.pagination.last_page;
                            this.totalImages = response.data.pagination.total;
                        }
                    } else {
                        console.error('Error:', response.statusText || 'Unknown error')
                        this.error_message = response.data?.message || 'Không thể tải dữ liệu'
                        this.imagesCreatedByUser = [];
                    }
                } catch (error) {
                    console.error('Error fetching images:', error)
                    this.error_message = error.message || 'Đã xảy ra lỗi'
                    this.imagesCreatedByUser = [];
                } finally {
                    this.isLoading = false
                }
            },
            async fetchImagesCreatedByUserPage(url, page) {
                this.error_message = null
                this.isLoading = true
                try {
                    const response = await imageAPI.getImagesCreatedByUserPage(url)
                    if (response.data && response.data.success) {
                        if (Array.isArray(response.data.data)) {
                            const newImages = response.data.data.map(item => {
                                const imageUrls = item.image_url || [];
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

                            // Thêm vào mảng hiện có nếu đang tải thêm (trang > 1)
                            if (page && page > 1) {
                                this.imagesCreatedByUser = [...this.imagesCreatedByUser, ...newImages];
                            } else {
                                this.imagesCreatedByUser = newImages;
                            }
                            
                            // Cập nhật thông tin phân trang
                            if (response.data.pagination) {
                                this.currentPage = response.data.pagination.current_page;
                                this.lastPage = response.data.pagination.last_page;
                                this.totalImages = response.data.pagination.total;
                            }
                            
                            return true;
                        } else {
                            console.error('Dữ liệu trả về không phải là mảng:', response.data.data);
                            return false;
                        }
                    } else {
                        console.error('Error:', response.statusText || 'Unknown error')
                        this.error_message = response.data?.message || 'Không thể tải dữ liệu'
                        return false;
                    }
                } catch (error) {
                    console.error('Error fetching more images:', error)
                    this.error_message = error.message || 'Đã xảy ra lỗi'
                    return false;
                } finally {
                    this.isLoading = false
                }
            },
            async fetchImagesByFeature(id, page = 1) {
                this.error_message = null
                this.isLoading = true
                
                try {
                    const response = await imageAPI.getImagesByFeature(id, page)             
                    if (response.data && response.data.success) {
                        const processedData = response.data.data.map(image => {
                            return {
                                ...image,
                                currentSlideIndex: 0
                            };
                        });
                        
                        if (page === 1) {
                            this.imagesByFeature = processedData;
                        } else {
                            this.imagesByFeature = [...this.imagesByFeature, ...processedData];
                        }
                        
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