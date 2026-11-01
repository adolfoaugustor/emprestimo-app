<?php

use App\Http\Controllers\ChargeController;
use App\Http\Controllers\InstallmentController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\FinancialReportController;

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

Route::middleware(['auth', 'account.access'])->group(function () {
    Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
    Route::post('/add-investment', [App\Http\Controllers\HomeController::class, 'addInvestment'])
        ->middleware('account.owner')->name('add.investment');

    Route::get('/clients/create', [ClientController::class, 'create'])
        ->middleware('account.permission:create_clients')->name('clients.create');
    Route::post('/clients', [ClientController::class, 'store'])
        ->middleware('account.permission:create_clients')->name('clients.store');
    Route::get('/clients/{id}/edit', [ClientController::class, 'edit'])
        ->middleware('account.owner')->name('clients.edit');
    Route::put('/clients/{id}', [ClientController::class, 'update'])
        ->middleware('account.owner')->name('clients.update');
    Route::delete('/clients/{id}', [ClientController::class, 'destroy'])
        ->middleware('account.owner')->name('clients.destroy');
    Route::post('/clients/{id}/transfer', [ClientController::class, 'transfer'])
        ->middleware('account.owner')->name('clients.transfer');

    Route::get('/charges/create/{client_id}', [ChargeController::class, 'create'])
        ->middleware('account.permission:create_charges')->name('charges.create');
    Route::get('/charges/show/{client_id}', [ChargeController::class, 'show'])->name('charges.show');
    Route::post('/charges/store/{client_id}', [ChargeController::class, 'store'])
        ->middleware('account.permission:create_charges')->name('charges.store');

    Route::get('/installments/pay/{installment}', [InstallmentController::class, 'showPaymentForm'])->name('installments.showPaymentForm');
    Route::post('/installments/pay/{installment}', [InstallmentController::class, 'pay'])
        ->middleware('account.permission:record_payments')->name('installments.pay');
    Route::patch('/installments/{installment}/update-due-date', [InstallmentController::class, 'updateDueDate'])
        ->middleware('account.permission:change_due_dates')->name('installments.updateDueDate');

    Route::get('/financial-report', [FinancialReportController::class, 'index'])
        ->middleware('account.owner')->name('financial.report');

    Route::prefix('subusers')->name('subusers.')->middleware('account.owner')->group(function () {
        Route::get('/', [App\Http\Controllers\SubuserController::class, 'index'])->name('index');
        Route::get('/create', [App\Http\Controllers\SubuserController::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\SubuserController::class, 'store'])->name('store');
        Route::get('/{subuser}/edit', [App\Http\Controllers\SubuserController::class, 'edit'])->name('edit');
        Route::put('/{subuser}', [App\Http\Controllers\SubuserController::class, 'update'])->name('update');
        Route::patch('/{subuser}/status', [App\Http\Controllers\SubuserController::class, 'toggleActive'])->name('status');
        Route::post('/{subuser}/temporary-password', [App\Http\Controllers\SubuserController::class, 'resetPassword'])->name('temporary-password');
    });
});

Route::middleware(['auth', 'account.access'])->prefix('account')->name('account.')->group(function () {
    Route::get('/temporary-password', [App\Http\Controllers\TemporaryPasswordController::class, 'edit'])
        ->name('temporary-password.edit');
    Route::put('/temporary-password', [App\Http\Controllers\TemporaryPasswordController::class, 'update'])
        ->name('temporary-password.update');
});
