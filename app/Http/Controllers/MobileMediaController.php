<?php

namespace App\Http\Controllers;

use App\Enum\MobileSections;
use App\MobileMedia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MobileMediaController extends Controller
{
    private function deleteFile($filePath, $folder)
    {
        $file = collect(explode('/', $filePath));
        Storage::disk('mobile-media')->delete($folder.'/'.$file->last());
    }
    public function getSections()
    {
        return pozzy_httpOk(MobileMedia::all());
    }

    public function getSectionThumbnails()
    {
        return pozzy_httpOk(MobileMedia::all());
    }

    public function getThumbnail(string $name)
    {
        $data = MobileMedia::where('section', $name)->first();
        return pozzy_httpOk($data);
    }

    public function submitThumbnail(Request $request)
    {
        $this->validate($request, [
            'section_name' => ['required'],
            'thumbnail' => ['required']
        ]);

        $data = MobileMedia::create([
            'section' => $request->section_name,
            'thumbnail' => config('services.app_url.url').'/storage/mobile-media/thumbnails/'.pathinfo($request->thumbnail->store('thumbnails', 'mobile-media'), PATHINFO_BASENAME)
        ]);

        return pozzy_httpOk($data);
    }

    public function updateThumbnail(Request $request)
    {
        $this->validate($request, [
            'section_id' => ['required'],
            'thumbnail' => ['required']
        ]);

        $data = MobileMedia::find($request->section_id);
        $this->deleteFile($data->thumbnail_url, 'mobile-media/thumbnail');

        $data->update([
                'thumbnail_url' => config('services.app_url.url').'/storage/mobile-media/thumbnails/'.pathinfo($request->thumbnail->store('thumbnails', 'mobile-media'), PATHINFO_BASENAME)
            ]);

        return pozzy_httpOk($data);
    }
}
