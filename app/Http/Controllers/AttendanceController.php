<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AttendanceRecord;
use App\Models\Student;
use App\Models\Activity;
use App\Models\Teacher;
use App\Models\Classroom;
use GuzzleHttp\Client;
use App\Models\Level;
use Maatwebsite\Excel\Facades\Excel;

class AttendanceController extends Controller
{

    public function importExcel2(Request $request)
    {
        $data = Excel::toArray(new ExcelImportController, $request->file('file'));

        // วน loop ผ่านแต่ละคอลัมน์
        foreach ($data[0] as $column) {
            // $column[0] คือค่าในแถวแรกของคอลัมน์นั้น ๆ
            // ใช้เงื่อนไขตามต้องการ
            if ($column[1] !== null) {
                // สร้างข้อมูลใน activities
                Activity::create([
                    'activity' => $column[0],
                    'level' => $column[1], // มอบหมาย level
                ]);
            }
        }


        return redirect()->back()->with('success', 'Import successful!');
    }



    public function welcome()
    {

        return view('welcome');

    }
    public function index(Request $request)
    {
        $level = $request->input('level'); // รับค่า level ที่ส่งมาจาก URL

        $classrooms = Classroom::where('level', $level)->get(); // ดึงข้อมูลห้องเรียนที่มี level ตรงกับค่าที่ส่งมา
        $activities = Activity::where('level', $level)->get(); // ดึงข้อมูลห้องเรียนที่มี level ตรงกับค่าที่ส่งมา
        $teachers = Teacher::all();


        return view('attendance.index', compact('classrooms', 'activities', 'teachers'));
    }
    public function index3()
    {

        $classrooms = Classroom::all();

        $activities = Activity::all();

        $teachers = Teacher::all();


        return view('report', compact('classrooms', 'activities', 'teachers'));
    }
    public function index1(Request $request)
    {
        $request->validate([
            'classroom' => 'required|exists:classrooms,id',
            'activity' => 'required|exists:activities,id',
            'lecturer' => 'required|exists:teachers,id',
        ]);

        $classroomId = $request->input('classroom');
        $activityId = $request->input('activity');
        $lecturerId = $request->input('lecturer');
        $classroom = Classroom::find($classroomId);
        $activity = Activity::find($activityId);
        $lecturer = Teacher::find($lecturerId);
        $students = Student::where('grade', $classroomId)->get();

        return view('attendance.index1', compact('classroomId', 'activityId', 'lecturerId', 'students', 'classroom', 'activity', 'lecturer'));

    }
    public function port(Request $request)
    {
        $request->validate([
            'classroom' => 'required|exists:classrooms,id',
            'activity' => 'required|exists:activities,id',
            'lecturer' => 'required|exists:teachers,id',
        ]);

        $classroomId = $request->input('classroom');
        $activityId = $request->input('activity');
        $lecturerId = $request->input('lecturer');

        $classroom = Classroom::find($classroomId);
        $activity = Activity::find($activityId);
        $lecturer = Teacher::find($lecturerId);
        $students = Student::where('grade', $classroomId)->get();
        $attendanceRecords = AttendanceRecord::where('activity_id', $activityId)
                                              ->where('grade', $classroomId)
                                              ->get();

// เก็บ student_ids ทั้งหมดไว้ใน array
$studentIds = $attendanceRecords->pluck('student_id');

// ดึงข้อมูลของนักเรียนจากฐานข้อมูล
$students = Student::whereIn('id', $studentIds)->get();

// วนลูปเพื่อเพิ่มชื่อและนามสกุลของนักเรียนลงในข้อมูลการเข้าร่วม
foreach ($attendanceRecords as $record) {
    $student = $students->where('id', $record->student_id)->first();
    $record->name = $student->name;
    $record->last_name = $student->last_name;
}

            return view('port', compact('classroomId', 'activityId', 'lecturerId', 'students', 'classroom', 'activity', 'lecturer', 'attendanceRecords'));

    }






    public function submitAttendance(Request $request)
    {
        $classrooms = Classroom::all();
        $activities = Activity::all();
        $teachers = Teacher::all();
        $students = $request->students;
        $classroomId = $request->input('classroomId');
        $activityId = $request->input('activityId');
        $lecturerId = $request->input('lecturerId');
        $studentNames = $request->input('studentName');
        $studentLastNames = $request->input('studentLastName');
        $studentLevels = $request->input('studentLevel');
        $studentID = $request->input('studentID');
        $classroom = Classroom::find($classroomId);
        $activity = Activity::find($activityId);
        $lecturer = Teacher::find($lecturerId);

        $attendanceCounts = [
            'มา' => 0,
            'สาย' => 0,
            'ขาด' => 0,
            'ลา' => 0,
        ];
        $absentStudents = [];
        $leaveStudents = [];
  foreach ($studentNames as $index => $name) {
            $attendance = $request->input('attendance' . $index);
            $studentFullName = $name . ' ' . $studentLastNames[$index];

            if (empty($request->input('attendance' . $index))) {
                return view('welcome')->with('error', 'ส่งข้อมูลไม่สำเร็จ กรุณาทำการส่งใหม่และกรอกข้อมูลให้ครบถ้วน');
            }            AttendanceRecord::create([
                'activity_id' => $activityId,
                'student_id' => $studentID[$index],
                'grade' =>  $classroomId,
                'time' => now(),
                'status' => $request->input('attendance' . $index),
                'lecturer_id'=> $lecturerId,
                // สามารถเพิ่มฟิลด์เพิ่มเติมได้ตามต้องการ
            ]);
        }
        foreach ($studentNames as $index => $name) {
            $attendance = $request->input('attendance' . $index);
            $studentFullName = $name . ' ' . $studentLastNames[$index];

            // นับจำนวนตามสถานะ
            if (isset($attendanceCounts[$attendance])) {
                $attendanceCounts[$attendance]++;
            }

            // แยกรายชื่อของนักเรียนที่ขาดและลา
            if ($attendance == 'ขาด') {
                $absentStudents[] = $studentFullName;
            } elseif ($attendance == 'ลา') {
                $leaveStudents[] = $studentFullName;
            }
        }

        // รวมรายชื่อในกลุ่มของ "ขาด"
        $allAbsentStudents = array_merge($absentStudents, $leaveStudents);
        $totalStudents = count($studentNames);

        $message = "ศูนย์ทดสอบเทคโนโลยีบ้านจั่น:\n";
        $message .= "📌 วันที่ " . now()->format('d/m/Y') . "\n";
        $message .= "ระดับชั้น:  $classroom->grade \n";
        $message .= "วิชา: $activity->activity\n";
        $message .= "อาจารย์ผู้สอน: $lecturer->lecturer\n";
        $message .= "📢 นักเรียนทั้งหมด: $totalStudents คน\n";
        $message .= "✅ มาเรียน: " . $attendanceCounts['มา'] . " คน\n";
        $message .= "⏰ มาสาย: " . $attendanceCounts['สาย'] . " คน\n";
        $message .= "❌ ไม่มาเรียน: " . $attendanceCounts['ขาด'] . " คน\n";
        $message .= "📊 รายชื่อนักเรียน (สาย, ขาด, ลา) มีดังนี้:\n";

        if (!empty($lateStudents)) {
            $message .= "⏰ มาสาย:\n";
            foreach ($lateStudents as $lateStudent) {
                $message .= "- $lateStudent\n";
            }
        }

        if (!empty($absentStudents)) {
            $message .= "❌ ขาด:\n";
            foreach ($absentStudents as $absentStudent) {
                $message .= "- $absentStudent\n";
            }
        }

        if (!empty($leaveStudents)) {
            $message .= "🏖 ลา:\n";
            foreach ($leaveStudents as $leaveStudent) {
                $message .= "- $leaveStudent\n";
            }
        }
        // ส่งข้อความไปยัง Line Notify
        $token = $classroom->line_1 . $classroom->line_2 . $classroom->line_3;
        $client = new Client();
        $response = $client->post('https://notify-api.line.me/api/notify', [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
            ],
            'form_params' => [
                'message' => $message,
            ],
        ]);


        return view('welcome', compact('classroomId', 'activityId', 'lecturerId',  'classroom', 'activity', 'lecturer', 'studentLevels', 'classrooms', 'activities', 'teachers', 'students'))
        ->with('success', 'บันทึกข้อมูลสำเร็จแล้ว');

    }
    public function store(Request $request)
    {
       // Check if $request->attendance exists and is not null
       if ($request->has('attendance') && !is_null($request->attendance)) {
        // Process and store attendance records
        foreach ($request->attendance as $index => $attendance) {
            AttendanceRecord::create([
                'activity_id' => $request->activityId,
                'student_id' => $request->studentId[$index],
                'grade' => $request->grade[$index],
                'time' => now(), // Assuming this is the current time
                'status' => $attendance,
                // Add more fields as needed
            ]);
        }

        // ส่งกลับไปยังหน้าแบบฟอร์มพร้อมกับข้อความแจ้งเตือนเมื่อบันทึกสำเร็จ
        return redirect()->back()->with('success', 'บันทึกข้อมูลสำเร็จแล้ว');
    } else {
        // ส่งกลับไปยังหน้าแบบฟอร์มพร้อมกับข้อความแจ้งเตือนเมื่อเกิดข้อผิดพลาด
        return redirect()->back()->with('error', 'ไม่พบข้อมูลการเข้าร่วมหรือมีข้อผิดพลาดในการบันทึกข้อมูล');
    }
    }

}
