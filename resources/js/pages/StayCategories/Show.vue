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
    price: number;
    accommodation: {
        label: string;
        property: {
            label: string;
        };
    };
}

interface StayCategory {
    id: number;
    label: string;
    stays?: Stay[];
}

interface Props {
    stayCategory: StayCategory;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Stay Categories',
        href: '/stay-categories',
    },
    {
        title: props.stayCategory.label,
        href: `/stay-categories/${props.stayCategory.id}`,
    },
];

const deleteCategory = () => {
    if (confirm('Are you sure you want to delete this category?')) {
        router.delete(`/stay-categories/${props.stayCategory.id}`);
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
    <Head :title="stayCategory.label" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-bold">{{ stayCategory.label }}</h1>
                <div class="flex gap-2">
                    <Link :href="`/stay-categories/${stayCategory.id}/edit`">
                        <Button variant="outline">Edit</Button>
                    </Link>
                    <Button variant="destructive" @click="deleteCategory"
                        >Delete</Button
                    >
                </div>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>Statistics</CardTitle>
                </CardHeader>
                <CardContent>
                    <div>
                        <p class="text-sm font-medium">Total Stays</p>
                        <p class="text-2xl font-bold">
                            {{ stayCategory.stays?.length || 0 }}
                        </p>
                    </div>
                </CardContent>
            </Card>

            <div v-if="stayCategory.stays?.length" class="grid gap-4">
                <h2 class="text-xl font-semibold">Stays</h2>
                <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                    <Card v-for="stay in stayCategory.stays" :key="stay.id">
                        <CardHeader>
                            <CardTitle>
                                {{ stay.accommodation.property.label }}
                            </CardTitle>
                            <CardDescription>
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
                                <p class="text-sm font-medium">Price</p>
                                <p class="text-sm font-semibold">
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
