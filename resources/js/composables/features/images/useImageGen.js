import { imageJobsAPI } from '@/services/api'
import { profileAPI } from '@/services/api'
import { toast } from 'vue-sonner'
import { generateRandomSeed } from '@/utils/index'

export function useImageGen() {
    // Lấy danh sách tiến trình đang hoạt động
    const fetchActiveJobs = async (activeJobs) => {
        try {
            const response = await imageJobsAPI.getActiveJobs()
            
            if (response.data && response.data.success) {
                // Cấu trúc mới: {success: true, data: {active_jobs: [...], count: X}, message: null}
                activeJobs.value = response.data.data.active_jobs || []
            }
        } catch (error) {
            console.error('Lỗi khi lấy tiến trình:', error)
        }
    }

    // Kiểm tra tiến trình đã hoàn thành
    const checkCompletedJobs = async (successfulJob, notifiedJobIds) => {
        try {
            // Lấy danh sách completed jobs mới nhất
            const response = await imageJobsAPI.getCompletedJobs(1, 10) // Lấy 10 job mới nhất
            
            if (response.data && response.data.success) {
                const completedJobs = response.data.data.jobs || []
                
                if (completedJobs.length > 0) {
                    // Tìm job mới nhất có result_image_url và chưa được thông báo
                    const jobsWithImage = completedJobs.filter(job => 
                        job.status === 'completed' &&
                        (job.result_image_url || job.result_image) && 
                        !notifiedJobIds.value.has(job.id)
                    )
                    
                    if (jobsWithImage.length > 0) {
                        // Lấy job mới nhất (đầu tiên trong danh sách đã sắp xếp theo thời gian)
                        const latestJobWithImage = jobsWithImage[0]
                        
                        // Kiểm tra thời gian tạo job (chỉ hiển thị job trong vòng 5 phút qua)
                        const currentTime = new Date().getTime()
                        const jobCreatedTime = new Date(latestJobWithImage.created_at).getTime()
                        const timeDiff = (currentTime - jobCreatedTime) / 1000 // chuyển đổi sang giây
                        
                        if (timeDiff < 300) { // Chỉ lấy kết quả trong vòng 5 phút = 300 giây
                            successfulJob.value = latestJobWithImage
                            notifiedJobIds.value.add(latestJobWithImage.id)
                            toast.success('Đã tạo ảnh thành công!')
                        }
                    }
                }
            }
        } catch (error) {
            console.error('Lỗi khi kiểm tra tiến trình hoàn thành:', error)
        }
    }

    // Hủy tiến trình
    const cancelJob = async (jobId, activeJobs) => {
        try {
            const response = await imageJobsAPI.cancelJob(jobId)
            if (response.data && response.data.success) {
                toast.success('Đã hủy tiến trình thành công')
                // Cập nhật lại danh sách activeJobs
                await fetchActiveJobs(activeJobs)
            }
        } catch (error) {
            toast.error('Lỗi khi hủy tiến trình')
            console.error('Lỗi khi hủy tiến trình:', error)
        }
    }



    // Tạo ảnh
    const generateImage = async (params) => {
        const {
            user,
            prompt,
            randomSeed,
            mainImage,
            secondaryImage,
            width,
            height,
            selectedOption,
            featureId,
            error_message,
            isGenerating,
            feature,
            activeJobs
        } = params

        // Kiểm tra các điều kiện
        if (!prompt.value.trim()) {
            error_message.value = "Vui lòng nhập mô tả để tạo ảnh"
            toast.error("Vui lòng nhập mô tả để tạo ảnh")
            return
        }
        
        if (feature.value?.input_requirements && !mainImage.value) {
            error_message.value = "Vui lòng tải lên ảnh chính"
            toast.error("Vui lòng tải lên ảnh chính")
            return
        }
        
        if (feature.value?.input_requirements === 2 && !secondaryImage.value) {
            error_message.value = "Vui lòng tải lên ảnh phụ"
            toast.error("Vui lòng tải lên ảnh phụ")
            return
        }
        
        // Kiểm tra số lượng tiến trình
        if (activeJobs.value.length >= 5) {
            error_message.value = "Bạn đã đạt đến giới hạn 5 tiến trình cùng lúc. Vui lòng đợi cho đến khi một số tiến trình hoàn thành."
            toast.error("Đã đạt giới hạn tiến trình")
            return
        }

        try {
            // Đánh dấu đang tạo ảnh
            isGenerating.value = true
            error_message.value = null
            
            // Gửi API tới server
            const formData = new FormData()
            formData.append('prompt', prompt.value)
            formData.append('width', width.value)
            formData.append('height', height.value)
            formData.append('seed', randomSeed.value)
            formData.append('style', selectedOption.value)
            formData.append('feature_id', featureId.value)
            
            if (mainImage.value) {
                formData.append('main_image', mainImage.value)
            }
            
            if (secondaryImage.value) {
                formData.append('secondary_image', secondaryImage.value)
            }
            
            const response = await imageJobsAPI.createJob(formData)
            
            if (response.data && response.data.success) {
                // Success message sẽ được hiển thị từ message của response
                toast.success(response.data.message || 'Tiến trình tạo ảnh đã được khởi tạo')
                console.log('Đã tạo job mới:', response.data.data)
                
                // Reset form sau khi tạo thành công
                prompt.value = ''
                randomSeed.value = generateRandomSeed()
                mainImage.value = null
                secondaryImage.value = null
                
                // Cập nhật danh sách tiến trình
                await fetchActiveJobs(activeJobs)
            } else {
                toast.error(response.data?.message || 'Lỗi khi tạo ảnh')
            }
        } catch (error) {
            console.error('Lỗi khi tạo ảnh:', error)
            
            // Hiển thị lỗi từ server nếu có
            if (error.response && error.response.data && error.response.data.message) {
                error_message.value = error.response.data.message
                toast.error(error.response.data.message)
            } else {
                error_message.value = 'Lỗi khi gửi yêu cầu tạo ảnh'
                toast.error('Lỗi khi gửi yêu cầu tạo ảnh')
            }
        } finally {
            isGenerating.value = false
        }
    }

    return {
        fetchActiveJobs,
        checkCompletedJobs,
        cancelJob,
        generateImage,
        generateRandomSeed
    }
}