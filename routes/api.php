<?php

use App\Http\Controllers\Accounting\BudgetController;
use App\Http\Controllers\Accounting\DepartmentController;
use App\Http\Controllers\Accounts\AccountController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\RegisterCompanyController;
use App\Http\Controllers\Company\FunderController;
use App\Http\Controllers\CompanyTypeController;
use App\Http\Controllers\CountryController;
use App\Http\Controllers\CurrencyController;
use App\Http\Controllers\Employees\EmployeesController;
use App\Http\Controllers\Projects\ProjectController;
use App\Http\Controllers\Sales\ClientController;
use App\Http\Controllers\Sales\CreditNoteController;
use App\Http\Controllers\Sales\DebitNoteController;
use App\Http\Controllers\Sales\IncomingOrderController;
use App\Http\Controllers\Sales\OutgoingShipmentController;
use App\Http\Controllers\Sales\QuotationController;
use App\Http\Controllers\Sales\ReturnInvoiceController;
use App\Http\Controllers\Sales\SalesInvoiceController;
use App\Http\Controllers\Sales\ServiceController;
use App\Http\Controllers\WorkTypeController;
use App\Models\CompanyType;
use App\Models\SalesReturnInvoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;

// Start Auth

Route::group(['prefix' => 'auth'], function () {

    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/forget-password', [AuthController::class, 'forgotPassword']);
    Route::post('/check-otp', [AuthController::class, 'checkOtp']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);
    Route::post('/create-company', [RegisterCompanyController::class, 'store'])->middleware('auth:sanctum');
});

Route::get('currency/all', [CurrencyController::class, 'all']);
Route::get('work-types/all', [WorkTypeController::class, 'all']);
Route::get('company-types/all', [CompanyTypeController::class, 'all']);
Route::get('country/all', [CountryController::class, 'index']);

Route::group(['prefix' => 'accounts', 'middleware' => 'auth:sanctum'], function () {

    Route::get('/all', [AccountController::class, 'index']);
    Route::post('/create', [AccountController::class, 'store']);
});


Route::group(['prefix' => 'sales', 'middleware' => 'auth:sanctum'], function () {

    Route::group(['prefix' => 'clients'], function () {

        Route::get('/all', [ClientController::class, 'index']);
        Route::post('/create', [ClientController::class, 'store']);
    });
    Route::group(['prefix' => 'quotations'], function () {

        Route::get('/all', [QuotationController::class, 'index']);
        Route::post('/create', [QuotationController::class, 'store']);
    });
    Route::group(['prefix' => 'incoming-orders'], function () {

        Route::get('/all', [IncomingOrderController::class, 'index']);
        Route::post('/create', [IncomingOrderController::class, 'store']);
    });
    Route::group(['prefix' => 'outgoing-shipments'], function () {

        Route::get('/all', [OutgoingShipmentController::class, 'index']);
        Route::post('/create', [OutgoingShipmentController::class, 'store']);
    });
    Route::group(['prefix' => 'sales-invoices'], function () {

        Route::get('/all', [SalesInvoiceController::class, 'index']);
        Route::post('/create', [SalesInvoiceController::class, 'store']);
    });
    Route::group(['prefix' => 'services'], function () {

        Route::get('/all', [ServiceController::class, 'index']);
        Route::post('/create', [ServiceController::class, 'store']);
    });
    Route::group(['prefix' => 'return-invoices'], function () {

        Route::get('/all', [ReturnInvoiceController::class, 'index']);
        Route::post('/create', [ReturnInvoiceController::class, 'store']);
    });
    Route::group(['prefix' => 'debit-notes'], function () {

        Route::get('/all', [DebitNoteController::class, 'index']);
        Route::post('/create', [DebitNoteController::class, 'store']);
    });
    Route::group(['prefix' => 'credit-notes'], function () {

        Route::get('/all', [CreditNoteController::class, 'index']);
        Route::post('/create', [CreditNoteController::class, 'store']);
    });
});
Route::group(['prefix' => 'accounting', 'middleware' => 'auth:sanctum'], function () {
    Route::group(['prefix' => 'funders', 'middleware' => 'auth:sanctum'], function () {

        Route::get('/all', [FunderController::class, 'index']);
        Route::post('/create', [FunderController::class, 'create']);
    });
    Route::group(['prefix' => 'budget'], function () {

        Route::get('/all', [BudgetController::class, 'index']);
        Route::post('/create', [BudgetController::class, 'store']);
    });
    Route::group(['prefix' => 'departments'], function () {

        Route::get('/all', [DepartmentController::class, 'index']);
        Route::post('/create', [DepartmentController::class, 'store']);
    });
});

Route::group(['prefix' => 'projects', 'middleware' => 'auth:sanctum'], function () {

    Route::get('/all', [ProjectController::class, 'index']);
    Route::post('/create', [ProjectController::class, 'store']);
});

Route::group(['prefix' => 'employees', 'middleware' => 'auth:sanctum'], function () {

    Route::get('/all', [EmployeesController::class, 'index']);
    Route::post('/create', [EmployeesController::class, 'store']);
});
