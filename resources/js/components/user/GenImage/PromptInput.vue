<template>
  <div>
    <!-- Prompt -->
    <div>
      <label for="prompt" class="block text-sm font-medium text-gray-700 mb-2">Prompt</label>
      <textarea
        id="prompt"
        v-model="promptValue"
        rows="4"
        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
        placeholder="Nhập mô tả chi tiết cho hình ảnh..."
        @input="updatePrompt"
      ></textarea>
    </div>

    <!-- Seed -->
    <div class="mt-4">
      <label for="seed" class="block text-sm font-medium text-gray-700 mb-2">Seed</label>
      <div class="flex items-center justify-between">
        <input
          id="seed"
          type="text"
          class="w-[80%] px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
          placeholder="Nhập seed hoặc tạo ngẫu nhiên"
          v-model="seedValue"
          @input="updateSeed"
        >
        <button @click="RandomSeed" class="mx-2 bg-gradient-text font-medium text-sm text-white py-2 px-4 rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
          Ngẫu nhiên
        </button>
        <!--<Button label="Tạo ngẫu nhiên" type="button" class="w-[200px]" @click="generateRandomSeed" />-->
      </div>
    </div>
    
    <!-- Lựa chọn phong cách -->
    <div class="mt-4">
      <label for="option" class="block text-sm font-medium text-gray-700 mb-2">Phong cách</label>
      <div class="relative">
        <select
          id="option"
          v-model="styleValue"
          class="w-full px-3 py-2 border border-gray-300 rounded-lg appearance-none focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white"
          @change="updateStyle"
        >
          <option v-for="option in options" :key="option.value" :value="option.value">
            {{ option.label }}
          </option>
        </select>
        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
          <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
          </svg>
        </div>
      </div>
    </div>
    
    <!-- Slot cho các tùy chọn bổ sung -->
    <slot></slot>
    
    <!-- Nút tạo ảnh -->
    <button
      class="w-full mt-6 bg-gradient-text text-white py-3 px-4 rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 font-medium transition duration-150"
      @click="$emit('generate')"
    >
      Tạo hình ảnh
    </button>
    <!--<Button label="Tạo hình ảnh" class="mt-6" type="button" @click="generate" />-->
  </div>
</template>

<script>
import { ref, watch, toRefs } from 'vue';
import { generateRandomSeed } from '@/utils/index';
/*import Button from './Button.vue';*/

export default {
  name: 'PromptInput',
  components: {
    /*Button*/
  },
  props: {
    prompt: {
      type: String,
      default: ''
    },
    seed: {
      type: [String, Number],
      default: () => generateRandomSeed()
    },
    style: {
      type: String,
      default: 'realistic'
    },
    options: {
      type: Array,
      default: () => [
        { value: 'realistic', label: 'Chân thực' },
        { value: 'cartoon', label: 'Phim hoạt hình' },
        { value: 'sketch', label: 'Phác họa' },
        { value: 'anime', label: 'Anime' },
        { value: 'watercolor', label: 'Màu nước' },
        { value: 'oil-painting', label: 'Sơn dầu' },
        { value: 'digital-art', label: 'Nghệ thuật số' },
        { value: 'abstract', label: 'Trừu tượng' }
      ]
    }
  },
  setup(props, { emit }) {
    // Refs for form values
    const promptValue = ref(props.prompt);
    const seedValue = ref(props.seed);
    const styleValue = ref(props.style);
    
    // Destructure props for watching
    const { prompt, seed, style } = toRefs(props);
    
    // Watch for prop changes
    watch(prompt, (newVal) => {
      promptValue.value = newVal;
    });
    
    watch(seed, (newVal) => {
      seedValue.value = newVal;
    });
    
    watch(style, (newVal) => {
      styleValue.value = newVal;
    });
    
    // Event handlers
    const generate = () => {
      console.log('generate');
      emit('generate');
    };
    const updatePrompt = () => {
      emit('update:prompt', promptValue.value);
    };
    
    const updateSeed = () => {
      emit('update:seed', seedValue.value);
    };
    
    const updateStyle = () => {
      emit('update:style', styleValue.value);
    };
    
    const RandomSeed = () => {
      seedValue.value = generateRandomSeed();
      emit('update:seed', seedValue.value);
    };
    
    // Return all reactive data and methods
    return {
      promptValue,
      seedValue,
      styleValue,
      updatePrompt,
      updateSeed,
      updateStyle,
      RandomSeed,
      generate
    };
  }
}
</script>