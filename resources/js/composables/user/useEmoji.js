import { ref } from 'vue'

export default function useEmoji(textRef) {
    const showEmojiPicker = ref(false)
    
    /**
     * Hiển thị/ẩn emoji picker
     */
    const toggleEmojiPicker = () => {
        showEmojiPicker.value = !showEmojiPicker.value
    }
    
    /**
     * Thêm emoji vào văn bản
     * @param {Object} emoji - Đối tượng emoji từ emoji picker
     */
    const addEmoji = (emoji) => {
        textRef.value += emoji.i
        showEmojiPicker.value = false
    }
    
    return {
        showEmojiPicker,
        toggleEmojiPicker,
        addEmoji
    }
} 