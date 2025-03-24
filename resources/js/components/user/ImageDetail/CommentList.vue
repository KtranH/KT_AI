<template>
    <div class="flex-1 overflow-y-auto" style="max-height: 380px">
        <!-- Không có bình luận -->
        <div v-if="comments.length === 0" class="flex justify-center items-center h-full">
            <p class="text-gray-500 text-center">Chưa có bình luận nào.<br>Hãy là người đầu tiên bình luận!</p>
        </div>
        
        <!-- Danh sách bình luận -->
        <div v-else class="space-y-4 p-4">
            <CommentItem
                v-for="(comment, index) in comments"
                :key="comment.id || index"
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
                @delete="handleDelete"
                @update="handleUpdate"
                @load-more-replies="handleLoadMoreReplies"
            />

            <!-- Nút xem thêm bình luận -->
            <div v-if="hasMoreComments" class="flex justify-center mt-4">
                <button 
                    @click="handleLoadMoreComments"
                    class="text-blue-500 hover:text-blue-600 font-medium"
                    :disabled="loading"
                >
                    {{ loading ? 'Đang tải...' : 'Xem thêm bình luận' }}
                </button>
            </div>
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
        },
        hasMoreComments: {
            type: Boolean,
            default: false
        },
        loading: {
            type: Boolean,
            default: false
        }
    },
    emits: [
        'reply', 
        'nested-reply', 
        'cancel-reply', 
        'reply-submit', 
        'nested-reply-submit',
        'delete',
        'update',
        'load-more-comments',
        'load-more-replies'
    ],
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
        
        const handleDelete = (data) => {
            emit('delete', data)
        }
        
        const handleUpdate = (data) => {
            emit('update', data)
        }

        const handleLoadMoreComments = () => {
            emit('load-more-comments')
        }

        const handleLoadMoreReplies = (commentId) => {
            emit('load-more-replies', commentId)
        }

        return {
            handleReply,
            handleNestedReply,
            handleCancelReply,
            handleReplySubmit,
            handleNestedReplySubmit,
            handleDelete,
            handleUpdate,
            handleLoadMoreComments,
            handleLoadMoreReplies
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