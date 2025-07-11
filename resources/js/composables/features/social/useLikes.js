import { computed } from 'vue'
import { useImageStore } from '@/stores/user/imagesStore'
import { useLikeStore } from '@/stores/user/likeStore'
import { likeAPI } from '@/services/api'
import { decodedID } from '@/utils'
import { useRoute } from 'vue-router'
import { ref } from 'vue'
import { toast } from 'vue-sonner'
import { isActionTooQuick } from '@/utils'

export default function useLikes() {
    const likeStore = useLikeStore()
    const imageStore = useImageStore()
    const isLiked = computed(() => likeStore.isLiked)
    const totalLikes = ref(imageStore.data?.sum_like || 0)
    const listLikes = computed(() => likeStore.likes)
    const route = useRoute()
    const lastActionTime = ref(null)

    // Phương thức fetch dữ liệu likes
    const fetchLikes = async (id) => {
        await likeStore.checkLiked(id)
        await likeStore.fetchLikes(id)
        totalLikes.value = imageStore.data?.sum_like || 0
    }

    // Phương thức like/unlike ảnh
    const likePost = async () => {
        try {
            const id = decodedID(route.params.encodedID)
            const currentLikeStatus = isLiked.value
            const previousLikeCount = totalLikes.value
            
            likeStore.$patch({
                isLiked: !currentLikeStatus
            })
            
            if (currentLikeStatus) {
                totalLikes.value--
                imageStore.updateLikeCount(false)
            } else {
                totalLikes.value++
                imageStore.updateLikeCount(true)
            }
            
            let response;
            if (currentLikeStatus) {
                response = await likeAPI.unlikePost(id)
            } else {
                response = await likeAPI.likePost(id)
            }
            
            if (response?.data?.success) {
                if (response.data.data && typeof response.data.data.new_like_count !== 'undefined') {
                    totalLikes.value = response.data.data.new_like_count
                    if (imageStore.data) {
                        imageStore.data.sum_like = response.data.data.new_like_count
                    }
                }
                await fetchLikes(id)
            } else {
                likeStore.$patch({
                    isLiked: currentLikeStatus
                })
                totalLikes.value = previousLikeCount
                if (imageStore.data) {
                    imageStore.data.sum_like = previousLikeCount
                }
                toast.error(response?.data?.message || 'Không thể thực hiện thao tác like/unlike')
            }
        } catch (error) {
            console.error('Lỗi khi thực hiện like/unlike:', error)
            likeStore.$patch({
                isLiked: !likeStore.isLiked
            })
            totalLikes.value = imageStore.data?.sum_like || 0
            
            const errorMessage = error.response?.data?.message || 'Không thể thực hiện thao tác like/unlike'
            toast.error(errorMessage)
        }
    }

    // Phương thức like/unlike bình luận
    const likeComment = async (comment) => {
        if (isActionTooQuick(lastActionTime.value)) {
            toast.error('Hãy đợi một chút trước khi thực hiện hành động này')
            return
        }
        try {
            const originalLiked = comment.isLiked
            const originalLikes = comment.likes
            
            comment.isLiked = !comment.isLiked
            comment.likes = comment.isLiked ? comment.likes + 1 : Math.max(0, comment.likes - 1)
            
            const response = await likeAPI.toggleCommentLike(comment.id)
            
            comment.likes = response.data.likes
            comment.isLiked = response.data.isLiked
        } catch (err) {
            console.error("Lỗi khi thích/bỏ thích bình luận:", err)
            comment.isLiked = originalLiked
            comment.likes = originalLikes
            
            toast.error('Không thể thích/bỏ thích bình luận')
        }
        lastActionTime.value = new Date()
    }

    // Phương thức like/unlike phản hồi
    const likeReply = async (comment, reply) => {
        if (isActionTooQuick(lastActionTime.value)) {
            toast.error('Hãy đợi một chút trước khi thực hiện hành động này')
            return
        }
        try {
            const originalLiked = reply.isLiked
            const originalLikes = reply.likes
            
            reply.isLiked = !reply.isLiked
            reply.likes = reply.isLiked ? reply.likes + 1 : Math.max(0, reply.likes - 1)
            
            const response = await likeAPI.toggleCommentLike(reply.id)
            
            reply.likes = response.data.likes
            reply.isLiked = response.data.isLiked
        } catch (err) {
            console.error("Lỗi khi thích/bỏ thích phản hồi:", err)
            reply.isLiked = originalLiked 
            reply.likes = originalLikes
            
            toast.error('Không thể thích/bỏ thích phản hồi')
        }
        lastActionTime.value = new Date()
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