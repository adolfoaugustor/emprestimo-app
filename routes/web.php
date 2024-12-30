<?php

use App\Http\Controllers\ChargeController;
use App\Http\Controllers\InstallmentController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ClientController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::get('/clients/create', [ClientController::class, 'create'])->name('clients.create');
Route::post('/clients', [ClientController::class, 'store'])->name('clients.store');
Route::get('/clients/{id}/edit', [ClientController::class, 'edit'])->name('clients.edit');
Route::put('/clients/{id}', [ClientController::class, 'update'])->name('clients.update');

Route::get('/charges/create/{client_id}', [ChargeController::class, 'create'])->name('charges.create');
Route::get('/charges/show/{client_id}', [ChargeController::class, 'show'])->name('charges.show');
Route::post('/charges/store/{client_id}', [ChargeController::class, 'store'])->name('charges.store');

Route::get('/installments/pay/{installment}', [InstallmentController::class, 'showPaymentForm'])->name('installments.showPaymentForm');
Route::post('/installments/pay/{installment}', [InstallmentController::class, 'pay'])->name('installments.pay');
Route::patch('/installments/{installment}/update-due-date', [InstallmentController::class, 'updateDueDate'])->name('installments.updateDueDate');