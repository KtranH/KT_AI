<template>
    <div class="flex items-center p-4 border-b">
        <!-- Go back -->
        <ButtonBackVue customClass="flex items-center bg-gradient-text mr-2 rounded-full hover: text-white font-bold py-2 px-4 rounded-full"/>
        <img :src="currentUserImage && currentUserImage.avatar_url ? currentUserImage.avatar_url : avataUser" class="w-8 h-8 rounded-full" alt="Profile" />
        <div class="ml-3">
            <div class="flex items-center">
                <span 
                    class="font-semibold cursor-pointer hover:text-purple-800 transition-colors duration-300 ease-in-out"
                    @click="navigateToUserDashboard(currentUserImage?.id)"
                >
                    {{ currentUserImage && currentUserImage.name ? currentUserImage.name : nameUser }}
                </span>
                <div class="flex items-center ml-1">
                    <img :src="icon_title" class="w-4 h-4" alt="Icon" />
                </div>
            </div>
            <span class="text-gray-500 ml-1 text-xs">
                Đã đăng vào {{ dataImage && dataImage.created_at ? formatTime(dataImage.created_at) : 'vừa xong' }}
            </span>
            <span v-if="dataImage && dataImage.updated_at !== dataImage.created_at" class="ml-1 text-gray-500 text-xs font-medium">
                (Đã chỉnh sửa)
            </span>
        </div>
         <!-- Setting post button with dropdown -->
        <div class="ml-auto relative dropdown-container">
            <button @click="toggleDropdown" class="focus:outline-none p-2 hover:bg-gray-100 rounded-full transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M6 10a2 2 0 11-4 0 2 2 0 014 0zM12 10a2 2 0 11-4 0 2 2 0 014 0zM16 12a2 2 0 100-4 2 2 0 000 4z" />
                </svg>
            </button>
            
            <!-- Dropdown menu -->
            <div v-if="isDropdownOpen" class="absolute right-0 mt-2 w-40 bg-white rounded-lg shadow-lg z-50 border border-gray-200">
                <div class="py-1" v-if="isOwner">
                    <button @click="handleEdit" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors">
                        <i class="fa-solid fa-pen-to-square mr-2"></i> Sửa bài viết
                    </button>
                    <button @click="handleDelete" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors">
                        <i class="fa-solid fa-trash mr-2"></i> Xóa bài viết
                    </button>
                    <ConfirmDelete ref="deleteRef" />
                </div>
                <div class="py-1" v-else>
                    <button @click="handleReport" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors">
                        <i class="fa-solid fa-bell mr-2"></i> Báo cáo
                    </button>
                    <ConfirmReport ref="reportRef" />
                </div>
            </div>
        </div>
    </div>

     <!-- Post Title -->
     <div class="px-4 py-2 border-b" v-if="!isEditing">
        <div class="flex items-center">
            <h1 class="text-xl font-bold">{{dataImage && dataImage.title? dataImage.title : title}}</h1>
        </div>
        <div class="flex items-center">
            <h1 class="text-sm">{{ dataImage && dataImage.prompt ? dataImage.prompt : title }}</h1>
        </div>
    </div>

    <!-- Edit Form -->
    <div v-else class="px-4 py-2 border-b">
        <EditImageForm
            :imageData="dataImage ? dataImage : null"
            @update:success="handleUpdateSuccess"
            @cancel="isEditing = false"
        />
    </div>
</template>

<script>

import useImage from '@/composables/user/useImage'
import dayjs from 'dayjs';
import relativeTime from 'dayjs/plugin/relativeTime';
import 'dayjs/locale/vi';
import ConfirmDelete from '@/components/common/ConfirmDelete.vue'
import ConfirmReport from '@/components/common/ConfirmReport.vue'
import ButtonBackVue from '../../common/ButtonBack.vue'
import EditImageForm from './EditImageForm.vue'
import { toast } from 'vue-sonner'
import { useAuthStore } from '@/stores/auth/authStore'
import { useImageStore } from '@/stores/user/imagesStore'
import { computed, ref, onMounted, onUnmounted, watch } from 'vue'
import { useRouter } from 'vue-router'

export default {
    name: 'HeaderSection',
    components:
    {
        ConfirmDelete,
        ConfirmReport,
        ButtonBackVue,
        EditImageForm
    },
    props:
    {
        avataUser:
        {
            type: String,
            default: 'https://imagedelivery.net/ZeGtsGSjuQe1P3UP_zk3fQ/ede24b65-497e-4940-ea90-06cc2757a200/storedata'
        },
        nameUser:
        {
            type: String,
            default: 'Đang tải...'
        },
        title: {
            type: String,
            default: 'Đang tải...'
        }
    },
    emits: ['navigate-to-user'],
    setup(props, { emit }) {
        dayjs.extend(relativeTime);
        dayjs.locale('vi');

        const router = useRouter()
        const isDropdownOpen = ref(false)
        const isEditing = ref(false)
        const { dataImage, deleteImage } = useImage()
        const imageStore = useImageStore()
        const auth = useAuthStore()
        const icon_title = ref("/img/creativity.png")

        // Đảm bảo trạng thái đăng nhập được khởi tạo
        onMounted(async () => {
            try {
                // Kiểm tra xác thực
                await auth.checkAuth()
            } catch (error) {
                console.error('Lỗi khi kiểm tra xác thực:', error)
            }
            document.addEventListener('click', handleClickOutside)
        })

        const user = computed(() => auth.user)
        const deleteRef = ref(null)
        const reportRef = ref(null)

        const currentUserImage = computed(() => imageStore.currentUser)
        
        // Computed để kiểm tra xem user hiện tại có phải chủ bài viết không
        const isOwner = computed(() => {
            const currentUser = auth.user.value
            const postOwner = dataImage.value && dataImage.value.user ? dataImage.value.user : null
            
            if (!currentUser || !postOwner) {
                return false
            }
            
            // Chuyển về cùng kiểu dữ liệu để so sánh
            const currentUserId = String(currentUser.id)
            const postOwnerId = String(postOwner.id)
            
            const isMatch = currentUserId === postOwnerId
            
            return isMatch
        })

        // Thêm hàm để điều hướng đến trang dashboard của người dùng
        const navigateToUserDashboard = (userId) => {
            if (!userId) return
            
            // Emit sự kiện để gọi hàm ở component cha
            emit('navigate-to-user', userId)
        }

        const formatTime = (timestamp) => {
            return dayjs(timestamp).fromNow();
        }

        const toggleDropdown = () => {
            isDropdownOpen.value = !isDropdownOpen.value
        }

        const handleClickOutside = (event) => {
            if (isDropdownOpen.value && !event.target.closest('.dropdown-container')) {
                isDropdownOpen.value = false
            }
        }

        onUnmounted(() => {
            document.removeEventListener('click', handleClickOutside)
        })

        const handleDelete = async () => {
            if (deleteRef.value) {
                const result = await deleteRef.value.showAlert()
                if (result.isConfirmed) {
                    try {
                        const imageId = dataImage.value && dataImage.value.id ? dataImage.value.id : null
                        if (!imageId) {
                            toast.error('Không tìm thấy ID ảnh để xóa')
                            return
                        }
                        
                        await deleteImage(imageId)
                        toast.success('Đã xóa bài viết thành công!')
                        router.go(-1)
                    } catch (error) {
                        console.error('Lỗi khi xóa bài viết:', error)
                        toast.error('Có lỗi xảy ra khi xóa bài viết')
                    }
                }
            }
            isDropdownOpen.value = false
        }

        const handleReport = async () => {
            if (reportRef.value) {
                const result = await reportRef.value.showAlert()
                if (result.isConfirmed) {
                    toast.success('Cảm ơn bạn đã báo cáo. Chúng tôi sẽ xem xét bài viết này sớm nhất có thể.')
                }
            }
            isDropdownOpen.value = false
        }

        const handleEdit = () => {
            isEditing.value = true
            isDropdownOpen.value = false
        }

        const handleUpdateSuccess = () => {
            isEditing.value = false
            toast.success('Đã cập nhật bài viết thành công!')
        }

        return {
            dataImage,
            currentUserImage,
            formatTime,
            isDropdownOpen,
            isEditing,
            icon_title,
            toggleDropdown,
            handleEdit,
            handleDelete,
            handleReport,
            handleUpdateSuccess,
            user,
            deleteRef,
            reportRef,
            navigateToUserDashboard,
            isOwner
        }
    }
}
</script>