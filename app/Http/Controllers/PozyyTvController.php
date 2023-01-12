<?php

namespace App\Http\Controllers;

use Image;
use App\PozyyTv;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class PozyyTvController extends Controller
{
    public function adminAddVideo(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string',
            'description' => 'sometimes|string',
            'video' => 'required|mimes:mp4',
            'thumbnail' => 'required|mimes:png,jpg,jpeg'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->messages(), 422);
        }

        $image = $request->file('thumbnail');
        $input['imagename'] = time().'.'.$image->extension();

        $filePath = public_path('storage/videos/pozyy_tv/thumbnails');
        $img = Image::make($image->path());
        $img->resize(700, 464, function($const) {
            $const->aspectRatio();
        })->save($filePath.'/'.$input['imagename']);

        $pozyy_tv = PozyyTv::create([
            'title' => $request->title,
            'description' => $request->has('description') && $request->description != NULL ? $request->description : NULL,
            'video_url' => config('app.url').'/storage/videos/pozyy_tv/'.pathinfo($request->video->store('pozyy_tv', 'videos'), PATHINFO_BASENAME),
            'thumbnail' => config('app.url').'/storage/videos/pozyy_tv/thumbnails/'.$img->basename,
        ]);

        $pozyy_tv_videos = PozyyTv::all();

        return pozzy_httpOk($pozyy_tv_videos);
    }

    public function adminUpdateVideo(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string',
            'description' => 'sometimes|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->messages(), 422);
        }

        $pozyy_tv_video = PozyyTv::find($id);

        $pozyy_tv_video->update([
            'title' => $request->title,
            'description' => $request->has('description') && $request->description != NULL ? $request->description : $pozyy_tv_video,
        ]);

        if ($request->hasFile('thumbnail')) {
            Storage::disk('videos')->delete('/pozyy_tv/thumbnails/'.$pozyy_tv_video->thumbnail);

            $image = $request->file('thumbnail');
            $input['imagename'] = time().'.'.$image->extension();
            $filePath = public_path('storage/videos/pozyy_tv/thumbnails');
            $img = Image::make($image->path());
            $img->resize(700, 464, function($const) {
                $const->aspectRatio();
            })->save($filePath.'/'.$input['imagename']);

            $pozyy_tv_video->update([
                'thumbnail' => config('app.url').'/storage/videos/pozyy_tv/thumbnails/'.$img->basename,
            ]);
        }

        if ($request->hasFile('video')) {
            Storage::disk('videos')->delete('/pozyy_tv/'.$pozyy_tv_video->video_url);

            $pozyy_tv_video->update([
                'video_url' => config('app.url').'/storage/videos/pozzy_tv/'.pathinfo($request->video->store('pozzy_tv', 'videos'), PATHINFO_BASENAME),
            ]);
        }

        $pozyy_tv_videos = PozyyTv::all();

        return pozzy_httpOk($pozyy_tv_videos);
    }

    public function delete($id)
    {
        $pozyy_tv_video = PozyyTv::find($id);

        Storage::disk('videos')->delete('/pozyy_tv/thumbnails/'.$pozyy_tv_video->thumbnail);
        Storage::disk('videos')->delete('/pozyy_tv/'.$pozyy_tv_video->video_url);

        $pozyy_tv_video->delete();

        $pozyy_tv_videos = PozyyTv::all();

        return pozzy_httpOk($pozyy_tv_videos);
    }

    public function getVideos()
    {
        $pozzy_tv_videos = PozyyTv::all();

        return response()->json($pozzy_tv_videos, 200);
    }

    public function getVideo($id)
    {
        $pozyy_tv_video = PozyyTv::find($id);

        return response()->json($pozyy_tv_video, 200);
    }
}
