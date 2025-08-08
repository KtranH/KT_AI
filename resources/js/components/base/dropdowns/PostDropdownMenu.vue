<template>
    <BaseDropdownMenu
        :isOpen="isOpen"
        :buttonClass="'focus:outline-none p-2 hover:bg-gray-100 rounded-full transition-colors'"
        :menuClass="'absolute right-0 mt-2 w-40 bg-white rounded-lg shadow-lg z-50 border border-gray-200'"
        @toggle="$emit('toggle')"
    >
        <template #trigger>
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path d="M6 10a2 2 0 11-4 0 2 2 0 014 0zM12 10a2 2 0 11-4 0 2 2 0 014 0zM16 12a2 2 0 100-4 2 2 0 000 4z" />
            </svg>
        </template>

        <template #menu-items>
            <!-- Menu cho chủ bài viết -->
            <div class="py-1" v-if="isOwner">
                <button @click="handleEdit" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors">
                    <i class="fa-solid fa-pen-to-square mr-2"></i> Sửa bài viết
                </button>
                <button @click="handleDelete" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors">
                    <i class="fa-solid fa-trash mr-2"></i> Xóa bài viết
                </button>
            </div>
            
            <!-- Menu cho người dùng khác -->
            <div class="py-1" v-else>
                <button @click="handleReport" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors">
                    <i class="fa-solid fa-bell mr-2"></i> Báo cáo
                </button>
            </div>
        </template>
    </BaseDropdownMenu>
</template>

<script>
import BaseDropdownMenu from './BaseDropdownMenu.vue'

export default {
    name: 'PostDropdownMenu',
    components: {
        BaseDropdownMenu
    },
    props: {
        isOpen: {
            type: Boolean,
            default: false
        },
        isOwner: {
            type: Boolean,
            default: false
        }
    },
    emits: ['toggle', 'edit', 'delete', 'report'],
    setup(props, { emit }) {
        const handleEdit = () => {
            emit('edit')
            // Tự động đóng menu sau khi click
            emit('toggle')
        }

        const handleDelete = () => {
            emit('delete')
            // Tự động đóng menu sau khi click
            emit('toggle')
        }

        const handleReport = () => {
            emit('report')
            // Tự động đóng menu sau khi click
            emit('toggle')
        }

        return {
            handleEdit,
            handleDelete,
            handleReport
        }
    }
}
</script>
