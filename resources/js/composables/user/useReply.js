import { ref } from 'vue'

export default function useReply(props, emit) {
    // State cho form trả lời
    const replyText = ref('')

    /**
     * Gửi phản hồi
     */
    const submitReply = () => {
        if (replyText.value.trim() === '') return
        
        emit('reply-submitted', {
            commentId: props.commentId,
            text: replyText.value.trim()
        })
        
        replyText.value = ''
    }

    /**
     * Hủy phản hồi
     */
    const cancelReply = () => {
        replyText.value = ''
        emit('cancel-reply')
    }

    return {
        replyText,
        submitReply,
        cancelReply
    }
} 