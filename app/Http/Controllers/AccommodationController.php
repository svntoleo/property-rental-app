<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAccommodationRequest;
use App\Http\Requests\UpdateAccommodationRequest;
use App\Http\Resources\AccommodationResource;
use App\Http\Resources\PropertyResource;
use App\Models\Accommodation;
use App\Models\Property;
use Inertia\Inertia;

class AccommodationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $search = request('search');
        $sortBy = request('sort_by');
        $sortDir = request('sort_dir') === 'asc' ? 'asc' : 'desc';

        $allowedSorts = [
            'label' => 'accommodations.label',
            'property' => 'properties.label',
            'created_at' => 'accommodations.created_at',
            'status' => 'is_occupied',
            'tenants' => 'active_tenants_count',
        ];

        $query = Accommodation::with([
                'property',
                // Only load active stays for occupancy + tenant count
                'stays' => function ($q) {
                    $q->active()->withCount('tenants');
                },
                'expenses'
            ])
            ->when(!empty($search), function ($query) use ($search) {
                $query->where('label', 'like', "%{$search}%")
                    ->orWhereHas('property', function ($q) use ($search) {
                        $q->where('label', 'like', "%{$search}%");
                    });
            });

        if ($sortBy && isset($allowedSorts[$sortBy])) {
            if ($sortBy === 'property') {
                $query->leftJoin('properties', 'properties.id', '=', 'accommodations.property_id')
                    ->select('accommodations.*')
                    ->orderBy('properties.label', $sortDir);
            } elseif ($sortBy === 'status' || $sortBy === 'tenants') {
                // Subquery to calculate occupancy and tenant count for sorting
                $query->leftJoin('stays', function ($join) {
                    $join->on('stays.accommodation_id', '=', 'accommodations.id')
                         ->where('stays.start_date', '<=', now())
                         ->where('stays.end_date', '>=', now())
                         ->whereNull('stays.deleted_at');
                })
                ->select('accommodations.*');
                
                if ($sortBy === 'status') {
                    // Sort by whether there's an active stay (occupied = 1, free = 0)
                    $query->orderByRaw('CASE WHEN stays.id IS NOT NULL THEN 1 ELSE 0 END ' . $sortDir);
                } else {
                    // Sort by tenant count of active stay
                    $query->leftJoin('tenants', 'tenants.stay_id', '=', 'stays.id')
                        ->groupBy('accommodations.id')
                        ->orderByRaw('COUNT(DISTINCT tenants.id) ' . $sortDir);
                }
            } else {
                $query->orderBy($allowedSorts[$sortBy], $sortDir);
            }
        } else {
            $query->latest();
        }

        $accommodations = $query->paginate(15)->appends([
            'search' => $search,
            'sort_by' => $sortBy,
            'sort_dir' => $sortDir,
        ]);

        // Get properties for modal create/edit
        $properties = Property::select('id', 'label', 'address')->get();

        return Inertia::render('Accommodations/Index', [
            'accommodations' => AccommodationResource::collection($accommodations),
            'properties' => PropertyResource::collection($properties),
            'search' => $search ?? '',
            'sort_by' => $sortBy,
            'sort_dir' => $sortDir,
        ]);
    }

    

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreAccommodationRequest $request)
    {
        $accommodation = Accommodation::create($request->validated());

        return redirect()
            ->route('accommodations.index')
            ->with('success', 'Accommodation created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Accommodation $accommodation)
    {
        $accommodation->load([
            'property',
            'stays.tenants',
            'stays.category',
            'expenses.category',
        ]);

        // Get all properties for edit modal dropdown
        $properties = Property::select('id', 'label')->get();

        return Inertia::render('Accommodations/Show', [
            'accommodation' => new AccommodationResource($accommodation),
            'properties' => PropertyResource::collection($properties),
        ]);
    }

    

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateAccommodationRequest $request, Accommodation $accommodation)
    {
        $accommodation->update($request->validated());

        if ($request->query('from') === 'show') {
            return redirect()
                ->route('accommodations.show', $accommodation)
                ->with('success', 'Accommodation updated successfully.');
        }

        return redirect()
            ->route('accommodations.index')
            ->with('success', 'Accommodation updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Accommodation $accommodation)
    {
        $accommodation->delete();

        return redirect()
            ->route('accommodations.index')
            ->with('success', 'Accommodation deleted successfully.');
    }
}
