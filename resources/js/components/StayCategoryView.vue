<script setup lang="ts">
interface StayCategory {
    id: number;
    label: string;
}

interface Props {
    stayCategory: StayCategory;
}

defineProps<Props>();
</script>

<template>
    <div class="space-y-4">
        <div>
            <p class="text-sm font-medium text-muted-foreground">Label</p>
            <p class="text-base">{{ stayCategory.label }}</p>
        </div>
    </div>
</template>
