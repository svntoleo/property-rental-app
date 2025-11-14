<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface Props {
    links: PaginationLink[];
    lastPage: number;
}

defineProps<Props>();
</script>

<template>
    <div
        v-if="lastPage > 1"
        class="mt-4 flex items-center justify-center gap-2"
    >
        <Link
            v-for="(link, index) in links"
            :key="index"
            :href="link.url || '#'"
            preserve-scroll
        >
            <Button
                :variant="link.active ? 'default' : 'outline'"
                size="sm"
                :disabled="!link.url"
            >
                <span v-html="link.label" />
            </Button>
        </Link>
    </div>
</template>
