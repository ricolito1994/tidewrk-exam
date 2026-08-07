<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});


Route::group([
    'prefix' => 'api',
    'namespace' => 'App\Http\Controllers',
],
    function () {
        Route::post('uploadStudentData', 'StudentController@processStudentExcelData');

        Router::group ([
            'prefix' => 'order'
        ],
            function () {
                Route::post('create', "OrderController@createOrder");
                Route::post('confirm/{order}', "OrderController@confirmOrder");
                Route::put('cancel', "OrderController@cancelOrder");
            }
        );
    }
);