<template>
    <div class="login-container fill-height">
        <!-- Animated Background Shapes -->
        <div class="background-shapes">
            <div class="shape shape-1"></div>
            <div class="shape shape-2"></div>
            <div class="shape shape-3"></div>
        </div>

        <v-container class="fill-height d-flex align-center justify-center" fluid>
            <v-row justify="center">
                <v-col cols="12" sm="10" md="8" lg="6" xl="4">
                    <v-card class="login-card glass-effect" elevation="24">
                        <v-row no-gutters>
                            <!-- Left Side: Welcome/Branding (Hidden on small screens) -->
                            <v-col cols="12" md="6"
                                class="d-none d-md-flex flex-column align-center justify-center branding-section pa-8">
                                <div class="brand-logo mb-6">
                                    <v-img :src="logoUrl" alt="Logo" width="120" class="drop-shadow rounded-logo"
                                        cover></v-img>
                                </div>
                                <h2 class="text-white text-h4 font-weight-bold mb-2 text-center">Welcome Back!</h2>
                                <p class="text-white text-body-1 text-center opacity-80">
                                    Sign in to access your dashboard and manage your business efficiently.
                                </p>
                            </v-col>

                            <!-- Right Side: Login Form -->
                            <v-col cols="12" md="6" class="form-section pa-8 bg-white">
                                <div class="d-flex d-md-none justify-center mb-6">
                                    <v-img :src="logoUrl" alt="Logo" width="80" class="rounded-logo" cover></v-img>
                                </div>

                                <h3 class="text-h5 font-weight-bold text-primary mb-1 text-center text-md-left">Admin
                                    Login</h3>
                                <p class="text-caption text-grey mb-8 text-center text-md-left">Please enter your
                                    credentials to continue</p>

                                <v-form @submit.prevent="handleLogin" ref="form">
                                    <v-text-field v-model="form.email" label="Email Address"
                                        placeholder="admin@example.com" type="email" variant="outlined" color="primary"
                                        prepend-inner-icon="mdi-email-outline" :rules="[rules.required, rules.email]"
                                        class="mb-2" density="comfortable"></v-text-field>

                                    <v-text-field v-model="form.password" label="Password" placeholder="••••••••"
                                        :type="showPassword ? 'text' : 'password'" variant="outlined" color="primary"
                                        prepend-inner-icon="mdi-lock-outline"
                                        :append-inner-icon="showPassword ? 'mdi-eye-off' : 'mdi-eye'"
                                        @click:append-inner="showPassword = !showPassword" :rules="[rules.required]"
                                        class="mb-6" density="comfortable"></v-text-field>

                                    <v-btn type="submit" color="primary" block size="large" :loading="loading"
                                        class="login-btn text-capitalize font-weight-bold mb-4" elevation="4">
                                        Sign In
                                        <v-icon end icon="mdi-arrow-right" class="ml-2"></v-icon>
                                    </v-btn>
                                </v-form>

                                <div class="text-center mt-4">
                                    <p class="text-caption text-grey">
                                        Protected by <span class="font-weight-bold text-primary">CPB-IT Security</span>
                                    </p>
                                </div>
                            </v-col>
                        </v-row>
                    </v-card>
                </v-col>
            </v-row>
        </v-container>
    </div>
</template>

<script>
import { useAuthStore } from '../../../stores/auth';
import { mapActions } from 'pinia';
import { resolveUploadUrl } from '../../../utils/uploads';

export default {
    data() {
        return {
            form: {
                email: '',
                password: ''
            },
            loading: false,
            showPassword: false,
            brandingLogo: null,
            rules: {
                required: v => !!v || 'This field is required',
                email: v => /.+@.+\..+/.test(v) || 'E-mail must be valid'
            }
        };
    },
    computed: {
        logoUrl() {
            if (this.brandingLogo) {
                return resolveUploadUrl(this.brandingLogo);
            }
            return null; // Return null to let v-img handle the missing image gracefully
        }
    },
    async mounted() {
        await this.loadBrandingSettings();
    },
    methods: {
        ...mapActions(useAuthStore, ['login']),
        async handleLogin() {
            const { valid } = await this.$refs.form.validate();
            if (!valid) return;

            this.loading = true;
            try {
                await this.login(this.form);
                this.$router.push({ name: 'AdminDashboard' });
            } catch (error) {
                let message = 'Login failed';
                if (error.response) {
                    if (error.response.data?.message) {
                        message = error.response.data.message;
                    } else if (error.response.data?.errors) {
                        const errors = error.response.data.errors;
                        message = Object.values(errors).flat().join(', ');
                    }
                }

                // Use SweetAlert if available, otherwise use alert
                if (window.Swal) {
                    window.Swal.fire({
                        icon: 'error',
                        title: 'Authentication Failed',
                        text: message,
                        confirmButtonColor: '#d33',
                        background: '#fff',
                        iconColor: '#d33'
                    });
                } else {
                    alert(message);
                }
            } finally {
                this.loading = false;
            }
        },
        resolveImageUrl(imageValue) {
            return resolveUploadUrl(imageValue);
        },
        async loadBrandingSettings() {
            try {
                // Use public endpoint since user is not authenticated yet
                const response = await this.$axios.get('/api/openapi/settings?group=branding');
                if (response.data && response.data.logo) {
                    this.brandingLogo = response.data.logo;
                }
            } catch (error) {
                console.error('Error loading branding settings:', error);
                // Don't show error to user, just use default logo
            }
        }
    }
};
</script>

<style scoped>
.login-container {
    background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
    position: relative;
    overflow: hidden;
    min-height: 100vh;
}

/* Animated Background Shapes */
.background-shapes .shape {
    position: absolute;
    border-radius: 50%;
    filter: blur(80px);
    opacity: 0.6;
    z-index: 0;
    animation: float 20s infinite;
}

.shape-1 {
    background: #4facfe;
    width: 400px;
    height: 400px;
    top: -100px;
    left: -100px;
    animation-delay: 0s;
}

.shape-2 {
    background: #00f2fe;
    width: 300px;
    height: 300px;
    bottom: -50px;
    right: -50px;
    animation-delay: -5s;
}

.shape-3 {
    background: #a18cd1;
    width: 200px;
    height: 200px;
    top: 40%;
    left: 40%;
    animation-delay: -10s;
}

@keyframes float {
    0% {
        transform: translate(0, 0) rotate(0deg);
    }

    33% {
        transform: translate(30px, -50px) rotate(10deg);
    }

    66% {
        transform: translate(-20px, 20px) rotate(-5deg);
    }

    100% {
        transform: translate(0, 0) rotate(0deg);
    }
}

.login-card {
    border-radius: 24px;
    overflow: hidden;
    backdrop-filter: blur(20px);
    border: 1px solid rgba(255, 255, 255, 0.3);
    z-index: 1;
}

.glass-effect {
    background: rgba(255, 255, 255, 0.8) !important;
}

.branding-section {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    position: relative;
    overflow: hidden;
}

.branding-section::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg width="20" height="20" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><circle cx="2" cy="2" r="2" fill="rgba(255,255,255,0.1)"/></svg>');
    opacity: 0.3;
}

.drop-shadow {
    filter: drop-shadow(0 4px 6px rgba(0, 0, 0, 0.2));
}

.rounded-logo {
    border-radius: 12px !important;
    overflow: hidden;
    object-fit: cover;
}

.form-section {
    position: relative;
}

.login-btn {
    border-radius: 12px;
    background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
    transition: all 0.3s ease;
}

.login-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(118, 75, 162, 0.4);
}

/* Input Field Customization */
:deep(.v-field--variant-outlined) {
    border-radius: 12px;
    background: #f8fafc;
    border-color: #e2e8f0;
}

:deep(.v-field--focused .v-field__outline) {
    --v-field-border-opacity: 1;
    color: #764ba2 !important;
}

:deep(.v-label) {
    font-size: 0.9rem;
}
</style>
