<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { useBreadcrumbs } from '@/composables/useBreadcrumbs';
import { useTableState } from '@/composables/useTableState';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { formatDate, formatCurrency } from '@/lib/format';
import ResourceDialog from '@/components/ResourceDialog.vue';
import PropertyForm from '@/components/PropertyForm.vue';
import { useResourceModal } from '@/composables/useResourceModal';
import AccommodationsTable from '@/components/AccommodationsTable.vue';
import ExpensesTable from '@/components/ExpensesTable.vue';
import StaysTable from '@/components/StaysTable.vue';
import TenantsTable from '@/components/TenantsTable.vue';
import TablePagination from '@/components/TablePagination.vue';
import {
    Card,
    CardContent,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';

interface Property {
    id: number;
    label: string;
    address: string;
    description: string | null;
}

interface Accommodation {
    id: number;
    label: string;
    is_occupied?: boolean;
    active_stay_tenants?: number;
    active_stay_end_date?: string;
    active_stay_category?: string;
}

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
    };
    accommodation?: {
        id: number;
        label: string;
    };
}

interface Tenant {
    id: number;
    name: string;
    email: string;
    cpf: string;
    cpf_formatted: string;
    phone: string;
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

interface Stay {
    id: number;
    start_date: string;
    end_date: string;
    due_date: number;
    price: number;
    accommodation: {
        id: number;
        label: string;
    };
    category: {
        id: number;
        label: string;
    };
    tenants: Tenant[];
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
    property: Property;
    expenses: {
        data: Expense[];
        meta: PaginationMeta;
    };
    expense_search: string;
    expense_sort_by?: string;
    expense_sort_dir?: 'asc' | 'desc';
    accommodations: {
        data: Accommodation[];
        meta: PaginationMeta;
    };
    accommodation_search: string;
    accommodation_sort_by?: string;
    accommodation_sort_dir?: 'asc' | 'desc';
    stays: {
        data: Stay[];
        meta: PaginationMeta;
    };
    stay_search: string;
    stay_sort_by?: string;
    stay_sort_dir?: 'asc' | 'desc';
    tenants: {
        data: Tenant[];
        meta: PaginationMeta;
    };
    tenant_search: string;
    tenant_sort_by?: string;
    tenant_sort_dir?: 'asc' | 'desc';
    expected_month_income: number;
    expected_month_expenses: number;
    expected_month_profit: number;
    free_accommodations: number;
}
const props = defineProps<Props>();

// Table state management using composable
const expenseTable = useTableState({
    resourceName: 'expense',
    defaultSortBy: 'date',
    defaultSortDir: 'desc',
    currentUrl: `/properties/${props.property.id}`,
    initialSearch: props.expense_search,
    initialSortBy: props.expense_sort_by,
    initialSortDir: props.expense_sort_dir,
    preserveParams: {
        accommodation_search: props.accommodation_search,
        accommodation_sort_by: props.accommodation_sort_by,
        accommodation_sort_dir: props.accommodation_sort_dir,
        stay_search: props.stay_search,
        stay_sort_by: props.stay_sort_by,
        stay_sort_dir: props.stay_sort_dir,
        tenant_search: props.tenant_search,
        tenant_sort_by: props.tenant_sort_by,
        tenant_sort_dir: props.tenant_sort_dir,
    },
});

const accommodationTable = useTableState({
    resourceName: 'accommodation',
    defaultSortBy: 'label',
    defaultSortDir: 'asc',
    currentUrl: `/properties/${props.property.id}`,
    initialSearch: props.accommodation_search,
    initialSortBy: props.accommodation_sort_by,
    initialSortDir: props.accommodation_sort_dir,
    preserveParams: {
        expense_search: props.expense_search,
        expense_sort_by: props.expense_sort_by,
        expense_sort_dir: props.expense_sort_dir,
        stay_search: props.stay_search,
        stay_sort_by: props.stay_sort_by,
        stay_sort_dir: props.stay_sort_dir,
        tenant_search: props.tenant_search,
        tenant_sort_by: props.tenant_sort_by,
        tenant_sort_dir: props.tenant_sort_dir,
    },
});

const stayTable = useTableState({
    resourceName: 'stay',
    defaultSortBy: 'start_date',
    defaultSortDir: 'desc',
    currentUrl: `/properties/${props.property.id}`,
    initialSearch: props.stay_search,
    initialSortBy: props.stay_sort_by,
    initialSortDir: props.stay_sort_dir,
    preserveParams: {
        expense_search: props.expense_search,
        expense_sort_by: props.expense_sort_by,
        expense_sort_dir: props.expense_sort_dir,
        accommodation_search: props.accommodation_search,
        accommodation_sort_by: props.accommodation_sort_by,
        accommodation_sort_dir: props.accommodation_sort_dir,
        tenant_search: props.tenant_search,
        tenant_sort_by: props.tenant_sort_by,
        tenant_sort_dir: props.tenant_sort_dir,
    },
});

const tenantTable = useTableState({
    resourceName: 'tenant',
    defaultSortBy: 'name',
    defaultSortDir: 'asc',
    currentUrl: `/properties/${props.property.id}`,
    initialSearch: props.tenant_search,
    initialSortBy: props.tenant_sort_by,
    initialSortDir: props.tenant_sort_dir,
    preserveParams: {
        expense_search: props.expense_search,
        expense_sort_by: props.expense_sort_by,
        expense_sort_dir: props.expense_sort_dir,
        accommodation_search: props.accommodation_search,
        accommodation_sort_by: props.accommodation_sort_by,
        accommodation_sort_dir: props.accommodation_sort_dir,
        stay_search: props.stay_search,
        stay_sort_by: props.stay_sort_by,
        stay_sort_dir: props.stay_sort_dir,
    },
});

const { breadcrumbs } = useBreadcrumbs();

// Modal state for in-place edit
const { isOpen, entity, open, close, onSuccess } = useResourceModal<Property>();

function openEditModal() {
    open('edit', props.property as unknown as Property);
}

function handleEditSuccess() {
    onSuccess();
    // reload current show page to reflect updated data
    router.get(`/properties/${props.property.id}`, {}, { replace: true });
}

const deleteProperty = () => {
    if (confirm('Are you sure you want to delete this property?')) {
        router.delete(`/properties/${props.property.id}`);
    }
};

// using shared formatDate util
</script>

<template>
    <Head :title="property.label" />

    <AppLayout :breadcrumbs="breadcrumbs as any">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-bold">{{ property.label }}</h1>
                <div class="flex gap-2">
                    <Button variant="outline" @click="openEditModal">Edit</Button>
                    <Button variant="destructive" @click="deleteProperty">Delete</Button>
                </div>
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                                <ResourceDialog :open="isOpen" :title="'Edit Property'" @close="close">
                                    <PropertyForm
                                        v-if="isOpen"
                                        :property="(entity as any) || undefined"
                                        :isEdit="true"
                                        context="show"
                                        @success="handleEditSuccess"
                                        @cancel="close"
                                    />
                                </ResourceDialog>
                <Card>
                    <CardHeader>
                        <CardTitle>Property Details</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-2">
                        <div>
                            <p class="text-sm font-medium">Address</p>
                            <p class="text-sm text-muted-foreground">
                                {{ property.address }}
                            </p>
                        </div>
                        <div v-if="property.description">
                            <p class="text-sm font-medium">Description</p>
                            <p class="text-sm text-muted-foreground">
                                {{ property.description }}
                            </p>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>Statistics</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <div>
                                <p class="text-sm font-medium">Accommodations</p>
                                <p class="text-2xl font-bold">
                                    {{ accommodations.meta.total }}
                                </p>
                            </div>
                            <div>
                                <p class="text-sm font-medium">Vacancies</p>
                                <p class="text-2xl font-bold">
                                    {{ props.free_accommodations }}
                                </p>
                            </div>
                            <div>
                                <p class="text-sm font-medium">Active Stays</p>
                                <p class="text-2xl font-bold">
                                    {{ stays.meta.total }}
                                </p>
                            </div>
                            <div>
                                <p class="text-sm font-medium">Tenants</p>
                                <p class="text-2xl font-bold">
                                    {{ tenants.meta.total }}
                                </p>
                            </div>
                        </div>
                        <div class="pt-2 border-t">
                            <p class="text-xs text-muted-foreground mb-3">
                                {{ new Date().toLocaleString('en-US', { month: 'long', year: 'numeric' }) }}
                            </p>
                            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                                <div>
                                    <p class="text-sm font-medium">Expected Income</p>
                                    <p class="text-2xl font-bold">
                                        {{ formatCurrency(props.expected_month_income) }}
                                    </p>
                                </div>
                                <div>
                                    <p class="text-sm font-medium">Expected Expenses</p>
                                    <p class="text-2xl font-bold">
                                        {{ formatCurrency(props.expected_month_expenses) }}
                                    </p>
                                </div>
                                <div>
                                    <p class="text-sm font-medium">Expected Profit</p>
                                    <p class="text-2xl font-bold">
                                        {{ formatCurrency(props.expected_month_profit) }}
                                    </p>
                                </div>
                            </div>
                        </div>
                        
                    </CardContent>
                </Card>
            </div>

            <div v-if="accommodations.data.length > 0" class="grid gap-4">
                <h2 class="text-xl font-semibold">Accommodations</h2>
                
                <div class="flex items-center gap-2">
                    <Input
                        v-model="accommodationTable.searchQuery.value"
                        placeholder="Search accommodations..."
                        class="max-w-sm"
                    />
                </div>

                <AccommodationsTable
                    :data="accommodations.data"
                    :sort-by="accommodationTable.sortBy.value"
                    :sort-dir="accommodationTable.sortDir.value"
                    actions-type="view-only"
                    @sort="accommodationTable.toggleSort"
                />

                <TablePagination
                    :links="accommodations.meta.links"
                    :last-page="accommodations.meta.last_page"
                />
            </div>

            <div v-if="stays.data.length > 0" class="grid gap-4">
                <h2 class="text-xl font-semibold">Active Stays</h2>
                
                <div class="flex items-center gap-2">
                    <Input
                        v-model="stayTable.searchQuery.value"
                        placeholder="Search stays..."
                        class="max-w-sm"
                    />
                </div>

                <StaysTable
                    :data="stays.data"
                    :sort-by="stayTable.sortBy.value"
                    :sort-dir="stayTable.sortDir.value"
                    actions-type="view-only"
                    @sort="stayTable.toggleSort"
                />

                <TablePagination
                    :links="stays.meta.links"
                    :last-page="stays.meta.last_page"
                />
            </div>

            <div v-if="tenants.data.length > 0" class="grid gap-4">
                <h2 class="text-xl font-semibold">Tenants</h2>
                
                <div class="flex items-center gap-2">
                    <Input
                        v-model="tenantTable.searchQuery.value"
                        placeholder="Search tenants..."
                        class="max-w-sm"
                    />
                </div>

                <TenantsTable
                    :data="tenants.data"
                    :sort-by="tenantTable.sortBy.value"
                    :sort-dir="tenantTable.sortDir.value"
                    actions-type="view-only"
                    @sort="tenantTable.toggleSort"
                />

                <TablePagination
                    :links="tenants.meta.links"
                    :last-page="tenants.meta.last_page"
                />
            </div>

            <div v-if="expenses.data.length > 0" class="grid gap-4">
                <h2 class="text-xl font-semibold">Expenses</h2>
                
                <div class="flex items-center gap-2">
                    <Input
                        v-model="expenseTable.searchQuery.value"
                        placeholder="Search expenses..."
                        class="max-w-sm"
                    />
                </div>
                <ExpensesTable
                    :data="expenses.data"
                    :sort-by="expenseTable.sortBy.value"
                    :sort-dir="expenseTable.sortDir.value"
                    actions-type="view-only"
                    @sort="expenseTable.toggleSort"
                />

                <TablePagination
                    :links="expenses.meta.links"
                    :last-page="expenses.meta.last_page"
                />
            </div>
        </div>
    </AppLayout>
</template>
