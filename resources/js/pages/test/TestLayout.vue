<template>
  <div class="container mx-auto p-6">
    <h1 class="text-3xl font-bold mb-8 bg-gradient-text-v2 bg-clip-text text-transparent">Test Components</h1>
    <div class="bg-white/30 backdrop-blur-md rounded-lg shadow-lg p-6">
      <router-view />
    </div>
  </div>
</template>

<script>
export default {
  name: 'TestLayout'
}
</script> 