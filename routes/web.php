<?php

use App\Http\Controllers\Admin\Dashboard\DashboardController as AdminDashboardController;
use App\Http\Controllers\Authentication\AuthenticationController;
use App\Http\Controllers\Csr\Dashboard\DashboardController as CsrDashboardController;
use App\Http\Controllers\Csr\Inventory\Categories\CategoryController;
use App\Http\Controllers\public\CsrStocksController as PubicCsrStocks;
use App\Http\Controllers\Csr\Inventory\Categories\SubCategory\SubCategoryController;
use App\Http\Controllers\Csr\Inventory\ItemPrice\ItemPriceController;
use App\Http\Controllers\Csr\Inventory\Items\ItemController;
use App\Http\Controllers\Csr\Inventory\Reports\CsrReportsController;
use App\Http\Controllers\Csr\Inventory\Stocks\CsrStocksController;
use App\Http\Controllers\Csr\Inventory\Stocks\CsrStocksControllers;
use App\Http\Controllers\Csr\IssueItems\ExportIssuedItems\ExportIssuedItemsController;
use App\Http\Controllers\Csr\IssueItems\IssueItemController;
use App\Http\Controllers\Csr\CsrManualReport\CsrManualReportController;
use App\Http\Controllers\Csr\Inventory\CsrConvertItem\CsrConvertItemController;
use App\Http\Controllers\Csr\Inventory\ItemConversion\CsrItemConversionController;
use App\Http\Controllers\Csr\IssueItems\MedicalGasController;
use App\Http\Controllers\Csr\Reports\ReportsController;
use App\Http\Controllers\Csr\ReturnedItems\ReturnedItemsController;
use App\Http\Controllers\Csr\Utility\ManualAddStocks\ManualAddStocksController;
use App\Http\Controllers\Csr\WardsInventory\WardsInventoryController;
use App\Http\Controllers\CsrLocationStockBalance\CsrLocationStockBalanceController;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\LocationStockBalance\LocationStockBalanceController;
use App\Http\Controllers\Reports\Csr\CsrManualReportExportController;
use App\Http\Controllers\Reports\Csr\CsrStocksReportController;
use App\Http\Controllers\Reports\Csr\IssuedItems\IssuedItemsReportController;
use App\Http\Controllers\Reports\Ward\WardsManualReportExportController;
use App\Http\Controllers\Reports\Ward\WardStocksReportController;
use App\Http\Controllers\Tools\GenericVariant\GenericVariantController;
use App\Http\Controllers\Tools\Packages\PackageController;
use App\Http\Controllers\Users\User\ProfileController;
use App\Http\Controllers\Users\UserController;
use App\Http\Controllers\Wards\Census\ECG\ECGController;
use App\Http\Controllers\Wards\Consignment\ConsignmentController;
use App\Http\Controllers\Wards\CsrInventory\CsrInventoryController;
use App\Http\Controllers\Wards\MedicalGases\WardMedicalGasesController;
use App\Http\Controllers\Wards\Dashboard\DashboardController as WardDashboardController;
use App\Http\Controllers\Wards\Delivery\DeliveryController;
use App\Http\Controllers\Wards\ExistingStock\ExistingStockController;
use App\Http\Controllers\Wards\Faq\FaqController;
use App\Http\Controllers\Wards\Inventory\InventoryController;
use App\Http\Controllers\Wards\ManualInventory\WardManualInventoryController;
use App\Http\Controllers\Wards\Patients\PatientChargeController;
use App\Http\Controllers\Wards\Patients\WardPatientsController;
use App\Http\Controllers\Wards\Patients\WardPatientSearchController;
use App\Http\Controllers\Wards\ReOrderLevel\ReOrderLevelController;
use App\Http\Controllers\Wards\Reports\ReportController;
use App\Http\Controllers\Wards\RequestStocks\RequestStocksController;
use App\Http\Controllers\Wards\RequestStocks\RequestStocksLogs\RequestStocksLogsController;
use App\Http\Controllers\Wards\Supplemental\SupplementalControler;
use App\Http\Controllers\Wards\TransferStock\TransferStockController;
use App\Http\Controllers\Wards\WaEndorsement\WaEndorsementController;
use App\Models\CsrStockBalance;
use App\Models\WaEndorsement;
use Illuminate\Support\Facades\Route;

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

Route::get('wardslocation', [AuthenticationController::class, 'wardslocation'])
    ->name('wardslocation');

// Route::middleware(['auth:sanctum'])->group(
// Route::middleware(['web', 'auth', 'verified', 'check.active.login'])->group(
Route::middleware(['web', 'auth', 'verified'])->group(
    function () {
        // admin routes
        Route::resource('admindashboard', AdminDashboardController::class)->middleware(['verified', 'designation_admin'])->only(['index', 'store', 'update', 'destroy']);
        Route::resource('users', UserController::class)->middleware(['verified', 'designation_admin'])->only(['index', 'store', 'update', 'destroy']);
        Route::resource('profile', ProfileController::class)->middleware(['verified'])->only(['store']);
        // end admin routes

        // csr routes
        Route::resource('csrdashboard', CsrDashboardController::class)->middleware(['verified', 'designation_csr_or_admin'])->only(['index', 'store', 'update', 'destroy']);
        Route::resource('categories', CategoryController::class)->middleware(['verified', 'designation_csr_or_admin'])->only(['index', 'store', 'update', 'destroy']);
        Route::resource('subcategories', SubCategoryController::class)->middleware(['verified', 'designation_csr_or_admin'])->only(['index', 'store', 'update', 'destroy']);
        Route::resource('items', ItemController::class)->middleware(['verified', 'designation_csr_or_admin'])->only(['index', 'store', 'update', 'destroy']);
        Route::resource('itemprices', ItemPriceController::class)->middleware(['verified', 'designation_csr_or_admin'])->only(['index', 'store', 'update', 'destroy']);
        Route::resource('csrconvertitem', CsrConvertItemController::class)->middleware(['verified', 'designation_csr_or_admin'])->only(['index', 'store', 'update', 'destroy']);
        Route::resource('csrconvertdelivery', CsrItemConversionController::class)->middleware(['verified', 'designation_csr_or_admin'])->only(['index', 'store', 'update', 'destroy']);
        Route::resource('csrstocks', CsrStocksControllers::class)->middleware(['verified', 'designation_csr'])->only(['index', 'store', 'update', 'destroy']);
        Route::resource('returneditems', ReturnedItemsController::class)->middleware(['verified', 'designation_csr'])->only(['index', 'store', 'update', 'destroy']);
        Route::resource('csrmanualadd', ManualAddStocksController::class)->middleware(['verified', 'designation_csr'])->only(['index', 'store', 'update', 'destroy']);
        Route::resource('issueitems', IssueItemController::class)->middleware(['verified', 'designation_csr'])->only(['index', 'store', 'update', 'destroy']);
        Route::resource('medicalgastoward', MedicalGasController::class)->middleware(['verified', 'designation_csr'])->only(['index', 'store', 'update', 'destroy']);
        Route::resource('wardsinv', WardsInventoryController::class)->middleware(['verified', 'designation_csr_or_admin'])->only(['index', 'store', 'update', 'destroy']);
        Route::get('issueitems/issued/', [IssuedItemsReportController::class, 'export']);
        Route::put('issueitems', [IssueItemController::class, 'acknowledgedrequest'])->name('issueitems.acknowledgedrequest');

        Route::resource('csrreports', ReportsController::class)->middleware(['verified', 'designation_csr'])->only(['index']);
        Route::get('csrstocks/export/', [CsrStocksReportController::class, 'export']);
        Route::resource('stockbal', LocationStockBalanceController::class)->middleware(['verified', 'designation_ward'])->only(['index', 'store', 'update', 'destroy']);
        Route::resource('csrstockbal', CsrLocationStockBalanceController::class)->middleware(['verified', 'designation_csr'])->only(['index', 'store', 'update', 'destroy']);
        Route::resource('risdetails', CsrDashboardController::class)->middleware(['verified', 'designation_csr_or_admin'])->only(['index', 'show']);
        // end csr routes


        // ward routes
        // Route::resource('warddashboard', WardDashboardController::class)->middleware(['verified', 'designation_ward'])->only(['index', 'store', 'update', 'destroy']);
        Route::middleware(['verified', 'designation_ward'])->group(function () {
            Route::resource('warddashboard', WardDashboardController::class)
                ->only(['index', 'store', 'update', 'destroy']);

            // New route for AJAX call to get top diagnoses
            Route::get('warddashboard/top-diagnoses', [WardDashboardController::class, 'topDiagnosesData'])
                ->name('warddashboard.topDiagnoses');
            Route::get('warddashboard/top-ten-items', [WardDashboardController::class, 'topTenItemsData'])
                ->name('warddashboard.topTenItems');

            Route::get('warddashboard/monthly-charge', [WardDashboardController::class, 'monthlyCharge'])
                ->name('warddashboard.monthlyCharge');

            Route::get('warddashboard/charges', [WardDashboardController::class, 'charges'])
                ->name('warddashboard.charges');
        });
        // non-medicine supplies
        Route::resource('requeststocks', RequestStocksController::class)->middleware(['verified', 'designation_ward'])->only(['index', 'store', 'update', 'destroy']);
        Route::put('requeststocks', [RequestStocksController::class, 'updatedeliverystatus'])->name('requeststocks.updatedeliverystatus');
        Route::get('requeststocks/view-reorder', [RequestStocksController::class, 'viewItemReOrderQuantity'])->name('requeststocks.view-reorder');
        Route::get('wardinv/getItems', [InventoryController::class, 'getItems'])
            ->name('wardinv/getItems')
            ->middleware(['verified', 'designation_ward']);
        Route::resource('wardinv', InventoryController::class)->middleware(['verified', 'designation_ward'])->only(['index', 'store', 'update', 'destroy']);
        // end non-medicine supplies


        Route::get('getCsrInventory', [CsrInventoryController::class, 'getCsrInventory'])
            ->name('getCsrInventory')
            ->middleware(['verified', 'designation_ward']);
        Route::get('getCurrentStocks', [CsrInventoryController::class, 'getCurrentStocks'])
            ->name('getCurrentStocks')
            ->middleware(['verified', 'designation_ward']);
        Route::resource('csrinv', CsrInventoryController::class)->middleware(['verified', 'designation_ward'])->only(['index', 'store', 'update', 'destroy']);

        Route::resource('medicalGases', WardMedicalGasesController::class)->middleware(['verified', 'designation_ward'])->only(['index', 'store', 'update', 'destroy']);
        Route::resource('consignment', ConsignmentController::class)->middleware(['verified', 'designation_ward'])->only(['index', 'store', 'update', 'destroy']);
        Route::resource('supplemental', SupplementalControler::class)->middleware(['verified', 'designation_ward'])->only(['index', 'store', 'update', 'destroy']);
        Route::resource('delivery', DeliveryController::class)->middleware(['verified', 'designation_ward'])->only(['index', 'store', 'update', 'destroy']);
        Route::resource('existingstock', ExistingStockController::class)->middleware(['verified', 'designation_ward'])->only(['index', 'store', 'update', 'destroy']);
        Route::resource('wardreports', ReportController::class)->middleware(['verified', 'designation_ward'])->only(['index']);
        Route::get('wardstocks/export/', [WardStocksReportController::class, 'export']);
        Route::resource('ecgreports', ECGController::class)->middleware(['verified', 'designation_ward'])->only(['index', 'store', 'update', 'destroy']);

        Route::resource('wardspatients', WardPatientsController::class)->middleware(['verified', 'designation_ward'])->only(['index', 'store', 'update', 'destroy']);
        Route::resource('wardspatientsearch', WardPatientSearchController::class)->middleware(['verified', 'designation_ward'])->only(['index', 'store', 'update', 'destroy']);
        Route::get('getWardMedSupplies', [PatientChargeController::class, 'getWardMedSupplies'])
            ->name('getWardMedSupplies')
            ->middleware(['verified', 'designation_ward']);
        Route::get('getPackages', [PatientChargeController::class, 'getPackages'])
            ->name('getPackages')
            ->middleware(['verified', 'designation_ward']);
        Route::get('getGenericVariant', [PatientChargeController::class, 'getGenericVariant'])
            ->name('getGenericVariant')
            ->middleware(['verified', 'designation_ward']);
        Route::get('getMisc', [PatientChargeController::class, 'getMisc'])
            ->name('getMisc')
            ->middleware(['verified', 'designation_ward']);
        Route::resource('patientcharge', PatientChargeController::class)->middleware(['verified', 'designation_ward'])->only(['index', 'store', 'update', 'destroy']);
        Route::resource('wardsstockslogs', RequestStocksLogsController::class)->middleware(['verified', 'designation_ward'])->only(['index', 'store', 'update', 'destroy']);

        Route::resource('wa-endorse', WaEndorsementController::class)->middleware(['verified', 'designation_ward'])->only(['index', 'store', 'update', 'destroy']);


        Route::get('getWardStocks', [TransferStockController::class, 'getWardStocks'])
            ->name('getWardStocks')
            ->middleware(['verified', 'designation_ward']);
        Route::get('getTransferredStocks', [TransferStockController::class, 'getTransferredStocks'])
            ->name('getTransferredStocks')
            ->middleware(['verified', 'designation_ward']);
        Route::get('getEmployees', [TransferStockController::class, 'getEmployees'])
            ->name('getEmployees')
            ->middleware(['verified', 'designation_ward']);
        Route::get('getWards', [TransferStockController::class, 'getWards'])
            ->name('getWards')
            ->middleware(['verified', 'designation_ward']);
        Route::resource('transferstock', TransferStockController::class)->middleware(['verified', 'designation_ward'])->only(['index', 'store', 'update', 'destroy']);
        Route::put('transferstock', [TransferStockController::class, 'updatetransferstatus', 'designation_ward'])->name('transferstock.updatetransferstatus');

        Route::resource('faq', FaqController::class)->middleware(['verified', 'designation_ward'])->only(['index', 'store', 'update', 'destroy']);

        Route::resource('reorder', ReOrderLevelController::class)->middleware(['verified', 'designation_ward'])->only(['index', 'store', 'update', 'destroy']);
        // end ward routes

        // tools
        Route::get('getItems', [PackageController::class, 'getItems'])
            ->name('getItems')
            ->middleware(['verified', 'designation_ward']);
        Route::get('getPackages', [PackageController::class, 'getPackages'])
            ->name('getPackages')
            ->middleware(['verified', 'designation_ward']);
        Route::resource('packages', PackageController::class)->middleware(['verified'])->only(['index', 'store', 'update', 'destroy']);
        Route::resource('generic-variants', GenericVariantController::class)->middleware(['verified', 'designation_csr'])->only(['index', 'store', 'update', 'destroy']);
    }
);

Route::resource('public/csrinventory', PubicCsrStocks::class)->only(['index']);
Route::get('api/inventory', [PubicCsrStocks::class, 'fetchInventory']);
