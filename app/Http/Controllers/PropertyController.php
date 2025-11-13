<?php
namespace App\Http\Controllers;

use App\Http\Resources\AccommodationResource;
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
        // Expenses search and sorting
        $expenseSearch = request('expense_search');
        $expenseSortBy = request('expense_sort_by', 'date');
        $expenseSortDir = request('expense_sort_dir', 'desc') === 'desc' ? 'desc' : 'asc';
        $allowedExpenseSorts = ['label', 'price', 'date', 'description'];
        $expenseSortBy = in_array($expenseSortBy, $allowedExpenseSorts) ? $expenseSortBy : 'date';

        $expenses = $property->expenses()
            ->with('category')
            ->when(!empty($expenseSearch), function ($query) use ($expenseSearch) {
                $query->where(function ($q) use ($expenseSearch) {
                    $q->where('label', 'like', "%{$expenseSearch}%")
                        ->orWhere('description', 'like', "%{$expenseSearch}%")
                        ->orWhereHas('category', function ($cat) use ($expenseSearch) {
                            $cat->where('label', 'like', "%{$expenseSearch}%");
                        });
                });
            })
            ->orderBy($expenseSortBy, $expenseSortDir)
            ->paginate(10)
            ->appends([
                'expense_search' => $expenseSearch,
                'expense_sort_by' => $expenseSortBy,
                'expense_sort_dir' => $expenseSortDir,
            ]);

        // Accommodations search and sorting
        $accommodationSearch = request('accommodation_search');
        $accommodationSortBy = request('accommodation_sort_by', 'label');
        $accommodationSortDir = request('accommodation_sort_dir', 'asc') === 'desc' ? 'desc' : 'asc';
        $allowedAccommodationSorts = ['label'];
        $accommodationSortBy = in_array($accommodationSortBy, $allowedAccommodationSorts) ? $accommodationSortBy : 'label';

        $accommodations = $property->accommodations()
            ->when(!empty($accommodationSearch), function ($query) use ($accommodationSearch) {
                $query->where('label', 'like', "%{$accommodationSearch}%");
            })
            ->orderBy($accommodationSortBy, $accommodationSortDir)
            ->paginate(10)
            ->appends([
                'accommodation_search' => $accommodationSearch,
                'accommodation_sort_by' => $accommodationSortBy,
                'accommodation_sort_dir' => $accommodationSortDir,
            ]);

        return Inertia::render('Properties/Show', [
            'property' => new PropertyResource($property),
            'expenses' => ExpenseResource::collection($expenses),
            'expense_search' => $expenseSearch ?? '',
            'expense_sort_by' => $expenseSortBy,
            'expense_sort_dir' => $expenseSortDir,
            'accommodations' => AccommodationResource::collection($accommodations),
            'accommodation_search' => $accommodationSearch ?? '',
            'accommodation_sort_by' => $accommodationSortBy,
            'accommodation_sort_dir' => $accommodationSortDir,
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
