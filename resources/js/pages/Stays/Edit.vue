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

interface Accommodation {
    id: number;
    label: string;
    property: {
        label: string;
    };
}

interface StayCategory {
    id: number;
    label: string;
}

interface Stay {
    id: number;
    accommodation_id: number;
    stay_category_id: number;
    start_date: string;
    end_date: string;
    due_date: string;
    price: number;
    category: StayCategory;
}

interface Props {
    stay: Stay;
    accommodations: Accommodation[];
    stayCategories: StayCategory[];
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Stays',
        href: '/stays',
    },
    {
        title: props.stay.category.label,
        href: `/stays/${props.stay.id}`,
    },
    {
        title: 'Edit',
        href: `/stays/${props.stay.id}/edit`,
    },
];

const form = useForm({
    accommodation_id: props.stay.accommodation_id,
    stay_category_id: props.stay.stay_category_id,
    start_date: props.stay.start_date,
    end_date: props.stay.end_date,
    due_date: props.stay.due_date,
    price: props.stay.price,
});

const submit = () => {
    form.put(`/stays/${props.stay.id}`);
};
</script>

<template>
    <Head :title="`Edit ${stay.category.label}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <h1 class="text-2xl font-bold">Edit Stay</h1>

            <Card class="mx-auto w-full max-w-2xl">
                <CardHeader>
                    <CardTitle>Stay Details</CardTitle>
                    <CardDescription>
                        Update the details for this stay
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="submit" class="space-y-4">
                        <div class="space-y-2">
                            <Label for="accommodation_id">Accommodation</Label>
                            <select
                                id="accommodation_id"
                                v-model="form.accommodation_id"
                                class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm transition-colors file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50"
                            >
                                <option value="">
                                    Select an accommodation
                                </option>
                                <option
                                    v-for="accommodation in accommodations"
                                    :key="accommodation.id"
                                    :value="accommodation.id"
                                >
                                    {{ accommodation.property.label }} -
                                    {{ accommodation.label }}
                                </option>
                            </select>
                            <InputError
                                :message="form.errors.accommodation_id"
                            />
                        </div>

                        <div class="space-y-2">
                            <Label for="stay_category_id">Category</Label>
                            <select
                                id="stay_category_id"
                                v-model="form.stay_category_id"
                                class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm transition-colors file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50"
                            >
                                <option value="">Select a category</option>
                                <option
                                    v-for="category in stayCategories"
                                    :key="category.id"
                                    :value="category.id"
                                >
                                    {{ category.label }}
                                </option>
                            </select>
                            <InputError
                                :message="form.errors.stay_category_id"
                            />
                        </div>

                        <div class="space-y-2">
                            <Label for="start_date">Start Date</Label>
                            <Input
                                id="start_date"
                                v-model="form.start_date"
                                type="date"
                            />
                            <InputError :message="form.errors.start_date" />
                        </div>

                        <div class="space-y-2">
                            <Label for="end_date">End Date</Label>
                            <Input
                                id="end_date"
                                v-model="form.end_date"
                                type="date"
                            />
                            <InputError :message="form.errors.end_date" />
                        </div>

                        <div class="space-y-2">
                            <Label for="due_date">Due Date</Label>
                            <Input
                                id="due_date"
                                v-model="form.due_date"
                                type="date"
                            />
                            <InputError :message="form.errors.due_date" />
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

                        <div class="flex justify-end gap-2">
                            <Button
                                type="button"
                                variant="outline"
                                @click="$inertia.visit(`/stays/${stay.id}`)"
                            >
                                Cancel
                            </Button>
                            <Button type="submit" :disabled="form.processing">
                                Update Stay
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
