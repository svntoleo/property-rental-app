<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreStayCategoryRequest;
use App\Http\Requests\UpdateStayCategoryRequest;
use App\Http\Resources\StayCategoryResource;
use App\Models\StayCategory;
use Inertia\Inertia;

class StayCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categories = StayCategory::withCount('stays')
            ->latest()
            ->paginate(15);

        return Inertia::render('StayCategories/Index', [
            'categories' => StayCategoryResource::collection($categories),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('StayCategories/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreStayCategoryRequest $request)
    {
        $category = StayCategory::create($request->validated());

        return redirect()
            ->route('stay-categories.index')
            ->with('success', 'Stay category created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(StayCategory $stayCategory)
    {
        $stayCategory->load(['stays.accommodation.property']);

        return Inertia::render('StayCategories/Show', [
            'category' => new StayCategoryResource($stayCategory),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(StayCategory $stayCategory)
    {
        return Inertia::render('StayCategories/Edit', [
            'category' => new StayCategoryResource($stayCategory),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateStayCategoryRequest $request, StayCategory $stayCategory)
    {
        $stayCategory->update($request->validated());

        return redirect()
            ->route('stay-categories.index')
            ->with('success', 'Stay category updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(StayCategory $stayCategory)
    {
        $stayCategory->delete();

        return redirect()
            ->route('stay-categories.index')
            ->with('success', 'Stay category deleted successfully.');
    }
}
