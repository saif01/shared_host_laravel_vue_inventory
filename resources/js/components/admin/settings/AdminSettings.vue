<template>
    <div class="settings-page">
        <!-- Header Section -->
        <div class="settings-header mb-8">
            <div class="header-content">
                <div class="header-icon-wrapper">
                    <v-icon icon="mdi-cog" size="48" class="header-icon"></v-icon>
                </div>
                <div class="header-text">
                    <h1 class="text-h4 font-weight-bold mb-2">Settings</h1>
                    <p class="text-body-1 text-medium-emphasis">Manage your application preferences and configurations
                    </p>
                </div>
            </div>
            <v-btn color="primary" size="large" prepend-icon="mdi-content-save" :loading="loading" @click="saveSettings"
                elevation="2" class="save-btn">
                Save Changes
            </v-btn>
        </div>

        <v-form @submit.prevent="saveSettings">
            <v-row>
                <!-- General & Footer Settings Card (Combined) -->
                <v-col cols="12">
                    <v-card class="settings-card" elevation="2" rounded="xl">
                        <v-card-item class="card-header compact-header">
                            <div class="d-flex align-center">
                                <v-avatar size="36" color="primary" class="mr-3">
                                    <v-icon icon="mdi-cog" color="white" size="20"></v-icon>
                                </v-avatar>
                                <div>
                                    <v-card-title class="pa-0 text-subtitle-1 font-weight-bold">General & Footer
                                        Settings</v-card-title>
                                    <v-card-subtitle class="pa-0 text-caption">Basic information and footer
                                        customization</v-card-subtitle>
                                </div>
                            </div>
                        </v-card-item>
                        <v-divider></v-divider>
                        <v-card-text class="pa-4">
                            <v-row dense>
                                <!-- General Settings -->
                                <v-col cols="12">
                                    <div class="section-label mb-2">
                                        <v-icon icon="mdi-information" size="16" class="mr-1"></v-icon>
                                        <span class="text-caption font-weight-bold text-uppercase">General</span>
                                    </div>
                                    <v-text-field v-model="settings.general.site_name.value" label="Site Name"
                                        variant="outlined" density="compact" color="primary"
                                        prepend-inner-icon="mdi-web" hint="The name of your website" persistent-hint
                                        hide-details="auto"></v-text-field>
                                </v-col>

                                <!-- Footer Settings -->
                                <v-col cols="12">
                                    <div class="section-label mb-2 mt-2">
                                        <v-icon icon="mdi-text-box" size="16" class="mr-1"></v-icon>
                                        <span class="text-caption font-weight-bold text-uppercase">Footer</span>
                                    </div>
                                </v-col>
                                <v-col cols="12" md="6">
                                    <v-text-field v-model="settings.footer.powered_by_text.value"
                                        label="Powered By Text" variant="outlined" density="compact" color="primary"
                                        prepend-inner-icon="mdi-text" hint="Text displayed next to logo in footer"
                                        persistent-hint hide-details="auto"></v-text-field>
                                </v-col>
                                <v-col cols="12" md="6">
                                    <v-text-field v-model="settings.footer.version.value" label="Version"
                                        variant="outlined" density="compact" color="primary"
                                        prepend-inner-icon="mdi-tag" hint="Application version displayed in footer"
                                        persistent-hint hide-details="auto"></v-text-field>
                                </v-col>
                                <v-col cols="12">
                                    <v-text-field v-model="settings.footer.copyright_text.value" label="Copyright Text"
                                        variant="outlined" density="compact" color="primary"
                                        prepend-inner-icon="mdi-copyright"
                                        hint="Copyright text (year will be added automatically)" persistent-hint
                                        hide-details="auto"></v-text-field>
                                </v-col>
                            </v-row>
                        </v-card-text>
                    </v-card>
                </v-col>

                <!-- Branding Settings Card -->
                <v-col cols="12">
                    <v-card class="settings-card" elevation="2" rounded="xl">
                        <v-card-item class="card-header">
                            <div class="d-flex align-center">
                                <v-avatar size="40" color="purple" class="mr-4">
                                    <v-icon icon="mdi-palette" color="white"></v-icon>
                                </v-avatar>
                                <div>
                                    <v-card-title class="pa-0 text-h6 font-weight-bold">Branding</v-card-title>
                                    <v-card-subtitle class="pa-0">Customize the look and feel of your
                                        site</v-card-subtitle>
                                </div>
                            </div>
                        </v-card-item>
                        <v-divider></v-divider>
                        <v-card-text class="pa-6">
                            <v-row justify="center">
                                <v-col cols="12" md="8" lg="6">
                                    <v-card variant="outlined" class="logo-upload-card pa-6" rounded="lg">
                                        <div class="d-flex align-center mb-4">
                                            <v-icon icon="mdi-image" size="24" class="mr-2" color="primary"></v-icon>
                                            <span class="text-subtitle-1 font-weight-bold">Logo</span>
                                        </div>

                                        <!-- Logo Preview -->
                                        <div v-if="settings.branding.logo.value" class="logo-preview-wrapper mb-4">
                                            <div class="logo-preview-container">
                                                <div class="logo-image-wrapper">
                                                    <div class="logo-preview-actions-top">
                                                        <v-btn size="small" variant="flat" color="error"
                                                            prepend-icon="mdi-delete" @click="clearLogo"
                                                            class="remove-logo-btn">
                                                            Remove Logo
                                                        </v-btn>
                                                    </div>
                                                    <v-img :src="logoUrl" alt="Logo Preview" :max-height="120"
                                                        :max-width="300" contain class="logo-preview-image" eager>
                                                        <template v-slot:placeholder>
                                                            <div class="d-flex align-center justify-center fill-height">
                                                                <v-progress-circular indeterminate
                                                                    color="primary"></v-progress-circular>
                                                            </div>
                                                        </template>
                                                        <template v-slot:error>
                                                            <div
                                                                class="d-flex align-center justify-center fill-height text-error">
                                                                <v-icon icon="mdi-alert-circle" class="mr-2"></v-icon>
                                                                <span>Failed to load image</span>
                                                            </div>
                                                        </template>
                                                    </v-img>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- File Upload -->
                                        <v-file-input v-model="logoFile" label="Upload Logo" variant="outlined"
                                            density="comfortable" color="primary" accept="image/*"
                                            prepend-icon="mdi-upload"
                                            hint="Upload a logo image (JPG, PNG, GIF, WebP - Max 5MB). Recommended size: 200x60px or 300x90px"
                                            persistent-hint show-size @update:model-value="handleLogoUpload"
                                            class="mb-4">
                                            <template v-slot:append-inner v-if="uploadingLogo">
                                                <v-progress-circular indeterminate size="20"
                                                    color="primary"></v-progress-circular>
                                            </template>
                                        </v-file-input>
                                    </v-card>
                                </v-col>
                            </v-row>
                        </v-card-text>
                    </v-card>
                </v-col>
                <v-card class="save-bar-card" elevation="8" rounded="lg">
                    <v-card-text class="d-flex align-center justify-space-between pa-4">
                        <div class="d-flex align-center">
                            <v-icon icon="mdi-information-outline" class="mr-2" color="primary"></v-icon>
                            <span class="text-body-2 mr-2">Make sure to save your changes</span>
                        </div>
                        <v-btn color="primary" size="large" prepend-icon="mdi-content-save" :loading="loading"
                            @click="saveSettings" elevation="2">
                            Save Changes
                        </v-btn>
                    </v-card-text>
                </v-card>
            </v-row>


        </v-form>
    </div>
</template>

<script>
import { normalizeUploadPath, resolveUploadUrl } from '../../../utils/uploads';

export default {
    data() {
        return {
            settings: {
                general: {
                    site_name: { value: '', type: 'text', group: 'general' },
                },
                branding: {
                    logo: { value: '', type: 'image', group: 'branding' },
                },
                footer: {
                    powered_by_text: { value: 'Powered By CPB-IT', type: 'text', group: 'footer' },
                    version: { value: 'v1.0', type: 'text', group: 'footer' },
                    copyright_text: { value: 'All Rights Reserved', type: 'text', group: 'footer' },
                },
            },
            loading: false,
            logoFile: null,
            uploadingLogo: false
        };
    },
    computed: {
        logoUrl() {
            if (!this.settings.branding.logo.value) {
                return null;
            }
            return this.resolveImageUrl(this.settings.branding.logo.value);
        }
    },
    async mounted() {
        await this.loadSettings();
    },
    methods: {
        async loadSettings() {
            try {
                const token = localStorage.getItem('admin_token');
                const response = await this.$axios.get('/api/v1/settings', {
                    headers: { Authorization: `Bearer ${token}` }
                });

                // Merge loaded settings with defaults
                const loadedSettings = response.data;

                // Update existing settings with loaded values
                Object.keys(loadedSettings).forEach(group => {
                    if (this.settings[group]) {
                        Object.keys(loadedSettings[group]).forEach(key => {
                            if (this.settings[group][key]) {
                                // Update value from loaded settings
                                this.settings[group][key].value = loadedSettings[group][key].value || '';
                                // Update type and group if they exist
                                if (loadedSettings[group][key].type) {
                                    this.settings[group][key].type = loadedSettings[group][key].type;
                                }
                                if (loadedSettings[group][key].group) {
                                    this.settings[group][key].group = loadedSettings[group][key].group;
                                }
                            } else {
                                // Add new setting that wasn't in defaults
                                this.settings[group][key] = {
                                    value: loadedSettings[group][key].value || '',
                                    type: loadedSettings[group][key].type || 'text',
                                    group: loadedSettings[group][key].group || group,
                                };
                            }
                        });
                    } else {
                        // Add new group that wasn't in defaults
                        this.settings[group] = {};
                        Object.keys(loadedSettings[group]).forEach(key => {
                            this.settings[group][key] = {
                                value: loadedSettings[group][key].value || '',
                                type: loadedSettings[group][key].type || 'text',
                                group: loadedSettings[group][key].group || group,
                            };
                        });
                    }
                });
            } catch (error) {
                console.error('Error loading settings:', error);
                this.showError('Failed to load settings');
            }
        },
        async saveSettings() {
            this.loading = true;
            try {
                const token = localStorage.getItem('admin_token');

                // Flatten settings for API
                const settingsToSave = {};
                Object.keys(this.settings).forEach(group => {
                    Object.keys(this.settings[group]).forEach(key => {
                        settingsToSave[key] = this.settings[group][key];
                    });
                });

                await this.$axios.post('/api/v1/settings', {
                    settings: settingsToSave
                }, {
                    headers: { Authorization: `Bearer ${token}` }
                });

                this.showSuccess('Settings saved successfully');
            } catch (error) {
                console.error('Error saving settings:', error);
                let message = 'Error saving settings';
                if (error.response && error.response.data && error.response.data.message) {
                    message = error.response.data.message;
                } else if (error.message) {
                    message = error.message;
                }
                this.showError(message);
            } finally {
                this.loading = false;
            }
        },
        showSuccess(message) {
            if (window.Toast) {
                window.Toast.fire({
                    icon: 'success',
                    title: message
                });
            } else {
                alert(message);
            }
        },
        showError(message) {
            if (window.Toast) {
                window.Toast.fire({
                    icon: 'error',
                    title: message
                });
            } else {
                alert(message);
            }
        },
        async handleLogoUpload() {
            if (!this.logoFile) {
                return;
            }

            const fileToUpload = Array.isArray(this.logoFile) ? this.logoFile[0] : this.logoFile;
            if (!fileToUpload) {
                return;
            }

            // Validate file type
            if (!fileToUpload.type.startsWith('image/')) {
                this.showError('Please select a valid image file');
                this.logoFile = null;
                return;
            }

            // Validate file size (5MB max)
            const maxSize = 5 * 1024 * 1024; // 5MB
            if (fileToUpload.size > maxSize) {
                this.showError('File size must be less than 5MB');
                this.logoFile = null;
                return;
            }

            this.uploadingLogo = true;
            try {
                const formData = new FormData();
                formData.append('image', fileToUpload);
                formData.append('folder', 'branding');

                const token = localStorage.getItem('admin_token');
                const response = await this.$axios.post('/api/v1/upload/image', formData, {
                    headers: {
                        Authorization: `Bearer ${token}`,
                        'Content-Type': 'multipart/form-data'
                    }
                });

                if (response.data.success) {
                    const uploadedPath = this.normalizeImageInput(response.data.path || response.data.url);
                    this.settings.branding.logo.value = uploadedPath;
                    this.logoFile = null;
                    this.showSuccess('Logo uploaded successfully');
                } else {
                    throw new Error(response.data.message || 'Failed to upload logo');
                }
            } catch (error) {
                console.error('Error uploading logo:', error);
                this.showError(error.response?.data?.message || error.message || 'Failed to upload logo');
                this.logoFile = null;
            } finally {
                this.uploadingLogo = false;
            }
        },
        clearLogo() {
            this.settings.branding.logo.value = '';
            this.logoFile = null;
        },
        normalizeImageInput(imageValue) {
            return normalizeUploadPath(imageValue);
        },
        resolveImageUrl(imageValue) {
            return resolveUploadUrl(imageValue);
        }
    }
};
</script>

<style scoped>
.settings-page {
    max-width: 1400px;
    margin: 0 auto;
    padding-bottom: 100px;
}

/* Header Styles */
.settings-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 24px;
}

.header-content {
    display: flex;
    align-items: center;
    gap: 20px;
}

.header-icon-wrapper {
    width: 80px;
    height: 80px;
    border-radius: 20px;
    background: linear-gradient(135deg, #1976D2 0%, #1565C0 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 8px 24px rgba(25, 118, 210, 0.3);
    position: relative;
}

.header-icon {
    color: #FFFFFF !important;
    z-index: 1;
    position: relative;
    opacity: 1 !important;
}

.header-text h1 {
    margin-bottom: 4px;
}

.save-btn {
    min-width: 160px;
}

/* Settings Card Styles */
.settings-card {
    margin-bottom: 24px;
    transition: all 0.3s ease;
    border: 1px solid rgba(0, 0, 0, 0.05);
}

.settings-card:hover {
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1) !important;
    transform: translateY(-2px);
}

.card-header {
    padding: 24px !important;
    background: linear-gradient(135deg, rgba(var(--v-theme-primary), 0.05) 0%, rgba(var(--v-theme-primary), 0.02) 100%);
}

.card-header.compact-header {
    padding: 16px 20px !important;
}

.section-label {
    display: flex;
    align-items: center;
    color: rgba(var(--v-theme-on-surface), 0.6);
    margin-top: 8px;
}

/* Logo Upload Card */
.logo-upload-card {
    background: linear-gradient(135deg, rgba(var(--v-theme-primary), 0.03) 0%, rgba(var(--v-theme-primary), 0.01) 100%);
    border: 2px dashed rgba(var(--v-theme-primary), 0.2);
    transition: all 0.3s ease;
}

.logo-upload-card:hover {
    border-color: rgba(var(--v-theme-primary), 0.4);
    background: linear-gradient(135deg, rgba(var(--v-theme-primary), 0.05) 0%, rgba(var(--v-theme-primary), 0.02) 100%);
}

.logo-preview-wrapper {
    text-align: center;
}

.logo-preview-container {
    display: inline-block;
    padding: 20px;
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    border: 1px solid rgba(0, 0, 0, 0.1);
    min-width: 200px;
    min-height: 80px;
    max-width: 100%;
}

.logo-image-wrapper {
    position: relative;
    display: inline-block;
    padding-top: 40px;
    width: 100%;
}

.logo-preview-actions-top {
    position: absolute;
    top: 0;
    left: 50%;
    transform: translateX(-50%);
    z-index: 10;
    text-align: center;
    width: 100%;
}

.remove-logo-btn {
    box-shadow: 0 2px 8px rgba(244, 67, 54, 0.3);
    transition: all 0.3s ease;
}

.remove-logo-btn:hover {
    box-shadow: 0 4px 12px rgba(244, 67, 54, 0.4);
    transform: translateY(-1px);
}

.logo-preview-image {
    border-radius: 8px;
    width: 100%;
    height: auto;
    max-width: 300px;
    max-height: 120px;
    object-fit: contain;
    display: block;
    margin: 0 auto;
}

.logo-preview-image :deep(img) {
    width: 100%;
    height: auto;
    max-width: 300px;
    max-height: 120px;
    object-fit: contain;
    display: block;
    margin: 0 auto;
}

/* Fixed Save Bar */
.fixed-save-bar {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    z-index: 1000;
    padding: 16px;
    background: linear-gradient(to top, rgba(255, 255, 255, 0.95) 0%, rgba(255, 255, 255, 0.98) 100%);
    backdrop-filter: blur(10px);
    box-shadow: 0 -4px 20px rgba(0, 0, 0, 0.1);
}

.save-bar-card {
    max-width: 1400px;
    margin: 0 auto;
    background: white;
}

/* Responsive Design */
@media (max-width: 960px) {
    .settings-header {
        flex-direction: column;
        align-items: flex-start;
    }

    .header-content {
        width: 100%;
    }

    .save-btn {
        width: 100%;
    }

    .fixed-save-bar {
        padding: 12px;
    }

    .save-bar-card :deep(.v-card-text) {
        flex-direction: column;
        gap: 16px;
    }

    .save-bar-card :deep(.v-btn) {
        width: 100%;
    }
}

@media (max-width: 600px) {
    .header-icon-wrapper {
        width: 60px;
        height: 60px;
    }

    .header-icon {
        font-size: 32px !important;
    }

    .card-header {
        padding: 16px !important;
    }

    .settings-card :deep(.v-card-text) {
        padding: 16px !important;
    }
}

/* Animation */
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.settings-card {
    animation: fadeIn 0.4s ease-out;
}

.settings-card:nth-child(1) {
    animation-delay: 0.1s;
}

.settings-card:nth-child(2) {
    animation-delay: 0.2s;
}

.settings-card:nth-child(3) {
    animation-delay: 0.3s;
}
</style>