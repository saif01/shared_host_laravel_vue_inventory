<template>
    <v-dialog v-model="dialog" max-width="700" scrollable persistent>
        <v-card>
            <v-card-title class="d-flex align-center justify-space-between bg-primary text-white pa-4">
                <span class="text-h5 font-weight-bold">
                    <v-icon class="mr-2">mdi-account</v-icon>
                    User Profile
                </span>
                <v-btn icon="mdi-close" variant="text" color="white" @click="closeDialog"></v-btn>
            </v-card-title>

            <v-card-text class="pa-0">
                <!-- Loading State -->
                <div v-if="loading" class="d-flex align-center justify-center pa-12">
                    <div class="text-center">
                        <v-progress-circular indeterminate color="primary" size="64"></v-progress-circular>
                        <p class="text-body-1 text-medium-emphasis mt-4">Loading profile...</p>
                    </div>
                </div>

                <!-- Profile Content -->
                <div v-else-if="user" class="pa-6">
                    <!-- Avatar Section -->
                    <div class="text-center mb-6">
                        <v-avatar size="120" class="mb-4 elevation-4">
                            <v-img v-if="resolvedAvatar" :src="resolvedAvatar" :alt="user.name" cover></v-img>
                            <v-icon v-else size="60" color="primary">mdi-account</v-icon>
                        </v-avatar>
                        <h2 class="text-h5 font-weight-bold mb-2">{{ user.name }}</h2>
                        <div v-if="userRoles && userRoles.length > 0" class="d-flex flex-wrap justify-center gap-2 mb-2">
                            <v-chip v-for="role in userRoles" :key="role.id" color="primary" variant="flat">
                                {{ role.name }}
                            </v-chip>
                        </div>
                    </div>

                    <v-divider class="my-4"></v-divider>

                    <!-- User Information -->
                    <v-list density="comfortable">
                        <v-list-item v-if="user.email">
                            <template v-slot:prepend>
                                <v-icon color="primary">mdi-email</v-icon>
                            </template>
                            <v-list-item-title>Email</v-list-item-title>
                            <v-list-item-subtitle>{{ user.email }}</v-list-item-subtitle>
                        </v-list-item>

                        <v-list-item v-if="user.phone">
                            <template v-slot:prepend>
                                <v-icon color="primary">mdi-phone</v-icon>
                            </template>
                            <v-list-item-title>Phone</v-list-item-title>
                            <v-list-item-subtitle>{{ user.phone }}</v-list-item-subtitle>
                        </v-list-item>

                        <v-list-item v-if="user.date_of_birth">
                            <template v-slot:prepend>
                                <v-icon color="primary">mdi-calendar</v-icon>
                            </template>
                            <v-list-item-title>Date of Birth</v-list-item-title>
                            <v-list-item-subtitle>{{ formatDate(user.date_of_birth) }}</v-list-item-subtitle>
                        </v-list-item>

                        <v-list-item v-if="user.gender">
                            <template v-slot:prepend>
                                <v-icon color="primary">mdi-gender-male-female</v-icon>
                            </template>
                            <v-list-item-title>Gender</v-list-item-title>
                            <v-list-item-subtitle>{{ formatGender(user.gender) }}</v-list-item-subtitle>
                        </v-list-item>

                        <v-list-item v-if="user.address || user.city || user.state || user.country || user.postal_code">
                            <template v-slot:prepend>
                                <v-icon color="primary">mdi-map-marker</v-icon>
                            </template>
                            <v-list-item-title>Address</v-list-item-title>
                            <v-list-item-subtitle>
                                <div v-if="user.address" class="mb-1">{{ user.address }}</div>
                                <div>
                                    <span v-if="user.city">{{ user.city }}</span>
                                    <span v-if="user.city && user.state">, </span>
                                    <span v-if="user.state">{{ user.state }}</span>
                                    <span v-if="(user.city || user.state) && user.postal_code"> </span>
                                    <span v-if="user.postal_code">{{ user.postal_code }}</span>
                                </div>
                                <div v-if="user.country" class="mt-1">{{ user.country }}</div>
                            </v-list-item-subtitle>
                        </v-list-item>

                        <v-list-item v-if="userRoles && userRoles.length > 0">
                            <template v-slot:prepend>
                                <v-icon color="primary">mdi-shield-account</v-icon>
                            </template>
                            <v-list-item-title>Assigned Roles</v-list-item-title>
                            <v-list-item-subtitle>
                                <div class="d-flex flex-wrap gap-2 mt-2">
                                    <v-chip v-for="role in userRoles" :key="role.id" size="small" color="primary" variant="outlined">
                                        {{ role.name }}
                                    </v-chip>
                                </div>
                            </v-list-item-subtitle>
                        </v-list-item>

                        <v-list-item v-if="userPermissions && userPermissions.length > 0">
                            <template v-slot:prepend>
                                <v-icon color="primary">mdi-key</v-icon>
                            </template>
                            <v-list-item-title>Permissions</v-list-item-title>
                            <v-list-item-subtitle>
                                <div class="d-flex flex-wrap gap-2 mt-2">
                                    <v-chip v-for="permission in userPermissions" :key="permission" size="small" color="success" variant="outlined">
                                        {{ permission }}
                                    </v-chip>
                                </div>
                            </v-list-item-subtitle>
                        </v-list-item>

                        <v-list-item v-if="user.bio">
                            <template v-slot:prepend>
                                <v-icon color="primary">mdi-text</v-icon>
                            </template>
                            <v-list-item-title>Bio</v-list-item-title>
                            <v-list-item-subtitle class="text-body-2">{{ user.bio }}</v-list-item-subtitle>
                        </v-list-item>

                        <v-list-item v-if="user.created_at">
                            <template v-slot:prepend>
                                <v-icon color="primary">mdi-calendar-plus</v-icon>
                            </template>
                            <v-list-item-title>Member Since</v-list-item-title>
                            <v-list-item-subtitle>{{ formatDate(user.created_at) }}</v-list-item-subtitle>
                        </v-list-item>
                    </v-list>
                </div>

                <!-- Error State -->
                <div v-else class="d-flex align-center justify-center pa-12">
                    <div class="text-center">
                        <v-icon size="64" color="error">mdi-alert-circle</v-icon>
                        <p class="text-body-1 text-medium-emphasis mt-4">Failed to load profile</p>
                    </div>
                </div>
            </v-card-text>

            <v-card-actions class="pa-4 bg-grey-lighten-4">
                <v-spacer></v-spacer>
                <v-btn color="primary" variant="flat" @click="closeDialog">
                    Close
                </v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>

<script>
import moment from 'moment';
import { resolveUploadUrl } from '../../../utils/uploads';

export default {
    name: 'UserProfileDialog',
    props: {
        modelValue: {
            type: Boolean,
            default: false
        },
        user: {
            type: Object,
            default: null
        }
    },
    emits: ['update:modelValue'],
    data() {
        return {
            loading: false
        };
    },
    computed: {
        dialog: {
            get() {
                return this.modelValue;
            },
            set(value) {
                this.$emit('update:modelValue', value);
            }
        },
        resolvedAvatar() {
            if (!this.user || !this.user.avatar) return null;
            return this.resolveImageUrl(this.user.avatar);
        },
        userRoles() {
            return this.user?.roles || [];
        },
        userPermissions() {
            if (!this.user || !this.userRoles || this.userRoles.length === 0) return [];
            
            const permissions = [];
            this.userRoles.forEach(role => {
                if (role.permissions && role.permissions.length > 0) {
                    role.permissions.forEach(permission => {
                        if (!permissions.includes(permission.slug)) {
                            permissions.push(permission.slug);
                        }
                    });
                }
            });
            return permissions;
        }
    },
    methods: {
        closeDialog() {
            this.dialog = false;
        },
        resolveImageUrl(imageValue) {
            return resolveUploadUrl(imageValue);
        },
        formatDate(date) {
            if (!date) return 'N/A';
            return moment(date).format('MMMM Do, YYYY');
        },
        formatGender(gender) {
            const genderMap = {
                'male': 'Male',
                'female': 'Female',
                'other': 'Other'
            };
            return genderMap[gender] || gender;
        }
    }
};
</script>

<style scoped>
:deep(.v-list-item-title) {
    font-weight: 600;
    color: #334155;
}

:deep(.v-list-item-subtitle) {
    color: #64748b;
    margin-top: 4px;
}
</style>

