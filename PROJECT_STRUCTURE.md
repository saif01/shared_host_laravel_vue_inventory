# Business Website Platform - Project Structure

This is a comprehensive business website platform built according to the SRS document. The platform supports multiple business types with configurable modules.

## Technology Stack

- **Backend**: Laravel 12 (PHP 8.2+)
- **Frontend**: Vue 3 with Vuetify 3
- **Database**: MySQL/PostgreSQL/SQLite
- **Authentication**: Laravel Sanctum (API tokens)
- **Build Tool**: Vite
- **UI Framework**: Vuetify 3
- **Charts**: Chart.js with vue-chartjs
- **Additional Libraries**: SweetAlert2, Vue Progress Bar, Quill (Rich Text Editor)

## Project Structure

### Backend (Laravel)

#### Models (`app/Models/`)
- **Core Models**: Module, Menu, Setting, Lead, User
- **Optional Modules**: Service, Product, Portfolio, BlogPost, Faq
- **Extended Modules**: Career, JobApplication, Booking, Event, EventRegistration, Branch
- **Supporting**: Category, Tag, Media
- **Newsletter**: NewsletterSubscription
- **Logging**: LoginLog, VisitorLog
- **RBAC**: Role, Permission

#### Controllers

**API Controllers** (`app/Http/Controllers/Api/`): Admin panel API endpoints
- `auth/AuthController.php` - Authentication (login/logout/user)
- `about/AboutController.php` - About page management (singleton)
- `service/ServiceController.php` - Services management
- `products/ProductController.php` - Products management
- `products/CategoryController.php` - Categories management
- `products/TagController.php` - Tags management
- `blog/BlogController.php` - Blog posts management
- `blog/BlogCategoryController.php` - Blog categories management
- `career/CareerController.php` - Career listings management
- `career/JobApplicationController.php` - Job applications management
- `leads/LeadController.php` - Leads management, export, and statistics
- `NewsletterController.php` - Newsletter subscriptions management
- `settings/SettingController.php` - Settings management
- `upload/UploadController.php` - File and image uploads
- `users/UserController.php` - User management
- `users/RoleController.php` - Role management
- `users/PermissionController.php` - Permission management
- `logs/LoginLogController.php` - Login logs with statistics and time-series data
- `logs/VisitorLogController.php` - Visitor logs with statistics and time-series data

**Public Controllers** (`app/Http/Controllers/Public/`): Public website endpoints
- `pages/HomeController.php` - Homepage data
- `pages/PageController.php` - Public pages (for viewing pages by slug)
- `pages/ContactController.php` - Contact form submission
- `NewsletterController.php` - Newsletter subscription (public)
- `products/ProductController.php` - Public products listing and details
- `services/ServiceController.php` - Public services listing and details
- `about/AboutController.php` - Public about page
- `blog/BlogController.php` - Public blog posts listing, details, and categories
- `career/CareerController.php` - Public career listings, details, and job application submission

#### Routes
- `routes/api.php`: Admin API routes (protected with Sanctum) and public API routes
- `routes/web.php`: Public API routes + Vue SPA catch-all

#### Database
- All migrations created for core and optional modules
- Seeders for initial modules and admin user
- Pivot tables for many-to-many relationships (category_product, tag_product, etc.)

### Frontend (Vue 3)

#### Main Entry Point
- **`resources/js/app.js`**: Main application entry point
  - Initializes Vue app instance
  - Registers all plugins and utilities
  - Mounts the application

#### Utilities (`resources/js/utils/`)
- **`axios.config.js`**: Axios HTTP client configuration
  - Base URL configuration (development/production)
  - Default headers setup
  - Request interceptors (authentication tokens)
  - Response interceptors (error handling, CORS, 401 errors)
- **`uploads.js`**: Upload URL resolution utilities

#### Plugins (`resources/js/plugins/`)
- **`vuetify.js`**: Vuetify UI framework configuration
  - Component registration
  - Directive registration
  - Theme configuration
- **`progressBar.js`**: Vue Progress Bar plugin configuration
  - Progress bar options (color, thickness, location)
  - Router helper functions for progress bar access
  - Setup functions for router hooks
- **`sweetalert.js`**: SweetAlert2 plugin configuration
  - Toast notifications setup
  - Global Swal and Toast exposure
  - Configuration for alerts and notifications

#### Components

**Admin Components** (`resources/js/components/admin/`):
- `AdminLayout.vue` - Admin layout (sidebar, app bar, footer) with permission-based menu
- `AdminDashboard.vue` - AI-powered analytics dashboard with Chart.js visualizations
- `auth/AdminLogin.vue` - Admin login page
- `about/AdminAbout.vue` - About page management
- `about/AboutFormDialog.vue` - About page form dialog
- `service/AdminServices.vue` - Services management
- `service/ServiceFormDialog.vue` - Service creation/editing form
- `service/ServiceDetailsDialog.vue` - Service details view
- `products/AdminProducts.vue` - Products management (11-tab form)
- `products/ProductFormDialog.vue` - Product creation/editing form (11 tabs)
- `products/ProductDetailsDialog.vue` - Product details view
- `products/AdminCategories.vue` - Categories management
- `products/AdminTags.vue` - Tags management
- `leads/AdminLeads.vue` - Leads management with status tracking
- `newsletters/AdminNewsletters.vue` - Newsletter subscriptions management
- `users/AdminUsers.vue` - User management
- `users/AdminRoles.vue` - Role management
- `users/AdminPermissions.vue` - Permission management (flat and grouped views)
- `settings/AdminSettings.vue` - Settings management (main container)
  - `settings/sections/GeneralSettings.vue` - General site settings
  - `settings/sections/HomePageSettings.vue` - Home page settings container with tabs
    - `settings/sections/home_page/HeroSectionSettings.vue` - Hero section settings
    - `settings/sections/home_page/StatsSectionSettings.vue` - Statistics section settings
    - `settings/sections/home_page/TrustedBySectionSettings.vue` - Trusted By section with client logo management
    - `settings/sections/home_page/ServicesSectionSettings.vue` - Services section with dynamic service management
    - `settings/sections/home_page/WhyChooseUsSectionSettings.vue` - Why Choose Us section with features management
    - `settings/sections/home_page/TestimonialsSectionSettings.vue` - Testimonials section settings
    - `settings/sections/home_page/ProductsSectionSettings.vue` - Featured Products section settings
    - `settings/sections/home_page/CTASectionSettings.vue` - CTA section settings
    - `settings/sections/home_page/VisibilitySectionSettings.vue` - Section visibility toggles
  - `settings/sections/ContactPageSettings.vue` - Contact page settings
  - `settings/sections/BrandingSettings.vue` - Branding settings
  - `settings/sections/SocialSettings.vue` - Social media links
  - `settings/sections/SEOSettings.vue` - SEO settings
  - `settings/sections/EmailSettings.vue` - Email/SMTP settings
- `logs/AdminLoginLogs.vue` - Login logs with statistics and filtering
- `logs/AdminVisitorLogs.vue` - Visitor logs with comprehensive analytics

**Public Components** (`resources/js/components/public/`):
- `PublicLayout.vue` - Public website layout
- `pages/HomePage.vue` - Homepage component with dynamic sections
- `pages/AboutPage.vue` - About page
- `pages/ContactPage.vue` - Contact page
- `services/ServicesPage.vue` - Services listing
- `services/ServiceDetailPage.vue` - Service detail page
- `products/ProductsPage.vue` - Products listing with filters, search, and comparison
- `products/ProductDetailPage.vue` - Product detail with gallery, specs, FAQs, warranty
- `blog/BlogPage.vue` - Blog posts listing with search, category filter, and sorting
- `blog/BlogDetailPage.vue` - Blog post detail with author, categories, tags, and social sharing
- `career/CareerPage.vue` - Career listings with search, department, location, and employment type filters
- `career/CareerDetailPage.vue` - Career detail with job application form and Bangladesh phone validation

#### Mixins (`resources/js/mixins/`)
- **`adminPaginationMixin.js`**: Shared pagination logic for admin components
  - Pagination state management
  - Search functionality
  - Loading/saving states
  - Success/Error notification methods
  - Date formatting utilities
  - Authentication helpers
  - API error handling
  - Sorting functionality
  - Standard pagination UI components

#### Routes (`resources/js/routes.js`)
- Public routes configuration
- Admin routes configuration (with authentication guards)
- Router navigation hooks for progress bar
- Route meta information handling

## Setup Instructions

### 1. Install Dependencies

```bash
composer install
npm install
```

### 2. Environment Configuration

Copy `.env.example` to `.env` and configure:
- Database connection
- APP_URL
- Mail settings

```bash
php artisan key:generate
```

### 3. Run Migrations & Seeders

```bash
php artisan migrate
php artisan db:seed
```

This will create:
- All database tables
- Module definitions (disabled by default)
- Admin user (email: admin@example.com, password: password)

### 4. Build Assets

```bash
npm run dev  # Development
# or
npm run build  # Production
```

### 5. Start Development Server

```bash
php artisan serve
npm run dev
```

## API Endpoints

### Admin API (`/api/v1/`)

**Authentication:**
- `POST /api/v1/auth/login` - Login
- `POST /api/v1/auth/logout` - Logout (requires auth)
- `GET /api/v1/auth/user` - Get current user (requires auth)

**About Page Management:**
- `GET /api/v1/about` - Get about page (requires `manage-pages` permission)
- `POST /api/v1/about` - Create about page (requires `manage-pages` permission)
- `PUT /api/v1/about` - Update about page (requires `manage-pages` permission)

**Services Management:**
- `GET /api/v1/services` - List services (with pagination, filtering, sorting, search)
- `POST /api/v1/services` - Create service (requires `manage-services` permission)
- `GET /api/v1/services/{id}` - Get service (by ID or slug)
- `PUT /api/v1/services/{id}` - Update service (requires `manage-services` permission)
- `DELETE /api/v1/services/{id}` - Delete service (requires `manage-services` permission)

**Product Management:**
- `GET /api/v1/products` - List products (with pagination, filtering, sorting, search)
- `POST /api/v1/products` - Create product (requires `manage-products` permission)
- `GET /api/v1/products/{id}` - Get product (by ID or slug, includes all relationships)
- `PUT /api/v1/products/{id}` - Update product (requires `manage-products` permission)
- `DELETE /api/v1/products/{id}` - Delete product (requires `manage-products` permission)

**Category Management:**
- `GET /api/v1/categories` - List categories (supports filtering by type, parent_id, published)
- `POST /api/v1/categories` - Create category (requires `manage-products` permission)
- `GET /api/v1/categories/{id}` - Get category (by ID or slug)
- `PUT /api/v1/categories/{id}` - Update category (requires `manage-products` permission)
- `DELETE /api/v1/categories/{id}` - Delete category (requires `manage-products` permission)

**Tag Management:**
- `GET /api/v1/tags` - List tags (supports filtering by type, search)
- `POST /api/v1/tags` - Create tag (requires `manage-products` permission)
- `GET /api/v1/tags/{id}` - Get tag (by ID or slug)
- `PUT /api/v1/tags/{id}` - Update tag (requires `manage-products` permission)
- `DELETE /api/v1/tags/{id}` - Delete tag (requires `manage-products` permission)

**File Upload:**
- `POST /api/v1/upload/image` - Upload single image
- `POST /api/v1/upload/images` - Upload multiple images
- `POST /api/v1/upload/file` - Upload file (PDF, DOC, ZIP, etc.)
- `DELETE /api/v1/upload/image` - Delete image

**Leads Management:**
- `GET /api/v1/leads` - List leads (with pagination, filtering, sorting, search) (requires `view-leads` permission)
- `GET /api/v1/leads/statistics` - Get leads statistics with time-series data (requires `view-leads` permission)
- `GET /api/v1/leads/unread-count` - Get count of unread leads (requires `view-leads` permission)
- `GET /api/v1/leads/{id}` - Get lead details (requires `view-leads` permission)
- `POST /api/v1/leads/{id}/mark-as-read` - Mark lead as read (requires `view-leads` permission)
- `PUT /api/v1/leads/{id}` - Update lead (requires `manage-leads` permission)
- `DELETE /api/v1/leads/{id}` - Delete lead (requires `manage-leads` permission)
- `GET /api/v1/leads/export/csv` - Export leads to CSV (requires `export-leads` permission)

**Newsletter Subscriptions:**
- `GET /api/v1/newsletters` - List newsletter subscriptions (requires `view-leads` permission)
- `GET /api/v1/newsletters/{id}` - Get subscription details (requires `view-leads` permission)
- `PUT /api/v1/newsletters/{id}` - Update subscription status (requires `manage-leads` permission)
- `DELETE /api/v1/newsletters/{id}` - Delete subscription (requires `manage-leads` permission)
- `GET /api/v1/newsletters/export/csv` - Export subscriptions to CSV (requires `manage-leads` permission)

**User Management:**
- `GET /api/v1/users` - List users (requires `manage-users` permission)
- `POST /api/v1/users` - Create user (requires `manage-users` permission)
- `GET /api/v1/users/{id}` - Get user (requires `manage-users` permission)
- `PUT /api/v1/users/{id}` - Update user (requires `manage-users` permission)
- `DELETE /api/v1/users/{id}` - Delete user (requires `manage-users` permission)

**Role & Permission Management:**
- `GET /api/v1/roles` - List roles (requires `manage-roles` permission)
- `POST /api/v1/roles` - Create role (requires `manage-roles` permission)
- `PUT /api/v1/roles/{id}` - Update role (requires `manage-roles` permission)
- `PUT /api/v1/roles/{id}/permissions` - Sync role permissions (requires `manage-roles` permission)
- `DELETE /api/v1/roles/{id}` - Delete role (requires `manage-roles` permission)
- `GET /api/v1/permissions` - List permissions (requires `manage-roles` permission)
- `GET /api/v1/permissions/groups` - Get permission groups (requires `manage-roles` permission)
- `POST /api/v1/permissions` - Create permission (requires `manage-roles` permission)
- `PUT /api/v1/permissions/{id}` - Update permission (requires `manage-roles` permission)
- `DELETE /api/v1/permissions/{id}` - Delete permission (requires `manage-roles` permission)

**Logs & Analytics:**
- `GET /api/v1/login-logs` - List login logs (with pagination, filtering, sorting, search) (requires `view-login-logs` permission)
- `GET /api/v1/login-logs/statistics` - Get login statistics with time-series trends data (requires `view-login-logs` permission)
- `GET /api/v1/login-logs/{id}` - Get login log details (requires `view-login-logs` permission)
- `DELETE /api/v1/login-logs/{id}` - Delete login log (requires `view-login-logs` permission)
- `GET /api/v1/visitor-logs` - List visitor logs (with pagination, filtering, sorting, search, bot filtering) (requires `view-visitor-logs` permission)
- `GET /api/v1/visitor-logs/statistics` - Get visitor statistics with time-series trends data (requires `view-visitor-logs` permission)
- `GET /api/v1/visitor-logs/{id}` - Get visitor log details (requires `view-visitor-logs` permission)
- `DELETE /api/v1/visitor-logs/{id}` - Delete visitor log (requires `view-visitor-logs` permission)
- `POST /api/v1/visitor-logs/delete-multiple` - Delete multiple visitor logs (requires `view-visitor-logs` permission)

**Settings:**
- `GET /api/v1/settings` - Get all settings (requires authentication)
- `PUT /api/v1/settings` - Update settings (requires `manage-settings` permission)

### Public API (`/api/openapi/`)

- `GET /api/openapi/home` - Homepage data (includes home page settings and featured content)
- `GET /api/openapi/pages/{slug}` - Get page by slug (for public page viewing)
- `GET /api/openapi/services` - List published services
- `GET /api/openapi/services/{slug}` - Get service by slug
- `GET /api/openapi/products` - List published products (supports category filter, search, sorting)
- `GET /api/openapi/products/{slug}` - Get product by slug (includes categories, tags, specifications, downloads)
- `GET /api/openapi/categories` - List categories (supports type filter, pagination)
- `GET /api/openapi/settings` - Get public settings
- `GET /api/openapi/about` - Get about page
- `POST /api/openapi/contact` - Submit contact form (creates lead)
- `POST /api/openapi/newsletter/subscribe` - Subscribe to newsletter
- `GET /api/openapi/blog` - List published blog posts (supports search, category filter, tag filter, sorting, pagination)
- `GET /api/openapi/blog/{slug}` - Get blog post by slug (includes author, categories, tags, auto-increments views)
- `GET /api/openapi/blog/categories` - List published blog categories
- `GET /api/openapi/careers` - List published and active careers (supports search, department, location, employment type filters, active_only flag, sorting, pagination, returns filter options)
- `GET /api/openapi/careers/{slug}` - Get career by slug (includes application count, active status check)
- `POST /api/openapi/careers/apply` - Submit job application (with resume upload, Bangladesh phone validation, deadline checking)

## Features Implemented

### Core Features

#### Authentication & Security
- **Laravel Sanctum Authentication**: Secure API token-based authentication
- **Role-Based Access Control (RBAC)**: Complete permission system
- **User Management**: Full user administration
- **Permission Management**: Fine-grained permission control
- **Login Logging**: Comprehensive login attempt tracking
- **Visitor Logging**: Advanced visitor analytics

#### Dashboard & Analytics
- **AI-Powered Analytics Dashboard**: 
  - Real-time statistics cards with trend indicators
  - Interactive Chart.js visualizations:
    - Visitor trends (line chart with time-series data)
    - Device distribution (doughnut chart)
    - Browser distribution (bar chart)
    - Login activity (pie chart)
    - Leads by status (bar chart)
    - Top visited pages (horizontal bar chart)
  - Time range selection (7d, 30d, 90d, 1y)
  - AI-powered insights with automated analysis
  - Recent activity tables
  - All data fetched from real database with time-series support

#### Services Management
- Complete CRUD operations
- Rich text editor for descriptions
- Image upload with preview
- SEO optimization
- Published/Draft status
- Display order management

#### Products Management
- **11-Tab Form System**:
  1. Basic Info - Title, slug, SKU, descriptions
  2. Media - Thumbnail and gallery images
  3. Pricing - Price, price range, show/hide toggle
  4. Categories & Tags - Multi-select with auto-creation
  5. Specifications - Dynamic key-value pairs
  6. Features - Key features list
  7. Downloads - File uploads (PDFs, datasheets)
  8. FAQs - Question-answer pairs
  9. Warranty & Service - Warranty information
  10. SEO - Meta tags and OG image
  11. Settings - Published, featured, stock, order
- Product details view
- Image and file management
- Comprehensive product information

#### Categories & Tags
- Hierarchical category structure
- Category type filtering
- Tag management with auto-slug generation
- Published/Draft status

#### Leads Management
- Lead status tracking (New, Contacted, Qualified, Converted, Lost)
- Lead type categorization
- Read/Unread status
- Lead assignment
- Notes and comments
- CSV export
- Statistics with time-series data

#### Newsletter Management
- Subscription tracking
- Status management
- CSV export
- Bulk operations

#### About Page Management
- Singleton about page
- Company story and mission
- Team information
- Company values

#### Settings Management
- **Modular Settings System**:
  - General settings
  - Home page settings (9 separate section components)
  - Contact page settings
  - Branding settings
  - Social media settings
  - SEO settings
  - Email/SMTP settings

#### Logs Management
- **Login Logs**:
  - Track all login attempts
  - Filter by status
  - Search functionality
  - Statistics with time-series data
- **Visitor Logs**:
  - Comprehensive visitor tracking
  - Device, browser, OS detection
  - Bot detection and filtering
  - Statistics with breakdowns
  - Top visited pages
  - Time-series trends data
  - Bulk delete functionality

### Admin Panel Features

- **Modern UI**: Vuetify 3 with gradient design
- **Responsive Design**: Works on all screen sizes
- **Standard Pagination**: Consistent pagination UI across all admin pages
- **Skeleton Loaders**: Loading states for better UX
- **Toast Notifications**: SweetAlert2 for user feedback
- **Progress Bar**: Route navigation progress indicator
- **Permission-Based Menu**: Sidebar menu items shown based on user permissions
- **Unread Count Badge**: Real-time unread leads count in sidebar

### Public Website Features

#### Homepage
- **Dynamic Sections** (all configurable from admin):
  - Hero section
  - Statistics section
  - Trusted By section
  - Services section
  - Why Choose Us section
  - Testimonials section
  - Featured Products section
  - CTA section

#### Products Display
- **Product Listing**:
  - Category filtering
  - Real-time search
  - Multiple sorting options
  - Product comparison tool (up to 3 products)
  - Responsive grid layout
- **Product Detail**:
  - Image gallery with zoom
  - Technical specifications
  - Features list
  - Downloadable files
  - FAQs section
  - Warranty information

#### Services Display
- Services listing
- Service detail pages

#### Blog System
- **Blog Listing Page**:
  - Modern hero section with animated gradient effects
  - Real-time search across post titles, excerpts, and content
  - Category-based filtering with sidebar navigation
  - Multiple sorting options (Latest, Oldest, Most Views, Title A-Z)
  - Pagination support with configurable items per page
  - Responsive card-based layout
  - Loading states and empty state handling
  - Category badges and tag display
  - Author information and view counters
  - Featured image support
- **Blog Detail Page**:
  - Hero section with featured image overlay
  - Full post content with rich HTML rendering
  - Breadcrumb navigation
  - Author information card
  - Social sharing (Facebook, Twitter, LinkedIn, Copy link)
  - Tags and categories display
  - Automatic view counter increment
  - Related posts placeholder
  - Styled content typography (headings, lists, blockquotes, code blocks)
  - Responsive image display
- **Blog Features**:
  - SEO-friendly URLs (slug-based)
  - Meta tags and Open Graph support
  - Category management
  - Tag system
  - Author attribution
  - Publication date management
  - Draft/Published status
  - View tracking

#### Career System
- **Career Listing Page**:
  - Modern hero section with animated gradient effects
  - Real-time search across job titles, departments, locations, and descriptions
  - Advanced filtering:
    - Department filter (dynamically populated from available careers)
    - Location filter (dynamically populated from available careers)
    - Employment type filter (full-time, part-time, contract, internship, freelance)
  - Active careers only (automatically filters out expired deadlines)
  - Responsive card-based layout with hover effects
  - Career cards displaying:
    - Job title and department badge
    - Location and employment type
    - Application deadline (if set)
    - Truncated job description
    - Quick view button
  - Pagination support
  - Loading states and empty state handling
  - Clear filters functionality
- **Career Detail Page**:
  - Hero section with job title and key information chips
  - Comprehensive job information display:
    - Full job description with rich HTML rendering
    - Detailed responsibilities section
    - Job requirements section
    - Benefits and perks section
  - **Job Application Form** (sidebar):
    - Full name field (required)
    - Email field with validation (required)
    - Phone field with Bangladesh phone number validation (optional)
    - Cover letter textarea
    - Resume upload (PDF, DOC, DOCX, max 5MB)
    - Application deadline display and validation
    - Active/inactive status checking
    - Form validation with user-friendly error messages
    - Success/error notifications using SweetAlert2
    - Automatic form reset on successful submission
  - Back to careers navigation
  - Responsive layout (details on left, form on right)
- **Career Features**:
  - SEO-friendly URLs (slug-based)
  - Automatic deadline expiration checking
  - Bangladesh phone number validation (supports local: 01707080401 and international: +8801707080401 formats)
  - Resume file management with secure uploads
  - Application tracking and status management
  - Only published and active careers shown to public
  - Application count per career

#### Contact & Communication
- Contact form
- Newsletter subscription

## Frontend File Structure

```
resources/js/
├── app.js                          # Main entry point
├── bootstrap.js                    # Bootstrap configuration
├── routes.js                       # Vue Router configuration
│
├── utils/                          # Utility functions
│   ├── axios.config.js            # Axios HTTP client configuration
│   └── uploads.js                  # Upload URL resolution
│
├── plugins/                        # Vue plugins
│   ├── vuetify.js                 # Vuetify UI framework setup
│   ├── progressBar.js             # Progress bar plugin setup
│   └── sweetalert.js              # SweetAlert2 plugin setup
│
├── mixins/                         # Vue mixins for shared logic
│   └── adminPaginationMixin.js    # Pagination mixin for admin components
│
└── components/
    ├── app.vue                     # Root component
    ├── PageLoader.vue              # Page loader component
    │
    ├── admin/                      # Admin panel components
    │   ├── AdminLayout.vue        # Admin layout (sidebar, app bar, footer)
    │   ├── AdminDashboard.vue     # AI-powered analytics dashboard
    │   │
    │   ├── auth/                   # Authentication
    │   │   └── AdminLogin.vue     # Admin login page
    │   │
    │   ├── about/                  # About page management
    │   │   ├── AdminAbout.vue     # About page management
    │   │   └── AboutFormDialog.vue # About page form
    │   │
    │   ├── service/                # Service management
    │   │   ├── AdminServices.vue  # Services management
    │   │   ├── ServiceFormDialog.vue # Service form
    │   │   └── ServiceDetailsDialog.vue # Service details
    │   │
    │   ├── products/               # Product management
    │   │   ├── AdminProducts.vue  # Products management (11-tab form)
    │   │   ├── ProductFormDialog.vue # Product form (11 tabs)
    │   │   ├── ProductDetailsDialog.vue # Product details
    │   │   ├── AdminCategories.vue # Categories management
    │   │   └── AdminTags.vue      # Tags management
    │   │
    │   ├── leads/                  # Leads management
    │   │   └── AdminLeads.vue     # Leads management
    │   │
    │   ├── newsletters/            # Newsletter management
    │   │   └── AdminNewsletters.vue # Newsletter subscriptions
    │   │
    │   ├── users/                  # User management
    │   │   ├── AdminUsers.vue     # User management
    │   │   ├── AdminRoles.vue     # Role management
    │   │   └── AdminPermissions.vue # Permission management
    │   │
    │   ├── settings/              # Settings
    │   │   ├── AdminSettings.vue # Settings management (main container)
    │   │   └── sections/          # Settings section components
    │   │       ├── GeneralSettings.vue
    │   │       ├── HomePageSettings.vue
    │   │       ├── home_page/     # Home page section components
    │   │       │   ├── HeroSectionSettings.vue
    │   │       │   ├── StatsSectionSettings.vue
    │   │       │   ├── TrustedBySectionSettings.vue
    │   │       │   ├── ServicesSectionSettings.vue
    │   │       │   ├── WhyChooseUsSectionSettings.vue
    │   │       │   ├── TestimonialsSectionSettings.vue
    │   │       │   ├── ProductsSectionSettings.vue
    │   │       │   ├── CTASectionSettings.vue
    │   │       │   └── VisibilitySectionSettings.vue
    │   │       ├── ContactPageSettings.vue
    │   │       ├── BrandingSettings.vue
    │   │       ├── SocialSettings.vue
    │   │       ├── SEOSettings.vue
    │   │       └── EmailSettings.vue
    │   │
    │   └── logs/                   # Logs
    │       ├── AdminLoginLogs.vue # Login logs
    │       └── AdminVisitorLogs.vue # Visitor logs
    │
    └── public/                     # Public website components
        ├── PublicLayout.vue       # Public layout
        │
        ├── layout/                 # Layout components
        │   ├── AppBar.vue         # Navigation bar
        │   ├── Footer.vue         # Footer
        │   ├── MobileDrawer.vue   # Mobile navigation
        │   ├── WhatsAppFloat.vue  # WhatsApp button
        │   └── GoToTopButton.vue  # Scroll to top
        │
        ├── pages/                  # Page components
        │   ├── HomePage.vue       # Homepage
        │   ├── AboutPage.vue      # About page
        │   └── ContactPage.vue    # Contact page
        │
        ├── products/               # Product pages
        │   ├── ProductsPage.vue   # Products listing
        │   └── ProductDetailPage.vue # Product detail
        │
        └── services/               # Service pages
            ├── ServicesPage.vue   # Services listing
            └── ServiceDetailPage.vue # Service detail
```

## Backend File Structure

```
app/Http/Controllers/
├── Api/                            # Admin API controllers
│   ├── auth/
│   │   └── AuthController.php
│   ├── about/
│   │   └── AboutController.php
│   ├── service/
│   │   └── ServiceController.php
│   ├── leads/
│   │   └── LeadController.php (includes statistics endpoint)
│   ├── NewsletterController.php
│   ├── logs/
│   │   ├── LoginLogController.php (includes statistics with time-series)
│   │   └── VisitorLogController.php (includes statistics with time-series)
│   ├── products/
│   │   ├── ProductController.php
│   │   ├── CategoryController.php
│   │   └── TagController.php
│   ├── settings/
│   │   └── SettingController.php
│   ├── upload/
│   │   └── UploadController.php
│   └── users/
│       ├── UserController.php
│       ├── RoleController.php
│       └── PermissionController.php
│
└── Public/                         # Public website controllers
    ├── pages/
    │   ├── HomeController.php
    │   ├── PageController.php (for viewing pages by slug)
    │   └── ContactController.php
    ├── about/
    │   └── AboutController.php
    ├── NewsletterController.php
    ├── products/
    │   └── ProductController.php
    └── services/
        └── ServiceController.php
```

## Styling & Assets

### CSS/SASS Files
- **`resources/sass/app.scss`**: Main stylesheet
  - Bootstrap import
  - Vuetify styles
  - CSS custom properties (variables) for admin theme colors
  - Admin table styles (compact, bordered, responsive)
  - Footer styles
  - SweetAlert2 z-index overrides

### CSS Variables (Centralized in `app.scss`)
- `--admin-gradient-start`: Primary gradient start color (#2c73d2)
- `--admin-gradient-end`: Primary gradient end color (#008f7a)
- `--admin-gradient-primary`: Complete gradient definition
- `--admin-overlay-*`: Various overlay opacity values
- `--admin-text-primary`: Primary text color (#ffffff)

### File Uploads
- Images and files are uploaded to `public/uploads/{folder}/`
- Product images: `public/uploads/products/`
- Files are named with prefix: `{prefix}-{random}.{ext}`
- Supports single image, multiple images, and general file uploads

## Dashboard Analytics Features

### Statistics Cards
- **Total Visitors**: Shows total visitor count with trend indicator (percentage change vs last period)
- **Human Visits**: Displays human visits count with percentage of total
- **New Leads**: Shows new leads requiring attention with alert indicator
- **Total Products**: Displays product count with services count

### Chart Visualizations
- **Visitor Trends**: Line chart showing daily visitor trends (total and human visits) over selected time range
- **Device Distribution**: Doughnut chart showing breakdown by device type (Desktop, Mobile, Tablet)
- **Browser Distribution**: Bar chart showing top 5 browsers used by visitors
- **Login Activity**: Pie chart showing successful vs failed login attempts
- **Leads by Status**: Bar chart showing distribution of leads by status (New, Contacted, Qualified, Converted, Lost)
- **Top Visited Pages**: Horizontal bar chart showing top 5 most visited URLs

### Time Range Support
- **7 Days**: Last week's data
- **30 Days**: Last month's data
- **90 Days**: Last quarter's data
- **1 Year**: Last year's data

### AI-Powered Insights
- **Automated Analysis**: System analyzes data and provides insights:
  - **Warnings**: High bot traffic, login failures, new leads requiring attention
  - **Success Messages**: Strong visitor engagement, good content portfolio
  - **Information**: Content statistics, system status
- **Color-Coded Icons**: Different icon types for different insight categories
- **Actionable Recommendations**: Insights provide actionable information

### Data Sources
- Real-time data from database
- Time-series data from controllers
- Statistics APIs with trend calculations
- Recent activity from latest records

## Product Pages Features

### ProductsPage.vue - Product Listing

**Features:**
- **Hero Section**: Modern hero with gradient background, product range title, and premium quality badge
- **Category Filtering**: Filter products by category with icon-based category buttons
- **Search Functionality**: Real-time search across product titles, descriptions, SKU, and specifications
- **Sorting Options**: Sort by newest, price (low to high/high to low), name (A-Z/Z-A), or featured
- **Product Cards**: 
  - Product images with hover effects
  - Quick specifications preview
  - Price display with optional old price
  - Featured product badges
  - Quick action buttons (view, compare)
- **Product Comparison Tool**: 
  - Compare up to 3 products side-by-side
  - Comparison dialog showing:
    - Price comparison
    - Key specifications
    - Technical differences
    - Recommended use-cases
    - Quick access to product details
- **Responsive Design**: Optimized for desktop, tablet, and mobile devices
- **Sticky Filter Bar**: Filter bar becomes sticky on scroll for easy access

### ProductDetailPage.vue - Product Details

**Features:**
- **Hero Section**: Full-width hero with product title, category badge, SKU, and price card
- **Product Gallery**: 
  - Main product image with zoom functionality
  - Thumbnail navigation
  - Image zoom dialog for detailed viewing
- **Key Features Section**: Highlighted product features with icons
- **Quick Specifications**: Quick specs preview in sidebar
- **Tabbed Content**:
  - **Overview**: Detailed product description and benefits
  - **Technical Specs**: Complete technical specifications table
  - **Features**: Detailed feature list with descriptions
  - **Downloads**: Downloadable datasheets, manuals, and documentation (PDF, ZIP, etc.)
  - **FAQs**: Expandable FAQ section with common questions
  - **Warranty & Service**: 
    - Warranty coverage details
    - Service and support information
    - Trust badges (Warranty, Delivery, Support)
- **Related Products**: Display related products at the bottom
- **Trust Badges**: Warranty, delivery, and support information
- **Responsive Design**: Fully responsive layout for all devices

**Technical Implementation:**
- Integrated with `/api/openapi/products` and `/api/openapi/products/{slug}` endpoints
- Handles missing data gracefully with fallback content
- Uses Vuetify components for consistent UI
- Optimized performance with computed properties
- Clean, maintainable code structure
- Handles paginated category responses correctly

## Admin Product Management

### AdminProducts.vue - Product Management

**Features:**
- **11-Tab Form**:
  1. Basic Info - Title, slug, SKU, descriptions
  2. Media - Thumbnail and gallery images with preview
  3. Pricing - Price, price range, show price toggle
  4. Categories & Tags - Multi-select with auto-creation
  5. Specifications - Dynamic key-value pairs
  6. Features - Key features list
  7. Downloads - File uploads with preview
  8. FAQs - Question-answer pairs
  9. Warranty & Service - Warranty information
  10. SEO - Meta tags and OG image
  11. Settings - Published, featured, stock, order
- **Image Upload**: 
  - File input with preview before upload
  - Uploads on form submission
  - Supports thumbnail and gallery images
  - Files named with product name prefix
- **File Upload**: 
  - Download section supports file uploads
  - Auto-detects file type and size
  - Preview before submission
- **Product Details View**: Read-only view of all product information
- **Edit from Details**: Seamless transition from details to edit view

## Admin Panel UI Features

### Standard Pagination
- **Consistent Design**: All admin pages use the same pagination style
- **Features**:
  - Responsive layout (stacks on mobile, side-by-side on desktop)
  - Page indicator: "(Page X of Y)" when multiple pages exist
  - Number formatting with `toLocaleString()` for large numbers
  - Bold formatting for key numbers
  - `total-visible="7"` to limit visible page buttons for large datasets
  - `density="comfortable"` for better spacing

### Sidebar Navigation
- **Organized Structure**:
  - Overview (Dashboard)
  - Content Management (About Page, Services, Products)
  - User Management (Users, Roles & Permissions)
  - Communication (Leads, Newsletters)
  - System & Administration (Settings, Logs)
- **Permission-Based**: Menu items shown based on user permissions
- **Unread Badge**: Real-time unread leads count in sidebar
- **Modern Design**: Gradient background with animated shapes

## Notes

- This is a foundational structure that can be expanded
- All core models and relationships are set up
- Admin authentication is working
- Public API endpoints are ready
- Vue components are fully configured with routes
- Product pages feature modern, clean design suitable for business/industrial websites
- Admin and public folders are organized by feature for better maintainability
- File uploads are stored in public directory for easy access
- Dashboard includes AI-powered insights and real-time analytics with Chart.js
- All statistics endpoints support time-series data for trend analysis
- Additional admin and public components can be added incrementally
