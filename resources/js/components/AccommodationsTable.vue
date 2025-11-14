<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import DataTable, { type Column } from '@/components/DataTable.vue';
import { formatDate } from '@/lib/format';
import {
    Tooltip,
    TooltipContent,
    TooltipProvider,
    TooltipTrigger,
} from '@/components/ui/tooltip';

interface Accommodation {
    id: number;
    label: string;
    is_occupied?: boolean;
    active_stay_tenants?: number;
    active_stay_end_date?: string;
    active_stay_category?: string;
    // Property can be absent on pages where accommodations are scoped to a single property (e.g. Property Show)
    property?: {
        id: number;
        label: string;
    };
}

interface Props {
    data: Accommodation[];
    sortBy?: string;
    sortDir?: 'asc' | 'desc';
    showProperty?: boolean;
    showActions?: boolean;
    actionsType?: 'full' | 'view-only';
}

const props = withDefaults(defineProps<Props>(), {
    sortBy: 'label',
    sortDir: 'asc',
    showProperty: false,
    showActions: true,
    actionsType: 'full',
});

const emit = defineEmits<{
    sort: [column: string];
    edit: [accommodation: Accommodation];
    delete: [id: number];
}>();

const columns: Column<Accommodation>[] = [
    { key: 'label', label: 'Accommodation', sortable: true },
    ...(props.showProperty ? [{ key: 'property', label: 'Property', sortable: true }] : []),
    { key: 'category', label: 'Category', sortable: true },
    { key: 'status', label: 'Status', sortable: true },
    { key: 'tenants', label: 'Tenants', sortable: true },
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
            empty-message="No accommodations found"
            @sort="emit('sort', $event)"
        >
            <template #cell-label="{ item }">
                <span class="font-medium">{{ item.label }}</span>
            </template>

            <template #cell-property="{ item }">
                {{ item.property?.label || '-' }}
            </template>

            <template #cell-category="{ item }">
                {{ item.is_occupied ? (item.active_stay_category || '-') : '-' }}
            </template>

            <template #cell-status="{ item }">
                <TooltipProvider v-if="item.is_occupied && item.active_stay_end_date">
                    <Tooltip>
                        <TooltipTrigger as-child>
                            <span
                                class="inline-flex items-center rounded border px-2 py-0.5 text-xs font-medium cursor-help"
                                :class="item.is_occupied
                                    ? 'bg-red-500/10 text-red-500 border-red-500/20'
                                    : 'bg-green-500/10 text-green-500 border-green-500/20'"
                            >
                                {{ item.is_occupied ? 'Occupied' : 'Free' }}
                            </span>
                        </TooltipTrigger>
                        <TooltipContent>
                            <p>Ends: {{ formatDate(item.active_stay_end_date) }}</p>
                        </TooltipContent>
                    </Tooltip>
                </TooltipProvider>
                <span
                    v-else
                    class="inline-flex items-center rounded border px-2 py-0.5 text-xs font-medium"
                    :class="item.is_occupied
                        ? 'bg-red-500/10 text-red-500 border-red-500/20'
                        : 'bg-green-500/10 text-green-500 border-green-500/20'"
                >
                    {{ item.is_occupied ? 'Occupied' : 'Free' }}
                </span>
            </template>

            <template #cell-tenants="{ item }">
                {{ item.is_occupied ? item.active_stay_tenants : '-' }}
            </template>

            <template #cell-actions="{ item }">
                <div v-if="actionsType === 'full'" class="flex justify-end gap-2">
                    <Link :href="`/accommodations/${item.id}`">
                        <Button variant="outline" size="sm">View</Button>
                    </Link>
                    <Button variant="outline" size="sm" @click="emit('edit', item)">Edit</Button>
                    <Button variant="destructive" size="sm" @click="emit('delete', item.id)">Delete</Button>
                </div>
                <div v-else-if="actionsType === 'view-only'" class="flex justify-end">
                    <Link :href="`/accommodations/${item.id}`">
                        <Button variant="outline" size="sm">View Details</Button>
                    </Link>
                </div>
            </template>
        </DataTable>
    </div>
</template>
