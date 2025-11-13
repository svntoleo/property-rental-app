<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { formatDate, formatCurrency } from '@/lib/format';
import { Head, Link, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { ref, watch, computed } from 'vue';
import { useResourceModal } from '@/composables/useResourceModal';
import { useBreadcrumbs } from '@/composables/useBreadcrumbs';
import ResourceDialog from '@/components/ResourceDialog.vue';
import ExpenseForm from '@/components/ExpenseForm.vue';

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
    };
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

const searchQuery = ref(props.search || '');
const sortBy = ref<string>(props.sort_by || '');
const sortDir = ref<'asc' | 'desc'>(props.sort_dir || 'desc');
const applyParams = (extra: Record<string, any> = {}) => {
    const params: Record<string, any> = {};
    if (searchQuery.value) params.search = searchQuery.value;
    if (sortBy.value) {
        params.sort_by = sortBy.value;
        params.sort_dir = sortDir.value;
    }
    Object.assign(params, extra);
    router.get('/expenses', params, { preserveState: true, replace: true });
};

const toggleSort = (column: string) => {
    if (sortBy.value === column) {
        sortDir.value = sortDir.value === 'asc' ? 'desc' : 'asc';
    } else {
        sortBy.value = column;
        sortDir.value = 'asc';
    }
    applyParams();
};
let debounceHandle: ReturnType<typeof setTimeout> | undefined;

// Debounced live search
watch(searchQuery, (q) => {
    if (debounceHandle) clearTimeout(debounceHandle);
    debounceHandle = setTimeout(() => {
        applyParams();
    }, 350);
});

const deleteExpense = (id: number) => {
    if (confirm('Are you sure you want to delete this expense?')) {
        router.delete(`/expenses/${id}`);
    }
};

// Modal state
const { isOpen, mode, entity, open: openModal, close: closeModal, onSuccess } =
    useResourceModal<Expense>();

// Transform expense for form (accommodation: null -> undefined)
const expenseForModal = computed(() => {
    if (!entity.value) return undefined;
    return {
        ...entity.value,
        accommodation: entity.value.accommodation ?? undefined,
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
                    v-model="searchQuery"
                    placeholder="Search expenses..."
                    class="max-w-sm"
                />
            </div>

            <div class="rounded-md border">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>
                                <button class="flex items-center gap-1" @click="toggleSort('label')">
                                    Label
                                    <span v-if="sortBy === 'label'">{{ sortDir === 'asc' ? '▲' : '▼' }}</span>
                                </button>
                            </TableHead>
                            <TableHead>
                                <button class="flex items-center gap-1" @click="toggleSort('category')">
                                    Category
                                    <span v-if="sortBy === 'category'">{{ sortDir === 'asc' ? '▲' : '▼' }}</span>
                                </button>
                            </TableHead>
                            <TableHead>
                                <button class="flex items-center gap-1" @click="toggleSort('property')">
                                    Property
                                    <span v-if="sortBy === 'property'">{{ sortDir === 'asc' ? '▲' : '▼' }}</span>
                                </button>
                            </TableHead>
                            <TableHead>
                                <button class="flex items-center gap-1" @click="toggleSort('accommodation')">
                                    Accommodation
                                    <span v-if="sortBy === 'accommodation'">{{ sortDir === 'asc' ? '▲' : '▼' }}</span>
                                </button>
                            </TableHead>
                            <TableHead>
                                <button class="flex items-center gap-1" @click="toggleSort('price')">
                                    Price
                                    <span v-if="sortBy === 'price'">{{ sortDir === 'asc' ? '▲' : '▼' }}</span>
                                </button>
                            </TableHead>
                            <TableHead>
                                <button class="flex items-center gap-1" @click="toggleSort('date')">
                                    Date
                                    <span v-if="sortBy === 'date'">{{ sortDir === 'asc' ? '▲' : '▼' }}</span>
                                </button>
                            </TableHead>
                            <TableHead>
                                <button class="flex items-center gap-1" @click="toggleSort('description')">
                                    Description
                                    <span v-if="sortBy === 'description'">{{ sortDir === 'asc' ? '▲' : '▼' }}</span>
                                </button>
                            </TableHead>
                            <TableHead class="text-right">Actions</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow
                            v-for="expense in expenses.data"
                            :key="expense.id"
                        >
                            <TableCell class="font-medium">{{
                                expense.label
                            }}</TableCell>
                            <TableCell>{{ expense.category.label }}</TableCell>
                            <TableCell>{{ expense.property.label }}</TableCell>
                            <TableCell>{{
                                expense.accommodation?.label || '-'
                            }}</TableCell>
                            <TableCell>{{ formatCurrency(expense.price) }}</TableCell>
                            <TableCell>{{ formatDate(expense.date) }}</TableCell>
                            <TableCell>{{
                                expense.description || '-'
                            }}</TableCell>
                            <TableCell class="text-right">
                                <div class="flex justify-end gap-2">
                                    <Link :href="`/expenses/${expense.id}`">
                                        <Button
                                            variant="outline"
                                            size="sm"
                                        >
                                            View
                                        </Button>
                                    </Link>
                                    <Button
                                        variant="outline"
                                        size="sm"
                                        @click="openModal('edit', expense)"
                                    >
                                        Edit
                                    </Button>
                                    <Button
                                        variant="destructive"
                                        size="sm"
                                        @click="deleteExpense(expense.id)"
                                        >Delete</Button
                                    >
                                </div>
                            </TableCell>
                        </TableRow>
                        <TableRow v-if="expenses.data.length === 0">
                            <TableCell colspan="8" class="text-center">
                                No expenses found
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>

            <!-- Pagination -->
            <div
                v-if="expenses.meta.last_page > 1"
                class="mt-4 flex items-center justify-center gap-2"
            >
                <Button
                    v-for="(link, index) in expenses.meta.links"
                    :key="index"
                    :variant="link.active ? 'default' : 'outline'"
                    size="sm"
                    :disabled="!link.url"
                    @click="
                        link.url &&
                            router.visit(link.url, {
                                data: {
                                    ...(searchQuery ? { search: searchQuery } : {}),
                                    ...(sortBy ? { sort_by: sortBy } : {}),
                                    ...(sortBy ? { sort_dir: sortDir } : {}),
                                },
                            })
                    "
                    v-html="link.label"
                />
            </div>
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
