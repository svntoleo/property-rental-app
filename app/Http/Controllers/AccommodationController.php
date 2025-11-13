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
        ];

        $query = Accommodation::with(['property', 'stays', 'expenses'])
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
