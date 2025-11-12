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

interface Accommodation {
    id: number;
    label: string;
    property: {
        id: number;
        label: string;
    };
}

interface StayCategory {
    id: number;
    label: string;
}

interface Stay {
    id: number;
    start_date: string;
    end_date: string;
    due_date: string;
    price: number;
    category: StayCategory;
    accommodation: Accommodation;
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
    stays: {
        data: Stay[];
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
        title: 'Stays',
        href: '/stays',
    },
];

const deleteStay = (id: number) => {
    if (confirm('Are you sure you want to delete this stay?')) {
        router.delete(`/stays/${id}`);
    }
};

const formatCurrency = (value: number) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(value);
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
    <Head title="Stays" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-bold">Stays</h1>
                <Link href="/stays/create">
                    <Button>Create Stay</Button>
                </Link>
            </div>

            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                <Card v-for="stay in stays.data" :key="stay.id">
                    <CardHeader>
                        <CardTitle>{{ stay.category.label }}</CardTitle>
                        <CardDescription>
                            {{ stay.accommodation.property.label }} -
                            {{ stay.accommodation.label }}
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-2">
                        <div>
                            <p class="text-sm font-medium">Period</p>
                            <p class="text-sm text-muted-foreground">
                                {{ formatDate(stay.start_date) }} -
                                {{ formatDate(stay.end_date) }}
                            </p>
                        </div>
                        <div>
                            <p class="text-sm font-medium">Due Date</p>
                            <p class="text-sm text-muted-foreground">
                                {{ formatDate(stay.due_date) }}
                            </p>
                        </div>
                        <div>
                            <p class="text-sm font-medium">Price</p>
                            <p class="text-sm font-semibold">
                                {{ formatCurrency(stay.price) }}
                            </p>
                        </div>
                        <div class="flex gap-2">
                            <Link :href="`/stays/${stay.id}`">
                                <Button variant="outline" size="sm">View</Button>
                            </Link>
                            <Link :href="`/stays/${stay.id}/edit`">
                                <Button variant="outline" size="sm">Edit</Button>
                            </Link>
                            <Button
                                variant="destructive"
                                size="sm"
                                @click="deleteStay(stay.id)"
                            >
                                Delete
                            </Button>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <div
                v-if="stays.meta.last_page > 1"
                class="mt-4 flex items-center justify-center gap-2"
            >
                <Link
                    v-for="(link, index) in stays.meta.links"
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
