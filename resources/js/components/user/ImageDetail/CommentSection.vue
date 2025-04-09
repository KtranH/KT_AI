<template>
    <div class="md:w-2/5 md:flex md:flex-col md:border-l">
        <!-- Header with profile info -->
        <HeaderSection />

        <!-- Debugging info
        <div class="p-2 bg-gray-200 text-xs">
            <p>Image ID: {{ imageId }} ({{ typeof imageId }})</p>
        </div>-->

        <!-- Loading indicator -->
        <div v-if="loading" class="flex justify-center items-center py-6">
            <span class="animate-pulse text-gray-500">Đang tải bình luận...</span>
        </div>

        <!-- Error message -->
        <div v-else-if="error" class="p-4 text-center text-red-500">
            {{ error }}
            <button @click="fetchComments" class="ml-2 text-blue-500 hover:underline">Thử lại</button>
        </div>

        <!-- Comments scrollable section -->
        <CommentList 
            v-else
            :comments="comments"
            :replyingToIndex="replyingToIndex"
            :replyingToReply="replyingToReply"
            :replyToUsername="replyToUsername"
            :replyToParentId="replyToParentId"
            :hasMoreComments="hasMoreComments"
            :loading="loading"
            @reply="handleStartReply"
            @cancel-reply="handleCancelReply"
            @reply-submit="handleReplySubmit"
            @delete="handleDeleteComment"
            @update="handleUpdateComment"
            @load-more-comments="loadMoreComments"
            @load-more-replies="loadMoreReplies"
        />

        <!-- Post actions (like, comment, share) -->
        <LikeSection />

        <!-- New comment input -->
        <CommentInput 
            :newComment="newComment"
            @update:newComment="newComment = $event"
            @add-comment="handleAddComment"
        />
    </div>
</template>

<script>
import { nextTick, onMounted, watch } from 'vue'
import HeaderSection from './HeaderSection.vue'
import CommentList from './CommentList.vue'
import LikeSection from './LikeSection.vue'
import CommentInput from './CommentInput.vue'
import useComments from '@/composables/user/useComments'

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
        }
    },
    setup(props) {        
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
        
        onMounted(async () => {
            if (props.imageId) {
                await fetchComments();
                console.log(hasMoreComments.value)
            } else {
                console.log("imageId không tồn tại hoặc không hợp lệ")
            }
        });

        // Watch prop changes
        watch(() => props.imageId, (newValue, oldValue) => {
            console.log("imageId changed:", oldValue, "->", newValue);
            if (newValue && newValue !== oldValue) {
                console.log("Refreshing comments due to imageId change");
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
            updateComment(data.commentId, data.content, data.isReply, data.parentIndex)
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
            hasMoreComments
        }
    }
}
</script>