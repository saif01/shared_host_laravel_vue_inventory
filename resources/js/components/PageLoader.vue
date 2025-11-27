<template>
    <v-overlay :model-value="isLoading" class="page-loader-overlay" persistent scrim="rgba(255, 255, 255, 0.95)">
        <div class="page-loader-content">
            <div class="modern-loader">
                <div class="loader-ring">
                    <div class="loader-ring-inner"></div>
                </div>
                <div class="loader-dots">
                    <span class="dot dot-1"></span>
                    <span class="dot dot-2"></span>
                    <span class="dot dot-3"></span>
                </div>
            </div>
            <p class="loading-text">
                <span class="text-char" v-for="(char, index) in loadingText" :key="index"
                    :style="{ animationDelay: index * 0.1 + 's' }">
                    {{ char === ' ' ? '\u00A0' : char }}
                </span>
            </p>
        </div>
    </v-overlay>
</template>

<script>
export default {
    name: 'PageLoader',
    data() {
        return {
            isLoading: true,
            loadingText: 'Loading'
        }
    },
    mounted() {
        // Hide the loader after Vue is fully mounted
        // Add a small delay to ensure smooth transition
        setTimeout(() => {
            this.isLoading = false;
            // Also hide any HTML-based loader that might exist
            const htmlLoader = document.getElementById('html-page-loader');
            if (htmlLoader) {
                htmlLoader.style.opacity = '0';
                setTimeout(() => {
                    htmlLoader.style.display = 'none';
                }, 300);
            }
        }, 500);
    }
}
</script>

<style scoped>
:deep(.page-loader-overlay) {
    z-index: 99999 !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
}

:deep(.v-overlay__content) {
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    position: absolute !important;
    top: 50% !important;
    left: 50% !important;
    transform: translate(-50%, -50%) !important;
    margin: 0 !important;
}

.page-loader-content {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
    gap: 2rem;
}

/* Modern Loader Ring Animation */
.modern-loader {
    position: relative;
    width: 120px;
    height: 120px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.loader-ring {
    position: absolute;
    width: 100%;
    height: 100%;
    border: 4px solid transparent;
    border-top: 4px solid #2c73d2;
    border-right: 4px solid #008f7a;
    border-radius: 50%;
    animation: rotate 1.5s linear infinite;
    filter: drop-shadow(0 0 10px rgba(44, 115, 210, 0.3));
}

.loader-ring-inner {
    position: absolute;
    width: 80%;
    height: 80%;
    top: 10%;
    left: 10%;
    border: 3px solid transparent;
    border-bottom: 3px solid #2c73d2;
    border-left: 3px solid #008f7a;
    border-radius: 50%;
    animation: rotate-reverse 1s linear infinite;
}

/* Animated Dots */
.loader-dots {
    position: absolute;
    display: flex;
    gap: 8px;
    bottom: -40px;
}

.dot {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background: linear-gradient(135deg, #2c73d2, #008f7a);
    animation: pulse 1.4s ease-in-out infinite;
    box-shadow: 0 0 10px rgba(44, 115, 210, 0.5);
}

.dot-1 {
    animation-delay: 0s;
}

.dot-2 {
    animation-delay: 0.2s;
}

.dot-3 {
    animation-delay: 0.4s;
}

/* Loading Text Animation */
.loading-text {
    font-size: 1.5rem;
    font-weight: 600;
    color: #2c73d2;
    letter-spacing: 2px;
    margin: 0;
    margin-top: 1rem;
}

.text-char {
    display: inline-block;
    animation: bounce 1.2s ease-in-out infinite;
    opacity: 0;
}

/* Keyframe Animations */
@keyframes rotate {
    0% {
        transform: rotate(0deg);
    }

    100% {
        transform: rotate(360deg);
    }
}

@keyframes rotate-reverse {
    0% {
        transform: rotate(360deg);
    }

    100% {
        transform: rotate(0deg);
    }
}

@keyframes pulse {

    0%,
    100% {
        transform: scale(1);
        opacity: 1;
    }

    50% {
        transform: scale(1.3);
        opacity: 0.7;
    }
}

@keyframes bounce {

    0%,
    100% {
        transform: translateY(0);
        opacity: 0.3;
    }

    50% {
        transform: translateY(-10px);
        opacity: 1;
    }
}

/* Responsive adjustments */
@media (max-width: 600px) {
    .modern-loader {
        width: 100px;
        height: 100px;
    }

    .loader-ring {
        border-width: 3px;
    }

    .loader-ring-inner {
        border-width: 2px;
    }

    .loading-text {
        font-size: 1.25rem;
    }
}
</style>
