<template>
  <div class="space-y-6">
    <!-- Accommodation Information -->
    <div class="space-y-4">
      <h3 class="text-lg font-semibold">Accommodation Information</h3>
      <div class="grid grid-cols-2 gap-4">
        <div>
          <p class="text-sm text-muted-foreground">Label</p>
          <p class="font-medium">{{ accommodation.label }}</p>
        </div>
        <div>
          <p class="text-sm text-muted-foreground">Property</p>
          <p class="font-medium">{{ accommodation.property?.label || 'N/A' }}</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
defineProps({
  accommodation: {
    type: Object,
    required: true
  }
});
</script>
