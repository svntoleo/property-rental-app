<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreExpenseCategoryRequest;
use App\Http\Requests\UpdateExpenseCategoryRequest;
use App\Http\Resources\ExpenseCategoryResource;
use App\Models\ExpenseCategory;
use Inertia\Inertia;

class ExpenseCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categories = ExpenseCategory::withCount('expenses')
            ->latest()
            ->paginate(15);

        return Inertia::render('ExpenseCategories/Index', [
            'categories' => ExpenseCategoryResource::collection($categories),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('ExpenseCategories/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreExpenseCategoryRequest $request)
    {
        $category = ExpenseCategory::create($request->validated());

        return redirect()
            ->route('expense-categories.index')
            ->with('success', 'Expense category created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(ExpenseCategory $expenseCategory)
    {
        $expenseCategory->load(['expenses.property', 'expenses.accommodation']);

        return Inertia::render('ExpenseCategories/Show', [
            'category' => new ExpenseCategoryResource($expenseCategory),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ExpenseCategory $expenseCategory)
    {
        return Inertia::render('ExpenseCategories/Edit', [
            'category' => new ExpenseCategoryResource($expenseCategory),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateExpenseCategoryRequest $request, ExpenseCategory $expenseCategory)
    {
        $expenseCategory->update($request->validated());

        return redirect()
            ->route('expense-categories.index')
            ->with('success', 'Expense category updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ExpenseCategory $expenseCategory)
    {
        $expenseCategory->delete();

        return redirect()
            ->route('expense-categories.index')
            ->with('success', 'Expense category deleted successfully.');
    }
}
