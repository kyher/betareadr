<script setup lang="ts">
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { Check, CircleCheck, FileText, X } from '@lucide/vue';
import { computed, onBeforeUnmount, ref, watch } from 'vue';
import BookCover from '@/components/BookCover.vue';
import BookMetadata from '@/components/BookMetadata.vue';
import ContentWarnings from '@/components/ContentWarnings.vue';
import ProfileAvatar from '@/components/ProfileAvatar.vue';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardFooter,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { useTranslation } from '@/composables/useTranslation';
import { show as showAuthor } from '@/routes/authors';
import { index as feed, interest, pass } from '@/routes/feed';
import type { ContentLanguage, Genre } from '@/types';

type FeedListing = {
    id: number;
    reading_brief: string;
    word_count: number;
    book: {
        title: string;
        description: string | null;
        language: ContentLanguage;
        genre: Genre;
        content_warnings: string[];
        cover_image_url: string | null;
    };
    author: {
        display_name: string;
        handle: string;
        avatar_url: string | null;
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
const detailsOpen = ref(false);
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
    () => displayedListing.value?.id,
    () => {
        detailsOpen.value = false;
    },
);

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
        detailsOpen.value ||
        event.button !== 0 ||
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

function onPointerCancel() {
    dragging.value = false;
    transitionEnabled.value = true;
    translateX.value = 0;
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
        decision === 'interested'
            ? interest(displayedListing.value.id)
            : pass(displayedListing.value.id),
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
    layout: { breadcrumbs: [{ title: 'Feed', href: feed() }] },
});
</script>

<template>
    <Head :title="t('Feed')" />
    <main
        class="mx-auto flex h-[calc(100dvh-4rem)] min-h-0 w-full max-w-3xl flex-none flex-col p-4 md:p-6"
    >
        <div class="mb-4 shrink-0">
            <h1 class="text-2xl font-semibold tracking-tight">
                {{ t('Feed') }}
            </h1>
            <p class="mt-1 hidden text-muted-foreground sm:block">
                {{ t('Find a book you would love to help shape.') }}
            </p>
        </div>

        <Alert
            v-if="page.props.errors.listing"
            variant="destructive"
            class="mb-4 shrink-0"
        >
            <AlertTitle>{{ t('Listing unavailable') }}</AlertTitle>
            <AlertDescription>{{ page.props.errors.listing }}</AlertDescription>
        </Alert>

        <div
            v-if="displayedListing"
            class="relative mx-auto flex min-h-0 w-full max-w-xl flex-1 flex-col"
        >
            <div
                aria-hidden="true"
                class="absolute inset-3 -z-10 rotate-1 rounded-sm border bg-muted/60"
            />

            <Dialog v-model:open="detailsOpen">
                <Card
                    class="paper-sheet relative min-h-0 flex-1 cursor-grab touch-pan-y gap-0 overflow-hidden border-l-4 border-l-primary/50 py-0 select-none active:cursor-grabbing [&_button]:cursor-pointer"
                    :style="cardStyle"
                    @pointerdown="onPointerDown"
                    @pointermove="onPointerMove"
                    @pointerup="onPointerUp"
                    @pointercancel="onPointerCancel"
                >
                    <div
                        aria-hidden="true"
                        class="pointer-events-none absolute inset-0 z-10 flex items-center justify-start bg-emerald-500/15 p-8 text-emerald-700 dark:text-emerald-300"
                        :style="{
                            opacity:
                                direction === 'interested'
                                    ? decisionStrength
                                    : 0,
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
                        class="pointer-events-none absolute inset-0 z-10 flex items-center justify-end bg-foreground/10 p-8 text-foreground"
                        :style="{
                            opacity:
                                direction === 'pass' ? decisionStrength : 0,
                        }"
                    >
                        <div
                            class="rotate-3 border-2 border-current px-4 py-1 font-hand text-3xl font-bold"
                        >
                            {{ t('Pass') }}
                        </div>
                    </div>

                    <div
                        :key="displayedListing.id"
                        role="region"
                        :aria-label="displayedListing.book.title"
                        tabindex="0"
                        class="min-h-0 flex-1 overflow-y-auto overscroll-contain [overflow-wrap:anywhere]"
                    >
                        <CardHeader class="gap-3 border-b p-4 [.border-b]:pb-4">
                            <div
                                class="flex flex-wrap items-center justify-between gap-2"
                            >
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
                                <DialogTrigger as-child>
                                    <Button
                                        type="button"
                                        variant="ghost"
                                        :disabled="processing"
                                    >
                                        {{ t('Read details') }}
                                    </Button>
                                </DialogTrigger>
                            </div>
                            <div class="flex items-start gap-3">
                                <BookCover
                                    v-if="displayedListing.book.cover_image_url"
                                    :src="displayedListing.book.cover_image_url"
                                    class="w-16 sm:w-20"
                                />
                                <div class="grid min-w-0 flex-1 gap-2">
                                    <CardTitle class="text-xl sm:text-2xl">
                                        {{ displayedListing.book.title }}
                                    </CardTitle>
                                    <BookMetadata
                                        :language="
                                            displayedListing.book.language
                                        "
                                        :genre="displayedListing.book.genre"
                                    />
                                    <Link
                                        :href="
                                            showAuthor(
                                                displayedListing.author.handle,
                                            )
                                        "
                                        class="flex w-fit max-w-full items-center gap-2 text-sm text-muted-foreground hover:text-foreground hover:underline"
                                    >
                                        <ProfileAvatar
                                            :profile="displayedListing.author"
                                            class="size-6 shrink-0"
                                        />
                                        <span>
                                            {{
                                                displayedListing.author
                                                    .display_name
                                            }}
                                            <span class="hidden sm:inline">
                                                <span aria-hidden="true"
                                                    >·</span
                                                >
                                                @{{
                                                    displayedListing.author
                                                        .handle
                                                }}
                                            </span>
                                        </span>
                                    </Link>
                                </div>
                            </div>
                        </CardHeader>

                        <CardContent class="grid gap-3 p-4">
                            <ContentWarnings
                                :warnings="
                                    displayedListing.book.content_warnings
                                "
                            />
                            <section v-if="displayedListing.book.description">
                                <h2
                                    class="mb-1 text-sm font-semibold tracking-wide text-muted-foreground uppercase"
                                >
                                    {{ t('About the book') }}
                                </h2>
                                <p
                                    class="reading-copy line-clamp-2 leading-6 whitespace-pre-line"
                                >
                                    {{ displayedListing.book.description }}
                                </p>
                            </section>
                            <section>
                                <h2
                                    class="mb-1 text-sm font-semibold tracking-wide text-muted-foreground uppercase"
                                >
                                    {{ t('Reading Brief') }}
                                </h2>
                                <p
                                    class="reading-copy line-clamp-3 leading-6 whitespace-pre-line"
                                >
                                    {{ displayedListing.reading_brief }}
                                </p>
                            </section>
                        </CardContent>
                    </div>

                    <CardFooter
                        class="grid shrink-0 grid-cols-2 gap-3 border-t p-4 [.border-t]:pt-3"
                    >
                        <Button
                            type="button"
                            size="lg"
                            variant="outline"
                            class="border-border text-muted-foreground hover:bg-muted hover:text-foreground"
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

                <DialogContent
                    class="flex max-h-[calc(100dvh-2rem)] flex-col gap-0 overflow-hidden p-0 [&_button]:cursor-pointer"
                >
                    <div
                        :key="displayedListing.id"
                        role="region"
                        :aria-label="displayedListing.book.title"
                        tabindex="0"
                        class="min-h-0 overflow-y-auto overscroll-contain p-4 [overflow-wrap:anywhere]"
                    >
                        <DialogHeader class="mb-4 pr-8 text-left">
                            <DialogTitle>{{
                                displayedListing.book.title
                            }}</DialogTitle>
                            <DialogDescription>{{
                                t('Reading Brief')
                            }}</DialogDescription>
                        </DialogHeader>
                        <ContentWarnings
                            :warnings="displayedListing.book.content_warnings"
                            class="mb-4"
                        />
                        <section
                            v-if="displayedListing.book.description"
                            class="mb-4"
                        >
                            <h2
                                class="mb-1 text-sm font-semibold tracking-wide text-muted-foreground uppercase"
                            >
                                {{ t('About the book') }}
                            </h2>
                            <p
                                class="reading-copy leading-6 whitespace-pre-line"
                            >
                                {{ displayedListing.book.description }}
                            </p>
                        </section>
                        <section>
                            <h2
                                class="mb-1 text-sm font-semibold tracking-wide text-muted-foreground uppercase"
                            >
                                {{ t('Reading Brief') }}
                            </h2>
                            <p
                                class="reading-copy leading-6 whitespace-pre-line"
                            >
                                {{ displayedListing.reading_brief }}
                            </p>
                        </section>
                    </div>
                    <DialogFooter
                        class="grid shrink-0 grid-cols-2 gap-3 border-t p-4"
                    >
                        <Button
                            type="button"
                            size="lg"
                            variant="outline"
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
                        <DialogClose as-child>
                            <Button
                                type="button"
                                variant="ghost"
                                class="col-span-2"
                                >{{ t('Close') }}</Button
                            >
                        </DialogClose>
                    </DialogFooter>
                </DialogContent>
            </Dialog>

            <p
                class="mt-3 hidden shrink-0 text-center text-sm text-muted-foreground sm:block"
            >
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
