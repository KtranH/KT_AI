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
      @paste="onPaste($event)"
    >
  </div>
</template>

<script>
import { ref, computed, watch } from 'vue'
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
    // Sử dụng ref trực tiếp thay vì composable để tránh vấn đề reactivity
    const codeValue = ref(Array(props.length).fill(''));
    const codeInputs = ref([]);
    
    // Kiểm tra xem mã có đủ 4 ký tự trở lên không
    const isCodeComplete = computed(() => {
      const filledCount = codeValue.value.filter(digit => digit && digit.trim() !== '').length;
      return filledCount >= 4;
    });
    
    // Sync với v-model
    watch(() => props.modelValue, (newValue) => {
      if (newValue && newValue.length === props.length) {
        codeValue.value = [...newValue];
      }
    }, { immediate: true });
    
    watch(codeValue, (newValue) => {
      emit('update:modelValue', [...newValue]);
      
      // Kiểm tra nếu có ít nhất 4 ô được điền
      if (isCodeComplete.value) {
        const completeCode = newValue.filter(digit => digit && digit.trim() !== '').join('');
        emit('code-complete', completeCode);
      }
    }, { deep: true });
    
    // Xử lý input
    const onInput = (event, index) => {
      const value = event.target.value;
      codeValue.value[index] = value;
      
      // Nếu nhập giá trị và không phải là index cuối cùng, tự động focus vào ô tiếp theo
      if (value && index < props.length - 1) {
        codeInputs.value[index + 1].focus();
      }
      
      emit('update:modelValue', [...codeValue.value]);
    };
    
    // Xử lý keydown
    const onKeydown = (event, index) => {
      // Nếu nhấn Backspace và ô hiện tại trống, focus vào ô trước đó
      if (event.key === 'Backspace' && !codeValue.value[index] && index > 0) {
        codeInputs.value[index - 1].focus();
      }
      
      // Nếu nhấn mũi tên trái, focus vào ô trước đó
      if (event.key === 'ArrowLeft' && index > 0) {
        codeInputs.value[index - 1].focus();
      }
      
      // Nếu nhấn mũi tên phải, focus vào ô sau đó
      if (event.key === 'ArrowRight' && index < props.length - 1) {
        codeInputs.value[index + 1].focus();
      }
    };
    
    // Xử lý sự kiện paste 
    const onPaste = (event) => {
      event.preventDefault();
      
      // Lấy nội dung từ clipboard
      const pasteData = (event.clipboardData || window.clipboardData).getData('text');
      
      if (!pasteData) return;
      
      // Chỉ lấy tối đa số ký tự bằng độ dài của input
      const chars = pasteData.trim().slice(0, props.length).split('');
      
      // Điền vào từng ô input
      chars.forEach((char, index) => {
        if (index < props.length) {
          codeValue.value[index] = char;
        }
      });
      
      // Focus vào ô input cuối cùng có dữ liệu hoặc ô tiếp theo
      const focusIndex = Math.min(chars.length, props.length - 1);
      if (codeInputs.value[focusIndex]) {
        codeInputs.value[focusIndex].focus();
      }
      emit('update:modelValue', [...codeValue.value]);
      
      // Kiểm tra nếu đã đủ để hoàn thành
      if (chars.length >= 4) {
        const completeCode = codeValue.value.filter(digit => digit && digit.trim() !== '').join('');
        emit('code-complete', completeCode);
      }
    };
    
    return {
      codeValue,
      codeInputs,
      isCodeComplete,
      onInput,
      onKeydown,
      onPaste,
      length: props.length
    };
  }
}
</script>

<style scoped>
/* Không có thêm CSS đặc biệt vì đã sử dụng Tailwind */
</style>
