<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { useResourceModal } from '@/composables/useResourceModal';
import ResourceDialog from '@/components/ResourceDialog.vue';
import ExpenseCategoryForm from '@/components/ExpenseCategoryForm.vue';

interface ExpenseCategory {
    id: number;
    label: string;
    expenses_count: number;
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
    categories: {
        data: ExpenseCategory[];
        meta: PaginationMeta;
    };
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Expense Categories',
        href: '/expense-categories',
    },
];

const deleteCategory = (id: number) => {
    if (confirm('Are you sure you want to delete this category?')) {
        router.delete(`/expense-categories/${id}`);
    }
};

// Modal state
const { isOpen, mode, entity, open: openModal, close: closeModal, onSuccess } =
    useResourceModal<ExpenseCategory>();

</script>

<template>
    <Head title="Expense Categories" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-bold">Expense Categories</h1>
                <Button @click="openModal('create')">Create Category</Button>
            </div>

            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                <Card
                    v-for="category in categories.data"
                    :key="category.id"
                >
                    <CardHeader>
                        <CardTitle>{{ category.label }}</CardTitle>
                        <CardDescription>
                            {{ category.expenses_count }} expense{{
                                category.expenses_count !== 1 ? 's' : ''
                            }}
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="flex gap-2">
                        <Link :href="`/expense-categories/${category.id}`">
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
                            @click="openModal('edit', category)"
                        >
                            Edit
                        </Button>
                        <Button
                            variant="destructive"
                            size="sm"
                            @click="deleteCategory(category.id)"
                        >
                            Delete
                        </Button>
                    </CardContent>
                </Card>
            </div>

            <div
                v-if="categories.meta.last_page > 1"
                class="mt-4 flex items-center justify-center gap-2"
            >
                <Button
                    v-for="(link, index) in categories.meta.links"
                    :key="index"
                    :variant="link.active ? 'default' : 'outline'"
                    size="sm"
                    :disabled="!link.url"
                    @click="link.url && router.visit(link.url)"
                    v-html="link.label"
                />
            </div>
        </div>

        <!-- Unified Modal for Create/Edit -->
        <ResourceDialog :open="isOpen" :title="mode === 'create' ? 'Create Expense Category' : 'Edit Expense Category'" @close="closeModal">
            <ExpenseCategoryForm
                v-if="isOpen"
                :expense-category="entity ?? undefined"
                :is-edit="mode === 'edit'"
                @success="onSuccess"
            />
        </ResourceDialog>
    </AppLayout>
</template>
