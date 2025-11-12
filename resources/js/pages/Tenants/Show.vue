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

interface Stay {
    id: number;
    start_date: string;
    end_date: string;
    due_date: number | null;
    price: number;
    accommodation: {
        id: number;
        label: string;
        property: {
            id: number;
            label: string;
        };
    };
    category: {
        label: string;
    };
}

interface Tenant {
    id: number;
    name: string;
    email: string;
    phone: string;
    cpf: string;
    stay: Stay;
}

interface Props {
    tenant: Tenant;
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
        title: props.tenant.name,
        href: `/tenants/${props.tenant.id}`,
    },
];

const deleteTenant = () => {
    if (confirm('Are you sure you want to delete this tenant?')) {
        router.delete(`/tenants/${props.tenant.id}`);
    }
};

const formatCurrency = (value: number) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(value);
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};

const formatCPF = (cpf: string) => {
    return cpf.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, '$1.$2.$3-$4');
};

const formatPhone = (phone: string) => {
    return phone.replace(/(\d{2})(\d{5})(\d{4})/, '($1) $2-$3');
};
</script>

<template>
    <Head :title="tenant.name" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-bold">{{ tenant.name }}</h1>
                <div class="flex gap-2">
                    <Link :href="`/tenants/${tenant.id}/edit`">
                        <Button variant="outline">Edit</Button>
                    </Link>
                    <Button variant="destructive" @click="deleteTenant"
                        >Delete</Button
                    >
                </div>
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                <Card>
                    <CardHeader>
                        <CardTitle>Contact Information</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-2">
                        <div>
                            <p class="text-sm font-medium">Email</p>
                            <p class="text-sm text-muted-foreground">
                                {{ tenant.email }}
                            </p>
                        </div>
                        <div>
                            <p class="text-sm font-medium">Phone</p>
                            <p class="text-sm text-muted-foreground">
                                {{ formatPhone(tenant.phone) }}
                            </p>
                        </div>
                        <div>
                            <p class="text-sm font-medium">CPF</p>
                            <p class="text-sm text-muted-foreground">
                                {{ formatCPF(tenant.cpf) }}
                            </p>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>Stay Information</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-2">
                        <div>
                            <p class="text-sm font-medium">Property</p>
                            <Link
                                :href="`/properties/${tenant.stay.accommodation.property.id}`"
                                class="text-sm text-primary hover:underline"
                            >
                                {{
                                    tenant.stay.accommodation.property.label
                                }}
                            </Link>
                        </div>
                        <div>
                            <p class="text-sm font-medium">Accommodation</p>
                            <Link
                                :href="`/accommodations/${tenant.stay.accommodation.id}`"
                                class="text-sm text-primary hover:underline"
                            >
                                {{ tenant.stay.accommodation.label }}
                            </Link>
                        </div>
                        <div>
                            <p class="text-sm font-medium">Category</p>
                            <p class="text-sm text-muted-foreground">
                                {{ tenant.stay.category.label }}
                            </p>
                        </div>
                        <div>
                            <p class="text-sm font-medium">Period</p>
                            <p class="text-sm text-muted-foreground">
                                {{ formatDate(tenant.stay.start_date) }} -
                                {{ formatDate(tenant.stay.end_date) }}
                            </p>
                        </div>
                        <div>
                            <p class="text-sm font-medium">Due Day of Month</p>
                            <p class="text-sm text-muted-foreground">
                                {{ tenant.stay.due_date ? `Day ${tenant.stay.due_date}` : '-' }}
                            </p>
                        </div>
                        <div>
                            <p class="text-sm font-medium">Price</p>
                            <p class="text-sm font-semibold">
                                {{ formatCurrency(tenant.stay.price) }}
                            </p>
                        </div>
                        <Link :href="`/stays/${tenant.stay.id}`">
                            <Button variant="outline" size="sm"
                                >View Stay Details</Button
                            >
                        </Link>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
