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
                        if (response.data.data && response.data.data.like) {
                            this.likes = response.data.data.like
                        } else if (response.data.like) {
                            this.likes = response.data.like
                        }
                    } else {
                        this.error_message = response.data?.message || 'Không thể tải dữ liệu like'
                    }
                } catch (error) {
                    console.error('Error fetching likes:', error)
                    this.error_message = error.message || 'Lỗi khi tải dữ liệu like'
                }
            },
            async checkLiked(id) {
                this.error_message = null;
                try {
                    const response = await likeAPI.checkLiked(id)
                    if (response.data && response.data.success) {
                        if (response.data.data && typeof response.data.data.is_liked !== 'undefined') {
                            this.isLiked = response.data.data.is_liked
                        } else if (typeof response.data.data === 'boolean') {
                            this.isLiked = response.data.data
                        }
                    } else {
                        console.error('Error:', response.statusText)
                        this.error_message = response.data?.message || 'Không thể kiểm tra trạng thái like'
                    }
                } catch (error) {
                    console.error('Error checking liked status:', error)
                    this.error_message = error.message || 'Lỗi khi kiểm tra trạng thái like'
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
