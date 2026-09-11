<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowRight, Eye, Pencil, UserRound } from '@lucide/vue';
import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { getInitials } from '@/composables/useInitials';
import { useTranslation } from '@/composables/useTranslation';
import { dashboard } from '@/routes';
import {
    create as createAuthorProfile,
    edit as editAuthorProfile,
} from '@/routes/author-profile';
import { show as showAuthorProfile } from '@/routes/authors';
import {
    create as createBetaReaderProfile,
    edit as editBetaReaderProfile,
} from '@/routes/beta-reader-profile';
import { show as showBetaReaderProfile } from '@/routes/beta-readers';
import type { AuthorProfile, BetaReaderProfile } from '@/types';

defineProps<{
    authorProfile: AuthorProfile | null;
    betaReaderProfile: BetaReaderProfile | null;
}>();

const { t } = useTranslation();

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Dashboard', href: dashboard() }],
    },
});
</script>

<template>
    <Head :title="t('Dashboard')" />

    <div class="mx-auto flex w-full max-w-4xl flex-1 flex-col gap-6 p-4 md:p-8">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight">
                {{ t('Dashboard') }}
            </h1>
            <p class="mt-1 text-muted-foreground">
                {{ t('Manage how you take part in BetaReadr.') }}
            </p>
        </div>

        <Card v-if="authorProfile">
            <CardHeader>
                <CardTitle>{{ t('Your author profile') }}</CardTitle>
                <CardDescription>{{
                    t('This is the public identity readers see on BetaReadr.')
                }}</CardDescription>
            </CardHeader>
            <CardContent
                class="flex flex-col gap-6 sm:flex-row sm:items-center"
            >
                <Avatar class="size-16">
                    <AvatarFallback class="text-lg font-semibold">
                        {{ getInitials(authorProfile.display_name) }}
                    </AvatarFallback>
                </Avatar>
                <div class="min-w-0 flex-1">
                    <p class="truncate text-lg font-semibold">
                        {{ authorProfile.display_name }}
                    </p>
                    <p class="text-sm text-muted-foreground">
                        @{{ authorProfile.handle }}
                    </p>
                    <p
                        v-if="authorProfile.bio"
                        class="mt-2 line-clamp-2 text-sm"
                    >
                        {{ authorProfile.bio }}
                    </p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <Button variant="outline" as-child>
                        <Link :href="showAuthorProfile(authorProfile.handle)"
                            ><Eye /> {{ t('View') }}</Link
                        >
                    </Button>
                    <Button as-child>
                        <Link :href="editAuthorProfile()"
                            ><Pencil /> {{ t('Edit') }}</Link
                        >
                    </Button>
                </div>
            </CardContent>
        </Card>

        <Card v-else class="border-dashed">
            <CardHeader>
                <div
                    class="mb-3 flex size-12 items-center justify-center rounded-full bg-muted"
                >
                    <UserRound class="size-6" />
                </div>
                <CardTitle>{{ t('Create your author profile') }}</CardTitle>
                <CardDescription class="max-w-2xl">
                    {{
                        t(
                            'Introduce yourself to beta readers with a public name, unique handle, and optional bio.',
                        )
                    }}
                </CardDescription>
            </CardHeader>
            <CardContent>
                <Button as-child>
                    <Link :href="createAuthorProfile()"
                        >{{ t('Create author profile') }} <ArrowRight
                    /></Link>
                </Button>
            </CardContent>
        </Card>

        <Card v-if="betaReaderProfile">
            <CardHeader>
                <CardTitle>{{ t('Your beta reader profile') }}</CardTitle>
                <CardDescription>{{
                    t('This is the public identity authors see on BetaReadr.')
                }}</CardDescription>
            </CardHeader>
            <CardContent
                class="flex flex-col gap-6 sm:flex-row sm:items-center"
            >
                <Avatar class="size-16">
                    <AvatarFallback class="text-lg font-semibold">
                        {{ getInitials(betaReaderProfile.display_name) }}
                    </AvatarFallback>
                </Avatar>
                <div class="min-w-0 flex-1">
                    <p class="truncate text-lg font-semibold">
                        {{ betaReaderProfile.display_name }}
                    </p>
                    <p class="text-sm text-muted-foreground">
                        @{{ betaReaderProfile.handle }}
                    </p>
                    <p
                        v-if="betaReaderProfile.bio"
                        class="mt-2 line-clamp-2 text-sm"
                    >
                        {{ betaReaderProfile.bio }}
                    </p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <Button variant="outline" as-child>
                        <Link
                            :href="
                                showBetaReaderProfile(betaReaderProfile.handle)
                            "
                            ><Eye /> {{ t('View') }}</Link
                        >
                    </Button>
                    <Button as-child>
                        <Link :href="editBetaReaderProfile()"
                            ><Pencil /> {{ t('Edit') }}</Link
                        >
                    </Button>
                </div>
            </CardContent>
        </Card>

        <Card v-else class="border-dashed">
            <CardHeader>
                <div
                    class="mb-3 flex size-12 items-center justify-center rounded-full bg-muted"
                >
                    <UserRound class="size-6" />
                </div>
                <CardTitle>{{
                    t('Create your beta reader profile')
                }}</CardTitle>
                <CardDescription class="max-w-2xl">
                    {{
                        t(
                            'Introduce yourself to authors with a public name, unique handle, and optional bio.',
                        )
                    }}
                </CardDescription>
            </CardHeader>
            <CardContent>
                <Button as-child>
                    <Link :href="createBetaReaderProfile()"
                        >{{ t('Create beta reader profile') }} <ArrowRight
                    /></Link>
                </Button>
            </CardContent>
        </Card>
    </div>
</template>
