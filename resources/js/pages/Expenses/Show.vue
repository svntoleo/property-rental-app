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

interface Props {
    expense: Expense;
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
    {
        title: props.expense.label,
        href: `/expenses/${props.expense.id}`,
    },
];

const deleteExpense = () => {
    if (confirm('Are you sure you want to delete this expense?')) {
        router.delete(`/expenses/${props.expense.id}`);
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
    <Head :title="expense.label" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-bold">{{ expense.label }}</h1>
                <div class="flex gap-2">
                    <Link :href="`/expenses/${expense.id}/edit`">
                        <Button variant="outline">Edit</Button>
                    </Link>
                    <Button variant="destructive" @click="deleteExpense"
                        >Delete</Button
                    >
                </div>
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                <Card>
                    <CardHeader>
                        <CardTitle>Expense Details</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-2">
                        <div>
                            <p class="text-sm font-medium">Category</p>
                            <p class="text-sm text-muted-foreground">
                                {{ expense.expense_category.label }}
                            </p>
                        </div>
                        <div>
                            <p class="text-sm font-medium">Property</p>
                            <Link
                                :href="`/properties/${expense.property.id}`"
                                class="text-sm text-primary hover:underline"
                            >
                                {{ expense.property.label }}
                            </Link>
                        </div>
                        <div v-if="expense.accommodation">
                            <p class="text-sm font-medium">Accommodation</p>
                            <Link
                                :href="`/accommodations/${expense.accommodation.id}`"
                                class="text-sm text-primary hover:underline"
                            >
                                {{ expense.accommodation.label }}
                            </Link>
                        </div>
                        <div v-if="expense.description">
                            <p class="text-sm font-medium">Description</p>
                            <p class="text-sm text-muted-foreground">
                                {{ expense.description }}
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
                            <p class="text-sm font-medium">Amount</p>
                            <p class="text-2xl font-bold">
                                {{ formatCurrency(expense.price) }}
                            </p>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
