import { ref } from 'vue'
import { toast } from 'vue-sonner'
import { commentAPI } from '@/services/api'
import { isActionTooQuick } from '@/utils'

export default function useComments(imageId) {
    const comments = ref([])
    const newComment = ref('')
    const replyingToIndex = ref(-1)
    const replyingToReply = ref(false)
    const replyToUsername = ref('')
    const replyToParentId = ref(null)
    const loading = ref(false)
    const error = ref(null)
    const currentPage = ref(1)
    const hasMoreComments = ref(false)
    const lastActionTime = ref(null)

    // Lấy danh sách bình luận từ API
    const fetchComments = async (page = 1) => {
        if (!imageId) {
            console.log('ImageId không hợp lệ:', imageId)
            return
        }

        try {
            loading.value = true
            error.value = null

            const response = await commentAPI.getComments(imageId, page)

            if (!response.data) {
                throw new Error('Không có dữ liệu từ API')
            }

            // Xử lý structure response từ API
            const responseData = response.data.data || response.data
            const commentsData = responseData.comments || []
            hasMoreComments.value = responseData.hasMore || false

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
        if (lastActionTime.value && isActionTooQuick(lastActionTime.value)) {
            toast.error('Hãy đợi một chút trước khi thực hiện hành động này')
            return
        }
        if (!newComment.value.trim() || !imageId) return

        try {
            const commentData = {
                image_id: imageId,
                content: newComment.value.trim()
            }
            const response = await commentAPI.createComment(commentData)

            // Đánh dấu là bình luận mới để hiển thị highlight
            response.data.is_new = true

            // Thêm comment mới vào đầu danh sách
            comments.value.unshift(response.data)
            newComment.value = ''
            toast.success('Đã thêm bình luận thành công!')
        } catch (err) {
            console.error("Lỗi khi thêm bình luận:", err)
            toast.error('Không thể thêm bình luận. Vui lòng thử lại sau.')
        }
        lastActionTime.value = new Date()
    }

    // Bắt đầu trả lời bình luận hoặc phản hồi
    const startReply = (index, username, replyId = null) => {
        replyingToIndex.value = index
        replyToUsername.value = username

        if (replyId) {
            // Đang trả lời một phản hồi
            replyingToReply.value = true
            replyToParentId.value = replyId
        } else {
            // Đang trả lời bình luận gốc
            replyingToReply.value = false
            replyToParentId.value = null
        }
    }

    // Hủy trả lời
    const cancelReply = () => {
        replyingToIndex.value = -1
        replyingToReply.value = false
        replyToUsername.value = ''
        replyToParentId.value = null
    }

    // Gửi reply cho một comment hoặc một reply khác
    const handleReplySubmit = async (data) => {
        if (isActionTooQuick(lastActionTime.value)) {
            toast.error('Hãy đợi một chút trước khi thực hiện hành động này')
            return
        }
        if (!data || !data.content || data.content.trim() === '' || !imageId) {
            error.value = 'Bình luận không hợp lệ. Vui lòng nhập bình luận.'
            toast.error(error.value)
            return
        }

        try {
            const parentIndex = data.commentId || replyingToIndex.value;
            if (parentIndex < 0 || parentIndex >= comments.value.length) {
                throw new Error('Không tìm thấy bình luận cha');
            }

            // Xác định ID của bình luận cha
            const parentComment = comments.value[parentIndex]

            const targetCommentId = replyingToReply.value && replyToParentId.value
                ? replyToParentId.value  // Trả lời một phản hồi
                : parentComment.id;      // Trả lời bình luận gốc

            // Xác định origin_comment (bình luận gốc)
            // Nếu đang trả lời một phản hồi, origin_comment là id của bình luận gốc
            // Nếu đang trả lời bình luận gốc, origin_comment là id của bình luận đó
            const originCommentId = replyingToReply.value
                ? (parentComment.origin_comment || parentComment.id)
                : parentComment.id;

            // Tạo dữ liệu phản hồi
            const replyData = {
                content: data.content.trim(),
                origin_comment: originCommentId
            }

            // Gọi API tạo phản hồi
            const response = await commentAPI.createReply(targetCommentId, replyData)

            console.log('Kết quả phản hồi:', response.data)

            // Thêm phản hồi mới vào danh sách
            if (!parentComment.replies) {
                parentComment.replies = []
            }

            // Đánh dấu là phản hồi mới để hiển thị highlight
            response.data.is_new = true
            parentComment.replies.push(response.data)

            // Nếu đây là phản hồi cho một bình luận khác (không phải bình luận gốc)
            // và có origin_comment, đưa bình luận gốc lên đầu danh sách
            if (response.data.origin_comment && response.data.origin_comment !== parentComment.id) {
                // Tìm bình luận gốc trong danh sách
                const originCommentIndex = comments.value.findIndex(c => c.id === response.data.origin_comment)
                if (originCommentIndex !== -1) {
                    // Lấy bình luận gốc ra khỏi danh sách
                    const originComment = comments.value[originCommentIndex]
                    comments.value.splice(originCommentIndex, 1)
                    // Đưa lên đầu danh sách
                    comments.value.unshift(originComment)
                }
            }

            cancelReply()
            toast.success('Đã trả lời thành công!')
        } catch (err) {
            console.error("Lỗi khi trả lời:", err)
            toast.error('Không thể trả lời. Vui lòng thử lại sau.')
        }
        lastActionTime.value = new Date()
    }

    // Xóa bình luận
    const deleteComment = async (commentId, isReply, parentIndex) => {
        try {
            await commentAPI.deleteComment(commentId)

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
    const updateComment = async (commentId, content) => {
        try {
            await commentAPI.updateComment(commentId, content.trim())
            toast.success('Đã cập nhật bình luận thành công!')
        } catch (err) {
            console.error("Lỗi khi cập nhật bình luận:", err)
            toast.error('Không thể cập nhật bình luận. Vui lòng thử lại sau.')
        }
    }

    // Tải thêm bình luận
    const loadMoreComments = async () => {
        if (!hasMoreComments.value || loading.value) return
        await fetchComments(currentPage.value + 1)
    }

    // Tải thêm phản hồi của một bình luận
    const loadMoreReplies = async (commentId, commentIndex, page = 2) => {
        if (loading.value) return

        try {
            loading.value = true
            error.value = null

            const response = await commentAPI.getComments(imageId, page, commentId)

            if (!response.data) {
                throw new Error('Không có dữ liệu từ API')
            }

            const repliesData = response.data.replies || []
            const hasMoreReplies = response.data.hasMore || false

            // Tìm comment theo index hoặc id
            const parentComment = (commentIndex !== undefined)
                ? comments.value[commentIndex]
                : comments.value.find(c => c.id === commentId);

            if (!parentComment) {
                throw new Error('Không tìm thấy bình luận cha');
            }

            // Đảm bảo replies là một mảng
            if (!parentComment.replies) {
                parentComment.replies = []
            }

            // Thêm replies mới vào cuối danh sách replies hiện tại
            parentComment.replies = [...parentComment.replies, ...repliesData]
            parentComment.hasMoreReplies = hasMoreReplies

            toast.success('Đã tải thêm phản hồi')
        } catch (err) {
            console.error("Lỗi khi tải thêm phản hồi:", err)
            error.value = "Không thể tải thêm phản hồi. Vui lòng thử lại sau."
            toast.error(error.value)
        } finally {
            loading.value = false
        }
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
        addComment,
        startReply,
        cancelReply,
        handleReplySubmit,
        deleteComment,
        updateComment,
        loadMoreComments,
        loadMoreReplies,
        hasMoreComments
    }
}