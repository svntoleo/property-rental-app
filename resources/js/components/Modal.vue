<template>
  <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-lg p-6 relative">
      <button class="absolute top-2 right-2 text-gray-400 hover:text-gray-600" @click="$emit('close')">
        <span aria-hidden="true">&times;</span>
      </button>
      <slot />
    </div>
  </div>
</template>

<script setup lang="ts">
defineProps<{ show: boolean }>();
</script>
