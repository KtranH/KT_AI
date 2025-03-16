import { ref, computed } from 'vue'
import { useImageStore } from '@/stores/user/imagesStore'

export default function useImage() {
    const imageUrls = computed(() => useImageStore().images)
    const dataImage = computed(() => useImageStore().data)
    const userImage = computed(() => useImageStore().user)
    return {
        imageUrls,
        dataImage,
        userImage
    }
}
