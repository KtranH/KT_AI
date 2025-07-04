<template>
  <div 
    class="md:w-1/2 flex items-center justify-center p-8 feature-image opacity-1 transform translate-x-12 rotate-0 transition-all duration-700 relative z-10 bg-gradient-to-l from-transparent via-white/50 to-transparent"
  >
    <img 
      :src="imageUrl" 
      :alt="feature.title"
      class="rounded-3xl shadow-2xl mr-16 w-full max-w-lg border-2 border-transparent group-hover:border-indigo-200/50 transform transition-all duration-700"
      :class="{'hover:scale-105': hoverEffect}"
      @click="$emit('click')"
    />
    
    <!-- Animation elements -->
    <slot name="animation-elements"></slot>
    
    <!-- Floating badge if available -->
    <div v-if="feature.badge" class="absolute top-16 right-16 bg-indigo-600/90 text-white px-4 py-2 rounded-full backdrop-blur-sm opacity-0 transform -translate-y-4 group-hover:opacity-100 group-hover:translate-y-0 transition-all duration-700 shadow-lg">
      {{ feature.badge }}
    </div>
  </div>
</template>

<script>
import { defineComponent } from 'vue';

export default defineComponent({
  name: 'FeatureImage',
  props: {
    feature: {
      type: Object,
      required: true
    },
    imageUrl: {
      type: String,
      required: true
    },
    hoverEffect: {
      type: Boolean,
      default: true
    }
  },
  setup() {
    // Component sử dụng props trực tiếp trong template
    return {};
  }
})
</script>

<style scoped>
.feature-card:hover .feature-image {
  opacity: 1;
  transform: translateX(0) rotate(0);
}
</style> 