<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>รายงาน</title>
    <style>
      @page {
            size: A4;
            margin: 0;
        }

        body {
            font-size: 16px;
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            line-height: 1.5;
            color: black;
            background-color: white;
        }

        .container {
            width: 21cm;
            margin: 0 auto;
            padding: 2cm 1cm;
            text-align: center; /* จัดเนื้อหาตรงกลาง */
        }

        h1 {
            font-size: 18px;
            margin-bottom: 20px;
            text-align: center;
        }

        p {
            text-align: center;
            margin-bottom: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table, th, td {
            border: 1px solid black;
        }

        th, td {
            padding: 8px;
            text-align: center;
        }

        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>

    <h1>วิทยาลัยเทคโนโลยีบ้านจั่น ภาคเรียนที่ 1 ปีการศึกษา 2567</h1>
    <p>รหัส: ______________ วิชา: {{ $activity->activity }} จำนวนหน่วยกิต: __ จำนวนเต็มคาบ: __ อาจารย์ผู้สอน: {{ $lecturer->lecturer }}</p>
    <p>ระดับชั้น: ปีที่ {{ $classroom->grade[4]}} ห้อง: {{ $classroom->grade }} ประเภทวิชา: ______________ สาขา: ______________</p>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th rowspan="2">ที่</th>
                <th rowspan="2">ชื่อ-สกุล</th>
                <th colspan="{{ $attendanceRecords->groupBy(function($record) { return \Carbon\Carbon::parse($record->time)->format('Y-m-d'); })->count() }}">วันที่-เดือน-ปี</th>
                <th rowspan="2">รวม</th>
                <th rowspan="2">หมายเหตุ</th>
            </tr>
            <tr>

                @foreach($attendanceRecords->groupBy(function($record) {
                    return \Carbon\Carbon::parse($record->time)->format('Y-m-d');
                }) as $date => $records)
                    <th>{{ $date }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @php
                $students = $attendanceRecords->groupBy('student_id');
                $index = 1;
            @endphp
            @foreach($students as $student_id => $records)
                <tr>
                    <td>{{ $index++ }}</td>
                    <td>{{ $records->first()->name }}   {{ $records->first()->last_name }}</td>

                    @foreach($attendanceRecords->groupBy(function($record) {
                        return \Carbon\Carbon::parse($record->time)->format('Y-m-d');
                    }) as $date => $dateRecords)
                        @php
                            $record = $records->firstWhere(function($r) use ($date) {
                                return \Carbon\Carbon::parse($r->time)->format('Y-m-d') === $date;
                            });
                        @endphp
                        <td>{{ $record ? $record->status : '-' }}</td>
                    @endforeach
                    <td>{{ $records->count() }}</td> <!-- รวม -->
                    <td></td> <!-- หมายเหตุ -->
                </tr>
            @endforeach
        </tbody>
    </table>


</body>
</html>
