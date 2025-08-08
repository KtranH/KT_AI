<template>
    <div class="flex items-center p-4 border-b">
        <!-- Go back -->
        <ButtonBack customClass="flex items-center bg-gradient-text mr-2 rounded-full hover: text-white font-bold py-2 px-4 rounded-full"/>
        <img :src="postOwnerImage && postOwnerImage.avatar_url ? postOwnerImage.avatar_url : avataUser" class="w-8 h-8 rounded-full" alt="Profile" />
        <div class="ml-3">
            <div class="flex items-center">
                <span 
                    class="font-semibold cursor-pointer hover:text-purple-800 transition-colors duration-300 ease-in-out"
                    @click="navigateToUserDashboard(postOwnerImage?.id)"
                >
                    {{ postOwnerImage && postOwnerImage.name ? postOwnerImage.name : nameUser }}
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
        <PostDropdownMenu
            :isOpen="isDropdownOpen"
            :isOwner="isOwner"
            @toggle="toggleDropdown"
            @edit="handleEdit"
            @delete="handleDelete"
            @report="handleReport"
        />
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

import useImage from '@/composables/features/images/useImage'
import dayjs from 'dayjs';
import relativeTime from 'dayjs/plugin/relativeTime';
import 'dayjs/locale/vi';
import { ConfirmDelete, ConfirmReport, PostDropdownMenu } from '@/components/base'
import { ButtonBack } from '@/components/base'
import EditImageForm from './EditImageForm.vue'
import { toast } from 'vue-sonner'
import { useAuthStore } from '@/stores/auth/authStore'
import { useImageStore } from '@/stores/user/imagesStore'
import { computed, ref, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'

export default {
    name: 'HeaderSection',
    components:
    {
        ConfirmDelete,
        ConfirmReport,
        ButtonBack,
        EditImageForm,
        PostDropdownMenu
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
        },
        isImageOwner: {
            type: Boolean,
            default: false
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

        const user = computed(() => auth.user)
        const deleteRef = ref(null)
        const reportRef = ref(null)

        const currentUserImage = computed(() => imageStore.currentUser)
        
        // Computed để lấy thông tin user của chủ bài viết
        const postOwnerImage = computed(() => imageStore.user || imageStore.currentUser)
        
        // Sử dụng prop isImageOwner thay vì logic riêng
        const isOwner = computed(() => props.isImageOwner)

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
            postOwnerImage,
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