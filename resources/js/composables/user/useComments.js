import { ref } from 'vue'
import axios from 'axios'
import { toast } from 'vue-sonner'

export default function useComments(imageId) {
    const comments = ref([])
    const newComment = ref('')
    const replyingToIndex = ref(-1)
    const replyingToNested = ref(false)
    const replyToNestedUsername = ref('')
    const loading = ref(false)
    const error = ref(null)
    const currentPage = ref(1)
    const hasMoreComments = ref(false)

    // Lấy danh sách bình luận từ API
    const fetchComments = async (page = 1) => {
        if (!imageId) {
            console.log('ImageId không hợp lệ:', imageId)
            return
        }
        
        try {
            loading.value = true
            error.value = null
            
            const response = await axios.get(`/api/images/${imageId}/comments`, {
                params: { page }
            })
                        
            if (!response.data) {
                throw new Error('Không có dữ liệu từ API')
            }

            const commentsData = response.data.comments || []
            hasMoreComments.value = response.data.hasMore || false
            
            if (page === 1) {
                comments.value = commentsData
            } else {
                comments.value = [...comments.value, ...commentsData]
            }
            
            currentPage.value = page
        } catch (err) {
            console.error("Lỗi khi tải bình luận:", err)
            error.value = "Không thể tải bình luận. Vui lòng thử lại sau."
            toast.error(error.value)
        } finally {
            loading.value = false
        }
    }

    // Thêm bình luận mới
    const addComment = async () => {
        if (!newComment.value.trim() || !imageId) return
        
        try {
            const response = await axios.post('/api/comments', {
                image_id: imageId,
                content: newComment.value.trim()
            })
            
            // Thêm comment mới vào đầu danh sách
            comments.value.unshift(response.data)
            newComment.value = ''
            toast.success('Đã thêm bình luận thành công!')
        } catch (err) {
            console.error("Lỗi khi thêm bình luận:", err)
            toast.error('Không thể thêm bình luận. Vui lòng thử lại sau.')
        }
    }

    // Bắt đầu trả lời bình luận
    const startReply = (index, username) => {
        replyingToIndex.value = index
        replyingToNested.value = false
        replyToNestedUsername.value = username
    }

    // Bắt đầu trả lời một reply
    const startNestedReply = (index, username) => {
        replyingToNested.value = true
        replyToNestedUsername.value = username
    }

    // Hủy trả lời
    const cancelReply = () => {
        replyingToIndex.value = -1
        replyingToNested.value = false
        replyToNestedUsername.value = ''
    }

    // Gửi reply cho một comment
    const handleReplySubmit = async (parentIndex, content) => {
        if (!content.trim() || !imageId) return
        
        try {
            const parentComment = comments.value[parentIndex]
            
            const response = await axios.post('/api/comments', {
                image_id: imageId,
                parent_id: parentComment.id,
                content: content.trim()
            })
            
            console.log('Kết quả reply comment:', response.data)
            
            // Thêm reply mới vào cuối danh sách replies của comment cha
            if (!parentComment.replies) {
                parentComment.replies = []
            }
            parentComment.replies.push(response.data)
            
            cancelReply()
            toast.success('Đã trả lời bình luận thành công!')
        } catch (err) {
            console.error("Lỗi khi trả lời bình luận:", err)
            toast.error('Không thể trả lời bình luận. Vui lòng thử lại sau.')
        }
    }

    // Xóa bình luận
    const deleteComment = async (commentId, isReply, parentIndex) => {
        try {
            await axios.delete(`/api/comments/${commentId}`)
            
            if (isReply && parentIndex !== null) {
                // Xóa reply khỏi danh sách replies của comment cha
                const parentComment = comments.value[parentIndex]
                parentComment.replies = parentComment.replies.filter(reply => reply.id !== commentId)
            } else {
                // Xóa comment chính khỏi danh sách comments
                comments.value = comments.value.filter(comment => comment.id !== commentId)
            }
            
            toast.success('Đã xóa bình luận thành công!')
        } catch (err) {
            console.error("Lỗi khi xóa bình luận:", err)
            toast.error('Không thể xóa bình luận. Vui lòng thử lại sau.')
        }
    }

    // Cập nhật bình luận
    const updateComment = async (commentId, content, isReply, parentIndex) => {
        try {
            const response = await axios.put(`/api/comments/${commentId}`, {
                content: content.trim()
            })
            
            if (isReply && parentIndex !== null) {
                // Cập nhật reply trong danh sách replies của comment cha
                const parentComment = comments.value[parentIndex]
                const replyIndex = parentComment.replies.findIndex(reply => reply.id === commentId)
                if (replyIndex !== -1) {
                    parentComment.replies[replyIndex] = response.data
                }
            } else {
                // Cập nhật comment chính trong danh sách comments
                const commentIndex = comments.value.findIndex(comment => comment.id === commentId)
                if (commentIndex !== -1) {
                    comments.value[commentIndex] = response.data
                }
            }
            
            toast.success('Đã cập nhật bình luận thành công!')
        } catch (err) {
            console.error("Lỗi khi cập nhật bình luận:", err)
            toast.error('Không thể cập nhật bình luận. Vui lòng thử lại sau.')
        }
    }

    // Toggle like comment
    const toggleLikeComment = async (commentId, isReply, parentIndex) => {
        try {
            const response = await axios.post(`/api/comments/${commentId}/toggle-like`)
            
            if (isReply && parentIndex !== null) {
                // Cập nhật likes trong reply
                const parentComment = comments.value[parentIndex]
                const replyIndex = parentComment.replies.findIndex(reply => reply.id === commentId)
                if (replyIndex !== -1) {
                    const reply = parentComment.replies[replyIndex]
                    reply.likes = response.data.likes
                    reply.isLiked = response.data.isLiked
                }
            } else {
                // Cập nhật likes trong comment chính
                const commentIndex = comments.value.findIndex(comment => comment.id === commentId)
                if (commentIndex !== -1) {
                    const comment = comments.value[commentIndex]
                    comment.likes = response.data.likes
                    comment.isLiked = response.data.isLiked
                }
            }
        } catch (err) {
            console.error("Lỗi khi thích/bỏ thích bình luận:", err)
            toast.error('Không thể thích/bỏ thích bình luận. Vui lòng thử lại sau.')
        }
    }

    // Tải thêm bình luận
    const loadMoreComments = async () => {
        if (!hasMoreComments.value || loading.value) return
        await fetchComments(currentPage.value + 1)
    }

    // Gửi nested reply
    const handleNestedReplySubmit = async (content) => {
        if (!content.trim() || !imageId || replyingToIndex.value === -1) return
        await handleReplySubmit(replyingToIndex.value, content)
    }

    return {
        comments,
        newComment,
        loading,
        error,
        replyingToIndex,
        replyingToNested,
        replyToNestedUsername,
        fetchComments,
        addComment,
        startReply,
        startNestedReply,
        cancelReply,
        handleReplySubmit,
        handleNestedReplySubmit,
        deleteComment,
        updateComment,
        toggleLikeComment,
        loadMoreComments,
        hasMoreComments
    }
}