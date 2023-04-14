<?php

namespace App\Http\Controllers\Students;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Video\{Video,Channel};
use App\Kid;
use App\KidPerformance;
use Illuminate\Support\Facades\Auth;
use App\Models\Grade;
use App\School;
use Illuminate\Support\Facades\Validator;

class studentscontroller extends Controller
{

    public function all_channels(Request $request)
    {
        $data = Channel::where('school_id',null)->where('suspend',0)->get();
        return pozzy_httpOk($data);
    }

    public function all_videos(Request $request)
    {
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

    public function school_video($school_id)
    {
        $school = School::where('id', $school_id)->orWhere('school_register_id', $school_id)->first();

        if (!$school) {
            return pozzy_httpNotFound('School not found');
        }

        $data = Video::where('school_id', $school->id)->get();

        if ($data->count()) {
            return pozzy_httpOk($data);
        }

        return pozzy_httpNotFound('No School Videos Found');
    }

    public function add_kid(Request $request)
    {
        $validatedData = Validator::make($request->all(), [
            'fname' => ['required'],
            'lname' => ['required'],
            'age' => ['required'],
            'gender' => ['required']
        ], [
            'fname.required' => 'Please enter the first name',
            'lname.required' => 'Please enter the last name',
            'age.required' => 'Please enter the age',
            'gender.required' => 'Please enter the gender',
        ]);

        if ($validatedData->fails()){
            return response()->json([
                'message' => "invalid data",
                'errors' =>[$validatedData->messages()]
            ], 400);
        }
        if(Auth::user()->getRoleNames()[0] == 'parent') {

            $data = [
                'fname' => $request->fname,
                'lname' => $request->lname,
                'gender' => $request->gender,
                'parent_id' => Auth::user()->id,
                'school_id' => $request->has('school') && $request->school != NULL ? $request->school : NULL,
                'grade_id' => $request->has('grade') && $request->grage != NULL ? $request->grade : NULL
            ];
            Kid::create($data);
            return pozzy_httpCreated('Student added successfully');
        }
        return pozzy_httpForbidden('Oops, you have no right to perform this operation');
    }

    public function edit_kid(Request $request, $id)
    {
        if(Auth::user()->getRoleNames()[0] == 'parent') {
            $kid = Kid::where('id', $id)->first();

            if($kid->parent_id != Auth::user()->id) {
                return pozzy_httpForbidden('Oops, you have no privilege to edit student');
            }

            $school = NULL;
            if ($request->has('school_id')) {
                $school = School::find($request->school_id);
                if (!$school) {
                    return pozzy_httpNotFound('Oops, the school was not found!!');
                }
            }
            $data = [
                'fname' => $request->fname,
                'lname' => $request->lname,
                'gender' => $request->gender,
                'school_id' => $school != NULL ? $school->id : NULL,
            ];
            $kid->update($data);
            return pozzy_httpOk('Student edited successfully');
        }
        return pozzy_httpForbidden('Oops, you have no right to perform this operation');
    }

    public function choose_between_student(Request $request)
    {
        $kid = Kid::where('id', $request->student_id)->with('parent', 'school')->first();
        
        return pozzy_httpOk($kid);
    }

    public function get_kids()
    {
        if(Auth::user()->getRoleNames()[0] == 'parent') {
            $kids = Kid::with('performances', 'clubs', 'grade', 'school')->where('parent_id', Auth::user()->id)->get();
            return pozzy_httpOk($kids);
        }
        return pozzy_httpForbidden('Oops, you have no right to perform this operation');
    }

    public function verifyCode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required',
            'kid_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->messages(), 422);
        }

        $school = School::where('school_register_id', $request->code)->first();

        if (!$school) {
            return pozzy_httpNotFound('Invalid school code');
        }

        $kid = Kid::find($request->kid_id);

        if (!$kid) {
            return pozzy_httpNotFound('The Student was not found');
        }

        $kid->update([
            'school_id' => $school->id,
        ]);

        if ($school) {
            return pozzy_httpOk($school);
        }

        return pozzy_httpNotFound('The code was incorrect');
    }

    public function getKid($id)
    {
        $kid = Kid::where('id', $id)->first();

        if (!$kid) {
            return pozzy_httpNotFound('The student was not found');
        }

        $kid->load(['grade', 'school', 'performances' => function($query) {
            return Grade::find('performances.grade_id');
        }]);

        return pozzy_httpOk($kid);
    }

    public function assignGrade(Request $request)
    {
        $this->validate($request, [
            'student_id' => ['required'],
            'grade_id' => ['required']
        ]);

        $student = Kid::find($request->student_id);
        $student->update([
            'grade_id' => $request->grade_id
        ]);

        return pozzy_httpOk($student);
    }

    public function addKidPerformance(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'kid_id' => ['required'],
            'grade_id' => ['required'],
            'student_performance' => ['required']
        ]);

        if ($validator->fails()) {
            return pozzy_httpBadRequest($validator->errors()->messages());
        }

        $performance = explode(',', $request->student_performance);
        $student_performance = [];

        for ($i=0; $i < count($performance) ; $i++) {
            if (!ctype_digit($performance[$i])) {
                $student_performance[$performance[$i]] = $performance[$i + 1];
            }
        }

        // Check if performance for the grade has already been uploaded
        $recorededPerformances = KidPerformance::where('kid_id', $request->kid_id)->where('grade_id', $request->grade_id)->first();
        if ($recorededPerformances) {
            return pozzy_httpForbidden('Performance this grade have already been recoreded');
        }

        $kidPerformance = new KidPerformance;
        $kidPerformance->kid_id = $request->kid_id;
        $kidPerformance->grade_id = $request->grade_id;
        $kidPerformance->kid_performance = json_encode($student_performance);

        // Calculate average performance
        $total_marks = 0;
        foreach ($student_performance as $subject => $performance) {
            $total_marks += $performance;
        }

        $kidPerformance->average_performance = $total_marks;
        $kidPerformance->save();

        return pozzy_httpOk('Performance saved successfully');
    }
}
