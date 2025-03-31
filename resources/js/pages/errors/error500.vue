<template>
  <div class="flex items-center justify-center h-screen">
    <div class="text-center">
      <h1 class="bg-gradient-text-v2 text-9xl font-bold mb-4">500</h1>
      <p class="text-2xl mb-4">
        Xin lỗi, đang có lỗi từ phía Server.
      </p>
      <button
        class="bg-gradient-text text-white font-bold px-6 py-2 rounded-full"
        @click="goBack"
      >
        Quay lại
      </button>
    </div>
  </div>
</template>

<script>
import { useRouter } from 'vue-router'
export default {
  name: "Error500",
  methods: {
    goBack() {
      this.$router.push('/dashboard')
    },
  },
};
</script>
<style scoped>
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
</style> 