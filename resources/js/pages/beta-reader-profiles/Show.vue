<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import PublicProfileHeader from '@/components/PublicProfileHeader.vue';
import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import { getInitials } from '@/composables/useInitials';
import { useTranslation } from '@/composables/useTranslation';
import type { BetaReaderProfile, ReadingLanguage } from '@/types';

defineProps<{ betaReaderProfile: BetaReaderProfile }>();
const page = usePage();
const { t } = useTranslation();
const languageName = (language: ReadingLanguage) =>
    language === '*'
        ? t('Any language')
        : page.props.contentLanguages[language];
</script>

<template>
    <Head :title="betaReaderProfile.display_name" />
    <div class="paper-texture min-h-screen bg-muted/30">
        <PublicProfileHeader />
        <main class="mx-auto max-w-3xl px-4 py-16 sm:py-24">
            <div class="flex flex-col items-center text-center">
                <Avatar class="size-24">
                    <AvatarFallback class="text-2xl font-semibold">{{
                        getInitials(betaReaderProfile.display_name)
                    }}</AvatarFallback>
                </Avatar>
                <h1 class="mt-6 text-3xl font-semibold tracking-tight">
                    {{ betaReaderProfile.display_name }}
                </h1>
                <p class="mt-1 text-muted-foreground">
                    @{{ betaReaderProfile.handle }}
                </p>
                <p class="mt-4 text-sm text-muted-foreground">
                    {{ t('Reading languages') }}:
                    {{
                        betaReaderProfile.reading_languages
                            .map(languageName)
                            .join(', ')
                    }}
                </p>
                <p
                    v-if="betaReaderProfile.bio"
                    class="reading-copy mt-8 max-w-2xl text-left leading-7 whitespace-pre-line"
                >
                    {{ betaReaderProfile.bio }}
                </p>
            </div>
        </main>
    </div>
</template>
