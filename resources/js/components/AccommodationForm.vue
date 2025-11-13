<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';

interface Property {
  id: number;
  label: string;
}

interface Accommodation {
  id: number;
  label: string;
  property_id: number;
}

interface Props {
  properties: Property[];
  accommodation?: Accommodation;
  isEdit?: boolean;
}

const props = defineProps<Props>();
const emit = defineEmits<{
  success: [];
}>();

const form = useForm({
  property_id: props.accommodation?.property_id || '',
  label: props.accommodation?.label || '',
});

function submit() {
  if (props.isEdit && props.accommodation?.id) {
    form.put(`/accommodations/${props.accommodation.id}`, {
      onSuccess: () => emit('success'),
    });
  } else {
    form.post('/accommodations', {
      onSuccess: () => emit('success'),
    });
  }
}
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
      <Label for="label">Label</Label>
      <Input
        id="label"
        v-model="form.label"
        type="text"
        placeholder="e.g., Room A, Apartment 101"
      />
      <InputError :message="form.errors.label" />
    </div>

    <div class="flex justify-end gap-2">
      <Button type="submit" :disabled="form.processing">
        {{ isEdit ? 'Update Accommodation' : 'Create Accommodation' }}
      </Button>
    </div>
  </form>
</template>

