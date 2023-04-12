<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\{Bank, Kid, User, School, Stream};
use App\Models\{Grade,Test,Subject};
use App\Models\Clubs\{Club,ClubActivity};
use Illuminate\Support\Facades\Storage;
use DB;
use Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use App\Models\Video\Video;
use App\Models\Video\Channel;
use App\UserGameNight;

class schoolcontroller extends Controller
{
    public function banks()
    {
        return pozzy_httpOk(Bank::all());
    }
    public function all_schools()
    {
        $school = School::with('admin', 'grades')->get();
        return pozzy_httpOk($school);
    }

    public function add_school(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'logo' => 'required',
            'box' => 'required',
            'school_contact' => 'required',
            'fname' => 'required',
            'lname' => 'required',
            'email' => 'required|unique:users',
            'phone_number' => 'required|unique:users',
            'username' => 'required',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->messages(), 422);
        }

        try {
            $school = new School;
            $school->name = $request->name;
            $school->logo = config('services.app_url.url').'/storage/school/logo/'.pathinfo($request->logo->store('logo', 'school'), PATHINFO_BASENAME);
            $school->county = $request->county;
            $school->box = $request->box;
            $school->school_contact = $request->school_contact;
            $school->bank_name = $request->has('bank_name') && $request->bank_name != '' ? $request->bank_name : NULL;
            $school->bank_branch = $request->has('bank_branch') && $request->bank_branch != '' ? $request->bank_branch : NULL;
            $school->bank_account_holder_name = $request->has('bank_account_holder_name') && $request->bank_account_holder_name != '' ? $request->bank_account_holder_name : NULL;
            $school->bank_account_number = $request->has('bank_account_number') && $request->bank_account_number != '' ? $request->bank_account_number : NULL;
            $school->save();

            if (Auth::check()) {
                if (auth()->user()->getRoleNames()[0] === 'admin') {
                    $uniqueId = mt_rand(10000, 99999);
                    $schoolsId = School::all()->pluck('school_register_id');
                    while ($schoolsId->contains($uniqueId)) {
                        $uniqueId = mt_rand(10000, 99999);
                    }
                    $school->update([
                        'school_register_id' => $uniqueId,
                        'suspend' => false
                    ]);
                }
            } else {
                $school->update([
                    'suspend' => true
                ]);
            }

            $user = new User;
            $user->school_id = $school->id;
            $user->username = $request->username;
            $user->fname = $request->fname;
            $user->lname = $request->lname;
            $user->email = $request->email;
            $user->phone_number = $request->phone_number;
            $user->password = bcrypt($request->password);
            $user->save();
            $user->assignRole('school');

            $school->load('admin');

            return pozzy_httpOk($school);
        } catch (\Exception $e) {
            throw new $e;
        }

    }
    public function edit_school(Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
            'box' => 'required',
            'school_contact' => 'required',
            'fname' => 'required',
            'lname' => 'required',
            'email' => 'required',
            'username' => 'required',
        ]);
        $edit = School::where('id', $request->id)->first();

        try {
            DB::transaction(function() use($edit, $request) {
                $edit->update([
                    'name' => $request->name,
                    'county' => $request->county,
                    'box' => $request->box,
                    'school_contact' => $request->school_contact,
                    'bank_name' => $request->has('bank_name') && $request->bank_name != '' ? $request->bank_name : $edit->bank_name,
                    'bank_branch' => $request->has('bank_branch') && $request->bank_branch != '' ? $request->bank_branch : $edit->bank_branch,
                    'bank_account_holder_name' => $request->has('bank_account_holder_name') && $request->bank_account_holder_name != '' ? $request->bank_account_holder_name : $edit->bank_account_holder_name,
                    'bank_account_number' => $request->has('bank_account_number') && $request->bank_account_number != '' ? $request->bank_account_number : $edit->bank_account_number,
                ]);
                if ($request->hasFile('logo')) {
                    $filePath = $edit->logo;
                    $file = collect(explode('/', $filePath));
                    Storage::disk('school')->delete('logo/'.$file->last());
                    $edit->update([
                        'logo' => config('services.app_url.url').'/storage/school/logo/'.pathinfo($request->logo->store('logo', 'school'), PATHINFO_BASENAME),
                    ]);
                }
                $user = User::where('school_id', $request->id)->update([
                    'username' => $request->username,
                    'fname' => $request->fname,
                    'lname' => $request->lname,
                    'phone_number' => $request->phone_number
                ]);

                $edit->load('admin');

                return pozzy_httpOk($edit);
            });
        } catch(\Exception $e) {
            return pozzy_httpBadRequest($e);
        }
    }

    public function suspend_school($id)
    {
        if(Auth::user()->getRoleNames()[0] == 'admin') {
            $sc = School::find($id);
            $sc->update([
                'suspend' => $sc->suspend == 1 ? 0 : 1
            ]);
            return pozzy_httpOk($sc);
        }
        return pozzy_httpForbidden('Oops, you have no privileges to run this operation');
    }

    public function delete_school($id)
    {
        //delete all tests and question
        Test::where('school_id', $id)->delete();
        //detach students from school/
        $kids = Kid::where('school_id', $id)->get();
        collect($kids)->each(function ($kid) {
            $kid->update([
                'school_id' => NULL
            ]);
        });
        //delete teachers and admin
        $users = User::where('school_id', $id)->get();
        collect($users)->each(function ($user) {
            $user->delete();
        });
        //delete school
        $school = School::find($id);
        $school->delete();

        return pozzy_httpOk($school);
    }

    public function school_data()
    {
        $userRole = Auth::user()->getRoleNames()[0];
        $school = School::with('admin')->find(Auth::user()->school_id);
        $kids = [];
        $kids = Kid::with(['parent', 'grade'])->where('school_id', $school->id)->get();
        $videos = Video::where('school_id', $school->id)->get();
        $channels = Channel::where('school_id', auth()->user()->school_id)->get();

        return pozzy_httpOk([$kids, $school, $videos, $channels]);
    }

    public function add_class(Request $request)
    {
        $data = Grade::all();
        foreach($data as $exist) {
            if($exist->school_id == Auth::user()->school_id && $exist->name == $request->name) {
                return pozzy_httpBadRequest('Grade already exists');
            }
        }
        if(Auth::user()->getRoleNames()[0] == 'school') {
            $grade = new Grade;
            $grade->school_id = Auth::user()->school_id;
            $grade->name = $request->name;
            $grade->user_id = $request->user_id;
            $grade->save();
            return pozzy_httpCreated($grade);
        } else {
            return pozzy_httpForbidden('Oops, you have no right to perform this operation');
        }
    }

    public function delete_class($id)
    {
        $grade = Grade::find($id);
        $grade->delete();
        return pozzy_httpOk($grade);
    }

    public function get_grade($id)
    {
        $grade = Grade::where('id', $id)->with('streams')->first();
        return pozzy_httpOk($grade);
    }

    public function all_grades()
    {
        $user = Auth::user();
        $grade = Grade::where('school_id', $user->school_id)->withCount('streams', 'subjects', 'kids')->get();
        return response()->json($grade);
    }

    public function add_stream(Request $request)
    {
        $this->validate($request, [
            'grade_id' => 'required',
            'name' => 'required'
        ]);
        if(Auth::user()->getRoleNames()[0] == 'school') {
            $stream = new Stream;
            $stream->name = $request->name;
            $stream->grade_id = $request->grade_id;
            $stream->school_id = Auth::user()->school_id;
            $stream->save();
            return pozzy_httpCreated($stream);
        }
        return pozzy_httpForbidden('Oops, you have no right to perform this operation');
    }

    public function get_grade_streams($id)
    {
        $streams = Stream::with('user')->where('grade_id', $id)->get();
        return pozzy_httpOk($streams);
    }

    public function add_teacher(Request $request)
    {
        if(Auth::user()->getRoleNames()[0] == 'school') {
            $this->validate($request, [
                'fname' => 'required|alpha',
                'lname' => 'required|alpha',
                'phone_number' => 'required|min:10',
                'email' => 'required|email',
                'password' => 'required',
                'username' => 'required'
             ]);

             $teacher = new User();
             $teacher->fname = $request->fname;
             $teacher->lname = $request->lname;
             $teacher->phone_number = $request->phone_number;
             $teacher->email = $request->email;
             $teacher->school_id = Auth::user()->school_id;
             $teacher->username = $request->username;
             $teacher->password = bcrypt($request->password);
             $teacher->save();
             $teacher->assignRole('teacher');

            return pozzy_httpOk($teacher);
        } else {
            return response()->json(['Oops, you have no right to perform this operation'], 401);
        }
    }

    public function all_teachers()
    {
        $user = Auth::user();
        $teachers = User::whereHas(
            'roles', function($q){
                $q->where('name','teacher');
            }
        )->where('users.school_id', $user->school_id)
        ->get();

        return pozzy_httpOk($teachers);
    }
    public function all_teacher_streams()
    {
        if(Auth::user()->getRoleNames()[0] == 'teacher') {
            $user = Auth::user();
            $data = Stream::where([['user_id', $user->id], ['school_id', $user->school_id]])->get();
            return pozzy_httpOk($data);
        }
        return pozzy_httpForbidden('Oops, you have no right to perform this operation');
    }
    public function add_teacher_to_Stream(Request $request)
    {
        if(Auth::user()->getRoleNames()[0] == 'school') {
            $this->validate($request, [
                'teacher_id' => 'required',
                'stream_id' => 'required'
            ]);

            $stream =  Stream::find($request->stream_id);

            if($stream->teacher != NULL){
                return pozzy_httpForbidden('Stream Already has a teacher');
            }

            $stream->user_id = $request->teacher_id;
            $stream->save();

            return pozzy_httpOk($stream);
        } else {
            return pozzy_httpForbidden(['Oops, you have no right to perform this operation']);
        }
    }
    public function get_tests()
    {
        $user = Auth::User();
        $test = Test::orderBy('id', 'desc')->with('user')->where([['school_id', $user->school_id]])->get(['name','serial_no','time','term','no_questions','created_by','created_at']);
        return pozzy_httpOk($test);
    }
    public function get_clubs()
    {
        $clubs = Club::with('user')->get();
        return pozzy_httpOk($clubs);
    }
    public function add_club(Request $request)
    {
        if(Auth::user()->getRoleNames()[0] == 'school') {
            $user = Auth::user();
            $club = new Club;
            $club->school_id = $user->school_id;
            $club->teacher_id = $request->id;
            $club->club_name = $request->name;
            $club->save();
            return pozzy_httpCreated('Club added successfully');
        }
        return pozzy_httpForbidden('Oops, you have no right to perform this operation');
    }
    public function reassign_teacher(Request $request)
    {
        if(Auth::user()->getRoleNames()[0] == 'school') {
            $club = Club::where('id', $request->club_id)->update([
                'teacher_id' => $request->teacher_id
            ]);
            return pozzy_httpOk('Club reassigned successfully');
        }
        return pozzy_httpForbidden('Oops, you have no right to perform this operation');
    }
    public function all_teacher_subject(Request $request)
    {
        $sub = Subject::where('grade_id', $request->id)->get();
        return pozzy_httpOk($sub);
    }
    public function all_teacher_clubs()
    {
        $user = Auth::user();
        $clubs = Club::withCount('kids', 'activities')->where('teacher_id', $user->id)->get();
        return pozzy_httpOk($clubs);
    }
    public function add_club_activity(Request $request)
    {
        if(Auth::user()->getRoleNames()[0] == 'teacher') {
            $user = Auth::user();
            $club = Club::where('teacher_id', $user->id)->first();
            if(!$club) return pozzy_httpBadRequest('Oop, you are not assigned to any club');
            $act = new ClubActivity;
            $act->school_id = $user->school_id;
            $act->club_id = $request->club_id;
            $act->activity_name = $request->activity_name;
            $act->description = strip_tags($request->description);
            if($request->hasFile('image')) {
                $act->image = pozzy_Images($request->file('image'));
            }
            if($request->hasFile('video')) {
                $act->video = pozzy_videoCompress($request->file('video'), $user);
            }
            $act->save();
            return pozzy_httpCreated('Club Activity Created');
        }
        return pozzy_httpForbidden('Oops, you have no right to perform this operation');
    }
    public function get_club($id)
    {
        $club = Club::with('kids.grade', 'activities')->where('id', $id)->first();
        return pozzy_httpOk($club);
    }
    public function count_tests()
    {
        $user = Auth::User();
        $test = Test::where([['school_id', $user->school_id]])->get()->count();
        return pozzy_httpOk($test);
    }
    public function week()
    {
        $der = collect();
        foreach(range(-6,0) AS $i) {
            $date = Carbon::yesterday()->addDays($i)->format('Y-m-d');
            $der->put($date,0);
        }
        $tests = Test::where('created_at', '>=', $der->keys()->first())->groupBy('date')->orderBy('date')
                                ->get([DB::raw('DATE(created_at) as date'),DB::raw('COUNT(*) as "count"')])->pluck('count','date');
        $datas = $der->merge($tests);
        $bon = [];
        foreach($datas as $data) {
            array_push($bon, $data);
        }
        dd($bon);
    }

    public function assignCode(Request $request)
    {
        $this->validate($request, [
            'school_id' => ['required']
        ]);

        $school = School::find($request->school_id);

        if (!$school) {
            return pozzy_httpNotFound('School not found');
        }

        $uniqueId = mt_rand(10000, 99999);
        $schoolsId = School::all()->pluck('school_register_id');
        while ($schoolsId->contains($uniqueId)) {
            $uniqueId = mt_rand(10000, 99999);
        }
        $school->update([
            'school_register_id' => $uniqueId,
            'suspend' => false
        ]);

        $school->load('admin');

        return pozzy_httpOk($school);
    }

    public function getGradeStudents($id)
    {
        $students = Kid::with('parent')->where('grade_id', $id)->get();
        return pozzy_httpOk($students);
    }

    public function dashBoardData()
    {
        $school = School::find(Auth()->user()->school_id);

        // Get Total marks per grade
        $kids = Kid::where('school_id', $school->id)->get();

        $allGrades = [];
        $gradesPerformance = [];

        foreach ($kids as $kid) {

        }
    }
}
