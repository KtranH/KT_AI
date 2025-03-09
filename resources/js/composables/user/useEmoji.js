import { ref } from 'vue'

export default function useEmoji(targetRef) {
    const showEmojiPicker = ref(false)
    
    const toggleEmojiPicker = () => {
        showEmojiPicker.value = !showEmojiPicker.value
    }
    
    const addEmoji = (emoji) => {
        targetRef.value += emoji.i
        showEmojiPicker.value = false
    }
    
    return {
        showEmojiPicker,
        toggleEmojiPicker,
        addEmoji
    }
} 