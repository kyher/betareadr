<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { ExternalLink } from '@lucide/vue';
import ProfileAvatar from '@/components/ProfileAvatar.vue';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { useTranslation } from '@/composables/useTranslation';
import { show as showBetaReader } from '@/routes/beta-readers';
import type { BetaReaderProfile, ReadingLanguage } from '@/types';

defineProps<{
    reader: {
        created_at: string;
        profile: BetaReaderProfile;
    };
}>();

const page = usePage();
const { t } = useTranslation();
const languageName = (language: ReadingLanguage) =>
    language === '*'
        ? t('Any language')
        : page.props.contentLanguages[language];
const formatDate = (date: string) =>
    new Intl.DateTimeFormat(page.props.locale, { dateStyle: 'medium' }).format(
        new Date(date),
    );
</script>

<template>
    <Card class="paper-sheet">
        <CardHeader class="sm:flex-row sm:items-start">
            <ProfileAvatar :profile="reader.profile" class="size-12" />
            <div class="min-w-0">
                <CardTitle>{{ reader.profile.display_name }}</CardTitle>
                <CardDescription>
                    @{{ reader.profile.handle }} · {{ t('Interested') }}
                    {{ formatDate(reader.created_at) }}
                </CardDescription>
            </div>
        </CardHeader>
        <CardContent class="grid gap-4">
            <p class="text-sm text-muted-foreground">
                {{ t('Reading languages') }}:
                {{
                    reader.profile.reading_languages
                        .map(languageName)
                        .join(', ')
                }}
            </p>
            <p v-if="reader.profile.bio" class="line-clamp-3 text-sm">
                {{ reader.profile.bio }}
            </p>
            <div class="flex flex-wrap gap-2">
                <Button variant="outline" size="sm" as-child>
                    <Link
                        :href="showBetaReader(reader.profile.handle)"
                        target="_blank"
                    >
                        <ExternalLink /> {{ t('View profile') }}
                    </Link>
                </Button>
                <slot />
            </div>
        </CardContent>
    </Card>
</template>
