
<script setup lang="ts">
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter, DialogClose } from '@/components/ui/dialog';
import TenantForm from '@/components/TenantForm.vue';
import { router } from '@inertiajs/vue3';
const props = defineProps({ stays: Array });

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
      <TenantForm :stays="stays" :isEdit="false" @success="closeModal" @cancel="closeModal" />
    </DialogContent>
  </Dialog>
</template>
