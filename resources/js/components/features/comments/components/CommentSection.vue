<template>
    <div class="md:w-2/5 md:flex md:flex-col md:border-l h-full">
        <!-- Header with profile info -->
        <HeaderSection 
            :isImageOwner="isImageOwner"
            @navigate-to-user="handleNavigateToUser" 
        />

        <!-- Loading indicator -->
        <div v-if="loading" class="flex justify-center items-center py-6">
            <span class="animate-pulse text-gray-500">Đang tải bình luận...</span>
        </div>

        <!-- Error message -->
        <div v-else-if="error" class="p-4 text-center text-red-500">
            {{ error }}
            <button @click="fetchComments" class="ml-2 text-blue-500 hover:underline">Thử lại</button>
        </div>

        <!-- Comments scrollable section - Flex grow để chiếm không gian còn lại -->
        <div class="flex-1 flex flex-col min-h-0">
            <CommentList
                v-if="!loading && !error"
                :comments="comments"
                :replyingToIndex="replyingToIndex"
                :replyingToReply="replyingToReply"
                :replyToUsername="replyToUsername"
                :replyToParentId="replyToParentId"
                :hasMoreComments="hasMoreComments"
                :loading="loading"
                :highlightCommentId="highlightCommentId"
                :isImageOwner="isImageOwner"
                :isCommentOwner="isCommentOwner"
                @reply="handleStartReply"
                @cancel-reply="handleCancelReply"
                @reply-submit="handleReplySubmit"
                @delete="handleDeleteComment"
                @update="handleUpdateComment"
                @load-more-comments="loadMoreComments"
                @load-more-replies="loadMoreReplies"
                @navigate-to-user="handleNavigateToUser"
            />
        </div>

        <!-- Post actions (like, comment, share) - Fixed ở dưới -->
        <div class="border-t bg-white">
            <LikeSection />
            
            <!-- New comment input -->
            <CommentInput
                :newComment="newComment"
                @update:newComment="newComment = $event"
                @add-comment="handleAddComment"
            />
        </div>
    </div>
</template>

<script>
import { nextTick, onMounted, watch } from 'vue'
import { HeaderSection, LikeSection } from '../../images'
import CommentList from './CommentList.vue'
import CommentInput from './CommentInput.vue'
import useComments from '@/composables/features/social/useComments'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth/authStore'

export default {
    name: 'CommentSection',
    components: {
        HeaderSection,
        CommentList,
        LikeSection,
        CommentInput
    },
    props: {
        imageId: {
            type: [Number, String],
            required: true,
            validator: function(value) {
                return value !== null && value !== undefined && value !== '';
            }
        },
        highlightCommentId: {
            type: [Number, String],
            default: null
        },
        shouldHighlight: {
            type: Boolean,
            default: false
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
    emits: ['navigate-to-user'],
    setup(props, { emit }) {
        const router = useRouter()
        const userStore = useAuthStore()
        const {
            comments,
            newComment,
            addComment,
            replyingToIndex,
            replyingToReply,
            replyToUsername,
            replyToParentId,
            startReply,
            cancelReply,
            handleReplySubmit,
            deleteComment,
            updateComment,
            loading,
            error,
            fetchComments,
            loadMoreComments,
            loadMoreReplies,
            hasMoreComments
        } = useComments(props.imageId)

        // Thêm hàm xử lý sự kiện navigate-to-user trực tiếp
        const handleNavigateToUser = (userId) => {            
            try {
                // Kiểm tra xem userId có phải là người dùng hiện tại không
                if (userId === userStore.user?.id) {
                    // Nếu là người dùng hiện tại, điều hướng đến trang dashboard cá nhân
                    router.push({ name: 'dashboard' })
                } else {
                    // Nếu là người dùng khác, điều hướng đến trang dashboard với userId
                    router.push({ 
                        name: 'dashboard', 
                        query: { userId: userId }
                    })
                }
            } catch (error) {
                console.error('CommentSection - Lỗi khi chuyển hướng:', error)
                // Vẫn emit sự kiện lên component cha trong trường hợp cần thiết
                emit('navigate-to-user', userId)
            }
        }

        onMounted(async () => {
            if (props.imageId) {
                await fetchComments();

                // Nếu có highlightCommentId, tìm và xử lý comment đó
                if (props.highlightCommentId) {
                    await nextTick();

                    // Tìm comment trong danh sách
                    const commentIndex = comments.value.findIndex(c => c.id == props.highlightCommentId);

                    // Nếu tìm thấy và cần highlight, đưa lên đầu danh sách
                    if (commentIndex > 0 && props.shouldHighlight) {
                        // Lấy comment ra khỏi danh sách
                        const highlightedComment = comments.value[commentIndex];
                        comments.value.splice(commentIndex, 1);
                        // Đưa lên đầu danh sách
                        comments.value.unshift(highlightedComment);

                        // Đánh dấu để highlight
                        highlightedComment.is_highlighted = true;

                        // Sau 3 giây, bỏ highlight
                        setTimeout(() => {
                            highlightedComment.is_highlighted = false;
                        }, 3000);
                    }

                    // Cuộn đến comment (dù đã đưa lên đầu hay không)
                    await nextTick();
                    const commentElement = document.getElementById(`comment-${props.highlightCommentId}`);
                    if (commentElement) {
                        commentElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                }
            } else {
                console.log("imageId không tồn tại hoặc không hợp lệ")
            }
        });

        // Watch prop changes
        watch(() => props.imageId, (newValue, oldValue) => {
            if (newValue && newValue !== oldValue) {
                fetchComments();
            }
        });

        const handleStartReply = (index, username, replyId = null) => {
            if (!props.imageId) return
            startReply(index, username, replyId)
        }

        const handleCancelReply = () => {
            cancelReply()
        }

        const handleAddComment = () => {
            addComment()
            nextTick(() => {
                newComment.value = ''
            })
        }

        const handleDeleteComment = (data) => {
            deleteComment(data.commentId, data.isReply, data.parentIndex)
        }

        const handleUpdateComment = (data) => {
            updateComment(data.commentId, data.content)
        }

        return {
            comments,
            newComment,
            replyingToIndex,
            replyingToReply,
            replyToUsername,
            replyToParentId,
            loading,
            error,
            fetchComments,
            handleStartReply,
            handleCancelReply,
            handleReplySubmit,
            handleDeleteComment,
            handleUpdateComment,
            loadMoreReplies,
            handleAddComment,
            loadMoreComments,
            hasMoreComments,
            handleNavigateToUser
        }
    }
}
</script>