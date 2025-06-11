<template>
    
</template>
<script>
import Swal from 'sweetalert2'
export default
{
    setup() {
        const showAlert = async () => {
            const result = await Swal.fire({
                title: 'Báo cáo bài viết',
                text: "Bạn có chắc chắn muốn báo cáo bài viết này? Chúng tôi sẽ xem xét báo cáo của bạn.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Báo cáo',
                cancelButtonText: 'Hủy',
            })
            return result
        }
        return {
            showAlert
        }
    }
}
</script>
