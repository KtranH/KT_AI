<template>
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
</template>

<script>
import { defineProps } from 'vue'
import EmojiPicker from 'vue3-emoji-picker'
import 'vue3-emoji-picker/css'
import useComments from '@/composables/useComments'
import useEmoji from '@/composables/useEmoji'

export default {
    name: 'CommentInput',
    components: {
        EmojiPicker
    },
    setup() {
        const { newComment, addComment } = useComments()
        const { showEmojiPicker, toggleEmojiPicker, addEmoji } = useEmoji(newComment)

        return {
            newComment,
            addComment,
            showEmojiPicker,
            toggleEmojiPicker,
            addEmoji
        }
    }
}
</script>

<style scoped>
emoji-picker {
  width: 300px;
  max-height: 400px;
  overflow-y: auto;
}
</style> 