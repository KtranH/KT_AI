<template>
    <div class="comment-reply-form mt-2" v-if="isReplying">
        <div class="flex space-x-2">
            <img src="https://via.placeholder.com/32" class="w-6 h-6 rounded-full" alt="Your profile" />
            <div class="flex-1 relative">
                <input 
                    type="text" 
                    v-model="replyText" 
                    class="w-full p-2 bg-gray-50 rounded-lg text-sm focus:outline-none pr-16"
                    :placeholder="`Trả lời cho ${replyToUsername}...`"
                    @keyup.enter="submitReply"
                />
                <button 
                    @click="submitReply" 
                    class="absolute right-2 top-1/2 transform -translate-y-1/2 text-blue-500 font-semibold text-sm"
                    :class="{'opacity-50 cursor-default': !replyText.trim(), 'hover:text-blue-600': replyText.trim()}"
                    :disabled="!replyText.trim()"
                >
                    Đăng
                </button>
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
import { ref, defineProps, defineEmits } from 'vue';

export default {
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
        }
    },
    emits: ['reply-submitted', 'cancel-reply'],
    setup(props, { emit }) {
        const replyText = ref('')

        const submitReply = () => {
            if (replyText.value.trim() === '') return
            
            emit('reply-submitted', {
                commentId: props.commentId,
                text: replyText.value
            });
            
            replyText.value = ''
        };

        const cancelReply = () => {
            replyText.value = ''
            emit('cancel-reply')
        };

        return {
            replyText,
            submitReply,
            cancelReply
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
</style>