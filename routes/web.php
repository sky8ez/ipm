<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of the routes that are handled
| by your application. Just tell Laravel the URIs it should respond
| to using a Closure or controller method. Build something great!
|
*/

// Route::get('/', function () {
//     return view('welcome');
// });

Auth::routes();

// API ROUTES ==================================
Route::group(['middleware' => ['auth'], 'prefix' => 'api' ], function() {
    // since we will be using this just for CRUD, we won't need create and edit
    // Angular will handle both of those forms
    // this ensures that a user can't access api/create or api/edit when there's nothing there
    Route::get('table-filter/{form_id}', 'App\Master\TableFilterController@index');
    Route::resource('table-filter', 'App\Master\TableFilterController',
        array('only' => array('store', 'destroy')));

    Route::get('form/user/{cond?}/{id?}', 'App\Master\UserController@getForm');
    Route::get('user/{skip?}', 'App\Master\UserController@index');
    Route::delete('user-many', 'App\Master\UserController@destroyMany');
    Route::resource('user', 'App\Master\UserController',
        array('only' => array('store', 'update', 'destroy')));

    // Route::get('form/activity/{cond?}/{id?}', 'App\Master\ActivityController@getForm');
    Route::get('activity/{skip?}', 'App\Module\ActivityController@index');
    // Route::delete('activity-many', 'App\Master\ActivityController@destroyMany');
    // Route::resource('activity', 'App\Master\ActivityController',
    //     array('only' => array('store', 'update', 'destroy')));

    Route::get('form/user-access/{cond?}/{id?}', 'App\Master\UserAccessController@getForm');
    Route::get('user-access/{skip?}', 'App\Master\UserAccessController@index');
    Route::delete('user-access-many', 'App\Master\UserAccessController@destroyMany');
    Route::resource('user-access', 'App\Master\UserAccessController',
        array('only' => array('store', 'update', 'destroy')));

    Route::get('form/password/{cond?}/{id?}', 'App\Master\PasswordController@getForm');
    Route::get('password/{skip?}', 'App\Master\PasswordController@index');
    Route::delete('password-many', 'App\Master\PasswordController@destroyMany');
    Route::resource('password', 'App\Master\PasswordController',
        array('only' => array('store', 'update', 'destroy')));

    Route::get('form/password-category/{cond?}/{id?}', 'App\Master\PassCategoryController@getForm');
    Route::get('password-category/{skip?}', 'App\Master\PassCategoryController@index');
    Route::delete('password-category-many', 'App\Master\PassCategoryController@destroyMany');
    Route::resource('password-category', 'App\Master\PassCategoryController',
        array('only' => array('store', 'update', 'destroy')));

    Route::get('form/file-manager/{cond?}/{id?}', 'App\Transaction\FileController@getForm');
    Route::get('file-manager/{skip?}', 'App\Transaction\FileController@index');
    Route::post('file-manager/upload', 'App\Transaction\FileController@upload');
    Route::post('file-manager/{id}', 'App\Transaction\FileController@update');
    Route::get('uploaded-file/{file}',['as' => 'file', 'uses' => 'App\Transaction\FileController@getUploaded']);
    Route::resource('file-manager', 'App\Transaction\FileController',
        array('only' => array('store', 'destroy')));


    Route::get('form/print-template/{cond?}/{id?}', 'App\Master\PrintController@getForm');
    Route::get('print-template/{skip?}', 'App\Master\PrintController@index');
    Route::delete('print-template-many', 'App\Master\PrintController@destroyMany');
    Route::resource('print-template', 'App\Master\PrintController',
        array('only' => array('index','store', 'update', 'destroy')));
    Route::post('print-template/{id?}/editor', 'App\Master\PrintController@saveDetail');
    Route::get('print-template/{id?}/editor', 'App\Master\PrintController@getDetail');

    //----------------------------TRANSACTION------------------------------------------------------
    Route::get('form/password-list/{cond?}/{id?}', 'App\Transaction\PasswordListController@getForm');
    Route::post('password-list/generate-password', 'App\Transaction\PasswordListController@generatePassword');
    Route::post('password-list/generate-password-random-number', 'App\Transaction\PasswordListController@generatePasswordRandomNumber');
    Route::get('password-list/{skip?}', 'App\Transaction\PasswordListController@index');
    Route::delete('password-list-many', 'App\Transaction\PasswordListController@destroyMany');
    Route::resource('password-list', 'App\Transaction\PasswordListController',
        array('only' => array('index','store', 'update', 'destroy')));

    // Route::get('search/{table?}/{parent_id?}/{skip?}/{column?}/{filter?}', 'App\Module\SearchController@search');
    Route::post('search/{table}', 'App\Module\SearchController@search');
    Route::get('quick-search/{table}/{parent_id?}/{cond?}', 'App\Module\SearchController@quickSearch');

    //print
    Route::get('print-cat/{form?}', 'App\Master\PrintController@getPrintTemplate');
    Route::get('print-get-row-count/{form?}/{id?}/{template_id?}', 'App\Master\PrintController@getRowCount');
    Route::get('print/{form?}/{id?}/{template_id?}', 'App\Master\PrintController@getPrint');
    Route::post('print/{category}/set-print-flag', 'App\Master\PrintController@setPrintFlag');
    Route::get('check-print/{form?}/{id?}', 'App\Master\PrintController@checkPrint');

    //report
    Route::get('report-list', 'App\Reports\ReportController@getReportList');
    Route::get('report-view/{form?}', 'App\Reports\ReportController@getReportView');
    Route::post('report-view', 'App\Reports\ReportController@refreshReport');
    Route::post('report/export', 'App\Reports\ReportController@export');
    Route::post('report/export-pdf', 'App\Reports\ReportController@exportPDF');
    Route::post('report/export-csv', 'App\Reports\ReportController@exportCSV');

    //general
    Route::get('general', 'App\Master\PreferenceController@getForm');
    Route::post('general/save', 'App\Master\PreferenceController@saveGeneral');
    Route::post('general/cleanup', 'App\Master\PreferenceController@cleanUp');
    Route::post('general/backup', 'App\Master\PreferenceController@backup');

    //Import Export
    // Route::get('tools/import-export', ['middleware' => 'access:IMPORT-EXPORT,nav', 'as' => 'import', 'uses' => 'App\Module\ImportController@getForm']);
    Route::get('tools/import-export', 'App\Module\ImportController@getForm');
    Route::post('tools/import-export/import', 'App\Module\ImportController@import');
    Route::post('tools/import-export/download-template', 'App\Module\ImportController@downloadTemplate');

    //Dashboard
    Route::post('dashboard', 'App\Master\DashboardController@index');
    // Route::post('dashboard/refresh', 'App\Master\DashboardController@refresh');

    //Audit
    Route::post('audit', 'App\Module\AuditController@index');

    Route::get('profil', 'App\Master\ProfilController@getForm');
    Route::post('profil/save', 'App\Master\ProfilController@saveProfil');


    Route::get('access/{module?}', 'App\Master\UserAccessController@checkAccess');
    Route::get('access-password/{module?}/{id?}', 'App\Master\UserAccessController@checkAccessPassword');
});

Route::get('', 'HomeController@index');
Route::get('/home', 'HomeController@index');
Route::get('/logout', 'HomeController@index');
Route::get('/reload-general', 'App\Master\PreferenceController@reloadGeneral');
Route::get('/remove-filter/{form}', 'HomeController@removeFilter');

Route::post('/set-period', 'HomeController@setPeriod');

//testing barcode route
Route::get('barcode', 'HomeController@barcode');
