<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $userId = $request->input('user_id');
        $orders = Order::query();

        $orders->when(
            $userId,
            function ($query) use ($userId) {
                return $query->where('user_id', '=', $userId);
            }
        );

        return response()->json([
            'status' => 'success',
            'data' => $orders->get(),
        ]);
    }
    //
    public function create(Request $request)
    {
        $user = $request->input('user');
        $course = $request->input('course');

        $order = Order::create([

            'user_id' => $user['id'],
            'course_id' => $course['id']
        ]);

        $transactionDetails = [
            'order_id' => $order->id . Str::random(10),
            //'order_id' => rand(),
            'gross_amount' => $course['price'],

        ];

        $itemDetails = [
            [
                'id' => $course['id'],
                'price' => $course['price'],
                'quantity' => 1,
                'name' => $course['name'],
                'brand' => 'jaridekat.com',
                'category' => 'Online course',
            ]
        ];

        $customerDetails = [
            'first_name' => $user['name'],
            'email' => $user['email'],
        ];

        $midtransParams = [
            'transaction_details' => $transactionDetails,
            'item_details' => $itemDetails,
            'customer_details' => $customerDetails
        ];

        $midstransSnapUrl = $this->getMidtransSnapUrl($midtransParams);

        $order->snap_url = $midstransSnapUrl;

        $order->metadata = [
            'course_id' => $course['id'],
            'course_price' => $course['price'],
            'course_name' => $course['name'],
            'course_thumbnail' => $course['thumbnail'],
            'course_level' => $course['level'],
        ];

        $order->save();

        return response()->json([
            'status' => 'success',
            'data' => $order,
            'message' => 'Order Payment has been created successfully'
        ]);
        // return response()->json($order);
    }


    private function getMidtransSnapUrl($params)
    {

        \Midtrans\Config::$serverKey = env('MIDTRANS_SERVER_KEY');
        \Midtrans\Config::$isProduction = (bool) env('MIDTRANS_PRODUCTION');
        \Midtrans\Config::$is3ds = (bool) env('MIDTRANS_3DS');


        $snapUrl = \Midtrans\Snap::createTransaction($params)->redirect_url;


        return $snapUrl;
    }
}
