/**
 * Directive click-outside cho Vue 3
 * Được sử dụng để phát hiện click bên ngoài phần tử
 */

export const clickOutside = {
  mounted(el, binding) {
    el._clickOutside = (event) => {
      // Kiểm tra xem click có xảy ra bên ngoài phần tử hay không
      if (!(el === event.target || el.contains(event.target))) {
        // Nếu click bên ngoài, gọi hàm được bind
        binding.value(event);
      }
    };
    
    // Đăng ký event listener
    document.addEventListener('click', el._clickOutside);
  },
  
  unmounted(el) {
    // Hủy đăng ký event listener khi component bị hủy
    document.removeEventListener('click', el._clickOutside);
  }
};

export default clickOutside; 