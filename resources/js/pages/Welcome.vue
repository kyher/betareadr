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
import { dashboard, home, login, register } from '@/routes';

const page = usePage();
</script>

<template>
    <Head title="Thoughtful feedback before publication">
        <meta
            head-key="description"
            name="description"
            content="BetaReadr connects authors seeking thoughtful pre-publication feedback with beta readers who want to help shape a book."
        />
    </Head>

    <div
        class="min-h-screen overflow-hidden bg-background text-foreground selection:bg-amber-200 selection:text-stone-950 dark:selection:bg-amber-700 dark:selection:text-white"
    >
        <header class="relative z-20 border-b border-border/70">
            <nav
                class="mx-auto flex max-w-7xl items-center justify-between px-5 py-4 sm:px-8 lg:px-10"
                aria-label="Primary navigation"
            >
                <Link
                    :href="home()"
                    class="flex items-center gap-2 rounded-md text-lg font-semibold tracking-tight focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-ring"
                    aria-label="BetaReadr home"
                >
                    <span
                        class="grid size-9 place-items-center rounded-xl bg-amber-400 text-stone-950 shadow-sm"
                        aria-hidden="true"
                    >
                        <BookOpen class="size-5" :stroke-width="2.25" />
                    </span>
                    BetaReadr
                </Link>

                <div class="flex items-center gap-2 sm:gap-3">
                    <Link
                        v-if="page.props.auth.user"
                        :href="dashboard()"
                        class="inline-flex min-h-10 items-center gap-2 rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-primary-foreground shadow-sm transition hover:opacity-90 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-ring"
                    >
                        Dashboard
                        <ArrowRight class="size-4" aria-hidden="true" />
                    </Link>
                    <template v-else>
                        <Link
                            :href="login()"
                            class="inline-flex min-h-10 items-center rounded-lg px-3 py-2 text-sm font-semibold text-muted-foreground transition hover:bg-muted hover:text-foreground focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-ring sm:px-4"
                            >Log in</Link
                        >
                        <Link
                            :href="register()"
                            class="inline-flex min-h-10 items-center gap-2 rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-primary-foreground shadow-sm transition hover:opacity-90 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-ring"
                        >
                            Register
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
                    class="absolute inset-x-0 top-0 -z-10 h-[34rem] bg-[radial-gradient(ellipse_at_top_right,rgba(251,191,36,0.18),transparent_48%),radial-gradient(ellipse_at_top_left,rgba(16,185,129,0.12),transparent_45%)] dark:bg-[radial-gradient(ellipse_at_top_right,rgba(251,191,36,0.10),transparent_48%),radial-gradient(ellipse_at_top_left,rgba(16,185,129,0.08),transparent_45%)]"
                    aria-hidden="true"
                />
                <div
                    class="mx-auto grid max-w-7xl gap-14 px-5 py-20 sm:px-8 sm:py-28 lg:grid-cols-[1.08fr_0.92fr] lg:items-center lg:px-10 lg:py-32"
                >
                    <div class="max-w-3xl">
                        <p
                            class="mb-6 inline-flex items-center gap-2 rounded-full border border-amber-300/70 bg-amber-50 px-3 py-1.5 text-sm font-semibold text-amber-950 dark:border-amber-700/60 dark:bg-amber-950/40 dark:text-amber-200"
                        >
                            <Sparkles class="size-4" aria-hidden="true" />
                            Better books begin with thoughtful beta readers
                        </p>
                        <h1
                            class="text-4xl leading-[1.08] font-bold tracking-tight text-balance sm:text-6xl lg:text-7xl"
                        >
                            Find the feedback your book needs
                            <span class="text-amber-600 dark:text-amber-400"
                                >before publication.</span
                            >
                        </h1>
                        <p
                            class="mt-7 max-w-2xl text-lg leading-8 text-muted-foreground sm:text-xl"
                        >
                            BetaReadr brings authors seeking useful,
                            pre-publication feedback together with beta readers
                            who want to help shape books they care about.
                        </p>
                        <div
                            v-if="!page.props.auth.user"
                            class="mt-9 flex flex-col gap-3 sm:flex-row"
                        >
                            <Link
                                :href="register()"
                                class="inline-flex min-h-12 items-center justify-center gap-2 rounded-lg bg-primary px-6 py-3 font-semibold text-primary-foreground shadow-sm transition hover:-translate-y-0.5 hover:shadow-md focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-ring"
                            >
                                Register for BetaReadr
                                <ArrowRight class="size-4" aria-hidden="true" />
                            </Link>
                            <Link
                                :href="login()"
                                class="inline-flex min-h-12 items-center justify-center rounded-lg border border-border bg-background/70 px-6 py-3 font-semibold backdrop-blur transition hover:bg-muted focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-ring"
                                >Log in</Link
                            >
                        </div>
                        <Link
                            v-else
                            :href="dashboard()"
                            class="mt-9 inline-flex min-h-12 items-center justify-center gap-2 rounded-lg bg-primary px-6 py-3 font-semibold text-primary-foreground shadow-sm transition hover:-translate-y-0.5 hover:shadow-md focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-ring"
                        >
                            Go to your Dashboard
                            <ArrowRight class="size-4" aria-hidden="true" />
                        </Link>
                    </div>

                    <div
                        class="relative mx-auto w-full max-w-lg lg:mx-0 lg:justify-self-end"
                        aria-hidden="true"
                    >
                        <div
                            class="absolute -inset-5 -z-10 rotate-3 rounded-[2.25rem] bg-amber-300/30 blur-sm dark:bg-amber-500/10"
                        />
                        <div
                            class="rounded-[2rem] border border-border bg-card p-5 shadow-2xl shadow-stone-900/10 sm:p-7 dark:shadow-black/30"
                        >
                            <div class="mb-6 flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="grid size-11 place-items-center rounded-full bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300"
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
                                    class="rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-800 dark:bg-amber-950 dark:text-amber-300"
                                    >In progress</span
                                >
                            </div>
                            <div
                                class="rounded-2xl bg-gradient-to-br from-stone-800 to-stone-950 p-6 text-white"
                            >
                                <BookOpen class="mb-10 size-7 text-amber-300" />
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
                                <div class="rounded-xl bg-muted p-3">
                                    <Heart
                                        class="mx-auto mb-2 size-5 text-rose-500"
                                    />Interest
                                </div>
                                <div class="rounded-xl bg-muted p-3">
                                    <Users
                                        class="mx-auto mb-2 size-5 text-emerald-600 dark:text-emerald-400"
                                    />Match
                                </div>
                                <div class="rounded-xl bg-muted p-3">
                                    <MessageCircle
                                        class="mx-auto mb-2 size-5 text-amber-600 dark:text-amber-400"
                                    />Feedback
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
                        <p
                            class="text-sm font-bold tracking-widest text-amber-700 uppercase dark:text-amber-400"
                        >
                            Two paths, one community
                        </p>
                        <h2
                            id="two-paths-heading"
                            class="mt-3 text-3xl font-bold tracking-tight sm:text-4xl"
                        >
                            Take part in the story
                        </h2>
                        <p class="mt-4 text-lg text-muted-foreground">
                            Create the profile that fits how you want to use
                            BetaReadr. You can be both an author and a beta
                            reader.
                        </p>
                    </div>

                    <div class="mt-12 grid gap-6 lg:grid-cols-2">
                        <article
                            class="rounded-3xl border border-border bg-card p-7 shadow-sm sm:p-10"
                        >
                            <div
                                class="grid size-12 place-items-center rounded-2xl bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300"
                            >
                                <Feather class="size-6" aria-hidden="true" />
                            </div>
                            <h3 class="mt-6 text-2xl font-bold">For authors</h3>
                            <p class="mt-3 leading-7 text-muted-foreground">
                                Present your work clearly and explain the
                                thoughtful pre-publication feedback you need.
                            </p>
                            <ol class="mt-7 space-y-4">
                                <li class="flex gap-3">
                                    <Check
                                        class="mt-0.5 size-5 shrink-0 text-emerald-600 dark:text-emerald-400"
                                        aria-hidden="true"
                                    /><span
                                        >Create your public author
                                        profile.</span
                                    >
                                </li>
                                <li class="flex gap-3">
                                    <Check
                                        class="mt-0.5 size-5 shrink-0 text-emerald-600 dark:text-emerald-400"
                                        aria-hidden="true"
                                    /><span
                                        >Add a book you want feedback on.</span
                                    >
                                </li>
                                <li class="flex gap-3">
                                    <Check
                                        class="mt-0.5 size-5 shrink-0 text-emerald-600 dark:text-emerald-400"
                                        aria-hidden="true"
                                    /><span
                                        >Open a Beta Reading Listing with a
                                        focused Reading Brief.</span
                                    >
                                </li>
                            </ol>
                        </article>

                        <article
                            class="rounded-3xl border border-border bg-card p-7 shadow-sm sm:p-10"
                        >
                            <div
                                class="grid size-12 place-items-center rounded-2xl bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300"
                            >
                                <Search class="size-6" aria-hidden="true" />
                            </div>
                            <h3 class="mt-6 text-2xl font-bold">
                                For beta readers
                            </h3>
                            <p class="mt-3 leading-7 text-muted-foreground">
                                Share what you enjoy reading and prepare to find
                                pre-publication books where your perspective can
                                help.
                            </p>
                            <ol class="mt-7 space-y-4">
                                <li class="flex gap-3">
                                    <Check
                                        class="mt-0.5 size-5 shrink-0 text-emerald-600 dark:text-emerald-400"
                                        aria-hidden="true"
                                    /><span
                                        >Create your public beta reader
                                        profile.</span
                                    >
                                </li>
                                <li class="flex gap-3">
                                    <Check
                                        class="mt-0.5 size-5 shrink-0 text-emerald-600 dark:text-emerald-400"
                                        aria-hidden="true"
                                    /><span
                                        >Use your bio to share your reading
                                        interests and experience.</span
                                    >
                                </li>
                                <li class="flex gap-3">
                                    <Check
                                        class="mt-0.5 size-5 shrink-0 text-emerald-600 dark:text-emerald-400"
                                        aria-hidden="true"
                                    /><span
                                        >Discovery and matching with authors and
                                        their books are still to come.</span
                                    >
                                </li>
                            </ol>
                        </article>
                    </div>
                </div>
            </section>

            <section aria-labelledby="cta-heading">
                <div class="mx-auto max-w-5xl px-5 py-20 sm:px-8 lg:py-28">
                    <div
                        class="relative overflow-hidden rounded-3xl bg-stone-950 px-6 py-14 text-center text-white shadow-xl sm:px-12 sm:py-16 dark:bg-stone-900"
                    >
                        <div
                            class="absolute -top-24 -right-16 size-64 rounded-full bg-amber-400/20 blur-3xl"
                            aria-hidden="true"
                        />
                        <div
                            class="absolute -bottom-24 -left-16 size-64 rounded-full bg-emerald-400/15 blur-3xl"
                            aria-hidden="true"
                        />
                        <div class="relative mx-auto max-w-2xl">
                            <h2
                                id="cta-heading"
                                class="text-3xl font-bold tracking-tight sm:text-4xl"
                            >
                                Your next chapter starts here.
                            </h2>
                            <p class="mt-4 text-lg leading-8 text-stone-300">
                                Join BetaReadr as an author, a beta reader, or
                                both—and help make pre-publication feedback more
                                thoughtful.
                            </p>
                            <Link
                                v-if="!page.props.auth.user"
                                :href="register()"
                                class="mt-8 inline-flex min-h-12 items-center justify-center gap-2 rounded-lg bg-amber-400 px-6 py-3 font-semibold text-stone-950 shadow-sm transition hover:bg-amber-300 focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-white"
                            >
                                Register for BetaReadr
                                <ArrowRight class="size-4" aria-hidden="true" />
                            </Link>
                            <Link
                                v-else
                                :href="dashboard()"
                                class="mt-8 inline-flex min-h-12 items-center justify-center gap-2 rounded-lg bg-amber-400 px-6 py-3 font-semibold text-stone-950 shadow-sm transition hover:bg-amber-300 focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-white"
                            >
                                Go to your Dashboard
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
                <p class="font-semibold text-foreground">BetaReadr</p>
                <p>Thoughtful feedback for books before publication.</p>
            </div>
        </footer>
    </div>
</template>
