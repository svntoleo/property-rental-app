<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreExpenseRequest;
use App\Http\Requests\UpdateExpenseRequest;
use App\Http\Resources\AccommodationResource;
use App\Http\Resources\ExpenseCategoryResource;
use App\Http\Resources\ExpenseResource;
use App\Http\Resources\PropertyResource;
use App\Models\Accommodation;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Property;
use Inertia\Inertia;

class ExpenseController extends Controller
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
            'label' => 'expenses.label',
            'description' => 'expenses.description',
            'price' => 'expenses.price',
            'date' => 'expenses.date',
            'category' => 'expense_categories.label',
            'property' => 'properties.label',
            'accommodation' => 'accommodations.label',
            'created_at' => 'expenses.created_at',
        ];

        $query = Expense::with(['property', 'accommodation', 'category'])
            ->when(!empty($search), function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('label', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhereHas('category', function ($cat) use ($search) {
                            $cat->where('label', 'like', "%{$search}%");
                        })
                        ->orWhereHas('property', function ($prop) use ($search) {
                            $prop->where('label', 'like', "%{$search}%");
                        });
                });
            });

        if ($sortBy && isset($allowedSorts[$sortBy])) {
            if (in_array($sortBy, ['category', 'property', 'accommodation'])) {
                $query->leftJoin('expense_categories', 'expense_categories.id', '=', 'expenses.expense_category_id')
                    ->leftJoin('properties', 'properties.id', '=', 'expenses.property_id')
                    ->leftJoin('accommodations', 'accommodations.id', '=', 'expenses.accommodation_id')
                    ->select('expenses.*')
                    ->orderBy($allowedSorts[$sortBy], $sortDir);
            } else {
                $query->orderBy($allowedSorts[$sortBy], $sortDir);
            }
        } else {
            // Default: order by date descending (latest first)
            $query->orderBy('expenses.date', 'desc')->orderBy('expenses.created_at', 'desc');
        }

        $expenses = $query->paginate(15)->appends([
            'search' => $search,
            'sort_by' => $sortBy,
            'sort_dir' => $sortDir,
        ]);

        // Get properties, accommodations, and categories for modal create/edit
        $properties = Property::select('id', 'label')->get();
        $accommodations = Accommodation::select('id', 'label', 'property_id')->get();
        $expenseCategories = ExpenseCategory::all(['id', 'label']);

        return Inertia::render('Expenses/Index', [
            'expenses' => ExpenseResource::collection($expenses),
            'properties' => PropertyResource::collection($properties),
            'accommodations' => AccommodationResource::collection($accommodations),
            'expenseCategories' => ExpenseCategoryResource::collection($expenseCategories),
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
        $properties = Property::select('id', 'label', 'address')->get();
        $accommodations = Accommodation::with('property')->get();
        $categories = ExpenseCategory::all();

        return Inertia::render('Expenses/Create', [
            'properties' => PropertyResource::collection($properties),
            'accommodations' => AccommodationResource::collection($accommodations),
            'categories' => ExpenseCategoryResource::collection($categories),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreExpenseRequest $request)
    {
        $expense = Expense::create($request->validated());

        return redirect()
            ->route('expenses.index')
            ->with('success', 'Expense created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Expense $expense)
    {
        $expense->load(['property', 'accommodation', 'category']);

        return Inertia::render('Expenses/Show', [
            'expense' => new ExpenseResource($expense),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Expense $expense)
    {
        $properties = Property::select('id', 'label', 'address')->get();
        $accommodations = Accommodation::with('property')->get();
        $categories = ExpenseCategory::all();

        return Inertia::render('Expenses/Edit', [
            'expense' => new ExpenseResource($expense),
            'properties' => PropertyResource::collection($properties),
            'accommodations' => AccommodationResource::collection($accommodations),
            'categories' => ExpenseCategoryResource::collection($categories),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateExpenseRequest $request, Expense $expense)
    {
        $expense->update($request->validated());

        return redirect()
            ->route('expenses.index')
            ->with('success', 'Expense updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Expense $expense)
    {
        $expense->delete();

        return redirect()
            ->route('expenses.index')
            ->with('success', 'Expense deleted successfully.');
    }
}
