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
        // When a stay is soft-deleted, cascade to all tenants
        if ($stay->isForceDeleting()) {
            // If force deleting, let database handle cascade
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
