<script setup lang="ts">
import { formatCurrency, formatDate } from '@/lib/format';

interface Expense {
    id: number;
    property: {
        id: number;
        label: string;
    };
    accommodation?: {
        id: number;
        label: string;
    };
    category: {
        id: number;
        label: string;
    };
    label: string;
    price: number;
    date: string;
    description: string | null;
}

interface Props {
    expense: Expense;
}

defineProps<Props>();
</script>

<template>
    <div class="space-y-4">
        <div class="grid gap-4 md:grid-cols-2">
            <div>
                <p class="text-sm font-medium text-muted-foreground">Label</p>
                <p class="text-base">{{ expense.label }}</p>
            </div>
            <div>
                <p class="text-sm font-medium text-muted-foreground">
                    Category
                </p>
                <p class="text-base">{{ expense.category.label }}</p>
            </div>
        </div>

        <div class="grid gap-4 md:grid-cols-2">
            <div>
                <p class="text-sm font-medium text-muted-foreground">
                    Property
                </p>
                <p class="text-base">{{ expense.property.label }}</p>
            </div>
            <div v-if="expense.accommodation">
                <p class="text-sm font-medium text-muted-foreground">
                    Accommodation
                </p>
                <p class="text-base">{{ expense.accommodation.label }}</p>
            </div>
        </div>

        <div class="grid gap-4 md:grid-cols-2">
            <div>
                <p class="text-sm font-medium text-muted-foreground">Price</p>
                <p class="text-base">{{ formatCurrency(expense.price) }}</p>
            </div>
            <div>
                <p class="text-sm font-medium text-muted-foreground">Date</p>
                <p class="text-base">{{ formatDate(expense.date) }}</p>
            </div>
        </div>

        <div v-if="expense.description">
            <p class="text-sm font-medium text-muted-foreground">
                Description
            </p>
            <p class="text-base">{{ expense.description }}</p>
        </div>
    </div>
</template>
