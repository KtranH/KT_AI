import { ref } from 'vue'

export default function useLikes() {
    const isLiked = ref(false)
    const totalLikes = ref(5532)
    const postDate = ref('12 THÁNG 5, 2023')

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
        postDate,
        likePost,
        likeComment,
        likeReply
    }
} 