<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { getStayStatus, formatDate } from '@/lib/format';

interface Stay {
  id: number;
  accommodation: {
    id: number;
    label: string;
    property: {
      id: number;
      label: string;
    };
  };
  start_date: string;
  end_date: string;
}

interface Tenant {
  id: number;
  stay_id: number;
  name: string;
  email: string;
  phone: string;
  cpf: string;
}

interface Props {
  stays: Stay[];
  tenant?: Tenant;
  isEdit?: boolean;
  context?: 'index' | 'show';
}

const props = defineProps<Props>();
const emit = defineEmits<{
  success: [];
  cancel: [];
}>();

const form = useForm({
  stay_id: props.tenant?.stay_id || '',
  name: props.tenant?.name || '',
  email: props.tenant?.email || '',
  phone: props.tenant?.phone || '',
  cpf: props.tenant?.cpf || '',
});

function submit() {
  if (props.isEdit && props.tenant?.id) {
  const url = `/tenants/${props.tenant.id}` + (props.context === 'show' ? '?from=show' : '');
  form.put(url, {
      onSuccess: () => emit('success'),
    });
  } else {
    form.post('/tenants', {
      onSuccess: () => emit('success'),
    });
  }
}
</script>

<template>
  <form @submit.prevent="submit" class="space-y-4">
    <div class="space-y-2">
      <Label for="stay_id">Stay</Label>
      <select
        id="stay_id"
        v-model="form.stay_id"
        class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm transition-colors file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50"
      >
        <option value="">Select a stay</option>
        <option
          v-for="stay in stays"
          :key="stay.id"
          :value="stay.id"
        >
          {{ stay.accommodation.property.label }} -
          {{ stay.accommodation.label }} ({{ formatDate(stay.start_date) }} - {{ formatDate(stay.end_date) }}) [{{ getStayStatus(stay) }}]
        </option>
      </select>
      <InputError :message="form.errors.stay_id" />
    </div>

    <div class="space-y-2">
      <Label for="name">Name</Label>
      <Input
        id="name"
        v-model="form.name"
        type="text"
        placeholder="Full name"
      />
      <InputError :message="form.errors.name" />
    </div>

    <div class="space-y-2">
      <Label for="email">Email</Label>
      <Input
        id="email"
        v-model="form.email"
        type="email"
        placeholder="email@example.com"
      />
      <InputError :message="form.errors.email" />
    </div>

    <div class="space-y-2">
      <Label for="phone">Phone</Label>
      <Input
        id="phone"
        v-model="form.phone"
        type="text"
        placeholder="11 digits"
      />
      <InputError :message="form.errors.phone" />
    </div>

    <div class="space-y-2">
      <Label for="cpf">CPF</Label>
      <Input
        id="cpf"
        v-model="form.cpf"
        type="text"
        placeholder="11 digits"
      />
      <InputError :message="form.errors.cpf" />
    </div>

    <div class="flex justify-end gap-2">
      <Button type="submit" :disabled="form.processing">
        {{ isEdit ? 'Update Tenant' : 'Create Tenant' }}
      </Button>
    </div>
  </form>
</template>
