<script setup lang="ts">
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import {
    BookOpen,
    Check,
    CircleCheck,
    FileText,
    UserRound,
    X,
} from '@lucide/vue';
import { computed, onBeforeUnmount, ref, watch } from 'vue';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardFooter,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { useTranslation } from '@/composables/useTranslation';
import type { ContentLanguage } from '@/types';

type FeedListing = {
    id: number;
    reading_brief: string;
    word_count: number;
    book: {
        title: string;
        description: string | null;
        language: ContentLanguage;
    };
    author: {
        display_name: string;
        handle: string;
    };
};

const props = defineProps<{ listing: FeedListing | null }>();
const page = usePage();
const { t } = useTranslation();
const displayedListing = ref<FeedListing | null>(props.listing);
const pendingListing = ref<FeedListing | null>(props.listing);
const processing = ref(false);
const exiting = ref(false);
const dragging = ref(false);
const startX = ref(0);
const startY = ref(0);
const translateX = ref(0);
const transitionEnabled = ref(true);
let animationTimer: ReturnType<typeof setTimeout> | undefined;

const threshold = 120;
const direction = computed(() =>
    translateX.value > 0 ? 'interested' : translateX.value < 0 ? 'pass' : null,
);
const decisionStrength = computed(() =>
    Math.min(Math.abs(translateX.value) / threshold, 1),
);
const cardStyle = computed(() => ({
    transform: `translateX(${translateX.value}px) rotate(${Math.max(-10, Math.min(10, translateX.value / 24))}deg)`,
    transition: transitionEnabled.value
        ? 'transform 260ms cubic-bezier(0.2, 0.8, 0.2, 1)'
        : 'none',
}));

watch(
    () => props.listing,
    (listing) => {
        if (processing.value || exiting.value) {
            pendingListing.value = listing;

            return;
        }

        displayedListing.value = listing;
        pendingListing.value = listing;
    },
);

function onPointerDown(event: PointerEvent) {
    if (
        processing.value ||
        (event.target as HTMLElement).closest('a, button') !== null
    ) {
        return;
    }

    dragging.value = true;
    transitionEnabled.value = false;
    startX.value = event.clientX;
    startY.value = event.clientY;
    (event.currentTarget as HTMLElement).setPointerCapture(event.pointerId);
}

function onPointerMove(event: PointerEvent) {
    if (!dragging.value) {
        return;
    }

    const horizontal = event.clientX - startX.value;
    const vertical = event.clientY - startY.value;

    if (Math.abs(horizontal) > Math.abs(vertical)) {
        event.preventDefault();
        translateX.value = horizontal;
    }
}

function onPointerUp(event: PointerEvent) {
    if (!dragging.value) {
        return;
    }

    dragging.value = false;
    transitionEnabled.value = true;
    (event.currentTarget as HTMLElement).releasePointerCapture(event.pointerId);

    if (Math.abs(translateX.value) >= threshold) {
        submitDecision(translateX.value > 0 ? 'interested' : 'pass');

        return;
    }

    translateX.value = 0;
}

function submitDecision(decision: 'interested' | 'pass') {
    if (processing.value || displayedListing.value === null) {
        return;
    }

    processing.value = true;
    transitionEnabled.value = true;
    translateX.value = decision === 'interested' ? 48 : -48;

    router.post(
        `/feed/${displayedListing.value.id}/${decision === 'interested' ? 'interest' : 'pass'}`,
        {},
        {
            preserveScroll: true,
            onSuccess: () => {
                exiting.value = true;
                translateX.value =
                    (decision === 'interested' ? 1 : -1) *
                    (window.innerWidth + 500);
                animationTimer = setTimeout(() => {
                    displayedListing.value = pendingListing.value;
                    transitionEnabled.value = false;
                    translateX.value = 0;
                    exiting.value = false;
                    processing.value = false;
                    requestAnimationFrame(() => {
                        transitionEnabled.value = true;
                    });
                }, 260);
            },
            onError: (errors) => {
                if (errors.listing) {
                    displayedListing.value = pendingListing.value;
                }

                translateX.value = 0;
                processing.value = false;
            },
            onFinish: () => {
                if (!exiting.value) {
                    processing.value = false;
                }
            },
        },
    );
}

onBeforeUnmount(() => {
    if (animationTimer !== undefined) {
        clearTimeout(animationTimer);
    }
});

defineOptions({
    layout: { breadcrumbs: [{ title: 'Feed', href: '/feed' }] },
});
</script>

<template>
    <Head :title="t('Feed')" />
    <main class="mx-auto flex w-full max-w-3xl flex-1 flex-col p-4 md:p-8">
        <div class="mb-6">
            <h1 class="text-2xl font-semibold tracking-tight">
                {{ t('Feed') }}
            </h1>
            <p class="mt-1 text-muted-foreground">
                {{ t('Find a book you would love to help shape.') }}
            </p>
        </div>

        <Alert
            v-if="page.props.errors.listing"
            variant="destructive"
            class="mb-5"
        >
            <AlertTitle>{{ t('Listing unavailable') }}</AlertTitle>
            <AlertDescription>{{ page.props.errors.listing }}</AlertDescription>
        </Alert>

        <div v-if="displayedListing" class="relative mx-auto w-full max-w-xl">
            <div
                aria-hidden="true"
                class="absolute inset-3 -z-10 rotate-1 rounded-sm border bg-muted/60"
            />

            <Card
                class="paper-sheet relative cursor-grab touch-pan-y overflow-hidden border-l-4 border-l-primary/50 select-none active:cursor-grabbing"
                :style="cardStyle"
                @pointerdown="onPointerDown"
                @pointermove="onPointerMove"
                @pointerup="onPointerUp"
                @pointercancel="onPointerUp"
            >
                <div
                    aria-hidden="true"
                    class="pointer-events-none absolute inset-0 z-10 flex items-center justify-start bg-emerald-500/15 p-8 text-emerald-700 dark:text-emerald-300"
                    :style="{
                        opacity:
                            direction === 'interested' ? decisionStrength : 0,
                    }"
                >
                    <div
                        class="rotate-[-4deg] border-2 border-current px-4 py-1 font-hand text-3xl font-bold"
                    >
                        {{ t('Interested') }}
                    </div>
                </div>
                <div
                    aria-hidden="true"
                    class="pointer-events-none absolute inset-0 z-10 flex items-center justify-end bg-rose-500/15 p-8 text-rose-700 dark:text-rose-300"
                    :style="{
                        opacity: direction === 'pass' ? decisionStrength : 0,
                    }"
                >
                    <div
                        class="rotate-3 border-2 border-current px-4 py-1 font-hand text-3xl font-bold"
                    >
                        {{ t('Pass') }}
                    </div>
                </div>

                <CardHeader class="gap-4 border-b">
                    <div class="flex items-center justify-between gap-3">
                        <Badge variant="secondary">
                            <BookOpen class="mr-1 size-3.5" />
                            {{ t('Open Listing') }}
                        </Badge>
                        <div
                            class="flex items-center gap-1.5 text-sm text-muted-foreground"
                        >
                            <FileText class="size-4" />
                            {{
                                displayedListing.word_count.toLocaleString(
                                    page.props.locale,
                                )
                            }}
                            {{ t('words') }}
                        </div>
                    </div>
                    <CardTitle class="text-2xl sm:text-3xl">
                        {{ displayedListing.book.title }}
                    </CardTitle>
                    <p class="text-sm text-muted-foreground">
                        {{
                            page.props.contentLanguages[
                                displayedListing.book.language
                            ]
                        }}
                    </p>
                    <Link
                        :href="`/authors/${displayedListing.author.handle}`"
                        class="flex w-fit items-center gap-2 text-sm text-muted-foreground hover:text-foreground hover:underline"
                    >
                        <UserRound class="size-4" />
                        {{ displayedListing.author.display_name }}
                        <span aria-hidden="true">·</span>
                        @{{ displayedListing.author.handle }}
                    </Link>
                </CardHeader>

                <CardContent class="grid gap-6">
                    <section v-if="displayedListing.book.description">
                        <h2
                            class="mb-2 text-sm font-semibold tracking-wide text-muted-foreground uppercase"
                        >
                            {{ t('About the book') }}
                        </h2>
                        <p class="leading-7 whitespace-pre-line">
                            {{ displayedListing.book.description }}
                        </p>
                    </section>
                    <section>
                        <h2
                            class="mb-2 text-sm font-semibold tracking-wide text-muted-foreground uppercase"
                        >
                            {{ t('Reading Brief') }}
                        </h2>
                        <p class="leading-7 whitespace-pre-line">
                            {{ displayedListing.reading_brief }}
                        </p>
                    </section>
                </CardContent>

                <CardFooter class="grid grid-cols-2 gap-3 border-t pt-6">
                    <Button
                        type="button"
                        size="lg"
                        variant="outline"
                        class="border-rose-300 text-rose-700 hover:bg-rose-50 hover:text-rose-800 dark:border-rose-800 dark:text-rose-300 dark:hover:bg-rose-950"
                        :disabled="processing"
                        @click="submitDecision('pass')"
                    >
                        <X /> {{ t('Pass') }}
                    </Button>
                    <Button
                        type="button"
                        size="lg"
                        class="bg-emerald-600 text-white hover:bg-emerald-700"
                        :disabled="processing"
                        @click="submitDecision('interested')"
                    >
                        <Check /> {{ t('Interested') }}
                    </Button>
                </CardFooter>
            </Card>

            <p class="mt-5 text-center text-sm text-muted-foreground">
                {{ t('Drag left to pass or right if you’re interested.') }}
            </p>
        </div>

        <Card v-else class="mx-auto w-full max-w-xl border-dashed text-center">
            <CardHeader class="justify-items-center">
                <div
                    class="mb-2 flex size-14 items-center justify-center rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300"
                >
                    <CircleCheck class="size-7" />
                </div>
                <CardTitle>{{ t('You’re all caught up') }}</CardTitle>
            </CardHeader>
            <CardContent class="text-muted-foreground">
                {{ t('Check back later for new Listings.') }}
            </CardContent>
        </Card>
    </main>
</template>
