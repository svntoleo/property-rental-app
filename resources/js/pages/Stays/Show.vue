<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import ResourceDialog from '@/components/ResourceDialog.vue';
import StayForm from '@/components/StayForm.vue';
import { useResourceModal } from '@/composables/useResourceModal';
import { useBreadcrumbs } from '@/composables/useBreadcrumbs';
import { Button } from '@/components/ui/button';
import { formatCurrency, formatDate } from '@/lib/format';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';

interface Tenant {
    id: number;
    name: string;
    email: string;
}

interface Accommodation {
    id: number;
    label: string;
    property: {
        id: number;
        label: string;
    };
}

interface StayCategory {
    id: number;
    label: string;
}

interface Stay {
    id: number;
    start_date: string;
    end_date: string;
    due_date: number | null;
    price: number;
    accommodation: Accommodation;
    category: StayCategory;
    tenants?: Tenant[];
}

interface Props {
    stay: Stay;
    accommodations: Accommodation[];
    stayCategories: StayCategory[];
}

const props = defineProps<Props>();

const { breadcrumbs } = useBreadcrumbs();

const { isOpen, open, close, entity } = useResourceModal();

const handleEditSuccess = () => {
    close();
    router.visit(`/stays/${props.stay.id}`);
};

const deleteStay = () => {
    if (confirm('Are you sure you want to delete this stay?')) {
        router.delete(`/stays/${props.stay.id}`);
    }
};
</script>

<template>
    <Head :title="stay.category.label" />

    <AppLayout :breadcrumbs="breadcrumbs as any">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-bold">
                    {{ stay.category.label }}
                </h1>
                <div class="flex gap-2">
                    <Button variant="outline" @click="open('edit', stay)">Edit</Button>
                    <Button variant="destructive" @click="deleteStay"
                        >Delete</Button
                    >
                </div>
            </div>

                        <div class="grid gap-4 md:grid-cols-2">
                                <ResourceDialog :open="isOpen" :title="'Edit Stay'" @close="close">
                                    <StayForm
                                        v-if="isOpen"
                                        :stay="(entity as any) || undefined"
                                        :accommodations="accommodations"
                                        :stayCategories="stayCategories"
                                        :isEdit="true"
                                        context="show"
                                        @success="handleEditSuccess"
                                        @cancel="close"
                                    />
                                </ResourceDialog>
                <Card>
                    <CardHeader>
                        <CardTitle>Stay Details</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-2">
                        <div>
                            <p class="text-sm font-medium">Accommodation</p>
                            <Link
                                :href="`/accommodations/${stay.accommodation.id}`"
                                class="text-sm text-primary hover:underline"
                            >
                                {{ stay.accommodation.property.label }} -
                                {{ stay.accommodation.label }}
                            </Link>
                        </div>
                        <div>
                            <p class="text-sm font-medium">Period</p>
                            <p class="text-sm text-muted-foreground">
                                {{ formatDate(stay.start_date) }} -
                                {{ formatDate(stay.end_date) }}
                            </p>
                        </div>
                        <div>
                            <p class="text-sm font-medium">Due Day of Month</p>
                            <p class="text-sm text-muted-foreground">
                                {{ stay.due_date ? `Day ${stay.due_date}` : '-' }}
                            </p>
                        </div>
                        <div>
                            <p class="text-sm font-medium">Category</p>
                            <p class="text-sm text-muted-foreground">
                                {{ stay.category.label }}
                            </p>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>Pricing</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div>
                            <p class="text-sm font-medium">Total Price</p>
                            <p class="text-2xl font-bold">
                                {{ formatCurrency(stay.price) }}
                            </p>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <div v-if="stay.tenants?.length" class="grid gap-4">
                <h2 class="text-xl font-semibold">Tenants</h2>
                <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                    <Card v-for="tenant in stay.tenants" :key="tenant.id">
                        <CardHeader>
                            <CardTitle>{{ tenant.name }}</CardTitle>
                            <CardDescription>{{ tenant.email }}</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <Link :href="`/tenants/${tenant.id}`">
                                <Button variant="outline" size="sm"
                                    >View Details</Button
                                >
                            </Link>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
