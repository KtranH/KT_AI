<template>
    <button
      class="w-full bg-gradient-text text-white py-3 px-4 rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 font-medium transition duration-150"
      :type="type"
      :label="label"
    >
    <slot> {{ label }} </slot>
    </button>
</template>
<script>
export default {
    props:
    {
        label:
        {
            type: String,
            default: 'Tạo hình ảnh'
        },
        type:
        {
            type: String,
            default: 'button'
        },
    },
    setup() {
        return {};
    },
}
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

@keyframes gradient-animation {
  0% { background-position: 0% 50%; }
  50% { background-position: 100% 50%; }
  100% { background-position: 0% 50%; }
}
</style>