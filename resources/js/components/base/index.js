// Base Components - Export các component cơ bản, tái sử dụng

// Buttons
import Button from './buttons/Button.vue';
import ButtonBack from './buttons/ButtonBack.vue';
import ButtonMore from './buttons/ButtonMore.vue';

// Modals
import ConfirmUpdate from './modals/ConfirmUpdate.vue';
import ConfirmReport from './modals/ConfirmReport.vue';
import ConfirmDelete from './modals/ConfirmDelete.vue';
import ConfirmUpload from './modals/ConfirmUpload.vue';

// Feedback
import LoadingState from './feedback/LoadingState.vue';

// Dropdowns
import { BaseDropdownMenu, CommentDropdownMenu, PostDropdownMenu } from './dropdowns';

// Named exports
export {
  Button,
  ButtonBack,
  ButtonMore,
  ConfirmUpdate,
  ConfirmReport,
  ConfirmDelete,
  ConfirmUpload,
  LoadingState,
  BaseDropdownMenu,
  CommentDropdownMenu,
  PostDropdownMenu
};

// Component để đăng ký toàn cục nếu cần
export const BASE_COMPONENTS = {
  Button,
  ButtonBack,
  ButtonMore,
  ConfirmUpdate,
  ConfirmReport,
  ConfirmDelete,
  ConfirmUpload,
  LoadingState,
  BaseDropdownMenu,
  CommentDropdownMenu,
  PostDropdownMenu
};