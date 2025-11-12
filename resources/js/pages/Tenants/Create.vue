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

interface Stay {
    id: number;
    start_date: string;
    end_date: string;
    accommodation: {
        label: string;
        property: {
            label: string;
        };
    };
    stay_category: {
        label: string;
    };
}

interface Props {
    stays: Stay[];
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Tenants',
        href: '/tenants',
    },
    {
        title: 'Create',
        href: '/tenants/create',
    },
];

const form = useForm({
    stay_id: '',
    name: '',
    email: '',
    phone: '',
    cpf: '',
});

const submit = () => {
    form.post('/tenants');
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};
</script>

<template>
    <Head title="Create Tenant" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <h1 class="text-2xl font-bold">Create Tenant</h1>

            <Card class="mx-auto w-full max-w-2xl">
                <CardHeader>
                    <CardTitle>Tenant Details</CardTitle>
                    <CardDescription>
                        Enter the details for the new tenant
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="submit" class="space-y-4">
                        <div class="space-y-2">
                            <Label for="stay_id">Stay</Label>
                            <select
                                id="stay_id"
                                v-model="form.stay_id"
                                class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm transition-colors file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50"
                            >
                                <option value="">Select a stay</option>
                                <option
                                    v-for="stay in stays"
                                    :key="stay.id"
                                    :value="stay.id"
                                >
                                    {{ stay.accommodation.property.label }} -
                                    {{ stay.accommodation.label }} ({{
                                        formatDate(stay.start_date)
                                    }}
                                    - {{ formatDate(stay.end_date) }})
                                </option>
                            </select>
                            <InputError :message="form.errors.stay_id" />
                        </div>

                        <div class="space-y-2">
                            <Label for="name">Name</Label>
                            <Input
                                id="name"
                                v-model="form.name"
                                type="text"
                                placeholder="Full name"
                            />
                            <InputError :message="form.errors.name" />
                        </div>

                        <div class="space-y-2">
                            <Label for="email">Email</Label>
                            <Input
                                id="email"
                                v-model="form.email"
                                type="email"
                                placeholder="email@example.com"
                            />
                            <InputError :message="form.errors.email" />
                        </div>

                        <div class="space-y-2">
                            <Label for="phone">Phone</Label>
                            <Input
                                id="phone"
                                v-model="form.phone"
                                type="text"
                                placeholder="11 digits"
                            />
                            <InputError :message="form.errors.phone" />
                        </div>

                        <div class="space-y-2">
                            <Label for="cpf">CPF</Label>
                            <Input
                                id="cpf"
                                v-model="form.cpf"
                                type="text"
                                placeholder="11 digits"
                            />
                            <InputError :message="form.errors.cpf" />
                        </div>

                        <div class="flex justify-end gap-2">
                            <Button
                                type="button"
                                variant="outline"
                                @click="$inertia.visit('/tenants')"
                            >
                                Cancel
                            </Button>
                            <Button type="submit" :disabled="form.processing">
                                Create Tenant
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
