import { defineStore } from 'pinia'
import { likeAPI } from '../../services/api'

export const useLikeStore = defineStore('like',
    {
        state: () => ({
            likes: [],
            isLiked: false,
            error_message: null,
        }),
        actions: {
            async fetchLikes(id) {
                this.error_message = null;
                try {
                    const response = await likeAPI.getLikesByID(id)
                    if (response.data && response.data.success) {
                        this.likes = response.data.data
                    } else {
                        this.error_message = response.data.message
                    }
                } catch (error) {
                    console.error('Error fetching likes:', error)
                    this.error_message = error.message
                }
            },
            async checkLiked(id) {
                this.error_message = null;
                try {
                    const response = await likeAPI.checkLiked(id)
                    if (response.data && response.data.success) {
                        this.isLiked = response.data.data
                    } else {
                        console.error('Error:', response.statusText)
                        this.error_message = response.data.message
                    }
                } catch (error) {
                    console.error('Error fetching likes:', error)
                    this.error_message = error.message
                }
            },
            clearLikes() {
                this.likes = []
                this.isLiked = false
                this.error_message = null
            }
        },
        persist: false
    }
)
