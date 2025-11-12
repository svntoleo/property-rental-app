<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTenantRequest;
use App\Http\Requests\UpdateTenantRequest;
use App\Http\Resources\StayResource;
use App\Http\Resources\TenantResource;
use App\Models\Stay;
use App\Models\Tenant;
use Inertia\Inertia;

class TenantController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $tenants = Tenant::with(['stay.accommodation.property'])
            ->latest()
            ->paginate(15);

        return Inertia::render('Tenants/Index', [
            'tenants' => TenantResource::collection($tenants),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $stays = Stay::with(['accommodation.property', 'category'])->get();

        return Inertia::render('Tenants/Create', [
            'stays' => StayResource::collection($stays),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTenantRequest $request)
    {
        $tenant = Tenant::create($request->validated());

        return redirect()
            ->route('tenants.index')
            ->with('success', 'Tenant created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Tenant $tenant)
    {
        $tenant->load([
            'stay.accommodation.property',
            'stay.category',
        ]);

        return Inertia::render('Tenants/Show', [
            'tenant' => new TenantResource($tenant),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Tenant $tenant)
    {
        $stays = Stay::with(['accommodation.property', 'category'])->get();

        return Inertia::render('Tenants/Edit', [
            'tenant' => new TenantResource($tenant),
            'stays' => StayResource::collection($stays),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTenantRequest $request, Tenant $tenant)
    {
        $tenant->update($request->validated());

        return redirect()
            ->route('tenants.show', $tenant)
            ->with('success', 'Tenant updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Tenant $tenant)
    {
        $tenant->delete();

        return redirect()
            ->route('tenants.index')
            ->with('success', 'Tenant deleted successfully.');
    }
}
