<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import DataTable, { type Column } from '@/components/DataTable.vue';
import { formatDate } from '@/lib/format';

interface Tenant {
    id: number;
    name: string;
    email: string;
    // Raw cpf/phone may not always be provided on list endpoints; mark optional
    cpf?: string;
    cpf_formatted: string;
    phone?: string;
    phone_formatted: string;
    stay: {
        id: number;
        start_date: string;
        end_date: string;
        accommodation: {
            id: number;
            label: string;
            property: {
                id: number;
                label: string;
            };
        };
    };
}

interface Props {
    data: Tenant[];
    sortBy?: string;
    sortDir?: 'asc' | 'desc';
    showStayPeriod?: boolean;
    showActions?: boolean;
    actionsType?: 'full' | 'view-only';
}

const props = withDefaults(defineProps<Props>(), {
    sortBy: 'name',
    sortDir: 'asc',
    showStayPeriod: true,
    showActions: true,
    actionsType: 'full',
});

const emit = defineEmits<{
    sort: [column: string];
    edit: [tenant: Tenant];
    delete: [id: number];
}>();

const columns: Column<Tenant>[] = [
    { key: 'name', label: 'Name', sortable: true },
    { key: 'email', label: 'Email', sortable: true },
    { key: 'cpf', label: 'CPF', sortable: true },
    { key: 'phone', label: 'Phone', sortable: true },
    { key: 'property', label: 'Property', sortable: true },
    { key: 'accommodation', label: 'Accommodation', sortable: true },
    ...(props.showStayPeriod ? [{ key: 'stay_period', label: 'Stay Period', sortable: true }] : []),
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
            empty-message="No tenants found"
            @sort="emit('sort', $event)"
        >
            <template #cell-name="{ item }">
                <span class="font-medium">{{ item.name }}</span>
            </template>

            <template #cell-email="{ item }">
                {{ item.email }}
            </template>

            <template #cell-cpf="{ item }">
                {{ item.cpf_formatted }}
            </template>

            <template #cell-phone="{ item }">
                {{ item.phone_formatted }}
            </template>

            <template #cell-property="{ item }">
                {{ item.stay?.accommodation?.property?.label || '-' }}
            </template>

            <template #cell-accommodation="{ item }">
                {{ item.stay?.accommodation?.label || '-' }}
            </template>

            <template #cell-stay_period="{ item }">
                <span v-if="item.stay">
                    {{ formatDate(item.stay.start_date) }} - {{ formatDate(item.stay.end_date) }}
                </span>
                <span v-else>-</span>
            </template>

            <template #cell-actions="{ item }">
                <div v-if="actionsType === 'full'" class="flex justify-end gap-2">
                    <Link :href="`/tenants/${item.id}`">
                        <Button variant="outline" size="sm">View</Button>
                    </Link>
                    <Button variant="outline" size="sm" @click="emit('edit', item)">Edit</Button>
                    <Button variant="destructive" size="sm" @click="emit('delete', item.id)">Delete</Button>
                </div>
                <div v-else-if="actionsType === 'view-only'" class="flex justify-end">
                    <Link :href="`/tenants/${item.id}`">
                        <Button variant="outline" size="sm">View Details</Button>
                    </Link>
                </div>
            </template>
        </DataTable>
    </div>
</template>
