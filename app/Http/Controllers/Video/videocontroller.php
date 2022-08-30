<?php

namespace App\Http\Controllers\Video;

use DB;
use Auth;
use App\School;
use App\Subchannel;
use App\Models\Video\Video;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\Video\Channel;
use App\Models\Video\UserVideo;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class videocontroller extends Controller
{
    public function admin_add_video(Request $request)
    {
        $rules = [
            'title' => 'required',
            'thumbnail' => 'required',
            'description' => 'required',
            'video' => 'required',
            'channel' => 'required'
        ];

        $messages = [
            'video.required' => 'Please add a video'
        ];

        $validate = Validator::make($request->all(), $rules, $messages);

        if ($validate->fails()) {
            return response()->json($validate->messages());
        }

        $user = Auth::user();
        $video = new Video;
        $video->user_id = $user->id;
        $video->school_id = null;
        $video->channel_id = $request->channel;
        $video->age = $request->age;
        $video->subject = $request->subject;
        $video->title = $request->title;
        $video->description = strip_tags($request->description);
        $video->thumbnail = config('services.app_url.url').'/storage/videos/thumbnails/'.pathinfo($request->thumbnail->store('thumbnails', 'videos'), PATHINFO_BASENAME);
        $video->video_url = pozzy_videoCompress($request->file('video'), $user);
        // $video->video_url = pathinfo($request->video->store('video', 'videos'), PATHINFO_BASENAME);
        $video->subchannel_id = $request->has('subchannel') && $request->subchannel != 'null' ? $request->subchannel : NULL;
        $video->save();
        return pozzy_httpCreated($video);
    }
    public function admin_update_video(Request $request)
    {
        $video = Video::findOrFail($request->id);
        $video->title = $request->title;
        $video->description = strip_tags($request->description);

        if($request->hasFile('thumbnail')) {
            $exploded = explode(',', $request->thumbnail);
            $decoded = base64_decode($exploded[1]);
            if(Str::contains($exploded[0], 'jpeg'))
                $extension = 'jpg';
            else
                $extension = 'png';
            $fileName = time().'.'.$extension;
            $path = public_path('storage/thumbnails').'/'.$fileName;
            file_put_contents($path, $decoded);

            $video->thumbnail = $fileName;
        }

        $video->update();
        $videos = Video::orderBy('id', 'desc')->with(['user'])->get();
        return pozzy_httpOk($videos);
    }
    public function admin_delete_video(Request $request)
    {
        $video = Video::findOrFail($request->id);

        Storage::disk('videos')->delete('video/'.$video->video_url);

        // unlink(public_path('storage/thumbnails/'.$video->thumbnail));
        Storage::disk('videos')->delete('thumbnails/'.$video->thumbnail);

        $video->delete();
        return pozzy_httpOk('Video deleted successfully');
    }
    public function admin_show_videos()
    {
        $data = Video::orderBy('id', 'desc')->with(['user'])->get();
        foreach ($data as $key => $video) {
            if($video->school_id != null) {
                $video['school'] = School::find($video->school_id);
            } else {
                $video['school'] = null;
            }
        }
        return pozzy_httpOk($data);
    }
    public function admin_show_video($id)
    {
        $video = Video::find($id);
        return pozzy_httpOk($video);
    }
    public function count_videos()
    {
        $data = Video::get()->count();
        return pozzy_httpOk($data);
    }
    public function school_add_video(Request $request)
    {
        $user = Auth::user();
        DB::transaction(function() use($request,$user) {
            $video = new Video;
            $video->user_id = $user->id;
            $video->school_id = $user->school_id;
            $video->age = $request->age;
            $video->subject = $request->subject;
            $video->stream_id = $request->stream;
            $video->title = $request->title;
            $video->description = strip_tags($request->description);
            $video->video_url = pozzy_videoCompress($request->file('video'), $user);
            $video->subchannel_id = $request->has('subchannel') && $request->subchannel != NULL ? $request->subchannel : NULL;
            $video->save();
            $data = [
                'user_id' => $user->id,
                'video_id' => $video->id
            ];
            UserVideo::create($data);
        });
        return pozzy_httpCreated('Video created successfully.');
    }
    public function school_show_video($id)
    {
        $video = Video::find($id);
        return pozzy_httpOk($video);
    }
    public function school_show_videos()
    {
        $user = Auth::user();
        if(Auth::user()->getRoleNames()[0] == 'teacher') {
            $video = Video::where([['user_id', $user->id]])->with(['stream'])->get();
            return pozzy_httpOk($video);
        } else if(Auth::user()->getRoleNames()[0] == 'school') {
            $video = Video::where([['school_id', $user->school_id]])->with(['user'])->get();
            return pozzy_httpOk($video);
        } else {
            return pozzy_httpForbidden('Oops, you have no right to perform this operation');
        }
    }
    public function school_count_videos()
    {
        $user = Auth::user();
        $data = Video::where([['school_id', $user->school_id]])->get()->count();
        return pozzy_httpOk($data);
    }
    public function school_update_video(Request $request)
    {
        $video = Video::findOrFail($request->id);
        $video->title = $request->title;
        $video->description = strip_tags($request->description);

        if($request->hasFile('thumbnail')) {
            $exploded = explode(',', $request->thumbnail);
            $decoded = base64_decode($exploded[1]);
            if(Str::contains($exploded[0], 'jpeg'))
                $extension = 'jpg';
            else
                $extension = 'png';
            $fileName = time().'.'.$extension;
            $path = public_path('storage/thumbnails').'/'.$fileName;
            file_put_contents($path, $decoded);

            $video->thumbnail = $fileName;
        }

        $video->update();

        $user = Auth::user();
        if(Auth::user()->getRoleNames()[0] == 'teacher') {
            $videos = Video::where('user_id', $user->id)->with(['stream'])->get();
            return pozzy_httpOk($videos);
        } else if(Auth::user()->getRoleNames()[0] == 'school') {
            $videos = Video::where('school_id', $user->school_id)->with(['user'])->get();
            return pozzy_httpOk($videos);
        }
    }
    public function school_delete_video(Request $request)
    {
        $video = Video::findOrFail($request->id);

        Storage::disk('videos')->delete('video/'.$video->video_url);

        // unlink(public_path('storage/thumbnails/'.$video->thumbnail));
        Storage::disk('videos')->delete('thumbnails/'.$video->thumbnail);

        $video->delete();
        return pozzy_httpOk('Video deleted successfully');
    }
    public function show_channel($id)
    {
        $channel = Channel::with('videos.subchannel', 'subchannels.videos')->where('id', $id)->first();

        return response()->json($channel, 200);
    }
    public function add_channel(Request $request)
    {
        $rules = [
            'name' => ['required', 'unique:channels'],
            'description' => 'required',
            'type' => ['required'],
            'thumbnail' => 'required'
        ];

        $messages = [
            'name.required' => 'Please fill in the name',
            'description.required' => 'Please enter a description',
            'type.required' => 'Please select the audience type',
            'thumbnail.required' => 'Please upload an image for the thumbnail',
        ];

        $validate = Validator::make($request->all(), $rules, $messages);

        if ($validate->fails()) {
            return response()->json($validate->messages());
        }

        $data = [
            'name' => $request->name,
            'description' => strip_tags($request->description),
            'school_id' => Auth::user()->school_id,
            'user_id' => Auth::user()->id,
            'type' => $request->type,
            'is_guide' => $request->has('is_guide') && $request->is_guide == true ? true : false,
            'thumbnail' => pathinfo($request->thumbnail->store('thumbnails', 'channel'), PATHINFO_BASENAME),
        ];


        $channel = Channel::create($data);

        return pozzy_httpOk($channel->loadCount('videos', 'subchannels'));
    }
    public function add_subchannel(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'channel_id' => ['required'],
            'name' => ['required'],
            'thumbnail' => ['required', 'mimes:png,jpg,jpeg'],
        ]);

        if ($validator->fails()) {
            return response()->json($validator->messages(), 422);
        }

        $subchannel = Subchannel::create([
            'channel_id' => $request->channel_id,
            'name' => $request->name,
            'thumbnail_url' => config('services.app_url.url').'/storage/channel/subchannel/thumbnail/'.pathinfo($request->thumbnail->store('subchannel/thumbnail', 'channel'), PATHINFO_BASENAME),
        ]);

        return response()->json(['message' => 'Subchannel created successfullly', 'data' => $subchannel], 201);
    }
    public function update_channel(Request $request)
    {
        $rules = [
            'channel_id' => 'required',
            'name' => 'required',
            'description' => 'required',
            'thumbnail' => 'required'
        ];

        $messages = [
            'name.required' => 'Please fill in the name',
            'description.required' => 'Please enter a description',
            'thumbnail.required' => 'Please upload an image for the thumbnail'
        ];

        $validate = Validator::make($request->all(), $rules, $messages);

        if ($validate->fails()) {
            return response()->json($validate->messages());
        }

        $channel = Channel::find($request->channel_id);
        $channel->update([
            'name' => $request->name,
            'description' => strip_tags($request->description),
        ]);

        if($request->hasFile('thumbnail')) {
            Storage::disk('channel')->delete('thumbnails/'.$channel->thumbnail);
            $channel->update([
                'thumbnail' => pathinfo($request->thumbnail->store('thumbnails', 'channel'), PATHINFO_BASENAME)
            ]);
        }

        $channels = Channel::withCount('videos', 'subchannels')->get();
        return pozzy_httpOk($channels);
    }
    public function update_subchannel(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required'],
        ]);

        if ($validator->fails()) {
            return response()->json($validator->messages(), 422);
        }

        $subchannel = Subchannel::find($id);
        $subchannel->update([
            'name' => $request->name,
        ]);

        if ($request->hasFile('thumbnail') && $request->thumbnail != NULL) {
            Storage::disk('videos')->delete('subchannel/thumbnail/'.$subchannel->thumbnail);
            $subchannel->update([
                'thumbnail_url' => config('services.app_url.url').'/storage/channel/subchannel/thumbnail/'.pathinfo($request->thumbnail->store('subchannel/thumbnail', 'channel'), PATHINFO_BASENAME),
            ]);
        }

        return response()->json('Subchannel updated', 200);

        return response()->json(['message' => 'Subchannel created successfullly', 'data' => $subchannel], 201);
    }
    public function all_channel()
    {
        $data = Channel::with('videos', 'subchannels')->get();
        return pozzy_httpOk($data);
    }
    public function channel_video(Request $request)
    {
        $this->validate($request, ['channel_id' => 'required']);
        if($request->grade_id != null) {
            $data = Video::where([
                ['channel_id', $request->channel_id],
                ['grade_id', $request->grade_id]
            ])->get();
            return pozzy_httpOk($data);
        } else {
            $data = Video::where([
                ['channel_id', $request->channel_id]
            ])->get();
            return pozzy_httpOk($data);
        }
    }
    public function subchannel_videos(Request $request)
    {
        $this->validate($request, [
            'channel_id' => 'required',
            'subchannel_id' => 'required'
        ]);

        $data = Video::where('channel_id', $request->channel_id)->where('subchannel_id', $request->subchannel_id)->get();

        return pozzy_httpOk($data);
    }
}
