// Dropdown Components - Export các component dropdown menu

import BaseDropdownMenu from './BaseDropdownMenu.vue'
import CommentDropdownMenu from './CommentDropdownMenu.vue'
import PostDropdownMenu from './PostDropdownMenu.vue'

// Named exports
export {
  BaseDropdownMenu,
  CommentDropdownMenu,
  PostDropdownMenu
}

// Component để đăng ký toàn cục nếu cần
export const DROPDOWN_COMPONENTS = {
  BaseDropdownMenu,
  CommentDropdownMenu,
  PostDropdownMenu
}
