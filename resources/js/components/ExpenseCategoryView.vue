<script setup lang="ts">
interface ExpenseCategory {
    id: number;
    label: string;
}

interface Props {
    expenseCategory: ExpenseCategory;
}

defineProps<Props>();
</script>

<template>
    <div class="space-y-4">
        <div>
            <p class="text-sm font-medium text-muted-foreground">Label</p>
            <p class="text-base">{{ expenseCategory.label }}</p>
        </div>
    </div>
</template>
