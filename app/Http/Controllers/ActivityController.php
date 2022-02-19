<?php

namespace App\Http\Controllers;

use App\{Activity};
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class ActivityController extends Controller
{
    public function index()
    {
        $activities = Activity::all();
        return response()->json($activities);
    }

    public function store(Request $request)
    {
        $rules = [
            'cover_image' => 'required|image|mimes:png,jpg,jpeg',
            'category' => 'required',
            'title' => 'required',
            'date' => 'required|date',
            'venue' => 'required',
            'ticket_price' => 'required'
        ];

        $messages = [
            'required' => 'Please enter this value',
            'image' => 'Please select a valid image'
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if($validator->fails()){
            return response()->json($validator->messages(), 200);
        }

        $activity = new Activity;

        $activity->title = $request->title;
        $activity->category = $request->category;
        $activity->date = $request->date;
        $activity->venue = $request->venue;
        $activity->ticket_price = $request->ticket_price;
        $activity->cover_image = pathinfo($request->cover_image->store('images', 'activity'), PATHINFO_BASENAME);

        if ($activity->save()) {
            return response()->json($activity, 200);
        }

        return response()->json(['message' => 'failed'], 500);
    }

    public function edit($id)
    {
        $activity = Activity::find($id);
        return response()->json($activity, 200);
    }

    public function update(Request $request, $id)
    {
        $rules = [
            'category' => 'required',
            'title' => 'required',
            'date' => 'required|date',
            'venue' => 'required',
            'ticket_price' => 'required'
        ];

        $messages = [
            'required' => 'Please enter this value',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if($validator->fails()){
            return response()->json($validator->messages(), 200);
        }

        $activity = Activity::find($id);

        $activity->title = $request->title;
        $activity->category = $request->category;
        $activity->date = $request->date;
        $activity->venue = $request->venue;
        $activity->ticket_price = $request->ticket_price;

        if($request->hasFile('cover_image')){
            $rules = [
                'category' => 'image|mimes:png,jpg,jpeg',
            ];

            $messages = [
                'image' => 'Please upload an image here',
            ];

            $validator = Validator::make($request->all(), $rules, $messages);

            if ($validator->fails()) {
                return response()->json($validator->messages(), 200);
            }

            $activity->cover_image = storage_path('activity/images/'.pathinfo($request->cover_image->store('images', 'activity'), PATHINFO_BASENAME));
        }

        if ($activity->save()) {
            return response()->json(['activity' => $activity, 'message' => 'Activity added'], 200);
        }

        return response()->json(['message' => 'Failed'], 500);

    }

    public function delete($id)
    {
        if(Activity::destroy($id)) {
            return response()->json(['message' => 'activity deleted'], 200);
        } else {
            return response()->json(['message' => 'failed'], 500);
        }
    }
}
