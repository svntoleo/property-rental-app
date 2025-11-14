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
    $sortBy = request('sort_by', 'label');
        $sortDir = request('sort_dir') === 'asc' ? 'asc' : 'desc';

    $allowedSorts = \App\Models\Accommodation::allowedSorts();

        $query = Accommodation::query()
            ->with([
                'property',
                'activeStay.category',
                'activeStay.tenants',
                'expenses'
            ])
            ->search($search);

        if (in_array($sortBy, $allowedSorts)) {
            $query->sortBy($sortBy, $sortDir);
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
            'activeStay.category',
            'activeStay.tenants',
            'expenses.category',
        ]);

        // Get all properties for edit modal dropdown
        $properties = Property::select('id', 'label')->get();

        return Inertia::render('Accommodations/Show', [
            'accommodation' => new AccommodationResource($accommodation),
            'properties' => PropertyResource::collection($properties),
        ]);
    }
}
