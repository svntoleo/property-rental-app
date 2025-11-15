<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { useBreadcrumbs } from '@/composables/useBreadcrumbs';
import { useTableState } from '@/composables/useTableState';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { formatDate, formatCurrency } from '@/lib/format';
import ResourceDialog from '@/components/ResourceDialog.vue';
import PropertyForm from '@/components/PropertyForm.vue';
import AccommodationForm from '@/components/AccommodationForm.vue';
import StayForm from '@/components/StayForm.vue';
import TenantForm from '@/components/TenantForm.vue';
import ExpenseForm from '@/components/ExpenseForm.vue';
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
    // Datasets for in-place edit modals
    properties: { id: number; label: string }[];
    accommodations_list: { id: number; label: string; property_id: number; property: { label: string } }[];
    expenseCategories: { id: number; label: string }[];
    stayCategories: { id: number; label: string }[];
    stays_list: Array<{
        id: number;
        start_date: string;
        end_date: string;
        accommodation: { id: number; label: string; property: { id: number; label: string } };
    }>;
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

// Modal state for Property edit
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

// Edit handlers: navigate to the resource's show page where in-place edit is available
// Accommodations modal
const {
    isOpen: isAccOpen,
    mode: accMode,
    entity: accEntity,
    open: openAcc,
    close: closeAcc,
    onSuccess: onAccSuccess,
} = useResourceModal<any>();
const accommodationForForm = computed(() => {
    if (accMode.value === 'create') {
        return {
            label: '',
            property_id: props.property.id,
        } as any;
    }
    if (!accEntity.value) return undefined;
    return {
        id: accEntity.value.id,
        label: accEntity.value.label,
        property_id: props.property.id,
    } as any;
});
const handleAccommodationEdit = (acc: any) => openAcc('edit', acc);

// Stays modal
const {
    isOpen: isStayOpen,
    mode: stayMode,
    entity: stayEntity,
    open: openStay,
    close: closeStay,
    onSuccess: onStaySuccess,
} = useResourceModal<any>();
const stayForForm = computed(() => {
    if (stayMode.value === 'create') return undefined;
    if (!stayEntity.value) return undefined;
    return {
        accommodation: { id: stayEntity.value.accommodation?.id, label: stayEntity.value.accommodation?.label, property: { label: '' } },
        category: stayEntity.value.category,
        start_date: stayEntity.value.start_date,
        end_date: stayEntity.value.end_date,
        due_date: stayEntity.value.due_date ?? undefined,
        price: stayEntity.value.price,
        id: stayEntity.value.id,
    } as any;
});
const handleStayEdit = (stay: any) => openStay('edit', stay);

// Tenants modal
const {
    isOpen: isTenantOpen,
    mode: tenantMode,
    entity: tenantEntity,
    open: openTenant,
    close: closeTenant,
    onSuccess: onTenantSuccess,
} = useResourceModal<any>();
const tenantForForm = computed(() => {
    if (tenantMode.value === 'create') return undefined;
    if (!tenantEntity.value) return undefined;
    return {
        id: tenantEntity.value.id,
        stay_id: tenantEntity.value.stay?.id,
        name: tenantEntity.value.name,
        email: tenantEntity.value.email,
        phone: tenantEntity.value.phone,
        cpf: tenantEntity.value.cpf,
    } as any;
});
// Tenant stays list: include all stays for this property (active + inactive) provided by controller
const staysForTenantSelect = computed(() => props.stays_list || []);
const handleTenantEdit = (tenant: any) => openTenant('edit', tenant);

// Expenses modal
const {
    isOpen: isExpenseOpen,
    mode: expenseMode,
    entity: expenseEntity,
    open: openExpense,
    close: closeExpense,
    onSuccess: onExpenseSuccess,
} = useResourceModal<any>();
const expenseForModal = computed(() => {
    if (expenseMode.value === 'create') {
        return {
            property: { id: props.property.id, label: props.property.label },
            label: '',
            price: '',
            date: '',
            description: '',
            accommodation: undefined,
            category: undefined,
        } as any;
    }
    if (!expenseEntity.value) return undefined;
    return {
        ...expenseEntity.value,
        accommodation: expenseEntity.value.accommodation ?? undefined,
        category: expenseEntity.value.category ?? undefined,
    } as any;
});
const handleExpenseEdit = (expense: any) => openExpense('edit', expense);

// Delete handlers for quick actions
const deleteAccommodation = (id: number) => {
    if (confirm('Are you sure you want to delete this accommodation?')) {
        router.delete(`/accommodations/${id}`);
    }
};
const deleteStay = (id: number) => {
    if (confirm('Are you sure you want to delete this stay?')) {
        router.delete(`/stays/${id}`);
    }
};
const deleteTenant = (id: number) => {
    if (confirm('Are you sure you want to delete this tenant?')) {
        router.delete(`/tenants/${id}`);
    }
};
const deleteExpense = (id: number) => {
    if (confirm('Are you sure you want to delete this expense?')) {
        router.delete(`/expenses/${id}`);
    }
};
</script>

<template>
    <Head :title="property.label" />

    <AppLayout :breadcrumbs="breadcrumbs as any">
    <div class="flex h-full flex-1 flex-col gap-6 p-4">
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
                <div class="flex items-center justify-between">
                    <h2 class="text-xl font-semibold">Accommodations</h2>
                    <Button @click="openAcc('create')">Create Accommodation</Button>
                </div>
                
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
                    actions-type="full"
                    @sort="accommodationTable.toggleSort"
                    @edit="handleAccommodationEdit"
                    @delete="deleteAccommodation"
                />

                <TablePagination
                    :links="accommodations.meta.links"
                    :last-page="accommodations.meta.last_page"
                />
            </div>

            <div v-if="stays.data.length > 0" class="grid gap-4">
                <div class="flex items-center justify-between">
                    <h2 class="text-xl font-semibold">Active Stays</h2>
                    <Button @click="openStay('create')">Create Stay</Button>
                </div>
                
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
                    actions-type="full"
                    @sort="stayTable.toggleSort"
                    @edit="handleStayEdit"
                    @delete="deleteStay"
                />

                <TablePagination
                    :links="stays.meta.links"
                    :last-page="stays.meta.last_page"
                />
            </div>

            <div v-if="tenants.data.length > 0" class="grid gap-4">
                <div class="flex items-center justify-between">
                    <h2 class="text-xl font-semibold">Tenants</h2>
                    <Button @click="openTenant('create')">Create Tenant</Button>
                </div>
                
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
                    :show-property="false"
                    actions-type="full"
                    @sort="tenantTable.toggleSort"
                    @edit="handleTenantEdit"
                    @delete="deleteTenant"
                />

                <TablePagination
                    :links="tenants.meta.links"
                    :last-page="tenants.meta.last_page"
                />
            </div>

            <div v-if="expenses.data.length > 0" class="grid gap-4">
                <div class="flex items-center justify-between">
                    <h2 class="text-xl font-semibold">Expenses</h2>
                    <Button @click="openExpense('create')">Create Expense</Button>
                </div>
                
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
                    actions-type="full"
                    @sort="expenseTable.toggleSort"
                    @edit="handleExpenseEdit"
                    @delete="deleteExpense"
                />

                <TablePagination
                    :links="expenses.meta.links"
                    :last-page="expenses.meta.last_page"
                />
            </div>
        </div>

        <!-- In-place edit modals for related resources -->
        <ResourceDialog :open="isAccOpen" :title="accMode === 'create' ? 'Create Accommodation' : 'Edit Accommodation'" @close="closeAcc">
            <AccommodationForm
                v-if="isAccOpen"
                :accommodation="accommodationForForm"
                :properties="[{ id: props.property.id, label: props.property.label }]"
                :is-edit="accMode === 'edit'"
                context="show"
                @success="onAccSuccess"
            />
        </ResourceDialog>

        <ResourceDialog :open="isStayOpen" :title="stayMode === 'create' ? 'Create Stay' : 'Edit Stay'" @close="closeStay">
            <StayForm
                v-if="isStayOpen"
                :stay="stayForForm"
                :accommodations="props.accommodations_list"
                :stay-categories="props.stayCategories"
                :is-edit="stayMode === 'edit'"
                context="show"
                @success="onStaySuccess"
            />
        </ResourceDialog>

        <ResourceDialog :open="isTenantOpen" :title="tenantMode === 'create' ? 'Create Tenant' : 'Edit Tenant'" @close="closeTenant">
            <TenantForm
                v-if="isTenantOpen"
                :tenant="tenantForForm"
                :stays="staysForTenantSelect"
                :is-edit="tenantMode === 'edit'"
                context="show"
                @success="onTenantSuccess"
            />
        </ResourceDialog>

        <ResourceDialog :open="isExpenseOpen" :title="expenseMode === 'create' ? 'Create Expense' : 'Edit Expense'" @close="closeExpense">
            <ExpenseForm
                v-if="isExpenseOpen"
                :expense="expenseForModal"
                :properties="[{ id: props.property.id, label: props.property.label }]"
                :accommodations="props.accommodations_list"
                :expense-categories="props.expenseCategories"
                :is-edit="expenseMode === 'edit'"
                context="show"
                @success="onExpenseSuccess"
            />
        </ResourceDialog>
    </AppLayout>
</template>
