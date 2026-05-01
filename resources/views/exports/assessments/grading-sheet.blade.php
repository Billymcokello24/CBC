<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>SBA Grading Sheet</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 10px; color: #333; margin: 0; padding: 0; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h1 { font-size: 16px; margin: 0; text-transform: uppercase; }
        .header h2 { font-size: 12px; margin: 5px 0; color: #666; }
        
        .meta-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        .meta-table td { padding: 4px; border: 1px solid #ddd; }
        .label { font-weight: bold; background: #f9f9f9; width: 120px; }
        
        .grading-table { width: 100%; border-collapse: collapse; }
        .grading-table th { background: #333; color: white; padding: 8px; text-transform: uppercase; font-size: 9px; border: 1px solid #000; }
        .grading-table td { padding: 6px; border: 1px solid #000; text-align: center; }
        .grading-table .name-col { text-align: left; width: 200px; font-weight: bold; }
        
        .badge { padding: 2px 6px; border-radius: 4px; font-weight: bold; color: white; }
        .ee { background: #10b981; }
        .me { background: #3b82f6; }
        .ae { background: #f59e0b; }
        .be { background: #ef4444; }
        
        .footer { margin-top: 30px; border-top: 1px solid #333; padding-top: 10px; }
        .footer table { width: 100%; }
        .signature-box { border-bottom: 1px solid #000; width: 200px; display: inline-block; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $ministry }}</h1>
        <h2>{{ $title }}</h2>
        <h3>{{ $assessment->school->name }}</h3>
    </div>

    <table class="meta-table">
        <tr>
            <td class="label">GRADUE / LEVEL:</td>
            <td>{{ $assessment->class->gradeLevel->name }}</td>
            <td class="label">CLASS / STREAM:</td>
            <td>{{ $assessment->class->name }}</td>
        </tr>
        <tr>
            <td class="label">LEARNING AREA:</td>
            <td>{{ $assessment->subject->name }}</td>
            <td class="label">ACADEMIC TERM:</td>
            <td>{{ $assessment->academic_term->name }} ({{ $assessment->academic_year->name }})</td>
        </tr>
        <tr>
            <td class="label">TEACHER:</td>
            <td colspan="3">{{ $assessment->teacher?->name ?? '________________________' }}</td>
        </tr>
    </table>

    <table class="grading-table">
        <thead>
            <tr>
                <th rowspan="2">Learner Name</th>
                <th colspan="6">Performance Criteria (Scores 1-4)</th>
                <th rowspan="2">Overall</th>
                <th rowspan="2">Remarks</th>
            </tr>
            <tr>
                <th>Knowledge</th>
                <th>Skills</th>
                <th>Communication</th>
                <th>Values</th>
                <th>Creativity</th>
                <th>Thinking</th>
            </tr>
        </thead>
        <tbody>
            @foreach($students as $student)
                <tr>
                    <td class="name-col">{{ $student->first_name }} {{ $student->last_name }}</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <table>
            <tr>
                <td>
                    <strong>KEY:</strong><br>
                    EE: Exceeding Expectation (4) | ME: Meeting Expectation (3) | <br>
                    AE: Approaching Expectation (2) | BE: Below Expectation (1)
                </td>
                <td style="text-align: right;">
                    <strong>Teacher Signature:</strong> <span class="signature-box"></span><br><br>
                    <strong>Date:</strong> <span class="signature-box"></span>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
