<template>
    <div class="flex space-x-3">
        <img :src="comment.avatar" class="w-8 h-8 rounded-full" :alt="comment.username" />
        <div class="flex-1">
            <div class="flex items-start">
                <span class="font-semibold mr-2">{{ comment.username }}</span>
                <span class="flex-1" v-html="comment.text"></span>
            </div>
            <div class="flex items-center text-xs text-gray-500 mt-1">
                <span>{{ comment.time }}</span>
                <span class="mx-1">•</span>
                <button class="font-medium hover:underline" @click="onReply(index, comment.username)">Trả lời</button>
                <div class="flex items-center ml-2">
                    <button @click="onLikeComment(comment)" class="flex items-center focus:outline-none">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" :class="{'text-red-500 fill-current': comment.isLiked}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                        </svg>
                        <span class="ml-1">{{ comment.likes }}</span>
                    </button>
                </div>
            </div>

            <!-- Reply form -->
            <CommentReply
                v-if="replyingToIndex === index"
                :commentId="index"
                :replyToUsername="comment.username"
                :isReplying="replyingToIndex === index"
                @reply-submitted="onReplySubmit"
                @cancel-reply="onCancelReply"
            />

            <!-- Replies -->
            <div v-if="comment.replies && comment.replies.length > 0" class="mt-2 ml-4">
                <div v-if="!comment.showAllReplies && comment.replies.length > 1" class="text-xs text-blue-500 hover:underline cursor-pointer mt-1" @click="comment.showAllReplies = true">
                    Xem {{ comment.replies.length }} câu trả lời
                </div>
                
                <div v-if="comment.showAllReplies || comment.replies.length === 1" class="space-y-3 mt-2">
                    <!-- Comment Reply Item -->
                    <div v-for="(reply, replyIndex) in comment.replies" :key="replyIndex" class="flex space-x-2">
                        <img :src="reply.avatar" class="w-6 h-6 rounded-full" :alt="reply.username" />
                        <div class="flex-1">
                            <div class="flex items-start">
                                <span class="font-semibold mr-2">{{ reply.username }}</span>
                                <span class="flex-1" v-html="reply.text"></span>
                            </div>
                            <div class="flex items-center text-xs text-gray-500 mt-1">
                                <span>{{ reply.time }}</span>
                                <span class="mx-1">•</span>
                                <button class="font-medium hover:underline" @click="onNestedReply(index, reply.username)">Trả lời</button>
                                <div class="flex items-center ml-2">
                                    <button @click="onLikeReply(comment, reply)" class="flex items-center focus:outline-none">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" :class="{'text-red-500 fill-current': reply.isLiked}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                                        </svg>
                                        <span class="ml-1">{{ reply.likes }}</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Nested reply form -->
                    <CommentReply
                        v-if="replyingToIndex === index && replyingToNested"
                        :commentId="index"
                        :replyToUsername="replyToNestedUsername"
                        :isReplying="replyingToIndex === index && replyingToNested"
                        @reply-submitted="onNestedReplySubmit"
                        @cancel-reply="onCancelReply"
                    />
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { defineProps, defineEmits } from 'vue'
import CommentReply from '@/components/ImageDetail/ReplyLayout.vue'
import useLikes from '@/composables/useLikes'

export default {
    name: 'CommentItem',
    components: {
        CommentReply
    },
    props: {
        comment: {
            type: Object,
            required: true
        },
        index: {
            type: Number,
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
        const { likeComment, likeReply } = useLikes()

        const onReply = (index, username) => {
            emit('reply', index, username)
        }

        const onNestedReply = (index, username) => {
            emit('nested-reply', index, username)
        }

        const onCancelReply = () => {
            emit('cancel-reply')
        }

        const onReplySubmit = (data) => {
            emit('reply-submit', data)
        }

        const onNestedReplySubmit = (data) => {
            emit('nested-reply-submit', data)
        }

        const onLikeComment = (comment) => {
            likeComment(comment)
        }

        const onLikeReply = (comment, reply) => {
            likeReply(comment, reply)
        }

        return {
            onReply,
            onNestedReply,
            onCancelReply,
            onReplySubmit,
            onNestedReplySubmit,
            onLikeComment,
            onLikeReply
        }
    }
}
</script> 