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
}

interface ExpenseCategory {
    id: number;
    label: string;
}

interface Expense {
    id: number;
    label: string;
    price: number;
    description: string | null;
    property: Property;
    accommodation: Accommodation | null;
    expense_category: ExpenseCategory;
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
    expenses: {
        data: Expense[];
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
        title: 'Expenses',
        href: '/expenses',
    },
];

const deleteExpense = (id: number) => {
    if (confirm('Are you sure you want to delete this expense?')) {
        router.delete(`/expenses/${id}`);
    }
};

const formatCurrency = (value: number) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(value);
};
</script>

<template>
    <Head title="Expenses" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-bold">Expenses</h1>
                <Link href="/expenses/create">
                    <Button>Create Expense</Button>
                </Link>
            </div>

            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                <Card v-for="expense in expenses.data" :key="expense.id">
                    <CardHeader>
                        <CardTitle>{{ expense.label }}</CardTitle>
                        <CardDescription>
                            {{ expense.expense_category.label }}
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-2">
                        <div>
                            <p class="text-sm font-medium">Property</p>
                            <p class="text-sm text-muted-foreground">
                                {{ expense.property.label }}
                            </p>
                        </div>
                        <div v-if="expense.accommodation">
                            <p class="text-sm font-medium">Accommodation</p>
                            <p class="text-sm text-muted-foreground">
                                {{ expense.accommodation.label }}
                            </p>
                        </div>
                        <div>
                            <p class="text-sm font-medium">Price</p>
                            <p class="text-sm font-semibold">
                                {{ formatCurrency(expense.price) }}
                            </p>
                        </div>
                        <div class="flex gap-2">
                            <Link :href="`/expenses/${expense.id}`">
                                <Button variant="outline" size="sm">View</Button>
                            </Link>
                            <Link :href="`/expenses/${expense.id}/edit`">
                                <Button variant="outline" size="sm">Edit</Button>
                            </Link>
                            <Button
                                variant="destructive"
                                size="sm"
                                @click="deleteExpense(expense.id)"
                            >
                                Delete
                            </Button>
                        </div>
                    </CardContent>
                </Card>
            </div>

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
