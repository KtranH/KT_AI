/**
 * Các hàm tiện ích để sử dụng trong dự án
 */

/**
 * Format date theo định dạng ngày/tháng/năm
 * @param {Date|string} date - Đối tượng Date hoặc chuỗi ngày tháng
 * @returns {string} Chuỗi ngày đã định dạng
 */

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

// Kiểm tra có phải là file ảnh JPG, PNG không
export function isFileImage(file) {
  return file && file.type && (file.type.startsWith('image/jpeg') || file.type.startsWith('image/png'))
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

import { format } from 'date-fns';
import vi from 'date-fns/locale/vi';

// Hàm định dạng ngày
export const formatDate = (dateString) => {
  try {
    return format(new Date(dateString), 'dd MMM yyyy, HH:mm', { locale: vi });
  } catch (error) {
    return dateString;
  }
};

// Hàm định dạng ngày tháng có giờ
export const formatTime = (dateString) => {
  if (!dateString) return '';
  const date = new Date(dateString);
  return new Intl.DateTimeFormat('vi-VN', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  }).format(date);
}

// Hàm định dạng ngày tháng có giờ v2
import 'dayjs/locale/vi'
import dayjs from 'dayjs'
import relativeTime from 'dayjs/plugin/relativeTime'
dayjs.locale('vi')
dayjs.extend(relativeTime)

export const formatTimev2 = (timestamp) => {
  
  const date = dayjs(timestamp)
  
  // Nếu thông báo trong vòng 24 giờ, hiển thị "x phút/giờ trước"
  // Nếu không, hiển thị ngày tháng đầy đủ
  if (dayjs().diff(date, 'day') < 1) {
    return date.fromNow()
  } else {
    return date.format('HH:mm - DD/MM/YYYY')
  }
}

// Hàm lấy URL hiện tại
export const getCurrentURL = () => {
  return window.location.href;
}

// Hàm tính toán tổng số trang
export const calculateTotalPages = (totalItems, itemsPerPage) => {
  return Math.ceil(totalItems / itemsPerPage)
}

// Hàm tính toán các trang cần hiển thị
export const calculateDisplayedPages = (currentPage, totalPages, maxVisiblePages = 5) => {
  const pages = []
  if (totalPages <= maxVisiblePages) {
    // Nếu tổng số trang <= 5, hiển thị tất cả
    for (let i = 1; i <= totalPages; i++) {
      pages.push(i)
    }
  } else {
    // Luôn hiển thị trang đầu tiên
    pages.push(1)

    // Tính toán các trang ở giữa
    let start = Math.max(2, currentPage - 1)
    let end = Math.min(totalPages - 1, currentPage + 1)

    // Điều chỉnh start và end để luôn hiển thị 3 trang ở giữa
    if (currentPage <= 3) {
      end = 4
    } else if (currentPage >= totalPages - 2) {
      start = totalPages - 3
    }

    // Thêm dấu ... nếu cần
    if (start > 2) {
      pages.push('...')
    }

    // Thêm các trang ở giữa
    for (let i = start; i <= end; i++) {
      pages.push(i)
    }

    // Thêm dấu ... nếu cần
    if (end < totalPages - 1) {
      pages.push('...')
    }

    // Luôn hiển thị trang cuối cùng
    pages.push(totalPages)
  }

  return pages
}

import { toast } from 'vue-sonner';

// Hàm tải xuống hình ảnh
export const downloadImage = (url, filename) => {
  toast.info('Đang tải xuống ảnh...');
  
  const baseUrl = window.location.origin;
  const downloadUrl = `${baseUrl}/api/r2-download?url=${encodeURIComponent(url)}&filename=${encodeURIComponent(filename)}`;
  
  window.open(downloadUrl, '_self');
  
  setTimeout(() => {
    toast.success('Đã tải xuống ảnh thành công!');
  }, 1000);
}