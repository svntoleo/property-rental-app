<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { computed } from 'vue';
import {
    Card,
    CardContent,
    CardDescription,
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

interface Property {
    id: number;
    label: string;
    address: string;
    description: string | null;
    accommodations?: any[];
}

interface Expense {
    id: number;
    label: string;
    price: number;
    description: string | null;
    category?: {
        id: number;
        label: string;
    };
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
    sort_by?: string;
    sort_dir?: string;
}

interface Props {
    property: Property;
    expenses: {
        data: Expense[];
        meta: PaginationMeta;
    };
    sort_by?: string;
    sort_dir?: 'asc' | 'desc';
}


const props = defineProps<Props>();

import { ref } from 'vue';
import { router as inertiaRouter } from '@inertiajs/vue3';

// Get current sort from props or default
const sortBy = ref(props.sort_by || 'label');
const sortDir = ref(props.sort_dir || 'asc');

function applyExpenseParams(extra: Record<string, any> = {}) {
    const params: Record<string, any> = {};
    params.sort_by = sortBy.value;
    params.sort_dir = sortDir.value;
    Object.assign(params, extra);
    inertiaRouter.get(props.expenses.meta.path, params, { preserveScroll: true, preserveState: true });
}

function toggleExpenseSort(column: string) {
    if (sortBy.value === column) {
        sortDir.value = sortDir.value === 'asc' ? 'desc' : 'asc';
    } else {
        sortBy.value = column;
        sortDir.value = 'asc';
    }
    applyExpenseParams();
}

const breadcrumbs = computed<BreadcrumbItem[]>(() => [
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
]);

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
                                {{ expenses.meta.total }}
                            </p>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <div v-if="property.accommodations && property.accommodations.length > 0" class="grid gap-4">
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

            <div v-if="expenses.data.length > 0" class="grid gap-4">
                <h2 class="text-xl font-semibold">Expenses</h2>
                <Card>
                    <CardContent class="p-0">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>
                                        <button class="flex items-center gap-1" @click="toggleExpenseSort('label')">
                                            Label
                                            <span v-if="sortBy === 'label'">{{ sortDir === 'asc' ? '▲' : '▼' }}</span>
                                        </button>
                                    </TableHead>
                                    <TableHead>Category</TableHead>
                                    <TableHead>
                                        <button class="flex items-center gap-1" @click="toggleExpenseSort('price')">
                                            Price
                                            <span v-if="sortBy === 'price'">{{ sortDir === 'asc' ? '▲' : '▼' }}</span>
                                        </button>
                                    </TableHead>
                                    <TableHead>
                                        <button class="flex items-center gap-1" @click="toggleExpenseSort('description')">
                                            Description
                                            <span v-if="sortBy === 'description'">{{ sortDir === 'asc' ? '▲' : '▼' }}</span>
                                        </button>
                                    </TableHead>
                                    <TableHead>Actions</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow
                                    v-for="expense in expenses.data"
                                    :key="expense.id"
                                >
                                    <TableCell>{{ expense.label }}</TableCell>
                                    <TableCell>{{ expense.category?.label || 'N/A' }}</TableCell>
                                    <TableCell>${{ expense.price }}</TableCell>
                                    <TableCell>{{ expense.description || '-' }}</TableCell>
                                    <TableCell>
                                        <Link :href="`/expenses/${expense.id}`">
                                            <Button variant="outline" size="sm"
                                                >View Details</Button
                                            >
                                        </Link>
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </CardContent>
                </Card>

                <!-- Pagination -->
                <div
                    v-if="expenses.meta.last_page > 1"
                    class="mt-4 flex items-center justify-center gap-2"
                >
                    <Link
                        v-for="(link, index) in expenses.meta.links"
                        :key="index"
                        :href="link.url || '#'"
                        :class="{
                            'pointer-events-none': !link.url,
                        }"
                        @click.prevent="applyExpenseParams({ page: link.label })"
                        preserve-scroll
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
        </div>
    </AppLayout>
</template>
