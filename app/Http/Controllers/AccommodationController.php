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
        $accommodations = Accommodation::with(['property', 'stays', 'expenses'])
            ->latest()
            ->paginate(15);

        return Inertia::render('Accommodations/Index', [
            'accommodations' => AccommodationResource::collection($accommodations),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $properties = Property::select('id', 'label', 'address')->get();

        return Inertia::render('Accommodations/Create', [
            'properties' => PropertyResource::collection($properties),
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

        return Inertia::render('Accommodations/Show', [
            'accommodation' => new AccommodationResource($accommodation),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Accommodation $accommodation)
    {
        $properties = Property::select('id', 'label', 'address')->get();

        return Inertia::render('Accommodations/Edit', [
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

        return redirect()
            ->route('accommodations.show', $accommodation)
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
