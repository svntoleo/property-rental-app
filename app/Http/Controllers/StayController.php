<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreStayRequest;
use App\Http\Requests\UpdateStayRequest;
use App\Http\Resources\AccommodationResource;
use App\Http\Resources\StayResource;
use App\Http\Resources\StayCategoryResource;
use App\Models\Accommodation;
use App\Models\Stay;
use App\Models\StayCategory;
use Inertia\Inertia;

class StayController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $stays = Stay::with(['accommodation.property', 'category', 'tenants'])
            ->latest()
            ->paginate(15);

        return Inertia::render('Stays/Index', [
            'stays' => StayResource::collection($stays),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $accommodations = Accommodation::with('property')->get();
        $categories = StayCategory::all();

        return Inertia::render('Stays/Create', [
            'accommodations' => AccommodationResource::collection($accommodations),
            'categories' => StayCategoryResource::collection($categories),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreStayRequest $request)
    {
        $stay = Stay::create($request->validated());

        return redirect()
            ->route('stays.index')
            ->with('success', 'Stay created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Stay $stay)
    {
        $stay->load([
            'accommodation.property',
            'category',
            'tenants',
        ]);

        return Inertia::render('Stays/Show', [
            'stay' => new StayResource($stay),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Stay $stay)
    {
        $accommodations = Accommodation::with('property')->get();
        $categories = StayCategory::all();

        return Inertia::render('Stays/Edit', [
            'stay' => new StayResource($stay),
            'accommodations' => AccommodationResource::collection($accommodations),
            'categories' => StayCategoryResource::collection($categories),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateStayRequest $request, Stay $stay)
    {
        $stay->update($request->validated());

        return redirect()
            ->route('stays.show', $stay)
            ->with('success', 'Stay updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Stay $stay)
    {
        $stay->delete();

        return redirect()
            ->route('stays.index')
            ->with('success', 'Stay deleted successfully.');
    }
}
