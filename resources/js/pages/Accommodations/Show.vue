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

interface StayCategory {
    id: number;
    label: string;
}

interface Stay {
    id: number;
    start_date: string;
    end_date: string;
    due_date: number | null;
    price: number;
    category: StayCategory;
}

interface Accommodation {
    id: number;
    label: string;
    property: Property;
    stays?: Stay[];
}

interface Props {
    accommodation: Accommodation;
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
    {
        title: props.accommodation.label,
        href: `/accommodations/${props.accommodation.id}`,
    },
];

const deleteAccommodation = () => {
    if (confirm('Are you sure you want to delete this accommodation?')) {
        router.delete(`/accommodations/${props.accommodation.id}`);
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
    <Head :title="accommodation.label" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-bold">{{ accommodation.label }}</h1>
                <div class="flex gap-2">
                    <Link :href="`/accommodations/${accommodation.id}/edit`">
                        <Button variant="outline">Edit</Button>
                    </Link>
                    <Button variant="destructive" @click="deleteAccommodation"
                        >Delete</Button
                    >
                </div>
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                <Card>
                    <CardHeader>
                        <CardTitle>Accommodation Details</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-2">
                        <div>
                            <p class="text-sm font-medium">Property</p>
                            <Link
                                :href="`/properties/${accommodation.property.id}`"
                                class="text-sm text-primary hover:underline"
                            >
                                {{ accommodation.property.label }}
                            </Link>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>Statistics</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-2">
                        <div>
                            <p class="text-sm font-medium">Total Stays</p>
                            <p class="text-2xl font-bold">
                                {{ accommodation.stays?.length || 0 }}
                            </p>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <div v-if="accommodation.stays?.length" class="grid gap-4">
                <h2 class="text-xl font-semibold">Stays</h2>
                <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                    <Card v-for="stay in accommodation.stays" :key="stay.id">
                        <CardHeader>
                            <CardTitle>{{
                                stay.category.label
                            }}</CardTitle>
                            <CardDescription>
                                {{ formatDate(stay.start_date) }} -
                                {{ formatDate(stay.end_date) }}
                            </CardDescription>
                        </CardHeader>
                        <CardContent class="space-y-2">
                            <div>
                                <p class="text-sm font-medium">Due Day of Month</p>
                                <p class="text-sm text-muted-foreground">
                                    {{ stay.due_date ? `Day ${stay.due_date}` : '-' }}
                                </p>
                            </div>
                            <div>
                                <p class="text-sm font-medium">Price</p>
                                <p class="text-lg font-semibold">
                                    {{ formatCurrency(stay.price) }}
                                </p>
                            </div>
                            <Link :href="`/stays/${stay.id}`">
                                <Button variant="outline" size="sm"
                                    >View Details</Button
                                >
                            </Link>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
