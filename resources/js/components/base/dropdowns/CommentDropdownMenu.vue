<template>
    <div class="relative dropdown-container">
        <!-- Button trigger -->
        <template v-if="isImageOwner && !isOwner">
            <button 
                @click="toggleDropdown"
                class="p-1 rounded-full hover:bg-gray-100 transition-colors duration-200 opacity-0 group-hover:opacity-100"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
                </svg>
            </button>
        </template>
        
        <!-- Dropdown menu -->
        <div 
            v-if="isOpen"
            class="absolute right-0 top-6 bg-white border border-gray-200 rounded-lg shadow-lg z-10 min-w-[120px]"
        >
            <!-- Nút báo cáo -->
            <button 
                @click="handleReport"
                class="w-full px-3 py-2 text-left text-sm text-gray-700 hover:bg-gray-50 flex items-center"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                </svg>
                Báo cáo
            </button>

            <!-- Nút xóa comment nếu là chủ bài viết -->
            <button 
                @click="handleDelete"
                class="w-full px-3 py-2 text-left text-sm text-gray-700 hover:bg-gray-50 flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
                Xóa
            </button>
        </div>
    </div>
</template>

<script>
import { ref, onMounted, onUnmounted } from 'vue'

export default {
    name: 'CommentDropdownMenu',
    props: {
        isOpen: {
            type: Boolean,
            default: false
        },
        isImageOwner: {
            type: Boolean,
            default: false
        },
        isOwner: {
            type: Boolean,
            default: false
        },
        showDelete: {
            type: Boolean,
            default: true
        }
    },
    emits: ['toggle', 'report', 'delete'],
    setup(props, { emit }) {
        const toggleDropdown = () => {
            emit('toggle')
        }

        const handleReport = () => {
            emit('toggle')
            emit('report')
        }

        const handleDelete = () => {
            emit('toggle')
            emit('delete')
        }

        const handleClickOutside = (event) => {
            if (props.isOpen && !event.target.closest('.dropdown-container')) {
                emit('toggle')
            }
        }

        onMounted(() => {
            document.addEventListener('click', handleClickOutside)
        })

        onUnmounted(() => {
            document.removeEventListener('click', handleClickOutside)
        })

        return {
            toggleDropdown,
            handleReport,
            handleDelete
        }
    }
}
</script>
