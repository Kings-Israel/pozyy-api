<?php

namespace App\Http\Controllers;

use App\Enum\MobileSections;
use App\MobileMedia;
use Illuminate\Http\Request;

class MobileMediaController extends Controller
{
    public function getSections()
    {
        $sections = [
            MobileSections::PARENT()->label, MobileSections::KIDS()->label
        ];

        return pozzy_httpOk($sections);
    }

    public function getSectionThumbnails()
    {
        $sections = [
            MobileSections::PARENT()->label, MobileSections::KIDS()->label
        ];

        $data = collect($sections)->each(function ($section) {
            MobileMedia::where('section', $section)->first();
        });

        return pozzy_httpOk($data);
    }

    public function submitThumbnail(Request $request)
    {
        
    }
}
