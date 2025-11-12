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

interface Props {
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
        title: 'Create',
        href: '/stays/create',
    },
];

const form = useForm({
    accommodation_id: '',
    stay_category_id: '',
    start_date: '',
    end_date: '',
    due_date: '',
    price: '',
});

const submit = () => {
    form.post('/stays');
};
</script>

<template>
    <Head title="Create Stay" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <h1 class="text-2xl font-bold">Create Stay</h1>

            <Card class="mx-auto w-full max-w-2xl">
                <CardHeader>
                    <CardTitle>Stay Details</CardTitle>
                    <CardDescription>
                        Enter the details for the new stay
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
                                @click="$inertia.visit('/stays')"
                            >
                                Cancel
                            </Button>
                            <Button type="submit" :disabled="form.processing">
                                Create Stay
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
