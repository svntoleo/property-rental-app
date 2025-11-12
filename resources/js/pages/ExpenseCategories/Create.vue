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
        title: 'Create',
        href: '/expense-categories/create',
    },
];

const form = useForm({
    label: '',
});

const submit = () => {
    form.post('/expense-categories');
};
</script>

<template>
    <Head title="Create Expense Category" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <h1 class="text-2xl font-bold">Create Expense Category</h1>

            <Card class="mx-auto w-full max-w-2xl">
                <CardHeader>
                    <CardTitle>Category Details</CardTitle>
                    <CardDescription>
                        Enter the details for the new expense category
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
                                @click="$inertia.visit('/expense-categories')"
                            >
                                Cancel
                            </Button>
                            <Button type="submit" :disabled="form.processing">
                                Create Category
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
