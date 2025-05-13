<template>
  <div>
    <!-- Prompt -->
    <div class="mb-4">
      <label for="prompt" class="block text-sm font-medium text-gray-700 mb-1">Prompt (Mô tả)</label>
      <textarea
        id="prompt"
        v-model="localPrompt"
        rows="4"
        class="w-full p-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
        placeholder="Nhập mô tả cho hình ảnh bạn muốn tạo..."
        :disabled="isGenerating"
      ></textarea>
      <p class="text-xs text-gray-500 mt-1">{{ localPrompt.length }}/1000 ký tự</p>
    </div>

    <!-- Seed -->
    <div class="mb-4">
      <label for="seed" class="block text-sm font-medium text-gray-700 mb-1">Seed (Số ngẫu nhiên)</label>
      <div class="flex">
        <input
          id="seed"
          v-model="localSeed"
          type="number"
          class="w-full p-3 border border-gray-300 rounded-l-lg focus:ring-blue-500 focus:border-blue-500"
          placeholder="Nhập seed..."
          :disabled="isGenerating"
        />
        <button
          @click="generateNewSeed"
          class="bg-gradient-text text-white px-4 py-2 rounded-r-lg hover:bg-blue-600 transition"
          :disabled="isGenerating"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd" />
          </svg>
        </button>
      </div>
    </div>
    
    <!-- Lựa chọn phong cách -->
    <div class="mb-6">
      <label for="style" class="block text-sm font-medium text-gray-700 mb-1">Thể loại</label>
      <select
        id="style"
        v-model="localStyle"
        class="w-full p-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
        :disabled="isGenerating"
      >
        <option v-for="option in options" :key="option.value" :value="option.value">
          {{ option.label }}
        </option>
      </select>
    </div>
    
    <!-- Slot cho các tùy chọn bổ sung -->
    <slot></slot>
    
    <!-- Nút tạo ảnh -->
    <button
      @click="handleGenerate"
      class="w-full bg-gradient-text text-white py-3 px-6 rounded-lg font-medium hover:from-blue-600 hover:to-purple-700 transition shadow-md flex items-center justify-center"
      :disabled="isGenerating"
    >
      <span v-if="isGenerating" class="mr-2">
        <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
      </span>
      {{ isGenerating ? 'Đang tạo...' : 'Tạo hình ảnh' }}
    </button>
  </div>
</template>

<script>
import { ref, watch, onMounted } from 'vue'
import { generateRandomSeed } from '@/utils/index'

/*import Button from './Button.vue';*/

export default {
  name: 'PromptInput',
  components: {
    
  },
  props: {
    prompt: {
      type: String,
      default: ''
    },
    seed: {
      type: Number,
      default: 0
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
    },
    isGenerating: {
      type: Boolean,
      default: false
    }
  },
  setup(props, { emit }) {
    // Refs for form values
    const localPrompt = ref(props.prompt)
    const localSeed = ref(props.seed)
    const localStyle = ref(props.style)
    
    // Watch for prop changes
    watch(() => props.prompt, (newVal) => {
      localPrompt.value = newVal
    })
    
    watch(() => props.seed, (newVal) => {
      localSeed.value = newVal
    })
    
    watch(() => props.style, (newVal) => {
      localStyle.value = newVal
    })
    
    // Thêm watcher cho các biến local để emit sự thay đổi
    watch(localPrompt, (newVal) => {
      emit('update:prompt', newVal)
    })
    
    watch(localSeed, (newVal) => {
      emit('update:seed', newVal)
    })
    
    watch(localStyle, (newVal) => {
      emit('update:style', newVal)
    })
    
    // Event handlers
    const generateNewSeed = () => {
      localSeed.value = generateRandomSeed()
    }
    
    const handleGenerate = () => {
      emit('generate')
    }
    
    onMounted(() => {
      if (!localSeed.value) {
        generateNewSeed()
      }
    })
    
    // Return all reactive data and methods
    return {
      localPrompt,
      localSeed,
      localStyle,
      generateNewSeed,
      handleGenerate
    }
  }
}
</script>