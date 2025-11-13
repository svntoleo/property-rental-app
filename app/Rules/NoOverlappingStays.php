<?php

namespace App\Rules;

use App\Models\Stay;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class NoOverlappingStays implements ValidationRule
{
    protected $accommodationId;
    protected $startDate;
    protected $endDate;
    protected $excludeStayId;

    public function __construct($accommodationId, $startDate, $endDate, $excludeStayId = null)
    {
        $this->accommodationId = $accommodationId;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->excludeStayId = $excludeStayId;
    }

    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Check if there are any overlapping stays
        // Overlap occurs when: (StartA <= EndB) AND (EndA >= StartB)
        $query = Stay::where('accommodation_id', $this->accommodationId)
            ->where('start_date', '<=', $this->endDate)
            ->where('end_date', '>=', $this->startDate);

        // Exclude the current stay when updating
        if ($this->excludeStayId) {
            $query->where('id', '!=', $this->excludeStayId);
        }

        if ($query->exists()) {
            $fail('The accommodation is already booked for the selected dates.');
        }
    }
}
