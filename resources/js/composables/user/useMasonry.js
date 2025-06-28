import Masonry from 'masonry-layout';
import { ref, nextTick } from 'vue'

export default function useMasonry() {
  const masonryContainer = ref(null)
  const masonryInstance = ref(null)
  
  // Tạo masonry instance
  const initMasonry = () => {
    nextTick(() => {
      if (masonryContainer.value) {
        // Xóa masonry instance cũ
        if (masonryInstance.value) {
          masonryInstance.value.destroy()
        }
        
        // Tạo masonry instance mới
        masonryInstance.value = new Masonry(masonryContainer.value, {
          itemSelector: '.masonry-item',
          columnWidth: '.masonry-item',
          percentPosition: true,
          gutter: 0,
          transitionDuration: '0.2s',
          initLayout: true
        })
      }
    })
  }

  // Xử lý khi hình ảnh được tải xong
  const onImageLoaded = () => {
    if (masonryInstance.value) {
      masonryInstance.value.layout()
    }
  }
  return {
    masonryContainer,
    masonryInstance,
    initMasonry,
    onImageLoaded
  }
}