<template>
    <div class="flex items-center p-4 border-b">
        <!-- Go back -->
        <ButtonBackVue customClass="flex items-center bg-gradient-text mr-2 rounded-full hover: text-white font-bold py-2 px-4 rounded-full"/>
        <img :src="currentUserImage && currentUserImage.avatar_url ? currentUserImage.avatar_url : avataUser" class="w-8 h-8 rounded-full" alt="Profile" />
        <div class="ml-3">
            <div class="flex items-center">
                <span class="font-semibold">{{ currentUserImage && currentUserImage.name ? currentUserImage.name : nameUser }}</span>
                <div class="flex items-center ml-1">
                    <span class="text-purple-400 mr-1">🔮</span>
                    <span class="text-pink-500">👡</span>
                </div>
            </div>
            <span class="text-gray-500 ml-1 text-xs">Đã đăng vào {{ dataImage && dataImage.created_at ? formatTime(dataImage.created_at) : 'vừa xong' }}</span>
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
                <div class="py-1" v-if="user && currentUserImage && user.id === currentUserImage.id">
                    <button @click="handleEdit" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                        <i class="fa-solid fa-pen-to-square"></i> Sửa bài viết
                    </button>
                    <ConfirmUpdate ref="updateRef" />
                    <button @click="handleDelete" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                        <i class="fa-solid fa-trash"></i> Xóa bài viết
                    </button>
                    <ConfirmDelete ref="deleteRef" />
                </div>
                <div class="py-1" v-else>
                    <button @click="handleReport" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                        <i class="fa-solid fa-bell"></i> Báo cáo bài viết
                    </button>
                    <ConfirmReport ref="reportRef" />
                </div>
            </div>
        </div>
    </div>

    <!-- Post Title -->
    <div class="px-4 py-2 border-b">
        <div class="flex items-center">
            <h1 class="text-base font-bold">{{ dataImage && dataImage.prompt ? dataImage.prompt : title }}</h1>
        </div>
    </div>
</template>

<script>

import useImage from '@/composables/user/useImage'
import { useAuthStore } from '@/stores/auth/authStore'
import { useImageStore } from '@/stores/user/imagesStore'
import { computed, ref, onMounted, onUnmounted } from 'vue'
import dayjs from 'dayjs';
import relativeTime from 'dayjs/plugin/relativeTime';
import 'dayjs/locale/vi';
import ConfirmUpdate from '@/components/common/ConfirmUpdate.vue'
import ConfirmDelete from '@/components/common/ConfirmDelete.vue'
import ConfirmReport from '@/components/common/ConfirmReport.vue'
import ButtonBackVue from '../../common/ButtonBack.vue'

export default {
    name: 'HeaderSection',
    components:
    {
        ConfirmUpdate,
        ConfirmDelete,
        ConfirmReport,
        ButtonBackVue
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
        const { dataImage } = useImage()
        const imageStore = useImageStore()
        const auth = useAuthStore()
        const user = computed(() => auth.user)
        const updateRef = ref(null)
        const deleteRef = ref(null)
        const reportRef = ref(null)
        
        const currentUserImage = computed(() => imageStore.currentUser)
        
        const formatTime = (time) => {
            if (!time) return 'vừa xong';
            return dayjs(time).fromNow()
        }

        const toggleSetting = () => {
            isOpen.value = !isOpen.value
        }

        const toggleDropdown = () => {
            isDropdownOpen.value = !isDropdownOpen.value
        }

        const handleEdit = () => {
            isDropdownOpen.value = false
            updateRef.value.showAlert()
            console.log('Edit post clicked')
        }

        const handleReport = () => {
            isDropdownOpen.value = false
            reportRef.value.showAlert()
            console.log('Report post clicked')
        }

        const handleDelete = () => {
            isDropdownOpen.value = false
            deleteRef.value.showAlert()
            console.log('Delete post clicked')
        }
        
        // Xử lý đóng dropdown khi click bên ngoài
        const handleClickOutside = (event) => {
            if (isDropdownOpen.value && !event.target.closest('.ml-auto')) {
                isDropdownOpen.value = false
            }
        }
        
        onMounted(() => {
            document.addEventListener('click', handleClickOutside)
        })
        
        onUnmounted(() => {
            document.removeEventListener('click', handleClickOutside)
        })
        
        return {
            dataImage,
            currentUserImage,
            formatTime,
            toggleSetting,
            isDropdownOpen,
            toggleDropdown,
            handleEdit,
            handleDelete,
            handleReport,
            user,
            updateRef,
            deleteRef,
            reportRef
        }
    }
}
</script>

<style scoped>
.bg-gradient-text {
  background: linear-gradient(
    -45deg,
    #3b82f6,
    #6366f1,
    #8b5cf6,
    #ec4899,
    #3b82f6
  );
  background-size: 400%;
  animation: gradient-animation 8s ease infinite;
}

@keyframes gradient-animation {
  0% { background-position: 0% 50%; }
  50% { background-position: 100% 50%; }
  100% { background-position: 0% 50%; }
}
</style> 