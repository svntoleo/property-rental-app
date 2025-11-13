<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import InputError from '@/components/InputError.vue';

interface ExpenseCategory {
    id: number;
    label: string;
}

interface Props {
    expenseCategory?: ExpenseCategory;
    isEdit?: boolean;
}

const props = defineProps<Props>();
const emit = defineEmits<{
    success: [];
}>();

const form = useForm({
    label: props.expenseCategory?.label || '',
});

const submit = () => {
    if (props.isEdit && props.expenseCategory) {
        form.put(`/expense-categories/${props.expenseCategory.id}`, {
            onSuccess: () => emit('success'),
        });
    } else {
        form.post('/expense-categories', {
            onSuccess: () => emit('success'),
        });
    }
};
</script>

<template>
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

        <div class="flex justify-end">
            <Button type="submit" :disabled="form.processing">
                {{ isEdit ? 'Update Category' : 'Create Category' }}
            </Button>
        </div>
    </form>
</template>
