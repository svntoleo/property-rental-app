<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
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
    accommodation: {
        id: number;
        label: string;
        property: {
            label: string;
        };
    };
    category: {
        id: number;
        label: string;
    };
    start_date: string;
    end_date: string;
    due_date: number | null;
    price: number;
}

interface Props {
    stay?: Stay;
    accommodations: Accommodation[];
    stayCategories: StayCategory[];
    isEdit?: boolean;
}

const props = defineProps<Props>();
const emit = defineEmits<{
    success: [];
}>();

const form = useForm({
    accommodation_id: props.stay?.accommodation.id || '',
    stay_category_id: props.stay?.category.id || '',
    start_date: props.stay?.start_date || '',
    end_date: props.stay?.end_date || '',
    due_date: props.stay?.due_date ?? undefined as number | undefined,
    price: props.stay?.price || '',
});

const submit = () => {
    if (props.isEdit && props.stay) {
        form.put(`/stays/${props.stay.id}`, {
            onSuccess: () => emit('success'),
        });
    } else {
        form.post('/stays', {
            onSuccess: () => emit('success'),
        });
    }
};
</script>

<template>
    <form @submit.prevent="submit" class="space-y-4">
        <div class="space-y-2">
            <Label for="accommodation_id">Accommodation</Label>
            <select
                id="accommodation_id"
                v-model="form.accommodation_id"
                class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm transition-colors file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50"
            >
                <option value="">Select an accommodation</option>
                <option
                    v-for="accommodation in accommodations"
                    :key="accommodation.id"
                    :value="accommodation.id"
                >
                    {{ accommodation.property.label }} -
                    {{ accommodation.label }}
                </option>
            </select>
            <InputError :message="form.errors.accommodation_id" />
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
            <InputError :message="form.errors.stay_category_id" />
        </div>

        <div class="space-y-2">
            <Label for="start_date">Start Date</Label>
            <Input id="start_date" v-model="form.start_date" type="date" />
            <InputError :message="form.errors.start_date" />
        </div>

        <div class="space-y-2">
            <Label for="end_date">End Date</Label>
            <Input id="end_date" v-model="form.end_date" type="date" />
            <InputError :message="form.errors.end_date" />
        </div>

        <div class="space-y-2">
            <Label for="due_date">Due Day of Month</Label>
            <Input
                id="due_date"
                v-model.number="form.due_date"
                type="number"
                min="1"
                max="31"
                placeholder="Day of month (1-31)"
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

        <div class="flex justify-end">
            <Button type="submit" :disabled="form.processing">
                {{ isEdit ? 'Update Stay' : 'Create Stay' }}
            </Button>
        </div>
    </form>
</template>
