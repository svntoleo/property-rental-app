<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import InputError from '@/components/InputError.vue';

interface StayCategory {
    id: number;
    label: string;
}

interface Props {
    stayCategory?: StayCategory;
    isEdit?: boolean;
    context?: 'index' | 'show';
}

const props = defineProps<Props>();
const emit = defineEmits<{
    success: [];
    cancel: [];
}>();

const form = useForm({
    label: props.stayCategory?.label || '',
});

const submit = () => {
    if (props.isEdit && props.stayCategory) {
    const url = `/stay-categories/${props.stayCategory.id}` + (props.context === 'show' ? '?from=show' : '');
    form.put(url, {
            onSuccess: () => emit('success'),
        });
    } else {
        form.post('/stay-categories', {
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
