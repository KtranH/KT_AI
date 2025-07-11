import { ref } from 'vue'

export default function useReply(props, emit) {
    // Khai báo biến lưu trữ nội dung phản hồi
    const replyText = ref('')
    
    // Phương thức gửi phản hồi
    const submitReply = () => {
        if (!replyText.value.trim()) return
        
        // Emit sự kiện reply-submitted với nội dung phản hồi
        emit('reply-submitted', {
            commentId: props.commentId,
            content: replyText.value.trim()
        })
        
        // Reset nội dung phản hồi
        replyText.value = ''
    }
    
    // Phương thức hủy phản hồi
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