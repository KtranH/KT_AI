<template>
  <div>
    <router-link to="/" class="flex justify-center">
      <img src="/img/voice.png" alt="KT_AI Logo" class="h-12 w-12" data-aos="fade-up">
    </router-link>
    <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900" data-aos="fade-up" data-aos-delay="200">
      {{ title }}
    </h2>
    <p v-if="subtitle" class="mt-2 text-center text-sm text-gray-600" data-aos="fade-up" data-aos-delay="400">
      {{ subtitle }}
      <slot name="subtitle-action"></slot>
    </p>
  </div>
</template>

<script>
export default {
  name: 'AuthFormHeader',
  
  props: {
    title: {
      type: String,
      required: true
    },
    subtitle: {
      type: String,
      default: ''
    }
  }
}
</script>
