<script setup lang="ts">
import { formatCurrency, formatDate } from '@/lib/format';

interface Stay {
    id: number;
    start_date: string;
    end_date: string;
    due_date: number | null;
    price: number;
    accommodation: {
        id: number;
        label: string;
        property: {
            id: number;
            label: string;
        };
    };
    category: {
        id: number;
        label: string;
    };
    tenants?: {
        id: number;
        name: string;
        email: string;
    }[];
}

interface Props {
    stay: Stay;
}

defineProps<Props>();
</script>

<template>
    <div class="space-y-4">
        <div class="grid gap-4 md:grid-cols-2">
            <div>
                <p class="text-sm font-medium text-muted-foreground">
                    Property
                </p>
                <p class="text-base">{{ stay.accommodation.property.label }}</p>
            </div>
            <div>
                <p class="text-sm font-medium text-muted-foreground">
                    Accommodation
                </p>
                <p class="text-base">{{ stay.accommodation.label }}</p>
            </div>
        </div>

        <div class="grid gap-4 md:grid-cols-2">
            <div>
                <p class="text-sm font-medium text-muted-foreground">
                    Category
                </p>
                <p class="text-base">{{ stay.category.label }}</p>
            </div>
            <div>
                <p class="text-sm font-medium text-muted-foreground">Price</p>
                <p class="text-base">{{ formatCurrency(stay.price) }}</p>
            </div>
        </div>

        <div class="grid gap-4 md:grid-cols-2">
            <div>
                <p class="text-sm font-medium text-muted-foreground">
                    Start Date
                </p>
                <p class="text-base">{{ formatDate(stay.start_date) }}</p>
            </div>
            <div>
                <p class="text-sm font-medium text-muted-foreground">
                    End Date
                </p>
                <p class="text-base">{{ formatDate(stay.end_date) }}</p>
            </div>
        </div>

        <div v-if="stay.due_date">
            <p class="text-sm font-medium text-muted-foreground">
                Due Day of Month
            </p>
            <p class="text-base">Day {{ stay.due_date }}</p>
        </div>

        <div v-if="stay.tenants && stay.tenants.length > 0">
            <p class="text-sm font-medium text-muted-foreground">Tenants</p>
            <div class="mt-2 space-y-2">
                <div
                    v-for="tenant in stay.tenants"
                    :key="tenant.id"
                    class="rounded-md border p-3"
                >
                    <p class="font-medium">{{ tenant.name }}</p>
                    <p class="text-sm text-muted-foreground">
                        {{ tenant.email }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>
