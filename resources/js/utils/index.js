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
export function isFileImage(file) {
  return file && file.type && file.type.startsWith('image/')
}

// Kiểm tra file ảnh có dưới 2MB không
export function isImageSizeValid(file) {
  const maxSize = 2 * 1024 * 1024; // 2MB
  return file && file.size <= maxSize
}

// Kiểm tra độ dài văn bản dưới 256 kí tự
export function isTextLengthValid(text, maxLength = 256) {
  return text && text.length <= maxLength
}

// Giải mã ID
export function decodedID(encodedID) {
  try {
    // Đảm bảo chuỗi hợp lệ trước khi decode
    if (!encodedID || typeof encodedID !== 'string' || !encodedID.trim()) {
      return encodedID; // Trả về ID gốc nếu không phải base64
    }
    
    const decodedID = atob(encodedID)
    return decodedID
  } catch (error) {
    console.error('Error decoding ID:', error)
    return encodedID; // Trả về ID gốc nếu có lỗi
  }
}

// Mã hóa ID
export function encodedID(id) {
  try {
    const encodedID = btoa(String(id))
    return encodedID
  } catch (error) {
    console.error('Error encoding ID:', error)
    return String(id); // Trả về ID gốc nếu có lỗi
  }
}

// Kiểm tra xem thao tác có quá nhanh không
export function isActionTooQuick(lastActionTime, minInterval = 500) {
  const now = Date.now()
  return lastActionTime && (now - lastActionTime < minInterval)
}


