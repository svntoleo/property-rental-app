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
import ExpenseForm from '@/components/ExpenseForm.vue';
import ExpensesTable from '@/components/ExpensesTable.vue';
import TablePagination from '@/components/TablePagination.vue';

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
    is_current_month?: boolean;
    description: string | null;
    property: {
        id: number;
        label: string;
    };
    accommodation: {
        id: number;
        label: string;
    } | null;
    category: {
        id: number;
        label: string;
    } | null;
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
    expenses: {
        data: Expense[];
        meta: PaginationMeta;
    };
    properties: Property[];
    accommodations: Accommodation[];
    expenseCategories: ExpenseCategory[];
    search: string;
    sort_by?: string;
    sort_dir?: 'asc' | 'desc';
}

const props = defineProps<Props>();

const { breadcrumbs } = useBreadcrumbs();

const tableState = useTableState({
    resourceName: '',
    defaults: { sortBy: '', sortDir: 'desc' },
    currentUrl: '/expenses',
    initialSearch: props.search || '',
    initialSortBy: props.sort_by || '',
    initialSortDir: props.sort_dir || 'desc',
});

const deleteExpense = (id: number) => {
    if (confirm('Are you sure you want to delete this expense?')) {
        router.delete(`/expenses/${id}`);
    }
};

// Modal state
const { isOpen, mode, entity, open: openModal, close: closeModal, onSuccess } =
    useResourceModal<Expense>();

// Transform expense for form (accommodation/category: null -> undefined)
const expenseForModal = computed(() => {
    if (!entity.value) return undefined;
    return {
        ...entity.value,
        accommodation: entity.value.accommodation ?? undefined,
        category: entity.value.category ?? undefined,
    };
});

</script>

<template>
    <Head title="Expenses" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-bold">Expenses</h1>
                <Button @click="openModal('create')">Create Expense</Button>
            </div>

            <div class="flex items-center gap-2">
                <Input
                    v-model="tableState.searchQuery.value"
                    placeholder="Search expenses..."
                    class="max-w-sm"
                />
            </div>

            <div class="rounded-md border">
                <ExpensesTable
                    :data="expenses.data"
                    :sort-by="tableState.sortBy.value"
                    :sort-dir="tableState.sortDir.value"
                    show-property
                    show-accommodation
                    @sort="tableState.toggleSort"
                    @edit="openModal('edit', $event)"
                    @delete="deleteExpense"
                />
            </div>

            <TablePagination
                v-if="expenses.meta.last_page > 1"
                :links="expenses.meta.links"
                :last-page="expenses.meta.last_page"
            />
        </div>

        <!-- Unified Modal for Create/Edit -->
        <ResourceDialog :open="isOpen" :title="mode === 'create' ? 'Create Expense' : 'Edit Expense'" @close="closeModal">
            <ExpenseForm
                v-if="isOpen"
                :expense="expenseForModal"
                :properties="properties"
                :accommodations="accommodations"
                :expense-categories="expenseCategories"
                :is-edit="mode === 'edit'"
                @success="onSuccess"
            />
        </ResourceDialog>
    </AppLayout>
</template>
