<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import InputError from '@/components/InputError.vue';

interface ExpenseCategory {
    id: number;
    label: string;
}

interface Props {
    expenseCategory: ExpenseCategory;
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
    {
        title: props.expenseCategory.label,
        href: `/expense-categories/${props.expenseCategory.id}`,
    },
    {
        title: 'Edit',
        href: `/expense-categories/${props.expenseCategory.id}/edit`,
    },
];

const form = useForm({
    label: props.expenseCategory.label,
});

const submit = () => {
    form.put(`/expense-categories/${props.expenseCategory.id}`);
};
</script>

<template>
    <Head :title="`Edit ${expenseCategory.label}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <h1 class="text-2xl font-bold">Edit Expense Category</h1>

            <Card class="mx-auto w-full max-w-2xl">
                <CardHeader>
                    <CardTitle>Category Details</CardTitle>
                    <CardDescription>
                        Update the details for this expense category
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="submit" class="space-y-4">
                        <div class="space-y-2">
                            <Label for="label">Label</Label>
                            <Input
                                id="label"
                                v-model="form.label"
                                type="text"
                                placeholder="Category name"
                            />
                            <InputError :message="form.errors.label" />
                        </div>

                        <div class="flex justify-end gap-2">
                            <Button
                                type="button"
                                variant="outline"
                                @click="
                                    $inertia.visit(
                                        `/expense-categories/${expenseCategory.id}`
                                    )
                                "
                            >
                                Cancel
                            </Button>
                            <Button type="submit" :disabled="form.processing">
                                Update Category
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
