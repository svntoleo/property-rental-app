<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreStayRequest;
use App\Http\Requests\UpdateStayRequest;
use App\Http\Resources\AccommodationResource;
use App\Http\Resources\StayResource;
use App\Http\Resources\StayCategoryResource;
use App\Models\Accommodation;
use App\Models\Stay;
use App\Models\StayCategory;
use Inertia\Inertia;

class StayController extends Controller
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
            'category' => 'stay_categories.label',
            'property' => 'properties.label',
            'accommodation' => 'accommodations.label',
            'start_date' => 'stays.start_date',
            'end_date' => 'stays.end_date',
            'due_date' => 'stays.due_date',
            'price' => 'stays.price',
            'created_at' => 'stays.created_at',
        ];

        $query = Stay::with(['accommodation.property', 'category', 'tenants'])
            ->whereHas('accommodation', function ($q) {
                $q->whereNull('deleted_at')
                    ->whereHas('property', function ($p) {
                        $p->whereNull('deleted_at');
                    });
            })
            ->when(!empty($search), function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->whereHas('category', function ($cat) use ($search) {
                        $cat->where('label', 'like', "%{$search}%");
                    })
                    ->orWhereHas('accommodation', function ($acc) use ($search) {
                        $acc->where('label', 'like', "%{$search}%")
                            ->orWhereHas('property', function ($prop) use ($search) {
                                $prop->where('label', 'like', "%{$search}%");
                            });
                    });
                });
            });

        if ($sortBy && isset($allowedSorts[$sortBy])) {
            if (in_array($sortBy, ['category', 'property', 'accommodation'])) {
                // Join related tables for sorting by their labels
                $query->leftJoin('accommodations', 'accommodations.id', '=', 'stays.accommodation_id')
                    ->leftJoin('properties', 'properties.id', '=', 'accommodations.property_id')
                    ->leftJoin('stay_categories', 'stay_categories.id', '=', 'stays.stay_category_id')
                    ->select('stays.*')
                    ->orderBy($allowedSorts[$sortBy], $sortDir);
            } else {
                $query->orderBy($allowedSorts[$sortBy], $sortDir);
            }
        } else {
            $query->latest();
        }

        $stays = $query->paginate(15)->appends([
            'search' => $search,
            'sort_by' => $sortBy,
            'sort_dir' => $sortDir,
        ]);

        // Get accommodations and categories for modal create/edit
        $accommodations = Accommodation::with('property:id,label')
            ->whereHas('property', function ($q) {
                $q->whereNull('deleted_at');
            })
            ->whereNull('deleted_at')
            ->get(['id', 'label', 'property_id']);
        $stayCategories = StayCategory::all(['id', 'label']);

        return Inertia::render('Stays/Index', [
            'stays' => StayResource::collection($stays),
            'accommodations' => AccommodationResource::collection($accommodations),
            'stayCategories' => StayCategoryResource::collection($stayCategories),
            'search' => $search ?? '',
            'sort_by' => $sortBy,
            'sort_dir' => $sortDir,
        ]);
    }

    

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreStayRequest $request)
    {
        $stay = Stay::create($request->validated());

        return redirect()
            ->route('stays.index')
            ->with('success', 'Stay created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Stay $stay)
    {
        $stay->load([
            'accommodation.property',
            'category',
            'tenants',
        ]);

        return Inertia::render('Stays/Show', [
            'stay' => new StayResource($stay),
        ]);
    }

    

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateStayRequest $request, Stay $stay)
    {
        $stay->update($request->validated());

        return redirect()
            ->route('stays.index')
            ->with('success', 'Stay updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Stay $stay)
    {
        $stay->delete();

        return redirect()
            ->route('stays.index')
            ->with('success', 'Stay deleted successfully.');
    }
}
