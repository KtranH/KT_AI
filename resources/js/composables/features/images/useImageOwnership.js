import { computed } from 'vue'
import { useAuthStore } from '@/stores/auth/authStore'
import { useImageStore } from '@/stores/user/imagesStore'

export function useImageOwnership() {
    const auth = useAuthStore()
    const imageStore = useImageStore()

    // Computed để kiểm tra xem user hiện tại có phải chủ bài viết không
    const isImageOwner = computed(() => {
        const currentUser = auth.user.value
        // Sử dụng user từ store thay vì từ dataImage
        const postOwner = imageStore.user || imageStore.currentUser
        
        if (!currentUser || !postOwner) {
            return false
        }
        
        // Chuyển về cùng kiểu dữ liệu để so sánh
        const currentUserId = Number(currentUser.id)
        const postOwnerId = Number(postOwner.id)
        
        return currentUserId === postOwnerId
    })

    // Hàm kiểm tra xem một user có phải là chủ sở hữu của comment/reply không
    const isCommentOwner = (commentUserId) => {
        const currentUser = auth.user.value
        
        if (!currentUser || !commentUserId) {
            return false
        }
        
        const currentUserId = Number(currentUser.id)
        const commentOwnerId = Number(commentUserId)
        
        return currentUserId === commentOwnerId
    }

    return {
        isImageOwner,
        isCommentOwner
    }
}
