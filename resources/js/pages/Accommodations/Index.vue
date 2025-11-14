<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { computed } from 'vue';
import { useResourceModal } from '@/composables/useResourceModal';
import { useBreadcrumbs } from '@/composables/useBreadcrumbs';
import { useTableState } from '@/composables/useTableState';
import ResourceDialog from '@/components/ResourceDialog.vue';
import AccommodationForm from '@/components/AccommodationForm.vue';
import AccommodationsTable from '@/components/AccommodationsTable.vue';
import TablePagination from '@/components/TablePagination.vue';

interface Property {
    id: number;
    label: string;
    address?: string;
}

interface Accommodation {
    id: number;
    label: string;
    property: Property;
    is_occupied?: boolean;
    active_stay_tenants?: number;
    active_stay_end_date?: string;
    active_stay_category?: string;
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface PaginationMeta {
    current_page: number;
    from: number;
    last_page: number;
    links: PaginationLink[];
    path: string;
    per_page: number;
    to: number;
    total: number;
}

interface Props {
    accommodations: {
        data: Accommodation[];
        meta: PaginationMeta;
    };
    properties: Property[];
    search: string;
    sort_by?: string;
    sort_dir?: 'asc' | 'desc';
}

const props = defineProps<Props>();

const { breadcrumbs } = useBreadcrumbs();

// Table state management
const tableState = useTableState({
    resourceName: '',  // Empty because we use non-prefixed params
    defaultSortBy: 'label',
    defaultSortDir: 'asc',
    currentUrl: '/accommodations',
    initialSearch: props.search,
    initialSortBy: props.sort_by,
    initialSortDir: props.sort_dir,
});

const deleteAccommodation = (id: number) => {
    if (confirm('Are you sure you want to delete this accommodation?')) {
        router.delete(`/accommodations/${id}`);
    }
};

// Modal state
const { isOpen, mode, entity, open: openModal, close: closeModal, onSuccess } =
    useResourceModal<Accommodation>();

// Transform accommodation for form (needs property_id instead of property object)
const accommodationForForm = computed(() => {
    if (!entity.value) return undefined;
    return {
        id: entity.value.id,
        label: entity.value.label,
        property_id: entity.value.property.id,
    };
});

</script>

<template>
    <Head title="Accommodations" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-bold">Accommodations</h1>
                <Button @click="openModal('create')">Create Accommodation</Button>
            </div>

            <div class="flex items-center gap-2">
                <Input
                    v-model="tableState.searchQuery.value"
                    placeholder="Search accommodations..."
                    class="max-w-sm"
                />
            </div>

            <AccommodationsTable
                :data="accommodations.data"
                :sort-by="tableState.sortBy.value"
                :sort-dir="tableState.sortDir.value"
                show-property
                @sort="tableState.toggleSort"
                @edit="openModal('edit', $event)"
                @delete="deleteAccommodation"
            />

            <TablePagination
                :links="accommodations.meta.links"
                :last-page="accommodations.meta.last_page"
            />
        </div>

        <!-- Unified Modal for Create/Edit -->
        <ResourceDialog :open="isOpen" :title="mode === 'create' ? 'Create Accommodation' : 'Edit Accommodation'" @close="closeModal">
            <AccommodationForm
                v-if="isOpen"
                :accommodation="accommodationForForm"
                :properties="properties"
                :is-edit="mode === 'edit'"
                @success="onSuccess"
            />
        </ResourceDialog>
    </AppLayout>
</template>
