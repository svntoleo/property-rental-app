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
import StayCategoryForm from '@/components/StayCategoryForm.vue';

interface StayCategory {
    id: number;
    label: string;
    stays_count: number;
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
        data: StayCategory[];
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
        title: 'Stay Categories',
        href: '/stay-categories',
    },
];

const deleteCategory = (id: number) => {
    if (confirm('Are you sure you want to delete this category?')) {
        router.delete(`/stay-categories/${id}`);
    }
};

// Modal state
const { isOpen, mode, entity, open: openModal, close: closeModal, onSuccess } =
    useResourceModal<StayCategory>();

</script>

<template>
    <Head title="Stay Categories" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-bold">Stay Categories</h1>
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
                            {{ category.stays_count }} stay{{
                                category.stays_count !== 1 ? 's' : ''
                            }}
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="flex gap-2">
                        <Link :href="`/stay-categories/${category.id}`">
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
        <ResourceDialog :open="isOpen" :title="mode === 'create' ? 'Create Stay Category' : 'Edit Stay Category'" @close="closeModal">
            <StayCategoryForm
                v-if="isOpen"
                :stay-category="entity ?? undefined"
                :is-edit="mode === 'edit'"
                @success="onSuccess"
            />
        </ResourceDialog>
    </AppLayout>
</template>

