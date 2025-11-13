<template>
  <div class="space-y-6">
    <!-- Property Information -->
    <div class="space-y-4">
      <h3 class="text-lg font-semibold">Property Information</h3>
      <div class="grid grid-cols-2 gap-4">
        <div>
          <p class="text-sm text-muted-foreground">Label</p>
          <p class="font-medium">{{ property.label }}</p>
        </div>
        <div>
          <p class="text-sm text-muted-foreground">Address</p>
          <p class="font-medium">{{ property.address }}</p>
        </div>
        <div class="col-span-2" v-if="property.description">
          <p class="text-sm text-muted-foreground">Description</p>
          <p class="font-medium">{{ property.description }}</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
defineProps({
  property: {
    type: Object,
    required: true
  }
});
</script>
