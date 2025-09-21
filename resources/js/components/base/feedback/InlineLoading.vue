<template>
  <div 
    v-if="visible" 
    class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-lg"
    :class="[
      size === 'sm' ? 'text-sm' : size === 'lg' ? 'text-lg' : 'text-base',
      variant === 'primary' ? 'bg-indigo-100 text-indigo-700' : 
      variant === 'secondary' ? 'bg-gray-100 text-gray-700' :
      variant === 'success' ? 'bg-green-100 text-green-700' :
      variant === 'warning' ? 'bg-yellow-100 text-yellow-700' :
      variant === 'error' ? 'bg-red-100 text-red-700' :
      'bg-indigo-100 text-indigo-700'
    ]"
  >
    <!-- Spinner -->
    <div 
      class="animate-spin rounded-full border-2 border-current border-t-transparent"
      :class="[
        size === 'sm' ? 'w-4 h-4' : size === 'lg' ? 'w-6 h-6' : 'w-5 h-5'
      ]"
    ></div>
    
    <!-- Loading text -->
    <span v-if="text" class="font-medium">{{ text }}</span>
  </div>
</template>

<script>
export default {
  name: 'InlineLoading',
  props: {
    visible: {
      type: Boolean,
      default: true
    },
    text: {
      type: String,
      default: 'Đang tải...'
    },
    size: {
      type: String,
      default: 'md',
      validator: (value) => ['sm', 'md', 'lg'].includes(value)
    },
    variant: {
      type: String,
      default: 'primary',
      validator: (value) => ['primary', 'secondary', 'success', 'warning', 'error'].includes(value)
    }
  }
}
</script>
