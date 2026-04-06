<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        @php
            $color = $themeColor ?? '#1e40af';
            // Generate lighter variant for table backgrounds
            $r = hexdec(substr($color, 1, 2));
            $g = hexdec(substr($color, 3, 2));
            $b = hexdec(substr($color, 5, 2));
            $lightBg = 'rgba('.$r.','.$g.','.$b.', 0.06)';
        @endphp

        @page {
            margin: 120px 30px 60px 30px;
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

        /* ── Watermark ── */
        .watermark {
            position: fixed;
            top: 35%;
            left: 30%;
            z-index: -1;
            opacity: 0.04;
        }
        .watermark img {
            width: 300px;
            height: auto;
        }

        /* ── Repeating Header (fixed = every page) ── */
        .page-header {
            position: fixed;
            top: -100px;
            left: 0;
            right: 0;
            height: 90px;
            text-align: center;
            border-bottom: 2px solid {{ $themeColor ?? '#1e40af' }};
            padding-bottom: 8px;
        }
        .page-header-logo {
            height: 38px;
            margin-bottom: 3px;
        }
        .page-header-name {
            font-size: 14px;
            font-weight: bold;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin: 0;
            line-height: 1.2;
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
            margin-bottom: 10px;
            padding: 6px 0;
            border-bottom: 1px solid #e2e8f0;
        }
        .doc-title {
            font-size: 12px;
            font-weight: bold;
            color: {{ $themeColor ?? '#1e40af' }};
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* ── Meta Grid ── */
        .meta-grid { width: 100%; margin-bottom: 8px; border-collapse: collapse; }
        .meta-grid td { padding: 4px 6px; }
        .label { font-weight: bold; color: #64748b; text-transform: uppercase; font-size: 7px; letter-spacing: 0.3px; }
        .value { font-weight: bold; font-size: 9px; color: #0f172a; margin-top: 1px; }

        /* ── Matrix Table ── */
        table.matrix {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }
        table.matrix thead {
            display: table-header-group;
        }
        table.matrix th {
            background-color: {{ $themeColor ?? '#1e40af' }};
            color: #ffffff;
            border: 1px solid {{ $themeColor ?? '#1e40af' }};
            padding: 5px 4px;
            font-weight: bold;
            text-align: left;
            text-transform: uppercase;
            font-size: 7px;
            letter-spacing: 0.3px;
        }
        table.matrix td {
            border: 1px solid #cbd5e1;
            padding: 4px 5px;
            vertical-align: top;
            font-size: 8px;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }
        table.matrix tr:nth-child(even) td {
            background-color: #f8fafc;
        }
        .col-wk       { width: 5%; text-align: center; }
        .col-strand   { width: 13%; }
        .col-topic    { width: 12%; }
        .col-outcomes { width: 20%; }
        .col-inquiry  { width: 14%; }
        .col-activities { width: 21%; }
        .col-assess   { width: 15%; }

        .week-cell {
            font-weight: bold;
            text-align: center;
            color: {{ $themeColor ?? '#1e40af' }};
        }

        /* ── Footer ── */
        .footer {
            position: fixed;
            bottom: -40px;
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
    {{-- ── Watermark ── --}}
    @if($school?->logo)
    <div class="watermark">
        <img src="{{ storage_path('app/public/' . $school->logo) }}" alt="" />
    </div>
    @endif

    {{-- ── Repeating Header ── --}}
    <div class="page-header">
        @if($school?->logo)
            <img class="page-header-logo" src="{{ storage_path('app/public/' . $school->logo) }}" alt="Logo" />
        @endif
        <div class="page-header-name">{{ $school?->name ?? config('app.name', 'CBC School System') }}</div>
        @if($school?->motto)
            <div class="page-header-motto">"{{ $school->motto }}"</div>
        @endif
    </div>

    {{-- ── Footer ── --}}
    <div class="footer">
        Generated on {{ date('d M Y, H:i') }} &bull; {{ $school?->name ?? config('app.name') }} &bull; Academic Planner System
    </div>

    {{-- ── Content ── --}}
    <div class="doc-title-block">
        <div class="doc-title">Scheme of Work — {{ $scheme->academicTerm?->name ?? '' }}</div>
    </div>

    <table class="meta-grid">
        <tr>
            <td width="20%">
                <div class="label">Subject</div>
                <div class="value">{{ $scheme->subject?->name ?? 'N/A' }}</div>
            </td>
            <td width="20%">
                <div class="label">Grade Level</div>
                <div class="value">{{ $scheme->gradeLevel?->name ?? 'N/A' }}</div>
            </td>
            <td width="20%">
                <div class="label">Academic Year</div>
                <div class="value">{{ $scheme->academicTerm?->academicYear?->name ?? 'N/A' }}</div>
            </td>
            <td width="20%">
                <div class="label">Prepared By</div>
                <div class="value">{{ $scheme->preparedBy?->name ?? 'N/A' }}</div>
            </td>
            <td width="20%">
                <div class="label">Status</div>
                <div class="value">{{ strtoupper($scheme->status) }}</div>
            </td>
        </tr>
    </table>

    <table class="matrix">
        <thead>
            <tr>
                <th class="col-wk">Wk/Lsn</th>
                <th class="col-strand">Strand / Sub-Strand</th>
                <th class="col-topic">Topic</th>
                <th class="col-outcomes">Learning Outcomes</th>
                <th class="col-inquiry">Inquiry Questions</th>
                <th class="col-activities">Activities & Resources</th>
                <th class="col-assess">Assessment</th>
            </tr>
        </thead>
        <tbody>
            @foreach($scheme->entries->sortBy(['week_number', 'lesson_number']) as $entry)
            <tr>
                <td class="week-cell">W{{ $entry->week_number }}<br/>L{{ $entry->lesson_number }}</td>
                <td><strong>{{ $entry->strand?->name ?? '—' }}</strong>@if($entry->subStrand)<br/><small>{{ $entry->subStrand->name }}</small>@endif</td>
                <td>{{ $entry->topic }}</td>
                <td>{{ $entry->learning_outcomes }}</td>
                <td>{{ $entry->inquiry_questions }}</td>
                <td>
                    @if($entry->learning_activities)<strong>Activities:</strong> {{ $entry->learning_activities }}<br/>@endif
                    @if($entry->resources)<strong>Resources:</strong> {{ $entry->resources }}@endif
                </td>
                <td>{{ $entry->assessment }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
