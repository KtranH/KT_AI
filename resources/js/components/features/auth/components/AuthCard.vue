<template>
  <transition name="fade-slide" mode="out-in">
    <div class="bg-white/80 backdrop-blur-md shadow-2xl rounded-3xl p-8 md:p-12 w-full border border-gray-100 relative overflow-hidden animate-fade-in">
      <slot />
    </div>
  </transition>
</template>

<script>
export default {
  name: 'AuthCard',
}
</script>

<style scoped>
.fade-slide-enter-active, .fade-slide-leave-active {
  transition: all 0.5s cubic-bezier(0.4,0,0.2,1);
}
.fade-slide-enter-from {
  opacity: 0;
  transform: translateY(40px) scale(0.98);
}
.fade-slide-leave-to {
  opacity: 0;
  transform: translateY(-40px) scale(0.98);
}
.animate-fade-in {
  animation: fadeIn 0.7s cubic-bezier(0.4,0,0.2,1);
}
@keyframes fadeIn {
  from { opacity: 0; transform: scale(0.96); }
  to { opacity: 1; transform: scale(1); }
}
</style> 