<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { formatCurrency, formatDate } from '@/lib/format';
import {
    Card,
    CardContent,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';

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
    category: StayCategory;
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
        title: props.category.label,
        href: `/stay-categories/${props.category.id}`,
    },
];

const deleteCategory = () => {
    if (confirm('Are you sure you want to delete this category?')) {
        router.delete(`/stay-categories/${props.category.id}`);
    }
};
</script>

<template>
    <Head :title="category.label" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-bold">{{ category.label }}</h1>
                <div class="flex gap-2">
                    <Link :href="`/stay-categories/${category.id}/edit`">
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
                            {{ category.stays?.length || 0 }}
                        </p>
                    </div>
                </CardContent>
            </Card>

            <div v-if="category.stays?.length" class="space-y-4">
                <h2 class="text-xl font-semibold">Stays</h2>
                <div class="rounded-md border">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Property</TableHead>
                                <TableHead>Accommodation</TableHead>
                                <TableHead>Period</TableHead>
                                <TableHead>Price</TableHead>
                                <TableHead class="text-right">Actions</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow
                                v-for="stay in category.stays"
                                :key="stay.id"
                            >
                                <TableCell class="font-medium">{{
                                    stay.accommodation.property.label
                                }}</TableCell>
                                <TableCell>{{
                                    stay.accommodation.label
                                }}</TableCell>
                                <TableCell
                                    >{{ formatDate(stay.start_date) }} -
                                    {{ formatDate(stay.end_date) }}</TableCell
                                >
                                <TableCell>{{
                                    formatCurrency(stay.price)
                                }}</TableCell>
                                <TableCell class="text-right">
                                    <Link :href="`/stays/${stay.id}`">
                                        <Button variant="outline" size="sm"
                                            >View</Button
                                        >
                                    </Link>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
