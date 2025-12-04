<?php

use App\Http\Controllers\Api\auth\AuthController;
use App\Http\Controllers\Api\products\CategoryController;
use App\Http\Controllers\Api\products\SubCategoryController;
use App\Http\Controllers\Api\leads\LeadController;
use App\Http\Controllers\Api\logs\LoginLogController;
use App\Http\Controllers\Api\users\PermissionController;
use App\Http\Controllers\Api\products\ProductController;
use App\Http\Controllers\Api\users\RoleController;
use App\Http\Controllers\Api\service\ServiceController;
use App\Http\Controllers\Api\settings\SettingController;
use App\Http\Controllers\Api\products\TagController;
use App\Http\Controllers\Api\upload\UploadController;
use App\Http\Controllers\Api\users\UserController;
use App\Http\Controllers\Api\logs\VisitorLogController;
use App\Http\Controllers\Api\newsletters\NewsletterController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Public\NewsletterController as PublicNewsletterController;
use App\Http\Controllers\Public\pages\ContactController;
use App\Http\Controllers\Public\pages\HomeController;
use App\Http\Controllers\Public\pages\PageController as PublicPageController;
use App\Http\Controllers\Public\products\ProductController as PublicProductController;
use App\Http\Controllers\Public\services\ServiceController as PublicServiceController;
use App\Http\Controllers\Public\about\AboutController as PublicAboutController;
use App\Http\Controllers\Public\blog\BlogController as PublicBlogController;
use App\Http\Controllers\Api\about\AboutController;
use App\Http\Controllers\Api\blog\BlogController;
use App\Http\Controllers\Api\career\CareerController;
use App\Http\Controllers\Api\career\JobApplicationController;
use Illuminate\Support\Facades\Route;

Route::get('test', function () {
    return response()->json(['message' => 'Hello World']);
});

// Test Telegram Notification
Route::get('test/telegram', function () {
    try {
        $message = request()->get('message', null);
        $chatId = request()->get('chat_id', null);
        
        $result = \App\Services\TelegramNotify::T_NOTIFY($message, $chatId);
        
        if ($result === false) {
            return response()->json([
                'success' => false,
                'message' => 'Telegram notification failed. Check logs for details.',
                'config' => [
                    'bot_token_configured' => !empty(config('telegram.bots.mybot.token')) || !empty(env('TELEGRAM_BOT_TOKEN')),
                    'chat_id_configured' => !empty(config('values.telegram_chat_id')) || !empty(env('TELEGRAM_CHAT_ID')),
                ]
            ], 500);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Telegram notification sent successfully!',
            'response' => [
                'message_id' => $result->messageId ?? $result->get('message_id'),
                'chat_id' => $result->chat->id ?? $result->get('chat')->get('id'),
                'date' => $result->date ?? $result->get('date'),
                'text' => $result->text ?? $result->get('text'),
            ]
        ], 200);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error: ' . $e->getMessage(),
            'trace' => config('app.debug') ? $e->getTraceAsString() : null
        ], 500);
    }
});



Route::prefix('v1')->group(function () {
    // Public routes
    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::get('/public/settings', [SettingController::class, 'publicIndex']);

    // Protected admin routes - requires authentication and admin access
    Route::middleware(['auth:sanctum', 'admin'])->group(function () {
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::get('/auth/user', [AuthController::class, 'user']);

        // Dashboard - no permissions required, only authentication
        Route::get('/dashboard', [DashboardController::class, 'index']);

       
        // Upload routes - requires manage-media permission
        Route::middleware('permission:manage-media')->group(function () {
            Route::post('upload/image', [UploadController::class, 'uploadImage']);
            Route::post('upload/images', [UploadController::class, 'uploadMultipleImages']);
            Route::post('upload/file', [UploadController::class, 'uploadFile']);
            Route::delete('upload/image', [UploadController::class, 'deleteImage']);
        });

        // Settings - requires manage-settings permission
        Route::middleware('permission:manage-settings')->group(function () {
            Route::get('settings', [SettingController::class, 'index']);
            Route::post('settings', [SettingController::class, 'update']);
        });

        // Users & Roles - requires manage-users permission
        Route::middleware('permission:manage-users')->group(function () {
            Route::get('users/roles', [UserController::class, 'roles']);
            Route::apiResource('users', UserController::class);
        });

        // Role Management - requires manage-roles permission
        Route::middleware('permission:manage-roles')->group(function () {
            Route::get('permissions', [RoleController::class, 'permissions']);
            Route::put('roles/{id}/permissions', [RoleController::class, 'syncPermissions']);
            Route::apiResource('roles', RoleController::class);
        });

        // Permission Management - requires manage-roles permission (same as roles)
        Route::middleware('permission:manage-roles')->group(function () {
            Route::get('permissions/groups', [PermissionController::class, 'groups']);
            Route::post('permissions/groups/rename', [PermissionController::class, 'renameGroup']);
            Route::post('permissions/groups/delete', [PermissionController::class, 'deleteGroup']);
            Route::apiResource('permissions', PermissionController::class);
        });

        // Login Logs - requires view-login-logs permission
        Route::middleware('permission:view-login-logs')->group(function () {
            Route::get('login-logs/statistics', [LoginLogController::class, 'statistics']);
            Route::apiResource('login-logs', LoginLogController::class)->only(['index', 'show', 'destroy']);
        });

        // Products & Categories - requires manage-products permission
        Route::middleware('permission:manage-products')->group(function () {
            Route::get('products/categories', [ProductController::class, 'categories']);
            Route::get('products/sub-categories', [ProductController::class, 'subCategories']);
            Route::get('products/units', [ProductController::class, 'units']);
            Route::apiResource('products', ProductController::class);
            Route::apiResource('categories', CategoryController::class);
            Route::get('sub-categories/categories', [SubCategoryController::class, 'categories']);
            Route::apiResource('sub-categories', SubCategoryController::class);
        });

        // Stock Management - requires manage-stock permission
        Route::middleware('permission:manage-stock')->group(function () {
            Route::get('stock-ledger/warehouses', [\App\Http\Controllers\Api\stock\StockLedgerController::class, 'warehouses']);
            Route::apiResource('stock-ledger', \App\Http\Controllers\Api\stock\StockLedgerController::class)->only(['index', 'show']);
            Route::get('transfers/warehouses', [\App\Http\Controllers\Api\stock\TransferController::class, 'warehouses']);
            Route::post('transfers/{id}/approve', [\App\Http\Controllers\Api\stock\TransferController::class, 'approve']);
            Route::post('transfers/{id}/receive', [\App\Http\Controllers\Api\stock\TransferController::class, 'receive']);
            Route::apiResource('transfers', \App\Http\Controllers\Api\stock\TransferController::class);
            Route::get('adjustments/warehouses', [\App\Http\Controllers\Api\stock\AdjustmentController::class, 'warehouses']);
            Route::post('adjustments/{id}/approve', [\App\Http\Controllers\Api\stock\AdjustmentController::class, 'approve']);
            Route::post('adjustments/{id}/complete', [\App\Http\Controllers\Api\stock\AdjustmentController::class, 'complete']);
            Route::apiResource('adjustments', \App\Http\Controllers\Api\stock\AdjustmentController::class);
            Route::apiResource('stocks', \App\Http\Controllers\Api\stock\StockController::class)->only(['index', 'show']);
        });

        // Purchase Management - requires manage-purchases permission
        Route::middleware('permission:manage-purchases')->group(function () {
            Route::post('purchase-requests/{id}/approve', [\App\Http\Controllers\Api\purchase\PurchaseRequestController::class, 'approve']);
            Route::post('purchase-requests/{id}/reject', [\App\Http\Controllers\Api\purchase\PurchaseRequestController::class, 'reject']);
            Route::apiResource('purchase-requests', \App\Http\Controllers\Api\purchase\PurchaseRequestController::class);
            Route::get('purchase-orders/suppliers', [\App\Http\Controllers\Api\purchase\PurchaseOrderController::class, 'suppliers']);
            Route::apiResource('purchase-orders', \App\Http\Controllers\Api\purchase\PurchaseOrderController::class);
            Route::post('grns/{id}/verify', [\App\Http\Controllers\Api\purchase\GrnController::class, 'verify']);
            Route::apiResource('grns', \App\Http\Controllers\Api\purchase\GrnController::class);
            Route::post('purchases/{id}/receive', [\App\Http\Controllers\Api\purchase\PurchaseController::class, 'receive']);
            Route::apiResource('purchases', \App\Http\Controllers\Api\purchase\PurchaseController::class);
            Route::post('purchase-returns/{id}/approve', [\App\Http\Controllers\Api\purchase\PurchaseReturnController::class, 'approve']);
            Route::post('purchase-returns/{id}/complete', [\App\Http\Controllers\Api\purchase\PurchaseReturnController::class, 'complete']);
            Route::apiResource('purchase-returns', \App\Http\Controllers\Api\purchase\PurchaseReturnController::class);
        });

        // Master Data - requires manage-master permission
        Route::middleware('permission:manage-master')->group(function () {
            Route::apiResource('suppliers', \App\Http\Controllers\Api\master\SupplierController::class);
            Route::apiResource('customers', \App\Http\Controllers\Api\master\CustomerController::class);
        });
      
    });
});
