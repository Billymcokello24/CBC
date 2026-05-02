<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        @php
            $color = $themeColor ?? '#1e40af';
            $r = hexdec(substr($color, 1, 2));
            $g = hexdec(substr($color, 3, 2));
            $b = hexdec(substr($color, 5, 2));
            $lightBg = 'rgba('.$r.','.$g.','.$b.', 0.05)';
            $borderCol = 'rgba('.$r.','.$g.','.$b.', 0.2)';
        @endphp

        @page {
            margin: 95px 30px 45px 30px;
        }
        * { box-sizing: border-box; }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 8px;
            color: #1e293b;
            line-height: 1.4;
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
            top: -80px;
            left: 0;
            right: 0;
            height: 70px;
            text-align: center;
            border-bottom: 1.5px solid {{ $color }};
            padding-bottom: 5px;
        }
        .page-header-logo {
            height: 35px;
            margin-bottom: 3px;
        }
        .page-header-name {
            font-size: 13px;
            font-weight: bold;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 0;
        }
        .page-header-motto {
            font-size: 7px;
            color: #64748b;
            font-style: italic;
            margin: 0;
        }
        .page-header-contacts {
            font-size: 6px;
            color: #94a3b8;
            margin: 1px 0 0;
        }

        /* ── Title Block ── */
        .doc-title-block {
            text-align: center;
            margin-bottom: 8px;
            padding: 4px 0;
            background-color: {{ $lightBg }};
            border-radius: 4px;
        }
        .doc-title {
            font-size: 11px;
            font-weight: bold;
            color: {{ $color }};
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* ── Section Titles ── */
        .section-title {
            font-size: 9px;
            font-weight: bold;
            color: {{ $color }};
            text-transform: uppercase;
            border-bottom: 1px solid {{ $borderCol }};
            margin: 10px 0 4px 0;
            padding-bottom: 2px;
        }

        /* ── Info Grid ── */
        .info-grid { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
        .info-grid td { padding: 3px 6px; vertical-align: top; border: none; }
        .label { font-weight: bold; color: #64748b; text-transform: uppercase; font-size: 6px; display: block; margin-bottom: 1px; letter-spacing: 0.2px; }
        .value { font-weight: bold; font-size: 8.5px; color: #0f172a; }

        /* ── Student Photo ── */
        .student-photo {
            width: 50px;
            height: 50px;
            border-radius: 4px;
            border: 1px solid {{ $color }};
            object-fit: cover;
        }
        .student-initials {
            width: 50px;
            height: 50px;
            border-radius: 4px;
            background-color: {{ $lightBg }};
            border: 1px solid {{ $color }};
            text-align: center;
            line-height: 50px;
            font-size: 16px;
            font-weight: bold;
            color: {{ $color }};
        }

        /* ── Results Table ── */
        table.results {
            width: 100%;
            border-collapse: collapse;
            margin-top: 4px;
        }
        table.results th {
            background-color: {{ $color }};
            color: #ffffff;
            padding: 4px 6px;
            text-align: left;
            font-size: 7px;
            text-transform: uppercase;
            letter-spacing: 0.2px;
            border: 1px solid {{ $color }};
            border-right: 1px solid rgba(255,255,255,0.3);
        }
        table.results th:last-child {
            border-right: 1px solid {{ $color }};
        }
        table.results td {
            padding: 4px 6px;
            border: 1px solid #cbd5e1;
            vertical-align: middle;
            font-size: 8px;
        }
        table.results tr:nth-child(even) td {
            background-color: #f8fafc;
        }

        /* ── Rating Badge ── */
        .rating-badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 2px;
            font-weight: bold;
            font-size: 7px;
            color: {{ $color }};
            border: 0.5px solid {{ $color }};
            background-color: {{ $lightBg }};
        }

        /* ── Summary Cards ── */
        .summary-row {
            width: 100%;
            margin-top: 8px;
            display: table;
            table-layout: fixed;
            border-collapse: collapse;
            border: 1px solid #cbd5e1;
        }
        .summary-card {
            display: table-cell;
            padding: 8px 6px;
            text-align: center;
            border: 1px solid #cbd5e1;
            background: transparent;
        }
        .sum-label { font-size: 6px; font-weight: bold; color: #1e293b; text-transform: uppercase; letter-spacing: 0.2px; }
        .sum-value { font-size: 13px; font-weight: bold; color: {{ $color }}; margin-top: 2px; }
        .sum-sub { font-size: 7px; color: #1e293b; }

        /* ── Content Boxes ── */
        .content-box {
            padding: 6px 8px;
            background-color: #ffffff;
            border: none;
            margin-bottom: 8px;
            border-left: 2px solid {{ $color }};
        }

        /* ── Signature ── */
        .sig-row { width: 100%; margin-top: 15px; display: table; table-layout: fixed; }
        .sig-cell { display: table-cell; padding: 0 10px; text-align: center; }
        .sig-line { border-top: 0.5px solid #1e293b; padding-top: 3px; margin-top: 20px; }
        .sig-label { font-size: 7px; font-weight: bold; color: #1e293b; text-transform: uppercase; }

        /* ── Footer ── */
        .footer {
            position: fixed;
            bottom: -20px;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 6px;
            color: #1e293b;
            border-top: 0.5px solid #1e293b;
            padding-top: 3px;
        }
    </style>
</head>
<body>
    {{-- ── Watermark ── --}}
    @if($school->logo)
        <div class="watermark">
            <img src="{{ public_path('storage/'.$school->logo) }}" alt="Logo">
        </div>
    @else
        <div class="watermark">{{ $school->name }}</div>
    @endif

    {{-- ── Repeating Header ── --}}
    <div class="page-header">
        @if($school->logo)
            <img class="page-header-logo" src="{{ public_path('storage/'.$school->logo) }}" alt="Logo">
        @endif
        <div class="page-header-name">{{ $school->name }}</div>
        @if($school->motto)
            <div class="page-header-motto">"{{ $school->motto }}"</div>
        @endif
        <div class="page-header-contacts" style="color: #1e293b; font-weight: bold;">
            @if($school->address){{ $school->address }} &bull; @endif
            @if($school->county){{ $school->county }} County &bull; @endif
            @if($school->phone)Tel: {{ $school->phone }} &bull; @endif
            @if($school->email){{ $school->email }}@endif
            @if($school->postal_code) &bull; P.O. Box {{ $school->postal_code }}@endif
        </div>
    </div>

    {{-- ── Footer ── --}}
    <div class="footer">
        Generated by {{ $school->name }} Academic Records System &bull; {{ date('d M Y') }} &bull; Confidential Document
    </div>

    {{-- ── Title Block ── --}}
    <div class="doc-title-block">
        <div class="doc-title">Learner Progress Report Card</div>
        <div style="font-size: 9px; color: #1e293b; margin-top: 3px; font-weight: bold;">
            {{ $activeTerm->name ?? 'Current Term' }} — {{ $activeYear->name ?? date('Y') }} Academic Year
        </div>
    </div>

    {{-- ── Student Profile ── --}}
    <div class="section-title">1. Learner Identification</div>
    <table class="info-grid">
        <tr>
            <td width="12%" rowspan="3" style="text-align: center; vertical-align: middle;">
                @if($student->photo)
                    <img class="student-photo" src="{{ public_path('storage/'.$student->photo) }}">
                @else
                    <div class="student-initials">{{ substr($student->first_name, 0, 1) }}{{ substr($student->last_name, 0, 1) }}</div>
                @endif
            </td>
            <td width="29%">
                <span class="label">Full Name</span>
                <span class="value">{{ $student->full_name }}</span>
            </td>
            <td width="29%">
                <span class="label">Admission Number</span>
                <span class="value">{{ $student->admission_number }}</span>
            </td>
            <td width="30%">
                <span class="label">UPI / National ID</span>
                <span class="value">{{ $student->upi ?? 'Not Registered' }}</span>
            </td>
        </tr>
        <tr>
            <td>
                <span class="label">Grade Level</span>
                <span class="value">{{ $student->currentClass?->gradeLevel?->name ?? '—' }}</span>
            </td>
            <td>
                <span class="label">Class / Stream</span>
                <span class="value">{{ $student->currentClass?->name ?? '—' }}</span>
            </td>
            <td>
                <span class="label">Gender</span>
                <span class="value" style="text-transform: capitalize;">{{ $student->gender ?? '—' }}</span>
            </td>
        </tr>
        <tr>
            <td>
                <span class="label">Date of Birth</span>
                <span class="value">{{ $student->date_of_birth?->format('d M Y') ?? '—' }}</span>
            </td>
            <td>
                <span class="label">Boarding Status</span>
                <span class="value" style="text-transform: capitalize;">{{ $student->boarding_status ?? 'Day Scholar' }}</span>
            </td>
            <td>
                <span class="label">Nationality</span>
                <span class="value" style="text-transform: capitalize;">{{ $student->nationality ?? 'Kenyan' }}</span>
            </td>
        </tr>
    </table>

    {{-- ── Performance Matrix ── --}}
    <div class="section-title">2. Learning Area Performance</div>
    <table class="results">
        <thead>
            <tr>
                <th width="5%" style="text-align: center;">#</th>
                <th width="30%">Learning Area</th>
                <th width="12%" style="text-align: center;">Score</th>
                <th width="13%" style="text-align: center;">Percentage</th>
                <th width="12%" style="text-align: center;">Rating</th>
                <th width="28%">Teacher's Remarks</th>
            </tr>
        </thead>
        <tbody>
            @forelse($subjects as $index => $s)
            <tr>
                <td style="text-align: center; font-weight: bold; color: {{ $color }};">{{ $index + 1 }}</td>
                <td style="font-weight: bold; color: #1e293b;">{{ $s['name'] }}</td>
                <td style="text-align: center; color: #1e293b;">
                    <strong>{{ $s['score'] }}</strong><span style="color: #1e293b;">/{{ $s['max'] }}</span>
                </td>
                <td style="text-align: center; font-weight: bold; color: #1e293b;">{{ $s['percentage'] }}%</td>
                <td style="text-align: center;">
                    <span class="rating-badge">{{ $s['rubric'] }}</span>
                    <div style="font-size: 7px; color: #1e293b; margin-top: 2px;">{{ $s['rubric_name'] }}</div>
                </td>
                <td style="font-size: 8px; color: #1e293b;">{{ $s['remarks'] }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="6" style="text-align: center; padding: 30px; color: #1e293b; font-style: italic;">
                    No assessment results have been recorded for this term.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    {{-- ── Summary Strip ── --}}
    <div class="section-title">3. Aggregate Performance</div>
    <div class="summary-row">
        <div class="summary-card">
            <div class="sum-label">Total Marks</div>
            <div class="sum-value">{{ $summary['total'] }}<span style="font-size: 8px; color:#1e293b;">/{{ $summary['max'] }}</span></div>
        </div>
        <div class="summary-card">
            <div class="sum-label">Mean Percentage</div>
            <div class="sum-value">{{ $summary['average'] }}%</div>
        </div>
        <div class="summary-card">
            <div class="sum-label">Peer Rank</div>
            <div class="sum-value">{{ $summary['rank'] }}<span style="font-size: 8px; color:#1e293b;">/{{ $summary['out_of'] }}</span></div>
        </div>
        <div class="summary-card">
            <div class="sum-label">Overall Rating</div>
            <div class="sum-value">{{ $summary['rubric'] }}</div>
            <div class="sum-sub">{{ $summary['rubric_name'] }}</div>
        </div>
    </div>

    {{-- ── Bottom Anchored Block ── --}}
    <div style="position: absolute; bottom: 15px; left: 0; right: 0; height: 180px;">
        {{-- ── Rating Key ── --}}
        <div class="content-box" style="margin-bottom: 6px;">
            <span class="label">CBC Rating Scale</span>
            <div style="margin-top: 3px; font-size: 7px; color: #1e293b;">
                <span class="rating-badge">EE</span> Exceeding Expectation (75–100%)
                &nbsp;&nbsp;
                <span class="rating-badge">ME</span> Meeting Expectation (50–74%)
                &nbsp;&nbsp;
                <span class="rating-badge">AE</span> Approaching Expectation (30–49%)
                &nbsp;&nbsp;
                <span class="rating-badge">BE</span> Below Expectation (0–29%)
            </div>
        </div>

        {{-- ── General Comments ── --}}
        <div style="margin-bottom: 6px; background: {{ $lightBg }}; padding: 6px 10px; border-radius: 2px;">
            <span class="label">Class Teacher's General Remarks</span>
            <p style="margin-top: 2px; font-size: 8px; color: #1e293b; font-style: italic; line-height: 1.4; font-weight: bold; margin-bottom: 2px;">
                "{{ $summary['class_teacher_remark'] ?? '..................................................................................' }}"
            </p>
        </div>

        <div style="margin-bottom: 6px; background: {{ $lightBg }}; padding: 6px 10px; border-radius: 2px;">
            <span class="label">Principal/Headteacher's Remarks</span>
            <p style="margin-top: 2px; font-size: 8px; color: #1e293b; font-style: italic; line-height: 1.4; font-weight: bold; margin-bottom: 2px;">
                "{{ $summary['head_teacher_remark'] ?? '..................................................................................' }}"
            </p>
        </div>

        {{-- ── Signatures ── --}}
        <div class="sig-row" style="margin-top: 8px;">
            <div class="sig-cell">
                <div class="sig-line">
                    <div class="sig-label">Class Teacher</div>
                </div>
            </div>
            <div class="sig-cell">
                <div class="sig-line">
                    <div class="sig-label">Head of Institution</div>
                </div>
            </div>
            <div class="sig-cell">
                <div class="sig-line">
                    <div class="sig-label">Parent / Guardian</div>
                </div>
            </div>
        </div>

        <div style="margin-top: 6px; text-align: center; font-size: 6px; color: #cbd5e1;">
            Date of Issue: {{ date('d M Y') }} &bull; Official School Stamp
        </div>
    </div>
</body>
</html>
