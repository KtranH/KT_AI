/**
 * Các hàm tiện ích để sử dụng trong dự án
 */

/**
 * Format date theo định dạng ngày/tháng/năm
 * @param {Date|string} date - Đối tượng Date hoặc chuỗi ngày tháng
 * @returns {string} Chuỗi ngày đã định dạng
 */
export const formatDate = (date) => {
  const d = new Date(date);
  return d.toLocaleDateString('vi-VN');
};

/**
 * Rút gọn văn bản dài thành văn bản ngắn hơn với dấu "..." ở cuối
 * @param {string} text - Văn bản cần rút gọn
 * @param {number} maxLength - Độ dài tối đa
 * @returns {string} Văn bản đã rút gọn
 */
export const truncateText = (text, maxLength) => {
  if (!text) return '';
  if (text.length <= maxLength) return text;
  return text.substring(0, maxLength) + '...';
};

// Export thêm các hàm utility khác ở đây 

export const generateRandomSeed = () => {
    return Math.floor(Math.random() * 1000000000);
};

// Kiểm tra có phải là file ảnh không
export const isFileImage = (file) => {
    const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
    return allowedTypes.includes(file.type);
};

// Kiểm tra file ảnh có dưới 2MB không
export const sizeImageUnder2MB = (file) => {
    const maxSize = 2 * 1024 * 1024; // 2MB
    return file.size <= maxSize;
}; 