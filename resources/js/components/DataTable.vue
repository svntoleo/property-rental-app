<script setup lang="ts" generic="T extends { id: number }">
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';

export interface Column<T = any> {
    key: string;
    label: string;
    sortable?: boolean;
    class?: string;
    headerClass?: string;
}

interface Props {
    data: T[];
    columns: Column<T>[];
    sortBy?: string;
    sortDir?: 'asc' | 'desc';
    emptyMessage?: string;
    emptyColspan?: number;
}

const props = withDefaults(defineProps<Props>(), {
    emptyMessage: 'No data found',
    sortBy: undefined,
    sortDir: 'asc',
});

const emit = defineEmits<{
    sort: [column: string];
}>();

function handleSort(column: Column<T>) {
    if (column.sortable) {
        emit('sort', column.key);
    }
}

function getSortIcon(column: Column<T>): string {
    if (!column.sortable || props.sortBy !== column.key) {
        return '';
    }
    return props.sortDir === 'asc' ? '▲' : '▼';
}
</script>

<template>
    <Table>
        <TableHeader>
            <TableRow>
                <TableHead
                    v-for="column in columns"
                    :key="column.key"
                    :class="column.headerClass"
                >
                    <button
                        v-if="column.sortable"
                        class="flex items-center gap-1"
                        @click="handleSort(column)"
                    >
                        {{ column.label }}
                        <span v-if="getSortIcon(column)">{{ getSortIcon(column) }}</span>
                    </button>
                    <span v-else>{{ column.label }}</span>
                </TableHead>
            </TableRow>
        </TableHeader>
        <TableBody>
            <TableRow v-for="item in data" :key="item.id">
                <TableCell
                    v-for="column in columns"
                    :key="column.key"
                    :class="column.class"
                >
                    <slot :name="`cell-${column.key}`" :item="item" :column="column">
                        {{ (item as any)[column.key] }}
                    </slot>
                </TableCell>
            </TableRow>
            <TableRow v-if="data.length === 0">
                <TableCell :colspan="emptyColspan || columns.length" class="text-center">
                    {{ emptyMessage }}
                </TableCell>
            </TableRow>
        </TableBody>
    </Table>
</template>
