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

        Route::group ([
            'prefix' => 'order'
        ],
            function () {
                Route::post('create', "OrderController@createOrder");
                Route::post('confirm/{order}', "OrderController@confirmOrder");
                Route::put('cancel', "OrderController@cancelOrder");
            }
        );

        Route::group([
            'prefix' => 'product'
        ],
            function () 
            {
                Route::get('', "ProductController@index"); #1
                Route::get('active', "ProductController@active"); #2
                Route::get('mostExpensive', "ProductController@mostExpensive"); #3
                Route::get('leastExpensive', "ProductController@leastExpensive"); #3
                Route::get('aboveAverageUnitPriceProducts', "ProductController@aboveAverageUnitPriceProducts"); #4
                Route::get('currentProductsLessThanUnitPrice', "ProductController@currentProductsLessThanUnitPrice"); #5
                Route::get('unitStockLessThanQuantityOrder', "ProductController@unitStockLessThanQuantityOrder"); #6
            }
        );
    }
);