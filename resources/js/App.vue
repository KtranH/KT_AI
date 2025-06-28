<template>
  <div v-if="isLoading">
    <Loading />
  </div>
  <div v-else>
    <Header />
    <Sidebar v-if="showSidebar">
      <router-view v-slot="{ Component, route }">
        <transition name="fade" mode="out-in">
          <keep-alive :include="['Dashboard', 'ImageList']">
            <component :is="Component" :key="route.meta.keepAlive ? route.name : route.fullPath" />
          </keep-alive>
        </transition>
      </router-view>
    </Sidebar>
    <router-view v-else v-slot="{ Component, route }">
      <transition name="fade" mode="out-in">
        <keep-alive :include="['Dashboard', 'ImageList']">
          <component :is="Component" :key="route.meta.keepAlive ? route.name : route.fullPath" />
        </keep-alive>
      </transition>
    </router-view>
    <VueSonner position="top-right" theme="light" />
  </div>
</template>

<script>
import { ref, onMounted, computed } from 'vue';
import { useRoute } from 'vue-router';
import Sidebar from './components/layouts/SidebarLayout.vue';
import Header from './components/layouts/HeaderLayout.vue';
import Loading from './components/layouts/LoadingLayout.vue';
import { toast, Toaster as VueSonner } from 'vue-sonner'


export default {
  name: 'App',
  components: {
    Sidebar,
    Header,
    Loading,
    VueSonner
  },
  setup() {
    const isLoading = ref(true);
    const route = useRoute();

    const showSidebar = computed(() => {
      // Các route không hiển thị sidebar
      const noSidebarRoutes = ['/', '/login', '/register', '/forgot-password', '/reset-password']; 
      // Kiểm tra xem route hiện tại có nằm trong danh sách không hiển thị sidebar không
      return !noSidebarRoutes.includes(route.path);
    });

    onMounted(() => {
      // Giảm thời gian loading xuống để tránh blocking UI
      setTimeout(() => {
        isLoading.value = false;
      }, 300);
    });

    return { isLoading, showSidebar };
  }
}
</script>

<style>

/* Gradient text effect */
.bg-gradient-text-v2 {
  background: linear-gradient(
    -45deg,
    #3b82f6,
    #6366f1,
    #8b5cf6,
    #ec4899,
    #3b82f6
  );
  background-size: 400%;
  animation: gradient-animation 8s ease infinite;

  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}

.bg-gradient-text {
  background: linear-gradient(
    -45deg,
    #3b82f6,
    #6366f1,
    #8b5cf6,
    #ec4899,
    #3b82f6
  );
  background-size: 400%;
  animation: gradient-animation 8s ease infinite;
}

.transition-all {
  transition-property: all;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
  transition-duration: 150ms;
}

@keyframes gradient-animation {
  0% {
    background-position: 0% 50%;
  }
  50% {
    background-position: 100% 50%;
  }
  100% {
    background-position: 0% 50%;
  }
}

.masonry-grid {
  width: 120%;
  margin: 0 16px;
}

.masonry-item {
  width: 20%; /* Tạo 5 cột */
  margin-bottom: 10px;
  margin-right: 10px;
  box-sizing: border-box;
}

/* Chắc chắn ảnh lấp đầy khung */
.masonry-item img {
  display: block;
  height: auto;
  object-fit: cover;
}

.masonry-item .h-full {
  min-height: 200px;
}

/* Responsive grid sizing */
@media (max-width: 1200px) {
  .masonry-item {
    width: 25%; /* 4 columns */
  }
}

@media (max-width: 992px) {
  .masonry-item {
    width: 33.333%; /* 3 columns */
  }
}

@media (max-width: 768px) {
  .masonry-item {
    width: 50%; /* 2 columns */
  }
}

@media (max-width: 576px) {
  .masonry-item {
    width: 100%; /* 1 column */
  }
}

.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

#app {
  min-height: 100vh;
}
</style>