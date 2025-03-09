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