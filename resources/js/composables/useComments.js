import { ref } from 'vue'

export default function useComments() {
    // State cho bình luận
    const newComment = ref('')
    const replyingToIndex = ref(-1)
    const replyingToNested = ref(false)
    const replyToNestedUsername = ref('')

    // Dữ liệu mẫu cho bình luận
    const comments = ref([
        {
            username: 'sasha',
            avatar: 'https://images2.thanhnien.vn/528068263637045248/2024/1/25/e093e9cfc9027d6a142358d24d2ee350-65a11ac2af785880-17061562929701875684912.jpg',
            text: "Where's that shirt from?",
            time: '6 tháng',
            likes: 10,
            isLiked: false,
            showAllReplies: false,
            replies: [
                {
                    username: 'ninn',
                    avatar: 'https://images2.thanhnien.vn/528068263637045248/2024/1/25/e093e9cfc9027d6a142358d24d2ee350-65a11ac2af785880-17061562929701875684912.jpg',
                    text: 'I believe that this one is from cider',
                    time: '6 tháng',
                    likes: 1,
                    isLiked: false
                }
            ]
        },
        {
            username: 'doll',
            avatar: 'https://images2.thanhnien.vn/528068263637045248/2024/1/25/e093e9cfc9027d6a142358d24d2ee350-65a11ac2af785880-17061562929701875684912.jpg',
            text: 'omg i JUST got the same exact <span class="text-blue-500">skirt</span> tdy',
            time: '5 tháng',
            likes: 4,
            isLiked: false,
            showAllReplies: false,
            replies: []
        },
        {
            username: 'love',
            avatar: 'https://images2.thanhnien.vn/528068263637045248/2024/1/25/e093e9cfc9027d6a142358d24d2ee350-65a11ac2af785880-17061562929701875684912.jpg',
            text: 'where did you get it from ? ❤️',
            time: '5 tháng',
            likes: 3,
            isLiked: false,
            showAllReplies: false,
            replies: []
        },
        {
            username: 'Parthivi',
            avatar: 'https://images2.thanhnien.vn/528068263637045248/2024/1/25/e093e9cfc9027d6a142358d24d2ee350-65a11ac2af785880-17061562929701875684912.jpg',
            text: 'hj evelyn vibes',
            time: '4 tháng',
            likes: 4,
            isLiked: false,
            showAllReplies: false,
            replies: []
        },
        {
            username: 'Aylin💕',
            avatar: 'https://images2.thanhnien.vn/528068263637045248/2024/1/25/e093e9cfc9027d6a142358d24d2ee350-65a11ac2af785880-17061562929701875684912.jpg',
            text: "It's giving the kalogeras sisters",
            time: '6 tháng',
            likes: 7,
            isLiked: false,
            showAllReplies: false,
            replies: []
        },
        {
            username: '•ᴥ°•ᵔ Sawako ❤️',
            avatar: 'https://images2.thanhnien.vn/528068263637045248/2024/1/25/e093e9cfc9027d6a142358d24d2ee350-65a11ac2af785880-17061562929701875684912.jpg',
            text: 'i have the same <span class="text-blue-500">skirt</span>!',
            time: '2 tuần',
            likes: 0,
            isLiked: false,
            showAllReplies: false,
            replies: []
        }
    ])

    // Phương thức quản lý bình luận
    const addComment = () => {
        if (newComment.value.trim() === '') return
        
        comments.value.unshift({
            username: 'you',
            avatar: 'https://images2.thanhnien.vn/528068263637045248/2024/1/25/e093e9cfc9027d6a142358d24d2ee350-65a11ac2af785880-17061562929701875684912.jpg',
            text: newComment.value,
            time: 'Vừa xong',
            likes: 0,
            isLiked: false,
            showAllReplies: false,
            replies: []
        })
        newComment.value = ''
    }

    const toggleReplies = (comment) => {
        comment.showAllReplies = !comment.showAllReplies
    }

    const startReply = (index, username) => {
        replyingToIndex.value = index
        replyingToNested.value = false
    }

    const startNestedReply = (index, username) => {
        replyingToIndex.value = index
        replyingToNested.value = true
        replyToNestedUsername.value = username
    }

    const cancelReply = () => {
        replyingToIndex.value = -1
        replyingToNested.value = false
        replyToNestedUsername.value = ''
    }

    const handleReplySubmit = (data) => {
        const { commentId, text } = data
        comments.value[commentId].replies.push({
            username: 'you',
            avatar: 'https://via.placeholder.com/32',
            text: text,
            time: 'Vừa xong',
            likes: 0,
            isLiked: false
        })
    
        // Đảm bảo hiển thị phản hồi sau khi thêm mới
        comments.value[commentId].showAllReplies = true
        cancelReply()
    }

    const handleNestedReplySubmit = (data) => {
        const { commentId, text } = data
        const mentionText = `<span class="font-medium text-blue-500">@${replyToNestedUsername.value}</span> ${text}`
        
        comments.value[commentId].replies.push({
            username: 'you',
            avatar: 'https://via.placeholder.com/32',
            text: mentionText,
            time: 'Vừa xong',
            likes: 0,
            isLiked: false
        })
    
        comments.value[commentId].showAllReplies = true
        cancelReply()
    }

    return {
        newComment,
        replyingToIndex,
        replyingToNested,
        replyToNestedUsername,
        comments,
        addComment,
        toggleReplies,
        startReply,
        startNestedReply,
        cancelReply,
        handleReplySubmit,
        handleNestedReplySubmit
    }
} 