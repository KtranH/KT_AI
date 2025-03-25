<template>
    <div class="md:w-2/5 md:flex md:flex-col md:border-l">
        <!-- Header with profile info -->
        <HeaderSection />

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
            :replyingToNested="replyingToNested"
            :replyToNestedUsername="replyToNestedUsername"
            @reply="handleStartReply"
            @nested-reply="handleStartNestedReply"
            @cancel-reply="handleCancelReply"
            @reply-submit="handleReplySubmit"
            @nested-reply-submit="handleNestedReplySubmit"
            @delete="handleDeleteComment"
            @update="handleUpdateComment"
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
import { nextTick, onMounted } from 'vue'
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
            replyingToNested, 
            replyToNestedUsername, 
            startReply, 
            startNestedReply, 
            cancelReply, 
            handleReplySubmit, 
            handleNestedReplySubmit,
            deleteComment,
            updateComment,
            toggleLikeComment,
            loading,
            error,
            fetchComments
        } = useComments(props.imageId)
        
        onMounted(() => {
            if (props.imageId) {
                fetchComments();
            }
        });

        const handleStartReply = (index, username) => {
            startReply(index, username)
        }

        const handleStartNestedReply = (index, username) => {
            startNestedReply(index, username)
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
            replyingToNested,
            replyToNestedUsername,
            loading,
            error,
            fetchComments,
            handleStartReply,
            handleStartNestedReply,
            handleCancelReply,
            handleReplySubmit,
            handleNestedReplySubmit,
            handleAddComment,
            handleDeleteComment,
            handleUpdateComment
        }
    }
}
</script> 