
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

const props = withDefaults(defineProps<{
  stays: Stay[];
}>(), {
  // Provide a safe default to avoid editor errors when this page isn't used
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
        <DialogTitle>Create Tenant</DialogTitle>
      </DialogHeader>
  <TenantForm :stays="stays" :isEdit="false" @success="closeModal" />
    </DialogContent>
  </Dialog>
</template>
