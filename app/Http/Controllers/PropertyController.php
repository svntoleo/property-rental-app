<?php
namespace App\Http\Controllers;

use App\Http\Resources\AccommodationResource;
use App\Http\Resources\ExpenseResource;
use App\Http\Resources\StayResource;
use App\Http\Resources\TenantResource;

use App\Http\Requests\StorePropertyRequest;
use App\Http\Requests\UpdatePropertyRequest;
use App\Http\Resources\PropertyResource;
use App\Models\Property;
use App\Models\Expense;
use App\Models\Stay;
use App\Models\Tenant;
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
        // Expenses search and sorting (DRY via model scopes)
        $expenseSearch = request('expense_search');
        $expenseSortBy = request('expense_sort_by', 'date');
        $expenseSortDir = request('expense_sort_dir', 'desc') === 'desc' ? 'desc' : 'asc';
        $allowedExpenseSorts = \App\Models\Expense::allowedSorts();
        // Add accommodation as allowed sort for this context
        $allowedExpenseSorts = array_merge($allowedExpenseSorts, ['accommodation']);
        $expenseSortBy = in_array($expenseSortBy, $allowedExpenseSorts) ? $expenseSortBy : 'date';

        $expensesQuery = $property->expenses()
            ->with(['category', 'accommodation'])
            ->search($expenseSearch);

        // Handle accommodation sorting with leftJoin
        if ($expenseSortBy === 'accommodation') {
            $expensesQuery->leftJoin('accommodations', 'accommodations.id', '=', 'expenses.accommodation_id')
                ->select('expenses.*')
                ->orderBy('accommodations.label', $expenseSortDir);
        } else {
            $expensesQuery->sortBy($expenseSortBy, $expenseSortDir);
        }

        $expenses = $expensesQuery->paginate(10, ['*'], 'expense_page')
            ->appends([
                'expense_search' => $expenseSearch,
                'expense_sort_by' => $expenseSortBy,
                'expense_sort_dir' => $expenseSortDir,
            ]);

        // Accommodations search and sorting
        $accommodationSearch = request('accommodation_search');
        $accommodationSortBy = request('accommodation_sort_by', 'label');
        $accommodationSortDir = request('accommodation_sort_dir', 'asc') === 'desc' ? 'desc' : 'asc';
        $allowedAccommodationSorts = \App\Models\Accommodation::allowedSorts();
        $accommodationSortBy = in_array($accommodationSortBy, $allowedAccommodationSorts) ? $accommodationSortBy : 'label';

        $accommodations = $property->accommodations()
            ->with([
                'activeStay.category',
                'activeStay.tenants'
            ])
            ->search($accommodationSearch);

        if (in_array($accommodationSortBy, $allowedAccommodationSorts)) {
            $accommodations->sortBy($accommodationSortBy, $accommodationSortDir);
        } else {
            $accommodations->latest();
        }
        
        $accommodations = $accommodations->paginate(10, ['*'], 'accommodation_page')
            ->appends([
                'accommodation_search' => $accommodationSearch,
                'accommodation_sort_by' => $accommodationSortBy,
                'accommodation_sort_dir' => $accommodationSortDir,
            ]);

        // Stays - split into active, past, and future with separate search/sort
        $allowedStaySorts = ['start_date', 'end_date', 'price'];
        
        // Active stays
        $activeStaySearch = request('active_stay_search');
        $activeStaySortBy = request('active_stay_sort_by', 'start_date');
        $activeStaySortDir = request('active_stay_sort_dir', 'desc') === 'desc' ? 'desc' : 'asc';
        $activeStaySortBy = in_array($activeStaySortBy, $allowedStaySorts) ? $activeStaySortBy : 'start_date';

        $activeStays = Stay::with(['accommodation', 'category', 'tenants'])
            ->whereHas('accommodation', function ($query) use ($property) {
                $query->where('property_id', $property->id);
            })
            ->active()
            ->when(!empty($activeStaySearch), function ($query) use ($activeStaySearch) {
                $query->where(function ($q) use ($activeStaySearch) {
                    $q->whereHas('category', function ($catQuery) use ($activeStaySearch) {
                        $catQuery->where('label', 'like', "%{$activeStaySearch}%");
                    })
                    ->orWhere('start_date', 'like', "%{$activeStaySearch}%")
                    ->orWhere('end_date', 'like', "%{$activeStaySearch}%")
                    ->orWhere('price', 'like', "%{$activeStaySearch}%");
                });
            })
            ->orderBy($activeStaySortBy, $activeStaySortDir)
            ->paginate(10, ['*'], 'active_stay_page')
            ->appends([
                'active_stay_search' => $activeStaySearch,
                'active_stay_sort_by' => $activeStaySortBy,
                'active_stay_sort_dir' => $activeStaySortDir,
            ]);

        // Past stays
        $pastStaySearch = request('past_stay_search');
        $pastStaySortBy = request('past_stay_sort_by', 'end_date');
        $pastStaySortDir = request('past_stay_sort_dir', 'desc') === 'desc' ? 'desc' : 'asc';
        $pastStaySortBy = in_array($pastStaySortBy, $allowedStaySorts) ? $pastStaySortBy : 'end_date';

        $pastStays = Stay::with(['accommodation', 'category', 'tenants'])
            ->whereHas('accommodation', function ($query) use ($property) {
                $query->where('property_id', $property->id);
            })
            ->where('end_date', '<', now())
            ->when(!empty($pastStaySearch), function ($query) use ($pastStaySearch) {
                $query->where(function ($q) use ($pastStaySearch) {
                    $q->whereHas('category', function ($catQuery) use ($pastStaySearch) {
                        $catQuery->where('label', 'like', "%{$pastStaySearch}%");
                    })
                    ->orWhere('start_date', 'like', "%{$pastStaySearch}%")
                    ->orWhere('end_date', 'like', "%{$pastStaySearch}%")
                    ->orWhere('price', 'like', "%{$pastStaySearch}%");
                });
            })
            ->orderBy($pastStaySortBy, $pastStaySortDir)
            ->paginate(10, ['*'], 'past_stay_page')
            ->appends([
                'past_stay_search' => $pastStaySearch,
                'past_stay_sort_by' => $pastStaySortBy,
                'past_stay_sort_dir' => $pastStaySortDir,
            ]);

        // Future stays
        $futureStaySearch = request('future_stay_search');
        $futureStaySortBy = request('future_stay_sort_by', 'start_date');
        $futureStaySortDir = request('future_stay_sort_dir', 'asc') === 'desc' ? 'desc' : 'asc';
        $futureStaySortBy = in_array($futureStaySortBy, $allowedStaySorts) ? $futureStaySortBy : 'start_date';

        $futureStays = Stay::with(['accommodation', 'category', 'tenants'])
            ->whereHas('accommodation', function ($query) use ($property) {
                $query->where('property_id', $property->id);
            })
            ->where('start_date', '>', now())
            ->when(!empty($futureStaySearch), function ($query) use ($futureStaySearch) {
                $query->where(function ($q) use ($futureStaySearch) {
                    $q->whereHas('category', function ($catQuery) use ($futureStaySearch) {
                        $catQuery->where('label', 'like', "%{$futureStaySearch}%");
                    })
                    ->orWhere('start_date', 'like', "%{$futureStaySearch}%")
                    ->orWhere('end_date', 'like', "%{$futureStaySearch}%")
                    ->orWhere('price', 'like', "%{$futureStaySearch}%");
                });
            })
            ->orderBy($futureStaySortBy, $futureStaySortDir)
            ->paginate(10, ['*'], 'future_stay_page')
            ->appends([
                'future_stay_search' => $futureStaySearch,
                'future_stay_sort_by' => $futureStaySortBy,
                'future_stay_sort_dir' => $futureStaySortDir,
            ]);

        // Tenants search and sorting (only from active stays)
        $tenantSearch = request('tenant_search');
        $tenantSortBy = request('tenant_sort_by', 'name');
        $tenantSortDir = request('tenant_sort_dir', 'asc') === 'desc' ? 'desc' : 'asc';
        $allowedTenantSorts = ['name', 'email', 'cpf', 'phone'];
        $tenantSortBy = in_array($tenantSortBy, $allowedTenantSorts) ? $tenantSortBy : 'name';

        $tenants = Tenant::with(['stay.accommodation'])
            ->whereHas('stay', function ($query) use ($property) {
                $query->active()
                    ->whereHas('accommodation', function ($accQuery) use ($property) {
                        $accQuery->where('property_id', $property->id);
                    });
            })
            ->when(!empty($tenantSearch), function ($query) use ($tenantSearch) {
                $query->where(function ($q) use ($tenantSearch) {
                    $q->where('name', 'like', "%{$tenantSearch}%")
                        ->orWhere('email', 'like', "%{$tenantSearch}%")
                        ->orWhere('cpf', 'like', "%{$tenantSearch}%")
                        ->orWhere('phone', 'like', "%{$tenantSearch}%");
                });
            })
            ->orderBy($tenantSortBy, $tenantSortDir)
            ->paginate(10, ['*'], 'tenant_page')
            ->appends([
                'tenant_search' => $tenantSearch,
                'tenant_sort_by' => $tenantSortBy,
                'tenant_sort_dir' => $tenantSortDir,
            ]);

        // Monthly financial stats for the property (current month)
        $monthStart = now()->startOfMonth();
        $monthEnd = now()->endOfMonth();

        $expectedIncome = Stay::whereHas('accommodation', function ($query) use ($property) {
                $query->where('property_id', $property->id);
            })
            ->where('start_date', '<=', $monthEnd)
            ->where('end_date', '>=', $monthStart)
            ->sum('price');

        $expectedExpenses = Expense::where('property_id', $property->id)
            ->whereBetween('date', [$monthStart, $monthEnd])
            ->sum('price');

        // Count of free accommodations (no active stay right now)
        $freeAccommodations = $property->accommodations()
            ->whereDoesntHave('stays', function ($q) {
                $q->where('start_date', '<=', now())
                  ->where('end_date', '>=', now());
            })
            ->count();

        // Support in-place edit modals on the show page (select options)
        $allProperties = \App\Models\Property::select('id', 'label')->orderBy('label')->get();
        $propertyAccommodations = \App\Models\Accommodation::with(['property:id,label'])
            ->select('id', 'label', 'property_id')
            ->where('property_id', $property->id)
            ->orderBy('label')
            ->get();
        $expenseCategories = \App\Models\ExpenseCategory::select('id', 'label')->orderBy('label')->get();
        $stayCategories = \App\Models\StayCategory::select('id', 'label')->orderBy('label')->get();
        $staysForSelect = Stay::with(['accommodation.property'])
            ->whereHas('accommodation', function ($q) use ($property) {
                $q->where('property_id', $property->id);
            })
            ->orderBy('start_date', 'desc')
            ->get();

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
            // Three separate stay collections
            'active_stays' => StayResource::collection($activeStays),
            'active_stay_search' => $activeStaySearch ?? '',
            'active_stay_sort_by' => $activeStaySortBy,
            'active_stay_sort_dir' => $activeStaySortDir,
            'past_stays' => StayResource::collection($pastStays),
            'past_stay_search' => $pastStaySearch ?? '',
            'past_stay_sort_by' => $pastStaySortBy,
            'past_stay_sort_dir' => $pastStaySortDir,
            'future_stays' => StayResource::collection($futureStays),
            'future_stay_search' => $futureStaySearch ?? '',
            'future_stay_sort_by' => $futureStaySortBy,
            'future_stay_sort_dir' => $futureStaySortDir,
            'tenants' => TenantResource::collection($tenants),
            'tenant_search' => $tenantSearch ?? '',
            'tenant_sort_by' => $tenantSortBy,
            'tenant_sort_dir' => $tenantSortDir,
            // Select option datasets for modals
            'properties' => $allProperties,
            'accommodations_list' => $propertyAccommodations,
            'expenseCategories' => $expenseCategories,
            'stayCategories' => $stayCategories,
            'stays_list' => $staysForSelect,
            // Monthly financial stats
            'expected_month_income' => (float) $expectedIncome,
            'expected_month_expenses' => (float) $expectedExpenses,
            'expected_month_profit' => (float) ($expectedIncome - $expectedExpenses),
            // Availability stats
            'free_accommodations' => (int) $freeAccommodations,
        ]);
    }

    

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePropertyRequest $request, Property $property)
    {
        $property->update($request->validated());
        // Redirect back to show page if edit originated there
        if ($request->query('from') === 'show') {
            return redirect()
                ->route('properties.show', $property)
                ->with('success', 'Property updated successfully.');
        }

        return redirect()->route('properties.index')
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
