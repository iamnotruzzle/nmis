<?php

use App\Http\Controllers\Csr\Inventory\Categories\CategoryController;
use App\Http\Controllers\Csr\Inventory\ItemPrice\ItemPriceController;
use App\Http\Controllers\Csr\Inventory\Items\ItemController;
use App\Http\Controllers\Csr\Inventory\Reports\CsrReportsController;
use App\Http\Controllers\Csr\Inventory\Stocks\Brand\BrandController;
use App\Http\Controllers\Csr\Inventory\Stocks\CsrStocksController;
use App\Http\Controllers\Csr\IssueItems\IssueItemController;
use App\Http\Controllers\Csr\Reports\ReportsController;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\Reports\Csr\CsrStocksReportController;
use App\Http\Controllers\Users\UserController;
use App\Http\Controllers\Wards\Consignment\WardConsignmentController;
use App\Http\Controllers\Wards\Patients\PatientChargeController;
use App\Http\Controllers\Wards\Patients\WardPatientsController;
use App\Http\Controllers\Wards\RequestStocks\RequestStocksController;
use App\Http\Controllers\Wards\RequestStocks\WardsStocksLogs\WardsStocksLogsController;
use App\Http\Controllers\Wards\TransferStock\TransferStockController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|/
*/

Route::redirect('/', 'login');

Route::resource('dashboard', DashboardController::class)->middleware(['auth:sanctum', 'verified'])->only(['index']);

// admin routes
Route::resource('users', UserController::class)->middleware(['auth:sanctum', 'verified', 'designation_admin'])->only(['index', 'store', 'update', 'destroy']);
// end admin routes

// csr routes
Route::resource('categories', CategoryController::class)->middleware(['auth:sanctum', 'verified', 'designation_csr_or_admin'])->only(['index', 'store', 'update', 'destroy']);
Route::resource('items', ItemController::class)->middleware(['auth:sanctum', 'verified', 'designation_csr_or_admin'])->only(['index', 'store', 'update', 'destroy']);
Route::resource('itemprices', ItemPriceController::class)->middleware(['auth:sanctum', 'verified', 'designation_csr_or_admin'])->only(['index', 'store', 'update', 'destroy']);
Route::resource('brands', BrandController::class)->middleware(['auth:sanctum', 'verified', 'designation_csr_or_admin'])->only(['index', 'store', 'update', 'destroy']);
Route::resource('csrstocks', CsrStocksController::class)->middleware(['auth:sanctum', 'verified', 'designation_csr'])->only(['index', 'store', 'update', 'destroy']);
Route::resource('issueitems', IssueItemController::class)->middleware(['auth:sanctum', 'verified', 'designation_csr'])->only(['index', 'store', 'update', 'destroy']);
Route::resource('csrreports', ReportsController::class)->middleware(['auth:sanctum', 'verified', 'designation_csr'])->only(['index']);
// end csr routes


// ward routes
Route::resource('requeststocks', RequestStocksController::class)->middleware(['auth:sanctum', 'verified', 'designation_ward'])->only(['index', 'store', 'update', 'destroy']);
Route::put('requeststocks', [RequestStocksController::class, 'updatedeliverystatus'])->name('requeststocks.updatedeliverystatus');
Route::resource('consignment', WardConsignmentController::class)->middleware(['auth:sanctum', 'verified', 'designation_ward'])->only(['index', 'store', 'update', 'destroy']);

Route::resource('wardspatients', WardPatientsController::class)->middleware(['auth:sanctum', 'verified', 'designation_ward'])->only(['index', 'store', 'update', 'destroy']);
Route::resource('patientcharge', PatientChargeController::class)->middleware(['auth:sanctum', 'verified', 'designation_ward'])->only(['index', 'store', 'update', 'destroy']);
Route::resource('wardsstockslogs', WardsStocksLogsController::class)->middleware(['auth:sanctum', 'verified', 'designation_ward'])->only(['index', 'store', 'update', 'destroy']);

Route::resource('transferstock', TransferStockController::class)->middleware(['auth:sanctum', 'verified', 'designation_ward'])->only(['index', 'store', 'update', 'destroy']);
Route::put('transferstock', [TransferStockController::class, 'updatetransferstatus', 'designation_ward'])->name('transferstock.updatetransferstatus');
// end ward routes


Route::get('csrstocks/export/', [CsrStocksReportController::class, 'export']);
