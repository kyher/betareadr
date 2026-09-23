<script setup lang="ts">
import type { ContentLanguage, Genre } from '@/types';

defineProps<{ language: ContentLanguage; genre: Genre | null }>();
</script>

<template>
    <p class="text-sm text-muted-foreground">
        {{ $page.props.contentLanguages[language] }}
        <template v-if="genre"> · {{ $page.props.genres[genre] }} </template>
    </p>
</template>
