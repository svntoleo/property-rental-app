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
    created_at: string;
    updated_at: string;
}

interface Props {
    properties: {
        data: Property[];
        meta: {
            current_page: number;
            last_page: number;
            total: number;
        };
    };
}

defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Properties',
        href: '/properties',
    },
];

const deleteProperty = (id: number) => {
    if (confirm('Are you sure you want to delete this property?')) {
        router.delete(`/properties/${id}`);
    }
};
</script>

<template>
    <Head title="Properties" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-bold">Properties</h1>
                <Link href="/properties/create">
                    <Button>Create Property</Button>
                </Link>
            </div>

            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                <Card v-for="property in properties.data" :key="property.id">
                    <CardHeader>
                        <CardTitle>{{ property.label }}</CardTitle>
                        <CardDescription>{{
                            property.address
                        }}</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <p class="mb-4 text-sm text-muted-foreground">
                            {{ property.description || 'No description' }}
                        </p>
                        <div class="flex gap-2">
                            <Link :href="`/properties/${property.id}`">
                                <Button variant="outline" size="sm"
                                    >View</Button
                                >
                            </Link>
                            <Link :href="`/properties/${property.id}/edit`">
                                <Button variant="outline" size="sm"
                                    >Edit</Button
                                >
                            </Link>
                            <Button
                                variant="destructive"
                                size="sm"
                                @click="deleteProperty(property.id)"
                                >Delete</Button
                            >
                        </div>
                    </CardContent>
                </Card>
            </div>

            <div
                v-if="properties.meta.last_page > 1"
                class="flex justify-center gap-2"
            >
                <Button
                    v-for="page in properties.meta.last_page"
                    :key="page"
                    :variant="
                        page === properties.meta.current_page
                            ? 'default'
                            : 'outline'
                    "
                    size="sm"
                    @click="router.visit(`/properties?page=${page}`)"
                >
                    {{ page }}
                </Button>
            </div>
        </div>
    </AppLayout>
</template>
