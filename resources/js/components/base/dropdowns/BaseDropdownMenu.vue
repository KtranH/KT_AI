<template>
    <div class="relative dropdown-container">
        <!-- Button trigger -->
        <button 
            @click="toggleDropdown"
            :class="buttonClass"
        >
            <slot name="trigger">
                <!-- Default trigger icon -->
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
                </svg>
            </slot>
        </button>
        
        <!-- Dropdown menu -->
        <div 
            v-if="isOpen"
            :class="menuClass"
        >
            <slot name="menu">
                <!-- Default menu content -->
                <div class="py-1">
                    <slot name="menu-items">
                        <!-- Default menu items -->
                    </slot>
                </div>
            </slot>
        </div>
    </div>
</template>

<script>
import { onMounted, onUnmounted } from 'vue'

export default {
    name: 'BaseDropdownMenu',
    props: {
        isOpen: {
            type: Boolean,
            default: false
        },
        buttonClass: {
            type: String,
            default: 'p-1 rounded-full hover:bg-gray-100 transition-colors duration-200 opacity-0 group-hover:opacity-100'
        },
        menuClass: {
            type: String,
            default: 'absolute right-0 top-6 bg-white border border-gray-200 rounded-lg shadow-lg z-10 min-w-[120px]'
        }
    },
    emits: ['toggle'],
    setup(props, { emit }) {
        const toggleDropdown = () => {
            emit('toggle')
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
            toggleDropdown
        }
    }
}
</script>
