<template>
    <div class="flex items-center p-4 border-b">
        <!-- Go back -->
        <ButtonBackVue customClass="flex items-center bg-gradient-text mr-2 rounded-full hover: text-white font-bold py-2 px-4 rounded-full"/>
        <img :src="currentUserImage && currentUserImage.avatar_url ? currentUserImage.avatar_url : avataUser" class="w-8 h-8 rounded-full" alt="Profile" />
        <div class="ml-3">
            <div class="flex items-center">
                <router-link to="#" class="font-semibold cursor-pointer hover:text-purple-800 transition-colors duration-300 ease-in-out">
                    {{ currentUserImage && currentUserImage.name ? currentUserImage.name : nameUser }}
                </router-link>
                <div class="flex items-center ml-1">
                    <img :src="icon_title" class="w-4 h-4" alt="Icon" />
                </div>
            </div>
            <span class="text-gray-500 ml-1 text-xs">Đã đăng vào {{ dataImage && dataImage.created_at ? formatTime(dataImage.created_at) : 'vừa xong' }}</span>
            <span v-if="dataImage && dataImage.updated_at !== dataImage.created_at" class="ml-1 text-gray-500 text-xs font-medium">(Đã chỉnh sửa)</span>
        </div>
         <!-- Setting post button with dropdown -->
        <div class="ml-auto relative">
            <button @click="toggleDropdown" class="focus:outline-none">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M6 10a2 2 0 11-4 0 2 2 0 014 0zM12 10a2 2 0 11-4 0 2 2 0 014 0zM16 12a2 2 0 100-4 2 2 0 000 4z" />
                </svg>
            </button>
            <!-- Dropdown menu -->
            <div v-if="isDropdownOpen" class="absolute right-0 mt-2 w-32 bg-white rounded-lg shadow-lg z-10">
                <div class="py-1" v-if="user.value.id === currentUserImage.id">
                    <button @click="handleEdit" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                        <i class="fa-solid fa-pen-to-square"></i> Sửa bài viết
                    </button>
                    <button @click="handleDelete" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                        <i class="fa-solid fa-trash"></i> Xóa bài viết
                    </button>
                    <ConfirmDelete ref="deleteRef" />
                </div>
                <div class="py-1" v-else>
                    <button @click="handleReport" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                        <i class="fa-solid fa-bell"></i> Báo cáo
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
            :imageData="dataImage"
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
            default: 'Lỗi 404'
        },
        title: {
            type: String,
            default: 'Lỗi 404'
        }
    },
    setup() {
        dayjs.extend(relativeTime);
        dayjs.locale('vi');

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
                // In ra console để debug
                console.log('Thông tin người dùng sau khi kiểm tra xác thực:', auth.user)
                console.log('Thông tin chủ bài viết:', imageStore.currentUser)
                console.log('Thông tin bài viết:', dataImage.value)
            } catch (error) {
                console.error('Lỗi khi kiểm tra xác thực:', error)
            }
            document.addEventListener('click', handleClickOutside)
        })

        const user = computed(() => auth.user)
        const deleteRef = ref(null)
        const reportRef = ref(null)

        const currentUserImage = computed(() => imageStore.currentUser)

        // Thêm console.log để debug
        watch(() => auth.user, (newUser) => {
            console.log('Auth user changed:', newUser)
        })

        watch(() => imageStore.currentUser, (newUser) => {
            console.log('Current image user changed:', newUser)
        })

        const formatTime = (time) => {
            if (!time) return 'vừa xong';
            return dayjs(time).fromNow()
        }

        const toggleDropdown = () => {
            isDropdownOpen.value = !isDropdownOpen.value
        }

        const handleEdit = () => {
            isDropdownOpen.value = false
            isEditing.value = true
            console.log('Edit post clicked')
        }

        const handleUpdateSuccess = () => {
            isEditing.value = false
            toast.success('Cập nhật bài viết thành công!')
        }

        const handleReport = () => {
            isDropdownOpen.value = false
            reportRef.value.showAlert()
            console.log('Report post clicked')
        }

        const handleDelete = async () => {
            isDropdownOpen.value = false

            // Hiển thị hộp thoại xác nhận
            const result = await deleteRef.value.showAlert()

            // Nếu người dùng xác nhận xóa
            if (result.isConfirmed) {
                console.log('Xóa bài viết có ID:', dataImage.value.id)
                await deleteImage(dataImage.value.id)
            }
        }

        // Xử lý đóng dropdown khi click bên ngoài
        const handleClickOutside = (event) => {
            if (isDropdownOpen.value && !event.target.closest('.ml-auto')) {
                isDropdownOpen.value = false
            }
        }

        onMounted(() => {
            auth.initializeAuth()
            document.addEventListener('click', handleClickOutside)
        })

        onUnmounted(() => {
            document.removeEventListener('click', handleClickOutside)
        })

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
        }
    }
}
</script>