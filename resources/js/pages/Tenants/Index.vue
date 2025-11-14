<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import TenantForm from '@/components/TenantForm.vue';
import ResourceDialog from '@/components/ResourceDialog.vue';
import { useResourceModal } from '@/composables/useResourceModal';
import { useBreadcrumbs } from '@/composables/useBreadcrumbs';
import { useTableState } from '@/composables/useTableState';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import TenantsTable from '@/components/TenantsTable.vue';
import TablePagination from '@/components/TablePagination.vue';
import { ref } from 'vue';

interface Stay {
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
    category?: { label: string };
}

interface Tenant {
    id: number;
    name: string;
    email: string;
    phone: string;
    phone_formatted: string;
    cpf_formatted: string;
    stay: Stay;
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
    tenants: {
        data: Tenant[];
        meta: PaginationMeta;
    };
    stays?: Stay[];
    search: string;
    sort_by?: string;
    sort_dir?: 'asc' | 'desc';
}

const props = defineProps<Props>();

// Composables
const { isOpen, mode, entity, open, close, onSuccess } = useResourceModal<Tenant>();
const { breadcrumbs } = useBreadcrumbs();
const stays = ref<Stay[]>(props.stays || []);

const tableState = useTableState({
    resourceName: '',
    defaults: { sortBy: '', sortDir: 'desc' },
    currentUrl: '/tenants',
    initialSearch: props.search || '',
    initialSortBy: props.sort_by || '',
    initialSortDir: props.sort_dir || 'desc',
});

function openModal(m: 'create' | 'edit', tenant: Tenant | null = null) {
    open(m, tenant);
}

function closeModal() {
    close();
}

function handleSuccess() {
    onSuccess();
    // Refresh the list after create/edit
    tableState.applyFilters();
}

const deleteTenant = (id: number) => {
    if (confirm('Are you sure you want to delete this tenant?')) {
        router.delete(`/tenants/${id}`);
    }
};
</script>

<template>
    <Head title="Tenants" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
                        <div class="flex items-center justify-between">
                <h1 class="text-2xl font-bold">Tenants</h1>
                <Button @click="openModal('create')">Create Tenant</Button>
            </div>

            <div class="flex items-center gap-2">
                <Input
                    v-model="tableState.searchQuery.value"
                    placeholder="Search tenants..."
                    class="max-w-sm"
                />
            </div>

            <div class="rounded-md border">
                <TenantsTable
                    :data="tenants.data"
                    :sort-by="tableState.sortBy.value"
                    :sort-dir="tableState.sortDir.value"
                    show-stay-period
                    @sort="tableState.toggleSort"
                    @edit="openModal('edit', $event)"
                    @delete="deleteTenant"
                />
            </div>

            <TablePagination
                v-if="tenants.meta.last_page > 1"
                :links="tenants.meta.links"
                :last-page="tenants.meta.last_page"
            />
        </div>

        <!-- Unified Modal for Create/Edit Tenant -->
        <ResourceDialog
            :open="isOpen"
            :title="mode === 'create' ? 'Create Tenant' : 'Edit Tenant'"
            @close="closeModal"
        >
            <TenantForm
                v-if="isOpen"
                :stays="stays"
                :tenant="(entity as any) || undefined"
                :isEdit="mode === 'edit'"
                @success="handleSuccess"
                @cancel="closeModal"
            />
        </ResourceDialog>
    </AppLayout>
</template>
