<?php

namespace App\Http\Controllers;

use App\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

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
}
