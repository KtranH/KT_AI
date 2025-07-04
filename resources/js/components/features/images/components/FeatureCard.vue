<template>
  <div 
    class="feature-card bg-white rounded-xl shadow-xl overflow-hidden flex flex-col md:flex-row transform transition-all duration-700 hover:scale-[1.02] relative h-[600px] w-[1200px] group"
    :class="{'feature-active': isActive}"
  >
    <!-- Animated gradient background -->
    <div class="absolute inset-0 bg-gradient-to-r from-blue-500/0 via-purple-500/0 to-pink-500/0 opacity-0 group-hover:opacity-5 transition-all duration-700"></div>
    
    <!-- Animated particles background -->
    <div class="absolute inset-0 overflow-hidden opacity-0 group-hover:opacity-10 transition-all duration-700">
      <div class="particles-bg"></div>
    </div>

    <!-- Gradient border effect -->
    <div class="absolute -inset-[2px] bg-gradient-to-r from-blue-500 via-purple-500 to-pink-500 rounded-xl opacity-0 group-hover:opacity-30 blur-xl transition-all duration-700"></div>
    
    <!-- Glass effect overlay -->
    <div class="absolute inset-0 bg-white/80 backdrop-blur-sm opacity-0 group-hover:opacity-20 transition-all duration-700"></div>

    <!-- Content -->
    <div class="md:w-1/2 p-12 flex flex-col justify-center relative z-10 transform transition-transform duration-700 hover:translate-x-2 bg-gradient-to-r from-transparent via-white/50 to-transparent group-hover:from-blue-50/50 group-hover:via-purple-50/50 group-hover:to-pink-50/50">
      <span class="inline-block feature-number bg-gradient-to-r from-blue-600 to-purple-600 text-transparent bg-clip-text font-semibold mb-2 transform transition-all duration-500">
        #{{ feature.id }} - Tổng số ảnh đã tạo ({{ feature.sum_img }})
      </span>
      <h2 class="text-3xl font-bold feature-title bg-gradient-to-r from-blue-600 to-purple-600 text-transparent bg-clip-text mb-6 transform transition-all duration-500">
        {{ feature.title }}
      </h2>
      <p class="text-gray-600 mb-4 leading-relaxed text-lg feature-description opacity-0 transform translate-y-4 transition-all duration-700">
        {{ feature.description }}.
      </p>
      <hr class="mb-4">
      <slot name="feature-info"></slot>
    </div>

    <slot name="feature-image"></slot>
  </div>
</template>

<script>
import { defineComponent } from 'vue';

export default defineComponent({
  name: 'FeatureCard',
  props: {
    feature: {
      type: Object,
      required: true
    },
    isActive: {
      type: Boolean,
      default: false
    }
  },
  setup() {
    // Component không có state hoặc methods, chỉ sử dụng props
    return {};
  }
})
</script>

<style scoped>
.feature-card:hover .feature-title {
  background-size: 200% 200%;
  animation: flow 4s ease-in-out infinite;
}

.feature-card:hover .feature-description {
  opacity: 1;
  transform: translateY(0);
}

.feature-card:hover .feature-image {
  transform: translateX(0) rotate(0);
  opacity: 1;
}

.feature-card:hover .feature-number {
  letter-spacing: 1px;
}

.particles-bg {
  background-image: url("data:image/svg+xml,%3Csvg width='20' height='20' viewBox='0 0 20 20' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='%239C92AC' fill-opacity='0.1' fill-rule='evenodd'%3E%3Ccircle cx='3' cy='3' r='3'/%3E%3Ccircle cx='13' cy='13' r='3'/%3E%3C/g%3E%3C/svg%3E");
  width: 200%;
  height: 200%;
  animation: particleMove 10s linear infinite;
}

@keyframes particleMove {
  0% {
    transform: translate(-50%, -50%);
  }
  100% {
    transform: translate(0%, 0%);
  }
}

@keyframes flow {
  0% {
    background-position: 0% 50%;
  }
  50% {
    background-position: 100% 50%;
  }
  100% {
    background-position: 0% 50%;
  }
}

.feature-active .feature-description {
  opacity: 1;
  transform: translateY(0);
}
</style> 