<?php

namespace App\Http\Controllers\Students;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Video\{Video,Channel};
use App\Kid;
use Auth;
use App\Models\Grade;
use App\School;

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
        $this->validate($request, ['school_id' => 'required']);

        $data = Video::where('school_id', $request->school_id)->get();
        if ($data->count()) {
            return pozzy_httpOk($data);
        }

        return pozzy_httpNotFound('No School Videos Found');
    }
    public function add_kid(Request $request) {
        if(Auth::user()->getRoleNames()[0] == 'parent') {

            $data = [
                'fname' => $request->fname,
                'lname' => $request->lname,
                'gender' => $request->gender,
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

            if($kid->parent_id != Auth::user()->id) {
                return pozzy_httpForbidden('Oops, you have no privilege to edit student');
            }
            $data = [
                'fname' => $request->fname,
                'lname' => $request->lname,
                'gender' => $request->gender,
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

    public function verifyCode(Request $request)
    {
        $this->validate($request, ['code' => 'required']);

        $school = School::where('school_register_id', $request->code)->first();

        if ($school) {
            return pozzy_httpOk($school);
        }

        return pozzy_httpNotFound('The code was incorrect');
    }
}
