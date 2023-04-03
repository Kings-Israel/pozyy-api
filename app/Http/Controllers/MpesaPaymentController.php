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
       $token = Mpesa::oxerus_mpesaGenerateAccessToken();
       $curl = curl_init();
       curl_setopt($curl, CURLOPT_URL, $url);
       curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type:application/json', 'Authorization:Bearer ' . $token));
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
}
