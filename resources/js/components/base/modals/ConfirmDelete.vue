<template>
    
</template>

<script>
import Swal from 'sweetalert2'
export default
{
    setup() {
        const showAlert = async () => {
            const result = await Swal.fire({
                title: 'Xóa bài viết',
                text: "Bạn có chắc chắn muốn xóa bài viết này? Hành động này không thể hoàn tác.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Xóa',
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

<style scoped></style>
