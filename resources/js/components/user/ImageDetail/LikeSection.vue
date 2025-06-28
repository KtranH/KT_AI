<template>
    <div class="border-t border-b p-4">
        <div class="flex items-center mb-2">
            <button class="mr-4 focus:outline-none" @click="userLikePost">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" :class="{'text-red-500 fill-current': isLiked}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                </svg>
            </button>
            <!-- List User name Liked -->
            <div class="flex items-center">
                <div v-if="listLikes.length > 0" class="flex items-center">
                    <span v-if="isLiked && listLikes.length > 1" class="text-gray-500 text-sm font-medium">Bạn và {{ listLikes.length - 1 }} người khác</span>
                    <span v-else-if="isLiked && listLikes.length === 1" class="text-gray-500 text-sm font-medium">Bạn đã thích</span>
                    <span v-else-if="listLikes.length > 0" class="text-gray-500 text-sm font-medium">{{ listLikes.length }} người khác đã thích</span>
                </div>
                <div v-else class="text-gray-500 text-sm font-medium">Chưa có ai thích. Hãy là người đầu tiên thích!</div>
            </div>
            <router-link
                :to="{ name: 'createimage', params: { encodedID: encodedID(featureImage?.id || 1) }}"
                class="text-blue-500 hover:underline ml-auto text-sm font-medium">
            <i class="fa-solid fa-square-caret-right" style="color: #74C0FC;"></i> {{ featureImage?.title || 'Đang tải...' }}</router-link>
        </div>
    </div>
</template>

<script>
import useImage from '@/composables/user/useImage'
import useLikes from '@/composables/user/useLikes'
import { onMounted, watch, ref } from 'vue'
import { useRoute } from 'vue-router'
import { decodedID, encodedID } from '@/utils'
import { isActionTooQuick } from '@/utils'
import { toast } from 'vue-sonner'

export default {
    name: 'LikeSection',
    setup() { 
        const isFast = ref(false)
        const lastLikeTime = ref(0)
        const route = useRoute()
        const { dataImage, goToImageDetail } = useImage()
        const featureImage = ref(null)
        
        // Lấy dữ liệu từ composable useLikes
        const { isLiked, likePost, listLikes, fetchLikes } = useLikes()

        const userLikePost = async () => {
            if (isActionTooQuick(lastLikeTime.value)) {
                toast.error('Vui lòng đợi 1 giây trước khi thực hiện thao tác khác', {
                    duration: 3000,            
                    position: 'bottom-right'
                })
                isFast.value = true
                return
            }
            lastLikeTime.value = Date.now()
            await likePost()
            isFast.value = false
        }
        
        // Theo dõi sự thay đổi của route.params.encodedID để cập nhật dữ liệu khi chuyển bài viết
        watch(() => route.params.encodedID, async (newId, oldId) => {
            if (newId && newId !== oldId) {
                await fetchLikes(decodedID(newId))
            }
        })

        onMounted(async () => {
            // Lấy thông tin like khi component được mount
            await fetchLikes(decodedID(route.params.encodedID))
            featureImage.value = dataImage.value.ai_feature
        })

        return {
            isLiked,
            likePost,
            listLikes,
            userLikePost,
            isFast,
            featureImage,
            goToImageDetail,
            encodedID
        }
    }
}
</script> 