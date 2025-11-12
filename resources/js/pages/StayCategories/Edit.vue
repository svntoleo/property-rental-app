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

interface StayCategory {
    id: number;
    label: string;
}

interface Props {
    stayCategory: StayCategory;
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
    {
        title: props.stayCategory.label,
        href: `/stay-categories/${props.stayCategory.id}`,
    },
    {
        title: 'Edit',
        href: `/stay-categories/${props.stayCategory.id}/edit`,
    },
];

const form = useForm({
    label: props.stayCategory.label,
});

const submit = () => {
    form.put(`/stay-categories/${props.stayCategory.id}`);
};
</script>

<template>
    <Head :title="`Edit ${stayCategory.label}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <h1 class="text-2xl font-bold">Edit Stay Category</h1>

            <Card class="mx-auto w-full max-w-2xl">
                <CardHeader>
                    <CardTitle>Category Details</CardTitle>
                    <CardDescription>
                        Update the details for this stay category
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
                                        `/stay-categories/${stayCategory.id}`
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
