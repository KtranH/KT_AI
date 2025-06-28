<template>
  <div class="loading-screen">
    <img :src="logo" alt="Logo" class="logo" />
  </div>
</template>

<script>
import { ref } from 'vue'
export default {
    setup() {
        const logo = ref('/img/voice.png')
        return {
            logo
        }
    },
}
</script>

<style scoped>
.loading-screen {
  display: flex;
  justify-content: center;
  align-items: center;
  height: 100vh;
  background-color: #fff;
}

.logo {
  width: 150px;
  animation: fadeIn 1.5s ease-in-out infinite;
}

@keyframes fadeIn {
  0%, 100% { opacity: 0.7; }
  50% { opacity: 1; }
}
</style>
