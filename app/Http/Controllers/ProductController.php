<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;

use App\Models\Product;

class ProductController extends Controller
{
    //
    public function index() 
    {
        try {
            $res = DB::select("
                SELECT 
                    ProductName,
                    QuantityPerUnit
                FROM Products
            ");
            return response()->json($res);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function active () 
    {
        try {
            $res = DB::select("
                SELECT 
                    ProductID,
                    ProductName
                FROM Products
                WHERE Discontinued = ?
            ", [false]);
            return response()->json($res);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function mostExpensive() 
    {
        try {
            $res = DB::select("
                SELECT ProductName, UnitPrice
                FROM Products
                WHERE UnitPrice = (
                    SELECT MAX(UnitPrice)
                    FROM Products where Discontinued = ?
                ) 
            ", [false]);
            return response()->json($res);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function leastExpensive() 
    {
        try {
            $res = Product::query()
                ->where('UnitPrice', '=', Product::min('UnitPrice'))
                ->where('Discontinued', false)
                ->select([
                    'ProductName',
                    'UnitPrice'
                ])
                ->get();            
            return response()->json($res);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function aboveAverageUnitPriceProducts ()
    {
        try {
            $res = Product::query()
                ->select([
                    'ProductName',
                    'UnitPrice',
                ])
                ->where('UnitPrice', '>', Product::where('Discontinued', false)->average('UnitPrice'))
                ->get();
            return response()->json($res);
        } catch (\Exception $e) {
            return response()->json([
                "message" => $e->getMessage()
            ], 500);
        }
    }
    
    public function currentProductsLessThanUnitPrice()
    {
        try {
            $res = Product::query()
                ->select([
                    'ProductID',
                    'ProductName',
                    'UnitPrice'
                ])
                ->where('UnitPrice', '<', 20)
                ->where('Discountinued', false)
                ->get();
            return json()->response($res);
        } catch (\Exception $e) {
            return json()->response([
                "message" => $e->getMessage()
            ], 500);
        }
    }

    public function unitStockLessThanQuantityOrder()
    {
        try {
            $res = Product::query()
                ->select([
                    'ProductName',
                    'UnitsOnOrder',
                    'UnitsInStock'
                ])
                ->whereColumn('UnitsInStock' , '<', 'UnitsOnOrder')
                ->get();
            return response()->json($res);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}
