<template>
    <div class="flex-1 overflow-y-auto">
        <!-- Không có bình luận -->
        <div v-if="!comments || comments.length === 0" class="flex justify-center items-center h-full">
            <div class="flex flex-col items-center justify-center space-y-3 py-8">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2v-8a2 2 0 012-2h2M12 12v.01M12 16h.01M8 12h.01M16 12h.01" />
                </svg>
                <p class="text-gray-500 text-lg font-medium">Chưa có bình luận nào</p>
                <span class="text-gray-400 text-sm">Hãy là người đầu tiên để lại bình luận!</span>
            </div>
        </div>

        <!-- Danh sách bình luận -->
        <div v-else class="space-y-4 p-4">
            <CommentItem
                v-for="(comment, index) in comments"
                :key="comment.id || index"
                :comment="comment"
                :index="index"
                :replyingToIndex="replyingToIndex"
                :replyingToReply="replyingToReply"
                :replyToUsername="replyToUsername"
                :replyToParentId="replyToParentId"
                :highlightCommentId="highlightCommentId"
                :isImageOwner="isImageOwner"
                :isCommentOwner="isCommentOwner"
                @reply="handleReply"
                @cancel-reply="handleCancelReply"
                @reply-submit="handleReplySubmit"
                @delete="handleDelete"
                @update="handleUpdate"
                @load-more-replies="handleLoadMoreReplies"
                @navigate-to-user="handleNavigateToUser"
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
import CommentItem from './CommentItem.vue'

export default {
    name: 'CommentList',
    components: {
        CommentItem
    },
    props: {
        comments: {
            type: Array,
            default: () => []
        },
        replyingToIndex: {
            type: [Number, null],
            default: null
        },
        replyingToReply: {
            type: Boolean,
            default: false
        },
        replyToUsername: {
            type: String,
            default: ''
        },
        replyToParentId: {
            type: [Number, String, null],
            default: null
        },
        hasMoreComments: {
            type: Boolean,
            default: false
        },
        loading: {
            type: Boolean,
            default: false
        },
        highlightCommentId: {
            type: [Number, String],
            default: null
        },
        isImageOwner: {
            type: Boolean,
            default: false
        },
        isCommentOwner: {
            type: Function,
            default: () => false
        }
    },
    emits: [
        'reply',
        'cancel-reply',
        'reply-submit',
        'delete',
        'update',
        'load-more-comments',
        'load-more-replies',
        'navigate-to-user'
    ],
    setup(props, { emit }) {
        const handleReply = (index, username, replyId = null, originCommentId = null) => {
            emit('reply', index, username, replyId, originCommentId)
        }

        const handleCancelReply = () => {
            emit('cancel-reply')
        }

        const handleReplySubmit = (data) => {
            emit('reply-submit', data)
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

        const handleLoadMoreReplies = (commentId, index) => {
            if (index === undefined) {
                index = props.comments.findIndex(comment => comment.id === commentId);
            }

            if (index !== -1) {
                emit('load-more-replies', commentId, index);
            }
        }

        const handleNavigateToUser = (userId) => {
            emit('navigate-to-user', userId)
        }

        return {
            handleReply,
            handleCancelReply,
            handleReplySubmit,
            handleDelete,
            handleUpdate,
            handleLoadMoreComments,
            handleLoadMoreReplies,
            handleNavigateToUser
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