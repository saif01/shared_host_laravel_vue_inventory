<template>
    <v-dialog v-model="dialogModel" max-width="1000" scrollable persistent>
        <v-card>
            <v-card-title>
                {{ editingProduct ? 'Edit Product' : 'Add New Product' }}
            </v-card-title>
            <v-card-text class="pa-0">
                <v-form ref="form" @submit.prevent="onSave">
                    <v-tabs v-model="activeTab" bg-color="grey-lighten-4">
                        <v-tab value="basic">Basic Info</v-tab>
                        <v-tab value="details">Additional Details</v-tab>
                        <v-tab value="pricing">Pricing & Tax</v-tab>
                        <v-tab value="stock">Stock & Tracking</v-tab>
                    </v-tabs>

                    <v-window v-model="activeTab">
                        <!-- Basic Information Tab -->
                        <v-window-item value="basic">
                            <div class="pa-6">
                                <v-text-field v-model="form.name" label="Product Name *" :rules="[rules.required]"
                                    required class="mb-4"></v-text-field>

                                <v-row>
                                    <v-col cols="12" md="6">
                                        <v-text-field v-model="form.sku" label="SKU *" :rules="[rules.required]" required
                                            hint="Stock Keeping Unit" persistent-hint></v-text-field>
                                    </v-col>
                                    <v-col cols="12" md="6">
                                        <v-text-field v-model="form.barcode" label="Barcode"
                                            hint="Optional barcode/QR code" persistent-hint></v-text-field>
                                    </v-col>
                                </v-row>

                                <v-row>
                                    <v-col cols="12" md="6">
                                        <v-select v-model="form.category_id" :items="categories" item-title="label"
                                            item-value="value" label="Category *" :rules="[rules.required]" required
                                            class="mb-4"></v-select>
                                    </v-col>
                                    <v-col cols="12" md="6">
                                        <v-select v-model="form.unit_id" :items="units" item-title="label"
                                            item-value="value" label="Unit of Measure *" :rules="[rules.required]"
                                            required class="mb-4"></v-select>
                                    </v-col>
                                </v-row>

                                <v-textarea v-model="form.description" label="Description" variant="outlined" rows="3"
                                    hint="Product description" persistent-hint class="mb-4"></v-textarea>

                                <!-- Image Upload Section -->
                                <div class="mb-4">
                                    <div class="text-subtitle-2 font-weight-medium mb-2">Product Image</div>

                                    <!-- Image Preview -->
                                    <div v-if="form.image" class="mb-3 text-center">
                                        <v-avatar size="120" class="mb-2">
                                            <v-img :src="form.image ? resolveImageUrl(form.image) : ''"
                                                alt="Product Preview"></v-img>
                                        </v-avatar>
                                        <div>
                                            <v-btn size="small" variant="text" color="error" prepend-icon="mdi-delete"
                                                @click="clearImage">Remove Image</v-btn>
                                        </div>
                                    </div>

                                    <!-- File Upload -->
                                    <v-file-input v-model="imageFile" label="Upload Image" variant="outlined"
                                        density="comfortable" color="primary" accept="image/*" prepend-icon="mdi-image"
                                        hint="Upload a product image (JPG, PNG, GIF, WebP - Max 5MB). Recommended size: 400x400px"
                                        persistent-hint show-size @update:model-value="handleImageUpload">
                                        <template v-slot:append-inner v-if="uploadingImage">
                                            <v-progress-circular indeterminate size="20"
                                                color="primary"></v-progress-circular>
                                        </template>
                                    </v-file-input>
                                </div>

                                <v-switch v-model="form.is_active" label="Active Product" color="success"></v-switch>
                            </div>
                        </v-window-item>

                        <!-- Additional Details Tab -->
                        <v-window-item value="details">
                            <div class="pa-6">
                                <v-row>
                                    <v-col cols="12" md="6">
                                        <v-text-field v-model="form.manufacturer" label="Manufacturer"
                                            prepend-inner-icon="mdi-factory"></v-text-field>
                                    </v-col>
                                    <v-col cols="12" md="6">
                                        <v-text-field v-model="form.brand" label="Brand"
                                            prepend-inner-icon="mdi-tag"></v-text-field>
                                    </v-col>
                                </v-row>

                                <v-text-field v-model.number="form.warranty_period" label="Warranty Period (Months)"
                                    type="number" min="0" prepend-inner-icon="mdi-shield-check"
                                    class="mb-4"></v-text-field>

                                <v-textarea v-model="form.specifications" label="Technical Specifications"
                                    variant="outlined" rows="3" hint="Technical details, features, etc."
                                    persistent-hint class="mb-4"></v-textarea>

                                <v-divider class="mb-4"></v-divider>
                                <div class="text-subtitle-2 font-weight-medium mb-3">Physical Dimensions</div>

                                <v-row>
                                    <v-col cols="12" md="6">
                                        <v-row>
                                            <v-col cols="8">
                                                <v-text-field v-model.number="form.weight" label="Weight" type="number"
                                                    step="0.01" min="0" prepend-inner-icon="mdi-weight"></v-text-field>
                                            </v-col>
                                            <v-col cols="4">
                                                <v-select v-model="form.weight_unit" :items="['kg', 'g', 'lb']"
                                                    label="Unit"></v-select>
                                            </v-col>
                                        </v-row>
                                    </v-col>
                                    <v-col cols="12" md="6">
                                        <v-select v-model="form.dimensions_unit" :items="['cm', 'm', 'inch']"
                                            label="Dimensions Unit"></v-select>
                                    </v-col>
                                </v-row>

                                <v-row>
                                    <v-col cols="12" md="4">
                                        <v-text-field v-model.number="form.dimensions_length" label="Length" type="number"
                                            step="0.01" min="0" prepend-inner-icon="mdi-ruler"></v-text-field>
                                    </v-col>
                                    <v-col cols="12" md="4">
                                        <v-text-field v-model.number="form.dimensions_width" label="Width" type="number"
                                            step="0.01" min="0" prepend-inner-icon="mdi-ruler"></v-text-field>
                                    </v-col>
                                    <v-col cols="12" md="4">
                                        <v-text-field v-model.number="form.dimensions_height" label="Height" type="number"
                                            step="0.01" min="0" prepend-inner-icon="mdi-ruler"></v-text-field>
                                    </v-col>
                                </v-row>
                            </div>
                        </v-window-item>

                        <!-- Pricing & Tax Tab -->
                        <v-window-item value="pricing">
                            <div class="pa-6">
                                <v-row>
                                    <v-col cols="12" md="6">
                                        <v-text-field v-model.number="form.cost_price" label="Cost Price *" type="number"
                                            step="0.01" min="0" :rules="[rules.required, rules.minValue]" required
                                            prefix="৳" prepend-inner-icon="mdi-cash-minus" class="mb-4"></v-text-field>
                                    </v-col>
                                    <v-col cols="12" md="6">
                                        <v-text-field v-model.number="form.selling_price" label="Selling Price *"
                                            type="number" step="0.01" min="0" :rules="[rules.required, rules.minValue]"
                                            required prefix="৳" prepend-inner-icon="mdi-cash-plus"
                                            class="mb-4"></v-text-field>
                                    </v-col>
                                </v-row>

                                <v-divider class="mb-4"></v-divider>
                                <div class="text-subtitle-2 font-weight-medium mb-3">Tax & Discount</div>

                                <v-row>
                                    <v-col cols="12" md="4">
                                        <v-text-field v-model.number="form.tax_rate" label="Tax Rate (%)" type="number"
                                            step="0.01" min="0" max="100" suffix="%"
                                            prepend-inner-icon="mdi-receipt-text"></v-text-field>
                                    </v-col>
                                    <v-col cols="12" md="4">
                                        <v-text-field v-model.number="form.discount_percentage" label="Discount (%)"
                                            type="number" step="0.01" min="0" max="100" suffix="%"
                                            prepend-inner-icon="mdi-percent"></v-text-field>
                                    </v-col>
                                    <v-col cols="12" md="4">
                                        <v-switch v-model="form.is_taxable" label="Taxable Product" color="primary"
                                            class="mt-2"></v-switch>
                                    </v-col>
                                </v-row>

                                <!-- Price Calculation Preview -->
                                <v-alert type="info" variant="tonal" class="mt-4">
                                    <div class="text-subtitle-2 mb-2">Price Breakdown:</div>
                                    <div><strong>Base Price:</strong> ৳{{ formatPrice(form.selling_price) }}</div>
                                    <div v-if="form.discount_percentage > 0">
                                        <strong>Discount ({{ form.discount_percentage }}%):</strong> -৳{{
                                            formatPrice(calculateDiscount) }}
                                    </div>
                                    <div v-if="form.is_taxable && form.tax_rate > 0">
                                        <strong>Tax ({{ form.tax_rate }}%):</strong> +৳{{ formatPrice(calculateTax) }}
                                    </div>
                                    <v-divider class="my-2"></v-divider>
                                    <div class="text-h6"><strong>Final Price:</strong> ৳{{ formatPrice(calculateFinalPrice)
                                        }}</div>
                                </v-alert>
                            </div>
                        </v-window-item>

                        <!-- Stock & Tracking Tab -->
                        <v-window-item value="stock">
                            <div class="pa-6">
                                <div class="text-subtitle-2 font-weight-medium mb-3">Stock Settings</div>

                                <v-row>
                                    <v-col cols="12" md="6">
                                        <v-text-field v-model.number="form.minimum_stock_level" label="Minimum Stock Level"
                                            type="number" min="0" prepend-inner-icon="mdi-package-variant"
                                            hint="Alert when stock falls below this level" persistent-hint
                                            class="mb-4"></v-text-field>
                                    </v-col>
                                    <v-col cols="12" md="6">
                                        <v-switch v-model="form.low_stock_alert" label="Enable Low Stock Alert"
                                            color="warning"></v-switch>
                                    </v-col>
                                </v-row>

                                <v-row>
                                    <v-col cols="12" md="6">
                                        <v-text-field v-model.number="form.expiry_alert_days" label="Expiry Alert (Days)"
                                            type="number" min="1" max="365" prepend-inner-icon="mdi-calendar-alert"
                                            hint="Alert X days before expiry" persistent-hint></v-text-field>
                                    </v-col>
                                    <v-col cols="12" md="6">
                                        <v-switch v-model="form.expiry_alert" label="Enable Expiry Alert"
                                            color="warning"></v-switch>
                                    </v-col>
                                </v-row>

                                <v-divider class="my-4"></v-divider>
                                <div class="text-subtitle-2 font-weight-medium mb-3">Tracking Options</div>

                                <v-row>
                                    <v-col cols="12" md="6">
                                        <v-switch v-model="form.track_serial" label="Track Serial Numbers" color="primary"
                                            hint="For electronics, phones, laptops" persistent-hint></v-switch>
                                    </v-col>
                                    <v-col cols="12" md="6">
                                        <v-switch v-model="form.track_imei" label="Track IMEI Numbers" color="primary"
                                            hint="For mobile phones" persistent-hint></v-switch>
                                    </v-col>
                                </v-row>

                                <v-row>
                                    <v-col cols="12" md="6">
                                        <v-switch v-model="form.track_batch" label="Track Batches/Lot Numbers"
                                            color="primary" hint="For perishable items, food, medicine"
                                            persistent-hint></v-switch>
                                    </v-col>
                                    <v-col cols="12" md="6">
                                        <v-switch v-model="form.has_variations" label="Has Product Variations"
                                            color="primary" hint="Size, Color, Material variations"
                                            persistent-hint></v-switch>
                                    </v-col>
                                </v-row>

                                <v-alert type="info" variant="tonal" class="mt-4">
                                    <div class="text-body-2">
                                        <strong>Tracking Tips:</strong>
                                        <ul class="ml-4 mt-2">
                                            <li><strong>Serial/IMEI:</strong> Each unit gets a unique number (electronics)
                                            </li>
                                            <li><strong>Batch Tracking:</strong> Group items by manufacturing date & expiry
                                                (food, medicine)</li>
                                            <li><strong>Variations:</strong> Same product in different sizes/colors (clothing)
                                            </li>
                                        </ul>
                                    </div>
                                </v-alert>
                            </div>
                        </v-window-item>
                    </v-window>
                </v-form>
            </v-card-text>
            <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn @click="onCancel" variant="text">Cancel</v-btn>
                <v-btn @click="onSave" color="primary" :loading="saving">
                    {{ editingProduct ? 'Update Product' : 'Create Product' }}
                </v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>

<script>
import { normalizeUploadPath, resolveUploadUrl } from '../../../utils/uploads';

export default {
    name: 'ProductDialog',
    props: {
        modelValue: {
            type: Boolean,
            default: false
        },
        product: {
            type: Object,
            default: null
        },
        categories: {
            type: Array,
            default: () => []
        },
        units: {
            type: Array,
            default: () => []
        }
    },
    emits: ['update:modelValue', 'save', 'cancel'],
    data() {
        return {
            activeTab: 'basic',
            saving: false,
            form: {
                name: '',
                sku: '',
                barcode: '',
                category_id: null,
                sub_category_id: null,
                unit_id: null,
                description: '',
                manufacturer: '',
                brand: '',
                specifications: '',
                warranty_period: null,
                weight: null,
                weight_unit: 'kg',
                dimensions_length: null,
                dimensions_width: null,
                dimensions_height: null,
                dimensions_unit: 'cm',
                image: '',
                cost_price: 0,
                selling_price: 0,
                tax_rate: 0,
                is_taxable: true,
                discount_percentage: 0,
                minimum_stock_level: 0,
                low_stock_alert: true,
                expiry_alert: false,
                expiry_alert_days: 30,
                track_serial: false,
                track_batch: false,
                track_imei: false,
                has_variations: false,
                is_active: true,
            },
            rules: {
                required: value => {
                    if (typeof value === 'number') {
                        return value >= 0 || 'This field is required';
                    }
                    return !!value || 'This field is required';
                },
                minValue: value => {
                    if (value === null || value === undefined || value === '') {
                        return true;
                    }
                    return value >= 0 || 'Value must be greater than or equal to 0';
                },
            },
            imageFile: null,
            uploadingImage: false,
        };
    },
    computed: {
        dialogModel: {
            get() {
                return this.modelValue;
            },
            set(value) {
                this.$emit('update:modelValue', value);
            }
        },
        editingProduct() {
            return this.product !== null;
        },
        calculateDiscount() {
            return (this.form.selling_price * this.form.discount_percentage) / 100;
        },
        calculateTax() {
            const priceAfterDiscount = this.form.selling_price - this.calculateDiscount;
            return this.form.is_taxable ? (priceAfterDiscount * this.form.tax_rate) / 100 : 0;
        },
        calculateFinalPrice() {
            return this.form.selling_price - this.calculateDiscount + this.calculateTax;
        }
    },
    watch: {
        product: {
            handler(newProduct) {
                this.activeTab = 'basic';
                this.imageFile = null;

                if (newProduct) {
                    const imagePath = this.normalizeImageInput(newProduct.image || '');
                    this.form = {
                        name: newProduct.name || '',
                        sku: newProduct.sku || '',
                        barcode: newProduct.barcode || '',
                        category_id: newProduct.category_id || null,
                        sub_category_id: newProduct.sub_category_id || null,
                        unit_id: newProduct.unit_id || null,
                        description: newProduct.description || '',
                        manufacturer: newProduct.manufacturer || '',
                        brand: newProduct.brand || '',
                        specifications: newProduct.specifications || '',
                        warranty_period: newProduct.warranty_period || null,
                        weight: newProduct.weight || null,
                        weight_unit: newProduct.weight_unit || 'kg',
                        dimensions_length: newProduct.dimensions_length || null,
                        dimensions_width: newProduct.dimensions_width || null,
                        dimensions_height: newProduct.dimensions_height || null,
                        dimensions_unit: newProduct.dimensions_unit || 'cm',
                        image: imagePath,
                        cost_price: newProduct.cost_price || 0,
                        selling_price: newProduct.selling_price || 0,
                        tax_rate: newProduct.tax_rate || 0,
                        is_taxable: newProduct.is_taxable !== undefined ? newProduct.is_taxable : true,
                        discount_percentage: newProduct.discount_percentage || 0,
                        minimum_stock_level: newProduct.minimum_stock_level || 0,
                        low_stock_alert: newProduct.low_stock_alert !== undefined ? newProduct.low_stock_alert : true,
                        expiry_alert: newProduct.expiry_alert || false,
                        expiry_alert_days: newProduct.expiry_alert_days || 30,
                        track_serial: newProduct.track_serial || false,
                        track_batch: newProduct.track_batch || false,
                        track_imei: newProduct.track_imei || false,
                        has_variations: newProduct.has_variations || false,
                        is_active: newProduct.is_active !== undefined ? newProduct.is_active : true,
                    };
                } else {
                    this.resetForm();
                }
            },
            immediate: true
        }
    },
    methods: {
        resetForm() {
            this.form = {
                name: '',
                sku: '',
                barcode: '',
                category_id: this.categories.length > 0 ? this.categories[0].value : null,
                sub_category_id: null,
                unit_id: this.units.length > 0 ? this.units[0].value : null,
                description: '',
                manufacturer: '',
                brand: '',
                specifications: '',
                warranty_period: null,
                weight: null,
                weight_unit: 'kg',
                dimensions_length: null,
                dimensions_width: null,
                dimensions_height: null,
                dimensions_unit: 'cm',
                image: '',
                cost_price: 0,
                selling_price: 0,
                tax_rate: 0,
                is_taxable: true,
                discount_percentage: 0,
                minimum_stock_level: 0,
                low_stock_alert: true,
                expiry_alert: false,
                expiry_alert_days: 30,
                track_serial: false,
                track_batch: false,
                track_imei: false,
                has_variations: false,
                is_active: true,
            };
            this.imageFile = null;
            if (this.$refs.form) {
                this.$refs.form.resetValidation();
            }
        },
        async handleImageUpload() {
            if (!this.imageFile) {
                return;
            }

            const fileToUpload = Array.isArray(this.imageFile) ? this.imageFile[0] : this.imageFile;
            if (!fileToUpload) {
                return;
            }

            if (!fileToUpload.type.startsWith('image/')) {
                this.showError('Please select a valid image file');
                this.imageFile = null;
                return;
            }

            const maxSize = 5 * 1024 * 1024;
            if (fileToUpload.size > maxSize) {
                this.showError('File size must be less than 5MB');
                this.imageFile = null;
                return;
            }

            this.uploadingImage = true;
            try {
                const formData = new FormData();
                formData.append('image', fileToUpload);
                formData.append('folder', 'products');
                if (this.form.name) {
                    formData.append('prefix', this.form.name);
                }

                const token = localStorage.getItem('admin_token');
                const response = await this.$axios.post('/api/v1/upload/image', formData, {
                    headers: {
                        Authorization: `Bearer ${token}`,
                        'Content-Type': 'multipart/form-data'
                    }
                });

                if (response.data.success) {
                    const uploadedPath = this.normalizeImageInput(response.data.path || response.data.url);
                    this.form.image = uploadedPath;
                    this.imageFile = null;
                    this.showSuccess('Image uploaded successfully');
                } else {
                    throw new Error(response.data.message || 'Failed to upload image');
                }
            } catch (error) {
                console.error('Error uploading image:', error);
                let errorMessage = 'Failed to upload image';
                if (error.response) {
                    errorMessage = error.response.data?.message || error.response.statusText || errorMessage;
                } else if (error.message) {
                    errorMessage = error.message;
                }
                this.showError(errorMessage);
                this.imageFile = null;
            } finally {
                this.uploadingImage = false;
            }
        },
        clearImage() {
            this.form.image = '';
            this.imageFile = null;
        },
        async onSave() {
            if (!this.$refs.form.validate()) {
                return;
            }

            this.saving = true;
            try {
                const data = { ...this.form };
                data.image = this.normalizeImageInput(data.image);

                await this.$emit('save', { data, isEditing: this.editingProduct });
            } finally {
                this.saving = false;
            }
        },
        onCancel() {
            this.resetForm();
            this.$emit('cancel');
        },
        normalizeImageInput(imageValue) {
            return normalizeUploadPath(imageValue);
        },
        resolveImageUrl(value) {
            return resolveUploadUrl(value);
        },
        formatPrice(value) {
            return new Intl.NumberFormat('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }).format(value || 0);
        },
        showError(message) {
            console.error(message);
            if (this.$root.showError) {
                this.$root.showError(message);
            }
        },
        showSuccess(message) {
            console.log(message);
            if (this.$root.showSuccess) {
                this.$root.showSuccess(message);
            }
        }
    }
};
</script>

<style scoped>
/* Add any dialog-specific styles here */
</style>
