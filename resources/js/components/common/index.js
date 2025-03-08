import GuideSection from './GuideSection.vue';
import ImageParameters from './ImageParameters.vue';
import ImageViewer from './ImageViewer.vue';
import PostHeader from './PostHeader.vue';
import CommentList from './CommentList.vue';
import FeatureCard from './FeatureCard.vue';
import FeatureImage from './FeatureImage.vue';
import FeatureInfo from './FeatureInfo.vue';
import ImageUploader from './ImageUploader.vue';
import PromptInput from './PromptInput.vue';
import Button from './Button.vue';

// Export tất cả component để import cục bộ khi cần
export {
  Button,
  GuideSection,
  ImageParameters,
  ImageViewer,
  PostHeader,
  CommentList,
  FeatureCard,
  FeatureImage,
  FeatureInfo,
  ImageUploader,
  PromptInput
};

// Component được sử dụng phổ biến và cần đăng ký toàn cục
const CORE_COMPONENTS = {
  ImageViewer,
  Button
};

// Đăng ký các component cần thiết toàn cục để tối ưu hiệu năng
export const registerGlobalComponents = (app) => {
  Object.entries(CORE_COMPONENTS).forEach(([name, component]) => {
    app.component(name, component);
  });
};