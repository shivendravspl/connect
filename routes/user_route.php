<?php

use App\Http\Controllers\Roles_Permission\PermissionController;
use App\Http\Controllers\Roles_Permission\RoleController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// User Routes (restricted to list-user permission)
Route::middleware('permission:list-user')->group(function () {
	Route::resource('users', UserController::class);
	Route::post('getUserList', [UserController::class, 'getUserList'])->name('getUserList');
	Route::post('/users/export', [UserController::class, 'export'])->name('users.export');
	Route::put('/users/{user}/password', [UserController::class, 'changePassword'])->name('users.password');
	Route::get('user/{user_id}/permission', [UserController::class, 'give_permission'])->name('give_permission');
	Route::post('user/{user_id}/permission', [UserController::class, 'set_user_permission'])->name('set_user_permission');
});
Route::middleware('auth')->group(function () {
	// Role and Permission Routes (restricted to list-role permission)
	Route::middleware('permission:list-role')->group(function () {
		Route::resource('roles', RoleController::class);
		Route::get('permission', [PermissionController::class, 'index'])->name('permission');
	});
});
