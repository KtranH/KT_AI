import { ref } from 'vue'

export default function useReply(props, emit) {
    // State
    const replyText = ref('')

    // Methods
    const submitReply = () => {
        if (replyText.value.trim() === '') return
        
        emit('reply-submitted', {
            commentId: props.commentId,
            text: replyText.value
        })
        
        replyText.value = ''
    }

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