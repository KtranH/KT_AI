import { ref, onMounted, onUnmounted } from 'vue'
import axios from 'axios'
import { useAuthStore } from '@/stores/auth/authStore'
import { toast } from 'vue-sonner'

export function useNotifications() {
    const notifications = ref([])
    const unreadCount = ref(0)
    const loading = ref(false)
    const authStore = useAuthStore()
    let echo = null

    // Lấy danh sách thông báo từ API
    const fetchNotifications = async () => {
        if (!authStore.isAuthenticated) return

        loading.value = true
        try {
            const response = await axios.get('/api/notifications')
            notifications.value = response.data.notifications
            unreadCount.value = response.data.unread_count
        } catch (error) {
            console.error('Lỗi khi tải thông báo:', error)
        } finally {
            loading.value = false
        }
    }

    // Đánh dấu thông báo đã đọc
    const markAsRead = async (notificationId) => {
        if (!authStore.isAuthenticated) return

        try {
            await axios.put(`/api/notifications/${notificationId}/read`)
            // Cập nhật trạng thái thông báo trong danh sách
            const notification = notifications.value.find(n => n.id === notificationId)
            if (notification && !notification.read_at) {
                notification.read_at = new Date().toISOString()
                unreadCount.value -= 1
            }
        } catch (error) {
            console.error('Lỗi khi đánh dấu đã đọc:', error)
        }
    }

    // Đánh dấu tất cả thông báo đã đọc
    const markAllAsRead = async () => {
        if (!authStore.isAuthenticated) return

        try {
            await axios.put('/api/notifications/mark-all-read')
            // Cập nhật tất cả thông báo trong danh sách
            notifications.value.forEach(notification => {
                if (!notification.read_at) {
                    notification.read_at = new Date().toISOString()
                }
            })
            unreadCount.value = 0
        } catch (error) {
            console.error('Lỗi khi đánh dấu tất cả đã đọc:', error)
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
        fetchNotifications,
        markAsRead,
        markAllAsRead
    }
} 