<template>
  <div class="min-h-screen bg-white">
    <!-- Sử dụng các component con -->
    <HeroSection />
    <FeaturesSection />
    <CreationToolsPreview />
    <GallerySection />
    <CallToActionSection />
    <FooterSection />
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import HeroSection from '@/components/user/Home/HeroSection.vue';
import FeaturesSection from '@/components/user/Home/FeaturesSection.vue';
import CreationToolsPreview from '@/components/user/Home/CreationToolsPreview.vue';
import GallerySection from '@/components/user/Home/GallerySection.vue';
import CallToActionSection from '@/components/user/Home/CallToActionSection.vue';
import FooterSection from '@/components/layouts/FooterLayout.vue';

const scrollY = ref(0);

// Handle scroll events
const handleScroll = () => {
  scrollY.value = window.scrollY;
};

// AOS animation simulation
const initAnimations = () => {
  const elements = document.querySelectorAll('[data-aos]');
  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        const delay = parseInt(entry.target.getAttribute('data-aos-delay') || '0');
        setTimeout(() => {
          entry.target.classList.add('aos-animate');
        }, delay);
      }
    });
  }, { threshold: 0.1 });
  
  elements.forEach(element => {
    observer.observe(element);
  });

  return observer;
};

// Lifecycle hooks
let observer;

onMounted(() => {
  // Add scroll event listener
  window.addEventListener('scroll', handleScroll);
  
  // Initialize AOS-like animations
  observer = initAnimations();
});

onUnmounted(() => {
  // Remove scroll event listener
  window.removeEventListener('scroll', handleScroll);
  
  // Cleanup observer
  if (observer) {
    observer.disconnect();
  }
});
</script>

<style scoped>
/* AOS animations simulation */
[data-aos] {
  opacity: 0;
  transform: translateY(20px);
  transition: opacity 0.6s ease, transform 0.6s ease;
}

[data-aos="fade-left"] {
  transform: translateX(20px);
}

[data-aos="fade-right"] {
  transform: translateX(-20px);
}

.aos-animate {
  opacity: 1;
  transform: translate(0, 0);
}

/* Custom scrollbar */
::-webkit-scrollbar {
  width: 8px;
}

::-webkit-scrollbar-track {
  background: #f1f1f1;
}

::-webkit-scrollbar-thumb {
  background: #6366f1;
  border-radius: 4px;
}

::-webkit-scrollbar-thumb:hover {
  background: #4f46e5;
}

/* Hero section styling */
.min-h-screen {
  min-height: 100vh;
}
</style>