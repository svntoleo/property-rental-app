
<script setup lang="ts">
import { Dialog, DialogContent, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import TenantForm from '@/components/TenantForm.vue';
import { router } from '@inertiajs/vue3';

// Match the shapes used by TenantForm to satisfy TypeScript
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

const props = withDefaults(defineProps<{
  stays: Stay[];
  tenant?: Tenant;
}>(), {
  // These pages are legacy; provide safe defaults to avoid editor errors
  stays: () => [] as Stay[],
});

function closeModal() {
  window.history.length > 1 ? window.history.back() : router.visit('/tenants');
}
</script>

<template>
  <Dialog :open="true" @update:open="val => { if (!val) closeModal() }">
    <DialogContent class="max-w-2xl">
      <DialogHeader>
        <DialogTitle>Edit Tenant</DialogTitle>
      </DialogHeader>
  <TenantForm :stays="stays" :tenant="tenant" :isEdit="true" @success="closeModal" />
    </DialogContent>
  </Dialog>
</template>