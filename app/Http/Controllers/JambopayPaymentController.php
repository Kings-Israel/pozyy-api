<?php

namespace App\Http\Controllers;

use App\Event;
use App\EventUserTicket;
use App\GameNight;
use App\Helpers\NumberGenerator;
use App\JambopayPayment;
use App\UserGameNight;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class JambopayPaymentController extends Controller
{
    //jambopay access token
    public static function accessToken()
    {
        $url = config('services.jambopay.url');
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);

        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/x-www-form-urlencoded',
            )
        );
        curl_setopt($curl, CURLOPT_POSTFIELDS,
            'username='.config('services.jambopay.username').'&Grant_type=password&Password='.config('services.jambopay.password').'',
        );

        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $curl_response = curl_exec($curl);
        $access_token=json_decode($curl_response);
        curl_close($curl);
        return $access_token->access_token;
    }

    /**
     * Get Jambopay access token
     *
     * @authenticated
     *
     * @bodyParam id ID of item being paid for
     * @bodyParam type Type of item being paid for: GameNight, Event
     *
     * @response 200
     *
     * @responseParam access_token The generated access token
     * @responseParma invoice_number The unique generated invoice of the payment
     */
    public function getAccessToken(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'type' => 'required',
            'user_id' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->messages(), 422);
        }

        $invoice_number = NumberGenerator::generateNumber(JambopayPayment::class, 'invoice_id');

        $type = '';
        $amount = '';

        if ($request->type === 'Event') {
            $event = Event::find($request->id);
            $type = Event::class;
            $amount = $event->ticket_price;

            EventUserTicket::create([
                'user_id' => $request->user_id,
                'event_id' => $event->id,
                'mpesa_checkout_request_id' => $invoice_number,
            ]);
        } elseif ($request->type === 'GameNight') {
            $game_night = GameNight::find($request->id);
            $type = GameNight::class;
            $amount = $game_night->price;
        }

        $token = $this->accessToken();

        JambopayPayment::create([
            'invoice_id' => $invoice_number,
            'user_id' => $request->user_id,
            'jambopay_payable_id' => $request->id,
            'jambopay_payable_type' => $type,
        ]);

        return response()->json([
            'success' => true,
            'invoice_id' =>$invoice_number,
            'access_token' => $token,
            'amount' => $amount,
            'client_key' => config('services.jambopay.client_key'),
            'callback_url' => route('jambopay.callback'),
            'cancel_url' => route('jambopay.cancel'),
        ], 200);
    }

    public function callback(Request $request)
    {
        info($request->all());
        if ($request->Status === 'SUCCESS') {
            $payment = JambopayPayment::where('invoice_id', $request->Order_Id)->first();

            $payment->update([
                'receipt' => $request->Receipt,
            ]);

            if($payment->jambopay_payable_type === 'App\Event') {
                $eventUserTicket = EventUserTicket::where('mpesa_checkout_request_id', $request->invoice_id)->first();
                $eventUserTicket->update([
                    'isPaid' => true,
                ]);
            } else {
                UserGameNight::create([
                    'user_id' => $payment->user_id,
                    'game_night_id' => $payment->jambopay_payable_id
                ]);
            }

            return view('jambopay-success');
        } else {
            return view('jambopay-cancel');
        }
    }
}
