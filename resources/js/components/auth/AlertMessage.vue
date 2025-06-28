<template>
  <div 
    v-if="message" 
    :class="[
      'border px-4 py-3 rounded relative', 
      type === 'error' ? 'bg-red-50 border-red-200 text-red-600' : 
      type === 'success' ? 'bg-green-50 border-green-200 text-green-600' : 
      'bg-yellow-50 border-yellow-200 text-yellow-800'
    ]" 
    role="alert"
  >
    <slot>{{ message }}</slot>
  </div>
</template>

<script>
export default {
  name: 'AlertMessage',
  
  props: {
    message: {
      type: String,
      default: ''
    },
    type: {
      type: String,
      default: 'error',
      validator: (value) => ['error', 'success', 'warning'].includes(value)
    }
  }
}
</script>
