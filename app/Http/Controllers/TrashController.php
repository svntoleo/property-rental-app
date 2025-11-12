<?php

namespace App\Http\Controllers;

use App\Http\Resources\AccommodationResource;
use App\Http\Resources\ExpenseResource;
use App\Http\Resources\PropertyResource;
use App\Http\Resources\StayResource;
use App\Http\Resources\TenantResource;
use App\Models\Accommodation;
use App\Models\Expense;
use App\Models\Property;
use App\Models\Stay;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TrashController extends Controller
{
    /**
     * Display all trashed items.
     */
    public function index(Request $request)
    {
        $type = (string) $request->input('type', 'properties');
        // Force string to avoid null being passed to typed helpers
        $search = (string) $request->input('search', '');
        $sortBy = (string) $request->input('sort_by', '');
        $sortDir = $request->input('sort_dir') === 'asc' ? 'asc' : 'desc';

        $data = match ($type) {
            'properties' => $this->getTrashedProperties($search, $sortBy, $sortDir),
            'accommodations' => $this->getTrashedAccommodations($search, $sortBy, $sortDir),
            'stays' => $this->getTrashedStays($search, $sortBy, $sortDir),
            'tenants' => $this->getTrashedTenants($search, $sortBy, $sortDir),
            'expenses' => $this->getTrashedExpenses($search, $sortBy, $sortDir),
            default => $this->getTrashedProperties($search, $sortBy, $sortDir),
        };

        return Inertia::render('Trash/Index', [
            'items' => $data,
            'type' => $type,
            'search' => $search,
            'sort_by' => $sortBy,
            'sort_dir' => $sortDir,
        ]);
    }

    /**
     * Restore a trashed item.
     */
    public function restore(Request $request, string $type, int $id)
    {
        $model = $this->getModel($type);
        $item = $model::onlyTrashed()->findOrFail($id);
        $item->restore();

        return redirect()
            ->back()
            ->with('success', ucfirst($type) . ' restored successfully.');
    }

    /**
     * Permanently delete a trashed item.
     */
    public function forceDelete(Request $request, string $type, int $id)
    {
        $model = $this->getModel($type);
        $item = $model::onlyTrashed()->findOrFail($id);
        $item->forceDelete();

        return redirect()
            ->back()
            ->with('success', ucfirst($type) . ' permanently deleted.');
    }

    private function getTrashedProperties(?string $search = null, ?string $sortBy = null, string $sortDir = 'desc')
    {
        $query = Property::onlyTrashed();

        $allowedSorts = [
            'label' => 'properties.label',
            'address' => 'properties.address',
            'created_at' => 'properties.created_at',
            'deleted_at' => 'properties.deleted_at',
        ];

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('label', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%");
            });
        }

        if (!empty($sortBy) && isset($allowedSorts[$sortBy])) {
            $query->orderBy($allowedSorts[$sortBy], $sortDir);
        } else {
            $query->orderBy('properties.deleted_at', 'desc');
        }

        return PropertyResource::collection(
            $query->paginate(15)->appends([
                'search' => $search,
                'sort_by' => $sortBy,
                'sort_dir' => $sortDir,
            ])
        );
    }

    private function getTrashedAccommodations(?string $search = null, ?string $sortBy = null, string $sortDir = 'desc')
    {
        $query = Accommodation::onlyTrashed()
            ->with(['property' => function ($q) {
                $q->withTrashed();
            }])
            ->select('accommodations.*');

        $allowedSorts = [
            'label' => 'accommodations.label',
            'property' => 'properties.label',
            'created_at' => 'accommodations.created_at',
            'deleted_at' => 'accommodations.deleted_at',
        ];

        if (!empty($search)) {
            $query->where('label', 'like', "%{$search}%");
        }

        if (!empty($sortBy) && isset($allowedSorts[$sortBy])) {
            if ($sortBy === 'property') {
                $query->leftJoin('properties', 'properties.id', '=', 'accommodations.property_id')
                    ->orderBy('properties.label', $sortDir)
                    ->select('accommodations.*');
            } else {
                $query->orderBy($allowedSorts[$sortBy], $sortDir);
            }
        } else {
            $query->orderBy('accommodations.deleted_at', 'desc');
        }

        return AccommodationResource::collection(
            $query->paginate(15)->appends([
                'search' => $search,
                'sort_by' => $sortBy,
                'sort_dir' => $sortDir,
            ])
        );
    }

    private function getTrashedStays(?string $search = null, ?string $sortBy = null, string $sortDir = 'desc')
    {
        $query = Stay::onlyTrashed()
            ->with([
                'accommodation' => function ($q) {
                    $q->withTrashed();
                },
                'accommodation.property' => function ($q) {
                    $q->withTrashed();
                },
                'category',
            ])
            ->select('stays.*');

        $allowedSorts = [
            'category' => 'stay_categories.label',
            'property' => 'properties.label',
            'accommodation' => 'accommodations.label',
            'start_date' => 'stays.start_date',
            'end_date' => 'stays.end_date',
            'due_date' => 'stays.due_date',
            'price' => 'stays.price',
            'created_at' => 'stays.created_at',
            'deleted_at' => 'stays.deleted_at',
        ];

        if (!empty($search)) {
            $query->whereHas('accommodation', function ($q) use ($search) {
                $q->withTrashed()->where('label', 'like', "%{$search}%");
            });
        }

        if (!empty($sortBy) && isset($allowedSorts[$sortBy])) {
            if (in_array($sortBy, ['category', 'property', 'accommodation'])) {
                $query->leftJoin('accommodations', 'accommodations.id', '=', 'stays.accommodation_id')
                    ->leftJoin('properties', 'properties.id', '=', 'accommodations.property_id')
                    ->leftJoin('stay_categories', 'stay_categories.id', '=', 'stays.stay_category_id')
                    ->orderBy($allowedSorts[$sortBy], $sortDir)
                    ->select('stays.*');
            } else {
                $query->orderBy($allowedSorts[$sortBy], $sortDir);
            }
        } else {
            $query->orderBy('stays.deleted_at', 'desc');
        }

        return StayResource::collection(
            $query->paginate(15)->appends([
                'search' => $search,
                'sort_by' => $sortBy,
                'sort_dir' => $sortDir,
            ])
        );
    }

    private function getTrashedTenants(?string $search = null, ?string $sortBy = null, string $sortDir = 'desc')
    {
        $query = Tenant::onlyTrashed()
            ->with([
                'stay' => function ($q) {
                    $q->withTrashed();
                },
            ]);

        $allowedSorts = [
            'name' => 'tenants.name',
            'email' => 'tenants.email',
            'cpf' => 'tenants.cpf',
            'created_at' => 'tenants.created_at',
            'deleted_at' => 'tenants.deleted_at',
        ];

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('cpf', 'like', "%{$search}%");
            });
        }

        if (!empty($sortBy) && isset($allowedSorts[$sortBy])) {
            $query->orderBy($allowedSorts[$sortBy], $sortDir);
        } else {
            $query->orderBy('tenants.deleted_at', 'desc');
        }

        return TenantResource::collection(
            $query->paginate(15)->appends([
                'search' => $search,
                'sort_by' => $sortBy,
                'sort_dir' => $sortDir,
            ])
        );
    }

    private function getTrashedExpenses(?string $search = null, ?string $sortBy = null, string $sortDir = 'desc')
    {
        $query = Expense::onlyTrashed()
            ->with([
                'property' => function ($q) {
                    $q->withTrashed();
                },
                'accommodation' => function ($q) {
                    $q->withTrashed();
                },
                'category',
            ])
            ->select('expenses.*');

        $allowedSorts = [
            'label' => 'expenses.label',
            'description' => 'expenses.description',
            'price' => 'expenses.price',
            'category' => 'expense_categories.label',
            'property' => 'properties.label',
            'accommodation' => 'accommodations.label',
            'created_at' => 'expenses.created_at',
            'deleted_at' => 'expenses.deleted_at',
        ];

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('label', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if (!empty($sortBy) && isset($allowedSorts[$sortBy])) {
            if (in_array($sortBy, ['category', 'property', 'accommodation'])) {
                $query->leftJoin('accommodations', 'accommodations.id', '=', 'expenses.accommodation_id')
                    ->leftJoin('properties', 'properties.id', '=', 'expenses.property_id')
                    ->leftJoin('expense_categories', 'expense_categories.id', '=', 'expenses.expense_category_id')
                    ->orderBy($allowedSorts[$sortBy], $sortDir)
                    ->select('expenses.*');
            } else {
                $query->orderBy($allowedSorts[$sortBy], $sortDir);
            }
        } else {
            $query->orderBy('expenses.deleted_at', 'desc');
        }

        return ExpenseResource::collection(
            $query->paginate(15)->appends([
                'search' => $search,
                'sort_by' => $sortBy,
                'sort_dir' => $sortDir,
            ])
        );
    }

    private function getModel(string $type): string
    {
        return match ($type) {
            'properties' => Property::class,
            'accommodations' => Accommodation::class,
            'stays' => Stay::class,
            'tenants' => Tenant::class,
            'expenses' => Expense::class,
            default => Property::class,
        };
    }
}
