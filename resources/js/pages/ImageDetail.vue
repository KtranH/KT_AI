<template>
<div class="min-h-screen bg-gray-100 pt-24" data-aos="zoom-out">
    <div class="max-w-[90%] mx-auto my-4">        
        <!-- Container with responsive design -->
        <div class="bg-white md:rounded-lg md:shadow-lg md:overflow-hidden md:max-w-8xl md:mx-auto md:my-8">
            <!-- Instagram-like layout: post image on left, details on right -->
            <div class="flex flex-col md:flex-row">
                <!-- Left column: Image -->
                <div class="md:w-3/5 bg-black flex items-center justify-center">
                    <ImageViewer 
                        :imageUrl="imageData.imageUrl || 'https://images2.thanhnien.vn/528068263637045248/2024/1/25/e093e9cfc9027d6a142358d24d2ee350-65a11ac2af785880-17061562929701875684912.jpg'"
                        :imageAlt="imageData.title || 'Fashion outfit'"
                        @preview="previewImage"
                    />
                </div>

                <!-- Right column: Post header, comments, interactions -->
                <div class="md:w-2/5 md:flex md:flex-col md:border-l">
                    <!-- Header with profile info -->
                    <PostHeader
                        :username="imageData.username || 'Test thử'"
                        :userAvatar="imageData.userAvatar || 'https://imagedelivery.net/ZeGtsGSjuQe1P3UP_zk3fQ/ede24b65-497e-4940-ea90-06cc2757a200/storedata'"
                        @go-back="goBack"
                    >
                        <template #user-badges>
                            <span class="text-purple-400 mr-1">🔮</span>
                            <span class="text-pink-500">👡</span>
                        </template>
                    </PostHeader>

                    <!-- Post Title -->
                    <div class="px-4 py-2 border-b">
                        <div class="flex items-center">
                            <h1 class="text-base font-bold">{{ imageData.title || 'Beach girl' }}</h1>
                        </div>
                    </div>

                    <!-- Comments scrollable section -->
                    <CommentList :comments="comments">
                        <template #comment-form>
                            <!-- Comment input at bottom -->
                            <div class="flex items-center mt-4 space-x-2">
                                <img src="https://randomuser.me/api/portraits/men/1.jpg" class="w-8 h-8 rounded-full" alt="Your profile" />
                                <div class="flex-1 border rounded-full overflow-hidden flex items-center bg-gray-50">
                                    <input type="text" placeholder="Add a comment..." class="flex-1 px-4 py-2 bg-transparent focus:outline-none" v-model="newComment" @keyup.enter="addComment" />
                                    <button @click="addComment" class="text-blue-500 font-semibold px-4">Post</button>
                                </div>
                            </div>
                        </template>
                    </CommentList>

                    <!-- Post interactions fixed at bottom -->
                    <div class="px-4 py-3 border-t mt-auto">
                        <div class="flex justify-between items-center">
                            <div class="flex space-x-4">
                                <button class="text-2xl" :class="{ 'text-red-500': liked }" @click="toggleLike">
                                    <span v-if="liked">❤️</span>
                                    <span v-else>🤍</span>
                                </button>
                                <button class="text-2xl">💬</button>
                                <button class="text-2xl">📤</button>
                            </div>
                            <button class="text-2xl">🔖</button>
                        </div>
                        <div class="mt-2">
                            <div class="font-semibold">{{ likeCount }} likes</div>
                            <div class="text-gray-500 text-xs mt-1">{{ formatDate(imageData.createdAt || new Date()) }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</template>

<script>
import { ref, reactive, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { ImageViewer, PostHeader, CommentList } from '../components/common';
import { formatDate } from '../utils';

export default {
    name: 'ImageDetail',
    components: {
        ImageViewer,
        PostHeader,
        CommentList
    },
    setup() {
        const router = useRouter();
        const liked = ref(false);
        const likeCount = ref(124);
        const newComment = ref('');
        
        // Dữ liệu hình ảnh (nên được lấy từ API dựa trên ID)
        const imageData = reactive({
            id: 1,
            imageUrl: 'https://images2.thanhnien.vn/528068263637045248/2024/1/25/e093e9cfc9027d6a142358d24d2ee350-65a11ac2af785880-17061562929701875684912.jpg',
            title: 'Beach girl',
            username: 'Test thử',
            userAvatar: 'https://imagedelivery.net/ZeGtsGSjuQe1P3UP_zk3fQ/ede24b65-497e-4940-ea90-06cc2757a200/storedata',
            createdAt: new Date()
        });
        
        // Danh sách bình luận
        const comments = ref([
            {
                username: 'johndoe',
                avatar: 'https://randomuser.me/api/portraits/men/42.jpg',
                text: 'This looks amazing! Love the style and colors.',
                time: '2 days ago',
                likes: 12,
                isVerified: true,
                replies: [
                    {
                        username: 'fashionblogger',
                        avatar: 'https://randomuser.me/api/portraits/women/35.jpg',
                        text: 'Totally agree! The composition is perfect.',
                        time: '1 day ago',
                        likes: 5,
                        isVerified: true
                    }
                ]
            },
            {
                username: 'creative_mind',
                avatar: 'https://randomuser.me/api/portraits/women/22.jpg',
                text: 'I need details on how you created this. The lighting is spot on!',
                time: '1 day ago',
                likes: 8,
                isVerified: false
            }
        ]);
        
        // Thêm bình luận mới
        const addComment = () => {
            if (newComment.value.trim()) {
                comments.value.push({
                    username: 'you',
                    avatar: 'https://randomuser.me/api/portraits/men/1.jpg',
                    text: newComment.value,
                    time: 'Just now',
                    likes: 0,
                    isVerified: false
                });
                newComment.value = '';
            }
        };
        
        // Chức năng thích
        const toggleLike = () => {
            liked.value = !liked.value;
            if (liked.value) {
                likeCount.value++;
            } else {
                likeCount.value--;
            }
        };
        
        // Preview ảnh ở chế độ toàn màn hình (có thể thực hiện bằng modal)
        const previewImage = () => {
            alert('Image preview functionality would open a modal or lightbox here');
        };
        
        // Quay lại trang trước
        const goBack = () => {
            router.back();
        };
        
        onMounted(() => {
            // Có thể gọi API để lấy chi tiết ảnh và bình luận
            console.log('Image detail page loaded');
        });
        
        return {
            imageData,
            comments,
            liked,
            likeCount,
            newComment,
            addComment,
            toggleLike,
            previewImage,
            goBack,
            formatDate
        };
    }
}
</script>

<style scoped>
/* Các style cần thiết có thể được thêm vào đây */
</style>