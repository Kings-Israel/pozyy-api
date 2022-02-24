<?php

namespace App\Http\Controllers\Students;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Video\{Video,Channel};
use App\Kid;
use Auth;
use App\Models\Grade;

class studentscontroller extends Controller
{

    public function all_channels(Request $request){
        $data = Channel::where('school_id',null)->where('suspend',0)->get();
        return pozzy_httpOk($data);
    }

    public function all_videos(Request $request) {
        $this->validate($request, ['grade_id' => 'required']);
        if($request->subject_id != null) {
            $data = Video::where([['school_id', null],['grade_id', $request->grade_id],['subject_id', $request->subject_id]])
                        ->get();
            return pozzy_httpOk($data);
        } else {
            $data = Video::where([['school_id', null],['grade_id', $request->grade_id]])
                        ->get();
            return pozzy_httpOk($data);
        }
    }
    public function school_video(Request $request) {
        $this->validate($request, ['school_id' => 'required', 'grade_id' => 'required','stream_id' => 'required']);
        if($request->subject_id != null) {
            $data = Video::where(
                [
                    ['school_id', $request->school_id],
                    ['grade_id', $request->grade_id],
                    ['subject_id', $request->subject_id],
                    ['stream_id', $request->stream_id]
                ]
            )->get();
            return pozzy_httpOk($data);
        } else {
            $data = Video::where(
                [
                    ['school_id', $request->school_id],
                    ['grade_id', $request->grade_id],
                    ['stream_id', $request->stream_id]
                ]
            )->get();
            return pozzy_httpOk($data);
        }
    }
    public function add_kid(Request $request) {
        if(Auth::user()->getRoleNames()[0] == 'parent') {
            $gradeId = Grade::where("name", $request->grade)->first();

            $data = [
                'fname' => $request->fname,
                'lname' => $request->lname,
                'gender' => $request->gender,
                // 'grade_id' => $gradeId,
                'parent_id' => Auth::user()->id,
                // 'school_id' => Auth::user()->school_id
            ];
            Kid::create($data);
            return pozzy_httpCreated('Student added successfully');
        }
        return pozzy_httpForbidden('Oops, you have no right to perform this operation');
    }
    public function edit_kid(Request $request, $id) {
        if(Auth::user()->getRoleNames()[0] == 'parent') {
            $kid = Kid::where('id', $id)->first();
            $gradeId = Grade::where("name", $request->grade)->first();

            if($kid->parent_id != Auth::user()->id) {
                return pozzy_httpForbidden('Oops, you have no privilege to edit student');
            }
            $data = [
                'fname' => $request->fname,
                'lname' => $request->lname,
                'gender' => $request->gender,
                'grade_id' => $gradeId
            ];
            $kid->update($data);
            return pozzy_httpOk('Student edited successfully');
        }
        return pozzy_httpForbidden('Oops, you have no right to perform this operation');
    }
    public function choose_between_student(Request $request) {
        $kid = Kid::where('id', $request->student_id)->with('parent')->first();
        return pozzy_httpOk($kid);
    }

    public function get_kids(){
        if(Auth::user()->getRoleNames()[0] == 'parent') {
            $kids = Kid::where('parent_id', Auth::user()->id)->get();
            return pozzy_httpOk($kids);
        }
        return pozzy_httpForbidden('Oops, you have no right to perform this operation');
    }
}
