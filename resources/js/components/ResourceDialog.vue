<template>
  <Dialog :open="open" @update:open="val => { if (!val) emit('close') }">
    <DialogContent :class="contentClass">
      <DialogHeader>
        <DialogTitle>{{ title }}</DialogTitle>
      </DialogHeader>
      <slot />
    </DialogContent>
  </Dialog>
</template>

<script setup lang="ts">
import { Dialog, DialogContent, DialogHeader, DialogTitle } from '@/components/ui/dialog';

withDefaults(defineProps<{
  open: boolean;
  title: string;
  contentClass?: string;
}>(), {
  contentClass: 'max-w-2xl'
});

const emit = defineEmits<{ (e: 'close'): void }>();
</script>
