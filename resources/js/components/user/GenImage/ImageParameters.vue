<template>
  <div class="bg-white rounded-xl shadow-lg p-6">
    <h2 class="text-xl font-semibold text-gray-700 mb-6">Thông số hình ảnh</h2>
    
    <div class="space-y-6">
      <!-- Chiều dài và rộng với slider -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Kích thước hình ảnh</label>
        
        <div class="grid grid-cols-2 gap-4 mb-2">
          <div>
            <label class="block text-sm text-gray-500 mb-1">Chiều rộng: {{ width }}px</label>
            <input
              type="range"
              v-model="widthValue"
              min="512"
              max="1024"
              step="64"
              class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-indigo-600"
              @input="updateDimensions"
            />
          </div>
          <div>
            <label class="block text-sm text-gray-500 mb-1">Chiều cao: {{ height }}px</label>
            <input
              type="range"
              v-model="heightValue"
              min="512"
              max="1024"
              step="64"
              class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-indigo-600"
              @input="updateDimensions"
            />
          </div>
        </div>
      </div>
      
      <slot></slot> <!-- Slot cho các tham số bổ sung -->
    </div>
  </div>
</template>

<script>
import { defineComponent, ref, watch } from 'vue';

export default defineComponent({
  name: 'ImageParameters',
  props: {
    width: {
      type: Number,
      default: 512
    },
    height: {
      type: Number,
      default: 768
    }
  },
  setup(props, { emit }) {
    // Local state
    const widthValue = ref(props.width);
    const heightValue = ref(props.height);
    
    // Methods
    const updateDimensions = () => {
      emit('update:width', parseInt(widthValue.value));
      emit('update:height', parseInt(heightValue.value));
    };
    
    // Watchers
    watch(() => props.width, (newVal) => {
      widthValue.value = newVal;
    });
    
    watch(() => props.height, (newVal) => {
      heightValue.value = newVal;
    });
    
    return {
      widthValue,
      heightValue,
      updateDimensions
    };
  }
})
</script> 