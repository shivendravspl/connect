<?php

use App\Http\Controllers\PurchaseOrderController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::post('purchase-orders/draft', [PurchaseOrderController::class, 'draft'])->name('purchase-orders.draft');
    Route::get('purchase-orders/get-indent-items', [PurchaseOrderController::class, 'getIndentItems'])->name('purchase-orders.get-indent-items');
    Route::resource('purchase-orders', PurchaseOrderController::class)->except(['create']);
});