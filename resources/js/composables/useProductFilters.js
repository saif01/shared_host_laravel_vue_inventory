import { ref, computed, watch } from 'vue';

/**
 * Composable for managing product filters and sorting
 */
export function useProductFilters(products) {
    const activeCategory = ref('all');
    const searchQuery = ref('');
    const sortBy = ref('newest');

    /**
     * Available sort options
     */
    const sortOptions = [
        { label: 'Newest First', value: 'newest' },
        { label: 'Price: Low to High', value: 'price_asc' },
        { label: 'Price: High to Low', value: 'price_desc' },
        { label: 'Name: A to Z', value: 'name_asc' },
        { label: 'Name: Z to A', value: 'name_desc' },
        { label: 'Featured First', value: 'featured' }
    ];

    /**
     * Get current sort label
     */
    const sortByLabel = computed(() => {
        const option = sortOptions.find(o => o.value === sortBy.value);
        return option ? option.label : 'Newest First';
    });

    /**
     * Filter products by category
     */
    const filterByCategory = (productsList) => {
        if (activeCategory.value === 'all') {
            return productsList;
        }
        
        return productsList.filter(p => {
            const categoryIds = p.categories?.map(c => c.id) || [];
            return categoryIds.includes(activeCategory.value);
        });
    };

    /**
     * Filter products by search query
     */
    const filterBySearch = (productsList) => {
        if (!searchQuery.value) {
            return productsList;
        }

        const query = searchQuery.value.toLowerCase().trim();
        return productsList.filter(p =>
            p.title?.toLowerCase().includes(query) ||
            p.short_description?.toLowerCase().includes(query) ||
            p.sku?.toLowerCase().includes(query) ||
            p.description?.toLowerCase().includes(query)
        );
    };

    /**
     * Sort products
     */
    const sortProducts = (productsList) => {
        const sorted = [...productsList];
        
        sorted.sort((a, b) => {
            switch (sortBy.value) {
                case 'price_asc':
                    return (parseFloat(a.price) || 0) - (parseFloat(b.price) || 0);
                case 'price_desc':
                    return (parseFloat(b.price) || 0) - (parseFloat(a.price) || 0);
                case 'name_asc':
                    return (a.title || '').localeCompare(b.title || '');
                case 'name_desc':
                    return (b.title || '').localeCompare(a.title || '');
                case 'featured':
                    if (a.featured && !b.featured) return -1;
                    if (!a.featured && b.featured) return 1;
                    return 0;
                case 'newest':
                default:
                    return new Date(b.created_at || 0) - new Date(a.created_at || 0);
            }
        });

        return sorted;
    };

    /**
     * Filtered and sorted products
     */
    const filteredProducts = computed(() => {
        let result = products.value || [];
        
        // Apply filters
        result = filterByCategory(result);
        result = filterBySearch(result);
        
        // Apply sorting
        result = sortProducts(result);
        
        return result;
    });

    /**
     * Check if any filters are active
     */
    const hasActiveFilters = computed(() => {
        return activeCategory.value !== 'all' || 
               searchQuery.value !== '' || 
               sortBy.value !== 'newest';
    });

    /**
     * Count active filters
     */
    const activeFiltersCount = computed(() => {
        let count = 0;
        if (activeCategory.value !== 'all') count++;
        if (searchQuery.value !== '') count++;
        if (sortBy.value !== 'newest') count++;
        return count;
    });

    /**
     * Set active category
     */
    const setActiveCategory = (categoryId) => {
        activeCategory.value = categoryId;
    };

    /**
     * Set sort order
     */
    const setSortBy = (sortValue) => {
        sortBy.value = sortValue;
    };

    /**
     * Set search query
     */
    const setSearchQuery = (query) => {
        searchQuery.value = query;
    };

    /**
     * Clear all filters
     */
    const clearFilters = () => {
        activeCategory.value = 'all';
        searchQuery.value = '';
        sortBy.value = 'newest';
    };

    return {
        // State
        activeCategory,
        searchQuery,
        sortBy,
        sortOptions,
        
        // Computed
        filteredProducts,
        sortByLabel,
        hasActiveFilters,
        activeFiltersCount,
        
        // Methods
        setActiveCategory,
        setSortBy,
        setSearchQuery,
        clearFilters
    };
}










