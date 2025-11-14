import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

interface BreadcrumbItem {
    title: string;
    href?: string;
}

const resourceLabels: Record<string, { singular: string; plural: string }> = {
    properties: { singular: 'Property', plural: 'Properties' },
    accommodations: { singular: 'Accommodation', plural: 'Accommodations' },
    stays: { singular: 'Stay', plural: 'Stays' },
    tenants: { singular: 'Tenant', plural: 'Tenants' },
    expenses: { singular: 'Expense', plural: 'Expenses' },
    'stay-categories': { singular: 'Stay Category', plural: 'Stay Categories' },
    'expense-categories': { singular: 'Expense Category', plural: 'Expense Categories' },
    settings: { singular: 'Settings', plural: 'Settings' },
    trash: { singular: 'Trash', plural: 'Trash' },
};

export function useBreadcrumbs(customBreadcrumbs?: BreadcrumbItem[]) {
    const page = usePage();

    const breadcrumbs = computed<BreadcrumbItem[]>(() => {
        if (customBreadcrumbs) {
            return customBreadcrumbs;
        }

    // Remove query string so breadcrumbs don't show params (e.g., ?sort_by=...)
    const url = page.url;
    const pathOnly = url.split('?')[0];
    const segments = pathOnly.split('/').filter(Boolean);
        const props = page.props as any;
        
        const items: BreadcrumbItem[] = [];

        let currentPath = '';

        for (let i = 0; i < segments.length; i++) {
            const segment = segments[i];
            currentPath += `/${segment}`;

            // Skip numeric IDs in breadcrumbs - get label from props
            if (/^\d+$/.test(segment)) {
                const resourceKey = segments[i - 1];
                
                // Try singular form first (e.g., 'property' for /properties/1)
                const singularKey = resourceKey?.replace(/ies$/, 'y').replace(/s$/, '');
                
                // Try to find the resource in props
                let label = segment;
                const resource = props[singularKey] || props[resourceKey];
                
                if (resource) {
                    label = resource.label || resource.name || resource.title || segment;
                }

                items.push({
                    title: label,
                    href: currentPath,
                });
                continue;
            }

            // Handle known resources
            const resourceInfo = resourceLabels[segment];
            if (resourceInfo) {
                items.push({
                    title: resourceInfo.plural,
                    href: currentPath,
                });
                continue;
            }

            // Handle settings sub-pages
            if (segments[i - 1] === 'settings') {
                items.push({
                    title: segment.charAt(0).toUpperCase() + segment.slice(1),
                    href: currentPath,
                });
                continue;
            }

            // Capitalize and add unknown segments
            const title = segment
                .split('-')
                .map(word => word.charAt(0).toUpperCase() + word.slice(1))
                .join(' ');
            
            items.push({
                title,
                href: currentPath,
            });
        }

        return items;
    });

    return { breadcrumbs };
}
