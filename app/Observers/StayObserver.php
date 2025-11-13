<?php

namespace App\Observers;

use App\Models\Stay;

class StayObserver
{
    /**
     * Handle the Stay "deleting" event.
     * Cascade soft delete to tenants.
     */
    public function deleting(Stay $stay): void
    {
        // Cascade delete depending on operation type
        if ($stay->isForceDeleting()) {
            // Let DB-level cascades handle hard deletes
            return;
        }

        // Soft delete all tenants
        foreach ($stay->tenants as $tenant) {
            $tenant->delete();
        }
    }

    /**
     * Handle the Stay "restoring" event.
     * Cascade restore to tenants.
     */
    public function restoring(Stay $stay): void
    {
        // When a stay is restored, cascade to all tenants
        foreach ($stay->tenants()->withTrashed()->get() as $tenant) {
            $tenant->restore();
        }
    }
}
