<template>
  <div class="custom-masonry-container" ref="masonryContainer">
    <slot />
  </div>
</template>

<script>
import { ref, onMounted, onUpdated, onUnmounted, nextTick, toRefs } from 'vue';

export default {
  name: 'CustomMasonry',
  props: {
    cols: {
      type: Object,
      default: () => ({ default: 3, 1200: 3, 992: 2, 768: 2, 576: 1 })
    },
    gutter: {
      type: Object,
      default: () => ({ default: '16px' })
    },
    itemSelector: {
      type: String,
      default: '.masonry-item'
    }
  },
  setup(props) {
    const { cols, gutter, itemSelector } = toRefs(props);
    const masonryContainer = ref(null);
    let masonryInstance = null;
    let resizeObserver = null;

    // Khởi tạo masonry
    const initMasonry = async () => {
      if (!masonryContainer.value) return;

      // Đảm bảo script Masonry đã được tải
      await loadMasonryScript();

      // Đảm bảo chúng ta có thể truy cập đối tượng Masonry từ window
      if (typeof window !== 'undefined' && window.Masonry) {
        // Nếu đã có instance, phá hủy nó
        if (masonryInstance) {
          destroyMasonry();
        }

        // Tạo instance mới
        masonryInstance = new window.Masonry(masonryContainer.value, {
          itemSelector: itemSelector.value,
          columnWidth: itemSelector.value,
          percentPosition: true,
          transitionDuration: 0
        });
      }
    };

    // Tải script Masonry nếu chưa có
    const loadMasonryScript = () => {
      return new Promise((resolve) => {
        if (typeof window !== 'undefined' && window.Masonry) {
          resolve();
          return;
        }

        const script = document.createElement('script');
        script.src = 'https://unpkg.com/masonry-layout@4/dist/masonry.pkgd.min.js';
        script.async = true;
        script.onload = () => {
          resolve();
        };
        document.head.appendChild(script);
      });
    };

    // Cập nhật layout
    const updateLayout = () => {
      if (!masonryInstance) return;
      
      nextTick(() => {
        masonryInstance.layout();
      });
    };

    // Phá hủy Masonry instance
    const destroyMasonry = () => {
      if (masonryInstance) {
        masonryInstance.destroy();
        masonryInstance = null;
      }
    };

    // Setup ResizeObserver để theo dõi kích thước container
    const setupResizeObserver = () => {
      if (typeof ResizeObserver === 'undefined' || !masonryContainer.value) return;
      
      resizeObserver = new ResizeObserver(() => {
        updateLayout();
      });
      
      resizeObserver.observe(masonryContainer.value);
    };

    // Khởi tạo khi component mount
    onMounted(() => {
      initMasonry().then(() => {
        setupResizeObserver();
        // Layout lần đầu sau khi tất cả ảnh đã tải
        nextTick(() => {
          updateLayout();
        });
      });
    });

    // Cập nhật layout khi component update
    onUpdated(() => {
      nextTick(() => {
        updateLayout();
      });
    });

    // Cleanup khi unmount
    onUnmounted(() => {
      if (resizeObserver) {
        resizeObserver.disconnect();
      }
      destroyMasonry();
    });

    return {
      masonryContainer,
      updateLayout
    };
  }
};
</script>

<style scoped>
.custom-masonry-container {
  width: 100%;
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
  grid-gap: 16px;
}

@media (max-width: 1200px) {
  .custom-masonry-container {
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
  }
}

@media (max-width: 992px) {
  .custom-masonry-container {
    grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
  }
}

@media (max-width: 768px) {
  .custom-masonry-container {
    grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
  }
}

@media (max-width: 576px) {
  .custom-masonry-container {
    grid-template-columns: repeat(auto-fill, minmax(100%, 1fr));
  }
}
</style> 