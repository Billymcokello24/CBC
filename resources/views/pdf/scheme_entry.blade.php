<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        @page {
            margin: 120px 40px 60px 40px;
        }
        * { box-sizing: border-box; }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 11px;
            color: #1e293b;
            line-height: 1.6;
            margin: 0;
            padding: 0;
        }

        /* ── Watermark ── */
        .watermark {
            position: fixed;
            top: 38%;
            left: 28%;
            z-index: -1;
            opacity: 0.04;
        }
        .watermark img {
            width: 320px;
            height: auto;
        }

        /* ── Repeating Header ── */
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
            font-size: 15px;
            font-weight: bold;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin: 0;
            line-height: 1.2;
        }
        .page-header-motto {
            font-size: 9px;
            color: #64748b;
            font-style: italic;
            margin: 0;
        }

        /* ── Topic Hero ── */
        .topic-hero {
            font-size: 20px;
            font-weight: bold;
            color: #0f172a;
            margin: 5px 0 3px;
            line-height: 1.3;
        }
        .topic-sub {
            font-size: 10px;
            color: #64748b;
            margin-bottom: 14px;
        }
        .doc-label {
            font-size: 11px;
            font-weight: bold;
            color: {{ $themeColor ?? '#1e40af' }};
            text-transform: uppercase;
            letter-spacing: 0.5px;
            text-align: center;
            margin-bottom: 6px;
            padding-bottom: 6px;
            border-bottom: 1px solid #e2e8f0;
        }

        /* ── Section Boxes ── */
        .section-box {
            margin-bottom: 14px;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            overflow: hidden;
            page-break-inside: avoid;
        }
        .section-header {
            background: #f1f5f9;
            padding: 6px 12px;
            border-bottom: 1px solid #e2e8f0;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 9px;
            color: {{ $themeColor ?? '#1e40af' }};
            letter-spacing: 0.5px;
        }
        .section-content {
            padding: 10px 12px;
        }

        /* ── Metadata Table ── */
        .meta-table { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
        .meta-table td { padding: 6px 8px; border-bottom: 1px solid #f1f5f9; }
        .label { font-size: 8px; font-weight: bold; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.3px; }
        .value { font-size: 11px; font-weight: bold; color: #0f172a; margin-top: 2px; }

        /* ── Pedagogical Phases ── */
        .phase {
            margin-bottom: 8px;
            border-left: 3px solid {{ $themeColor ?? '#1e40af' }};
            padding-left: 10px;
        }
        .phase-title {
            font-weight: bold;
            font-size: 10px;
            color: {{ $themeColor ?? '#1e40af' }};
            text-transform: uppercase;
            margin-bottom: 2px;
        }
        .phase p {
            margin: 0;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }

        /* ── Badges ── */
        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 9px;
            font-weight: bold;
            margin-right: 3px;
            margin-bottom: 3px;
            background: #eef2ff;
            color: {{ $themeColor ?? '#3730a3' }};
            border: 1px solid #c7d2fe;
        }

        /* ── Two-Column Layout ── */
        .two-col { width: 100%; border-collapse: collapse; }
        .two-col td { vertical-align: top; padding: 0 5px; width: 50%; }

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
            padding: 4px 0;
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
    <div class="doc-label">
        Instructional Profile — Week {{ $entry->week_number }}, Lesson {{ $entry->lesson_number }}
    </div>

    <div class="topic-hero">{{ $entry->topic }}</div>
    <div class="topic-sub">{{ $scheme->subject?->name ?? '' }} &bull; {{ $scheme->gradeLevel?->name ?? '' }} &bull; {{ $scheme->academicTerm?->name ?? '' }}</div>

    {{-- ── Metadata ── --}}
    <table class="meta-table">
        <tr>
            <td width="33%">
                <div class="label">Strand</div>
                <div class="value">{{ $entry->strand?->name ?? 'Unassigned' }}</div>
            </td>
            <td width="33%">
                <div class="label">Sub-Strand</div>
                <div class="value">{{ $entry->subStrand?->name ?? 'N/A' }}</div>
            </td>
            <td width="33%">
                <div class="label">Duration</div>
                <div class="value">{{ $entry->duration_minutes ?? '—' }} Minutes</div>
            </td>
        </tr>
        <tr>
            <td>
                <div class="label">Key Vocabulary</div>
                <div class="value" style="font-weight: normal;">{{ $entry->key_vocabulary ?? '—' }}</div>
            </td>
            <td>
                <div class="label">Lesson Date</div>
                <div class="value">{{ $entry->lesson_date ? $entry->lesson_date->format('d M Y') : 'N/A' }}</div>
            </td>
            <td>
                <div class="label">Prepared By</div>
                <div class="value">{{ $scheme->preparedBy?->name ?? 'N/A' }}</div>
            </td>
        </tr>
    </table>

    {{-- ── Inquiry & Outcomes ── --}}
    <div class="section-box">
        <div class="section-header">Core Instructional Goals</div>
        <div class="section-content">
            @if($entry->inquiry_questions)
            <div style="margin-bottom: 8px;">
                <div class="label">Inquiry Questions</div>
                <div style="font-size: 13px; font-weight: bold; margin-top: 3px; color: {{ $themeColor ?? '#1e40af' }};">{{ $entry->inquiry_questions }}</div>
            </div>
            @endif
            @if($entry->learning_outcomes)
            <div>
                <div class="label">Learning Outcomes</div>
                <div style="margin-top: 3px;">{{ $entry->learning_outcomes }}</div>
            </div>
            @endif
        </div>
    </div>

    {{-- ── Pedagogical Phases ── --}}
    <div class="section-box">
        <div class="section-header">Pedagogical Phases</div>
        <div class="section-content">
            @if($entry->introduction)
            <div class="phase">
                <div class="phase-title">1. Introduction</div>
                <p>{{ $entry->introduction }}</p>
            </div>
            @endif
            @if($entry->lesson_development)
            <div class="phase">
                <div class="phase-title">2. Lesson Development</div>
                <p>{{ $entry->lesson_development }}</p>
            </div>
            @endif
            @if($entry->conclusion)
            <div class="phase">
                <div class="phase-title">3. Conclusion</div>
                <p>{{ $entry->conclusion }}</p>
            </div>
            @endif
        </div>
    </div>

    {{-- ── Activities & Resources ── --}}
    <div class="section-box">
        <div class="section-header">Delivery & Resources</div>
        <div class="section-content">
            <table class="two-col">
                <tr>
                    <td>
                        <div class="label">Learner Activities</div>
                        <p>{{ $entry->learning_activities ?? '—' }}</p>
                    </td>
                    <td>
                        <div class="label">Teacher Activities</div>
                        <p>{{ $entry->teacher_activities ?? '—' }}</p>
                    </td>
                </tr>
            </table>
            <div style="margin-top: 6px;">
                <div class="label">Instructional Resources</div>
                <p>{{ $entry->resources ?? '—' }}</p>
            </div>
            @if($entry->references)
            <div style="margin-top: 5px;">
                <div class="label">Textbook References</div>
                <p>{{ $entry->references }}</p>
            </div>
            @endif
        </div>
    </div>

    {{-- ── Competencies ── --}}
    <div class="section-box">
        <div class="section-header">CBC Competencies & PCIs</div>
        <div class="section-content">
            <table class="two-col">
                <tr>
                    <td>
                        <div class="label" style="margin-bottom: 5px;">Core Competencies</div>
                        @if($entry->core_competencies && count($entry->core_competencies))
                            @foreach($entry->core_competencies as $comp)
                                <span class="badge">{{ $comp }}</span>
                            @endforeach
                        @else
                            <span style="color: #94a3b8; font-size: 9px;">None recorded</span>
                        @endif
                    </td>
                    <td>
                        <div class="label" style="margin-bottom: 5px;">Pertinent & Contemporary Issues</div>
                        @if($entry->pci && count($entry->pci))
                            @foreach($entry->pci as $item)
                                <span class="badge">{{ $item }}</span>
                            @endforeach
                        @else
                            <span style="color: #94a3b8; font-size: 9px;">None recorded</span>
                        @endif
                    </td>
                </tr>
            </table>
        </div>
    </div>

    {{-- ── Assessment & Remarks ── --}}
    <div class="section-box">
        <div class="section-header">Assessment & Teacher Reflection</div>
        <div class="section-content">
            @if($entry->assessment)
            <div style="margin-bottom: 6px;">
                <div class="label">Assessment Method</div>
                <p>{{ $entry->assessment }}</p>
            </div>
            @endif
            @if($entry->homework)
            <div style="margin-bottom: 6px;">
                <div class="label">Homework / Assignment</div>
                <p>{{ $entry->homework }}</p>
            </div>
            @endif
            @if($entry->remarks)
            <div style="margin-bottom: 6px;">
                <div class="label">Teacher Remarks</div>
                <p>{{ $entry->remarks }}</p>
            </div>
            @endif
            @if($entry->reflection)
            <div>
                <div class="label">Self-Reflection</div>
                <p>{{ $entry->reflection }}</p>
            </div>
            @endif
        </div>
    </div>
</body>
</html>
