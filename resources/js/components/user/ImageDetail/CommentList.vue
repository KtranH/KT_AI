<template>
    <div class="flex-1 overflow-y-auto">
        <!-- Không có bình luận -->
        <div v-if="!comments || comments.length === 0" class="flex justify-center items-center h-full">
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
                :replyingToReply="replyingToReply"
                :replyToUsername="replyToUsername"
                :replyToParentId="replyToParentId"
                :highlightCommentId="highlightCommentId"
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