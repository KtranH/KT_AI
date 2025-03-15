import { ref, computed } from 'vue'
import { useImageStore } from '@/stores/user/imagesStore'

export default function useLikes() {
    const isLiked = ref(false)
    const totalLikes = computed(() => useImageStore().data.sum_like)
   
    const likePost = () => {
        if (isLiked.value) {
            totalLikes.value--
        } else {
            totalLikes.value++
        }
        isLiked.value = !isLiked.value
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
        likeReply
    }
} 