import { ref, onMounted, onUnmounted, reactive, toRefs } from 'vue'
import axios from 'axios'
import { useAuthStore } from '@/stores/auth/authStore'
import { toast } from 'vue-sonner'

export function useNotifications(withPagination = false) {
    const notifications = ref([])
    const unreadCount = ref(0)
    const loading = ref(false)
    const pagination = reactive({
        current_page: 1,
        last_page: 1,
        per_page: 10,
        total: 0
    })
    const authStore = useAuthStore()
    let echo = null

    // Lấy danh sách thông báo
    const fetchNotifications = async (page = 1) => {
        if (!authStore.isAuthenticated) return

        loading.value = true
        try {
            const response = await axios.get('/api/notifications', {
                params: {
                    page: page,
                    per_page: withPagination ? 10 : 5, // Số lượng item trên mỗi trang
                    paginate: withPagination // Có phân trang hay không
                }
            })
            // Kiểm tra cấu trúc dữ liệu và xử lý tương ứng
            if (response.data) {
                if (withPagination) {
                    // Ưu tiên cấu trúc Laravel Pagination (có 'data' key)
                    if (response.data.data !== undefined) {
                        notifications.value = Array.isArray(response.data.data) ? response.data.data : []
                        pagination.current_page = response.data.current_page || 1
                        pagination.last_page = response.data.last_page || 1
                        pagination.per_page = response.data.per_page || 10
                        pagination.total = response.data.total || 0
                        // Cập nhật unreadCount nếu có trong response chính
                        if (response.data.unread_count !== undefined) {
                            unreadCount.value = response.data.unread_count
                        }
                    } 
                    // Xử lý cấu trúc cũ { notifications, pagination, unread_count }
                    else if (response.data.notifications !== undefined) {
                        notifications.value = Array.isArray(response.data.notifications) ? response.data.notifications : []
                        if (response.data.pagination) {
                            pagination.current_page = response.data.pagination.current_page || 1
                            pagination.last_page = response.data.pagination.last_page || 1
                            pagination.per_page = response.data.pagination.per_page || 10
                            pagination.total = response.data.pagination.total || 0
                        }
                        if (response.data.unread_count !== undefined) {
                            unreadCount.value = response.data.unread_count
                        }
                    } else {
                        // Trường hợp không khớp cấu trúc nào
                        console.warn('Cấu trúc dữ liệu thông báo không nhận dạng được:', response.data)
                        notifications.value = []
                    }
                } else {
                    // Không phân trang, xử lý như mảng đơn giản (thường là response.data.notifications)
                    notifications.value = Array.isArray(response.data.notifications) ? response.data.notifications : (Array.isArray(response.data) ? response.data : [])
                    // Cập nhật unreadCount nếu có
                     if (response.data.unread_count !== undefined) {
                         unreadCount.value = response.data.unread_count
                     }
                }
            } else {
                // Nếu không có dữ liệu
                notifications.value = []
                pagination.current_page = 1
                pagination.last_page = 1
                pagination.total = 0
            }
            
            // Đếm số thông báo chưa đọc
            updateUnreadCount()
        } catch (error) {
            console.error('Lỗi khi lấy thông báo:', error)
        } finally {
            loading.value = false
        }
    }

    // Đánh dấu một thông báo là đã đọc
    const markAsRead = async (notificationId) => {
        if (!authStore.isAuthenticated) return

        try {
            await axios.post(`/api/notifications/${notificationId}/read`)
            
            // Cập nhật trạng thái thông báo trong danh sách hiện tại
            const index = notifications.value.findIndex(n => n.id === notificationId)
            if (index !== -1) {
                notifications.value[index].read_at = new Date().toISOString()
            }
            
            // Cập nhật số lượng thông báo chưa đọc
            updateUnreadCount()
        } catch (error) {
            console.error('Lỗi khi đánh dấu thông báo đã đọc:', error)
        }
    }

    // Đánh dấu tất cả thông báo là đã đọc
    const markAllAsRead = async () => {
        if (!authStore.isAuthenticated) return

        try {
            await axios.post('/api/notifications/mark-all-read')
            
            // Cập nhật trạng thái tất cả thông báo trong danh sách hiện tại
            notifications.value.forEach(notification => {
                if (!notification.read_at) {
                    notification.read_at = new Date().toISOString()
                }
            })
            
            // Cập nhật số lượng thông báo chưa đọc
            unreadCount.value = 0
        } catch (error) {
            console.error('Lỗi khi đánh dấu tất cả thông báo đã đọc:', error)
        }
    }

    // Lấy số lượng thông báo chưa đọc
    const fetchUnreadCount = async () => {
        if (!authStore.isAuthenticated) return

        try {
            const response = await axios.get('/api/notifications/unread-count')
            unreadCount.value = response.data.count
        } catch (error) {
            console.error('Lỗi khi lấy số lượng thông báo chưa đọc:', error)
        }
    }

    // Cập nhật số lượng thông báo chưa đọc từ danh sách hiện tại
    const updateUnreadCount = () => {
        // Kiểm tra notifications.value có phải là mảng không
        if (Array.isArray(notifications.value)) {
            unreadCount.value = notifications.value.filter(n => !n.read_at).length
        } else {
            console.warn('notifications.value không phải là mảng:', notifications.value)
            unreadCount.value = 0
        }
    }

    // Thiết lập Echo để lắng nghe sự kiện thông báo
    const setupEchoListeners = () => {
        if (!window.Echo || !authStore.isAuthenticated || !authStore.user) {
            console.log('Không thể thiết lập Echo: Echo không tồn tại hoặc người dùng chưa xác thực')
            return
        }

        // Kiểm tra đảm bảo user ID tồn tại
        if (!authStore.user.value.id) {
            console.log('Không thể thiết lập Echo: ID người dùng không tồn tại')
            return
        }

        try {
            echo = window.Echo.private(`App.Models.User.${authStore.user.value.id}`)
                .notification((notification) => {
                    // Thêm thông báo mới vào đầu danh sách
                    notifications.value.unshift(notification)
                    unreadCount.value += 1
                    
                    // Hiển thị thông báo trên giao diện
                    showNotification(notification)
                })
                
            console.log('Đã thiết lập kênh thông báo cho user:', authStore.user.value.id)
        } catch (error) {
            console.error('Lỗi khi thiết lập lắng nghe thông báo:', error)
        }
    }
    
    // Hiển thị thông báo (sử dụng Notification API hoặc thay thế)
    const showNotification = (notification) => {
        // Sử dụng browser notification nếu được phép
        if ('Notification' in window && Notification.permission === 'granted') {
            try {
                new Notification('KT-AI', {
                    body: notification.message || 'Bạn có thông báo mới',
                    icon: '/img/voice.png',
                })
            } catch (error) {
                console.error('Lỗi hiển thị browser notification:', error)
                showFallbackNotification(notification)
            }
        } else {
            // Sử dụng phương án thay thế
            showFallbackNotification(notification)
        }
    }
    
    // Phương án thay thế khi không dùng được browser notification
    const showFallbackNotification = (notification) => {
        // Hiển thị thông báo dùng toast từ vue-sonner
        toast.success(notification.message || 'Bạn có thông báo mới', {
            duration: 5000,
            position: 'top-right',
            icon: '🔔',
            description: 'Nhấp vào đây để xem chi tiết',
            onDismiss: () => {
                // Tùy chọn - xử lý khi người dùng đóng toast
            },
            onClick: () => {
                // Mở panel thông báo khi nhấp vào toast
                fetchNotifications()
            }
        })
        
        console.log('Thông báo mới:', notification.message || 'Bạn có thông báo mới')
    }

    // Xóa listeners khi component unmounted
    const cleanupEchoListeners = () => {
        if (echo) {
            echo.stopListening('notification')
            echo = null
        }
    }

    // Yêu cầu quyền thông báo từ trình duyệt
    const requestNotificationPermission = async () => {
        if (!('Notification' in window)) {
            console.log('Trình duyệt không hỗ trợ thông báo')
            return
        }
        
        // Nếu đã được cấp quyền, không cần làm gì thêm
        if (Notification.permission === 'granted') {
            return
        }
        
        // Nếu quyền không bị chặn (chưa bị từ chối), thử yêu cầu
        if (Notification.permission !== 'denied') {
            try {
                const permission = await Notification.requestPermission()
                console.log('Trạng thái quyền thông báo:', permission)
            } catch (error) {
                console.error('Lỗi khi yêu cầu quyền thông báo:', error)
            }
        } else {
            console.log('Quyền thông báo đã bị từ chối trước đó')
        }
    }

    onMounted(() => {
        fetchNotifications()
        setupEchoListeners()
        requestNotificationPermission()
    })

    onUnmounted(() => {
        cleanupEchoListeners()
    })

    return {
        notifications,
        unreadCount,
        loading,
        pagination: toRefs(pagination),
        fetchNotifications,
        markAsRead,
        markAllAsRead,
        fetchUnreadCount
    }
}