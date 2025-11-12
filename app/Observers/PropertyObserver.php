<?php

namespace App\Observers;

use App\Models\Property;

class PropertyObserver
{
    /**
     * Handle the Property "deleting" event.
     * Cascade soft delete to accommodations, stays, tenants, and expenses.
     */
    public function deleting(Property $property): void
    {
        // When a property is soft-deleted, cascade to all children
        if ($property->isForceDeleting()) {
            // If force deleting, let database handle cascade
            return;
        }

        // Soft delete all accommodations
        foreach ($property->accommodations as $accommodation) {
            $accommodation->delete(); // This will trigger AccommodationObserver
        }

        // Soft delete all direct expenses
        foreach ($property->expenses as $expense) {
            $expense->delete();
        }
    }

    /**
     * Handle the Property "restoring" event.
     * Cascade restore to accommodations and their children.
     */
    public function restoring(Property $property): void
    {
        // When a property is restored, cascade to all children
        foreach ($property->accommodations()->withTrashed()->get() as $accommodation) {
            $accommodation->restore(); // This will trigger AccommodationObserver
        }

        // Restore all direct expenses
        foreach ($property->expenses()->withTrashed()->get() as $expense) {
            $expense->restore();
        }
    }
}
