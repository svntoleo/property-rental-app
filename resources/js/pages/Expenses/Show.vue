<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { formatDate, formatCurrency } from '@/lib/format';
import { Head, Link, router } from '@inertiajs/vue3';
import ResourceDialog from '@/components/ResourceDialog.vue';
import ExpenseForm from '@/components/ExpenseForm.vue';
import { useResourceModal } from '@/composables/useResourceModal';
import { useBreadcrumbs } from '@/composables/useBreadcrumbs';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
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
    property_id: number;
}

interface ExpenseCategory {
    id: number;
    label: string;
}

interface Expense {
    id: number;
    label: string;
    price: number;
    date: string;
    description: string | null;
    property: Property;
    accommodation: Accommodation | null;
    category: ExpenseCategory | null;
}

interface Props {
    expense: Expense;
    properties: Property[];
    accommodations: Accommodation[];
    expenseCategories: ExpenseCategory[];
}

const props = defineProps<Props>();

const { breadcrumbs } = useBreadcrumbs();

const { isOpen, open, close, entity } = useResourceModal();

const handleEditSuccess = () => {
    close();
    router.visit(`/expenses/${props.expense.id}`);
};

const deleteExpense = () => {
    if (confirm('Are you sure you want to delete this expense?')) {
        router.delete(`/expenses/${props.expense.id}`);
    }
};

// using shared formatDate util
</script>

<template>
    <Head :title="expense.label" />

    <AppLayout :breadcrumbs="breadcrumbs as any">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-bold">{{ expense.label }}</h1>
                <div class="flex gap-2">
                    <Button variant="outline" @click="open('edit', expense)">Edit</Button>
                    <Button variant="destructive" @click="deleteExpense"
                        >Delete</Button
                    >
                </div>
            </div>

                        <div class="grid gap-4 md:grid-cols-2">
                                <ResourceDialog :open="isOpen" :title="'Edit Expense'" @close="close">
                                    <ExpenseForm
                                        v-if="isOpen"
                                        :expense="(entity as any) || undefined"
                                        :properties="properties"
                                        :accommodations="accommodations"
                                        :expenseCategories="expenseCategories"
                                        :isEdit="true"
                                        context="show"
                                        @success="handleEditSuccess"
                                        @cancel="close"
                                    />
                                </ResourceDialog>
                <Card>
                    <CardHeader>
                        <CardTitle>Expense Details</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-2">
                        <div>
                            <p class="text-sm font-medium">Category</p>
                            <p class="text-sm text-muted-foreground">
                                {{ expense.category?.label || '-' }}
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
                        <div>
                            <p class="text-sm font-medium">Date</p>
                            <p class="text-sm text-muted-foreground">
                                {{ formatDate(expense.date) }}
                            </p>
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
