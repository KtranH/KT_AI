import { useRouter } from 'vue-router'

export default function useNavigation() {
    const router = useRouter()
    
    const goBack = () => {
        router.back()
    }
    
    const previewImage = (event) => {
        const imageUrl = event.target.src
        window.open(imageUrl, '_blank')
    }
    
    return {
        goBack,
        previewImage
    }
} 