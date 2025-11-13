<?php

namespace App\Observers;

use App\Models\Accommodation;

class AccommodationObserver
{
    /**
     * Handle the Accommodation "deleting" event.
     * Cascade soft delete to stays, tenants, and expenses.
     */
    public function deleting(Accommodation $accommodation): void
    {
        // Cascade delete depending on operation type
        if ($accommodation->isForceDeleting()) {
            // Let DB-level cascades handle hard deletes
            return;
        }

        // Soft delete all stays
        foreach ($accommodation->stays as $stay) {
            $stay->delete(); // This will trigger StayObserver
        }

        // Soft delete all expenses
        foreach ($accommodation->expenses as $expense) {
            $expense->delete();
        }
    }

    /**
     * Handle the Accommodation "restoring" event.
     * Cascade restore to stays and their children.
     */
    public function restoring(Accommodation $accommodation): void
    {
        // When an accommodation is restored, cascade to all children
        foreach ($accommodation->stays()->withTrashed()->get() as $stay) {
            $stay->restore(); // This will trigger StayObserver
        }

        // Restore all expenses
        foreach ($accommodation->expenses()->withTrashed()->get() as $expense) {
            $expense->restore();
        }
    }
}
