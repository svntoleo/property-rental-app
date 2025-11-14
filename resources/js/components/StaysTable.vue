<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import DataTable, { type Column } from '@/components/DataTable.vue';
import { formatDate, formatCurrency } from '@/lib/format';

interface Stay {
    id: number;
    start_date: string;
    end_date: string;
    due_date: number | null;
    price: number;
    accommodation: {
        id: number;
        label: string;
        property?: {
            id: number;
            label: string;
        };
    };
    category: {
        id: number;
        label: string;
    };
    tenants?: any[];
}

interface Props {
    data: Stay[];
    sortBy?: string;
    sortDir?: 'asc' | 'desc';
    showProperty?: boolean;
    showActions?: boolean;
    actionsType?: 'full' | 'view-only';
}

const props = withDefaults(defineProps<Props>(), {
    sortBy: 'start_date',
    sortDir: 'desc',
    showProperty: false,
    showActions: true,
    actionsType: 'full',
});

const emit = defineEmits<{
    sort: [column: string];
    edit: [stay: Stay];
    delete: [id: number];
}>();

const columns: Column<Stay>[] = [
    ...(props.showProperty ? [{ key: 'property', label: 'Property', sortable: true }] : []),
    { key: 'accommodation', label: 'Accommodation', sortable: props.showProperty },
    { key: 'category', label: 'Category', sortable: true },
    { key: 'start_date', label: props.showProperty ? 'Period' : 'Start Date', sortable: true },
    ...(props.showProperty ? [] : [{ key: 'end_date', label: 'End Date', sortable: true }]),
    { key: 'due_date', label: 'Due Day', sortable: true },
    { key: 'price', label: 'Price', sortable: true },
    { key: 'tenants', label: 'Tenants', sortable: false },
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
            empty-message="No stays found"
            @sort="emit('sort', $event)"
        >
            <template #cell-property="{ item }">
                {{ item.accommodation?.property?.label || 'Unknown' }}
            </template>

            <template #cell-accommodation="{ item }">
                <span :class="{ 'font-medium': !showProperty }">
                    {{ item.accommodation?.label || 'Unknown' }}
                </span>
            </template>

            <template #cell-category="{ item }">
                <span :class="{ 'font-medium': showProperty }">
                    {{ item.category?.label || 'Uncategorized' }}
                </span>
            </template>

            <template #cell-start_date="{ item }">
                <span v-if="showProperty">
                    {{ formatDate(item.start_date) }} - {{ formatDate(item.end_date) }}
                </span>
                <span v-else>
                    {{ formatDate(item.start_date) }}
                </span>
            </template>

            <template #cell-end_date="{ item }">
                {{ formatDate(item.end_date) }}
            </template>

            <template #cell-due_date="{ item }">
                {{ item.due_date ? `Day ${item.due_date}` : '-' }}
            </template>

            <template #cell-price="{ item }">
                {{ formatCurrency(item.price) }}
            </template>

            <template #cell-tenants="{ item }">
                {{ item.tenants?.length || 0 }}
            </template>

            <template #cell-actions="{ item }">
                <div v-if="actionsType === 'full'" class="flex justify-end gap-2">
                    <Link :href="`/stays/${item.id}`">
                        <Button variant="outline" size="sm">View</Button>
                    </Link>
                    <Button variant="outline" size="sm" @click="emit('edit', item)">Edit</Button>
                    <Button variant="destructive" size="sm" @click="emit('delete', item.id)">Delete</Button>
                </div>
                <div v-else-if="actionsType === 'view-only'" class="flex justify-end">
                    <Link :href="`/stays/${item.id}`">
                        <Button variant="outline" size="sm">View Details</Button>
                    </Link>
                </div>
            </template>
        </DataTable>
    </div>
</template>
