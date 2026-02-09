<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\masters\LoginController;
use App\Http\Controllers\masters\HomeController;
use App\Http\Controllers\masters\BasicInfoController;
use App\Http\Controllers\masters\StaffController;
use App\Http\Controllers\masters\VehicleController;
use App\Http\Controllers\masters\GuideController;
use App\Http\Controllers\masters\AgencyController;
use App\Http\Controllers\masters\CustomerController;
use App\Http\Controllers\masters\BranchController;
use App\Http\Controllers\masters\DriverController;
use App\Http\Controllers\masters\PartnerController;
use App\Http\Controllers\masters\LoginHistoryController;
use App\Http\Controllers\masters\ItineraryController;
use App\Http\Controllers\masters\FacilityController;
use App\Http\Controllers\masters\LocationController;
use App\Http\Controllers\masters\PurposeController;
use App\Http\Controllers\masters\ReservationCategoryController;
use App\Http\Controllers\masters\AttendanceCategoryController;
use App\Http\Controllers\masters\RemarkController;
use App\Http\Controllers\masters\FeeController;
use App\Http\Controllers\masters\BankController;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/login', [LoginController::class, 'index'])->name('login');
Route::post('/login', [LoginController::class, 'authenticate'])->name('login.post');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
Route::get('master/settings', [BasicInfoController::class, 'settings'])->name('master.settings');

Route::prefix('masters')->name('masters.')->group(function () {
    Route::get('/', 'MasterController@index')->name('home');
    Route::resource('basicinfo', BasicInfoController::class)->names('basicinfo');
    Route::resource('branches', BranchController::class)->names('branches');
    Route::resource('staffs', StaffController::class)->names('staffs');
    Route::resource('vehicles', VehicleController::class)->names('vehicles');
    Route::resource('drivers', DriverController::class)->names('drivers');
    Route::resource('guides', GuideController::class)->names('guides');
    Route::resource('agencies', AgencyController::class)->names('agencies');
    Route::resource('customers', CustomerController::class)->names('customers');
    Route::resource('partners', PartnerController::class)->names('partners');
    Route::resource('itineraries', ItineraryController::class)->names('itineraries');
    Route::resource('facilities', FacilityController::class)->names('facilities');
    Route::resource('locations', LocationController::class)->names('locations');
    Route::resource('purposes', PurposeController::class)->names('purposes');
    Route::resource('reservation-categories', ReservationCategoryController::class)->names('reservation-categories');
    Route::resource('attendance-categories', AttendanceCategoryController::class)->names('attendance-categories');
    Route::resource('remarks', RemarkController::class)->names('remarks');
    Route::resource('fees', FeeController::class)->names('fees');
    Route::get('login-histories', [LoginHistoryController::class, 'index'])->name('login-histories.index');
});