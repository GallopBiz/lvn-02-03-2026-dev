<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\backend\AdminController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\backend\InquiryController;
use App\Http\Controllers\backend\RegistrationController;
use App\Http\Controllers\backend\StudentRegistrationController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\LogoutController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AddVehical;
use App\Http\Controllers\FeestypemasterController;
use App\Http\Controllers\BusfeesmasterController;
use App\Http\Controllers\backend\InquiryEntryController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
// Route::get('/', function () {
//     return view('welcome');
// });
Auth::routes();
Route::get('/', [LoginController::class, 'login']);

Route::group(['namespace' => 'App\Http\Controllers'], function()
{   
    /**
     * Home Routes
     */
    // Route::get('/', 'HomeController@index')->name('home.index');

    Route::group(['middleware' => ['guest']], function() {
        /**
         * Register Routes
         */
        Route::get('/register', 'RegisterController@show')->name('register.show');
        Route::post('/register', 'RegisterController@register')->name('register.perform');

        /**
         * Login Routes
         */
        Route::get('/login', 'LoginController@show')->name('login.show');
        Route::post('/login', 'LoginController@login')->name('login.perform');

    });

    Route::group(['middleware' => ['auth']], function() {
        /**
         * Logout Routes
         */
        Route::get('/logout', 'LogoutController@perform')->name('logout.perform');
    });
});

Auth::routes();Route::get('/home', [HomeController::class, 'index'])->name('home');
  
Route::group(['middleware' => ['auth']], function() {
    Route::resource('roles', RoleController::class);
    Route::resource('users', UserController::class);
    // Route::resource('products', ProductController::class);
    /*Admin Routes*/
    Route::get('admin-dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');

    Route::resource('inquiry-data', InquiryController::class);
    Route::get('inquiry-data-show', [InquiryController::class,'show'])->name('inquiry-data');

    Route::resource('inquiry-data', InquiryController::class);

    /*Student registration data*/
    Route::get('student-registrations', [StudentRegistrationController::class,'student_registrations'])->name('student-registrations');
    Route::get('add-student-registrations',[StudentRegistrationController::class,'add_student_registrations'])->name('add-student-registrations');
    Route::post('save-student-registration', [StudentRegistrationController::class, 'save_student_registration']);
    Route::post('getDataByFormNumber', [StudentRegistrationController::class, 'getDataByFormNumber']);

    /*Student routes*/
    Route::get('student-inquiry', [HomeController::class, 'index'])->name('student-inquiry');
    Route::post('save-student-inquiry', [HomeController::class, 'save_student_inquiry']);
    Route::get('student-inquiry-out', [HomeController::class, 'student_inquiry_out'])->name('student-inquiry-out');
    Route::post('create', [HomeController::class, 'create' ]);

    Route::get('admin-enquiryform', [RegistrationController::class,'enquiryform'])->name('admin-enquiryform');
<<<<<<< HEAD 
    // Transport-Module
    // Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

     /*Transport  routes*/
     Route::get('addvehical',[AddVehical::class, 'index'])->name('addvehical');
     Route::post('addvehical',[AddVehical::class, 'addvehical'])->name('addvehical');
>>>>>>> 2de68ffb1dfc673ab6fd228911619838b08eb915

    Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

    Route::get('/schalars',[RegistrationController::class, 'schalars_all'])->name('schalars');

    // Fees routes
    Route::get('fees-type-master', [feestypemasterController::class, 'show'])->name('fees-type-master');
    Route::post('save-fees-type-master', [feestypemasterController::class, 'create']);
    Route::get('view/{id}', [feestypemasterController::class, 'view']);
    Route::post('store', [feestypemasterController::class, 'store']);
    Route::get('delete/{id}', [feestypemasterController::class, 'delete']);

    Route::get('bus-fees-master', [busfeesmasterController::class, 'index'])->name('bus-fees-master');
    Route::post('save-bus-fees-type', [busfeesmasterController::class, 'create']);
<<<<<<< HEAD

    Route::post('save_student_inquiryentry', [InquiryEntryController::class, 'save_student_inquiryentry']);
    Route::get('adminenquirylist', [InquiryEntryController::class,'adminenquirylist'])->name('adminenquirylist');

=======
    Route::get('bus-view/{id}', [busfeesmasterController::class, 'view']);
    Route::post('bus-store', [busfeesmasterController::class, 'store']);
    Route::get('bus-delete/{id}', [busfeesmasterController::class, 'delete']);
>>>>>>> 2de68ffb1dfc673ab6fd228911619838b08eb915
});


// Route::resource('inquiry-data', InquiryController::class);

// /*Student registration data*/
// Route::get('student-registrations', [StudentRegistrationController::class,'student_registrations'])->name('student-registrations');
// Route::get('add-student-registrations',[StudentRegistrationController::class,'add_student_registrations'])->name('add-student-registrations');

