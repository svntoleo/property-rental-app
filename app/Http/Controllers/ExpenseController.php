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
        $sortBy = request('sort_by', 'date');
        $sortDir = request('sort_dir') === 'asc' ? 'asc' : 'desc';

        $allowedSorts = \App\Models\Expense::allowedSorts();
        // Add virtual sorts for joined tables
        $allowedSorts = array_merge($allowedSorts, ['category', 'property', 'accommodation']);
        $sortBy = in_array($sortBy, $allowedSorts) ? $sortBy : 'date';

        $query = Expense::with(['property', 'accommodation', 'category'])
            ->search($search);

        // Handle sorting (including virtual joins for category/property/accommodation)
        if (in_array($sortBy, ['category', 'property', 'accommodation'])) {
            $query->leftJoin('expense_categories', 'expense_categories.id', '=', 'expenses.expense_category_id')
                ->leftJoin('properties', 'properties.id', '=', 'expenses.property_id')
                ->leftJoin('accommodations', 'accommodations.id', '=', 'expenses.accommodation_id')
                ->select('expenses.*');
            $sortMap = [
                'category' => 'expense_categories.label',
                'property' => 'properties.label',
                'accommodation' => 'accommodations.label',
            ];
            $query->orderBy($sortMap[$sortBy], $sortDir);
        } else {
            $query->sortBy($sortBy, $sortDir);
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

        // Get all options for edit modal dropdowns
        $properties = Property::select('id', 'label')->get();
        $accommodations = Accommodation::select('id', 'label', 'property_id')->get();
        $expenseCategories = ExpenseCategory::all(['id', 'label']);

        return Inertia::render('Expenses/Show', [
            'expense' => new ExpenseResource($expense),
            'properties' => PropertyResource::collection($properties),
            'accommodations' => AccommodationResource::collection($accommodations),
            'expenseCategories' => ExpenseCategoryResource::collection($expenseCategories),
        ]);
    }

    

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateExpenseRequest $request, Expense $expense)
    {
        $expense->update($request->validated());

        if ($request->query('from') === 'show') {
            return redirect()
                ->route('expenses.show', $expense)
                ->with('success', 'Expense updated successfully.');
        }

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
