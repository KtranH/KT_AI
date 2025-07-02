<template>
    <div class="p-3 flex items-center relative">
        <button class="p-2" @click="toggleEmojiPicker">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-500" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM7 9a1 1 0 100-2 1 1 0 000 2zm7-1a1 1 0 11-2 0 1 1 0 012 0zm-.464 5.535a1 1 0 10-1.415-1.414 3 3 0 01-4.242 0 1 1 0 00-1.415 1.414 5 5 0 007.072 0z" clip-rule="evenodd" />
            </svg>
        </button>
        <input
            type="text"
            v-model="commentText"
            placeholder="Thêm nhận xét..."
            class="flex-1 p-2 focus:outline-none"
            @keyup.enter="submitComment"
        />
        <button
            @click="submitComment"
            class="text-blue-500 font-semibold px-2"
            :class="{'opacity-50 cursor-default': !canSubmit, 'hover:text-blue-600': canSubmit}"
            :disabled="!canSubmit"
        >
            Đăng
        </button>

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
    </div>
</template>

<script>
import { ref, computed, watch } from 'vue'
import EmojiPicker from 'vue3-emoji-picker'
import 'vue3-emoji-picker/css'
import useEmoji from '@/composables/user/useEmoji'

export default {
    name: 'CommentInput',
    components: {
        EmojiPicker
    },
    props: {
        newComment: {
            type: String,
            required: true
        }
    },
    emits: ['update:newComment', 'add-comment'],
    setup(props, { emit }) {
        const commentText = ref('')
        
        // Theo dõi thay đổi từ prop và cập nhật commentText
        watch(() => props.newComment, (newVal) => {
            commentText.value = newVal
        }, { immediate: true })
        
        // Cập nhật giá trị newComment khi commentText thay đổi
        watch(() => commentText.value, (newVal) => {
            emit('update:newComment', newVal)
        })
        
        const { showEmojiPicker, toggleEmojiPicker, addEmoji: addEmojiToText } = useEmoji(commentText)
        
        const addEmoji = (emoji) => {
            addEmojiToText(emoji)
            // Đảm bảo sự thay đổi được truyền lên component cha
            emit('update:newComment', commentText.value)
        }
        
        const canSubmit = computed(() => {
            return commentText.value && commentText.value.trim() !== ''
        })
        
        const submitComment = () => {
            if (!canSubmit.value) return
            
            emit('add-comment')
            emit('update:newComment', '')
        }

        return {
            commentText,
            canSubmit,
            submitComment,
            showEmojiPicker,
            toggleEmojiPicker,
            addEmoji,
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