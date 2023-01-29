<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\PaymentLog;
use Illuminate\Support\Facades\DB;

class WebHookController extends Controller
{
    public function midtransHandler(Request $request)
    {
        $data = $request->all();

        $signatureKey = $data['signature_key'];

        $orderId = $data['order_id'];

        $statusCode = $data['status_code'];

        $grossAmount = $data['gross_amount'];

        $serverKey = env('MIDTRANS_SERVER_KEY');

        $mySignatureKey = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);

        $transactionStatus = $data['transaction_status'];

        $type = $data['payment_type'];

        $fraudStatus = $data['fraud_status'];

        if ($signatureKey !== $mySignatureKey) {

            return response()->json([
                'status' => 'error',
                'message' => 'invalid signature'
            ], 400);
        }

        $realOrderId = explode('-', $orderId);
        $order = Order::find($realOrderId)->first();

        //$order = Order::where('id', $realOrderId)->get();
        //$order = DB::table('t_orders')->where('id', $realOrderId)->get();

        if (!$order) {
            return response()->json([
                'status' => 'error',
                'message' => 'order id is not found'
            ], 404);
        }



        if ($order->status === 'success') {
            return response()->json([
                'status' => 'error',
                'message' => 'operation not permitted'
            ], 405);
        }

        if ($transactionStatus == 'capture') {
            if ($fraudStatus == 'challenge') {

                $order->status = 'challenge';
            } else if ($fraudStatus == 'accept') {

                $order->status = 'success';
            }
        } else if ($transactionStatus == 'settlement') {

            $order->status = 'success';
        } else if (
            $transactionStatus == 'cancel' ||
            $transactionStatus == 'deny' ||
            $transactionStatus == 'expire'
        ) {

            $order->status = 'failure';
        } else if ($transactionStatus == 'pending') {

            $order->status = 'pending';
        }

        $logData = [
            'status' => $transactionStatus,
            'raw_response' => json_encode($data),

            't_order_id' => $realOrderId[0],
            'payment_type' => $type
        ];



        //$logDataCreate = json_encode($logData);
        PaymentLog::create($logData);
        $order->save();

        if ($order->status === 'success') {
            createPremiumAccess([
                'user_id' => $order->user_id,
                'course_id' => $order->t_course_id,
            ]);
        }

        return response()->json('OK');
    }
}
