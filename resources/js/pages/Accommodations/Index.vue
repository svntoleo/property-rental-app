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

interface Property {
    id: number;
    label: string;
}

interface Accommodation {
    id: number;
    label: string;
    property: Property;
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
    accommodations: {
        data: Accommodation[];
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
        title: 'Accommodations',
        href: '/accommodations',
    },
];

const deleteAccommodation = (id: number) => {
    if (confirm('Are you sure you want to delete this accommodation?')) {
        router.delete(`/accommodations/${id}`);
    }
};
</script>

<template>
    <Head title="Accommodations" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-bold">Accommodations</h1>
                <Link href="/accommodations/create">
                    <Button>Create Accommodation</Button>
                </Link>
            </div>

            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                <Card
                    v-for="accommodation in accommodations.data"
                    :key="accommodation.id"
                >
                    <CardHeader>
                        <CardTitle>{{ accommodation.label }}</CardTitle>
                        <CardDescription>
                            {{ accommodation.property.label }}
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="flex gap-2">
                        <Link :href="`/accommodations/${accommodation.id}`">
                            <Button variant="outline" size="sm">View</Button>
                        </Link>
                        <Link :href="`/accommodations/${accommodation.id}/edit`">
                            <Button variant="outline" size="sm">Edit</Button>
                        </Link>
                        <Button
                            variant="destructive"
                            size="sm"
                            @click="deleteAccommodation(accommodation.id)"
                        >
                            Delete
                        </Button>
                    </CardContent>
                </Card>
            </div>

            <div
                v-if="accommodations.meta.last_page > 1"
                class="mt-4 flex items-center justify-center gap-2"
            >
                <Link
                    v-for="(link, index) in accommodations.meta.links"
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
