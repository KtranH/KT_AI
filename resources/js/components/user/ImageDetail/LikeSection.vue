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
                    <div v-for="(like, index) in listLikes" :key="index" class="flex items-center">    
                        <span class="ml-2 text-gray-500 text-sm font-medium">{{ like.user_name }}</span>
                        <span v-if="index < listLikes.length - 1">,</span>
                    </div>
                    <span v-if="listLikes.length < currentImageLikes" class="text-gray-500 text-sm font-medium ml-2"> và {{ currentImageLikes - listLikes.length }} người khác</span>
                </div>
                <div v-else class="text-gray-500 text-sm font-medium">Chưa có ai thích. Hãy là người đầu tiên thích!</div>
            </div>
            <!--<button class="mr-4 focus:outline-none">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                </svg>
            </button>
            <button class="mr-4 focus:outline-none">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
                </svg>
            </button>
            <button class="ml-auto focus:outline-none">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z" />
                </svg>
            </button>-->
        </div>
        <div class="font-semibold mb-1">{{ currentImageLikes }} lượt thích</div>
        <VueSonner />
    </div>
</template>

<script>
import useLikes from '@/composables/user/useLikes'
import { onMounted, watch, computed, ref } from 'vue'
import { useRoute } from 'vue-router'
import { decodedID } from '@/utils'
import { isActionTooQuick } from '@/utils'
import { toast, Toaster as VueSonner } from 'vue-sonner'
import { useImageStore } from '@/stores/user/imagesStore'

export default {
    name: 'LikeSection',
    components: {
        VueSonner
    },
    setup() {
        const isFast = ref(false)
        const lastLikeTime = ref(0)
        const route = useRoute()
        const imageStore = useImageStore()
        
        // Lấy dữ liệu từ composable useLikes
        const { isLiked, totalLikes, likePost, listLikes, fetchLikes } = useLikes()
        
        // Tạo một computed để luôn lấy giá trị mới nhất từ imageStore
        const currentImageLikes = computed(() => {
            return imageStore.data?.sum_like || 0
        })

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
        })

        return {
            isLiked,
            currentImageLikes,
            likePost,
            listLikes,
            userLikePost,
            isFast
        }
    }
}
</script> 