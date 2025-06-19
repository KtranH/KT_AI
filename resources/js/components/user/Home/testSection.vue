<template>
    <div class="parallax-container">
        <div class="header">
            <h1 class="title">Khám Phá Không Gian</h1>
            <h1 aria-hidden="true" class="title-outline">Khám Phá Không Gian</h1>
        </div>
        
        <div class="scroll-instruction">
            <span>Cuộn xuống để khám phá</span>
            <div class="arrow-down"></div>
        </div>
        
        <div id="gallery-wrapper">
            <section id="scroll-content">
                <div class="gallery-container">
                    <div v-for="(image, index) in images" :key="index" class="image-wrapper" :class="`image-${index}`">
                        <img 
                            :src="image.src" 
                            :alt="image.alt" 
                            :data-speed="image.speed"
                            class="parallax-image"
                        >
                    </div>
                </div>
                
                <div class="text-sections">
                    <div class="text-section" ref="textSection1">
                        <h2>Công nghệ tương lai</h2>
                        <p>Trí tuệ nhân tạo đang thay đổi cách chúng ta tiếp cận thế giới</p>
                    </div>
                    
                    <div class="text-section" ref="textSection2">
                        <h2>Sáng tạo không giới hạn</h2>
                        <p>Khám phá những cơ hội mới với công nghệ hiện đại</p>
                    </div>
                </div>
            </section>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, nextTick } from 'vue';
import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';

// Đăng ký plugin ScrollTrigger với GSAP
gsap.registerPlugin(ScrollTrigger);

const textSection1 = ref(null);
const textSection2 = ref(null);

// Danh sách hình ảnh với tốc độ parallax khác nhau
const images = [
    {
        src: "https://images.unsplash.com/photo-1556856425-366d6618905d?ixid=MnwxMjA3fDB8MHxzZWFyY2h8MTV8fG5lb258ZW58MHx8MHx8&ixlib=rb-1.2.1&auto=format&fit=crop&w=400&q=60",
        alt: "Neon light image",
        speed: 0.8
    },
    {
        src: "https://images.unsplash.com/photo-1520271348391-049dd132bb7c?ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&ixlib=rb-1.2.1&auto=format&fit=crop&w=400&q=80",
        alt: "Abstract lights",
        speed: 0.9
    },
    {
        src: "https://images.unsplash.com/photo-1609166214994-502d326bafee?ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&ixlib=rb-1.2.1&auto=format&fit=crop&w=400&q=80",
        alt: "Neon lights",
        speed: 1
    },
    {
        src: "https://images.unsplash.com/photo-1589882265634-84f7eb9a3414?ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&ixlib=rb-1.2.1&auto=format&fit=crop&w=434&q=80",
        alt: "City lights",
        speed: 1.1
    },
    {
        src: "https://images.unsplash.com/photo-1514689832698-319d3bcac5d5?ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&ixlib=rb-1.2.1&auto=format&fit=crop&w=434&q=80",
        alt: "Abstract colors", 
        speed: 0.9
    },
    {
        src: "https://images.unsplash.com/photo-1535207010348-71e47296838a?ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&ixlib=rb-1.2.1&auto=format&fit=crop&w=300&q=80",
        alt: "Colorful lights",
        speed: 1.2
    }
];

// Khởi tạo các animation khi component được mounted
onMounted(() => {
    // Chờ Vue cập nhật DOM hoàn tất
    nextTick(() => {
        // Tạo hiệu ứng parallax cho từng hình ảnh
        const imageElements = document.querySelectorAll('.parallax-image');
        
        if (imageElements && imageElements.length > 0) {
            imageElements.forEach((image) => {
                const speed = parseFloat(image.getAttribute('data-speed'));
                
                // Đảm bảo hình ảnh hiển thị ngay từ đầu
                gsap.set(image, {
                    visibility: 'visible',
                    opacity: 1
                });
                
                // Tạo hiệu ứng parallax khi scroll
                gsap.to(image, {
                    y: () => (window.innerHeight * speed * 0.1),  // Giảm phạm vi chuyển động
                    ease: "none",
                    scrollTrigger: {
                        trigger: image.parentElement,  // Trigger là phần tử cha của hình ảnh
                        start: "top bottom",  // Kích hoạt khi phần trên của trigger đến phần dưới viewport
                        end: "bottom top",    // Kết thúc khi phần dưới của trigger đến phần trên viewport
                        scrub: 0.5,           // Làm mượt hiệu ứng
                        markers: false,       // Hiển thị markers để debug (có thể bật lên thành true khi test)
                        toggleActions: "play none none reverse"
                    }
                });
            });
        }
        
        // Animation cho các phần text khi scroll
        if (textSection1.value) {
            gsap.from(textSection1.value, {
                y: 50,
                opacity: 0,
                duration: 0.8,
                scrollTrigger: {
                    trigger: textSection1.value,
                    start: "top 80%",
                    toggleActions: "play none none reverse"
                }
            });
        }
        
        if (textSection2.value) {
            gsap.from(textSection2.value, {
                y: 50,
                opacity: 0,
                duration: 0.8,
                scrollTrigger: {
                    trigger: textSection2.value,
                    start: "top 80%",
                    toggleActions: "play none none reverse"
                }
            });
        }
        
        // Hiệu ứng chuyển động cho tiêu đề
        gsap.to('.title', {
            backgroundPositionX: '200%',
            duration: 10,
            repeat: -1,
            ease: 'linear'
        });
        
        // Tạo scroll smoother toàn trang
        ScrollTrigger.refresh();
    });
});

// Dọn dẹp ScrollTrigger khi component unmounted để tránh memory leak
onUnmounted(() => {
    ScrollTrigger.getAll().forEach(trigger => trigger.kill());
});
</script>

<style scoped>
.parallax-container {
    min-height: 100vh;
    background-color: #0a0a0a;
    color: white;
    overflow-x: hidden;
}

.header {
    padding: 5vh 5vw;
    position: relative;
    text-align: center;
    height: 40vh;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
}

.title {
    font-size: clamp(2rem, 8vw, 6rem);
    font-weight: 800;
    margin: 0;
    background: linear-gradient(90deg, #ff3c00, #ffb300, #ff3c00);
    background-size: 200% 100%;
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    text-fill-color: transparent;
    position: relative;
    z-index: 1;
}

.title-outline {
    position: absolute;
    font-size: clamp(2rem, 8vw, 6rem);
    font-weight: 800;
    -webkit-text-stroke: 1px rgba(255, 255, 255, 0.2);
    -webkit-text-fill-color: transparent;
    z-index: 0;
    margin: 0;
    opacity: 0.4;
    transform: translateY(5px);
}

.scroll-instruction {
    position: absolute;
    bottom: 10vh;
    left: 50%;
    transform: translateX(-50%);
    text-align: center;
    color: rgba(255, 255, 255, 0.7);
    font-size: 1rem;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 10px;
}

.arrow-down {
    width: 20px;
    height: 20px;
    border-right: 2px solid white;
    border-bottom: 2px solid white;
    transform: rotate(45deg);
    animation: arrowBounce 2s infinite;
}

#gallery-wrapper {
    width: 100%;
}

#scroll-content {
    min-height: 150vh; /* Giảm chiều cao tối thiểu */
    padding: 5vh 0;
}

.gallery-container {
    display: grid;
    grid-template-columns: repeat(3, 1fr); /* Số cột cố định */
    gap: 2rem;
    padding: 0 5vw;
    position: relative;
}

.image-wrapper {
    overflow: hidden;
    border-radius: 12px;
    height: 50vh;
    max-height: 500px;
    visibility: visible; /* Đảm bảo container luôn hiển thị */
    opacity: 1;
}

.image-wrapper img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
    display: block; /* Đảm bảo hình ảnh hiển thị dạng block */
    visibility: visible; /* Đảm bảo hình ảnh luôn hiển thị */
    opacity: 1;
}

.image-wrapper:hover img {
    transform: scale(1.05);
}

/* Phân bố hình ảnh không đồng đều một cách đơn giản hơn */
.image-0, .image-3 { grid-column: span 1; }
.image-1 { margin-top: 5vh; }
.image-2 { margin-top: -5vh; }
.image-4 { margin-top: -5vh; }
.image-5 { margin-top: 5vh; }

.text-sections {
    padding: 10vh 10vw;
    display: flex;
    flex-direction: column;
    gap: 15vh;
}

.text-section {
    max-width: 800px;
    margin: 0 auto;
    text-align: center;
}

.text-section h2 {
    font-size: clamp(1.5rem, 4vw, 3rem);
    margin-bottom: 1rem;
    background: linear-gradient(90deg, #3498db, #2ecc71);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.text-section p {
    font-size: clamp(1rem, 2vw, 1.5rem);
    line-height: 1.6;
    color: rgba(255, 255, 255, 0.8);
}

@keyframes arrowBounce {
    0%, 20%, 50%, 80%, 100% {
        transform: rotate(45deg) translateY(0);
    }
    40% {
        transform: rotate(45deg) translateY(10px);
    }
    60% {
        transform: rotate(45deg) translateY(5px);
    }
}

@media (max-width: 992px) {
    .gallery-container {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 768px) {
    .gallery-container {
        grid-template-columns: 1fr;
    }
    
    .image-wrapper {
        height: 40vh;
    }
    
    .image-0, .image-1, .image-2, .image-3, .image-4, .image-5 {
        margin-top: 0;
        grid-column: auto;
    }
}
</style>
