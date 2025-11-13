<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { formatCurrency, formatDate } from '@/lib/format';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { ref, watch } from 'vue';

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
        title: 'Stays',
        href: '/stays',
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
    router.get('/stays', params, { preserveState: true, replace: true });
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

const deleteStay = (id: number) => {
    if (confirm('Are you sure you want to delete this stay?')) {
        router.delete(`/stays/${id}`);
    }
};
</script>

<template>
    <Head title="Stays" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-bold">Stays</h1>
                <Link href="/stays/create">
                    <Button>Create Stay</Button>
                </Link>
            </div>

            <div class="flex items-center gap-2">
                <Input
                    v-model="searchQuery"
                    placeholder="Search stays..."
                    class="max-w-sm"
                />
            </div>

            <div class="rounded-md border">
                <Table>
                    <TableHeader>
                        <TableRow>
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
                                <button class="flex items-center gap-1" @click="toggleSort('start_date')">
                                    Period
                                    <span v-if="sortBy === 'start_date'">{{ sortDir === 'asc' ? '▲' : '▼' }}</span>
                                </button>
                            </TableHead>
                            <TableHead>
                                <button class="flex items-center gap-1" @click="toggleSort('due_date')">
                                    Due Day
                                    <span v-if="sortBy === 'due_date'">{{ sortDir === 'asc' ? '▲' : '▼' }}</span>
                                </button>
                            </TableHead>
                            <TableHead>
                                <button class="flex items-center gap-1" @click="toggleSort('price')">
                                    Price
                                    <span v-if="sortBy === 'price'">{{ sortDir === 'asc' ? '▲' : '▼' }}</span>
                                </button>
                            </TableHead>
                            <TableHead class="text-right">Actions</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="stay in stays.data" :key="stay.id">
                            <TableCell class="font-medium">
                                {{ stay.category?.label || 'Uncategorized' }}
                            </TableCell>
                            <TableCell>
                                {{ stay.accommodation?.property?.label || 'Unknown' }}
                            </TableCell>
                            <TableCell>
                                {{ stay.accommodation?.label || 'Unknown' }}
                            </TableCell>
                            <TableCell>
                                {{ formatDate(stay.start_date) }} -
                                {{ formatDate(stay.end_date) }}
                            </TableCell>
                            <TableCell>
                                {{ stay.due_date ? `Day ${stay.due_date}` : '-' }}
                            </TableCell>
                            <TableCell>{{ formatCurrency(stay.price) }}</TableCell>
                            <TableCell class="text-right">
                                <div class="flex justify-end gap-2">
                                    <Link :href="`/stays/${stay.id}`">
                                        <Button variant="outline" size="sm"
                                            >View</Button
                                        >
                                    </Link>
                                    <Link :href="`/stays/${stay.id}/edit`">
                                        <Button variant="outline" size="sm"
                                            >Edit</Button
                                        >
                                    </Link>
                                    <Button
                                        variant="destructive"
                                        size="sm"
                                        @click="deleteStay(stay.id)"
                                        >Delete</Button
                                    >
                                </div>
                            </TableCell>
                        </TableRow>
                        <TableRow v-if="stays.data.length === 0">
                            <TableCell colspan="7" class="text-center">
                                No stays found
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>

            <!-- Pagination -->
            <div
                v-if="stays.meta.last_page > 1"
                class="mt-4 flex items-center justify-center gap-2"
            >
                <Link
                    v-for="(link, index) in stays.meta.links"
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
                        v-html="link.label"
                    />
                </Link>
            </div>
        </div>
    </AppLayout>
</template>
