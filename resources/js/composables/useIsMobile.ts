import { ref, onMounted, onUnmounted } from 'vue';

export function useIsMobile(breakpoint: number = 1024) {
    const isMobile = ref(false);

    const checkIsMobile = () => {
        isMobile.value = window.innerWidth < breakpoint;
    };

    onMounted(() => {
        checkIsMobile();
        window.addEventListener('resize', checkIsMobile);
    });

    onUnmounted(() => {
        window.removeEventListener('resize', checkIsMobile);
    });

    return { isMobile };
}
