<template>
<div class="h-[90vh]" data-aos = "zoom-out">
    <div class="h-full mx-auto">
        <!-- Container with responsive design -->
        <div class="bg-white h-full md:rounded-lg md:shadow-lg md:overflow-hidden md:max-w-8xl md:mx-auto">
            <!-- Instagram-like layout: post image on left, details on right -->
            <div class="flex flex-col md:flex-row h-full">
                <!-- Left column: Image -->
                <ImageViewer :imageID="imageId" @navigate-to-user="navigateToUserDashboard" />

                <!-- Right column: Post header, comments, interactions -->
                <CommentSection :imageId="imageId" :highlightCommentId="commentId" :shouldHighlight="shouldHighlight" @navigate-to-user="navigateToUserDashboard" />
            </div>
        </div>
    </div>
</div>
</template>

<script>
import { ImageViewer } from '@/components/features/images'
import { CommentSection } from '@/components/features/comments'
import { useRoute, useRouter } from 'vue-router'
import { decodedID } from '@/utils'
import { useAuthStore } from '@/stores/auth/authStore'

export default {
    name: 'Detail',
    components: {
        ImageViewer,
        CommentSection
    },
    setup() {
        const route = useRoute()
        const router = useRouter()
        const userStore = useAuthStore()
        let imageId = null
        let commentId = null
        let shouldHighlight = false

        try {
            // Thêm xử lý lỗi khi decode ID
            imageId = decodedID(route.params.encodedID)
        } catch (error) {
            imageId = route.params.encodedID // Sử dụng ID gốc nếu không thể giải mã
        }

        // Lấy comment ID từ query parameter nếu có
        if (route.query.comment) {
            commentId = parseInt(route.query.comment, 10) || route.query.comment
        }

        // Kiểm tra xem có cần highlight không
        if (route.query.highlight === 'true') {
            shouldHighlight = true
        }

        // Hàm điều hướng đến trang dashboard của người dùng
        const navigateToUserDashboard = (userId) => {
            // Kiểm tra xem userId có phải là người dùng hiện tại không
            if (userId === userStore.user?.id) {
                // Nếu là người dùng hiện tại, điều hướng đến trang dashboard cá nhân
                router.push({ name: 'dashboard' })
            } else {
                // Nếu là người dùng khác, điều hướng đến trang dashboard với userId
                router.push({ 
                    name: 'dashboard', 
                    query: { userId: userId }
                })
            }
        }

        return {
            imageId,
            commentId,
            shouldHighlight,
            navigateToUserDashboard
        }
    }
}
</script>

<style scoped>
/* Enhanced gradient animation */
.bg-gradient-text {
  background: linear-gradient(
    -45deg,
    #3b82f6,
    #6366f1,
    #8b5cf6,
    #ec4899,
    #3b82f6
  );
  background-size: 400%;
  animation: gradient-animation 8s ease infinite;
}

@keyframes gradient-animation {
  0% { background-position: 0% 50%; }
  50% { background-position: 100% 50%; }
  100% { background-position: 0% 50%; }
}
</style>