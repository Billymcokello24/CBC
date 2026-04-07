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
            font-size: 9px;
            color: #1e293b;
            line-height: 1.5;
            margin: 0;
            padding: 0;
        }

        .page-break {
            page-break-after: always;
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
            height: 38px;
            margin-bottom: 3px;
        }
        .page-header-name {
            font-size: 15px;
            font-weight: bold;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin: 0;
        }
        .page-header-motto {
            font-size: 8px;
            color: #64748b;
            font-style: italic;
            margin: 0;
        }

        /* ── Title Block ── */
        .doc-title-block {
            text-align: center;
            margin-bottom: 15px;
            padding: 8px 0;
            background-color: {{ $lightBg }};
            border-radius: 6px;
        }
        .doc-title {
            font-size: 13px;
            font-weight: bold;
            color: {{ $color }};
            text-transform: uppercase;
        }

        .section-title {
            font-size: 9px;
            font-weight: bold;
            color: {{ $color }};
            text-transform: uppercase;
            border-bottom: 1px solid {{ $borderCol }};
            margin: 15px 0 8px 0;
            padding-bottom: 2px;
        }

        .info-grid { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        .info-grid td { padding: 5px 8px; vertical-align: top; border: 1px solid #f1f5f9; }
        .label { font-weight: bold; color: #64748b; text-transform: uppercase; font-size: 7px; display: block; }
        .value { font-weight: bold; font-size: 9px; color: #0f172a; }

        .content-box {
            padding: 8px 10px;
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 5px;
            margin-bottom: 10px;
        }
        .italic { font-style: italic; color: #475569; }

        table.stages {
            width: 100%;
            border-collapse: collapse;
        }
        table.stages th {
            background-color: {{ $color }};
            color: #ffffff;
            padding: 6px;
            text-align: left;
            font-size: 8px;
        }
        table.stages td {
            padding: 8px;
            border: 1px solid #e2e8f0;
            vertical-align: top;
            font-size: 8.5px;
        }

        .footer {
            position: fixed;
            bottom: -30px;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 7px;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
            padding-top: 4px;
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
        Generated on {{ date('d M Y') }} &bull; Class: {{ $schoolClass->name }} &bull; Subject: {{ $subject->name }} &bull; Page Based Export
    </div>

    @foreach($plans as $plan)
        <div class="doc-title-block">
            <div class="doc-title">{{ $subject->name ?? 'Lesson Plan' }}: {{ $plan->title }}</div>
        </div>

        <table class="info-grid">
            <tr>
                <td width="30%"><span class="label">Teacher</span><span class="value">{{ $plan->teacher->user->name ?? 'N/A' }}</span></td>
                <td width="40%"><span class="label">Day/Date</span><span class="value">{{ $plan->lesson_date->format('l, d M Y') }}</span></td>
                <td width="30%"><span class="label">Week/Period</span><span class="value">W{{ $plan->week_number }} / L{{ $plan->period_number }}</span></td>
            </tr>
            <tr>
                <td><span class="label">Strand</span><span class="value">{{ $plan->strand->name ?? 'N/A' }}</span></td>
                <td><span class="label">Sub-Strand</span><span class="value">{{ $plan->subStrand->name ?? 'N/A' }}</span></td>
                <td><span class="label">Duration</span><span class="value">{{ $plan->duration_minutes }} Mins</span></td>
            </tr>
            <tr>
                <td><span class="label">Subject</span><span class="value">{{ $plan->subject->name }}</span></td>
                <td><span class="label">Students</span><span class="value">{{ $plan->number_of_learners }}</span></td>
                <td><span class="label">Term</span><span class="value">{{ $plan->academicTerm->name ?? '' }}</span></td>
            </tr>
        </table>

        <div class="section-title">1. Goals & Competencies</div>
        <div class="content-box">
            <div style="display: table; width: 100%;">
                <div style="display: table-cell; width: 60%;">
                    <span class="label">Outcomes (Expected Results):</span>
                    <div class="value italic">{{ $plan->learning_outcomes }}</div>
                </div>
                <div style="display: table-cell; width: 40%; padding-left: 15px;">
                    <span class="label">Inquiry Questions:</span>
                    <div class="value italic">"{{ $plan->inquiry_questions ?: 'What have we learned today?' }}"</div>
                </div>
            </div>
        </div>

        <table class="info-grid">
            <tr>
                <td width="33%">
                    <span class="label">Core Competencies</span>
                    <div class="value" style="font-size: 8px;">{{ is_array($plan->core_competencies) ? implode(', ', $plan->core_competencies) : 'N/A' }}</div>
                </td>
                <td width="33%">
                    <span class="label">Values & Life Skills</span>
                    <div class="value" style="font-size: 8px;">{{ is_array($plan->values) ? implode(', ', $plan->values) : '' }} {{ is_array($plan->life_skills) ? implode(', ', $plan->life_skills) : '' }}</div>
                </td>
                <td width="34%">
                    <span class="label">PCI (Pertinent Issues)</span>
                    <div class="value" style="font-size: 8px;">{{ is_array($plan->pci) ? implode(', ', $plan->pci) : 'N/A' }}</div>
                </td>
            </tr>
        </table>

        <div class="section-title">2. Instructional Process</div>
        <table class="stages">
            <thead>
                <tr>
                    <th width="15%">Stage</th>
                    <th>Process & Activities</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><strong>Intro</strong></td>
                    <td>{{ $plan->introduction }}</td>
                </tr>
                <tr>
                    <td><strong>Delivery</strong></td>
                    <td>
                        @if(is_array($plan->learning_activities))
                            @foreach($plan->learning_activities as $i => $act)
                                <div style="margin-bottom: 4px;">{{ $i+1 }}. {{ $act }}</div>
                            @endforeach
                        @else
                            {{ $plan->lesson_development }}
                        @endif

                        @if($plan->teacher_activities || $plan->learner_activities)
                            <div style="margin-top: 8px; border-top: 1px solid #f1f5f9; padding-top: 5px; font-size: 8px;">
                                @if($plan->teacher_activities)
                                    <div style="margin-bottom: 3px;"><strong>Teacher Core Tasks:</strong> {{ $plan->teacher_activities }}</div>
                                @endif
                                @if($plan->learner_activities)
                                    <div><strong>Learner Core Tasks:</strong> {{ $plan->learner_activities }}</div>
                                @endif
                            </div>
                        @endif
                    </td>
                </tr>
                <tr>
                    <td><strong>Summary</strong></td>
                    <td>{{ $plan->conclusion }}</td>
                </tr>
            </tbody>
        </table>

        <div class="section-title">3. Resources & Evaluation</div>
        <table class="info-grid">
            <tr>
                <td width="50%">
                    <span class="label">Teaching Aids & Materials</span>
                    <span class="value">{{ $plan->teaching_aids ?: 'N/A' }}</span>
                </td>
                <td width="50%">
                    <span class="label">References & Sources</span>
                    <span class="value">{{ $plan->references ?: 'N/A' }}</span>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <span class="label">Key Vocabulary (New Terms)</span>
                    <span class="value">{{ $plan->key_vocabulary ?: 'N/A' }}</span>
                </td>
            </tr>
            <tr>
                <td width="50%">
                    <span class="label">Assessment Methods</span>
                    <span class="value">{{ $plan->assessment_methods ?: 'Direct Observation' }}</span>
                </td>
                <td width="50%">
                    <span class="label">Homework</span>
                    <span class="value">{{ $plan->homework ?: 'None' }}</span>
                </td>
            </tr>
        </table>

        <div style="margin-top: 15px; border: 1px dashed #cbd5e1; padding: 10px; border-radius: 6px;">
            <span class="label">Teacher's Self-Reflection</span>
            <p class="italic" style="font-size: 8px; margin-top: 5px;">
                {{ $plan->reflection ?: '............................................................................................................................................................................................................................................................' }}
            </p>
        </div>

        @if(!$loop->last)
            <div class="page-break"></div>
        @endif
    @endforeach
</body>
</html>
