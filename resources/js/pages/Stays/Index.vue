<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { useResourceModal } from '@/composables/useResourceModal';
import { useBreadcrumbs } from '@/composables/useBreadcrumbs';
import { useTableState } from '@/composables/useTableState';
import ResourceDialog from '@/components/ResourceDialog.vue';
import StayForm from '@/components/StayForm.vue';
import StaysTable from '@/components/StaysTable.vue';
import TablePagination from '@/components/TablePagination.vue';

interface Accommodation {
    id: number;
    label: string;
    property: {
        id: number;
        label: string;
    };
}

interface StayCategory {
    id: number;
    label: string;
}

interface Stay {
    id: number;
    start_date: string;
    end_date: string;
    due_date: number | null;
    price: number;
    category: StayCategory;
    accommodation: Accommodation;
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
    stays: {
        data: Stay[];
        meta: PaginationMeta;
    };
    accommodations: Accommodation[];
    stayCategories: StayCategory[];
    search: string;
    sort_by?: string;
    sort_dir?: 'asc' | 'desc';
}

const props = defineProps<Props>();

const { breadcrumbs } = useBreadcrumbs();

const tableState = useTableState({
    resourceName: '',
    defaults: { sortBy: '', sortDir: 'desc' },
    currentUrl: '/stays',
    initialSearch: props.search || '',
    initialSortBy: props.sort_by || '',
    initialSortDir: props.sort_dir || 'desc',
});

const deleteStay = (id: number) => {
    if (confirm('Are you sure you want to delete this stay?')) {
        router.delete(`/stays/${id}`);
    }
};

// Modal state
const { isOpen, mode, entity, open: openModal, close: closeModal, onSuccess } =
    useResourceModal<Stay>();

</script>

<template>
    <Head title="Stays" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-bold">Stays</h1>
                <Button @click="openModal('create')">Create Stay</Button>
            </div>

            <div class="flex items-center gap-2">
                <Input
                    v-model="tableState.searchQuery.value"
                    placeholder="Search stays..."
                    class="max-w-sm"
                />
            </div>

            <div class="rounded-md border">
                <StaysTable
                    :data="stays.data"
                    :sort-by="tableState.sortBy.value"
                    :sort-dir="tableState.sortDir.value"
                    show-property
                    @sort="tableState.toggleSort"
                    @edit="openModal('edit', $event)"
                    @delete="deleteStay"
                />
            </div>

            <TablePagination
                v-if="stays.meta.last_page > 1"
                :links="stays.meta.links"
                :last-page="stays.meta.last_page"
            />
        </div>

        <!-- Unified Modal for Create/Edit -->
        <ResourceDialog :open="isOpen" :title="mode === 'create' ? 'Create Stay' : 'Edit Stay'" @close="closeModal">
            <StayForm
                v-if="isOpen"
                :stay="entity ?? undefined"
                :accommodations="accommodations"
                :stay-categories="stayCategories"
                :is-edit="mode === 'edit'"
                @success="onSuccess"
            />
        </ResourceDialog>
    </AppLayout>
</template>
