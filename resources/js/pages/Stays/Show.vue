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

interface Tenant {
    id: number;
    name: string;
    email: string;
}

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
    price: number;
    accommodation: Accommodation;
    stay_category: StayCategory;
    tenants?: Tenant[];
}

interface Props {
    stay: Stay;
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
    {
        title: props.stay.stay_category.label,
        href: `/stays/${props.stay.id}`,
    },
];

const deleteStay = () => {
    if (confirm('Are you sure you want to delete this stay?')) {
        router.delete(`/stays/${props.stay.id}`);
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
    <Head :title="stay.stay_category.label" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-bold">
                    {{ stay.stay_category.label }}
                </h1>
                <div class="flex gap-2">
                    <Link :href="`/stays/${stay.id}/edit`">
                        <Button variant="outline">Edit</Button>
                    </Link>
                    <Button variant="destructive" @click="deleteStay"
                        >Delete</Button
                    >
                </div>
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                <Card>
                    <CardHeader>
                        <CardTitle>Stay Details</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-2">
                        <div>
                            <p class="text-sm font-medium">Accommodation</p>
                            <Link
                                :href="`/accommodations/${stay.accommodation.id}`"
                                class="text-sm text-primary hover:underline"
                            >
                                {{ stay.accommodation.property.label }} -
                                {{ stay.accommodation.label }}
                            </Link>
                        </div>
                        <div>
                            <p class="text-sm font-medium">Period</p>
                            <p class="text-sm text-muted-foreground">
                                {{ formatDate(stay.start_date) }} -
                                {{ formatDate(stay.end_date) }}
                            </p>
                        </div>
                        <div>
                            <p class="text-sm font-medium">Category</p>
                            <p class="text-sm text-muted-foreground">
                                {{ stay.stay_category.label }}
                            </p>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>Pricing</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div>
                            <p class="text-sm font-medium">Total Price</p>
                            <p class="text-2xl font-bold">
                                {{ formatCurrency(stay.price) }}
                            </p>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <div v-if="stay.tenants?.length" class="grid gap-4">
                <h2 class="text-xl font-semibold">Tenants</h2>
                <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                    <Card v-for="tenant in stay.tenants" :key="tenant.id">
                        <CardHeader>
                            <CardTitle>{{ tenant.name }}</CardTitle>
                            <CardDescription>{{ tenant.email }}</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <Link :href="`/tenants/${tenant.id}`">
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
