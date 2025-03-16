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
            error_message: null,
            imagesCreatedByUser: [],
        }),
        actions: {
            async fetchImages(id) {
                this.error_message = null;
                try {
                    const response = await imageAPI.getImagesByID(id)
                    if (response.data && response.data.success) {
                        this.images = response.data.images
                        this.data = response.data.data
                        this.user = toRaw(response.data.user)
                    } else {
                        console.error('Error:', response.statusText)
                        this.error_message = response.data.message
                    }
                } catch (error) {
                    console.error('Error fetching images:', error)
                    this.error_message = 'An error occurred'
                }
            },            
            clearImages() {
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
            }
        },
        persist: true
    },
)