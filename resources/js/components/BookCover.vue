<script setup lang="ts">
defineProps<{ src: string | null }>();
</script>

<template>
    <img
        v-if="src"
        :src="src"
        alt=""
        loading="lazy"
        decoding="async"
        class="aspect-[2/3] shrink-0 rounded-md border object-cover"
    />
</template>
