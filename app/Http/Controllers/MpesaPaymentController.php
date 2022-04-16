<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\Mpesa;
use Carbon\Carbon;

class MpesaPaymentController extends Controller
{
  /**
    * @param $phone
    * @param $amount
    * @param $callback
    * @param $account_number
    * @param $remarks
    * @return array
    */
    public function stkPush($phone, $amount, $callback, $account_number, $remarks)
    {
       $url = Mpesa::oxerus_mpesaGetStkPushUrl();
       $curl = curl_init();
       curl_setopt($curl, CURLOPT_URL, $url);
       curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type:application/json', 'Authorization:Bearer ' . Mpesa::oxerus_mpesaGenerateAccessToken()));
       $curl_post_data = [
          'BusinessShortCode' => config('services.mpesa.business_shortcode'),
          'Password' => Mpesa::oxerus_mpesaLipaNaMpesaPassword(),
          'Timestamp' => Carbon::now()->format('YmdHis'),
          'TransactionType' => 'CustomerPayBillOnline',
          'Amount' => $amount,
          'PartyA' => $phone,
          'PartyB' => config('services.mpesa.business_shortcode'),
          'PhoneNumber' => $phone,
          'CallBackURL' => $callback,
          'AccountReference' => $account_number,
          'TransactionDesc' => $remarks,
       ];
       $data_string = json_encode($curl_post_data);
       curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
       curl_setopt($curl, CURLOPT_POST, true);
       curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
       $curl_response = curl_exec($curl);
       $responseObj = json_decode($curl_response);
       $response_details = [
          "merchant_request_id" => $responseObj->MerchantRequestID ?? null,
          "checkout_request_id" => $responseObj->CheckoutRequestID ?? null,
          "response_code" => $responseObj->ResponseCode ?? null,
          "response_desc" => $responseObj->ResponseDescription ?? null,
          "customer_msg" => $responseObj->CustomerMessage ?? null,
          "phone" => $phone,
          "amount" => $amount,
          "remarks" => $remarks
       ];

       return $response_details;
    }

    /**
     * @param Request $request
     */
    public function stkPushCallback(Request $request)
    {
       $callbackJSONData = file_get_contents('php://input');
       $callbackData = json_decode($callbackJSONData);

       info($callbackJSONData);

       $result_code = $callbackData->Body->stkCallback->ResultCode;
       // $result_desc = $callbackData->Body->stkCallback->ResultDesc;
       $merchant_request_id = $callbackData->Body->stkCallback->MerchantRequestID;
       $checkout_request_id = $callbackData->Body->stkCallback->CheckoutRequestID;
       $amount = $callbackData->Body->stkCallback->CallbackMetadata->Item[0]->Value;
       $mpesa_receipt_number = $callbackData->Body->stkCallback->CallbackMetadata->Item[1]->Value;
       // $transaction_date = $callbackData->Body->stkCallback->CallbackMetadata->Item[3]->Value;
       // $phone_number = $callbackData->Body->stkCallback->CallbackMetadata->Item[4]->Value;


       $result = [
          // "result_desc" => $result_desc,
          "result_code" => $result_code,
          "merchant_request_id" => $merchant_request_id,
          "checkout_request_id" => $checkout_request_id,
          "amount" => $amount,
          "mpesa_receipt_number" => $mpesa_receipt_number,
          // "phone" => $phone_number,
          // "transaction_date" => Carbon::parse($transaction_date)->toDateTimeString()
       ];

       if($result['result_code'] == 0) {
          $mpesaPayments = MpesaPayment::where('checkout_request_id', $result['checkout_request_id'])->get();

          foreach ($mpesaPayments as $payment) {
             $order = Order::find($payment->order_id);
             $order->status = "Paid";
             $order->save();

             // If the Order has a linked event, add transaction to the budgets table
             if ($order->event_id != null) {
                $event = Event::find($order->event_id);
                if ($event) {
                   $budget = Budget::firstOrCreate([
                      'event_id' => $event->id,
                      'title' => 'Initial Budget',
                   ]);

                   $category = Category::find($order->service->category_id);

                   BudgetTransaction::create([
                      'budget_id' => $budget->id,
                      'event_id' => $order->event_id,
                      'type' => 'Expense',
                      'title' => 'Order '. $order->order_id.' payment',
                      'description' => 'An expense for payment of the order '.$order->order_id,
                      'amount' => $order->service_pricing ? $order->service_pricing->service_pricing_price : $order->order_quotation->order_pricing_price,
                      'date' => now(),
                      'reference' => $result['mpesa_receipt_number'],
                      'transaction_service_category' => $category->name,
                   ]);
                }
             }

             Payment::create([
                'order_id' => $order->id,
                'user_id' => $order->user_id,
                'amount' => $order->service_pricing ? $order->service_pricing->service_pricing_price : $order->order_quotation->order_pricing_price,
                'payment_method' => 'mpesa',
                'transaction_id' => $result['mpesa_receipt_number']
             ]);

             // Notify Vendor
             $order->service->vendor->notify(new VendorNotification($order, 'Order Paid'));
             // Notify Client
             $order->user->notify(new ClientNotification($order, 'Successful Payment'));
             SendSms::dispatchAfterResponse($order->service->vendor->company_phone_number, 'The client completed the payment for the order '.$order->order_id);
          }
       }
    }
}
