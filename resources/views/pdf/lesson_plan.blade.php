<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        @php
            $color = $themeColor ?? '#1e40af';
            // Generate lighter variant for backgrounds
            $r = hexdec(substr($color, 1, 2));
            $g = hexdec(substr($color, 3, 2));
            $b = hexdec(substr($color, 5, 2));
            $lightBg = 'rgba('.$r.','.$g.','.$b.', 0.05)';
            $borderCol = 'rgba('.$r.','.$g.','.$b.', 0.2)';
        @endphp

        @page {
            margin: 110px 40px 60px 40px;
        }
        * { box-sizing: border-box; }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 10px;
            color: #1e293b;
            line-height: 1.6;
            margin: 0;
            padding: 0;
        }

        /* ── Watermark ── */
        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            z-index: -1;
            opacity: 0.05;
            transform: translate(-50%, -50%);
            width: 350px;
            text-align: center;
        }
        .watermark img {
            width: 100%;
            height: auto;
        }

        /* ── Repeating Header ── */
        .page-header {
            position: fixed;
            top: -90px;
            left: 0;
            right: 0;
            height: 80px;
            text-align: center;
            border-bottom: 2px solid {{ $color }};
            padding-bottom: 10px;
        }
        .page-header-logo {
            height: 40px;
            margin-bottom: 5px;
        }
        .page-header-name {
            font-size: 16px;
            font-weight: bold;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin: 0;
        }
        .page-header-motto {
            font-size: 9px;
            color: #64748b;
            font-style: italic;
            margin: 0;
        }

        /* ── Title Block ── */
        .doc-title-block {
            text-align: center;
            margin-bottom: 20px;
            padding: 10px 0;
            background-color: {{ $lightBg }};
            border-radius: 8px;
        }
        .doc-title {
            font-size: 14px;
            font-weight: bold;
            color: {{ $color }};
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* ── Sections ── */
        .section-title {
            font-size: 10px;
            font-weight: bold;
            color: {{ $color }};
            text-transform: uppercase;
            border-bottom: 1px solid {{ $borderCol }};
            margin: 20px 0 10px 0;
            padding-bottom: 3px;
        }

        /* ── Info Grid ── */
        .info-grid { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        .info-grid td { padding: 6px 8px; vertical-align: top; border: 1px solid #f1f5f9; }
        .label { font-weight: bold; color: #64748b; text-transform: uppercase; font-size: 8px; display: block; margin-bottom: 2px; }
        .value { font-weight: bold; font-size: 10px; color: #0f172a; }

        /* ── Content Blocks ── */
        .content-box {
            padding: 10px 12px;
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            margin-bottom: 15px;
        }
        .italic { font-style: italic; color: #475569; }

        /* ── Table for Stages ── */
        table.stages {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        table.stages th {
            background-color: {{ $color }};
            color: #ffffff;
            padding: 8px;
            text-align: left;
            font-size: 9px;
            text-transform: uppercase;
        }
        table.stages td {
            padding: 10px;
            border: 1px solid #e2e8f0;
            vertical-align: top;
        }
        .stage-name { font-weight: bold; color: {{ $color }}; }

        /* ── Footer ── */
        .footer {
            position: fixed;
            bottom: -30px;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 8px;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
            padding-top: 5px;
        }
    </style>
</head>
<body>
    @if($school?->logo)
        <div class="watermark">
            <img src="{{ public_path('storage/'.$school->logo) }}" alt="Logo">
        </div>
    @else
        <div class="watermark">{{ $school->name }}</div>
    @endif

    <div class="page-header">
        @if($school->logo)
            <img class="page-header-logo" src="{{ public_path('storage/'.$school->logo) }}" alt="Logo">
        @endif
        <div class="page-header-name">{{ $school->name }}</div>
        @if($school->motto)
            <div class="page-header-motto">"{{ $school->motto }}"</div>
        @endif
    </div>

    <div class="footer">
        Generated by {{ $school->name }} Academic Planner • {{ date('d M Y') }} • Page Based View
    </div>

    <div class="doc-title-block">
        <div class="doc-title">{{ $plan->subject->name ?? 'Lesson Plan' }}: {{ $plan->title }}</div>
    </div>

    <div class="section-title">1. Identification & Context</div>
    <table class="info-grid">
        <tr>
            <td width="33%">
                <span class="label">Teacher</span>
                <span class="value">{{ $plan->teacher->user->name ?? 'N/A' }}</span>
            </td>
            <td width="33%">
                <span class="label">Class / Grade</span>
                <span class="value">{{ $plan->classroom->gradeLevel->name ?? '' }} - {{ $plan->classroom->name ?? '' }}</span>
            </td>
            <td width="34%">
                <span class="label">Subject</span>
                <span class="value">{{ $plan->subject->name ?? 'N/A' }}</span>
            </td>
        </tr>
        <tr>
            <td>
                <span class="label">Date</span>
                <span class="value">{{ $plan->lesson_date->format('l, d M Y') }}</span>
            </td>
            <td>
                <span class="label">Duration</span>
                <span class="value">{{ $plan->duration_minutes ?? '35' }} Minutes</span>
            </td>
            <td>
                <span class="label">Learners</span>
                <span class="value">{{ $plan->number_of_learners ?? '0' }} Students</span>
            </td>
        </tr>
        <tr>
            <td>
                <span class="label">Strand</span>
                <span class="value">{{ $plan->strand->name ?? 'N/A' }}</span>
            </td>
            <td>
                <span class="label">Sub-Strand</span>
                <span class="value">{{ $plan->subStrand->name ?? 'N/A' }}</span>
            </td>
            <td>
                <span class="label">Term</span>
                <span class="value">{{ $plan->academicTerm->name ?? 'N/A' }}</span>
            </td>
        </tr>
        <tr>
            <td>
                <span class="label">Week</span>
                <span class="value">Week {{ $plan->week_number ?? '—' }}</span>
            </td>
            <td>
                <span class="label">Period</span>
                <span class="value">Lesson {{ $plan->period_number ?? '—' }}</span>
            </td>
            <td></td>
        </tr>
    </table>

    <div class="section-title">2. Learning Outcomes & Goals</div>
    <div class="content-box">
        <div class="label" style="margin-bottom: 5px;">Expected Outcomes</div>
        <div class="value italic">{{ $plan->learning_outcomes }}</div>
    </div>

    <table class="info-grid" style="margin-top: 10px;">
        <tr>
            <td width="33%">
                <span class="label">Core Competencies</span>
                <div class="value">
                    @if($plan->core_competencies)
                        <ul style="margin: 5px 0; padding-left: 15px;">
                            @foreach($plan->core_competencies as $comp)
                                <li>{{ $comp }}</li>
                            @endforeach
                        </ul>
                    @else
                        None listed
                    @endif
                </div>
            </td>
            <td width="33%">
                <span class="label">Values & Life Skills</span>
                <div class="value">
                    @if($plan->values || $plan->life_skills)
                        <ul style="margin: 5px 0; padding-left: 15px;">
                            @if($plan->values)
                                @foreach($plan->values as $val)
                                    <li>{{ $val }} (Value)</li>
                                @endforeach
                            @endif
                            @if($plan->life_skills)
                                @foreach($plan->life_skills as $skill)
                                    <li>{{ $skill }} (Skill)</li>
                                @endforeach
                            @endif
                        </ul>
                    @else
                        None listed
                    @endif
                </div>
            </td>
            <td width="34%">
                <span class="label">PCI (Pertinent Issues)</span>
                <div class="value">
                    @if($plan->pci)
                        <ul style="margin: 5px 0; padding-left: 15px;">
                            @foreach($plan->pci as $issue)
                                <li>{{ $issue }}</li>
                            @endforeach
                        </ul>
                    @else
                        None listed
                    @endif
                </div>
            </td>
        </tr>
    </table>

    <div class="section-title">3. Instructional Process</div>
    <table class="stages">
        <thead>
            <tr>
                <th width="20%">Lesson Stage</th>
                <th>Activities & Procedures</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><span class="stage-name">Introduction</span></td>
                <td>{{ $plan->introduction ?: 'N/A' }}</td>
            </tr>
            <tr>
                <td><span class="stage-name">Development</span></td>
                <td>
                    @if(is_array($plan->learning_activities))
                        @foreach($plan->learning_activities as $index => $activity)
                            <div style="margin-bottom: 8px;"><strong>Activity {{ $index + 1 }}:</strong> {{ $activity }}</div>
                        @endforeach
                    @else
                        {{ $plan->lesson_development ?: 'N/A' }}
                    @endif

                    <div style="margin-top: 10px; border-top: 1px solid #f1f5f9; padding-top: 10px;">
                        @if($plan->teacher_activities)
                            <div style="margin-bottom: 5px;"><strong>Teacher Activities:</strong> {{ $plan->teacher_activities }}</div>
                        @endif
                        @if($plan->learner_activities)
                            <div><strong>Learner Activities:</strong> {{ $plan->learner_activities }}</div>
                        @endif
                    </div>
                </td>
            </tr>
            <tr>
                <td><span class="stage-name">Conclusion</span></td>
                <td>{{ $plan->conclusion ?: 'N/A' }}</td>
            </tr>
        </tbody>
    </table>

    <div class="section-title">4. Resources & Support</div>
    <div class="content-box">
        <div class="grid" style="display: table; width: 100%;">
            <div style="display: table-cell; width: 33%;">
                <span class="label">Teaching Aids</span>
                <span class="value">{{ $plan->teaching_aids ?: 'N/A' }}</span>
            </div>
            <div style="display: table-cell; width: 33%;">
                <span class="label">References</span>
                <span class="value">{{ $plan->references ?: 'N/A' }}</span>
            </div>
            <div style="display: table-cell; width: 34%;">
                <span class="label">Key Vocabulary</span>
                <span class="value">{{ $plan->key_vocabulary ?: 'N/A' }}</span>
            </div>
        </div>
    </div>

    <div class="content-box">
        <div style="display: table; width: 100%;">
            <div style="display: table-cell; width: 50%;">
                <span class="label">Methods of Assessment</span>
                <span class="value">{{ $plan->assessment_methods ?: 'Direct observation and oral questioning' }}</span>
            </div>
            <div style="display: table-cell; width: 50%;">
                <span class="label">Homework</span>
                <span class="value">{{ $plan->homework ?: 'None' }}</span>
            </div>
        </div>
    </div>
    
    <div class="content-box" style="background-color: {{ $lightBg }}; border-color: {{ $borderCol }};">
        <span class="label">Inquiry Questions</span>
        <span class="value italic">"{{ $plan->inquiry_questions ?: 'What have we learned today?' }}"</span>
    </div>

    <div style="margin-top: 30px; border: 1px dashed #cbd5e1; padding: 15px; border-radius: 8px;">
        <span class="label">Teacher's Self-Reflection</span>
        <p class="italic" style="margin-top: 10px;">
            {{ $plan->reflection ?: '..........................................................................................................................................................................................................................................................................................................................' }}
        </p>
    </div>
</body>
</html>
