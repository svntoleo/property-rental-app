<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm } from '@inertiajs/vue3';
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
import { computed } from 'vue';

interface Property {
    id: number;
    label: string;
    address: string;
    description: string | null;
}

interface Props {
    property: Property;
}

const props = defineProps<Props>();

const breadcrumbs = computed<BreadcrumbItem[]>(() => [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Properties',
        href: '/properties',
    },
    {
        title: props.property.label,
        href: `/properties/${props.property.id}`,
    },
    {
        title: 'Edit',
        href: `/properties/${props.property.id}/edit`,
    },
]);

const form = useForm({
    label: props.property.label,
    address: props.property.address,
    description: props.property.description || '',
});

const submit = () => {
    form.put(`/properties/${props.property.id}`);
};
</script>

<template>
    <Head :title="`Edit ${property.label}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <h1 class="text-2xl font-bold">Edit Property</h1>

            <Card class="max-w-2xl">
                <CardHeader>
                    <CardTitle>Property Information</CardTitle>
                    <CardDescription
                        >Update the property details</CardDescription
                    >
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="submit" class="space-y-4">
                        <div>
                            <Label for="label">Label</Label>
                            <Input
                                id="label"
                                v-model="form.label"
                                type="text"
                                required
                            />
                            <InputError :message="form.errors.label" />
                        </div>

                        <div>
                            <Label for="address">Address</Label>
                            <Input
                                id="address"
                                v-model="form.address"
                                type="text"
                                required
                            />
                            <InputError :message="form.errors.address" />
                        </div>

                        <div>
                            <Label for="description">Description</Label>
                            <Input
                                id="description"
                                v-model="form.description"
                                type="text"
                            />
                            <InputError :message="form.errors.description" />
                        </div>

                        <div class="flex gap-2">
                            <Button type="submit" :disabled="form.processing"
                                >Update Property</Button
                            >
                            <Button
                                type="button"
                                variant="outline"
                                @click="router.visit(`/properties/${property.id}`)"
                                >Cancel</Button
                            >
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
