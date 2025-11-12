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
import { computed } from 'vue';

interface Property {
    id: number;
    label: string;
}

interface Accommodation {
    id: number;
    label: string;
    property_id: number;
}

interface ExpenseCategory {
    id: number;
    label: string;
}

interface Expense {
    id: number;
    property_id: number;
    accommodation_id: number | null;
    expense_category_id: number;
    label: string;
    price: number;
    description: string | null;
}

interface Props {
    expense: Expense;
    properties: Property[];
    accommodations: Accommodation[];
    categories: ExpenseCategory[];
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Expenses',
        href: '/expenses',
    },
    {
        title: props.expense.label,
        href: `/expenses/${props.expense.id}`,
    },
    {
        title: 'Edit',
        href: `/expenses/${props.expense.id}/edit`,
    },
];

const form = useForm({
    property_id: props.expense.property_id,
    accommodation_id: props.expense.accommodation_id || '',
    expense_category_id: props.expense.expense_category_id,
    label: props.expense.label,
    price: props.expense.price,
    description: props.expense.description || '',
});

const submit = () => {
    form.put(`/expenses/${props.expense.id}`);
};

const filteredAccommodations = computed(() => {
    if (!form.property_id) return [];
    return props.accommodations.filter(
        (acc) => acc.property_id === Number(form.property_id)
    );
});
</script>

<template>
    <Head :title="`Edit ${expense.label}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <h1 class="text-2xl font-bold">Edit Expense</h1>

            <Card class="mx-auto w-full max-w-2xl">
                <CardHeader>
                    <CardTitle>Expense Details</CardTitle>
                    <CardDescription>
                        Update the details for this expense
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="submit" class="space-y-4">
                        <div class="space-y-2">
                            <Label for="property_id">Property</Label>
                            <select
                                id="property_id"
                                v-model="form.property_id"
                                class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm transition-colors file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50"
                            >
                                <option value="">Select a property</option>
                                <option
                                    v-for="property in properties"
                                    :key="property.id"
                                    :value="property.id"
                                >
                                    {{ property.label }}
                                </option>
                            </select>
                            <InputError :message="form.errors.property_id" />
                        </div>

                        <div class="space-y-2">
                            <Label for="accommodation_id"
                                >Accommodation (Optional)</Label
                            >
                            <select
                                id="accommodation_id"
                                v-model="form.accommodation_id"
                                :disabled="!form.property_id"
                                class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm transition-colors file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50"
                            >
                                <option value="">
                                    Select an accommodation
                                </option>
                                <option
                                    v-for="accommodation in filteredAccommodations"
                                    :key="accommodation.id"
                                    :value="accommodation.id"
                                >
                                    {{ accommodation.label }}
                                </option>
                            </select>
                            <InputError
                                :message="form.errors.accommodation_id"
                            />
                        </div>

                        <div class="space-y-2">
                            <Label for="expense_category_id">Category</Label>
                            <select
                                id="expense_category_id"
                                v-model="form.expense_category_id"
                                class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm transition-colors file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50"
                            >
                                <option value="">Select a category</option>
                                <option
                                    v-for="category in categories"
                                    :key="category.id"
                                    :value="category.id"
                                >
                                    {{ category.label }}
                                </option>
                            </select>
                            <InputError
                                :message="form.errors.expense_category_id"
                            />
                        </div>

                        <div class="space-y-2">
                            <Label for="label">Label</Label>
                            <Input
                                id="label"
                                v-model="form.label"
                                type="text"
                                placeholder="Expense name"
                            />
                            <InputError :message="form.errors.label" />
                        </div>

                        <div class="space-y-2">
                            <Label for="price">Price</Label>
                            <Input
                                id="price"
                                v-model="form.price"
                                type="number"
                                step="0.01"
                                placeholder="0.00"
                            />
                            <InputError :message="form.errors.price" />
                        </div>

                        <div class="space-y-2">
                            <Label for="description"
                                >Description (Optional)</Label
                            >
                            <textarea
                                id="description"
                                v-model="form.description"
                                rows="3"
                                class="flex min-h-[60px] w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50"
                                placeholder="Additional details"
                            />
                            <InputError :message="form.errors.description" />
                        </div>

                        <div class="flex justify-end gap-2">
                            <Button
                                type="button"
                                variant="outline"
                                @click="$inertia.visit(`/expenses/${expense.id}`)"
                            >
                                Cancel
                            </Button>
                            <Button type="submit" :disabled="form.processing">
                                Update Expense
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
