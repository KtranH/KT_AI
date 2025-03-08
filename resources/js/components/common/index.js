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

export {
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

// Đăng ký toàn cục các component phổ biến
export const registerGlobalComponents = (app) => {
  app.component('GuideSection', GuideSection);
  app.component('ImageParameters', ImageParameters);
  app.component('ImageViewer', ImageViewer);
  app.component('PostHeader', PostHeader);
  app.component('CommentList', CommentList);
  app.component('FeatureCard', FeatureCard);
  app.component('FeatureImage', FeatureImage);
  app.component('FeatureInfo', FeatureInfo);
  app.component('ImageUploader', ImageUploader);
  app.component('PromptInput', PromptInput);
  // Thêm các component khác ở đây khi cần
}; 