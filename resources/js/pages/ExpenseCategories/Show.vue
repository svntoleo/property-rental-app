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
    label: string;
}

interface Accommodation {
    label: string;
}

interface Expense {
    id: number;
    label: string;
    price: number;
    property: Property;
    accommodation: Accommodation | null;
}

interface ExpenseCategory {
    id: number;
    label: string;
    expenses?: Expense[];
}

interface Props {
    expenseCategory: ExpenseCategory;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Expense Categories',
        href: '/expense-categories',
    },
    {
        title: props.expenseCategory.label,
        href: `/expense-categories/${props.expenseCategory.id}`,
    },
];

const deleteCategory = () => {
    if (confirm('Are you sure you want to delete this category?')) {
        router.delete(`/expense-categories/${props.expenseCategory.id}`);
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
    <Head :title="expenseCategory.label" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-bold">{{ expenseCategory.label }}</h1>
                <div class="flex gap-2">
                    <Link
                        :href="`/expense-categories/${expenseCategory.id}/edit`"
                    >
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
                        <p class="text-sm font-medium">Total Expenses</p>
                        <p class="text-2xl font-bold">
                            {{ expenseCategory.expenses?.length || 0 }}
                        </p>
                    </div>
                </CardContent>
            </Card>

            <div v-if="expenseCategory.expenses?.length" class="grid gap-4">
                <h2 class="text-xl font-semibold">Expenses</h2>
                <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                    <Card
                        v-for="expense in expenseCategory.expenses"
                        :key="expense.id"
                    >
                        <CardHeader>
                            <CardTitle>{{ expense.label }}</CardTitle>
                            <CardDescription>
                                {{ expense.property.label }}
                                <span v-if="expense.accommodation">
                                    - {{ expense.accommodation.label }}
                                </span>
                            </CardDescription>
                        </CardHeader>
                        <CardContent class="space-y-2">
                            <div>
                                <p class="text-sm font-medium">Price</p>
                                <p class="text-sm font-semibold">
                                    {{ formatCurrency(expense.price) }}
                                </p>
                            </div>
                            <Link :href="`/expenses/${expense.id}`">
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
