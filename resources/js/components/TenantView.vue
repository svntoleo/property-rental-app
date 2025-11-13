<template>
  <div class="space-y-6">
    <!-- Personal Information -->
    <div class="space-y-4">
      <h3 class="text-lg font-semibold">Personal Information</h3>
      <div class="grid grid-cols-2 gap-4">
        <div>
          <p class="text-sm text-muted-foreground">Name</p>
          <p class="font-medium">{{ tenant.name }}</p>
        </div>
        <div>
          <p class="text-sm text-muted-foreground">Email</p>
          <p class="font-medium">{{ tenant.email || 'N/A' }}</p>
        </div>
        <div>
          <p class="text-sm text-muted-foreground">Phone</p>
          <p class="font-medium">{{ tenant.phone_formatted || 'N/A' }}</p>
        </div>
        <div>
          <p class="text-sm text-muted-foreground">CPF</p>
          <p class="font-medium">{{ tenant.cpf_formatted || 'N/A' }}</p>
        </div>
      </div>
    </div>

    <!-- Stay Information -->
    <div class="space-y-4">
      <h3 class="text-lg font-semibold">Stay Information</h3>
      <div class="grid grid-cols-2 gap-4">
        <div>
          <p class="text-sm text-muted-foreground">Property</p>
          <p class="font-medium">{{ tenant.stay.accommodation.property.label }}</p>
        </div>
        <div>
          <p class="text-sm text-muted-foreground">Accommodation</p>
          <p class="font-medium">{{ tenant.stay.accommodation.label }}</p>
        </div>
        <div>
          <p class="text-sm text-muted-foreground">Period</p>
          <p class="font-medium">
            {{ formatDate(tenant.stay.start_date) }} - {{ formatDate(tenant.stay.end_date) }}
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { formatDate } from '@/lib/format';

defineProps({
  tenant: {
    type: Object,
    required: true
  }
});
</script>
