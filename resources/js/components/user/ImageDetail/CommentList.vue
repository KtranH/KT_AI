<template>
    <div class="flex-1 overflow-y-auto" style="max-height: 380px">
        <div class="space-y-4 p-4">
            <CommentItem
                v-for="(comment, index) in comments"
                :key="index"
                :comment="comment"
                :index="index"
                :replyingToIndex="replyingToIndex"
                :replyingToNested="replyingToNested"
                :replyToNestedUsername="replyToNestedUsername"
                @reply="handleReply"
                @nested-reply="handleNestedReply"
                @cancel-reply="handleCancelReply"
                @reply-submit="handleReplySubmit"
                @nested-reply-submit="handleNestedReplySubmit"
            />
        </div>
    </div>
</template>

<script>
import { defineProps } from 'vue'
import CommentItem from './CommentItem.vue'

export default {
    name: 'CommentList',
    components: {
        CommentItem
    },
    props: {
        comments: {
            type: Array,
            required: true
        },
        replyingToIndex: {
            type: Number,
            required: true
        },
        replyingToNested: {
            type: Boolean,
            required: true
        },
        replyToNestedUsername: {
            type: String,
            required: true
        }
    },
    emits: ['reply', 'nested-reply', 'cancel-reply', 'reply-submit', 'nested-reply-submit'],
    setup(props, { emit }) {
        const handleReply = (index, username) => {
            emit('reply', index, username)
        }

        const handleNestedReply = (index, username) => {
            emit('nested-reply', index, username)
        }

        const handleCancelReply = () => {
            emit('cancel-reply')
        }

        const handleReplySubmit = (data) => {
            emit('reply-submit', data)
        }

        const handleNestedReplySubmit = (data) => {
            emit('nested-reply-submit', data)
        }

        return {
            handleReply,
            handleNestedReply,
            handleCancelReply,
            handleReplySubmit,
            handleNestedReplySubmit
        }
    }
}
</script>

<style scoped>
/* Nicer scrollbar for comment section */
div::-webkit-scrollbar {
  width: 8px;
}

div::-webkit-scrollbar-track {
  background: #f1f1f1;
}

div::-webkit-scrollbar-thumb {
  background: #ddd;
  border-radius: 4px;
}

div::-webkit-scrollbar-thumb:hover {
  background: #ccc;
}
</style> 