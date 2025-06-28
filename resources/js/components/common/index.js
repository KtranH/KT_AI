import Button from './Button.vue';

// Export tất cả component để import cục bộ khi cần
export {
  Button,
};

// Component được sử dụng phổ biến và cần đăng ký toàn cục
const CORE_COMPONENTS = {
  Button
};

// Đăng ký các component cần thiết toàn cục để tối ưu hiệu năng
export const registerGlobalComponents = (app) => {
  Object.entries(CORE_COMPONENTS).forEach(([name, component]) => {
    app.component(name, component);
  });
};