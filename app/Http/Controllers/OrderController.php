<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Http\JsonResponse;

use App\Models\Order;

use App\Http\Repository\OrderRepository;

class OrderController extends Controller
{
    //
    public function __construct(
        protected readonly OrderRepository $orderRepository
    )
    {}

    public function createOrder(Request $request): JsonResponse
    {
        try {
            $this->orderRepository->create($request->all());

            return response()->json([
                'message' => 'Order created.',
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'reason' => $e->getMessage()
            ], 500);
        }
    }

    public function confirmOrder(Request $request, Order $order): JsonResponse
    {
        try {
            $order->confirm(
                $request->filled('failSimulateInventory') ? $request->failSimulateInventory : false,
                $request->filled('failSimulateShipment') ? $request->failSimulateShipment : false,
                $request->filled('failSimulatePayment') ? $request->failSimulatePayment : false,
            );

            return response()->json([
                'success' => true,
                'message' => 'Order confirmed.',
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'reason' => $e->getMessage()
            ], 500);
        }
    }

    public function cancelOrder(Order $order): JsonResponse
    {
        try {
            $order->cancel();

            return response()->json([
                'success' => true,
                'message' => 'Order cancelled.',
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'reason' => $e->getMessage()
            ], 500);
        }
    }
}
