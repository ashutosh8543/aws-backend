<?php
use App\Http\Controllers\API\AdjustmentAPIController;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\BaseUnitAPIController;
use App\Http\Controllers\API\BrandAPIController;
use App\Http\Controllers\API\CouponCodeAPIController;
use App\Http\Controllers\API\CurrencyAPIController;
use App\Http\Controllers\API\CustomerAPIController;
use App\Http\Controllers\API\DashboardAPIController;
use App\Http\Controllers\API\ExpenseAPIController;
use App\Http\Controllers\API\ExpenseCategoryAPIController;
use App\Http\Controllers\API\HoldAPIController;
use App\Http\Controllers\API\LanguageAPIController;
use App\Http\Controllers\API\MainProductAPIController;
use App\Http\Controllers\API\ManageStockAPIController;
use App\Http\Controllers\API\PermissionController;
use App\Http\Controllers\API\POSRegisterAPIController;
use App\Http\Controllers\API\ProductAPIController;
use App\Http\Controllers\API\ProductCategoryAPIController;
use App\Http\Controllers\API\PurchaseAPIController;
use App\Http\Controllers\API\PurchaseReturnAPIController;
use App\Http\Controllers\API\QuotationAPIController;
use App\Http\Controllers\API\ReportAPIController;
use App\Http\Controllers\API\RoleAPIController;
use App\Http\Controllers\API\SaleAPIController;
use App\Http\Controllers\API\SaleReturnAPIController;
use App\Http\Controllers\API\SalesPaymentAPIController;
use App\Http\Controllers\API\SettingAPIController;
use App\Http\Controllers\API\SmsSettingAPIController;
use App\Http\Controllers\API\SmsTemplateAPIController;
use App\Http\Controllers\API\SupplierAPIController;
use App\Http\Controllers\API\TransferAPIController;
use App\Http\Controllers\API\UnitAPIController;
use App\Http\Controllers\API\UserAPIController;
use App\Http\Controllers\API\WarehouseAPIController;
use App\Http\Controllers\API\VariationAPIController;
use App\Http\Controllers\MailTemplateAPIController;
use App\Http\Controllers\API\GiftController;
use App\Http\Controllers\API\InventoryController;
use App\Http\Controllers\API\ChanelController;
use App\Http\Controllers\API\AreaController;
use App\Http\Controllers\API\AssignCustomerController;
use App\Http\Controllers\API\LoadUnloadController;
use App\Http\Controllers\API\AssignedGiftController;
use App\Http\Controllers\API\SurveyController;
use App\Http\Controllers\API\GiftInvetoryController;
use App\Http\Controllers\API\LanguageContentController;
use App\Http\Controllers\API\ProductInventoryController;
use App\Http\Controllers\API\NotificationTemplateController;
use App\Http\Controllers\API\AdminNotificationController;
use App\Http\Controllers\API\SubAreaController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

//Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//    return $request->user();
//});

Route::get('show-users/{roleId}', [UserAPIController::class, 'showUsersByRole']);
Route::get('show-products', [ProductAPIController::class, 'showProduct']);
Route::get('config', [UserAPIController::class, 'config']);

Route::middleware('auth:api')->group(function () {
    //    Route::middleware('permission:manage_brands')->group(function () {
    Route::post('/brands', [BrandAPIController::class, 'store']);
    Route::get('/brands/{id}', [BrandAPIController::class, 'show'])->name('brands.show');
    Route::post('/brands/{id}', [BrandAPIController::class, 'update']);
    Route::delete('/brands/{brand}', [BrandAPIController::class, 'destroy']);
    //    });
    Route::get('/brands', [BrandAPIController::class, 'index']);


    // API FOR UPDATE PRICE DISTRIBUTOR
    Route::post('update-qunatity', [InventoryController::class, 'update']);
    Route::post('update-quantity-warehouse', [InventoryController::class, 'quantityUpdate']);
    Route::get('distributors/{productId}', [InventoryController::class,
    'getDistributorsByProductId']);
    Route::get('distributor-prices/{distributorId}/{productId}', [InventoryController::class, 'getDistributorPrices']);

    // channels api
    Route::get('chanels', [InventoryController::class, 'show']);
    Route::post('price-inventories', [InventoryController::class, 'store']);

    //Dashboard
    Route::get('today-sales-purchases-count', [DashboardAPIController::class, 'getPurchaseSalesCounts']);
    Route::get('all-sales-purchases-count', [DashboardAPIController::class, 'getAllPurchaseSalesCounts']);
    Route::get('recent-sales', [DashboardAPIController::class, 'getRecentSales']);
    Route::get('top-selling-products', [DashboardAPIController::class, 'getTopSellingProducts']);
    Route::get('week-selling-purchases', [DashboardAPIController::class, 'getWeekSalePurchases']);
    Route::get('yearly-top-selling', [DashboardAPIController::class, 'getYearlyTopSelling']);
    Route::get('top-customers', [DashboardAPIController::class, 'getTopCustomer']);
    // Route::get('stock-alerts', [DashboardAPIController::class, 'stockAlerts']);
    Route::get('all-collection-list', [DashboardAPIController::class, 'SalesmanCollectionList']);

    // get all permission
    Route::get('/permissions', [PermissionController::class, 'getPermissions'])->name('get-permissions');

    // roles route
    //    Route::middleware('permission:manage_roles')->group(function () {
    //using this on front
    Route::resource('roles', RoleAPIController::class);
    //    });
    Route::get('roles', [RoleAPIController::class, 'index']);

    Route::get('/chanels',[ChanelController::class,'index']);
    Route::post('/add-chanels',[ChanelController::class,'AddChanels']);
    Route::get('/fetch-chanel/{id}', [ChanelController::class, 'fetchChanel']);
    Route::post('/edit-chanel',[ChanelController::class,'EditChanel']);
    Route::get('/delete-chanel/{id}',[ChanelController::class,'DeleteChanel']);


    Route::get('/chanels-list',[ChanelController::class,'ChannelList']);

    Route::get('/areas',[AreaController::class,'index']);
    Route::post('/add-area',[AreaController::class,'AddArea']);
    Route::get('/fetch-area/{id}', [AreaController::class, 'fetchArea']);
    Route::post('/edit-area',[AreaController::class,'EditArea']);
    Route::get('/delete-area/{id}',[AreaController::class,'DeleteArea']);
    Route::get('/area-list',[AreaController::class,'AreaList']);

    // Routes for sub area
    Route::post('sub-area-add', [SubAreaController::class, 'store']);
    Route::get('sub-areas', [SubAreaController::class,  'index']);
    Route::get('sub-area/{id}', [SubAreaController::class, 'show']);
    Route::put('update-subArea/{id}', [SubAreaController::class, 'updateSubAreaName']);
    Route::get('sub-area-list', [SubAreaController::class, 'subAreaList']);


    //Route for product inventories
    Route::get('stock-alerts', [DashboardAPIController::class, 'remainingStockAlert']);


    // product category route
    //    Route::middleware('permission:manage_product_categories')->group(function () {
    Route::resource('product-categories', ProductCategoryAPIController::class);
    Route::post(
        'product-categories/{product_category}',
        [ProductCategoryAPIController::class, 'update']
    )->name('product-category');
    //    });

    Route::get('product-categories', [ProductCategoryAPIController::class, 'index']);

    //    Route::middleware('permission:manage_currency')->group(function () {
    Route::resource('currencies', CurrencyAPIController::class);
    //    });
    Route::get('currencies', [CurrencyAPIController::class, 'index']);

    // warehouses route
    //    Route::middleware('permission:manage_warehouses')->group(function () {
    Route::resource('warehouses', WarehouseAPIController::class);
    Route::get('warehouse-details/{id}', [WarehouseAPIController::class, 'warehouseDetails']);
    Route::get('warehouse-list', [WarehouseAPIController::class, 'warehouseList']);
    //    });
    // Route::get('warehouses', [WarehouseAPIController::class, 'index']);

    // units route
    //    Route::middleware('permission:manage_units')->group(function () {
    Route::resource('units', UnitAPIController::class);
    Route::resource('base-units', BaseUnitAPIController::class);
    //    });
    Route::get('units', [UnitAPIController::class, 'index']);

    // products route

    Route::resource('products', ProductAPIController::class);
    Route::resource('main-products', MainProductAPIController::class);
    Route::post(
        'products/{product}',
        [ProductAPIController::class, 'update']
    );
    Route::post(
        'main-products/{product}',
        [MainProductAPIController::class, 'update']
    );
    Route::delete(
        'products-image-delete/{mediaId}',
        [ProductAPIController::class, 'productImageDelete']
    )->name('products-image-delete');

    Route::get('main-products-all', [MainProductAPIController::class, 'mainProductsAll']);



    Route::get('get-products-details/{id}',[ProductAPIController::class, 'getProductsDetails']);
    Route::get('product-details/{id}', [ProductAPIController::class, 'productDetail']);


    // assign customer for salesman
    Route::post('assign-customers', [AssignCustomerController::class, 'assignCustomers']);
    Route::get('assigned-customer', [AssignCustomerController::class, 'index']);
    Route::get('all-salesman', [AssignCustomerController::class, 'getAllSalesman']);
    Route::get('fetch-single-assigned-customer/{id}', [AssignCustomerController::class, 'fetchSingleSssignedCustomer']);
    //for traking
    Route::get('fetch-single-assigned-customer-salesman/{id}', [AssignCustomerController::class, 'fetchSingleSssignedCustomerSalesman']);
    Route::get('all-assigned-salesman', [AssignCustomerController::class, 'allAssignedSalesman']);


    // Route for assigned load product
    Route::get('assigned-load-product', [LoadUnloadController::class, 'index']);
    Route::get('stock-out-product-list', [LoadUnloadController::class, 'stockOutList']);
    Route::post('stock-out-product', [LoadUnloadController::class, 'stockOutProduct']);




    Route::get('products', [ProductAPIController::class, 'index']);
    Route::get('get-all-products', [ProductAPIController::class, 'getAllProducts']);

    Route::resource('variations', VariationAPIController::class);

    //    Route::middleware('permission:manage_transfers')->group(function () {
    Route::resource('transfers', TransferAPIController::class);
    //    });

    Route::post('import-products', [ProductAPIController::class, 'importProducts']);
    Route::post('import-customers', [CustomerAPIController::class, 'importCustomers']);

    Route::get(
        'products-export-excel/{id?}',
        [ProductAPIController::class, 'getProductExportExcel']
    )->name('products-export-excel');

    Route::resource('transfers', TransferAPIController::class);

    // customers route
    //    Route::middleware('permission:manage_customers')->group(function () {
    Route::resource('customers', CustomerAPIController::class);
    // rotue to get customer details
    // Route::get('customers/{id}', [CustomerAPIController::class, 'detail']);
    //    });

    // Route::get('customers', [CustomerAPIController::class, 'index']);


    //For Mobile App

    Route::resource('outlets', CustomerAPIController::class);








    //Users route
    //    Route::middleware('permission:manage_users')->group(function () {
    Route::resource('users', UserAPIController::class);
    Route::post('users/{user}', [UserAPIController::class, 'update']);

    Route::get('distributors', [UserAPIController::class,'DistributorList'])->name('distributor-list');
    Route::get('supervisors', [UserAPIController::class,'SupervisorList'])->name('supervisor-list');
    Route::get('salesmans', [UserAPIController::class,'SalesmansList'])->name('salesmans-list');
    Route::get('distributor-list', [UserAPIController::class,  'DistributorsList']);
    Route::get('show-salesman', [UserAPIController::class,  'showSalesMan']);
    Route::get('supervisor-list', [UserAPIController::class,  'SupervisorListAll']);

    //sales man for load pos
    // Route::get('SALESMAN_LIST', [UserAPIController::class,'SalesmansList'])->name('salesmans-list');





    //    });
    // update user profile
    Route::get('edit-profile', [UserAPIController::class, 'editProfile'])->name('edit-profile');
    Route::post('update-profile', [UserAPIController::class, 'updateProfile'])->name('update-profile');
    Route::patch('/change-password', [UserAPIController::class, 'changePassword'])->name('user.changePassword');

    //    Route::middleware('permission:manage_suppliers')->group(function () {
    Route::resource('suppliers', SupplierAPIController::class);
    //    });
    Route::get('suppliers', [SupplierAPIController::class, 'index']);
    Route::post('import-suppliers', [SupplierAPIController::class, 'importSuppliers']);

    //sale
    //    Route::middleware('permission:manage_sale')->group(function () {
    Route::resource('sales', SaleAPIController::class);
    Route::get('sale-pdf-download/{sale}', [SaleAPIController::class, 'pdfDownload'])->name('sale-pdf-download');
    Route::get('sale-info/{sale}', [SaleAPIController::class, 'saleInfo'])->name('sale-info');
    Route::post('sales/{sale}/capture-payment', [SalesPaymentAPIController::class, 'createSalePayment']);
    Route::get('sales/{sale}/payments', [SalesPaymentAPIController::class, 'getAllPayments']);
    Route::post('sales/{salesPayment}/payment', [SalesPaymentAPIController::class, 'updateSalePayment']);
    Route::delete('sales/{id}/payment', [SalesPaymentAPIController::class, 'deletePayment']);
    //    });

    Route::resource('holds', HoldAPIController::class);

    // Quotation
    Route::resource('quotations', QuotationAPIController::class);
    Route::get('quotation-info/{quotation}', [QuotationAPIController::class, 'quotationInfo']);
    Route::get('quotation-pdf-download/{quotation}', [QuotationAPIController::class, 'pdfDownload']);

    Route::resource('mail-templates', MailTemplateAPIController::class);
    Route::post('mail-template-status/{id}', [MailTemplateAPIController::class, 'changeActiveStatus']);
    Route::post('users-status/{id}', [UserAPIController::class, 'changeActiveStatus']);
    Route::post('warehouse-status/{id}', [warehouseApiController::class, 'changeActiveStatus']);

    Route::resource('sms-templates', SmsTemplateAPIController::class);
    Route::post('sms-template-status/{id}', [SmsTemplateAPIController::class, 'changeActiveStatus']);

    //sale return
    //    Route::middleware('permission:manage_sale_return')->group(function () {
    Route::resource('sales-return', SaleReturnAPIController::class);
    Route::get('sales-return-edit/{id}', [SaleReturnAPIController::class, 'editBySale']);
    Route::get(
        'sale-return-info/{sales_return}',
        [SaleReturnAPIController::class, 'saleReturnInfo']
    )->name('sale-return-info');
    Route::get(
        'sale-return-pdf-download/{sale_return}',
        [SaleReturnAPIController::class, 'pdfDownload']
    )->name('sale-return-pdf-download');
    //    });

    //expense category route
    //    Route::middleware('permission:manage_expense_categories')->group(function () {
    Route::resource('expense-categories', ExpenseCategoryAPIController::class);
    //    });
    Route::get('expense-categories', [ExpenseCategoryAPIController::class, 'index']);

    //expense route
    //    Route::middleware('permission:manage_expenses')->group(function () {
    Route::resource('expenses', ExpenseAPIController::class);
    //    });

    //setting route
    //    Route::middleware('permission:manage_setting')->group(function () {
    Route::resource('settings', SettingAPIController::class);
    Route::post('settings', [SettingAPIController::class, 'update']);
    Route::get('states/{id}', [SettingAPIController::class, 'getStates']);
    Route::get('mail-settings', [SettingAPIController::class, 'getMailSettings']);
    Route::post('mail-settings/update', [SettingAPIController::class, 'updateMailSettings']);
    Route::post('receipt-settings/update', [SettingAPIController::class, 'updateReceiptSetting']);
    //    });

    //    Route::middleware('permission:manage_language')->group(function () {
    Route::resource('languages', LanguageAPIController::class);
    Route::get('languages/translation/{language}', [LanguageAPIController::class, 'showTranslation']);
    Route::post('languages/translation/{language}/update', [LanguageAPIController::class, 'updateTranslation']);
    //    });


    // routes for language content controller
    Route::get('language-contents', [LanguageContentController::class, 'index']);
    Route::post('language-contents-add', [LanguageContentController::class, 'store']);
    Route::get('language-contents/{id}', [LanguageContentController::class, 'show']);
    Route::post('update-language-contents/{id}', [LanguageContentController::class, 'update']);

    Route::resource('sms-settings', SmsSettingAPIController::class);
    Route::post('sms-settings', [SmsSettingAPIController::class, 'update']);

    Route::get('settings', [SettingAPIController::class, 'index']);

    //clear cache route
    Route::get('cache-clear', [SettingAPIController::class, 'clearCache'])->name('cache-clear');

    //purchase routes
    Route::resource('purchases', PurchaseAPIController::class);
    Route::get(
        'purchase-pdf-download/{purchase}',
        [PurchaseAPIController::class, 'pdfDownload']
    )->name('purchase-pdf-download');
    Route::get('purchase-info/{purchase}', [PurchaseAPIController::class, 'purchaseInfo'])->name('purchase-info');
    Route::post('logout', [AuthController::class, 'logout']);

    //    Route::middleware('permission:manage_adjustments')->group(function () {
    Route::resource('adjustments', AdjustmentAPIController::class);
    //    });

    //purchase return routes
    Route::resource('purchases-return', PurchaseReturnAPIController::class);
    Route::get(
        'purchase-return-info/{purchase_return}',
        [PurchaseReturnAPIController::class, 'purchaseReturnInfo']
    )->name('purchase-return-info');
    Route::get(
        'purchase-return-pdf-download/{purchase_return}',
        [PurchaseReturnAPIController::class, 'pdfDownload']
    )->name('purchase-return-pdf-download');

    //Language Change
    Route::post('change-language', [UserAPIController::class, 'updateLanguage']);

    // warehouse report
    Route::get('warehouse-report', [WarehouseAPIController::class, 'warehouseReport'])->name('report-warehouse');
    Route::get(
        'sales-report-excel',
        [ReportAPIController::class, 'getWarehouseSaleReportExcel']
    )->name('report-getSaleReportExcel');
    Route::get(
        'purchases-report-excel',
        [ReportAPIController::class, 'getWarehousePurchaseReportExcel']
    );
    Route::get(
        'sales-return-report-excel',
        [ReportAPIController::class, 'getWarehouseSaleReturnReportExcel']
    )->name('report-getSaleReturnReportExcel');
    Route::get(
        'purchases-return-report-excel',
        [
            ReportAPIController::class, 'getWarehousePurchaseReturnReportExcel',
        ]
    )->name('report-getPurchaseReturnReportExcel');
    Route::get(
        'expense-report-excel',
        [ReportAPIController::class, 'getWarehouseExpenseReportExcel']
    )->name('report-getExpenseReportExcel');

    //sale report
    Route::get(
        'total-sale-report-excel',
        [ReportAPIController::class, 'getSalesReportExcel']
    )->name('report-getSalesReportExcel');
    Route::get(
        'customer-sale-report-excel/{cus_id}',
        [ReportAPIController::class, 'getCustomerSalesReportExcel']
    )->name('report-getCustomerSalesReportExcel');
    Route::get(
        'customer-sale-return-report-excel/{cus_id}',
        [ReportAPIController::class, 'getCustomerSalesReturnReportExcel']
    )->name('report-getCustomerSalesReturnReportExcel');

    Route::get(
        'total-gift-report-excel',
        [ReportAPIController::class, 'getGiftsReportExcel']
    )->name('report-getGiftsReportExcel');
    Route::get(
        'total-mileage-report-excel',
        [ReportAPIController::class, 'getMileageReportExcel']
    )->name('report-getMileageReportExcel');
    Route::get(
        'total-cashassign-report-excel',
        [ReportAPIController::class, 'getCashAssignReportExcel']
    )->name('report-getCashAssignReportExcel');

    // purchase report
    Route::get(
        'total-purchase-report-excel',
        [ReportAPIController::class, 'getPurchaseReportExcel']
    );
    // top-selling product report
    Route::get(
        'top-selling-product-report-excel',
        [ReportAPIController::class, 'getSellingProductReportExcel']
    );
    Route::get(
        'top-selling-product-report',
        [ReportAPIController::class, 'getSellingProductReport']
    );

    Route::get('supplier-report', [ReportAPIController::class, 'getSupplierReport']);

    Route::get('supplier-purchases-report/{supplier_id}', [ReportAPIController::class, 'getSupplierPurchasesReport']);
    Route::get(
        'supplier-purchases-return-report/{supplier_id}',
        [ReportAPIController::class, 'getSupplierPurchasesReturnReport']
    );
    Route::get('supplier-report-info/{supplier_id}', [ReportAPIController::class, 'getSupplierInfo']);

    // profit loss report
    Route::get('profit-loss-report', [ReportAPIController::class, 'getProfitLossReport']);

    // best customers report

    Route::get('best-customers-report', [ReportAPIController::class, 'getBestCustomersReport']);
    Route::get('best-customers-pdf-download', [CustomerAPIController::class, 'bestCustomersPdfDownload']);
    Route::get('customer-list', [CustomerAPIController::class, 'customerList']);
    //customer all report
    Route::get('customer-report', [ReportAPIController::class, 'getCustomerReport']);
    Route::get('customer-payments-report/{customer}', [ReportAPIController::class, 'getCustomerPaymentsReport']);
    Route::get('customer-info/{customer}', [ReportAPIController::class, 'getCustomerInfo']);
    Route::get('customer-pdf-download/{customer}', [CustomerAPIController::class, 'pdfDownload']);
    Route::get('customer-sales-pdf-download/{customer}', [CustomerAPIController::class, 'customerSalesPdfDownload']);
    Route::get(
        'customer-quotations-pdf-download/{customer}',
        [CustomerAPIController::class, 'customerQuotationsPdfDownload']
    );
    Route::get(
        'customer-returns-pdf-download/{customer}',
        [CustomerAPIController::class, 'customerReturnsPdfDownload']
    );
    Route::get(
        'customer-payments-pdf-download/{customer}',
        [CustomerAPIController::class, 'customerPaymentsPdfDownload']
    );

    //Warehouse Products alert Quantity Report
    Route::get('product-stock-alerts', [ReportAPIController::class, 'stockAlerts']);
    Route::get('product-stock-inventory', [ReportAPIController::class, 'stockInventory']);

    //stock report
    Route::get('stock-report', [ManageStockAPIController::class, 'stockReport'])->name('report-stockReport');
    Route::get('stock-report-excel', [ReportAPIController::class, 'stockReportExcel'])->name('report-stockReportExcel');
    Route::get('gift-stock-report-excel', [ReportAPIController::class, 'giftStockReportExcel'])->name('report-giftStockReportExcel');
    Route::get('product-stock-report-excel', [ReportAPIController::class, 'productStockReportExcel'])->name('report-productStockReportExcel');
    Route::get(
        'get-sale-product-report',
        [SaleAPIController::class, 'getSaleProductReport']
    )->name('report-get-sale-product-report');
    Route::get(
        'get-product-sales-report',
        [SaleAPIController::class, 'getProductSalesReport']
    )->name('report-get-product-sale-report');

    Route::get(
        'get-purchase-product-report',
        [PurchaseAPIController::class, 'getPurchaseProductReport']
    )->name('report-get-purchase-product-report');
    Route::get(
        'get-sale-return-product-report',
        [SaleReturnAPIController::class, 'getSaleReturnProductReport']
    );
    Route::get(
        'get-product-return-sale-report',
        [SaleReturnAPIController::class, 'getProductReturnSaleReport']
    );
    Route::get('get-purchase-return-product-report', [
        PurchaseReturnAPIController::class, 'getPurchaseReturnProductReport',
    ]);

    // Today sale overall report

    Route::get('today-sales-overall-report', [ReportAPIController::class, 'getTodaySalesOverallReport']);

    // stock report excel
    Route::get('get-product-sale-report-excel', [ReportAPIController::class, 'getProductSaleReportExport']);
    Route::get('get-product-purchase-report-excel', [ReportAPIController::class, 'getPurchaseProductReportExport']);
    Route::get(
        'get-product-sale-return-report-excel',
        [ReportAPIController::class, 'getSaleReturnProductReportExport']
    );
    Route::get(
        'get-product-purchase-return-report-excel',
        [ReportAPIController::class, 'getPurchaseReturnProductReportExport']
    );
    Route::get('get-product-count', [ReportAPIController::class, 'getProductQuantity']);

    Route::get('config', [UserAPIController::class, 'config']);

    // POS Register routes
    Route::get('get-register-details', [POSRegisterAPIController::class, 'getRegisterDetails']);
    Route::post('register-entry', [POSRegisterAPIController::class, 'entry']);
    Route::post('register-close', [POSRegisterAPIController::class, 'closeRegister']);
    Route::get('register-report', [POSRegisterAPIController::class, 'registerReport']);

   //Regions


    Route::post('add-region', [LanguageAPIController::class, 'addRegion']);
    Route::get('fetch-regions', [LanguageAPIController::class, 'fetchRegions']);
    Route::get('fetch-regions-list', [LanguageAPIController::class, 'fetchRegionsList']);
    Route::get('delete-regions/{id}', [LanguageAPIController::class, 'deleteRegions']);
    Route::post('edit-regions/{id}', [LanguageAPIController::class, 'editRegion']);
    Route::post('fetch-region/{id}', [LanguageAPIController::class, 'fetchRegion']);
    Route::get('countries', [AreaController::class, 'fetchCountries']);
    Route::post('update-country', [AreaController::class, 'updateCountry']);

    //GIFTS
    Route::get('get-gift-list', [GiftController::class, 'index'])->name('get-gift-list');
    Route::get('get-gift-list-all', [GiftController::class, 'indexAll'])->name('get-gift-list-all');
    Route::get('submit-gift-history', [GiftController::class, 'submitGiftHistory'])->name('submit-gift-history');
    Route::get('submit-gift-details/{id}', [GiftController::class, 'submitGiftDetails'])->name('submit-gift-details');
    Route::post('store-gift', [GiftController::class,  'storeGift'])->name('store-gift');
   Route::post('gifts/update/{id}', [GiftController::class, 'updateGift']);
   Route::delete('gifts/{id}', [GiftController::class, 'deleteGift']);
   Route::get('gift/detail/{id}', [GiftController::class, 'show']);
   Route::post('assign-gift', [GiftController::class, 'assign']);
   Route::get('assigned-gift-list', [AssignedGiftController::class, 'index']);
   Route::post('update-gift-inventory', [GiftInvetoryController::class, 'updateQuantity']);
   Route::get('gift-inventory', [GiftInvetoryController::class, 'index']);
   Route::post('update-product-inventory', [ProductInventoryController::class, 'updateQuantity']);
   Route::get('product-inventory', [ProductInventoryController::class, 'index']);


    //cash list

    Route::get('opening-closing-cash-list', [UserAPIController::class,'openingClosingCashList']);

    Route::post('add-cash-amount', [UserAPIController::class,'addCashAmount']);

    //  mileage
    Route::get('mileage-records', [UserAPIController::class,'MileageRecords']);
    Route::get('fetch-mileage/{id}',[UserAPIController::class,'fetchMileage']);

    // Notification Templates
    Route::post('create-notification-template', [NotificationTemplateController::class, 'store']);
    Route::get('user-notification-template', [NotificationTemplateController::class, 'index']);
    Route::get('user-notification-templates/{id}', [NotificationTemplateController::class, 'show']);
    Route::put('user-notification-templates-update/{id}', [NotificationTemplateController::class, 'update']);
    Route::post('create-admin-notification-template', [AdminNotificationController::class, 'store']);
    Route::get('admin-notification-template-list', [AdminNotificationController::class, 'index']);
    Route::get('addmin-notification-detail/{id}', [AdminNotificationController::class, 'show']);
    Route::put('update-admin-notification/{id}', [AdminNotificationController::class, 'update']);

    // mail templates routes
    Route::post('add-email-templates', [MailTemplateAPIController::class, 'store']);
    Route::post('add-admin-email-templates', [MailTemplateAPIController::class, 'admintemplate']);
    Route::get('admin-email-templates', [MailTemplateAPIController::class, 'fetchAdminEmailTemplate']);
    Route::get('edit-admin-email-template/{id}', [MailTemplateAPIController::class, 'fetchAdminEmailTemplateDetails']);
   Route::put('update-admin-email-template/{id}', [MailTemplateAPIController::class, 'updateAdminTemplate']);




    // Coupon Code Routes
    Route::resource('coupon-codes', CouponCodeAPIController::class);
    Route::post('/load', [LoadUnloadController::class,'Load']);
    Route::get('today-stockin-product-list', [LoadUnloadController::class, 'todayStockInProducts']);
    Route::post('question-add-option', [SurveyController::class, 'addQuestionOption']);
    Route::get('question-list', [SurveyController::class, 'QuestionList']);
    Route::get('questions/{id}', [SurveyController::class, 'getQuestionById']);
    Route::delete('question/{id}', [SurveyController::class, 'deleteQuestion']);
    Route::get('survey-list', [SurveyController::class, 'SurveyList']);
    Route::get('survey-details/{id}', [SurveyController::class, 'SurveyDetails']);
    Route::get('all-checkin-list', [SurveyController::class, 'CheckInList']);
    Route::get('checkin-details/{id}', [SurveyController::class, 'checkinDetails']);
    Route::get('all-checkout-list', [SurveyController::class, 'CheckOutList']);
    Route::get('checkout-details/{id}', [SurveyController::class, 'checkoutDetails']);
    Route::post('questions/update/{id}', [SurveyController::class, 'updateQuestionOption']);

});

Route::post('login', [AuthController::class, 'login'])->name('login');
Route::post('register', [AuthController::class, 'register']);

Route::post(
    '/forgot-password',
    [AuthController::class, 'sendPasswordResetLinkEmail']
)->middleware('throttle:5,1')->name('password.email');
Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.reset');

Route::get('front-setting', [SettingAPIController::class, 'getFrontSettingsValue'])->name('front-settings');
Route::get('login-roles', [RoleAPIController::class, 'LoginRole']);

Route::post('validate-auth-token', [AuthController::class, 'isValidToken']);




require __DIR__ . '/m1.php';
