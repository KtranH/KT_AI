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
                if (this.lastFetchedId === id && this.data !== null) {
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
                    
                    const response = await imageAPI.getImages(id)
                    if (response.data && response.data.success) {
                        this.images = response.data.images
                        this.data = response.data.data
                        
                        if (response.data.user) {
                            this.user = toRaw(response.data.user)
                            this.lastUser = { ...toRaw(response.data.user) }
                        }
                        
                        this.lastFetchedId = id;
                    } else {
                        console.error('Error:', response.statusText)
                        this.error_message = response.data.message
                    }
                } catch (error) {
                    console.error('Error fetching images:', error)
                    this.error_message = 'An error occurred'
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
                this.data = null;
                this.user = null;
                this.error_message = null;
            },
            async fetchImagesCreatedByUser() {
                this.error_message = null
                this.isLoading = true
                try {
                    const response = await imageAPI.getImagesCreatedByUser()
                    if (response.data && response.data.success) {
                        this.imagesCreatedByUser = response.data.data.map(item => {
                            const imageUrls = item.image_url || [];
                            return {
                                id: item.id,
                                prompt: item.prompt,
                                sum_like: item.sum_like,
                                sum_comment: item.sum_comment,
                                created_at: item.created_at,
                                updated_at: item.updated_at,
                                url: imageUrls,
                                image_url: imageUrls
                            };
                        });
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
                            this.images = processedData;
                        } else {
                            this.imagesByFeature = [...this.imagesByFeature, ...processedData];
                            this.images = [...this.images, ...processedData];
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