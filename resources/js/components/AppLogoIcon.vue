<script setup lang="ts">
import type { HTMLAttributes } from 'vue';

defineOptions({
    inheritAttrs: false,
});

type Props = {
    className?: HTMLAttributes['class'];
};

defineProps<Props>();
</script>

<template>
    <svg
        xmlns="http://www.w3.org/2000/svg"
        viewBox="0 0 48 48"
        :class="className"
        v-bind="$attrs"
    >
        <path
            d="M7 10.5c6.7-1.5 12.4-.2 17 3.8 4.6-4 10.3-5.3 17-3.8v27c-6.7-1.5-12.4-.2-17 3.8-4.6-4-10.3-5.3-17-3.8v-27Z"
            fill="none"
            stroke="currentColor"
            stroke-width="2.7"
            stroke-linejoin="round"
        />
        <path
            d="M24 14.3v27M12.5 17.3c3.6-.4 6.3.3 8.3 2M27.8 19.3c2.6-1.9 5.4-2.7 8.5-2.2M27.8 25.2c2.5-1.7 5.2-2.4 8.2-2M27.8 31.1c2.2-1.4 4.6-2 7.2-1.8"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
            stroke-linecap="round"
        />
        <path
            d="m11.5 29.5 3.3 3.2 6.5-8"
            fill="none"
            stroke="currentColor"
            stroke-width="2.8"
            stroke-linecap="round"
            stroke-linejoin="round"
        />
    </svg>
</template>
