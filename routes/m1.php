<?php

use App\Http\Controllers\API\M1\AuthController;
use App\Http\Controllers\API\M1\BrandAPIController;
use App\Http\Controllers\API\M1\CustomerAPIController;
use App\Http\Controllers\API\M1\DashboardAPIController;
use App\Http\Controllers\API\M1\HoldAPIController;
use App\Http\Controllers\API\M1\ProductAPIController;
use App\Http\Controllers\API\M1\ProductCategoryAPIController;
use App\Http\Controllers\API\M1\ReportAPIController;
use App\Http\Controllers\API\M1\SaleAPIController;
use App\Http\Controllers\API\M1\UserAPIController;
use App\Http\Controllers\API\M1\WarehouseAPIController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\M1\CouponCodeAPIController;
use App\Http\Controllers\API\M1\GiftController;
use App\Http\Controllers\API\M1\SaleReturnAPIController;
use App\Http\Controllers\API\M1\AreaController;
use App\Http\Controllers\API\M1\ChanelController;
use App\Http\Controllers\API\M1\MainProductAPIController;
use App\Http\Controllers\API\M1\SurveyController;


Route::prefix('m1')->as('m1.')->group(function () {

    Route::get('get-languages', [UserAPIController::class, 'languages']);
    Route::get('get-countries', [UserAPIController::class, 'countries']);
    Route::post('change_language', [UserAPIController::class, 'change_language']);

    // login
    Route::post('login', [AuthController::class, 'login']);
    Route::post('/forgot-password',
        [AuthController::class, 'sendPasswordResetLinkEmail'])->middleware('throttle:5,1');
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);

    Route::middleware('auth:api')->group(function () {
        // dashboard

        Route::get('dashboard', [DashboardAPIController::class, 'index']);

        //coupon
        Route::resource('coupon-codes', CouponCodeAPIController::class);



        // profile
        Route::get('edit-profile', [UserAPIController::class, 'editProfile']);
        Route::post('update-profile', [UserAPIController::class, 'updateProfile']);
        Route::post('change-password', [UserAPIController::class, 'changePassword']);
        // Language Change
        Route::get('languages', [UserAPIController::class, 'languages']);
        Route::post('change-language', [UserAPIController::class, 'updateLanguage']);
        // charts
        Route::get('week-selling-purchases', [DashboardAPIController::class, 'getWeekSalePurchases']);
        Route::get('top-selling-products', [DashboardAPIController::class, 'getTopSellingProducts']);
        Route::get('yearly-top-selling', [DashboardAPIController::class, 'getYearlyTopSelling']);
        Route::get('top-customers', [DashboardAPIController::class, 'getTopCustomer']);
        Route::get('recent-sales', [DashboardAPIController::class, 'getRecentSales']);
        Route::get('stock-alerts', [DashboardAPIController::class, 'stockAlerts']);
        // POS
        Route::resource('customers', CustomerAPIController::class);
        Route::get('warehouses', [WarehouseAPIController::class, 'index']);
        Route::get('product-categories', [ProductCategoryAPIController::class, 'index']);
        Route::get('brands', [BrandAPIController::class, 'index']);
        Route::resource('holds', HoldAPIController::class);
        Route::get('today-sales-overall-report', [ReportAPIController::class, 'getTodaySalesOverallReport']);
        Route::resource('products', ProductAPIController::class);


        //API for mobile app

        Route::resource('outlets', CustomerAPIController::class);
        Route::get('get-outlets-details/{id}', [CustomerAPIController::class,'Details']);
        Route::post('update-outlets', [CustomerAPIController::class,'Update']);

        Route::get('payment-list', [CustomerAPIController::class,'PaymentList']);


        Route::get('opening-cash-list', [UserAPIController::class,'OpeningCashList']);
        Route::get('closing-cash-list', [UserAPIController::class,'closingCashList']);

        Route::get('opening-closing-cash-list', [UserAPIController::class,'openingClosingCashList']);

        Route::post('upload_mileage', [UserAPIController::class,'UploadMileage']);
        Route::post('mileage-records', [UserAPIController::class,'MileageRecords']);



       // End APP Api

        // Logout
        Route::post('logout', [AuthController::class, 'logout']);
        // Sales
        Route::resource('sales', SaleAPIController::class);
        Route::get('sales/salesman/{salesman_id}', [SaleAPIController::class, 'getSalesBySalesman']);
      //Sales return

      Route::resource('sales-return', SaleReturnAPIController::class);
      Route::get('sales-return-edit/{id}', [SaleReturnAPIController::class, 'editBySale']);
      Route::get(
          'sale-return-info/{sales_return}',
          [SaleReturnAPIController::class, 'saleReturnInfo']
      )->name('sale-return-info');


        // Products
        Route::get('get-product-details/{id}', [ProductAPIController::class, 'show'])->name('get-product-details');

        Route::get('get-product-by-category/{id}', [ProductAPIController::class, 'getProductByCategory'])->name('get-product-by-category');
        Route::get('get-product-by-brand/{id}', [ProductAPIController::class, 'getProductByBrand']);
        Route::post('get-product-by-brand-and-category', [ProductAPIController::class, 'getProductByBrandAndCategory']);


        // gifts

        Route::get('get-gift-list', [GiftController::class, 'index'])->name('get-gift-list');
        Route::get('get-gift-details/{id}', [GiftController::class, 'getDetails'])->name('get-gift-details');
        Route::post('submit-gift', [GiftController::class, 'submitGift'])->name('submit-gift');
        Route::get('submit-gift-history/{salse_man_id}', [GiftController::class, 'submitGiftHistory'])->name('submit-gift-history');

        Route::get('/areas',[AreaController::class,'index']);
        Route::get('/chanels-list',[ChanelController::class,'ChannelList']);
        Route::get('/area-list',[AreaController::class,'AreaList']);
        Route::get('/sub-area-list',[AreaController::class,'SubAreaList']);
        Route::get('/distributors-list',[UserAPIController::class,'DistributorList']);
        Route::get('/today-loaded-product-list/{id}',[ProductAPIController::class,'TodayLoadedProductList']);
        Route::get('/all-loaded-product-list/{id}',[ProductAPIController::class,'AllLoadedProductList']);

        Route::get('distributor-products/{id}', [ProductAPIController::class,'DistributorProducts']);
        Route::get('/upcoming-loaded-product-list/{id}',[ProductAPIController::class,'UpcomingLoadedProductList']);


        // Route::get('distributor-products/{id}', [ProductAPIController::class,'DistributorProducts']);
        Route::get('get-profile/{id}', [UserAPIController::class, 'getProfile']);

        Route::get('get-today-customers/{id}', [CustomerAPIController::class, 'getTodayCustomers']);
        Route::get('get-today-unplaned-customers/{id}', [CustomerAPIController::class, 'getUnplanedTodayCustomers']);
        Route::get('get-upcoming-customers/{id}', [CustomerAPIController::class, 'getUpcommingCustomers']);
        Route::get('get-all-customers/{id}', [CustomerAPIController::class, 'getAllCustomers']);
        Route::get('get-completed-customers/{id}', [CustomerAPIController::class, 'getCompletedCustomers']);


        Route::get('get-today-gift/{id}', [GiftController::class, 'TodayLoadedGiftList']);
        Route::get('get-all-gift/{id}', [GiftController::class, 'AllLoadedGiftList']);
        Route::get('get-all-notifications/{id}', [DashboardAPIController::class, 'Notifications']);
        Route::get('read-notifications/{id}', [DashboardAPIController::class, 'readNotifications']);
        Route::post('upload-bulk-customer', [CustomerAPIController::class,'uploadBulkCustomer']);
        Route::get('/all-stock-in-details/{id}',[ProductAPIController::class,'AllSockInDetailsList']);
        


        Route::post('check-in', [UserAPIController::class,'CheckIn']);
        Route::post('update-salesman-trip', [UserAPIController::class,'UpdateSalesmanTrip']);
        Route::post('check-out', [UserAPIController::class,'Checkout']);
        Route::get('check-in-list/{salesman_id}', [UserAPIController::class,'CheckInList']);
        Route::get('check-out-list/{salesman_id}', [UserAPIController::class,'CheckOutList']);
        Route::get('check-in-out-list/{salesman_id}', [UserAPIController::class,'CheckInOutList']);

        Route::get('stock-out-product-list/{id}',[ProductAPIController::class,'stockOutProductList']);
        Route::get('today-stock-out-product-list/{id}',[ProductAPIController::class,'todayStockOutProductList']);
        Route::get('today-summary', [DashboardAPIController::class, 'TodaySummary']);
        Route::get('stock-in-out-product-list/{id}',[ProductAPIController::class,'stockInOutProductList']);

        
        Route::get('salesman-credit-list/{id}', [DashboardAPIController::class, 'SalesmanCreditList']);
        Route::post('update-salesman-credit-list', [DashboardAPIController::class, 'SalesmanUpdateCreditList']);
        Route::post('salesman-credit-list-details', [DashboardAPIController::class, 'SalesmanCreditDetails']);
        Route::post('get-details-for-invoice', [SaleAPIController::class, 'getDetailsForInvoice']);

        Route::get('survey-questions', [SurveyController::class, 'QuestionList']);
        Route::post('create-survey', [SurveyController::class, 'CreateSurvey']);
        Route::get('salesman-survey-history/{salseman_id}', [SurveyController::class, 'SurveyHistoryList']);
        Route::post('salesman-stockin-list', [SurveyController::class, 'salesManStocks']);
        Route::post('salesman-stock-update', [SurveyController::class, 'salesManStocksUpdate']);
        
        Route::post('update-latitude-longitude', [AuthController::class, 'updateLatitudeAndLongitude']);



        
    });
});
