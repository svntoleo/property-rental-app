<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { formatCurrency } from '@/lib/format';
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
    category: ExpenseCategory;
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
        title: props.category.label,
        href: `/expense-categories/${props.category.id}`,
    },
];

const deleteCategory = () => {
    if (confirm('Are you sure you want to delete this category?')) {
        router.delete(`/expense-categories/${props.category.id}`);
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
                    <Link
                        :href="`/expense-categories/${category.id}/edit`"
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
                            {{ category.expenses?.length || 0 }}
                        </p>
                    </div>
                </CardContent>
            </Card>

            <div v-if="category.expenses?.length" class="space-y-4">
                <h2 class="text-xl font-semibold">Expenses</h2>
                <div class="rounded-md border">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Label</TableHead>
                                <TableHead>Property</TableHead>
                                <TableHead>Accommodation</TableHead>
                                <TableHead>Price</TableHead>
                                <TableHead class="text-right">Actions</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow
                                v-for="expense in category.expenses"
                                :key="expense.id"
                            >
                                <TableCell class="font-medium">{{
                                    expense.label
                                }}</TableCell>
                                <TableCell>{{
                                    expense.property.label
                                }}</TableCell>
                                <TableCell>{{
                                    expense.accommodation?.label || 'N/A'
                                }}</TableCell>
                                <TableCell>{{
                                    formatCurrency(expense.price)
                                }}</TableCell>
                                <TableCell class="text-right">
                                    <Link :href="`/expenses/${expense.id}`">
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
