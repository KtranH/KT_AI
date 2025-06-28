<template>
    <div class="comment-reply-form mt-2" v-if="isReplying">
        <div class="flex space-x-2">
            <img :src="user.avatar_url" class="w-6 h-6 rounded-full" alt="Your profile" />
            <!-- Container chính -->
            <div class="flex items-center w-full bg-gray-50 rounded-lg p-1 relative overflow-visible">
            <!-- Nút Emoji -->
            <button @click="toggleEmojiPicker" class="p-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-500" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM7 9a1 1 0 100-2 1 1 0 000 2zm7-1a1 1 0 11-2 0 1 1 0 012 0zm-.464 5.535a1 1 0 10-1.415-1.414 3 3 0 01-4.242 0 1 1 0 00-1.415 1.414 5 5 0 007.072 0z" clip-rule="evenodd" />
                </svg>
            </button>

            <!-- Input -->
            <input
                ref="inputRef"
                type="text"
                v-model="replyText"
                class="flex-1 bg-transparent text-sm focus:outline-none ml-2"
                :placeholder="`Trả lời cho ${replyToUsername}...`"
                @keyup.enter="handleSubmitReply"
            />

            <!-- Emoji Picker -->
            <Teleport to="body">
                <div v-if="showEmojiPicker" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-30 z-[9999]">
                <div class="bg-white p-4 rounded-lg shadow-lg relative" style="width: 320px;">
                    <button @click="showEmojiPicker = false" class="absolute top-0 right-2 text-gray-600 font-bold text-lg hover:text-gray-800">
                    ✕
                    </button>
                    <EmojiPicker @select="addEmoji" :style="{ height: '400px', width: '300px' }" />
                </div>
                </div>
            </Teleport>

            <!-- Nút Đăng -->
            <button
                @click="handleSubmitReply"
                class="absolute right-2 top-1/2 transform -translate-y-1/2 text-blue-500 font-semibold text-sm"
                :class="{'opacity-50 cursor-default': !canSubmit, 'hover:text-blue-600': canSubmit}"
                :disabled="!canSubmit"
            >
                Đăng
            </button>

            <!-- Nút Hủy -->
            <button
                @click="cancelReply"
                class="absolute right-12 top-1/2 transform -translate-y-1/2 text-gray-500 text-sm hover:text-gray-700"
            >
                Hủy
            </button>
            </div>
        </div>
    </div>
</template>

<script>
import { computed, ref, onMounted, onUpdated, nextTick } from 'vue';
import { useAuthStore } from '@/stores/auth/authStore'
import EmojiPicker from 'vue3-emoji-picker'
import 'vue3-emoji-picker/css'
import useEmoji from '@/composables/user/useEmoji'

export default {
    // Props and emits
    props: {
        commentId: {
            type: Number,
            required: true
        },
        replyToUsername: {
            type: String,
            required: true
        },
        isReplying: {
            type: Boolean,
            required: true
        },
        replyId: {
            type: [Number, String, null],
            default: null
        },
        originCommentId: {
            type: [Number, String, null],
            default: null
        }
    },
    components: {
        EmojiPicker
    },
    emits: ['reply-submitted', 'cancel-reply'],
    setup(props, { emit, expose }) {
        const authStore = useAuthStore()
        const user = authStore.user

        // Không sử dụng composable useReply để tránh xung đột
        const replyText = ref('')

        // Xử lý gửi phản hồi
        const submitReply = () => {
            if (!replyText.value.trim()) return

            // Emit sự kiện reply-submitted với nội dung phản hồi và replyId nếu có
            emit('reply-submitted', {
                commentId: props.commentId,
                content: replyText.value.trim(),
                replyId: props.replyId,
                originCommentId: props.originCommentId
            })

            // Reset nội dung phản hồi
            replyText.value = ''
        }

        // Xử lý hủy phản hồi
        const cancelReply = () => {
            replyText.value = ''
            emit('cancel-reply')
        }

        const { showEmojiPicker, toggleEmojiPicker, addEmoji } = useEmoji(replyText)

        const canSubmit = computed(() => {
            return replyText.value && replyText.value.trim() !== ''
        })

        const handleSubmitReply = () => {
            if (!canSubmit.value) return

            console.log(`Đang gửi phản hồi cho ${props.replyToUsername}:`, replyText.value)
            submitReply()
        }

        // Thêm phương thức focus để có thể gọi từ component cha
        const inputRef = ref(null)

        // Phương thức focus sẽ tập trung vào input
        const focus = () => {
            // Đảm bảo inputRef đã được gán và có phương thức focus
            nextTick(() => {
                if (inputRef.value) {
                    inputRef.value.focus()
                }
            })
        }

        // Tự động focus khi component được render
        onMounted(() => {
            nextTick(() => {
                if (props.isReplying && inputRef.value) {
                    inputRef.value.focus()
                }
            })
        })

        // Tự động focus khi isReplying thay đổi từ false sang true
        onUpdated(() => {
            if (props.isReplying && inputRef.value) {
                inputRef.value.focus()
            }
        })

        // Expose phương thức focus để component cha có thể gọi
        expose({
            focus
        })

        return {
            replyText,
            submitReply,
            cancelReply,
            showEmojiPicker,
            toggleEmojiPicker,
            addEmoji,
            canSubmit,
            handleSubmitReply,
            user,
            inputRef,
            focus
        }
    }
}
</script>

<style scoped>
/* Instagram-style focus */
input:focus {
    box-shadow: none;
    border-color: #e2e8f0;
}

/* Thêm animation khi hiển thị reply form */
.comment-reply-form {
    animation: fadeIn 0.3s ease-in-out;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(5px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>