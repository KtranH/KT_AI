import { ref, onMounted, onUnmounted, reactive, toRefs } from 'vue'
import { useAuthStore } from '@/stores/auth/authStore'
import { toast, Toaster as VueSonner } from 'vue-sonner'
import { notificationAPI } from '@/services/api'

export function useNotifications(loadMore = false) { // Đổi tên tham số để rõ ràng hơn
    const notifications = ref([])
    const unreadCount = ref(0)
    const loading = ref(false)
    const currentPage = ref(1)
    const hasMorePages = ref(true)
    const authStore = useAuthStore()
    let echo = null

    // Lấy danh sách thông báo
    const fetchNotifications = async (page = 1, filter = 'all', append = false) => {
        if (!authStore.isAuthenticated) return

        try {
            // Chỉ hiển thị loading khi tải lần đầu hoặc khi đổi filter (không phải khi load more)
            if (!append) {
                loading.value = true 
            }
            
            const response = await notificationAPI.getNotifications(page, filter, append)
            
            if (response.data.data) {
                const newNotifications = Array.isArray(response.data.data.notifications) ? response.data.data.notifications : []
                
                if (append) {
                    // Nối thêm mảng mới vào mảng hiện tại
                    notifications.value = [...notifications.value, ...newNotifications]
                } else {
                    // Thay thế mảng hiện tại bằng mảng mới
                    notifications.value = newNotifications
                }
                
                // Cập nhật thông tin phân trang từ response
                if (response.data.data.pagination) {
                    // Cập nhật chỉ khi response có dữ liệu pagination
                    currentPage.value = response.data.data.pagination.current_page || page
                    hasMorePages.value = response.data.data.pagination.has_more_pages || 
                                      (response.data.data.pagination.current_page < response.data.data.pagination.last_page)
                } else if (response.data.data.current_page !== undefined && response.data.data.last_page !== undefined) {
                    // Hỗ trợ cả định dạng cũ nếu API chưa được cập nhật
                    currentPage.value = response.data.data.current_page
                    hasMorePages.value = response.data.data.current_page < response.data.data.last_page
                } else {
                    // Nếu không có thông tin phân trang, giả định không còn trang nào nữa
                    if (!append) {
                        currentPage.value = 1
                    }
                    hasMorePages.value = false
                }

                // Cập nhật unreadCount
                if (response.data.data.unread_count !== undefined) {
                    unreadCount.value = response.data.data.unread_count
                } else {
                    // Nếu API không trả về số lượng chưa đọc, tính toán từ dữ liệu hiện tại
                    updateUnreadCount()
                }
            } else {
                // Nếu không có dữ liệu trả về
                if (!append) {
                    notifications.value = []
                    currentPage.value = 1
                }
                hasMorePages.value = false
                console.warn('Cấu trúc dữ liệu thông báo không nhận dạng được hoặc không có dữ liệu:', response.data)
            }
        } catch (error) {
            console.error('Lỗi khi lấy thông báo:', error)
            hasMorePages.value = false // Dừng tải thêm nếu có lỗi
        } finally {
            // Tắt loading sau khi tải xong
            if (!append) {
                loading.value = false
            }
        }
    }

    // Tải thêm thông báo
    const loadMoreNotifications = async (filter = 'all') => {
        if (loading.value || !hasMorePages.value) return
        
        loading.value = true // Cập nhật trạng thái loading
        try {
            // Tải trang tiếp theo và append dữ liệu
            await fetchNotifications(currentPage.value + 1, filter, true) // true để append
        } catch (error) {
            console.error('Lỗi khi tải thêm thông báo:', error)
        } finally {
            loading.value = false // Đảm bảo reset trạng thái loading
        }
    }

    // Đánh dấu một thông báo là đã đọc
    const markAsRead = async (notificationId) => {
        if (!authStore.isAuthenticated) return

        try {
            await notificationAPI.markAsRead(notificationId)
            
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
            await notificationAPI.markAllAsRead()
            
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
            const response = await notificationAPI.getUnreadCount()
            unreadCount.value = response.data.data.count
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
        toast.success('Bạn có thông báo mới', {
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
        hasMorePages, // Trả về trạng thái còn trang hay không
        fetchNotifications,
        loadMoreNotifications, // Trả về hàm tải thêm
        markAsRead,
        markAllAsRead,
        fetchUnreadCount
    }
}