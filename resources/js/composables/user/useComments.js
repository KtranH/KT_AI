import { ref, onMounted } from 'vue'
import { commentAPI } from '@/services/api'
import { toast } from 'vue-sonner'

export default function useComments(imageId = null) {
    // State cho bình luận
    const comments = ref([])
    const newComment = ref('')
    const replyingToIndex = ref(-1)
    const replyingToNested = ref(false)
    const replyToNestedUsername = ref('')
    const loading = ref(false)
    const error = ref(null)
    const hasMoreComments = ref(false)
    const currentPage = ref(1)

    // Lấy danh sách bình luận từ API
    const fetchComments = async (page = 1) => {
        if (!imageId) return
        
        try {
            loading.value = true
            error.value = null
            
            const response = await commentAPI.getComments(imageId, page)
            
            if (!response.data) {
                throw new Error('Không có dữ liệu từ API')
            }

            const commentsData = response.data.comments || []
            
            if (page === 1) {
                comments.value = commentsData
            } else {
                comments.value = [...comments.value, ...commentsData]
            }
            
            hasMoreComments.value = response.data.hasMore || false
            currentPage.value = page
            
            // Đảm bảo mỗi bình luận có thuộc tính showAllReplies
            if (Array.isArray(comments.value)) {
                comments.value.forEach(comment => {
                    if (comment.replies && !('showAllReplies' in comment)) {
                        comment.showAllReplies = false
                    }
                })
            }
            
            console.log("Danh sách bình luận đã được tải:", comments.value)
        } catch (err) {
            console.error("Lỗi khi tải bình luận:", err)
            error.value = "Không thể tải bình luận. Vui lòng thử lại sau."
            toast.error(error.value)
        } finally {
            loading.value = false
        }
    }

    // Tải thêm bình luận
    const loadMoreComments = async () => {
        if (!hasMoreComments.value || loading.value) return
        await fetchComments(currentPage.value + 1)
    }

    // Tải thêm phản hồi cho một bình luận
    const loadMoreReplies = async (commentId) => {
        try {
            loading.value = true
            const response = await commentAPI.getComments(imageId, currentPage.value, commentId)
            
            const comment = comments.value.find(c => c.id === commentId)
            if (comment) {
                comment.replies = [...comment.replies, ...response.data.replies]
                comment.hasMoreReplies = response.data.hasMore
            }
        } catch (err) {
            console.error("Lỗi khi tải thêm phản hồi:", err)
            toast.error('Không thể tải thêm phản hồi')
        } finally {
            loading.value = false
        }
    }
    
    // Tải comments khi component được tạo nếu có imageId
    onMounted(() => {
        if (imageId) {
            fetchComments()
        }
    })

    // Thêm bình luận mới
    const addComment = async () => {
        if (newComment.value.trim() === '') return
        
        try {
            loading.value = true
            
            const commentData = {
                image_id: imageId,
                content: newComment.value,
                parent_id: null
            }
            
            const response = await commentAPI.createComment(commentData)
            
            // Thêm bình luận mới vào danh sách
            comments.value.unshift(response.data)
            newComment.value = ''
            
            toast.success('Đã thêm bình luận')
        } catch (err) {
            console.error("Lỗi khi thêm bình luận:", err)
            toast.error('Không thể thêm bình luận')
        } finally {
            loading.value = false
        }
    }

    // Xóa bình luận
    const deleteComment = async (commentId, isReply = false, parentIndex = null) => {
        try {
            loading.value = true
            
            await commentAPI.deleteComment(commentId)
            
            if (isReply && parentIndex !== null) {
                // Xóa phản hồi từ bình luận cha
                const parentComment = comments.value[parentIndex]
                const replyIndex = parentComment.replies.findIndex(reply => reply.id === commentId)
                
                if (replyIndex !== -1) {
                    parentComment.replies.splice(replyIndex, 1)
                }
            } else {
                // Xóa bình luận chính
                const index = comments.value.findIndex(comment => comment.id === commentId)
                if (index !== -1) {
                    comments.value.splice(index, 1)
                }
            }
            
            toast.success('Đã xóa bình luận')
        } catch (err) {
            console.error("Lỗi khi xóa bình luận:", err)
            toast.error('Không thể xóa bình luận')
        } finally {
            loading.value = false
        }
    }

    // Cập nhật bình luận
    const updateComment = async (commentId, content, isReply = false, parentIndex = null) => {
        try {
            loading.value = true
            
            const response = await commentAPI.updateComment(commentId, content)
            
            if (isReply && parentIndex !== null) {
                // Cập nhật phản hồi trong bình luận cha
                const parentComment = comments.value[parentIndex]
                const replyIndex = parentComment.replies.findIndex(reply => reply.id === commentId)
                
                if (replyIndex !== -1) {
                    parentComment.replies[replyIndex] = {
                        ...parentComment.replies[replyIndex],
                        text: response.data.text
                    }
                }
            } else {
                // Cập nhật bình luận chính
                const index = comments.value.findIndex(comment => comment.id === commentId)
                if (index !== -1) {
                    comments.value[index] = {
                        ...comments.value[index],
                        text: response.data.text
                    }
                }
            }
            
            toast.success('Đã cập nhật bình luận')
        } catch (err) {
            console.error("Lỗi khi cập nhật bình luận:", err)
            toast.error('Không thể cập nhật bình luận')
        } finally {
            loading.value = false
        }
    }

    // Thích hoặc bỏ thích bình luận
    const toggleLikeComment = async (comment) => {
        try {
            // Cập nhật UI trước để tương tác nhanh hơn
            const originalLiked = comment.isLiked
            const originalLikes = comment.likes
            
            comment.isLiked = !comment.isLiked
            comment.likes = comment.isLiked ? comment.likes + 1 : Math.max(0, comment.likes - 1)
            
            // Gọi API
            const response = await commentAPI.toggleLike(comment.id)
            
            // Cập nhật lại từ kết quả API
            comment.likes = response.data.likes
            comment.isLiked = response.data.isLiked
        } catch (err) {
            console.error("Lỗi khi thích/bỏ thích bình luận:", err)
            // Khôi phục trạng thái ban đầu nếu có lỗi
            comment.isLiked = originalLiked
            comment.likes = originalLikes
            
            toast.error('Không thể thích/bỏ thích bình luận')
        }
    }

    // Quản lý hiển thị phản hồi
    const toggleReplies = (comment) => {
        comment.showAllReplies = !comment.showAllReplies
    }

    // Bắt đầu trả lời bình luận
    const startReply = (index, username) => {
        console.log(`Bắt đầu trả lời bình luận của ${username} ở vị trí ${index}`)
        replyingToIndex.value = index
        replyingToNested.value = false
    }

    // Bắt đầu trả lời một phản hồi
    const startNestedReply = (index, username) => {
        console.log(`Bắt đầu trả lời phản hồi của ${username} ở vị trí ${index}`)
        replyingToIndex.value = index
        replyingToNested.value = true
        replyToNestedUsername.value = username
    }

    // Hủy trả lời
    const cancelReply = () => {
        console.log("Hủy trả lời")
        replyingToIndex.value = -1
        replyingToNested.value = false
        replyToNestedUsername.value = ''
    }

    // Gửi phản hồi cho một bình luận
    const handleReplySubmit = async (data) => {
        const { commentId, text } = data
        
        if (!text.trim()) return
        
        try {
            loading.value = true
            
            const parentComment = comments.value[commentId]
            
            const commentData = {
                image_id: imageId,
                content: text,
                parent_id: parentComment.id
            }
            
            const response = await commentAPI.createComment(commentData)
            
            // Thêm phản hồi vào bình luận cha
            if (!parentComment.replies) {
                parentComment.replies = []
            }
            
            parentComment.replies.push(response.data)
            parentComment.showAllReplies = true
            
            toast.success('Đã thêm phản hồi')
        } catch (err) {
            console.error("Lỗi khi thêm phản hồi:", err)
            toast.error('Không thể thêm phản hồi')
        } finally {
            loading.value = false
            cancelReply()
        }
    }

    // Gửi phản hồi cho một phản hồi khác (nested reply)
    const handleNestedReplySubmit = async (data) => {
        const { commentId, text } = data
        
        if (!text.trim()) return
        
        try {
            loading.value = true
            
            const parentComment = comments.value[commentId]
            const mentionText = `@${replyToNestedUsername.value} ${text}`
            
            const commentData = {
                image_id: imageId,
                content: mentionText,
                parent_id: parentComment.id
            }
            
            const response = await commentAPI.createComment(commentData)
            
            // Thêm phản hồi vào bình luận cha
            if (!parentComment.replies) {
                parentComment.replies = []
            }
            
            parentComment.replies.push(response.data)
            parentComment.showAllReplies = true
            
            toast.success('Đã thêm phản hồi')
        } catch (err) {
            console.error("Lỗi khi thêm phản hồi:", err)
            toast.error('Không thể thêm phản hồi')
        } finally {
            loading.value = false
            cancelReply()
        }
    }

    return {
        comments,
        newComment,
        replyingToIndex,
        replyingToNested,
        replyToNestedUsername,
        loading,
        error,
        hasMoreComments,
        fetchComments,
        loadMoreComments,
        loadMoreReplies,
        addComment,
        deleteComment,
        updateComment,
        toggleLikeComment,
        toggleReplies,
        startReply,
        startNestedReply,
        cancelReply,
        handleReplySubmit,
        handleNestedReplySubmit
    }
}