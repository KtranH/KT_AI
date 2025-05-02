<template>
<div class="min-h-screen bg-gray-100 pt-24" data-aos = "zoom-out">
    <div class="max-w-[90%] mx-auto my-4">
        <!-- Container with responsive design -->
        <div class="bg-white md:rounded-lg md:shadow-lg md:overflow-hidden md:max-w-8xl md:mx-auto md:my-8">
            <!-- Instagram-like layout: post image on left, details on right -->
            <div class="flex flex-col md:flex-row">
                <!-- Left column: Image -->
                <ImageViewer :imageID="imageId" />

                <!-- Right column: Post header, comments, interactions -->
                <CommentSection :imageId="imageId" :highlightCommentId="commentId" :shouldHighlight="shouldHighlight" />
            </div>
        </div>
    </div>
</div>
</template>

<script>
import ImageViewer from '@/components/user/ImageDetail/ImageViewer.vue'
import CommentSection from '@/components/user/ImageDetail/CommentSection.vue'
import { useRoute } from 'vue-router'
import { decodedID } from '@/utils'

export default {
    name: 'Detail',
    components: {
        ImageViewer,
        CommentSection
    },
    setup() {
        const route = useRoute()
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

        return {
            imageId,
            commentId,
            shouldHighlight
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