<?php

namespace App\Http\Controllers;

use App\JambopayPayment;
use App\MpesaPayment;
use Illuminate\Http\Request;

class PaymentsController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        // Get Game night payments
        // Mpesa payments
        $mpesa_payments = MpesaPayment::with([
                                            'mpesaPayable',
                                            'user' => function($query) {
                                                $query->withTrashed();
                                            }
                                        ])
                                        ->where('checkout_request_id', '!=', NULL)
                                        ->orderBy('created_at', 'DESC')
                                        ->get();
        // Jambopay Payments
        $jambopay_payments = JambopayPayment::with([
                                                'jambopayPayable',
                                                'user' => function($query) {
                                                    $query->withTrashed();
                                                }
                                            ])
                                            ->where('receipt', '!=', NULL)
                                            ->orderBy('created_at', 'DESC')
                                            ->get();

        return pozzy_httpOk(['mpesa_payments' => $mpesa_payments, 'jambopay_payments' => $jambopay_payments]);
    }
}
