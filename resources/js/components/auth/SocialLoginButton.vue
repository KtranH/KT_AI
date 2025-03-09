<template>
  <button
    @click="onClick"
    class="w-full flex items-center justify-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500"
  >
    <img v-if="icon" :src="icon" :alt="provider + ' logo'" class="h-5 w-5 mr-2">
    <slot>{{ provider }}</slot>
  </button>
</template>

<script>
export default {
  name: 'SocialLoginButton',
  
  props: {
    provider: {
      type: String,
      required: true
    },
    icon: {
      type: String,
      default: ''
    }
  },
  
  emits: ['click'],
  
  setup(props, { emit }) {
    const onClick = () => {
      emit('click')
    }
    
    return {
      onClick
    }
  }
}
</script>
