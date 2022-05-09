<?php

namespace App\Http\Controllers;

use App\Event;
use App\EventUserTicket;
use App\MpesaPayment;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EventController extends Controller
{
  private function deleteFile($filePath, $folder)
  {
    $file = collect(explode('/', $filePath));
    Storage::disk('event')->delete($folder.'/'.$file->last());
  }

  public function allEvents()
  {
    $events = Event::all();

    return pozzy_httpOk($events);
  }

  public function addEvent(Request $request)
  {
    $this->validate($request, [
      'title' => ['required'],
      'description' => ['required'],
      'date' => ['required', 'date'],
      'venue' => ['required'],
      'poster' => ['required', 'mimes:png,jpg,jpeg'],
    ]);

    $event = Event::create([
      'title' => $request->title,
      'description' => strip_tags($request->description),
      'date' => $request->date,
      'venue' => $request->venue,
      'ticket_price' => $request->has('ticket_price') ? $request->ticket_price : NULL,
      'isFree' => $request->has('isFree') ? true : false,
      'poster' => config('services.app_url.url').'/storage/event/poster/'.pathinfo($request->poster->store('poster', 'event'), PATHINFO_BASENAME)
    ]);

    if ($event) {
        return pozzy_httpCreated($event);
    }

    return pozzy_httpBadRequest('An error occurred please try again');
  }

  public function updateEvent(Request $request)
  {
    $this->validate($request, [
      'title' => ['required'],
      'description' => ['required'],
      'date' => ['required', 'date'],
      'venue' => ['required'],
    ]);

    $event = Event::find($request->id);

    $event->title = $request->title;
    $event->description = strip_tags($request->description);
    $event->date = $request->date;
    $event->venue = $request->venue;
    $event->ticket_price = $request->has('ticket_price') ? $request->ticket_price : NULL;
    $event->isFree = $request->has('isFree') ? true : false;

    if ($request->hasFile('poster')) {
      $this->deleteFile($event->poster, 'poster');
      $event->poster = config('services.app_url.url').'/storage/event/poster/'.pathinfo($request->poster->store('poster', 'event'), PATHINFO_BASENAME);
    }

    $event->save();

    if ($event) {
        return pozzy_httpCreated($event);
    }

    return pozzy_httpBadRequest('An error occurred please try again');
  }

  public function deleteEvent($id)
  {
    $event = Event::find($id);

    $this->deleteFile($event->poster, 'poster');

    $event->delete();

    return pozzy_httpOk($event);
  }

  public function singleEvent($id)
  {
    $event = Event::find($id);

    return pozzy_httpOk($event);
  }

  public function adminGetEvent($id)
  {
      $event = Event::find($id);
      $event->load(['eventUserTickets' => function($query) {
          return $query->with('user')->where('isPaid', true);
      }]);

      return pozzy_httpOk($event);
  }

  public function buyTicket(Request $request)
  {
    $this->validate($request, [
      'event_id' => ['required'],
    ]);

    $phone_number = Auth::user()->phone_number;
    if (strlen($phone_number) == 9) {
        $phone_number = '254'.$phone_number;
    } else {
        $phone_number = '254'.substr($phone_number, -9);
    }

    $event = Event::find($request->event_id);
    if (!$event) {
        return pozzy_httpNotFound('The event was not found');
    }

    if ($request->has('number_of_tickets')) {
        $amount = $event->ticket_price * (int) $request->number_of_tickets;
    } else {
        $amount = $event->ticket_price;
    }

    $account_number = Str::upper(Str::random(3)).time().Str::upper(Str::random(3));
    $transaction = new MpesaPaymentController;
    $results = $transaction->stkPush(
        $phone_number,
        $amount,
        route('event.ticket.purchase.callback'),
        $account_number,
        'Purchase of Event Ticket'
    );

    if ($results['response_code'] != NULL) {
        $mpesa_payable_type = Event::class;
        MpesaPayment::create([
            'user_id' => Auth::user()->id,
            'user_phone_number' => $phone_number,
            'mpesa_payable_id' => $event->id,
            'mpesa_payable_type' => $mpesa_payable_type,
            'checkout_request_id' => $results['checkout_request_id']
        ]);

        EventUserTicket::create([
            'user_id' => auth()->user()->id,
            'event_id' => $event->id,
            'mpesa_checkout_request_id' => $results['checkout_request_id']
        ]);
    }

    return pozzy_httpOk($results);
  }

  public function buyTicketMpesaCallback(Request $request)
  {
    $callbackJSONData = file_get_contents('php://input');
    $callbackData = json_decode($callbackJSONData);

    info($callbackJSONData);

    $result_code = $callbackData->Body->stkCallback->ResultCode;
    $merchant_request_id = $callbackData->Body->stkCallback->MerchantRequestID;
    $checkout_request_id = $callbackData->Body->stkCallback->CheckoutRequestID;
    $amount = $callbackData->Body->stkCallback->CallbackMetadata->Item[0]->Value;
    $mpesa_receipt_number = $callbackData->Body->stkCallback->CallbackMetadata->Item[1]->Value;

    if($result_code === 0) {
        $mpesaPayment = MpesaPayment::where('checkout_request_id', $checkout_request_id)->first();
        $mpesaPayment->mpesa_receipt_number = $mpesa_receipt_number;
        $mpesaPayment->save();

        $eventUserTicket = EventUserTicket::where('mpesa_checkout_request_id', $checkout_request_id)->first();
        $eventUserTicket->update([
            'isPaid' => true,
        ]);
    }
  }

  public function purchasedTickets()
  {
    $tickets = EventUserTicket::where('user_id', auth()->user()->id)->where('isPaid', true)->get();

    if (!$tickets->isEmpty()) {
        $events = [];
        foreach ($tickets as $ticket) {
            array_push($events, Event::find($ticket->event_id));
        }

        return pozzy_httpOk($events);
    }

    return pozzy_httpNotFound([]);

  }
}
