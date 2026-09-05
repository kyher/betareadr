<script setup lang="ts">
import { Form, Head, Link, usePage } from '@inertiajs/vue3';
import { Check, ExternalLink, UserRoundCheck, UserRoundX } from '@lucide/vue';
import BetaReadingListingReviewController from '@/actions/App/Http/Controllers/BetaReadingListingReviewController';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
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
import { show as showBetaReader } from '@/routes/beta-readers';
import type { ReadingLanguage } from '@/types';

type Book = { id: number; title: string };
type Listing = { id: number; closed_at: string | null };
type Reader = {
    id: number;
    match_id: number | null;
    created_at: string;
    profile: {
        display_name: string;
        handle: string;
        bio: string | null;
        reading_languages: ReadingLanguage[];
    };
};

defineProps<{
    book: Book;
    listing: Listing;
    interested: Reader[];
    approved: Reader[];
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

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Listings', href: '#' },
            { title: 'Review beta readers', href: '#' },
        ],
    },
});
</script>

<template>
    <Head :title="`${book.title} · ${t('Review beta readers')}`" />
    <div class="mx-auto flex w-full max-w-4xl flex-1 flex-col gap-8 p-4 md:p-8">
        <div>
            <p class="text-sm text-muted-foreground">{{ book.title }}</p>
            <h1 class="text-2xl font-semibold tracking-tight">
                {{ t('Review beta readers') }}
            </h1>
            <p class="mt-1 text-muted-foreground">
                {{
                    t(
                        listing.closed_at
                            ? 'This listing is closed, but existing interest can still be reviewed.'
                            : 'Approve or decline beta readers interested in this listing.',
                    )
                }}
            </p>
        </div>

        <section class="grid gap-4">
            <div>
                <h2 class="text-lg font-semibold">{{ t('Interested') }}</h2>
                <p class="text-sm text-muted-foreground">
                    {{ t('Oldest interest is shown first.') }}
                </p>
            </div>
            <Card v-if="interested.length === 0" class="border-dashed">
                <CardContent class="py-8 text-center text-muted-foreground">
                    {{ t('No beta readers are waiting for review.') }}
                </CardContent>
            </Card>
            <Card v-for="reader in interested" v-else :key="reader.id">
                <CardHeader>
                    <CardTitle>{{ reader.profile.display_name }}</CardTitle>
                    <CardDescription>
                        @{{ reader.profile.handle }} · {{ t('Interested') }}
                        {{ formatDate(reader.created_at) }}
                    </CardDescription>
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
                        <Form
                            v-bind="
                                BetaReadingListingReviewController.approve.form(
                                    {
                                        book: book.id,
                                        listing: listing.id,
                                        interest: reader.id,
                                    },
                                )
                            "
                            :options="{ preserveScroll: true }"
                            v-slot="{ processing }"
                        >
                            <Button size="sm" :disabled="processing">
                                <Check /> {{ t('Approve') }}
                            </Button>
                        </Form>
                        <Dialog>
                            <DialogTrigger as-child>
                                <Button variant="destructive" size="sm">
                                    <UserRoundX /> {{ t('Decline') }}
                                </Button>
                            </DialogTrigger>
                            <DialogContent>
                                <Form
                                    v-bind="
                                        BetaReadingListingReviewController.decline.form(
                                            {
                                                book: book.id,
                                                listing: listing.id,
                                                interest: reader.id,
                                            },
                                        )
                                    "
                                    :options="{ preserveScroll: true }"
                                    v-slot="{ processing }"
                                >
                                    <DialogHeader>
                                        <DialogTitle>{{
                                            t('Decline this interest?')
                                        }}</DialogTitle>
                                        <DialogDescription>{{
                                            t(
                                                'This is permanent and the beta reader will not be notified.',
                                            )
                                        }}</DialogDescription>
                                    </DialogHeader>
                                    <DialogFooter class="mt-6 gap-2">
                                        <DialogClose as-child
                                            ><Button
                                                type="button"
                                                variant="secondary"
                                                >{{ t('Cancel') }}</Button
                                            ></DialogClose
                                        >
                                        <Button
                                            type="submit"
                                            variant="destructive"
                                            :disabled="processing"
                                            >{{ t('Decline') }}</Button
                                        >
                                    </DialogFooter>
                                </Form>
                            </DialogContent>
                        </Dialog>
                    </div>
                </CardContent>
            </Card>
        </section>

        <section class="grid gap-4">
            <div>
                <h2 class="text-lg font-semibold">{{ t('Approved') }}</h2>
                <p class="text-sm text-muted-foreground">
                    {{ t('Most recently approved is shown first.') }}
                </p>
            </div>
            <Card v-if="approved.length === 0" class="border-dashed">
                <CardContent class="py-8 text-center text-muted-foreground">
                    {{ t('No approved beta readers yet.') }}
                </CardContent>
            </Card>
            <Card v-for="reader in approved" v-else :key="reader.id">
                <CardHeader>
                    <CardTitle>{{ reader.profile.display_name }}</CardTitle>
                    <CardDescription>
                        @{{ reader.profile.handle }} · {{ t('Interested') }}
                        {{ formatDate(reader.created_at) }}
                    </CardDescription>
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
                        <Dialog>
                            <DialogTrigger as-child>
                                <Button variant="destructive" size="sm">
                                    <UserRoundCheck /> {{ t('End match') }}
                                </Button>
                            </DialogTrigger>
                            <DialogContent>
                                <Form
                                    v-bind="
                                        BetaReadingListingReviewController.end.form(
                                            {
                                                book: book.id,
                                                listing: listing.id,
                                                match: reader.match_id!,
                                            },
                                        )
                                    "
                                    :options="{ preserveScroll: true }"
                                    v-slot="{ processing }"
                                >
                                    <DialogHeader>
                                        <DialogTitle>{{
                                            t('End this match?')
                                        }}</DialogTitle>
                                        <DialogDescription>{{
                                            t(
                                                'This is permanent. The original interest cannot be reconsidered.',
                                            )
                                        }}</DialogDescription>
                                    </DialogHeader>
                                    <DialogFooter class="mt-6 gap-2">
                                        <DialogClose as-child
                                            ><Button
                                                type="button"
                                                variant="secondary"
                                                >{{ t('Cancel') }}</Button
                                            ></DialogClose
                                        >
                                        <Button
                                            type="submit"
                                            variant="destructive"
                                            :disabled="processing"
                                            >{{ t('End match') }}</Button
                                        >
                                    </DialogFooter>
                                </Form>
                            </DialogContent>
                        </Dialog>
                    </div>
                </CardContent>
            </Card>
        </section>
    </div>
</template>
