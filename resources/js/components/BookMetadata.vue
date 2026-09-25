<script setup lang="ts">
import type { ContentLanguage, Genre } from '@/types';

defineProps<{ language: ContentLanguage; genre: Genre }>();
</script>

<template>
    <p class="text-sm text-muted-foreground">
        {{ $page.props.contentLanguages[language] }}
        · {{ $page.props.genres[genre] }}
    </p>
</template>
