<template>
    <div class="md:w-3/5 bg-black flex items-center justify-center">
        <img 
            src="https://images2.thanhnien.vn/528068263637045248/2024/1/25/e093e9cfc9027d6a142358d24d2ee350-65a11ac2af785880-17061562929701875684912.jpg" 
            class="w-full h-full md:h-[600px] md:object-contain cursor-pointer" 
            alt="Fashion outfit" 
            @click="previewImage"
        />
    </div>
</template>

<script>
import { defineProps } from 'vue'
import useNavigation from '@/composables/useNavigation'

export default {
    name: 'ImageViewer',
    props: {
        imageUrl: {
            type: String,
            default: 'https://images2.thanhnien.vn/528068263637045248/2024/1/25/e093e9cfc9027d6a142358d24d2ee350-65a11ac2af785880-17061562929701875684912.jpg'
        },
        altText: {
            type: String,
            default: 'Fashion outfit'
        }
    },
    setup() {
        const { previewImage } = useNavigation()

        return {
            previewImage
        }
    }
}
</script> 