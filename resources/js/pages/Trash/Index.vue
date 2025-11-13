<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { useBreadcrumbs } from '@/composables/useBreadcrumbs';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { formatCurrency } from '@/lib/format';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { ref, watch } from 'vue';

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

interface Property {
    id: number;
    label: string;
    address: string;
    deleted_at: string;
}

interface Accommodation {
    id: number;
    label: string;
    property?: {
        label: string;
    };
    deleted_at: string;
}

interface Stay {
    id: number;
    start_date: string;
    end_date: string;
    category?: {
        label: string;
    };
    accommodation?: {
        label: string;
        property?: {
            label: string;
        };
    };
    deleted_at: string;
}

interface Tenant {
    id: number;
    name: string;
    email: string;
    cpf?: string;
    cpf_formatted?: string;
    stay?: {
        id: number;
    };
    deleted_at: string;
}

interface Expense {
    id: number;
    label: string;
    price: number;
    property?: {
        label: string;
    };
    accommodation?: {
        label: string;
    };
    deleted_at: string;
}

type ItemType = Property | Accommodation | Stay | Tenant | Expense;

interface Props {
    items: {
        data: ItemType[];
        meta: PaginationMeta;
    };
    type: string;
    search: string;
    sort_by?: string;
    sort_dir?: 'asc' | 'desc';
}

const props = defineProps<Props>();

const { breadcrumbs } = useBreadcrumbs();

const searchQuery = ref(props.search);
const currentTab = ref(props.type);
const sortBy = ref<string>(props.sort_by || '');
const sortDir = ref<'asc' | 'desc'>(props.sort_dir || 'desc');
const suppressSearchWatch = ref(false);
let debounceHandle: ReturnType<typeof setTimeout> | undefined;

// Watch for tab changes
const applyParams = (extra: Record<string, any> = {}, replace = true) => {
    const params: Record<string, any> = { type: currentTab.value };
    if (searchQuery.value) params.search = searchQuery.value;
    if (sortBy.value) {
        params.sort_by = sortBy.value;
        params.sort_dir = sortDir.value;
    }
    Object.assign(params, extra);
    router.get('/trash', params, { preserveState: true, replace });
};

watch(currentTab, (newType) => {
    // On tab change, reset search and sort and fetch by type only
    suppressSearchWatch.value = true;
    searchQuery.value = '';
    sortBy.value = '';
    sortDir.value = 'asc';
    applyParams({ type: newType });
    suppressSearchWatch.value = false;
});

// Handle search
const handleSearch = () => {
    applyParams({}, false);
};

// Debounced live search: triggers after user stops typing
watch(searchQuery, (q) => {
    if (suppressSearchWatch.value) return;
    if (debounceHandle) clearTimeout(debounceHandle);
    debounceHandle = setTimeout(() => {
        applyParams();
    }, 350);
});

const toggleSort = (column: string) => {
    if (sortBy.value === column) {
        sortDir.value = sortDir.value === 'asc' ? 'desc' : 'asc';
    } else {
        sortBy.value = column;
        sortDir.value = 'asc';
    }
    applyParams();
};

const restoreItem = (id: number) => {
    if (confirm('Are you sure you want to restore this item?')) {
        router.post(`/trash/${currentTab.value}/${id}/restore`);
    }
};

const permanentlyDelete = (id: number) => {
    if (
        confirm(
            'Are you sure you want to PERMANENTLY delete this item? This action cannot be undone!'
        )
    ) {
        router.delete(`/trash/${currentTab.value}/${id}`);
    }
};

const formatDate = (date: string | null | undefined) => {
    if (!date) return '-';
    const dt = new Date(date);
    if (isNaN(dt.getTime())) return '-';
    return dt.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};
</script>

<template>
    <Head title="Trash" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold">Trash</h1>
                    <p class="text-sm text-muted-foreground">
                        Recover or permanently delete items
                    </p>
                </div>
            </div>

                <div class="flex items-center gap-2">
                    <Input
                        v-model="searchQuery"
                        placeholder="Search..."
                        class="max-w-sm"
                    />
                </div>

            <Tabs v-model="currentTab" class="w-full">
                <TabsList class="grid w-full grid-cols-5">
                    <TabsTrigger value="properties">Properties</TabsTrigger>
                    <TabsTrigger value="accommodations"
                        >Accommodations</TabsTrigger
                    >
                    <TabsTrigger value="stays">Stays</TabsTrigger>
                    <TabsTrigger value="tenants">Tenants</TabsTrigger>
                    <TabsTrigger value="expenses">Expenses</TabsTrigger>
                </TabsList>

                <TabsContent value="properties" class="mt-4">
                    <div class="rounded-md border">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>
                                        <button class="flex items-center gap-1" @click="toggleSort('label')">
                                            Property
                                            <span v-if="sortBy === 'label'">{{ sortDir === 'asc' ? '▲' : '▼' }}</span>
                                        </button>
                                    </TableHead>
                                    <TableHead>
                                        <button class="flex items-center gap-1" @click="toggleSort('address')">
                                            Address
                                            <span v-if="sortBy === 'address'">{{ sortDir === 'asc' ? '▲' : '▼' }}</span>
                                        </button>
                                    </TableHead>
                                    <TableHead>
                                        <button class="flex items-center gap-1" @click="toggleSort('deleted_at')">
                                            Deleted At
                                            <span v-if="sortBy === 'deleted_at'">{{ sortDir === 'asc' ? '▲' : '▼' }}</span>
                                        </button>
                                    </TableHead>
                                    <TableHead class="text-right">Actions</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-for="item in items.data as Property[]" :key="item.id">
                                    <TableCell class="font-medium">{{ item.label }}</TableCell>
                                    <TableCell>{{ item.address }}</TableCell>
                                    <TableCell>{{ formatDate(item.deleted_at) }}</TableCell>
                                    <TableCell class="text-right">
                                        <div class="flex justify-end gap-2">
                                            <Button variant="outline" size="sm" @click="restoreItem(item.id)">Restore</Button>
                                            <Button variant="destructive" size="sm" @click="permanentlyDelete(item.id)">Delete Forever</Button>
                                        </div>
                                    </TableCell>
                                </TableRow>
                                <TableRow v-if="items.data.length === 0">
                                    <TableCell colspan="4" class="text-center">No deleted properties found</TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>
                </TabsContent>

                <TabsContent value="accommodations" class="mt-4">
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
                                    <TableHead>
                                        <button class="flex items-center gap-1" @click="toggleSort('deleted_at')">
                                            Deleted At
                                            <span v-if="sortBy === 'deleted_at'">{{ sortDir === 'asc' ? '▲' : '▼' }}</span>
                                        </button>
                                    </TableHead>
                                    <TableHead class="text-right"
                                        >Actions</TableHead
                                    >
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow
                                    v-for="item in items.data as Accommodation[]"
                                    :key="item.id"
                                >
                                    <TableCell class="font-medium">{{
                                        item.label
                                    }}</TableCell>
                                    <TableCell>{{
                                        item.property?.label || 'N/A'
                                    }}</TableCell>
                                    <TableCell>{{
                                        formatDate(item.deleted_at)
                                    }}</TableCell>
                                    <TableCell class="text-right">
                                        <div class="flex justify-end gap-2">
                                            <Button
                                                variant="outline"
                                                size="sm"
                                                @click="restoreItem(item.id)"
                                            >
                                                Restore
                                            </Button>
                                            <Button
                                                variant="destructive"
                                                size="sm"
                                                @click="
                                                    permanentlyDelete(item.id)
                                                "
                                            >
                                                Delete Forever
                                            </Button>
                                        </div>
                                    </TableCell>
                                </TableRow>
                                <TableRow v-if="items.data.length === 0">
                                    <TableCell colspan="4" class="text-center">
                                        No deleted accommodations found
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>
                </TabsContent>

                <TabsContent value="stays" class="mt-4">
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
                                        <button class="flex items-center gap-1" @click="toggleSort('deleted_at')">
                                            Deleted At
                                            <span v-if="sortBy === 'deleted_at'">{{ sortDir === 'asc' ? '▲' : '▼' }}</span>
                                        </button>
                                    </TableHead>
                                    <TableHead class="text-right"
                                        >Actions</TableHead
                                    >
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow
                                    v-for="item in items.data as Stay[]"
                                    :key="item.id"
                                >
                                    <TableCell class="font-medium">{{
                                        item.category?.label || 'N/A'
                                    }}</TableCell>
                                    <TableCell
                                        >{{
                                            item.accommodation?.property
                                                ?.label || 'N/A'
                                        }}
                                        -
                                        {{
                                            item.accommodation?.label || 'N/A'
                                        }}</TableCell
                                    >
                                    <TableCell
                                        >{{ formatDate(item.start_date) }} -
                                        {{ formatDate(item.end_date) }}</TableCell
                                    >
                                    <TableCell>{{
                                        formatDate(item.deleted_at)
                                    }}</TableCell>
                                    <TableCell class="text-right">
                                        <div class="flex justify-end gap-2">
                                            <Button
                                                variant="outline"
                                                size="sm"
                                                @click="restoreItem(item.id)"
                                            >
                                                Restore
                                            </Button>
                                            <Button
                                                variant="destructive"
                                                size="sm"
                                                @click="
                                                    permanentlyDelete(item.id)
                                                "
                                            >
                                                Delete Forever
                                            </Button>
                                        </div>
                                    </TableCell>
                                </TableRow>
                                <TableRow v-if="items.data.length === 0">
                                    <TableCell colspan="5" class="text-center">
                                        No deleted stays found
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>
                </TabsContent>

                <TabsContent value="tenants" class="mt-4">
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
                                        <button class="flex items-center gap-1" @click="toggleSort('deleted_at')">
                                            Deleted At
                                            <span v-if="sortBy === 'deleted_at'">{{ sortDir === 'asc' ? '▲' : '▼' }}</span>
                                        </button>
                                    </TableHead>
                                    <TableHead class="text-right"
                                        >Actions</TableHead
                                    >
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow
                                    v-for="item in items.data as Tenant[]"
                                    :key="item.id"
                                >
                                    <TableCell class="font-medium">{{
                                        item.name
                                    }}</TableCell>
                                    <TableCell>{{ item.email }}</TableCell>
                                    <TableCell>{{ item.cpf_formatted || item.cpf || 'N/A' }}</TableCell>
                                    <TableCell>{{
                                        formatDate(item.deleted_at)
                                    }}</TableCell>
                                    <TableCell class="text-right">
                                        <div class="flex justify-end gap-2">
                                            <Button
                                                variant="outline"
                                                size="sm"
                                                @click="restoreItem(item.id)"
                                            >
                                                Restore
                                            </Button>
                                            <Button
                                                variant="destructive"
                                                size="sm"
                                                @click="
                                                    permanentlyDelete(item.id)
                                                "
                                            >
                                                Delete Forever
                                            </Button>
                                        </div>
                                    </TableCell>
                                </TableRow>
                                <TableRow v-if="items.data.length === 0">
                                    <TableCell colspan="5" class="text-center">
                                        No deleted tenants found
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>
                </TabsContent>

                <TabsContent value="expenses" class="mt-4">
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
                                        <button class="flex items-center gap-1" @click="toggleSort('deleted_at')">
                                            Deleted At
                                            <span v-if="sortBy === 'deleted_at'">{{ sortDir === 'asc' ? '▲' : '▼' }}</span>
                                        </button>
                                    </TableHead>
                                    <TableHead class="text-right"
                                        >Actions</TableHead
                                    >
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow
                                    v-for="item in items.data as Expense[]"
                                    :key="item.id"
                                >
                                    <TableCell class="font-medium">{{
                                        item.label
                                    }}</TableCell>
                                    <TableCell>{{
                                        item.property?.label || 'N/A'
                                    }}</TableCell>
                                    <TableCell>{{
                                        item.accommodation?.label || 'N/A'
                                    }}</TableCell>
                                    <TableCell>{{
                                        formatCurrency(item.price)
                                    }}</TableCell>
                                    <TableCell>{{
                                        formatDate(item.deleted_at)
                                    }}</TableCell>
                                    <TableCell class="text-right">
                                        <div class="flex justify-end gap-2">
                                            <Button
                                                variant="outline"
                                                size="sm"
                                                @click="restoreItem(item.id)"
                                            >
                                                Restore
                                            </Button>
                                            <Button
                                                variant="destructive"
                                                size="sm"
                                                @click="
                                                    permanentlyDelete(item.id)
                                                "
                                            >
                                                Delete Forever
                                            </Button>
                                        </div>
                                    </TableCell>
                                </TableRow>
                                <TableRow v-if="items.data.length === 0">
                                    <TableCell colspan="6" class="text-center">
                                        No deleted expenses found
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>
                </TabsContent>
            </Tabs>

            <!-- Pagination -->
            <div
                v-if="items.meta.last_page > 1"
                class="mt-4 flex items-center justify-center gap-2"
            >
                <Link
                    v-for="(link, index) in items.meta.links"
                    :key="index"
                    :href="link.url || '#'"
                    :class="{
                        'pointer-events-none': !link.url,
                    }"
                    :data="{
                        type: currentTab,
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
