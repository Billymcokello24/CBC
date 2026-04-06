<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Lesson Plan - {{ $plan->title }}</title>
    <style>
        @page {
            margin: 2cm;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 11pt;
            line-height: 1.6;
            color: #333;
        }
        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 80pt;
            color: rgba(0, 0, 0, 0.03);
            z-index: -1000;
            white-space: nowrap;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #2563eb;
            padding-bottom: 10px;
            margin-bottom: 30px;
        }
        .header img {
            max-height: 80px;
            margin-bottom: 10px;
        }
        .header h1 {
            color: #1e3a8a;
            margin: 0;
            font-size: 22pt;
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        .header p {
            margin: 5px 0;
            font-weight: bold;
            font-style: italic;
        }
        .section-title {
            background: #f8fafc;
            border-left: 4px solid #2563eb;
            padding: 8px 15px;
            margin: 25px 0 15px 0;
            font-weight: bold;
            color: #1e3a8a;
            text-transform: uppercase;
            font-size: 10pt;
            letter-spacing: 1px;
        }
        .info-grid {
            width: 100%;
            border-collapse: collapse;
        }
        .info-grid td {
            padding: 8px;
            vertical-align: top;
            width: 50%;
        }
        .label {
            font-weight: bold;
            color: #64748b;
            font-size: 9pt;
            display: block;
            text-transform: uppercase;
        }
        .value {
            font-size: 11pt;
            color: #1e293b;
            font-weight: 600;
        }
        .content-block {
            padding: 10px 0;
            border-bottom: 1px solid #f1f5f9;
        }
        .stage-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .stage-table th {
            background: #f8fafc;
            text-align: left;
            padding: 10px;
            border: 1px solid #e2e8f0;
            font-size: 9pt;
            color: #475569;
        }
        .stage-table td {
            padding: 10px;
            border: 1px solid #e2e8f0;
            vertical-align: top;
        }
        .list-item {
            margin-bottom: 5px;
        }
        footer {
            position: fixed;
            bottom: -1cm;
            left: 0;
            right: 0;
            height: 1cm;
            text-align: center;
            font-size: 9pt;
            color: #94a3b8;
            border-top: 1px solid #f1f5f9;
            padding-top: 10px;
        }
    </style>
</head>
<body>
    @if($school->logo)
        <div class="watermark">{{ $school->name }}</div>
    @endif

    <div class="header">
        @if($school->logo)
            <img src="{{ public_path('storage/'.$school->logo) }}" alt="Logo">
        @endif
        <h1>{{ $school->name }}</h1>
        @if($school->motto)
            <p>"{{ $school->motto }}"</p>
        @endif
    </div>

    <div class="section-title">A. Identification Section</div>
    <table class="info-grid">
        <tr>
            <td>
                <span class="label">Teacher</span>
                <span class="value">{{ $plan->teacher->user->name ?? 'N/A' }}</span>
            </td>
            <td>
                <span class="label">Grade / Class</span>
                <span class="value">{{ $plan->classroom->gradeLevel->name ?? '' }} - {{ $plan->classroom->name ?? '' }}</span>
            </td>
        </tr>
        <tr>
            <td>
                <span class="label">Learning Area (Subject)</span>
                <span class="value">{{ $plan->subject->name ?? 'N/A' }}</span>
            </td>
            <td>
                <span class="label">Date & Time</span>
                <span class="value">{{ $plan->lesson_date->format('jS M, Y') }} | {{ $plan->duration_minutes }} Mins</span>
            </td>
        </tr>
        <tr>
            <td>
                <span class="label">Strand</span>
                <span class="value">{{ $plan->strand->name ?? 'N/A' }}</span>
            </td>
            <td>
                <span class="label">Sub-strand</span>
                <span class="value">{{ $plan->subStrand->name ?? 'N/A' }}</span>
            </td>
        </tr>
        <tr>
            <td>
                <span class="label">Number of Learners</span>
                <span class="value">{{ $plan->number_of_learners ?? 'N/A' }}</span>
            </td>
            <td>
                <span class="label">Academic Term</span>
                <span class="value">{{ $plan->academicTerm->name ?? 'N/A' }}</span>
            </td>
        </tr>
    </table>

    <div class="section-title">B. Learning Outcome</div>
    <div class="content-block">
        {{ $plan->learning_outcomes }}
    </div>

    <div class="section-title">C. Key Competencies, Values & Life Skills</div>
    <table class="info-grid">
        <tr>
            <td>
                <span class="label">Core Competencies</span>
                <div class="value">
                    @foreach($plan->core_competencies as $comp)
                        <div class="list-item">• {{ $comp }}</div>
                    @endforeach
                </div>
            </td>
            <td>
                <span class="label">Values & Life Skills</span>
                <div class="value">
                    @if($plan->values)
                        @foreach($plan->values as $val)
                            <div class="list-item">• {{ $val }}</div>
                        @endforeach
                    @endif
                    @if($plan->life_skills)
                        @foreach($plan->life_skills as $skill)
                            <div class="list-item">• {{ $skill }}</div>
                        @endforeach
                    @endif
                </div>
            </td>
        </tr>
    </table>

    <div class="section-title">D. Learning Resources</div>
    <div class="content-block">
        <span class="label">Tools & Materials</span>
        <span class="value">{{ $plan->teaching_aids ?: 'N/A' }}</span>
        <br>
        <span class="label">References</span>
        <span class="value">{{ $plan->references ?: 'N/A' }}</span>
    </div>

    <div class="section-title">E. Core Teaching Section</div>
    <table class="stage-table">
        <tr>
            <th width="20%">Stage</th>
            <th>Procedure / Activities</th>
        </tr>
        <tr>
            <td><strong>Introduction</strong><br><small>(≈ 5 min)</small></td>
            <td>{{ $plan->introduction }}</td>
        </tr>
        <tr>
            <td><strong>Lesson Development</strong><br><small>(≈ 25 min)</small></td>
            <td>
                @if($plan->learning_activities)
                    @foreach($plan->learning_activities as $index => $activity)
                        <div class="list-item"><strong>Activity {{ $index + 1 }}:</strong> {{ $activity }}</div>
                        <br>
                    @endforeach
                @else
                    {{ $plan->lesson_development }}
                @endif
            </td>
        </tr>
        <tr>
            <td><strong>Conclusion</strong><br><small>(≈ 5 min)</small></td>
            <td>{{ $plan->conclusion }}</td>
        </tr>
    </table>

    <div class="section-title">F. Assessment & Reflection</div>
    <div class="content-block">
        <span class="label">Formative Assessment</span>
        <span class="value">{{ $plan->assessment_methods ?: 'N/A' }}</span>
    </div>
    <div class="content-block">
        <span class="label">Teacher's Reflection</span>
        <span class="value italic text-muted-foreground">{{ $plan->reflection ?: 'To be filled after the lesson.' }}</span>
    </div>

    <footer>
        Generated by {{ $school->name }} Academic Planner • {{ date('Y') }}
    </footer>
</body>
</html>
