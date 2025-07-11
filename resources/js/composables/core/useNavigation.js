import { useRouter } from 'vue-router'

export default function useNavigation() {
    const router = useRouter()
    
    // Phương thức quay lại trang trước
    const goBack = () => {
        router.back()
    }
    
    // Phương thức xem ảnh
    const previewImage = (event) => {
        const imageUrl = event.target.src
        window.open(imageUrl, '_blank')
    }
    
    return {
        goBack,
        previewImage
    }
} 