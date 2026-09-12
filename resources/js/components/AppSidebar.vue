<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { BookCopy, Handshake, LayoutGrid, Rows3 } from '@lucide/vue';
import { computed } from 'vue';
import AppLogo from '@/components/AppLogo.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { useTranslation } from '@/composables/useTranslation';
import { dashboard } from '@/routes';
import { index as books } from '@/routes/books';
import type { NavItem } from '@/types';

const page = usePage();
const { t } = useTranslation();
const mainNavItems = computed<NavItem[]>(() => [
    { title: t('Dashboard'), href: dashboard(), icon: LayoutGrid },
    ...(page.props.auth.has_beta_reader_profile
        ? [{ title: t('Feed'), href: '/feed', icon: Rows3 }]
        : []),
    ...(page.props.auth.has_author_profile
        ? [{ title: t('Books'), href: books(), icon: BookCopy }]
        : []),
    ...(page.props.auth.has_author_profile ||
    page.props.auth.has_beta_reader_profile
        ? [{ title: t('Matches'), href: '/matches', icon: Handshake }]
        : []),
]);
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboard()">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
