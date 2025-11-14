<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import ResourceDialog from '@/components/ResourceDialog.vue';
import ExpenseCategoryForm from '@/components/ExpenseCategoryForm.vue';
import { useResourceModal } from '@/composables/useResourceModal';
import { useBreadcrumbs } from '@/composables/useBreadcrumbs';
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

const { breadcrumbs } = useBreadcrumbs();

const { isOpen, open, close, entity } = useResourceModal();

const handleEditSuccess = () => {
    close();
    router.visit(`/expense-categories/${props.category.id}`);
};

const deleteCategory = () => {
    if (confirm('Are you sure you want to delete this category?')) {
        router.delete(`/expense-categories/${props.category.id}`);
    }
};
</script>

<template>
    <Head :title="category.label" />

    <AppLayout :breadcrumbs="breadcrumbs as any">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-bold">{{ category.label }}</h1>
                <div class="flex gap-2">
                    <Button variant="outline" @click="open('edit', category)">Edit</Button>
                    <Button variant="destructive" @click="deleteCategory"
                        >Delete</Button
                    >
                </div>
            </div>

                        <ResourceDialog :open="isOpen" :title="'Edit Expense Category'" @close="close">
                            <ExpenseCategoryForm
                                v-if="isOpen"
                                :expenseCategory="(entity as any) || undefined"
                                :isEdit="true"
                                context="show"
                                @success="handleEditSuccess"
                                @cancel="close"
                            />
                        </ResourceDialog>

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
