<script setup lang="ts">
import { useTranslation } from '@/composables/useTranslation';

defineProps<{ warnings: string[] }>();
const { t } = useTranslation();
</script>

<template>
    <div class="text-sm">
        <span class="font-medium">{{ t('Content warnings') }}:</span>
        {{
            warnings.length
                ? warnings
                      .map((topic) => $page.props.contentTopics[topic])
                      .join(', ')
                : t('No content warnings declared')
        }}
    </div>
</template>
