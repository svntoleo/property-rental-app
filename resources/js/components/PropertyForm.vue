<template>
  <form @submit.prevent="submit" class="space-y-4">
    <div>
      <Label for="label">Label</Label>
      <Input
        id="label"
        v-model="form.label"
        type="text"
        required
      />
      <InputError :message="form.errors.label" />
    </div>

    <div>
      <Label for="address">Address</Label>
      <Input
        id="address"
        v-model="form.address"
        type="text"
        required
      />
      <InputError :message="form.errors.address" />
    </div>

    <div>
      <Label for="description">Description</Label>
      <textarea
        id="description"
        v-model="form.description"
        class="flex min-h-[80px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
      ></textarea>
      <InputError :message="form.errors.description" />
    </div>

    <div class="flex justify-end gap-2">
      <Button type="submit" :disabled="form.processing">
        {{ isEdit ? 'Update Property' : 'Create Property' }}
      </Button>
    </div>
  </form>
</template>

<script setup lang="ts">
import { watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';

const props = defineProps({
  property: Object,
  isEdit: Boolean,
  // optional context: 'index' | 'show'
  context: {
    type: String,
    default: 'index'
  }
});

const emit = defineEmits(['success', 'cancel']);

const form = useForm({
  label: props.property?.label || '',
  address: props.property?.address || '',
  description: props.property?.description || '',
});

watch(
  () => props.property,
  (newProperty) => {
    form.label = newProperty?.label || '';
    form.address = newProperty?.address || '';
    form.description = newProperty?.description || '';
  },
  { immediate: true }
);

function submit() {
  if (props.isEdit && props.property?.id) {
    const url = `/properties/${props.property.id}` + (props.context === 'show' ? '?from=show' : '');
    form.put(url, {
      onSuccess: () => emit('success'),
    });
  } else {
    form.post('/properties', {
      onSuccess: () => emit('success'),
    });
  }
}
</script>
