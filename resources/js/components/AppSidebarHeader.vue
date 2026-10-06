<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { MessageSquare } from '@lucide/vue';
import type Echo from 'laravel-echo';
import { onMounted, onUnmounted, watch } from 'vue';
import AppearanceToggle from '@/components/AppearanceToggle.vue';
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import { SidebarTrigger } from '@/components/ui/sidebar';
import { useTranslation } from '@/composables/useTranslation';
import { createConversationEcho } from '@/lib/conversationEcho';
import { refreshUnreadMessages as refresh } from '@/lib/refreshUnreadMessages';
import { index as matchesIndex } from '@/routes/matches';
import type { BreadcrumbItem } from '@/types';

const page = usePage();
const { t } = useTranslation();
let echo: Echo<'reverb'> | undefined;

function subscribe() {
    echo?.disconnect();
    echo = undefined;

    if (!page.props.auth.user) {
        return;
    }

    echo = createConversationEcho();
    echo?.private(`users.${page.props.auth.user.id}`)
        .listen('ConversationMessageSent', refresh)
        .listen('UnreadMessagesChanged', refresh)
        .subscribed(refresh);
}

onMounted(() => {
    subscribe();
    window.addEventListener('focus', refresh);
});
watch(() => page.props.auth.user?.id, subscribe);
onUnmounted(() => {
    echo?.disconnect();
    window.removeEventListener('focus', refresh);
});

withDefaults(
    defineProps<{
        breadcrumbs?: BreadcrumbItem[];
    }>(),
    {
        breadcrumbs: () => [],
    },
);
</script>

<template>
    <header
        class="flex h-16 shrink-0 items-center gap-2 border-b border-sidebar-border/70 px-6 transition-[width,height] ease-linear group-has-data-[collapsible=icon]/sidebar-wrapper:h-12 md:px-4"
    >
        <div class="flex items-center gap-2">
            <SidebarTrigger class="-ml-1" />
            <template v-if="breadcrumbs && breadcrumbs.length > 0">
                <Breadcrumbs :breadcrumbs="breadcrumbs" />
            </template>
        </div>
        <Link
            v-if="page.props.auth.user"
            :href="matchesIndex()"
            class="ml-auto flex items-center gap-1 rounded-md p-2 hover:bg-accent"
            :aria-label="`${t('Conversation')} · ${page.props.unreadMessageCount} ${t('unread')}`"
        >
            <MessageSquare class="size-5" />
            <span
                v-if="page.props.unreadMessageCount"
                class="rounded-full bg-primary px-2 text-xs font-medium text-primary-foreground"
                aria-live="polite"
            >
                {{ page.props.unreadMessageCount }}
            </span>
        </Link>
        <AppearanceToggle />
    </header>
</template>
