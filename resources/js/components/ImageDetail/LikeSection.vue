<template>
    <div class="border-t border-b p-4">
        <div class="flex items-center mb-2">
            <button class="mr-4 focus:outline-none" @click="likePost">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" :class="{'text-red-500 fill-current': isLiked}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                </svg>
            </button>
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
        <div class="font-semibold mb-1">{{ totalLikes }} lượt thích</div>
        <div class="text-xs text-gray-500">{{ postDate }}</div>
    </div>
</template>

<script>
import { defineProps } from 'vue'
import useLikes from '@/composables/useLikes'

export default {
    name: 'LikeSection',
    setup() {
        const { isLiked, totalLikes, postDate, likePost } = useLikes()

        return {
            isLiked,
            totalLikes,
            postDate,
            likePost
        }
    }
}
</script> 