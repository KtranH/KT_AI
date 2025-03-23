<template>
    
</template>

<script>
import Swal from 'sweetalert2'
export default
{
    setup() {
        const showAlert = async () => {
            const result = await Swal.fire({
                title: 'Xác nhận',
                text: "Bạn có chắc chắn muốn thay đổi không?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Có, tôi chắc chắn',
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
