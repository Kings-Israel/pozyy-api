<?php

use Illuminate\Support\Facades\Log;
use App\Jobs\CompressVideo;
use Intervention\Image\ImageManagerStatic as Image2;

function pozzy_videoCompress($video, $user) {
    $folder = 'storage/videos/video/';
    // $video_name = time() . rand(100,999) . '.mp4';

    $video_name = pathinfo($video->store('video', 'videos'), PATHINFO_BASENAME);
    $new_folder = public_path($folder . $video_name);
    $bitrate = "700k";
    // $storage_path=  Storage::disk('public')->makeDirectory('cec/');
    // $storage_path_full = '/'.$video_name;
    // $localVideo =  Storage::disk('public')->put($storage_path_full,  file_get_contents($video));

    $data = [
        'video_name' => $video_name,
        'bitrate' => $bitrate,
        'new_folder' => $new_folder
    ];

    CompressVideo::dispatch($data)->delay(now()->addSeconds(10));

    return $video_name;
}
function pozzy_Images($image) {
    $filename = time() . '.' . $image->getClientOriginalExtension();
    $location = public_path('uploaded_images/' . $filename);
    Image2::make($image)->resize(800,400)->save($location);
    return $filename;
}
