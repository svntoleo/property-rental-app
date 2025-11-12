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
        $expenses = Expense::with(['property', 'accommodation', 'category'])
            ->latest()
            ->paginate(15);

        return Inertia::render('Expenses/Index', [
            'expenses' => ExpenseResource::collection($expenses),
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
            ->route('expenses.show', $expense)
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
