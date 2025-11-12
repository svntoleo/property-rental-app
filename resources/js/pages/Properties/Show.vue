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
    address: string;
    description: string | null;
    accommodations?: any[];
    expenses?: any[];
}

interface Props {
    property: Property;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Properties',
        href: '/properties',
    },
    {
        title: props.property.label,
        href: `/properties/${props.property.id}`,
    },
];

const deleteProperty = () => {
    if (confirm('Are you sure you want to delete this property?')) {
        router.delete(`/properties/${props.property.id}`);
    }
};
</script>

<template>
    <Head :title="property.label" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-bold">{{ property.label }}</h1>
                <div class="flex gap-2">
                    <Link :href="`/properties/${property.id}/edit`">
                        <Button variant="outline">Edit</Button>
                    </Link>
                    <Button variant="destructive" @click="deleteProperty"
                        >Delete</Button
                    >
                </div>
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                <Card>
                    <CardHeader>
                        <CardTitle>Property Details</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-2">
                        <div>
                            <p class="text-sm font-medium">Address</p>
                            <p class="text-sm text-muted-foreground">
                                {{ property.address }}
                            </p>
                        </div>
                        <div v-if="property.description">
                            <p class="text-sm font-medium">Description</p>
                            <p class="text-sm text-muted-foreground">
                                {{ property.description }}
                            </p>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>Statistics</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-2">
                        <div>
                            <p class="text-sm font-medium">Accommodations</p>
                            <p class="text-2xl font-bold">
                                {{ property.accommodations?.length || 0 }}
                            </p>
                        </div>
                        <div>
                            <p class="text-sm font-medium">Expenses</p>
                            <p class="text-2xl font-bold">
                                {{ property.expenses?.length || 0 }}
                            </p>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <div v-if="property.accommodations?.length" class="grid gap-4">
                <h2 class="text-xl font-semibold">Accommodations</h2>
                <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                    <Card
                        v-for="accommodation in property.accommodations"
                        :key="accommodation.id"
                    >
                        <CardHeader>
                            <CardTitle>{{ accommodation.label }}</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <Link :href="`/accommodations/${accommodation.id}`">
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
