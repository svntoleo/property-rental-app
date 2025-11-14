<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
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
import AccommodationForm from '@/components/AccommodationForm.vue';
import { formatDate } from '@/lib/format';
import {
    Tooltip,
    TooltipContent,
    TooltipProvider,
    TooltipTrigger,
} from '@/components/ui/tooltip';

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
    router.get('/accommodations', params, { preserveState: true, replace: true });
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
                    v-model="searchQuery"
                    placeholder="Search accommodations..."
                    class="max-w-sm"
                />
            </div>

            <div class="rounded-md border overflow-x-auto">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>
                                <button class="flex items-center gap-1" @click="toggleSort('label')">
                                    Accommodation
                                    <span v-if="sortBy === 'label'">{{ sortDir === 'asc' ? '▲' : '▼' }}</span>
                                </button>
                            </TableHead>
                            <TableHead>
                                <button class="flex items-center gap-1" @click="toggleSort('property')">
                                    Property
                                    <span v-if="sortBy === 'property'">{{ sortDir === 'asc' ? '▲' : '▼' }}</span>
                                </button>
                            </TableHead>
                            <TableHead>
                                <button class="flex items-center gap-1" @click="toggleSort('category')">
                                    Category
                                    <span v-if="sortBy === 'category'">{{ sortDir === 'asc' ? '▲' : '▼' }}</span>
                                </button>
                            </TableHead>
                            <TableHead>
                                <button class="flex items-center gap-1" @click="toggleSort('status')">
                                    Status
                                    <span v-if="sortBy === 'status'">{{ sortDir === 'asc' ? '▲' : '▼' }}</span>
                                </button>
                            </TableHead>
                            <TableHead>
                                <button class="flex items-center gap-1" @click="toggleSort('tenants')">
                                    Tenants
                                    <span v-if="sortBy === 'tenants'">{{ sortDir === 'asc' ? '▲' : '▼' }}</span>
                                </button>
                            </TableHead>
                            <TableHead class="text-right">Actions</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow
                            v-for="accommodation in accommodations.data"
                            :key="accommodation.id"
                        >
                            <TableCell class="font-medium">{{ accommodation.label }}</TableCell>
                            <TableCell>{{ accommodation.property.label }}</TableCell>
                            <TableCell>{{ accommodation.is_occupied ? (accommodation.active_stay_category || '-') : '-' }}</TableCell>
                            <TableCell>
                                <TooltipProvider v-if="accommodation.is_occupied && accommodation.active_stay_end_date">
                                    <Tooltip>
                                        <TooltipTrigger as-child>
                                            <span
                                                class="inline-flex items-center rounded border px-2 py-0.5 text-xs font-medium cursor-help"
                                                :class="accommodation.is_occupied
                                                    ? 'bg-red-500/10 text-red-500 border-red-500/20'
                                                    : 'bg-green-500/10 text-green-500 border-green-500/20'"
                                            >
                                                {{ accommodation.is_occupied ? 'Occupied' : 'Free' }}
                                            </span>
                                        </TooltipTrigger>
                                        <TooltipContent>
                                            <p>Ends: {{ formatDate(accommodation.active_stay_end_date) }}</p>
                                        </TooltipContent>
                                    </Tooltip>
                                </TooltipProvider>
                                <span
                                    v-else
                                    class="inline-flex items-center rounded border px-2 py-0.5 text-xs font-medium"
                                    :class="accommodation.is_occupied
                                        ? 'bg-red-500/10 text-red-500 border-red-500/20'
                                        : 'bg-green-500/10 text-green-500 border-green-500/20'"
                                >
                                    {{ accommodation.is_occupied ? 'Occupied' : 'Free' }}
                                </span>
                            </TableCell>
                            <TableCell>{{ accommodation.is_occupied ? accommodation.active_stay_tenants : '-' }}</TableCell>
                            <TableCell class="text-right">
                                <div class="flex justify-end gap-2">
                                    <Link :href="`/accommodations/${accommodation.id}`">
                                        <Button variant="outline" size="sm">View</Button>
                                    </Link>
                                    <Button variant="outline" size="sm" @click="openModal('edit', accommodation)">Edit</Button>
                                    <Button variant="destructive" size="sm" @click="deleteAccommodation(accommodation.id)">Delete</Button>
                                </div>
                            </TableCell>
                        </TableRow>
                        <TableRow v-if="accommodations.data.length === 0">
                            <TableCell colspan="5" class="text-center">No accommodations found</TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>

            <!-- Pagination -->
            <div
                v-if="accommodations.meta.last_page > 1"
                class="mt-4 flex items-center justify-center gap-2"
            >
                <Button
                    v-for="(link, index) in accommodations.meta.links"
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
                >
                    <span v-html="link.label" />
                </Button>
            </div>
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
