import { ref, computed } from 'vue';

/**
 * Composable for managing products data and operations
 */
export function useProducts() {
    const products = ref([]);
    const loading = ref(false);
    const error = ref(null);

    /**
     * Fetch products from API
     */
    const fetchProducts = async () => {
        loading.value = true;
        error.value = null;
        
        try {
            const response = await window.axios.get('/api/openapi/products');
            products.value = Array.isArray(response.data) ? response.data : [];
            return products.value;
        } catch (err) {
            console.error('Error loading products:', err);
            error.value = err.message || 'Failed to load products';
            // Return mock data as fallback
            products.value = generateMockProducts();
            return products.value;
        } finally {
            loading.value = false;
        }
    };

    /**
     * Get product by slug
     */
    const getProductBySlug = (slug) => {
        return products.value.find(p => p.slug === slug) || null;
    };

    /**
     * Get product image URL
     */
    const getProductImage = (product) => {
        if (product?.thumbnail) return product.thumbnail;
        if (product?.images && product.images.length > 0) return product.images[0];
        return 'https://via.placeholder.com/400x300?text=Product';
    };

    /**
     * Get category name for product
     */
    const getCategoryName = (product) => {
        if (product?.categories && product.categories.length > 0) {
            return product.categories[0].name;
        }
        return 'Uncategorized';
    };

    /**
     * Format product price
     */
    const formatPrice = (product) => {
        if (product?.price_range) return product.price_range;
        if (product?.price) return `$${parseFloat(product.price).toFixed(2)}`;
        return 'Contact for Price';
    };

    /**
     * Get quick specs for product card
     */
    const getQuickSpecs = (product) => {
        const specs = [];
        if (product?.specifications) {
            if (product.specifications.capacity) {
                specs.push({ label: 'Capacity', value: product.specifications.capacity });
            }
            if (product.specifications.input) {
                specs.push({ label: 'Input', value: product.specifications.input });
            }
            if (product.specifications.type) {
                specs.push({ label: 'Type', value: product.specifications.type });
            }
        }
        return specs.slice(0, 3);
    };

    /**
     * Generate mock products for fallback
     */
    const generateMockProducts = () => {
        return Array.from({ length: 12 }).map((_, i) => ({
            id: i + 1,
            title: `Industrial Power Unit ${i + 100}`,
            slug: `product-${i + 1}`,
            sku: `MCT-${String(i + 1).padStart(4, '0')}`,
            short_description: 'High-performance power solution designed for reliability and efficiency.',
            price: (Math.random() * 1000 + 200).toFixed(2),
            featured: i % 3 === 0,
            rating: (4 + Math.random()).toFixed(1),
            categories: [
                { 
                    id: i % 2 === 0 ? 'ups' : 'batteries', 
                    name: i % 2 === 0 ? 'UPS Systems' : 'Batteries' 
                }
            ],
            specifications: {
                capacity: `${500 + i * 100}VA`,
                input: '230V AC',
                type: 'Online Double Conversion'
            },
            created_at: new Date(Date.now() - i * 86400000).toISOString()
        }));
    };

    return {
        products,
        loading,
        error,
        fetchProducts,
        getProductBySlug,
        getProductImage,
        getCategoryName,
        formatPrice,
        getQuickSpecs
    };
}



