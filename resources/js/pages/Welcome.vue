<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import {
    ArrowRight,
    BookOpen,
    Check,
    Feather,
    Heart,
    MessageCircle,
    Search,
    Sparkles,
    Users,
} from '@lucide/vue';
import AppearanceToggle from '@/components/AppearanceToggle.vue';
import BrandLogo from '@/components/BrandLogo.vue';
import LanguageSelect from '@/components/LanguageSelect.vue';
import { useTranslation } from '@/composables/useTranslation';
import { dashboard, home, login, register } from '@/routes';

const page = usePage();
const { t } = useTranslation();
</script>

<template>
    <Head :title="t('Thoughtful feedback before publication')">
        <meta
            head-key="description"
            name="description"
            :content="
                t(
                    'BetaReadr connects authors seeking thoughtful pre-publication feedback with beta readers who want to help shape a book.',
                )
            "
        />
    </Head>

    <div
        class="paper-texture min-h-screen overflow-hidden bg-background text-foreground"
    >
        <header class="relative z-20 border-b border-border/70">
            <nav
                class="mx-auto flex max-w-7xl items-center justify-between px-3 py-4 sm:px-8 lg:px-10"
                :aria-label="t('Primary navigation')"
            >
                <Link
                    :href="home()"
                    class="rounded-sm focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-ring"
                    :aria-label="t('BetaReadr home')"
                >
                    <BrandLogo class="max-sm:gap-0 max-sm:text-[0px]" />
                </Link>

                <div class="flex items-center gap-2 sm:gap-3">
                    <AppearanceToggle />
                    <LanguageSelect />
                    <Link
                        v-if="page.props.auth.user"
                        :href="dashboard()"
                        class="inline-flex min-h-10 items-center gap-2 rounded-sm bg-primary px-4 py-2 text-sm font-semibold text-primary-foreground shadow-sm transition hover:-translate-y-px hover:opacity-90 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-ring"
                    >
                        {{ t('Dashboard') }}
                        <ArrowRight class="size-4" aria-hidden="true" />
                    </Link>
                    <template v-else>
                        <Link
                            :href="login()"
                            class="hidden min-h-10 items-center rounded-sm px-4 py-2 text-sm font-semibold text-muted-foreground transition hover:bg-muted hover:text-foreground focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-ring sm:inline-flex"
                            >{{ t('Log in') }}</Link
                        >
                        <Link
                            :href="register()"
                            class="inline-flex min-h-10 items-center gap-2 rounded-sm bg-primary px-4 py-2 text-sm font-semibold text-primary-foreground shadow-sm transition hover:-translate-y-px hover:opacity-90 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-ring"
                        >
                            {{ t('Register') }}
                            <ArrowRight
                                class="hidden size-4 sm:block"
                                aria-hidden="true"
                            />
                        </Link>
                    </template>
                </div>
            </nav>
        </header>

        <main>
            <section class="relative isolate">
                <div
                    class="absolute inset-x-0 top-0 -z-10 h-[34rem] bg-linear-to-b from-primary/5 to-transparent"
                    aria-hidden="true"
                />
                <div
                    class="mx-auto grid max-w-7xl gap-14 px-5 py-20 sm:px-8 sm:py-28 lg:grid-cols-[1.08fr_0.92fr] lg:items-center lg:px-10 lg:py-32"
                >
                    <div class="max-w-3xl">
                        <p
                            class="mb-6 inline-flex items-center gap-2 border-b-2 border-primary/45 px-1 py-1 font-hand text-xl font-semibold text-primary"
                        >
                            <Sparkles class="size-4" aria-hidden="true" />
                            {{
                                t(
                                    'Better books begin with thoughtful beta readers',
                                )
                            }}
                        </p>
                        <h1
                            class="text-4xl leading-[1.02] font-semibold text-balance sm:text-6xl lg:text-7xl"
                        >
                            {{ t('Find the feedback your book needs') }}
                            <span class="text-primary">{{
                                t('before publication.')
                            }}</span>
                        </h1>
                        <p
                            class="mt-7 max-w-2xl text-lg leading-8 text-muted-foreground sm:text-xl"
                        >
                            {{
                                t(
                                    'BetaReadr brings authors seeking useful, pre-publication feedback together with beta readers who want to help shape books they care about.',
                                )
                            }}
                        </p>
                        <div
                            v-if="!page.props.auth.user"
                            class="mt-9 flex flex-col gap-3 sm:flex-row"
                        >
                            <Link
                                :href="register()"
                                class="inline-flex min-h-12 items-center justify-center gap-2 rounded-sm bg-primary px-6 py-3 font-semibold text-primary-foreground shadow-sm transition hover:-translate-y-px focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-ring"
                            >
                                {{ t('Register for BetaReadr') }}
                                <ArrowRight class="size-4" aria-hidden="true" />
                            </Link>
                            <Link
                                :href="login()"
                                class="inline-flex min-h-12 items-center justify-center rounded-sm border border-border bg-card/70 px-6 py-3 font-semibold transition hover:bg-muted focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-ring"
                                >{{ t('Log in') }}</Link
                            >
                        </div>
                        <Link
                            v-else
                            :href="dashboard()"
                            class="mt-9 inline-flex min-h-12 items-center justify-center gap-2 rounded-sm bg-primary px-6 py-3 font-semibold text-primary-foreground shadow-sm transition hover:-translate-y-px focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-ring"
                        >
                            {{ t('Go to your Dashboard') }}
                            <ArrowRight class="size-4" aria-hidden="true" />
                        </Link>
                    </div>

                    <div
                        class="relative mx-auto w-full max-w-lg lg:mx-0 lg:justify-self-end"
                        aria-hidden="true"
                    >
                        <div
                            class="absolute -inset-3 -z-10 rotate-2 rounded-sm border border-primary/20 bg-primary/8"
                        />
                        <div
                            class="rounded-sm border border-border bg-card p-5 shadow-[4px_6px_0_hsl(24_24%_16%/0.12)] sm:p-7"
                        >
                            <div class="mb-6 flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="grid size-11 place-items-center rounded-full bg-secondary text-primary"
                                    >
                                        <Feather class="size-5" />
                                    </div>
                                    <div>
                                        <div
                                            class="h-2.5 w-24 rounded-full bg-foreground/80"
                                        />
                                        <div
                                            class="mt-2 h-2 w-16 rounded-full bg-muted-foreground/30"
                                        />
                                    </div>
                                </div>
                                <span
                                    class="rounded-sm bg-secondary px-3 py-1 text-xs font-semibold text-secondary-foreground"
                                    >{{ t('In progress') }}</span
                                >
                            </div>
                            <div
                                class="rounded-sm bg-sidebar p-6 text-sidebar-foreground"
                            >
                                <BookOpen
                                    class="mb-10 size-7 text-sidebar-primary"
                                />
                                <div
                                    class="h-4 w-4/5 rounded-full bg-white/90"
                                />
                                <div
                                    class="mt-3 h-2.5 w-1/2 rounded-full bg-white/40"
                                />
                            </div>
                            <div
                                class="mt-5 grid grid-cols-3 gap-2 text-center text-xs font-medium text-muted-foreground"
                            >
                                <div class="rounded-sm bg-muted p-3">
                                    <Heart
                                        class="mx-auto mb-2 size-5 text-rose-500"
                                    />{{ t('Interest') }}
                                </div>
                                <div class="rounded-sm bg-muted p-3">
                                    <Users
                                        class="mx-auto mb-2 size-5 text-emerald-600 dark:text-emerald-400"
                                    />{{ t('Match') }}
                                </div>
                                <div class="rounded-sm bg-muted p-3">
                                    <MessageCircle
                                        class="mx-auto mb-2 size-5 text-amber-600 dark:text-amber-400"
                                    />{{ t('Feedback') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section
                class="border-y border-border bg-muted/45"
                aria-labelledby="two-paths-heading"
            >
                <div
                    class="mx-auto max-w-7xl px-5 py-20 sm:px-8 lg:px-10 lg:py-28"
                >
                    <div class="mx-auto max-w-2xl text-center">
                        <p class="font-hand text-xl font-semibold text-primary">
                            {{ t('Two paths, one community') }}
                        </p>
                        <h2
                            id="two-paths-heading"
                            class="mt-3 text-3xl font-bold tracking-tight sm:text-4xl"
                        >
                            {{ t('Take part in the story') }}
                        </h2>
                        <p class="mt-4 text-lg text-muted-foreground">
                            {{
                                t(
                                    'Create the profile that fits how you want to use BetaReadr. You can be both an author and a beta reader.',
                                )
                            }}
                        </p>
                    </div>

                    <div class="mt-12 grid gap-6 lg:grid-cols-2">
                        <article
                            class="rounded-sm border border-border bg-card p-7 sm:p-10"
                        >
                            <div
                                class="grid size-12 place-items-center rounded-sm bg-secondary text-primary"
                            >
                                <Feather class="size-6" aria-hidden="true" />
                            </div>
                            <h3 class="mt-6 text-2xl font-bold">
                                {{ t('For authors') }}
                            </h3>
                            <p class="mt-3 leading-7 text-muted-foreground">
                                {{
                                    t(
                                        'Present your work clearly and explain the thoughtful pre-publication feedback you need.',
                                    )
                                }}
                            </p>
                            <ol class="mt-7 space-y-4">
                                <li class="flex gap-3">
                                    <Check
                                        class="mt-0.5 size-5 shrink-0 text-emerald-600 dark:text-emerald-400"
                                        aria-hidden="true"
                                    /><span>{{
                                        t('Create your public author profile.')
                                    }}</span>
                                </li>
                                <li class="flex gap-3">
                                    <Check
                                        class="mt-0.5 size-5 shrink-0 text-emerald-600 dark:text-emerald-400"
                                        aria-hidden="true"
                                    /><span>{{
                                        t('Add a book you want feedback on.')
                                    }}</span>
                                </li>
                                <li class="flex gap-3">
                                    <Check
                                        class="mt-0.5 size-5 shrink-0 text-emerald-600 dark:text-emerald-400"
                                        aria-hidden="true"
                                    /><span>{{
                                        t(
                                            'Open a Beta Reading Listing with a focused Reading Brief.',
                                        )
                                    }}</span>
                                </li>
                            </ol>
                        </article>

                        <article
                            class="rounded-sm border border-border bg-card p-7 sm:p-10"
                        >
                            <div
                                class="grid size-12 place-items-center rounded-sm bg-secondary text-primary"
                            >
                                <Search class="size-6" aria-hidden="true" />
                            </div>
                            <h3 class="mt-6 text-2xl font-bold">
                                {{ t('For beta readers') }}
                            </h3>
                            <p class="mt-3 leading-7 text-muted-foreground">
                                {{
                                    t(
                                        'Share what you enjoy reading and prepare to find pre-publication books where your perspective can help.',
                                    )
                                }}
                            </p>
                            <ol class="mt-7 space-y-4">
                                <li class="flex gap-3">
                                    <Check
                                        class="mt-0.5 size-5 shrink-0 text-emerald-600 dark:text-emerald-400"
                                        aria-hidden="true"
                                    /><span>{{
                                        t(
                                            'Create your public beta reader profile.',
                                        )
                                    }}</span>
                                </li>
                                <li class="flex gap-3">
                                    <Check
                                        class="mt-0.5 size-5 shrink-0 text-emerald-600 dark:text-emerald-400"
                                        aria-hidden="true"
                                    /><span>{{
                                        t(
                                            'Use your bio to share your reading interests and experience.',
                                        )
                                    }}</span>
                                </li>
                                <li class="flex gap-3">
                                    <Check
                                        class="mt-0.5 size-5 shrink-0 text-emerald-600 dark:text-emerald-400"
                                        aria-hidden="true"
                                    /><span>{{
                                        t(
                                            'Discovery and matching with authors and their books are still to come.',
                                        )
                                    }}</span>
                                </li>
                            </ol>
                        </article>
                    </div>
                </div>
            </section>

            <section aria-labelledby="cta-heading">
                <div class="mx-auto max-w-5xl px-5 py-20 sm:px-8 lg:py-28">
                    <div
                        class="relative overflow-hidden rounded-sm border border-sidebar-border bg-sidebar px-6 py-14 text-center text-sidebar-foreground shadow-[4px_6px_0_hsl(24_24%_16%/0.18)] sm:px-12 sm:py-16"
                    >
                        <div
                            class="absolute -top-24 -right-16 size-64 rounded-full border border-sidebar-primary/15"
                            aria-hidden="true"
                        />
                        <div
                            class="absolute -bottom-24 -left-16 size-64 rounded-full border border-sidebar-primary/10"
                            aria-hidden="true"
                        />
                        <div class="relative mx-auto max-w-2xl">
                            <h2
                                id="cta-heading"
                                class="text-3xl font-bold tracking-tight sm:text-4xl"
                            >
                                {{ t('Your next chapter starts here.') }}
                            </h2>
                            <p
                                class="mt-4 text-lg leading-8 text-sidebar-foreground/75"
                            >
                                {{
                                    t(
                                        'Join BetaReadr as an author, a beta reader, or both—and help make pre-publication feedback more thoughtful.',
                                    )
                                }}
                            </p>
                            <Link
                                v-if="!page.props.auth.user"
                                :href="register()"
                                class="mt-8 inline-flex min-h-12 items-center justify-center gap-2 rounded-sm bg-sidebar-primary px-6 py-3 font-semibold text-sidebar-primary-foreground shadow-sm transition hover:-translate-y-px focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-white"
                            >
                                {{ t('Register for BetaReadr') }}
                                <ArrowRight class="size-4" aria-hidden="true" />
                            </Link>
                            <Link
                                v-else
                                :href="dashboard()"
                                class="mt-8 inline-flex min-h-12 items-center justify-center gap-2 rounded-sm bg-sidebar-primary px-6 py-3 font-semibold text-sidebar-primary-foreground shadow-sm transition hover:-translate-y-px focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-white"
                            >
                                {{ t('Go to your Dashboard') }}
                                <ArrowRight class="size-4" aria-hidden="true" />
                            </Link>
                        </div>
                    </div>
                </div>
            </section>
        </main>

        <footer class="border-t border-border">
            <div
                class="mx-auto flex max-w-7xl flex-col gap-3 px-5 py-8 text-sm text-muted-foreground sm:flex-row sm:items-center sm:justify-between sm:px-8 lg:px-10"
            >
                <BrandLogo class="text-sm [&_svg]:size-6" />
                <p>
                    {{ t('Thoughtful feedback for books before publication.') }}
                </p>
            </div>
        </footer>
    </div>
</template>
