<template>
<div class="min-h-screen bg-gray-100 pt-24" data-aos = "zoom-out">
    <div class="max-w-[90%] mx-auto my-4">        
        <!-- Container with responsive design -->
        <div class="bg-white md:rounded-lg md:shadow-lg md:overflow-hidden md:max-w-8xl md:mx-auto md:my-8">
            <!-- Instagram-like layout: post image on left, details on right -->
            <div class="flex flex-col md:flex-row">
                <!-- Left column: Image -->
                <div class="md:w-3/5 bg-black flex items-center justify-center">
                    <img 
                        src="https://images2.thanhnien.vn/528068263637045248/2024/1/25/e093e9cfc9027d6a142358d24d2ee350-65a11ac2af785880-17061562929701875684912.jpg" 
                        class="w-full h-full md:h-[600px] md:object-contain cursor-pointer" 
                        alt="Fashion outfit" 
                        @click="previewImage"
                    />
                </div>

                <!-- Right column: Post header, comments, interactions -->
                <div class="md:w-2/5 md:flex md:flex-col md:border-l">
                    <!-- Header with profile info -->
                    <div class="flex items-center p-4 border-b">
                        <!-- Go back -->
                        <div class="flex items-center bg-gradient-text mr-2 rounded-xl">
                            <button @click="goBack" class="flex-shrink-0 px-4 py-1 text-[12px] font-medium text-white hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                                </svg>
                                Back
                            </button>
                        </div>
                        <img src="https://imagedelivery.net/ZeGtsGSjuQe1P3UP_zk3fQ/ede24b65-497e-4940-ea90-06cc2757a200/storedata" class="w-8 h-8 rounded-full" alt="Profile" />
                        <div class="ml-3">
                            <div class="flex items-center">
                                <span class="font-semibold">Test thử</span>
                                <div class="flex items-center ml-1">
                                    <span class="text-purple-400 mr-1">🔮</span>
                                    <span class="text-pink-500">👡</span>
                                </div>
                            </div>
                        </div>
                        <button class="ml-auto">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M6 10a2 2 0 11-4 0 2 2 0 014 0zM12 10a2 2 0 11-4 0 2 2 0 014 0zM16 12a2 2 0 100-4 2 2 0 000 4z" />
                            </svg>
                        </button>
                    </div>

                    <!-- Post Title (moved from original position) -->
                    <div class="px-4 py-2 border-b">
                        <div class="flex items-center">
                            <h1 class="text-base font-bold">Beach girl</h1>
                        </div>
                    </div>

                    <!-- Comments scrollable section -->
                    <div class="flex-1 overflow-y-auto" style="max-height: 380px">
                        <!-- Comment list -->
                        <div class="space-y-4 p-4">
                            <div v-for="(comment, index) in comments" :key="index" class="flex space-x-3">
                                <img :src="comment.avatar" class="w-8 h-8 rounded-full" :alt="comment.username" />
                                <div class="flex-1">
                                    <div class="flex items-start">
                                        <span class="font-semibold mr-2">{{ comment.username }}</span>
                                        <span class="flex-1" v-html="comment.text"></span>
                                    </div>
                                    <div class="flex items-center text-xs text-gray-500 mt-1">
                                        <span>{{ comment.time }}</span>
                                        <span class="mx-1">•</span>
                                        <button class="font-medium hover:underline" @click="startReply(index, comment.username)">Trả lời</button>
                                        <div class="flex items-center ml-2">
                                            <button @click="likeComment(comment)" class="flex items-center focus:outline-none">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" :class="{'text-red-500 fill-current': isLiked}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                                                </svg>
                                                <span class="ml-1">{{ comment.likes }}</span>
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Reply form -->
                                    <CommentReply
                                        v-if="replyingToIndex === index"
                                        :commentId="index"
                                        :replyToUsername="comment.username"
                                        :isReplying="replyingToIndex === index"
                                        @reply-submitted="handleReplySubmit"
                                        @cancel-reply="cancelReply"
                                    />

                                    <!-- Replies -->
                                    <div v-if="comment.replies && comment.replies.length > 0" class="mt-2 ml-4">
                                        <div v-if="!comment.showAllReplies && comment.replies.length > 1" class="text-xs text-blue-500 hover:underline cursor-pointer mt-1" @click="comment.showAllReplies = true">
                                            Xem {{ comment.replies.length }} câu trả lời
                                        </div>
                                        
                                        <div v-if="comment.showAllReplies || comment.replies.length === 1" class="space-y-3 mt-2">
                                            <div v-for="(reply, replyIndex) in comment.replies" :key="replyIndex" class="flex space-x-2">
                                                <img :src="reply.avatar" class="w-6 h-6 rounded-full" :alt="reply.username" />
                                                <div class="flex-1">
                                                    <div class="flex items-start">
                                                        <span class="font-semibold mr-2">{{ reply.username }}</span>
                                                        <span class="flex-1" v-html="reply.text"></span>
                                                    </div>
                                                    <div class="flex items-center text-xs text-gray-500 mt-1">
                                                        <span>{{ reply.time }}</span>
                                                        <span class="mx-1">•</span>
                                                        <button class="font-medium hover:underline" @click="startNestedReply(index, reply.username)">Trả lời</button>
                                                        <div class="flex items-center ml-2">
                                                            <button @click="likeReply(comment, reply)" class="flex items-center focus:outline-none">
                                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" :class="{'text-red-500 fill-current': isLiked}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                                                                </svg>
                                                                <span class="ml-1">{{ reply.likes }}</span>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Nested reply form -->
                                            <CommentReply
                                                v-if="replyingToIndex === index && replyingToNested"
                                                :commentId="index"
                                                :replyToUsername="replyToNestedUsername"
                                                :isReplying="replyingToIndex === index && replyingToNested"
                                                @reply-submitted="handleNestedReplySubmit"
                                                @cancel-reply="cancelReply"
                                            />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Post actions (like, comment, share) -->
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

                    <!-- New comment input -->
                    <div class="p-3 flex items-center relative">
                        <button class="p-2" @click="toggleEmojiPicker">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-500" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM7 9a1 1 0 100-2 1 1 0 000 2zm7-1a1 1 0 11-2 0 1 1 0 012 0zm-.464 5.535a1 1 0 10-1.415-1.414 3 3 0 01-4.242 0 1 1 0 00-1.415 1.414 5 5 0 007.072 0z" clip-rule="evenodd" />
                            </svg>
                        </button>
                        <input
                            type="text"
                            v-model="newComment"
                            placeholder="Thêm nhận xét..."
                            class="flex-1 p-2 focus:outline-none"
                            @keyup.enter="addComment"
                        />
                        <button
                            @click="addComment"
                            class="text-blue-500 font-semibold px-2"
                            :class="{'opacity-50 cursor-default': !newComment.trim(), 'hover:text-blue-600': newComment.trim()}"
                            :disabled="!newComment.trim()"
                        >
                            Đăng
                        </button>
                        <!-- Emoji Picker -->
                        <div v-if="showEmojiPicker" class="absolute bottom-14 left-0 z-50">
                            <EmojiPicker @select="addEmoji" :style="{ height: '400px', width: '300px' }" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</template>

<script>
import { ref } from 'vue'
import CommentReply from '@/components/layout/Reply.vue'
import EmojiPicker from 'vue3-emoji-picker';
import 'vue3-emoji-picker/css';

export default {
    name: 'Detail',
    components: {
        CommentReply,
        EmojiPicker,
    },
    setup() {
        const showEmojiPicker = ref(false)
        // State
        const newComment = ref('')
        const replyingToIndex = ref(-1)
        const replyingToNested = ref(false)
        const replyToNestedUsername = ref('')
        const isLiked = ref(false)
        const totalLikes = ref(5532)
        const postDate = ref('12 THÁNG 5, 2023')

        // Sample data
        const comments = ref([
        {
            username: 'sasha',
            avatar: 'https://images2.thanhnien.vn/528068263637045248/2024/1/25/e093e9cfc9027d6a142358d24d2ee350-65a11ac2af785880-17061562929701875684912.jpg',
            text: "Where's that shirt from?",
            time: '6 tháng',
            likes: 10,
            isLiked: false,
            showAllReplies: false,
            replies: [
            {
                username: 'ninn',
                avatar: 'https://images2.thanhnien.vn/528068263637045248/2024/1/25/e093e9cfc9027d6a142358d24d2ee350-65a11ac2af785880-17061562929701875684912.jpg',
                text: 'I believe that this one is from cider',
                time: '6 tháng',
                likes: 1,
                isLiked: false
            }
            ]
        },
        {
            username: 'doll',
            avatar: 'https://images2.thanhnien.vn/528068263637045248/2024/1/25/e093e9cfc9027d6a142358d24d2ee350-65a11ac2af785880-17061562929701875684912.jpg',
            text: 'omg i JUST got the same exact <span class="text-blue-500">skirt</span> tdy',
            time: '5 tháng',
            likes: 4,
            isLiked: false,
            showAllReplies: false,
            replies: []
        },
        {
            username: 'love',
            avatar: 'https://images2.thanhnien.vn/528068263637045248/2024/1/25/e093e9cfc9027d6a142358d24d2ee350-65a11ac2af785880-17061562929701875684912.jpg',
            text: 'where did you get it from ? ❤️',
            time: '5 tháng',
            likes: 3,
            isLiked: false,
            showAllReplies: false,
            replies: []
        },
        {
            username: 'Parthivi',
            avatar: 'https://images2.thanhnien.vn/528068263637045248/2024/1/25/e093e9cfc9027d6a142358d24d2ee350-65a11ac2af785880-17061562929701875684912.jpg',
            text: 'hj evelyn vibes',
            time: '4 tháng',
            likes: 4,
            isLiked: false,
            showAllReplies: false,
            replies: []
        },
        {
            username: 'Aylin💕',
            avatar: 'https://images2.thanhnien.vn/528068263637045248/2024/1/25/e093e9cfc9027d6a142358d24d2ee350-65a11ac2af785880-17061562929701875684912.jpg',
            text: "It's giving the kalogeras sisters",
            time: '6 tháng',
            likes: 7,
            isLiked: false,
            showAllReplies: false,
            replies: []
        },
        {
            username: '•ᴥ°•ᵔ Sawako ❤️',
            avatar: 'https://images2.thanhnien.vn/528068263637045248/2024/1/25/e093e9cfc9027d6a142358d24d2ee350-65a11ac2af785880-17061562929701875684912.jpg',
            text: 'i have the same <span class="text-blue-500">skirt</span>!',
            time: '2 tuần',
            likes: 0,
            isLiked: false,
            showAllReplies: false,
            replies: []
        }
        ])
        // Methods
        const toggleEmojiPicker = () => showEmojiPicker.value =!showEmojiPicker.value

        const addEmoji = (emoji) => {
            newComment.value += emoji.i;
            showEmojiPicker.value = false;
        }

        const likeComment = (comment) => {
            if (comment.isLiked) {
                comment.likes--
            } else {
                comment.likes++
            }
            comment.isLiked = !comment.isLiked;
        }

        const likeReply = (comment, reply) => {
            if (reply.isLiked) {
                reply.likes--
            } else {
                reply.likes++
            }
            reply.isLiked = !reply.isLiked;
        }
        
        const likePost = () =>
        {
            if (isLiked.value) {
                totalLikes.value--
            } else {
                totalLikes.value++
            }
            isLiked.value = !isLiked.value
        }

        const addComment = () => {
            if (newComment.value.trim() === '') return
            
            comments.value.unshift({
                username: 'you',
                avatar: 'https://images2.thanhnien.vn/528068263637045248/2024/1/25/e093e9cfc9027d6a142358d24d2ee350-65a11ac2af785880-17061562929701875684912.jpg',
                text: newComment.value,
                time: 'Vừa xong',
                likes: 0,
                isLiked: false,
                showAllReplies: false,
                replies: []
            })
            newComment.value = ''
        }

        const toggleReplies = (comment) => {
            comment.showAllReplies = !comment.showAllReplies
        }

        const startReply = (index, username) => {
            replyingToIndex.value = index
            replyingToNested.value = false
        }

        const startNestedReply = (index, username) => {
            replyingToIndex.value = index
            replyingToNested.value = true
            replyToNestedUsername.value = username
        };

        const cancelReply = () => {
            replyingToIndex.value = -1
            replyingToNested.value = false
            replyToNestedUsername.value = ''
        }

        const handleReplySubmit = (data) => {
            const { commentId, text } = data
            comments.value[commentId].replies.push({
                username: 'you',
                avatar: 'https://via.placeholder.com/32',
                text: text,
                time: 'Vừa xong',
                likes: 0,
                isLiked: false
             })
        
            // Ensure replies are visible after adding a new one
            comments.value[commentId].showAllReplies = true;
            cancelReply()
        }

        const handleNestedReplySubmit = (data) => {
            const { commentId, text } = data
            const mentionText = `<span class="font-medium text-blue-500">@${replyToNestedUsername.value}</span> ${text}`
            
            comments.value[commentId].replies.push({
                username: 'you',
                avatar: 'https://via.placeholder.com/32',
                text: mentionText,
                time: 'Vừa xong',
                likes: 0,
                isLiked: false
            })
        
            comments.value[commentId].showAllReplies = true
            cancelReply()
        }

        const previewImage = (event) =>
        {
            const imageUrl = event.target.src
            window.open(imageUrl, '_blank')
        }

        return{
            newComment,
            replyingToIndex,
            replyingToNested,
            replyToNestedUsername,
            comments,
            isLiked,
            totalLikes,
            postDate,
            likeComment,
            likeReply,
            likePost,
            addComment,
            toggleReplies,
            startReply,
            startNestedReply,
            cancelReply,
            handleReplySubmit,
            handleNestedReplySubmit,
            previewImage,
            toggleEmojiPicker,
            addEmoji,
            showEmojiPicker
        }
    }
}
</script>

<style scoped>
/* Nicer scrollbar for comment section */
div::-webkit-scrollbar {
  width: 8px;
}

div::-webkit-scrollbar-track {
  background: #f1f1f1;
}

div::-webkit-scrollbar-thumb {
  background: #ddd;
  border-radius: 4px;
}

div::-webkit-scrollbar-thumb:hover {
  background: #ccc;
}
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
emoji-picker {
  width: 300px;
  max-height: 400px;
  overflow-y: auto;
}
</style>