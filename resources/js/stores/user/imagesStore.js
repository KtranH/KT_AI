import { defineStore } from 'pinia';
import { imageAPI } from '../../services/api';

export const useImageStore = defineStore('image',
    {
        state: () => ({
            images: [],
        }),
        actions: {
            async fetchImages(id) {
                if(this.images.length > 0)
                { 
                    console.log('Fetching images: already loaded');
                    return this.images
                }
                try {
                    const response = await imageAPI.getImagesByID(id);
                    if (response.data && response.data.success) {
                        this.images = response.data.data;
                    } else {
                        console.error('Error:', response.statusText)
                    }
                } catch (error) {
                    console.error('Error fetching images:', error)
                }
            }
        },
        persist: true
    },
)