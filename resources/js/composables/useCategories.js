import { ref, computed } from 'vue';

/**
 * Composable for managing product categories
 */
export function useCategories() {
    const categories = ref([]);
    const loading = ref(false);
    const error = ref(null);

    /**
     * Default categories as fallback
     */
    const defaultCategories = [
        { id: 'all', name: 'All Products', icon: 'mdi-view-grid' },
        { id: 'ups', name: 'UPS Systems', icon: 'mdi-power' },
        { id: 'batteries', name: 'Batteries', icon: 'mdi-battery' },
        { id: 'inverters', name: 'Inverters', icon: 'mdi-flash' },
        { id: 'solar', name: 'Solar', icon: 'mdi-solar-power' },
        { id: 'accessories', name: 'Accessories', icon: 'mdi-package-variant' }
    ];

    /**
     * Icon mapping for categories
     */
    const iconMap = {
        'ups': 'mdi-power',
        'batteries': 'mdi-battery',
        'inverters': 'mdi-flash',
        'solar': 'mdi-solar-power',
        'accessories': 'mdi-package-variant',
        'power': 'mdi-lightning-bolt',
        'energy': 'mdi-flash-circle'
    };

    /**
     * Get icon for category
     */
    const getCategoryIcon = (slug) => {
        const normalizedSlug = slug?.toLowerCase() || '';
        return iconMap[normalizedSlug] || 'mdi-package-variant';
    };

    /**
     * Fetch categories from API
     */
    const fetchCategories = async () => {
        loading.value = true;
        error.value = null;
        
        try {
            const response = await window.axios.get('/api/openapi/categories?type=product');
            
            // Handle different response structures
            const categoriesData = Array.isArray(response.data)
                ? response.data
                : (response.data.data || response.data.categories || []);

            if (Array.isArray(categoriesData) && categoriesData.length > 0) {
                categories.value = [
                    { id: 'all', name: 'All Products', icon: 'mdi-view-grid' },
                    ...categoriesData.map(c => ({
                        id: c.id,
                        name: c.name,
                        slug: c.slug,
                        icon: getCategoryIcon(c.slug || c.name)
                    }))
                ];
            } else {
                // Use default categories if API returns empty
                categories.value = defaultCategories;
            }
            
            return categories.value;
        } catch (err) {
            console.error('Error loading categories:', err);
            error.value = err.message || 'Failed to load categories';
            categories.value = defaultCategories;
            return categories.value;
        } finally {
            loading.value = false;
        }
    };

    /**
     * Get category by ID
     */
    const getCategoryById = (id) => {
        return categories.value.find(c => c.id === id) || null;
    };

    /**
     * Get category name by ID
     */
    const getCategoryNameById = (id) => {
        const category = getCategoryById(id);
        return category ? category.name : 'Unknown';
    };

    return {
        categories,
        loading,
        error,
        fetchCategories,
        getCategoryById,
        getCategoryNameById,
        getCategoryIcon
    };
}










