import { ref, computed } from 'vue';

/**
 * Composable for managing product comparison
 */
export function useProductComparison(maxProducts = 3) {
    const comparisonProducts = ref([]);
    const showComparison = ref(false);

    /**
     * Check if product is in comparison
     */
    const isInComparison = (product) => {
        return comparisonProducts.value.some(p => p.id === product.id);
    };

    /**
     * Can add more products to comparison
     */
    const canAddMore = computed(() => {
        return comparisonProducts.value.length < maxProducts;
    });

    /**
     * Add product to comparison
     */
    const addToComparison = (product) => {
        if (!product) return false;
        
        if (isInComparison(product)) {
            return false;
        }

        if (!canAddMore.value) {
            return false;
        }

        comparisonProducts.value.push(product);
        return true;
    };

    /**
     * Remove product from comparison
     */
    const removeFromComparison = (product) => {
        const index = comparisonProducts.value.findIndex(p => p.id === product.id);
        if (index > -1) {
            comparisonProducts.value.splice(index, 1);
            return true;
        }
        return false;
    };

    /**
     * Toggle product in comparison
     */
    const toggleComparison = (product) => {
        if (isInComparison(product)) {
            removeFromComparison(product);
        } else {
            if (!addToComparison(product)) {
                return false; // Could not add (limit reached)
            }
        }
        return true;
    };

    /**
     * Clear all products from comparison
     */
    const clearComparison = () => {
        comparisonProducts.value = [];
    };

    /**
     * Open comparison dialog
     */
    const openComparison = () => {
        showComparison.value = true;
    };

    /**
     * Close comparison dialog
     */
    const closeComparison = () => {
        showComparison.value = false;
    };

    /**
     * Get all unique specification keys from compared products
     */
    const getComparisonSpecs = () => {
        const allSpecs = new Set();
        comparisonProducts.value.forEach(product => {
            if (product.specifications) {
                Object.keys(product.specifications).forEach(key => allSpecs.add(key));
            }
        });
        
        return Array.from(allSpecs).map(key => ({
            key,
            label: key.charAt(0).toUpperCase() + key.slice(1).replace(/_/g, ' ')
        }));
    };

    /**
     * Get specification value for a product
     */
    const getSpecValue = (product, key) => {
        if (product.specifications && product.specifications[key]) {
            return product.specifications[key];
        }
        return 'N/A';
    };

    /**
     * Get key features for a product
     */
    const getKeyFeatures = (product) => {
        if (product.key_features && Array.isArray(product.key_features)) {
            return product.key_features;
        }
        // Default features
        return ['High Performance', 'Reliable Design', 'Energy Efficient'];
    };

    /**
     * Get recommended use for a product
     */
    const getRecommendedUse = (product) => {
        if (product.recommended_use) return product.recommended_use;
        
        // Default based on category
        const categoryName = product.categories?.[0]?.name?.toLowerCase() || '';
        if (categoryName.includes('ups')) return 'Data Centers, Servers';
        if (categoryName.includes('battery')) return 'Backup Power Systems';
        if (categoryName.includes('solar')) return 'Renewable Energy Systems';
        return 'General Purpose';
    };

    return {
        // State
        comparisonProducts,
        showComparison,
        maxProducts,
        
        // Computed
        canAddMore,
        
        // Methods
        isInComparison,
        addToComparison,
        removeFromComparison,
        toggleComparison,
        clearComparison,
        openComparison,
        closeComparison,
        getComparisonSpecs,
        getSpecValue,
        getKeyFeatures,
        getRecommendedUse
    };
}










