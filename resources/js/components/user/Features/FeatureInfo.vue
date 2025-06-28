<template>
  <div>
    <h2 class="text-3xl font-bold feature-title bg-gradient-to-r from-blue-600 to-purple-600 text-transparent bg-clip-text mb-6 transform transition-all duration-500">Thông tin</h2>
    <ul class="list-disc list-inside text-gray-600 text-lg">
      <li class="mb-2"><span class="font-semibold">Lời nhắc khuyến nghị:</span> {{ info.prompt_template }}</li>
      <li class="mb-2"><span class="font-semibold">Yêu cầu đầu vào: </span>{{ info.input_requirements ? info.input_requirements : 'Không' }}.</li>
      <li class="mb-2"><span class="font-semibold">Thể loại: </span>{{ info.category }}.</li>
      <li class="mb-2"><span class="font-semibold">Thời gian tạo ảnh: </span>{{ info.average_processing_time }}s.</li>
      <li class="mb-2"><span class="font-semibold">Chi phí: </span>{{ info.creadit_cost }}.</li>
    </ul>
    <div class="mt-8" v-if="hasCTA">
      <slot name="cta"></slot>
    </div>
  </div>
</template>

<script>
import { defineComponent } from 'vue';

export default defineComponent({
  name: 'FeatureInfo',
  props: {
    info: {
      type: Object,
      required: true
    },
    hasCTA: {
      type: Boolean,
      default: false
    }
  },
  setup() {
    // Component sử dụng props trực tiếp trong template
    return {};
  }
})
</script> 