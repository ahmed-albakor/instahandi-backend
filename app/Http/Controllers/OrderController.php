<?php

namespace App\Http\Controllers;

use App\Http\Requests\Order\CreateOrderRequest;
use App\Http\Requests\Order\UpdateOrderRequest;
use App\Http\Requests\Order\UpdateStatusRequest;
use App\Http\Resources\OrderResource;
use App\Permissions\OrderPermission;
use App\Services\Helper\ResponseService;
use App\Services\System\OrderService;

class OrderController extends Controller
{
    protected $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    public function index()
    {
        $orders = $this->orderService->index();

        return response()->json(
            [
                'success' => true,
                'data' => OrderResource::collection($orders->items()),
                'meta' => ResponseService::meta($orders)
            ]
        );
    }

    public function show($id)
    {
        $relationships = ['serviceRequest.client.user', 'serviceRequest.service', 'workLocation', 'images', 'vendor.user', 'vendor.services', 'proposal'];

        $order = $this->orderService->getOrderById($id, $relationships);

        OrderPermission::show($order);

        return response()->json([
            'success' => true,
            'data' => new OrderResource($order),
        ]);
    }

    public function create(CreateOrderRequest $request)
    {
        $order = $this->orderService->createOrder($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Order created successfully.',
            'data' => new OrderResource($order),
        ]);
    }

    public function update($id, UpdateOrderRequest $request)
    {
        $relationships = ['serviceRequest.client.user', 'serviceRequest.service', 'workLocation', 'images', 'vendor.user', 'vendor.services', 'proposal'];

        $order = $this->orderService->getOrderById($id, $relationships);

        OrderPermission::update($order);

        $order = $this->orderService->updateOrder($order, $request->validated());


 
        return response()->json([
            'success' => true,
            'message' => 'Order updated successfully.',
            'data' => new OrderResource($order),
        ]);
    }

    public function destroy($id)
    {
        $order = $this->orderService->getOrderById($id);

        OrderPermission::destory($order);

        $this->orderService->deleteOrder($order);

        return response()->json([
            'success' => true,
            'message' => 'Order deleted successfully.',
        ]);
    }


    public function updateStatus($id, UpdateStatusRequest $request)
    {
        $relationships = ['serviceRequest.client.user', 'serviceRequest.service', 'workLocation', 'images', 'vendor.user', 'vendor.services', 'proposal'];

        $order = $this->orderService->getOrderById($id);
        OrderPermission::update($order);

        $validated = $request->validated();

        $order = $this->orderService->updateOrderStatus($order, $validated['status']);

        $order->load($relationships);

        return response()->json([
            'success' => true,
            'message' => 'Order updated successfully.',
            'data' => new OrderResource($order),
        ]);
    }
}
