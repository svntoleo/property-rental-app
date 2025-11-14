<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import TenantForm from '@/components/TenantForm.vue';
import ResourceDialog from '@/components/ResourceDialog.vue';
import { useResourceModal } from '@/composables/useResourceModal';
import { useBreadcrumbs } from '@/composables/useBreadcrumbs';
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
import { ref, watch } from 'vue';
import { formatDate } from '@/lib/format';

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

function openModal(m: 'create' | 'edit', tenant: Tenant | null = null) {
    open(m, tenant);
}

function closeModal() {
    close();
}

function handleSuccess() {
    onSuccess();
    // Refresh the list after create/edit
    applyParams();
}

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
    router.get('/tenants', params, { preserveState: true, replace: true });
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
watch(searchQuery, () => {
    if (debounceHandle) clearTimeout(debounceHandle);
    debounceHandle = setTimeout(() => {
        applyParams();
    }, 350);
});

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
                    v-model="searchQuery"
                    placeholder="Search tenants..."
                    class="max-w-sm"
                />
            </div>

            <div class="rounded-md border">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>
                                <button class="flex items-center gap-1" @click="toggleSort('name')">
                                    Name
                                    <span v-if="sortBy === 'name'">{{ sortDir === 'asc' ? '▲' : '▼' }}</span>
                                </button>
                            </TableHead>
                            <TableHead>
                                <button class="flex items-center gap-1" @click="toggleSort('email')">
                                    Email
                                    <span v-if="sortBy === 'email'">{{ sortDir === 'asc' ? '▲' : '▼' }}</span>
                                </button>
                            </TableHead>
                            <TableHead>
                                <button class="flex items-center gap-1" @click="toggleSort('cpf')">
                                    CPF
                                    <span v-if="sortBy === 'cpf'">{{ sortDir === 'asc' ? '▲' : '▼' }}</span>
                                </button>
                            </TableHead>
                            <TableHead>
                                <button class="flex items-center gap-1" @click="toggleSort('phone')">
                                    Phone
                                    <span v-if="sortBy === 'phone'">{{ sortDir === 'asc' ? '▲' : '▼' }}</span>
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
                                <button class="flex items-center gap-1" @click="toggleSort('start_date')">
                                    Stay Period
                                    <span v-if="sortBy === 'start_date'">{{ sortDir === 'asc' ? '▲' : '▼' }}</span>
                                </button>
                            </TableHead>
                            <TableHead class="text-right">Actions</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="tenant in tenants.data" :key="tenant.id">
                            <TableCell class="font-medium">{{
                                tenant.name
                            }}</TableCell>
                            <TableCell>{{ tenant.email }}</TableCell>
                            <TableCell>{{ tenant.cpf_formatted }}</TableCell>
                            <TableCell>{{ tenant.phone_formatted }}</TableCell>
                            <TableCell>
                                {{ tenant.stay.accommodation.property.label }}
                            </TableCell>
                            <TableCell>
                                {{ tenant.stay.accommodation.label }}
                            </TableCell>
                            <TableCell>
                                {{ formatDate(tenant.stay.start_date) }} -
                                {{ formatDate(tenant.stay.end_date) }}
                            </TableCell>
                            <TableCell class="text-right">
                                <div class="flex justify-end gap-2">
                                    <Link :href="`/tenants/${tenant.id}`">
                                        <Button variant="outline" size="sm">View</Button>
                                    </Link>
                                    <Button variant="outline" size="sm" @click="openModal('edit', tenant)">Edit</Button>
                                    <Button
                                        variant="destructive"
                                        size="sm"
                                        @click="deleteTenant(tenant.id)"
                                        >Delete</Button
                                    >
                                </div>
                            </TableCell>
                        </TableRow>
                        <TableRow v-if="tenants.data.length === 0">
                            <TableCell colspan="8" class="text-center">
                                No tenants found
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>

            <!-- Pagination -->
            <div
                v-if="tenants.meta.last_page > 1"
                class="mt-4 flex items-center justify-center gap-2"
            >
                <Link
                    v-for="(link, index) in tenants.meta.links"
                    :key="index"
                    :href="link.url || '#'"
                    :class="{
                        'pointer-events-none': !link.url,
                    }"
                    :data="{
                        ...(searchQuery ? { search: searchQuery } : {}),
                        ...(sortBy ? { sort_by: sortBy } : {}),
                        ...(sortBy ? { sort_dir: sortDir } : {}),
                    }"
                >
                    <Button
                        :variant="link.active ? 'default' : 'outline'"
                        size="sm"
                        :disabled="!link.url"
                    >
                        <span v-html="link.label" />
                    </Button>
                </Link>
            </div>
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
