import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';

export interface TableStateOptions {
    /**
     * Resource name for URL parameters (e.g., 'expense', 'accommodation')
     */
    resourceName: string;
    
    /**
     * Default sort column
     */
    defaultSortBy: string;
    
    /**
     * Default sort direction
     */
    defaultSortDir: 'asc' | 'desc';
    
    /**
     * Current URL (for building navigation)
     */
    currentUrl: string;
    
    /**
     * Initial search value from server
     */
    initialSearch?: string;
    
    /**
     * Initial sort by value from server
     */
    initialSortBy?: string;
    
    /**
     * Initial sort direction from server
     */
    initialSortDir?: 'asc' | 'desc';
    
    /**
     * Other URL parameters to preserve when applying filters
     * e.g., ['expense_*', 'stay_*'] to keep other table states
     */
    preserveParams?: Record<string, any>;
    
    /**
     * Debounce delay in milliseconds for search input
     */
    debounceMs?: number;
}

export function useTableState(options: TableStateOptions) {
    const {
        resourceName,
        defaultSortBy,
        defaultSortDir,
        currentUrl,
        initialSearch = '',
        initialSortBy,
        initialSortDir,
        preserveParams = {},
        debounceMs = 350,
    } = options;

    // Reactive state
    const searchQuery = ref(initialSearch);
    const sortBy = ref(initialSortBy || defaultSortBy);
    const sortDir = ref(initialSortDir || defaultSortDir);

    // Debounce handle for search
    let debounceHandle: ReturnType<typeof setTimeout> | undefined;

    /**
     * Build URL parameters for current table state
     */
    function buildParams(extra: Record<string, any> = {}): Record<string, any> {
        return {
            ...preserveParams,
            [`${resourceName}_search`]: searchQuery.value || undefined,
            [`${resourceName}_sort_by`]: sortBy.value,
            [`${resourceName}_sort_dir`]: sortDir.value,
            ...extra,
        };
    }

    /**
     * Apply current filters/sort to URL and reload data
     */
    function applyFilters(extra: Record<string, any> = {}) {
        const params = buildParams(extra);
        router.get(currentUrl, params, {
            preserveScroll: true,
            preserveState: true,
        });
    }

    /**
     * Toggle sort direction or change sort column
     */
    function toggleSort(column: string) {
        if (sortBy.value === column) {
            // Same column - toggle direction
            sortDir.value = sortDir.value === 'asc' ? 'desc' : 'asc';
        } else {
            // New column - default to ascending
            sortBy.value = column;
            sortDir.value = 'asc';
        }
        applyFilters();
    }

    /**
     * Reset to default state
     */
    function reset() {
        searchQuery.value = '';
        sortBy.value = defaultSortBy;
        sortDir.value = defaultSortDir;
        applyFilters();
    }

    // Watch search query with debounce
    watch(searchQuery, () => {
        if (debounceHandle) clearTimeout(debounceHandle);
        debounceHandle = setTimeout(() => {
            applyFilters();
        }, debounceMs);
    });

    return {
        // State
        searchQuery,
        sortBy,
        sortDir,
        
        // Methods
        toggleSort,
        applyFilters,
        buildParams,
        reset,
    };
}
