<?php
namespace App\Http\Controllers;

use App\Http\Resources\ExpenseResource;

use App\Http\Requests\StorePropertyRequest;
use App\Http\Requests\UpdatePropertyRequest;
use App\Http\Resources\PropertyResource;
use App\Models\Property;
use Inertia\Inertia;

class PropertyController extends Controller
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
            'label' => 'properties.label',
            'address' => 'properties.address',
            'description' => 'properties.description',
            'created_at' => 'properties.created_at',
        ];

        $query = Property::with(['accommodations', 'expenses.category'])
            ->when(!empty($search), function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('label', 'like', "%{$search}%")
                        ->orWhere('address', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            });

        if ($sortBy && isset($allowedSorts[$sortBy])) {
            $query->orderBy($allowedSorts[$sortBy], $sortDir);
        } else {
            $query->latest();
        }

        $properties = $query->paginate(15)->appends([
            'search' => $search,
            'sort_by' => $sortBy,
            'sort_dir' => $sortDir,
        ]);

        return Inertia::render('Properties/Index', [
            'properties' => PropertyResource::collection($properties),
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
        return Inertia::render('Properties/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePropertyRequest $request)
    {
        $property = Property::create($request->validated());

        return redirect()
            ->route('properties.index')
            ->with('success', 'Property created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Property $property)
    {
        $property->load([
            'accommodations.stays.tenants',
            'accommodations.expenses.category',
        ]);


        // Sorting for expenses
        $sortBy = request('sort_by', 'date');
        $sortDir = request('sort_dir', 'desc') === 'desc' ? 'desc' : 'asc';
    $allowedSorts = ['label', 'price', 'date', 'description'];
        $sortBy = in_array($sortBy, $allowedSorts) ? $sortBy : 'date';

        $expenses = $property->expenses()
            ->with('category')
            ->orderBy($sortBy, $sortDir)
            ->paginate(10)
            ->appends([
                'sort_by' => $sortBy,
                'sort_dir' => $sortDir,
            ]);

        return Inertia::render('Properties/Show', [
            'property' => new PropertyResource($property),
            'expenses' => ExpenseResource::collection($expenses),
            'sort_by' => $sortBy,
            'sort_dir' => $sortDir,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Property $property)
    {
        return Inertia::render('Properties/Edit', [
            'property' => new PropertyResource($property),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePropertyRequest $request, Property $property)
    {
        $property->update($request->validated());

        return redirect()
            ->route('properties.show', $property)
            ->with('success', 'Property updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Property $property)
    {
        $property->delete();

        return redirect()
            ->route('properties.index')
            ->with('success', 'Property deleted successfully.');
    }
}
