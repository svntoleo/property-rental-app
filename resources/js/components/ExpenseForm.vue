<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
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
    property: {
        id: number;
        label: string;
    };
    accommodation?: {
        id: number;
        label: string;
    };
    category: {
        id: number;
        label: string;
    };
    label: string;
    price: number;
    date: string;
    description: string | null;
}

interface Props {
    expense?: Expense;
    properties: Property[];
    accommodations: Accommodation[];
    expenseCategories: ExpenseCategory[];
    isEdit?: boolean;
}

const props = defineProps<Props>();
const emit = defineEmits<{
    success: [];
}>();

const form = useForm({
    property_id: props.expense?.property.id || '',
    accommodation_id: props.expense?.accommodation?.id || '',
    expense_category_id: props.expense?.category.id || '',
    label: props.expense?.label || '',
    price: props.expense?.price || '',
    date: props.expense?.date || '',
    description: props.expense?.description || '',
});

const submit = () => {
    if (props.isEdit && props.expense) {
        form.put(`/expenses/${props.expense.id}`, {
            onSuccess: () => emit('success'),
        });
    } else {
        form.post('/expenses', {
            onSuccess: () => emit('success'),
        });
    }
};

const filteredAccommodations = computed(() => {
    if (!form.property_id) return [];
    return props.accommodations.filter(
        (acc) => acc.property_id === Number(form.property_id)
    );
});
</script>

<template>
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
            <Label for="accommodation_id">Accommodation (Optional)</Label>
            <select
                id="accommodation_id"
                v-model="form.accommodation_id"
                :disabled="!form.property_id"
                class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm transition-colors file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50"
            >
                <option value="">Select an accommodation</option>
                <option
                    v-for="accommodation in filteredAccommodations"
                    :key="accommodation.id"
                    :value="accommodation.id"
                >
                    {{ accommodation.label }}
                </option>
            </select>
            <InputError :message="form.errors.accommodation_id" />
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
                    v-for="category in expenseCategories"
                    :key="category.id"
                    :value="category.id"
                >
                    {{ category.label }}
                </option>
            </select>
            <InputError :message="form.errors.expense_category_id" />
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
            <Label for="date">Date</Label>
            <Input id="date" v-model="form.date" type="date" />
            <InputError :message="form.errors.date" />
        </div>

        <div class="space-y-2">
            <Label for="description">Description (Optional)</Label>
            <textarea
                id="description"
                v-model="form.description"
                rows="3"
                class="flex min-h-[60px] w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50"
                placeholder="Additional details"
            />
            <InputError :message="form.errors.description" />
        </div>

        <div class="flex justify-end">
            <Button type="submit" :disabled="form.processing">
                {{ isEdit ? 'Update Expense' : 'Create Expense' }}
            </Button>
        </div>
    </form>
</template>
