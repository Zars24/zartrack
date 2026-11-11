<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\Auth;
use App\Http\Controllers\Customer;
use App\Http\Controllers\Driver;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PublicController;
use Illuminate\Support\Facades\Route;

/*
| Public website
*/
Route::controller(PublicController::class)->group(function () {
    Route::get('/', 'home')->name('public.home');
    Route::get('/about', 'about')->name('public.about');
    Route::get('/services', 'services')->name('public.services');
    Route::get('/contact', 'contact')->name('public.contact');
    Route::post('/quote', 'storeQuote')->name('public.quote')->middleware('throttle:5,1');
    Route::get('/track', 'track')->name('public.track');
    Route::get('/track/{ref}', 'track')->name('public.track.ref');
    Route::get('/api/track/{ref}', 'trackData')->name('public.track.data')->middleware('throttle:30,1');
});

/*
| Authentication
*/
Route::middleware('guest')->group(function () {
    Route::get('/login', [Auth\LoginController::class, 'show'])->name('login');
    Route::post('/login', [Auth\LoginController::class, 'store'])->name('login.store')->middleware('throttle:10,1');
});

Route::post('/logout', [Auth\LoginController::class, 'destroy'])->middleware('auth')->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [Auth\ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [Auth\ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [Auth\ProfileController::class, 'updatePassword'])->name('profile.password');

    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAll'])->name('notifications.readAll');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
});

/*
| Admin & operations
*/
Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:super_admin,ops_admin,doc_admin,finance'])
    ->group(function () {
        Route::get('/', [Admin\DashboardController::class, 'index'])->name('dashboard');

        // super_admin + ops_admin
        Route::middleware('role:super_admin,ops_admin')->group(function () {
            Route::resource('customers', Admin\CustomerController::class);
            Route::resource('drivers', Admin\DriverController::class)->except('show');
            Route::resource('trucks', Admin\TruckController::class)->except('show');
            Route::resource('orders', Admin\OrderController::class);
            Route::post('orders/{order}/containers', [Admin\OrderController::class, 'storeContainer'])->name('orders.containers.store');
            Route::post('containers/{container}/assign', [Admin\OrderController::class, 'assign'])->name('containers.assign');
            Route::delete('containers/{container}', [Admin\OrderController::class, 'destroyContainer'])->name('containers.destroy');
        });

        // ops + doc can move shipment status
        Route::post('containers/{container}/transition', [Admin\OrderController::class, 'transition'])
            ->middleware('role:super_admin,ops_admin,doc_admin')->name('containers.transition');

        Route::get('tracking', [Admin\TrackingController::class, 'index'])->name('tracking.index');
        Route::get('tracking/{container}', [Admin\TrackingController::class, 'show'])->name('tracking.show');

        Route::get('documents', [Admin\DocumentController::class, 'index'])->name('documents.index');
        Route::get('documents/{document}/download', [Admin\DocumentController::class, 'download'])->name('documents.download');
        Route::middleware('role:super_admin,ops_admin,doc_admin')->group(function () {
            Route::get('documents/create', [Admin\DocumentController::class, 'create'])->name('documents.create');
            Route::post('documents', [Admin\DocumentController::class, 'store'])->name('documents.store');
            Route::delete('documents/{document}', [Admin\DocumentController::class, 'destroy'])->name('documents.destroy');
        });

        // invoices: finance writes, all admin roles read
        Route::middleware('role:super_admin,finance')->group(function () {
            Route::get('invoices/create', [Admin\InvoiceController::class, 'create'])->name('invoices.create');
            Route::post('invoices', [Admin\InvoiceController::class, 'store'])->name('invoices.store');
            Route::get('invoices/{invoice}/edit', [Admin\InvoiceController::class, 'edit'])->name('invoices.edit');
            Route::put('invoices/{invoice}', [Admin\InvoiceController::class, 'update'])->name('invoices.update');
            Route::delete('invoices/{invoice}', [Admin\InvoiceController::class, 'destroy'])->name('invoices.destroy');
            Route::post('invoices/{invoice}/payments', [Admin\InvoiceController::class, 'storePayment'])->name('invoices.payments');
            Route::get('reports/finance', [Admin\ReportController::class, 'finance'])->name('reports.finance');
        });
        Route::get('invoices', [Admin\InvoiceController::class, 'index'])->name('invoices.index');
        Route::get('invoices/{invoice}', [Admin\InvoiceController::class, 'show'])->name('invoices.show');
        Route::get('invoices/{invoice}/pdf', [Admin\InvoiceController::class, 'pdf'])->name('invoices.pdf');

        Route::get('reports/operations', [Admin\ReportController::class, 'operations'])
            ->middleware('role:super_admin,ops_admin,doc_admin')->name('reports.operations');

        Route::get('quotes', [Admin\QuoteController::class, 'index'])->name('quotes.index');
        Route::patch('quotes/{quote}', [Admin\QuoteController::class, 'update'])->name('quotes.update');

        Route::middleware('role:super_admin')->group(function () {
            Route::resource('users', Admin\UserController::class)->except('show');
            Route::get('activity-logs', [Admin\UserController::class, 'logs'])->name('logs');
        });
    });

/*
| Driver portal
*/
Route::prefix('driver')->name('driver.')->middleware(['auth', 'role:driver'])->group(function () {
    Route::get('/', [Driver\TaskController::class, 'dashboard'])->name('dashboard');
    Route::get('tasks/{container}', [Driver\TaskController::class, 'show'])->name('tasks.show');
    Route::post('tasks/{container}/status', [Driver\TaskController::class, 'updateStatus'])->name('tasks.status');
    Route::post('tasks/{container}/ping', [Driver\TaskController::class, 'ping'])->name('tasks.ping');
    Route::post('tasks/{container}/documents', [Driver\TaskController::class, 'uploadDocument'])->name('tasks.documents');
    Route::post('tasks/{container}/issue', [Driver\TaskController::class, 'reportIssue'])->name('tasks.issue');
});

/*
| Customer portal
*/
Route::prefix('customer')->name('customer.')->middleware(['auth', 'role:customer'])->group(function () {
    Route::get('/', [Customer\PortalController::class, 'dashboard'])->name('dashboard');
    Route::get('orders', [Customer\PortalController::class, 'orders'])->name('orders');
    Route::get('orders/{order}', [Customer\PortalController::class, 'order'])->name('orders.show');
    Route::get('invoices', [Customer\PortalController::class, 'invoices'])->name('invoices');
    Route::get('invoices/{invoice}', [Customer\PortalController::class, 'invoice'])->name('invoice.show');
    Route::get('invoices/{invoice}/pdf', [Admin\InvoiceController::class, 'pdf'])->name('invoice.pdf');
    Route::get('documents', [Customer\PortalController::class, 'documents'])->name('documents');
    Route::get('documents/{document}/download', [Admin\DocumentController::class, 'download'])->name('documents.download');
});
