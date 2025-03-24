import { defineStore } from 'pinia'
import { imageAPI } from '../../services/api'
import { imageCreatedByUserAPI } from '../../services/api'
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
            lastFetchedId: null,
            isLoading: false,
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
                    
                    const response = await imageAPI.getImagesByID(id)
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
                try {
                    const response = await imageCreatedByUserAPI.getImagesCreatedByUser()
                    if (response.data && response.data.success) {
                        this.imagesCreatedByUser = response.data.data.map(item => ({
                            id: item.id,
                            url: item.image_url
                        }));
                    } else {
                        console.error('Error:', response.statusText)
                        this.error_message = response.data.message
                    }
                } catch (error) {
                    console.error('Error fetching images:', error)
                    this.error_message = 'An error occurred'
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