<template>
     <div>
        <button @click="goBack" :class="customClass">
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
        </button>
    </div>
</template>
<script>
import useNavigation from '@/composables/user/useNavigation'

export default {
    name: 'ButtonBack',
    props: {
        customClass:String
    },
    setup() {
        const { goBack } = useNavigation()
        return { goBack }
    }
}
</script>
<style scoped>
    .transition-all {
        transition-property: all;
        transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
        transition-duration: 150ms;
    }
    /* Gradient background animation */
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
