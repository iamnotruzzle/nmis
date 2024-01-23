<?php

use App\Http\Controllers\Admin\Dashboard\DashboardController as AdminDashboardController;
use App\Http\Controllers\Csr\Dashboard\DashboardController as CsrDashboardController;
use App\Http\Controllers\Csr\Inventory\Categories\CategoryController;
use App\Http\Controllers\Csr\Inventory\Categories\SubCategory\SubCategoryController;
use App\Http\Controllers\Csr\Inventory\ItemPrice\ItemPriceController;
use App\Http\Controllers\Csr\Inventory\Items\ItemController;
use App\Http\Controllers\Csr\Inventory\Reports\CsrReportsController;
use App\Http\Controllers\Csr\Inventory\Stocks\Brand\BrandController;
use App\Http\Controllers\Csr\Inventory\Stocks\CsrStocksController;
use App\Http\Controllers\Csr\Inventory\Stocks\CsrStocksMedicalSuppliesController;
use App\Http\Controllers\Csr\IssueItems\ExportIssuedItems\ExportIssuedItemsController;
use App\Http\Controllers\Csr\IssueItems\IssueItemController;
use App\Http\Controllers\Csr\CsrManualReport\CsrManualReportController;
use App\Http\Controllers\Csr\IssueTankItems\IssueTankItemsController;
use App\Http\Controllers\Csr\Reports\ReportsController;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\LocationStockBalance\LocationStockBalanceController;
use App\Http\Controllers\Reports\Csr\CsrManualReportExportController;
use App\Http\Controllers\Reports\Csr\CsrStocksMedicalSuppliesReportController;
use App\Http\Controllers\Reports\Csr\CsrStocksReportController;
use App\Http\Controllers\Reports\Csr\IssuedItems\IssuedItemsReportController;
use App\Http\Controllers\Reports\Csr\IssuedTankItems\IssuedTankItemsReportController;
use App\Http\Controllers\Reports\Ward\WardsManualReportExportController;
use App\Http\Controllers\Reports\Ward\WardStocksReportController;
use App\Http\Controllers\Users\User\ProfileController;
use App\Http\Controllers\Users\UserController;
use App\Http\Controllers\Wards\Consignment\WardConsignmentController;
use App\Http\Controllers\Wards\ConsignmentTank\WardConsignmentTankController;
use App\Http\Controllers\Wards\ConvertItem\ConvertItemController;
use App\Http\Controllers\Wards\ConvertTank\ConvertTankController;
use App\Http\Controllers\Wards\Dashboard\DashboardController as WardDashboardController;
use App\Http\Controllers\Wards\Patients\PatientChargeController;
use App\Http\Controllers\Wards\Patients\WardPatientsController;
use App\Http\Controllers\Wards\Reports\ReportController;
use App\Http\Controllers\Wards\RequestStocks\RequestStocksController;
use App\Http\Controllers\Wards\RequestStocks\WardsStocksLogs\WardsStocksLogsController;
use App\Http\Controllers\Wards\RequestTankStocks\RequestTankStocksController;
use App\Http\Controllers\Wards\Tanks\TankStocks\WardTankStocksController;
use App\Http\Controllers\Wards\TransferStock\TransferStockController;
use App\Http\Controllers\Wards\WardsManualReport\WardsManualReportController;
use App\Models\CsrStocksMedicalSupplies;
use App\Models\Location;
use App\Models\LocationStockBalance;
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
Route::resource('admindashboard', AdminDashboardController::class)->middleware(['auth:sanctum', 'verified', 'designation_admin'])->only(['index', 'store', 'update', 'destroy']);
Route::resource('users', UserController::class)->middleware(['auth:sanctum', 'verified', 'designation_admin'])->only(['index', 'store', 'update', 'destroy']);
Route::resource('profile', ProfileController::class)->middleware(['auth:sanctum', 'verified'])->only(['store']);
// end admin routes

// csr routes
Route::resource('csrdashboard', CsrDashboardController::class)->middleware(['auth:sanctum', 'verified', 'designation_csr_or_admin'])->only(['index', 'store', 'update', 'destroy']);
Route::resource('categories', CategoryController::class)->middleware(['auth:sanctum', 'verified', 'designation_csr_or_admin'])->only(['index', 'store', 'update', 'destroy']);
Route::resource('subcategories', SubCategoryController::class)->middleware(['auth:sanctum', 'verified', 'designation_csr_or_admin'])->only(['index', 'store', 'update', 'destroy']);
Route::resource('items', ItemController::class)->middleware(['auth:sanctum', 'verified', 'designation_csr_or_admin'])->only(['index', 'store', 'update', 'destroy']);
Route::resource('itemprices', ItemPriceController::class)->middleware(['auth:sanctum', 'verified', 'designation_csr_or_admin'])->only(['index', 'store', 'update', 'destroy']);
Route::resource('brands', BrandController::class)->middleware(['auth:sanctum', 'verified', 'designation_csr_or_admin'])->only(['index', 'store', 'update', 'destroy']);
Route::resource('csrstocks', CsrStocksMedicalSuppliesController::class)->middleware(['auth:sanctum', 'verified', 'designation_csr'])->only(['index', 'store', 'update', 'destroy']);
Route::resource('issueitems', IssueItemController::class)->middleware(['auth:sanctum', 'verified', 'designation_csr'])->only(['index', 'store', 'update', 'destroy']);
Route::get('issueitems/issued/', [IssuedItemsReportController::class, 'export']);
Route::put('issueitems', [IssueItemController::class, 'acknowledgedrequest'])->name('issueitems.acknowledgedrequest');
// Issue tank items
Route::resource('issuetankitems', IssueTankItemsController::class)->middleware(['auth:sanctum', 'verified', 'designation_csr'])->only(['index', 'store', 'update', 'destroy']);
Route::get('issuetankitems/issued/', [IssuedTankItemsReportController::class, 'export']);
Route::put('issuetankitems', [IssueTankItemsController::class, 'acknowledgedrequest'])->name('issuetankitems.acknowledgedrequest');

Route::resource('csrreports', ReportsController::class)->middleware(['auth:sanctum', 'verified', 'designation_csr'])->only(['index']);
Route::resource('csrmanualreports', CsrManualReportController::class)->middleware(['auth:sanctum', 'verified', 'designation_csr'])->only(['index', 'store', 'update', 'destroy']);
Route::get('csrstocks/export/', [CsrStocksMedicalSuppliesReportController::class, 'export']);
Route::get('csrmanualreports/export/', [CsrManualReportExportController::class, 'export']);
Route::resource('stockbal', LocationStockBalanceController::class)->middleware(['auth:sanctum', 'verified'])->only(['index', 'store', 'update', 'destroy']);
// Route::resource('csrstocks/export/', [CsrStocksReportController::class, 'export'])->only(['index']);
// end csr routes


// ward routes
Route::resource('warddashboard', WardDashboardController::class)->middleware(['auth:sanctum', 'verified', 'designation_ward'])->only(['index', 'store', 'update', 'destroy']);
// medical supplies
Route::resource('requeststocks', RequestStocksController::class)->middleware(['auth:sanctum', 'verified', 'designation_ward'])->only(['index', 'store', 'update', 'destroy']);
Route::put('requeststocks', [RequestStocksController::class, 'updatedeliverystatus'])->name('requeststocks.updatedeliverystatus');
// end medical supplies

// tanks
Route::resource('requesttankstocks', RequestTankStocksController::class)->middleware(['auth:sanctum', 'verified', 'designation_ward'])->only(['index', 'store', 'update', 'destroy']);
Route::put('requesttankstocks', [RequestTankStocksController::class, 'updatedeliverystatus'])->name('requesttankstocks.updatedeliverystatus');
Route::resource('wardtankstocks', WardTankStocksController::class)->middleware(['auth:sanctum', 'verified', 'designation_ward'])->only(['index', 'store', 'update', 'destroy']);
// end tanks

Route::resource('convertitem', ConvertItemController::class)->middleware(['auth:sanctum', 'verified', 'designation_ward'])->only(['index', 'store', 'update', 'destroy']);
Route::resource('converttank', ConvertTankController::class)->middleware(['auth:sanctum', 'verified', 'designation_ward'])->only(['index', 'store', 'update', 'destroy']);
Route::resource('consignment', WardConsignmentController::class)->middleware(['auth:sanctum', 'verified', 'designation_ward'])->only(['index', 'store', 'update', 'destroy']);
Route::resource('consignmenttank', WardConsignmentTankController::class)->middleware(['auth:sanctum', 'verified', 'designation_ward'])->only(['index', 'store', 'update', 'destroy']);
Route::resource('wardreports', ReportController::class)->middleware(['auth:sanctum', 'verified', 'designation_ward'])->only(['index']);
Route::get('wardstocks/export/', [WardStocksReportController::class, 'export']);
Route::resource('wardsmanualreports', WardsManualReportController::class)->middleware(['auth:sanctum', 'verified', 'designation_ward'])->only(['index', 'store', 'update', 'destroy']);
Route::get('wardsmanualreports/export/', [WardsManualReportExportController::class, 'export']);

Route::resource('wardspatients', WardPatientsController::class)->middleware(['auth:sanctum', 'verified', 'designation_ward'])->only(['index', 'store', 'update', 'destroy']);
Route::resource('patientcharge', PatientChargeController::class)->middleware(['auth:sanctum', 'verified', 'designation_ward'])->only(['index', 'store', 'update', 'destroy']);
Route::resource('wardsstockslogs', WardsStocksLogsController::class)->middleware(['auth:sanctum', 'verified', 'designation_ward'])->only(['index', 'store', 'update', 'destroy']);

Route::resource('transferstock', TransferStockController::class)->middleware(['auth:sanctum', 'verified', 'designation_ward'])->only(['index', 'store', 'update', 'destroy']);
Route::put('transferstock', [TransferStockController::class, 'updatetransferstatus', 'designation_ward'])->name('transferstock.updatetransferstatus');
// end ward routes
