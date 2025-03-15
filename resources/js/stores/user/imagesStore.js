import { defineStore } from 'pinia';
import { imageAPI } from '../../services/api';

export const useImageStore = defineStore('image',
    {
        state: () => ({
            images: [],
            data: null,
            user: null,
            error_message: null
        }),
        actions: {
            async fetchImages(id) {
                if(this.images.length > 0 && this.data !== null && this.user !== null)
                { 
                    console.log('Fetching images: already loaded');
                    return this.images
                }
                this.error_message = null;
                try {
                    const response = await imageAPI.getImagesByID(id);
                    if (response.data && response.data.success) {
                        this.images = response.data.images;
                        this.data = response.data.data;
                        this.user = response.data.user;
                    } else {
                        console.error('Error:', response.statusText)
                        this.error_message = response.data.message
                    }
                } catch (error) {
                    console.error('Error fetching images:', error)
                    this.error_message = 'An error occurred'
                }
            }
        },
        persist: true
    },
)