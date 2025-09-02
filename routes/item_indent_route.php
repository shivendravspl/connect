<?php

use App\Http\Controllers\IndentController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\ItemGroupController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function(){

        Route::get('/items/export', [ItemController::class, 'exportItems'])->name('items.export');
        Route::get('/categories/export', [ItemController::class, 'exportCategories'])->name('categories.export');
        Route::resource('item-groups', ItemGroupController::class);
        Route::resource('items', ItemController::class);

        Route::delete('categories/{category}', [ItemController::class, 'destroyCategory'])->name('categories.destroy');
        Route::get('items/{item}/categories', [ItemController::class, 'getItemCategories'])->name('items.categories.get');
        Route::post('items/{item}/categories', [ItemController::class, 'updateItemCategories'])->name('items.categories.update');

        Route::post('indents/{indent}/save-header', [IndentController::class, 'saveHeaderUpdate'])->name('indents.saveHeader');
        Route::post('indents/save-header', [IndentController::class, 'saveHeader'])->name('indents.createHeader');
        
        Route::post('indents/{indent}/submit', [IndentController::class, 'submit'])->name('indents.submit');
        Route::get('indents/approval', [IndentController::class, 'approvalIndex'])->name('indents.approval.index');
        Route::get('indents/{indent}/approve', [IndentController::class, 'approvalShow'])->name('indents.approval.show');
        Route::post('indents/{indent}/approve', [IndentController::class, 'approveIndent'])->name('indents.approve');
        Route::post('indents/{indent}/reject', [IndentController::class, 'rejectIndent'])->name('indents.reject');
        Route::resource('indents', IndentController::class);
    
});