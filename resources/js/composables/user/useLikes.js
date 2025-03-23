import { computed } from 'vue'
import { useImageStore } from '@/stores/user/imagesStore'
import { useLikeStore } from '@/stores/user/likeStore'
import { likeAPI } from '@/services/api'
import { decodedID } from '@/utils'
import { useRoute } from 'vue-router'
import { ref } from 'vue'

export default function useLikes() {
    const likeStore = useLikeStore()
    const isLiked = computed(() => likeStore.isLiked)
    const totalLikes = ref(useImageStore().data.sum_like)
    const listLikes = computed(() => likeStore.likes)
    const route = useRoute()

    const fetchLikes = async (id) => {
        await likeStore.checkLiked(id)
        await likeStore.fetchLikes(id)
    }

    const likePost = async () => {
        try {
            const id = decodedID(route.params.encodedID)
            const currentLikeStatus = isLiked.value
            
            // Cập nhật UI trước (optimistic update)
            // Không thay đổi isLiked trực tiếp mà thay đổi giá trị trong store
            likeStore.$patch({
                isLiked: !currentLikeStatus
            })
            
            if (currentLikeStatus) {
                totalLikes.value--
            } else {
                totalLikes.value++
            }
            
            // Call API
            if (currentLikeStatus) {
                await likeAPI.unlikePost(id)
            } else {
                await likeAPI.likePost(id)
            }
            
            // Refresh data from server
            await fetchLikes(id)
        } catch (error) {
            console.error('Lỗi khi thực hiện like/unlike:', error)
            // Rollback UI nếu API thất bại
            likeStore.$patch({
                isLiked: !likeStore.isLiked
            })
            
            if (likeStore.isLiked) {
                totalLikes.value++
            } else {
                totalLikes.value--
            }
        }
    }

    const likeComment = (comment) => {
        if (comment.isLiked) {
            comment.likes--
        } else {
            comment.likes++
        }
        comment.isLiked = !comment.isLiked
    }

    const likeReply = (comment, reply) => {
        if (reply.isLiked) {
            reply.likes--
        } else {
            reply.likes++
        }
        reply.isLiked = !reply.isLiked
    }

    return {
        isLiked,
        totalLikes,
        likePost,
        likeComment,
        likeReply,
        fetchLikes,
        listLikes
    }
} 