<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import DataTable, { type Column } from '@/components/DataTable.vue';
import { formatDate, formatCurrency } from '@/lib/format';

interface Expense {
    id: number;
    label: string;
    price: number;
    date: string;
    is_current_month?: boolean;
    description: string | null;
    category?: {
        id: number;
        label: string;
    } | null;
    // Property is normally always present for expenses; keep optional for flexibility
    property?: {
        id: number;
        label: string;
    };
    accommodation?: {
        id: number;
        label: string;
    } | null;
}

interface Props {
    data: Expense[];
    sortBy?: string;
    sortDir?: 'asc' | 'desc';
    showProperty?: boolean;
    showAccommodation?: boolean;
    showActions?: boolean;
    actionsType?: 'full' | 'view-only';
}

const props = withDefaults(defineProps<Props>(), {
    sortBy: 'date',
    sortDir: 'desc',
    showProperty: false,
    showAccommodation: true,
    showActions: true,
    actionsType: 'full',
});

const emit = defineEmits<{
    sort: [column: string];
    edit: [expense: Expense];
    delete: [id: number];
}>();

const columns: Column<Expense>[] = [
    { key: 'label', label: 'Label', sortable: true },
    { key: 'category', label: 'Category', sortable: false },
    ...(props.showProperty ? [{ key: 'property', label: 'Property', sortable: true }] : []),
    ...(props.showAccommodation ? [{ key: 'accommodation', label: 'Accommodation', sortable: true }] : []),
    { key: 'price', label: 'Price', sortable: true },
    { key: 'date', label: 'Date', sortable: true },
    { key: 'current_month', label: 'This Month', sortable: true },
    { key: 'description', label: 'Description', sortable: true },
    ...(props.showActions ? [{ key: 'actions', label: 'Actions', headerClass: 'text-right', class: 'text-right' }] : []),
];
</script>

<template>
    <div class="rounded-md border overflow-x-auto">
        <DataTable
            :data="data"
            :columns="columns"
            :sort-by="sortBy"
            :sort-dir="sortDir"
            empty-message="No expenses found"
            @sort="emit('sort', $event)"
        >
            <template #cell-label="{ item }">
                <span class="font-medium">{{ item.label }}</span>
            </template>

            <template #cell-category="{ item }">
                {{ item.category?.label || '-' }}
            </template>

            <template #cell-property="{ item }">
                {{ item.property?.label || '-' }}
            </template>

            <template #cell-accommodation="{ item }">
                {{ item.accommodation?.label || '-' }}
            </template>

            <template #cell-price="{ item }">
                {{ formatCurrency(item.price) }}
            </template>

            <template #cell-date="{ item }">
                {{ formatDate(item.date) }}
            </template>

            <template #cell-current_month="{ item }">
                <span v-if="item.is_current_month"
                    class="inline-flex items-center rounded border px-2 py-0.5 text-xs font-medium"
                    :class="'bg-blue-500/10 text-blue-500 border-blue-500/20'"
                >This month</span>
                <span v-else>-</span>
            </template>

            <template #cell-description="{ item }">
                {{ item.description || '-' }}
            </template>

            <template #cell-actions="{ item }">
                <div v-if="actionsType === 'full'" class="flex justify-end gap-2">
                    <Link :href="`/expenses/${item.id}`">
                        <Button variant="outline" size="sm">View</Button>
                    </Link>
                    <Button variant="outline" size="sm" @click="emit('edit', item)">Edit</Button>
                    <Button variant="destructive" size="sm" @click="emit('delete', item.id)">Delete</Button>
                </div>
                <div v-else-if="actionsType === 'view-only'" class="flex justify-end">
                    <Link :href="`/expenses/${item.id}`">
                        <Button variant="outline" size="sm">View Details</Button>
                    </Link>
                </div>
            </template>
        </DataTable>
    </div>
</template>
