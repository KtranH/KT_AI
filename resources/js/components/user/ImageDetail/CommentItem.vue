<template>
    <div class="flex space-x-3">
        <img :src="comment.avatar" class="w-8 h-8 rounded-full" :alt="comment.username" />
        <div class="flex-1">
            <div class="flex items-start">
                <span class="font-semibold mr-2">{{ comment.username }}</span>
                
                <!-- Nội dung bình luận - chế độ xem -->
                <span v-if="!isEditing" class="flex-1" v-html="comment.text"></span>
                
                <!-- Form chỉnh sửa bình luận -->
                <div v-else class="flex-1">
                    <div class="flex w-full">
                        <input
                            type="text"
                            v-model="editText"
                            class="flex-1 p-1 border rounded-md text-sm"
                            @keyup.enter="submitEdit"
                        />
                        <div class="flex space-x-1 ml-1">
                            <button 
                                @click="submitEdit" 
                                class="text-blue-500 text-sm font-medium px-2 py-1 hover:bg-blue-50 rounded"
                            >
                                Lưu
                            </button>
                            <button 
                                @click="cancelEdit" 
                                class="text-gray-500 text-sm px-2 py-1 hover:bg-gray-50 rounded"
                            >
                                Hủy
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="flex items-center text-xs text-gray-500 mt-1">
                <span>{{ comment.time }}</span>
                <span class="mx-1">•</span>
                <button class="font-medium hover:underline" @click="onReply(index, comment.username)">Trả lời</button>
                
                <!-- Nút xóa và sửa chỉ hiển thị cho chủ sở hữu bình luận -->
                <template v-if="comment.isOwner">
                    <button class="font-medium ml-2 hover:underline" @click="confirmDelete">Xóa</button>
                    <button class="font-medium ml-2 hover:underline" @click="startEdit">Sửa</button>
                </template>
                
                <div class="flex items-center ml-2">
                    <button @click="onLikeComment(comment)" class="flex items-center focus:outline-none">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" :class="{'text-red-500 fill-current': comment.isLiked}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                        </svg>
                        <span class="ml-1">{{ comment.likes }}</span>
                    </button>
                </div>
            </div>

            <!-- Xác nhận xóa bình luận -->
            <div v-if="showDeleteConfirm" class="mt-2 p-2 bg-gray-50 rounded-lg">
                <p class="text-sm text-gray-800">Bạn có chắc muốn xóa bình luận này?</p>
                <div class="flex space-x-2 mt-2">
                    <button @click="deleteComment" class="px-3 py-1 bg-red-500 text-white text-xs rounded hover:bg-red-600">Xóa</button>
                    <button @click="showDeleteConfirm = false" class="px-3 py-1 bg-gray-200 text-gray-800 text-xs rounded hover:bg-gray-300">Hủy</button>
                </div>
            </div>

            <!-- Replies -->
            <div v-if="comment.replies && comment.replies.length > 0" class="mt-2 ml-4">
                <div v-if="!comment.showReplies && comment.reply_count > 3" class="text-xs text-blue-500 font-bold hover:underline cursor-pointer mt-1" @click="showAllReplies">
                    Xem {{ comment.reply_count }} câu trả lời
                </div>
                
                <div v-if="comment.showReplies || comment.reply_count <= 3" class="space-y-3 mt-2">
                    <!-- Comment Reply Item -->
                    <div v-for="(reply, replyIndex) in comment.replies" :key="reply.id" class="flex space-x-2">
                        <img :src="reply.avatar" class="w-6 h-6 rounded-full" :alt="reply.username" />
                        <div class="flex-1">
                            <div class="flex items-start">
                                <span class="font-semibold mr-2">{{ reply.username }}</span>
                                
                                <!-- Nội dung phản hồi - chế độ xem -->
                                <span v-if="!isEditingReply[replyIndex]" class="flex-1">
                                    <span v-if="reply.reply_to && reply.reply_to.id != reply.userid" class="text-blue-500 mr-1">@{{ reply.reply_to.name }}</span>
                                    <span v-html="reply.text"></span>
                                </span>
                                
                                <!-- Form chỉnh sửa phản hồi -->
                                <div v-else class="flex-1">
                                    <div class="flex w-full">
                                        <input
                                            type="text"
                                            v-model="editReplyText"
                                            class="flex-1 p-1 border rounded-md text-sm"
                                            @keyup.enter="submitReplyEdit(reply, replyIndex)"
                                        />
                                        <div class="flex space-x-1 ml-1">
                                            <button 
                                                @click="submitReplyEdit(reply, replyIndex)" 
                                                class="text-blue-500 text-sm font-medium px-2 py-1 hover:bg-blue-50 rounded"
                                            >
                                                Lưu
                                            </button>
                                            <button 
                                                @click="cancelReplyEdit(replyIndex)" 
                                                class="text-gray-500 text-sm px-2 py-1 hover:bg-gray-50 rounded"
                                            >
                                                Hủy
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center text-xs text-gray-500 mt-1">
                                <span>{{ reply.time }}</span>
                                <span class="mx-1">•</span>
                                <button class="font-medium hover:underline" @click="onReplyToReply(index, reply.username, reply.id)">Trả lời</button>
                                
                                <!-- Nút xóa và sửa chỉ hiển thị cho chủ sở hữu phản hồi -->
                                <template v-if="reply.isOwner">
                                    <button class="font-medium ml-2 hover:underline" @click="confirmReplyDelete(replyIndex)">Xóa</button>
                                    <button class="font-medium ml-2 hover:underline" @click="startReplyEdit(reply, replyIndex)">Sửa</button>
                                </template>
                                
                                <div class="flex items-center ml-2">
                                    <button @click="onLikeReply(reply)" class="flex items-center focus:outline-none">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" :class="{'text-red-500 fill-current': reply.isLiked}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                                        </svg>
                                        <span class="ml-1">{{ reply.likes }}</span>
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Xác nhận xóa phản hồi -->
                            <div v-if="showDeleteReplyConfirm[replyIndex]" class="mt-2 p-2 bg-gray-50 rounded-lg">
                                <p class="text-sm text-gray-800">Bạn có chắc muốn xóa phản hồi này?</p>
                                <div class="flex space-x-2 mt-2">
                                    <button @click="deleteReply(reply)" class="px-3 py-1 bg-red-500 text-white text-xs rounded hover:bg-red-600">Xóa</button>
                                    <button @click="cancelReplyDelete(replyIndex)" class="px-3 py-1 bg-gray-200 text-gray-800 text-xs rounded hover:bg-gray-300">Hủy</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Nút xem thêm phản hồi -->
                    <div v-if="comment.hasMoreReplies" class="flex justify-center mt-2">
                        <button 
                            @click="loadMoreReplies(comment.id)"
                            class="text-blue-500 hover:text-blue-600 text-sm font-medium"
                            :disabled="loading"
                        >
                            {{ loading ? 'Đang tải...' : 'Xem thêm phản hồi' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Reply form cho phản hồi lồng nhau -->
    <CommentReply
        ref="replyNestedRef"
        v-if="replyingToReply && replyingToIndex === index && replyToParentId"
        :commentId="replyToParentId"
        :replyToUsername="replyToUsername"
        :isReplying="replyingToReply && replyingToIndex === index"
        @reply-submitted="onReplySubmit"
        @cancel-reply="onCancelReply"
    />
    <!-- Reply form cho phản hồi bình luận gốc -->
    <CommentReply
        ref="replyRef"
        v-if="replyingToIndex === index && !replyingToReply"
        :commentId="comment.id"
        :replyToUsername="replyToUsername"
        :isReplying="replyingToIndex === index"
        @reply-submitted="onReplySubmit"
        @cancel-reply="onCancelReply"
    />
</template>

<script>
import { ref, nextTick } from 'vue'
import CommentReply from '@/components/user/ImageDetail/ReplyLayout.vue'
import useLikes from '@/composables/user/useLikes'

export default {
    name: 'CommentItem',
    components: {
        CommentReply
    },
    props: {
        comment: Object,
        index: Number,
        replyingToIndex: {
            type: Number,
            default: -1
        },
        replyingToReply: {
            type: Boolean,
            default: false
        },
        replyToParentId: {
            type: [Number, String, null],
            default: null
        },
        replyToUsername: String,
        loading: {
            type: Boolean,
            default: false
        }
    },
    emits: [
        'reply', 
        'reply-submit', 
        'cancel-reply', 
        'delete',
        'load-more-replies',
        'update'
    ],
    setup(props, { emit }) {
        const { likeComment, likeReply } = useLikes()

        // Tham chiếu tới DOM của phản hồi và phản hồi lồng nhau
        const replyRef = ref(null)
        const replyNestedRef = ref(null)
                
        // State cho chỉnh sửa và xóa
        const isEditing = ref(false)
        const editText = ref('')
        const showDeleteConfirm = ref(false)
        
        // State cho chỉnh sửa và xóa phản hồi
        const isEditingReply = ref({})
        const editReplyText = ref('')
        const showDeleteReplyConfirm = ref({})

        // Xử lý trả lời bình luận
        const onReply = (index, username) => {
            emit('reply', index, username)
            
            // Sử dụng nextTick để đảm bảo DOM đã được cập nhật
            nextTick(() => {
                setTimeout(() => {
                    if (replyRef.value) {
                        // Cuộn đến form reply với behavior smooth và block start
                        replyRef.value.$el.scrollIntoView({ 
                            behavior: 'smooth', 
                            block: 'nearest'
                        })
                        
                        // Sử dụng phương thức focus trực tiếp từ component con
                        if (typeof replyRef.value.focus === 'function') {
                            replyRef.value.focus()
                        }
                    }
                }, 100)
            })
        }

        // Xử lý trả lời phản hồi
        const onReplyToReply = (index, username, replyId) => {
            emit('reply', index, username, replyId)
            
            // Sử dụng nextTick để đảm bảo DOM đã được cập nhật
            nextTick(() => {
                setTimeout(() => {
                    if (replyNestedRef.value) {
                        // Cuộn đến form reply với behavior smooth và block start
                        replyNestedRef.value.$el.scrollIntoView({ 
                            behavior: 'smooth', 
                            block: 'nearest'
                        })
                        
                        // Sử dụng phương thức focus trực tiếp từ component con
                        if (typeof replyNestedRef.value.focus === 'function') {
                            replyNestedRef.value.focus()
                        }
                    }
                }, 100)
            })
        }

        // Xử lý hủy trả lời
        const onCancelReply = () => {
            emit('cancel-reply')
        }

        const onReplySubmit = (data) => {
            // Đảm bảo data có định dạng đúng cho handleReplySubmit
            const replyData = {
                commentId: props.index,
                content: data.content
            };
            emit('reply-submit', replyData);
        }

        // Xử lý thích bình luận và phản hồi
        const onLikeComment = (comment) => {
            likeComment(comment)
        }

        const onLikeReply = (reply) => {
            likeReply(props.comment, reply)
        }
        
        // Xử lý chỉnh sửa bình luận
        const startEdit = () => {
            editText.value = props.comment.text.replace(/<[^>]*>/g, '') // Loại bỏ các thẻ HTML
            isEditing.value = true
        }
        
        const cancelEdit = () => {
            isEditing.value = false
            editText.value = ''
        }
        
        const submitEdit = () => {
            if (editText.value.trim() === '') return
            
            emit('update', {
                commentId: props.comment.id,
                content: editText.value,
                isReply: false,
                parentIndex: null
            })
            
            isEditing.value = false
        }
        
        // Xử lý xóa bình luận
        const confirmDelete = () => {
            showDeleteConfirm.value = true
        }
        
        const deleteComment = () => {
            emit('delete', {
                commentId: props.comment.id,
                isReply: false,
                parentIndex: null
            })
            
            showDeleteConfirm.value = false
        }
        
        // Xử lý chỉnh sửa phản hồi
        const startReplyEdit = (reply, replyIndex) => {
            editReplyText.value = reply.text.replace(/<[^>]*>/g, '') // Loại bỏ các thẻ HTML
            isEditingReply.value = { ...isEditingReply.value, [replyIndex]: true }
        }
        
        const cancelReplyEdit = (replyIndex) => {
            isEditingReply.value = { ...isEditingReply.value, [replyIndex]: false }
            editReplyText.value = ''
        }
        
        const submitReplyEdit = (reply, replyIndex) => {
            if (editReplyText.value.trim() === '') return
            
            emit('update', {
                commentId: reply.id,
                content: editReplyText.value,
                isReply: true,
                parentIndex: props.index
            })
            
            isEditingReply.value = { ...isEditingReply.value, [replyIndex]: false }
        }
        
        // Xử lý xóa phản hồi
        const confirmReplyDelete = (replyIndex) => {
            showDeleteReplyConfirm.value = { ...showDeleteReplyConfirm.value, [replyIndex]: true }
        }
        
        const cancelReplyDelete = (replyIndex) => {
            showDeleteReplyConfirm.value = { ...showDeleteReplyConfirm.value, [replyIndex]: false }
        }
        
        const deleteReply = (reply) => {
            emit('delete', {
                commentId: reply.id,
                isReply: true,
                parentIndex: props.index
            })
            
            // Đặt lại state
            const replyIndex = props.comment.replies.findIndex(r => r.id === reply.id)
            if (replyIndex !== -1) {
                showDeleteReplyConfirm.value = { ...showDeleteReplyConfirm.value, [replyIndex]: false }
            }
        }

        const loadMoreReplies = (commentId) => {
            emit('load-more-replies', commentId)
        }

        const showAllReplies = () => {
            // Thêm thuộc tính showReplies nếu chưa có
            if (typeof props.comment.showReplies === 'undefined') {
                props.comment.showReplies = true;
            } else {
                props.comment.showReplies = true;
            }
        }

        return {
            onReply,
            onCancelReply,
            onReplySubmit,
            onLikeComment,
            onLikeReply,
            isEditing,
            editText,
            startEdit,
            cancelEdit,
            submitEdit,
            showDeleteConfirm,
            confirmDelete,
            deleteComment,
            isEditingReply,
            editReplyText,
            startReplyEdit,
            cancelReplyEdit,
            submitReplyEdit,
            showDeleteReplyConfirm,
            confirmReplyDelete,
            cancelReplyDelete,
            deleteReply,
            loadMoreReplies,
            showAllReplies,
            onReplyToReply,
            replyRef,
            replyNestedRef
        }
    }
}
</script>

<style scoped>
/* CSS cho phản hồi lồng nhau */
.nested-reply-indicator {
    position: relative;
}

.nested-reply-indicator::before {
    content: '';
    position: absolute;
    left: -12px;
    top: 0;
    bottom: 0;
    width: 2px;
    background-color: #e5e7eb;
}
</style>