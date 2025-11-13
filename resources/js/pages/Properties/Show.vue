<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { computed } from 'vue';
import { formatDate, formatCurrency } from '@/lib/format';
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
}

interface Accommodation {
    id: number;
    label: string;
}

interface Expense {
    id: number;
    label: string;
    price: number;
    date: string;
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
}

interface Props {
    property: Property;
    expenses: {
        data: Expense[];
        meta: PaginationMeta;
    };
    expense_search: string;
    expense_sort_by?: string;
    expense_sort_dir?: 'asc' | 'desc';
    accommodations: {
        data: Accommodation[];
        meta: PaginationMeta;
    };
    accommodation_search: string;
    accommodation_sort_by?: string;
    accommodation_sort_dir?: 'asc' | 'desc';
}


const props = defineProps<Props>();

import { ref, watch } from 'vue';
import { router as inertiaRouter } from '@inertiajs/vue3';

// Expenses search and sorting
const expenseSearchQuery = ref(props.expense_search || '');
const expenseSortBy = ref(props.expense_sort_by || 'date');
const expenseSortDir = ref(props.expense_sort_dir || 'desc');

let expenseDebounceHandle: ReturnType<typeof setTimeout> | undefined;

function applyExpenseParams(extra: Record<string, any> = {}) {
    const params: Record<string, any> = {};
    if (expenseSearchQuery.value) params.expense_search = expenseSearchQuery.value;
    params.expense_sort_by = expenseSortBy.value;
    params.expense_sort_dir = expenseSortDir.value;
    Object.assign(params, extra);
    inertiaRouter.get(props.expenses.meta.path, params, { preserveScroll: true, preserveState: true });
}

function toggleExpenseSort(column: string) {
    if (expenseSortBy.value === column) {
        expenseSortDir.value = expenseSortDir.value === 'asc' ? 'desc' : 'asc';
    } else {
        expenseSortBy.value = column;
        expenseSortDir.value = 'asc';
    }
    applyExpenseParams();
}

watch(expenseSearchQuery, () => {
    if (expenseDebounceHandle) clearTimeout(expenseDebounceHandle);
    expenseDebounceHandle = setTimeout(() => {
        applyExpenseParams();
    }, 350);
});

// Accommodations search and sorting
const accommodationSearchQuery = ref(props.accommodation_search || '');
const accommodationSortBy = ref(props.accommodation_sort_by || 'label');
const accommodationSortDir = ref(props.accommodation_sort_dir || 'asc');

let accommodationDebounceHandle: ReturnType<typeof setTimeout> | undefined;

function applyAccommodationParams(extra: Record<string, any> = {}) {
    const params: Record<string, any> = {};
    if (accommodationSearchQuery.value) params.accommodation_search = accommodationSearchQuery.value;
    params.accommodation_sort_by = accommodationSortBy.value;
    params.accommodation_sort_dir = accommodationSortDir.value;
    Object.assign(params, extra);
    inertiaRouter.get(props.accommodations.meta.path, params, { preserveScroll: true, preserveState: true });
}

function toggleAccommodationSort(column: string) {
    if (accommodationSortBy.value === column) {
        accommodationSortDir.value = accommodationSortDir.value === 'asc' ? 'desc' : 'asc';
    } else {
        accommodationSortBy.value = column;
        accommodationSortDir.value = 'asc';
    }
    applyAccommodationParams();
}

watch(accommodationSearchQuery, () => {
    if (accommodationDebounceHandle) clearTimeout(accommodationDebounceHandle);
    accommodationDebounceHandle = setTimeout(() => {
        applyAccommodationParams();
    }, 350);
});

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

// using shared formatDate util
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
                                {{ accommodations.meta.total }}
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

            <div v-if="accommodations.data.length > 0" class="grid gap-4">
                <h2 class="text-xl font-semibold">Accommodations</h2>
                
                <div class="flex items-center gap-2">
                    <Input
                        v-model="accommodationSearchQuery"
                        placeholder="Search accommodations..."
                        class="max-w-sm"
                    />
                </div>

                <div class="rounded-md border">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>
                                    <button class="flex items-center gap-1" @click="toggleAccommodationSort('label')">
                                        Label
                                        <span v-if="accommodationSortBy === 'label'">{{ accommodationSortDir === 'asc' ? '▲' : '▼' }}</span>
                                    </button>
                                </TableHead>
                                <TableHead class="text-right">Actions</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow
                                v-for="accommodation in accommodations.data"
                                :key="accommodation.id"
                            >
                                <TableCell class="font-medium">{{ accommodation.label }}</TableCell>
                                <TableCell class="text-right">
                                    <Link :href="`/accommodations/${accommodation.id}`">
                                        <Button variant="outline" size="sm">View Details</Button>
                                    </Link>
                                </TableCell>
                            </TableRow>
                            <TableRow v-if="accommodations.data.length === 0">
                                <TableCell colspan="2" class="text-center">
                                    No accommodations found
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>

                <!-- Pagination -->
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
                        @click.prevent="applyAccommodationParams({ page: link.label })"
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

            <div v-if="expenses.data.length > 0" class="grid gap-4">
                <h2 class="text-xl font-semibold">Expenses</h2>
                
                <div class="flex items-center gap-2">
                    <Input
                        v-model="expenseSearchQuery"
                        placeholder="Search expenses..."
                        class="max-w-sm"
                    />
                </div>
                <Card>
                    <CardContent class="p-0">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>
                                        <button class="flex items-center gap-1" @click="toggleExpenseSort('label')">
                                            Label
                                            <span v-if="expenseSortBy === 'label'">{{ expenseSortDir === 'asc' ? '▲' : '▼' }}</span>
                                        </button>
                                    </TableHead>
                                    <TableHead>Category</TableHead>
                                    <TableHead>
                                        <button class="flex items-center gap-1" @click="toggleExpenseSort('price')">
                                            Price
                                            <span v-if="expenseSortBy === 'price'">{{ expenseSortDir === 'asc' ? '▲' : '▼' }}</span>
                                        </button>
                                    </TableHead>
                                    <TableHead>
                                        <button class="flex items-center gap-1" @click="toggleExpenseSort('date')">
                                            Date
                                            <span v-if="expenseSortBy === 'date'">{{ expenseSortDir === 'asc' ? '▲' : '▼' }}</span>
                                        </button>
                                    </TableHead>
                                    <TableHead>
                                        <button class="flex items-center gap-1" @click="toggleExpenseSort('description')">
                                            Description
                                            <span v-if="expenseSortBy === 'description'">{{ expenseSortDir === 'asc' ? '▲' : '▼' }}</span>
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
                                    <TableCell>{{ formatCurrency(expense.price) }}</TableCell>
                                    <TableCell>{{ formatDate(expense.date) }}</TableCell>
                                    <TableCell>{{ expense.description || '-' }}</TableCell>
                                    <TableCell>
                                        <Link :href="`/expenses/${expense.id}`">
                                            <Button variant="outline" size="sm"
                                                >View Details</Button
                                            >
                                        </Link>
                                    </TableCell>
                                </TableRow>
                                <TableRow v-if="expenses.data.length === 0">
                                    <TableCell colspan="6" class="text-center">
                                        No expenses found
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
