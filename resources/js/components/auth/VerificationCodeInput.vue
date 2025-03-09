<template>
  <div class="flex justify-between gap-2">
    <input
      v-for="(digit, index) in length"
      :key="index"
      v-model="codeValue[index]"
      type="text"
      maxlength="1"
      class="appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-purple-500 focus:border-purple-500 text-center text-xl"
      :ref="el => { if (el) codeInputs[index] = el }"
      @input="onInput($event, index)"
      @keydown="onKeydown($event, index)"
    >
  </div>
</template>

<script>
import { computed, watch } from 'vue'
import { useCodeVerification } from '@/composables/auth/useCodeVerification'

export default {
  name: 'VerificationCodeInput',
  
  props: {
    modelValue: {
      type: Array,
      default: () => []
    },
    length: {
      type: Number,
      default: 6
    }
  },
  
  emits: ['update:modelValue', 'code-complete'],
  
  setup(props, { emit }) {
    const {
      verificationCode: codeValue,
      codeInputs,
      isCodeComplete,
      handleInput,
      handleKeydown
    } = useCodeVerification(props.length)
    
    // Sync với v-model
    watch(() => props.modelValue, (newValue) => {
      if (newValue && newValue.length === props.length) {
        codeValue.value = [...newValue]
      }
    }, { immediate: true })
    
    watch(codeValue, (newValue) => {
      emit('update:modelValue', newValue)
    }, { deep: true })
    
    // Theo dõi khi mã hoàn thành
    watch(isCodeComplete, (isComplete) => {
      if (isComplete) {
        emit('code-complete', codeValue.value.join(''))
      }
    })
    
    // Xử lý input với emit
    const onInput = (event, index) => {
      handleInput(event, index)
      emit('update:modelValue', [...codeValue.value])
    }
    
    // Xử lý keydown với emit
    const onKeydown = (event, index) => {
      handleKeydown(event, index)
    }
    
    return {
      codeValue,
      codeInputs,
      isCodeComplete,
      onInput,
      onKeydown,
      length: props.length
    }
  }
}
</script>

<style scoped>
/* Không có thêm CSS đặc biệt vì đã sử dụng Tailwind */
</style>
