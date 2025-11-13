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
        $search = request('search');
        $sortBy = request('sort_by');
        $sortDir = request('sort_dir') === 'asc' ? 'asc' : 'desc';

        $allowedSorts = [
            'name' => 'tenants.name',
            'email' => 'tenants.email',
            'cpf' => 'tenants.cpf',
            'phone' => 'tenants.phone',
            'property' => 'properties.label',
            'accommodation' => 'accommodations.label',
            'start_date' => 'stays.start_date',
            'end_date' => 'stays.end_date',
            'created_at' => 'tenants.created_at',
        ];

        $query = Tenant::with(['stay.accommodation.property'])
            ->when(!empty($search), function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('cpf', 'like', "%{$search}%");
                });
            });

        if ($sortBy && isset($allowedSorts[$sortBy])) {
            if (in_array($sortBy, ['property', 'accommodation', 'start_date', 'end_date'])) {
                $query->leftJoin('stays', 'stays.id', '=', 'tenants.stay_id')
                    ->leftJoin('accommodations', 'accommodations.id', '=', 'stays.accommodation_id')
                    ->leftJoin('properties', 'properties.id', '=', 'accommodations.property_id')
                    ->select('tenants.*')
                    ->orderBy($allowedSorts[$sortBy], $sortDir);
            } else {
                $query->orderBy($allowedSorts[$sortBy], $sortDir);
            }
        } else {
            $query->latest();
        }

        $tenants = $query->paginate(15)->appends([
            'search' => $search,
            'sort_by' => $sortBy,
            'sort_dir' => $sortDir,
        ]);

        // Get stays for modal create/edit
        $stays = Stay::with(['accommodation.property', 'category'])->get();

        return Inertia::render('Tenants/Index', [
            'tenants' => TenantResource::collection($tenants),
            'stays' => StayResource::collection($stays),
            'search' => $search ?? '',
            'sort_by' => $sortBy,
            'sort_dir' => $sortDir,
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
            ->route('tenants.index')
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
