<template>
    <div class="md:w-2/5 md:flex md:flex-col md:border-l">
        <!-- Header with profile info -->
        <HeaderSection />

        <!-- Comments scrollable section -->
        <CommentList 
            :comments="comments"
            :replyingToIndex="replyingToIndex"
            :replyingToNested="replyingToNested"
            :replyToNestedUsername="replyToNestedUsername"
            @reply="handleStartReply"
            @nested-reply="handleStartNestedReply"
            @cancel-reply="handleCancelReply"
            @reply-submit="handleReplySubmit"
            @nested-reply-submit="handleNestedReplySubmit"
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
import { defineProps, nextTick } from 'vue'
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
    setup() {
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
            handleNestedReplySubmit 
        } = useComments()

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

        return {
            comments,
            newComment,
            replyingToIndex,
            replyingToNested,
            replyToNestedUsername,
            handleStartReply,
            handleStartNestedReply,
            handleCancelReply,
            handleReplySubmit,
            handleNestedReplySubmit,
            handleAddComment
        }
    }
}
</script> 