<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
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
import ResourceDialog from '@/components/ResourceDialog.vue';
import AccommodationForm from '@/components/AccommodationForm.vue';
import AccommodationView from '@/components/AccommodationView.vue';

interface Property {
    id: number;
    label: string;
    address?: string;
}

interface Accommodation {
    id: number;
    label: string;
    property: Property;
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

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Accommodations',
        href: '/accommodations',
    },
];

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
watch(searchQuery, (q) => {
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

const getModalTitle = () => {
    if (mode.value === 'create') return 'Create Accommodation';
    if (mode.value === 'edit') return 'Edit Accommodation';
    return 'Accommodation Details';
};

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

            <div class="rounded-md border">
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
                            <TableHead class="text-right">Actions</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow
                            v-for="accommodation in accommodations.data"
                            :key="accommodation.id"
                        >
                            <TableCell class="font-medium">{{
                                accommodation.label
                            }}</TableCell>
                            <TableCell>{{ accommodation.property.label }}</TableCell>
                            <TableCell class="text-right">
                                <div class="flex justify-end gap-2">
                                    <Button
                                        variant="outline"
                                        size="sm"
                                        @click="openModal('view', accommodation)"
                                    >
                                        View
                                    </Button>
                                    <Button
                                        variant="outline"
                                        size="sm"
                                        @click="openModal('edit', accommodation)"
                                    >
                                        Edit
                                    </Button>
                                    <Button
                                        variant="destructive"
                                        size="sm"
                                        @click="deleteAccommodation(accommodation.id)"
                                        >Delete</Button
                                    >
                                </div>
                            </TableCell>
                        </TableRow>
                        <TableRow v-if="accommodations.data.length === 0">
                            <TableCell colspan="3" class="text-center">
                                No accommodations found
                            </TableCell>
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
                    v-html="link.label"
                />
            </div>
        </div>

        <!-- Unified Modal -->
        <ResourceDialog :open="isOpen" :title="getModalTitle()" @close="closeModal">
            <AccommodationForm
                v-if="mode === 'create' || mode === 'edit'"
                :accommodation="accommodationForForm"
                :properties="properties"
                :is-edit="mode === 'edit'"
                @success="onSuccess"
            />
            <AccommodationView v-else-if="mode === 'view'" :accommodation="entity!" />
        </ResourceDialog>
    </AppLayout>
</template>
