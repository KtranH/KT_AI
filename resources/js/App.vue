<template>
  <div v-if="isLoading">
    <Loading />
  </div>
  <div v-else>
    <Header />
    <router-view v-slot="{ Component }" :key="$route.fullPath">
      <transition name="fade" mode="out-in">
        <component :is="Component" />
      </transition>
    </router-view>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue';
import Header from './components/layouts/HeaderLayout.vue';
import Loading from './components/layouts/LoadingLayout.vue';

export default {
  name: 'App',
  components: {
    Header,
    Loading
  },
  setup() {
    const isLoading = ref(true);

    onMounted(() => {
      setTimeout(() => {
        isLoading.value = false;
      }, 1000);
    });

    return { isLoading };
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