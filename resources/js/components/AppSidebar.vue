<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
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
import { dashboard } from '@/routes';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/vue3';
import {
    BookOpen,
    Folder,
    LayoutGrid,
    Building2,
    BedDouble,
    Calendar,
    Users,
    ReceiptText,
    Tags,
} from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';

// Core application navigation reflecting main domain resources.
const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
        icon: LayoutGrid,
    },
    {
        title: 'Properties',
        href: '/properties',
        icon: Building2,
    },
    {
        title: 'Accommodations',
        href: '/accommodations',
        icon: BedDouble,
    },
    {
        title: 'Stays',
        href: '/stays',
        icon: Calendar,
    },
    {
        title: 'Tenants',
        href: '/tenants',
        icon: Users,
    },
    {
        title: 'Expenses',
        href: '/expenses',
        icon: ReceiptText,
    },
    {
        title: 'Stay Categories',
        href: '/stay-categories',
        icon: Tags,
    },
    {
        title: 'Expense Categories',
        href: '/expense-categories',
        icon: Tags,
    },
];

const footerNavItems: NavItem[] = [
    {
        title: 'Github Repo',
        href: 'https://github.com/laravel/vue-starter-kit',
        icon: Folder,
    },
    {
        title: 'Documentation',
        href: 'https://laravel.com/docs/starter-kits#vue',
        icon: BookOpen,
    },
];
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
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
