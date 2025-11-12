<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';

interface Stay {
    id: number;
    start_date: string;
    end_date: string;
    accommodation: {
        label: string;
        property: {
            label: string;
        };
    };
}

interface Tenant {
    id: number;
    name: string;
    email: string;
    phone: string;
    cpf: string;
    stay: Stay;
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface PaginationMeta {
    current_page: number;
    from: number;
    last_page: number;
    links: PaginationLink[];
    path: string;
    per_page: number;
    to: number;
    total: number;
}

interface Props {
    tenants: {
        data: Tenant[];
        meta: PaginationMeta;
    };
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Tenants',
        href: '/tenants',
    },
];

const deleteTenant = (id: number) => {
    if (confirm('Are you sure you want to delete this tenant?')) {
        router.delete(`/tenants/${id}`);
    }
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};
</script>

<template>
    <Head title="Tenants" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-bold">Tenants</h1>
                <Link href="/tenants/create">
                    <Button>Create Tenant</Button>
                </Link>
            </div>

            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                <Card v-for="tenant in tenants.data" :key="tenant.id">
                    <CardHeader>
                        <CardTitle>{{ tenant.name }}</CardTitle>
                        <CardDescription>{{ tenant.email }}</CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-2">
                        <div>
                            <p class="text-sm font-medium">Stay</p>
                            <p class="text-sm text-muted-foreground">
                                {{ tenant.stay.accommodation.property.label }} -
                                {{ tenant.stay.accommodation.label }}
                            </p>
                            <p class="text-xs text-muted-foreground">
                                {{ formatDate(tenant.stay.start_date) }} -
                                {{ formatDate(tenant.stay.end_date) }}
                            </p>
                        </div>
                        <div class="flex gap-2">
                            <Link :href="`/tenants/${tenant.id}`">
                                <Button variant="outline" size="sm">View</Button>
                            </Link>
                            <Link :href="`/tenants/${tenant.id}/edit`">
                                <Button variant="outline" size="sm">Edit</Button>
                            </Link>
                            <Button
                                variant="destructive"
                                size="sm"
                                @click="deleteTenant(tenant.id)"
                            >
                                Delete
                            </Button>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <div
                v-if="tenants.meta.last_page > 1"
                class="mt-4 flex items-center justify-center gap-2"
            >
                <Link
                    v-for="(link, index) in tenants.meta.links"
                    :key="index"
                    :href="link.url || '#'"
                    :class="{
                        'pointer-events-none': !link.url,
                    }"
                >
                    <Button
                        :variant="link.active ? 'default' : 'outline'"
                        size="sm"
                        :disabled="!link.url"
                        v-html="link.label"
                    />
                </Link>
            </div>
        </div>
    </AppLayout>
</template>
