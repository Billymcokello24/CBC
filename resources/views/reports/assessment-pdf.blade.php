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
        @endphp

        @page {
            margin: 120px 30px 60px 30px;
        }
        * { box-sizing: border-box; }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 10px;
            color: #1e293b;
            line-height: 1.5;
            margin: 0;
            padding: 0;
        }

        .page-header {
            position: fixed;
            top: -100px;
            left: 0;
            right: 0;
            height: 90px;
            text-align: center;
            border-bottom: 2px solid {{ $color }};
            padding-bottom: 8px;
        }
        .page-header-logo {
            height: 45px;
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

        .footer {
            position: fixed;
            bottom: -40px;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 8px;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
            padding-top: 5px;
        }

        .doc-title-block {
            text-align: center;
            margin-bottom: 20px;
        }
        .doc-title {
            font-size: 14px;
            font-weight: bold;
            color: {{ $color }};
            text-transform: uppercase;
        }

        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        table.data-table th {
            background-color: {{ $color }};
            color: #ffffff;
            padding: 8px;
            text-align: left;
            text-transform: uppercase;
            font-size: 8px;
        }
        table.data-table td {
            border: 1px solid #e2e8f0;
            padding: 8px;
            vertical-align: middle;
        }
        table.data-table tr:nth-child(even) td {
            background-color: #f8fafc;
        }

        .chart-container { margin-bottom: 25px; padding: 15px; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; }
        .chart-title { font-size: 10px; font-weight: bold; margin-bottom: 12px; text-transform: uppercase; color: #64748b; text-align: center; }
        .bar-wrapper { height: 120px; overflow: hidden; position: relative; padding-top: 20px; }
        .bar-group { float: left; width: 25%; height: 100%; position: relative; text-align: center; }
        .bar-item { position: absolute; bottom: 30px; left: 20%; right: 20%; border-radius: 4px 4px 0 0; min-width: 15px; }
        .bar-label { position: absolute; bottom: 10px; width: 100%; font-size: 7px; font-weight: 800; color: #64748b; }
        .bar-value { position: absolute; top: -15px; width: 100%; font-size: 8px; font-weight: bold; color: #0f172a; }
        
        .progress-bar-bg { background-color: #f1f5f9; height: 10px; border-radius: 5px; overflow: hidden; margin: 8px 0; }
        .progress-bar-fill { height: 100%; border-radius: 5px; }

        .status-badge {
            padding: 2px 6px;
            border-radius: 9999px;
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .status-Excellent { background-color: #dcfce7; color: #166534; }
        .status-Average { background-color: #dbeafe; color: #1e40af; }
        .status-Below { background-color: #fee2e2; color: #991b1b; }
    </style>
</head>
<body>
    <div class="page-header">
        @if($school?->logo)
            <img class="page-header-logo" src="{{ storage_path('app/public/' . $school->logo) }}" />
        @endif
        <div class="page-header-name">{{ $school?->name ?? 'CBC School System' }}</div>
    </div>

    <div class="footer">
        Generated on {{ date('d M Y, H:i') }} &bull; Academic Assessment Analytics
    </div>

    <div class="doc-title-block">
        <div class="doc-title">Pedagogical Assessment & Mastery Analytics</div>
        <div style="font-size: 9px; color: #64748b; margin-top: 5px;">
            Target Node: {{ $classTitle }} &bull; Systematic Competency Audit
        </div>
    </div>

    <div style="margin-bottom: 25px; overflow: hidden;">
        <div style="width: 24%; float: left; background: #f1f5f9; border-radius: 8px; padding: 10px; border-left: 3px solid {{ $color }};">
            <div style="font-size: 8px; text-transform: uppercase; font-weight: bold; color: #64748b;">Collective Mean</div>
            <div style="font-size: 16px; font-weight: 800; color: #0f172a;">{{ $stats->avg_score }}%</div>
        </div>
        <div style="width: 24%; float: left; margin-left: 1%; background: #f1f5f9; border-radius: 8px; padding: 10px; border-left: 3px solid #3b82f6;">
            <div style="font-size: 8px; text-transform: uppercase; font-weight: bold; color: #64748b;">Assessed Items</div>
            <div style="font-size: 16px; font-weight: 800; color: #1e3a8a;">{{ count($assessmentsData) }}</div>
        </div>
        <div style="width: 24%; float: left; margin-left: 1%; background: #f1f5f9; border-radius: 8px; padding: 10px; border-left: 3px solid #10b981;">
            <div style="font-size: 8px; text-transform: uppercase; font-weight: bold; color: #64748b;">Total Learners</div>
            <div style="font-size: 16px; font-weight: 800; color: #065f46;">{{ $stats->total_assessed }}</div>
        </div>
        <div style="width: 24%; float: left; margin-left: 1%; background: #f1f5f9; border-radius: 8px; padding: 10px; border-left: 3px solid #f59e0b;">
            <div style="font-size: 8px; text-transform: uppercase; font-weight: bold; color: #64748b;">Target Readiness</div>
            <div style="font-size: 16px; font-weight: 800; color: #92400e;">Optimal</div>
        </div>
    </div>

    <!-- Assessment Records Table -->
    <table class="data-table">
        <thead>
            <tr>
                <th width="30%">Learning Domain (Subject)</th>
                <th width="15%" style="text-align: center;">Sample Size</th>
                <th width="15%" style="text-align: center;">Global Mean</th>
                <th width="15%" style="text-align: center;">Male Mean</th>
                <th width="15%" style="text-align: center;">Female Mean</th>
                <th width="10%" style="text-align: right;">Yield</th>
            </tr>
        </thead>
        <tbody>
            @foreach($assessmentsData as $a)
                <tr>
                    <td style="font-weight: bold;">{{ $a->subject }}</td>
                    <td align="center">{{ $a->total_assessed }} Learners</td>
                    <td align="center" style="font-weight: 800;">{{ $a->average_score }}%</td>
                    <td align="center" style="color: #3b82f6;">{{ $a->male_avg }}%</td>
                    <td align="center" style="color: #ec4899;">{{ $a->female_avg }}%</td>
                    <td align="right">
                        <span class="status-badge status-{{ str_replace(' ', '', $a->status) }}">
                            {{ $a->status }}
                        </span>
                    </td>
                </tr>
            @endforeach
            @if(empty($assessmentsData))
                <tr>
                    <td colspan="6" align="center" style="padding: 20px;">No assessment records found in the current instructional period.</td>
                </tr>
            @endif
        </tbody>
        <tfoot>
            <tr style="background-color: #f1f5f9; font-weight: bold;">
                <td colspan="2">INSTITUTIONAL MASTERY TALLY</td>
                <td align="center">{{ $stats->avg_score }}% (Global)</td>
                <td align="center">{{ collect($assessmentsData)->avg('male_avg') }}%</td>
                <td align="center">{{ collect($assessmentsData)->avg('female_avg') }}%</td>
                <td></td>
            </tr>
        </tfoot>
    </table>

    <div style="page-break-before: always;"></div>

    <!-- Vivid Analysis Section -->
    <div style="margin-top: 30px;">
        <h2 style="font-size: 14px; text-transform: uppercase; border-bottom: 2px solid {{ $color }}; padding-bottom: 5px; color: {{ $color }};">Institutional Proficiency Analysis</h2>
        
        <div style="margin-top: 20px; overflow: hidden;">
            <!-- Proficiency by Gender Chart -->
            <div class="chart-container" style="width: 48%; float: left;">
                <div class="chart-title">Global Proficiency Yield (Gender Parity)</div>
                <div style="padding: 10px;">
                    @php 
                        $mMean = collect($assessmentsData)->avg('male_avg');
                        $fMean = collect($assessmentsData)->avg('female_avg');
                    @endphp
                    
                    <div style="margin-bottom: 25px;">
                        <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom: 5px;">
                            <tr>
                                <td style="font-size: 9px; font-weight: bold; color: #1e3a8a;">MALE PROFICIENCY</td>
                                <td align="right" style="font-size: 9px; font-weight: bold; color: #1e3a8a;">{{ number_format($mMean, 1) }}%</td>
                            </tr>
                        </table>
                        <div style="background-color: #f1f5f9; height: 15px; border-radius: 4px; overflow: hidden;">
                            <div style="width: {{ $mMean }}%; height: 100%; background-color: #3b82f6 !important;">&nbsp;</div>
                        </div>
                    </div>

                    <div>
                        <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom: 5px;">
                            <tr>
                                <td style="font-size: 9px; font-weight: bold; color: #831843;">FEMALE PROFICIENCY</td>
                                <td align="right" style="font-size: 9px; font-weight: bold; color: #831843;">{{ number_format($fMean, 1) }}%</td>
                            </tr>
                        </table>
                        <div style="background-color: #f1f5f9; height: 15px; border-radius: 4px; overflow: hidden;">
                            <div style="width: {{ $fMean }}%; height: 100%; background-color: #ec4899 !important;">&nbsp;</div>
                        </div>
                    </div>

                    <div style="margin-top: 25px; padding: 12px; background: {{ $lightBg }}; border-radius: 8px; border: 1px solid #e2e8f0; font-size: 8px; color: #475569;">
                        <strong>INSIGHT:</strong> The current global mean of <strong>{{ $stats->avg_score }}%</strong> indicates 
                        @if($stats->avg_score >= 80)
                            exceptional instructional stability.
                        @elseif($stats->avg_score >= 60)
                            steady pedagogical progress.
                        @else
                            a requirement for targeted intervention.
                        @endif
                    </div>
                </div>
            </div>

            <!-- Top Domains Bar Chart -->
            <div class="chart-container" style="width: 48%; float: right;">
                <div class="chart-title">Top Performing Domains (Yield Analysis)</div>
                <div style="padding: 10px;">
                    @php 
                        $top3 = collect($assessmentsData)->sortByDesc('average_score')->take(5);
                        $maxScore = $top3->max('average_score') ?: 1;
                    @endphp
                    
                    @foreach($top3 as $a)
                    <div style="margin-bottom: 12px;">
                        <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom: 3px;">
                            <tr>
                                <td style="font-size: 8px; font-weight: bold;">{{ $a->subject }}</td>
                                <td align="right" style="font-size: 8px; font-weight: bold;">{{ $a->average_score }}%</td>
                            </tr>
                        </table>
                        <div style="background-color: #f1f5f9; height: 10px; border-radius: 3px; overflow: hidden;">
                            <div style="width: {{ ($a->average_score / $maxScore) * 100 }}%; height: 100%; background-color: {{ $color }} !important;">&nbsp;</div>
                        </div>
                    </div>
                    @endforeach
                    
                    <div style="margin-top: 15px; border-top: 1px dashed #e2e8f0; padding-top: 10px; font-size: 8px; color: #64748b;">
                        * This visualization represents subjects with the highest overall proficiency levels.
                    </div>
                </div>
            </div>
        </div>

        <div style="margin-top: 20px; padding: 15px; border: 1px solid #e2e8f0; border-radius: 8px; background-color: #fafafa;">
            <h3 style="font-size: 11px; margin: 0 0 10px 0; color: #1e293b;">Executive Competency Statement</h3>
            <p style="font-size: 9px; color: #475569; margin: 0;">
                The audited pedagogical roadmap for {{ $classTitle }} reveals a collective mean of {{ $stats->avg_score }}%. 
                Male proficiency stands at {{ number_format($mMean, 1) }}% while female proficiency stands at {{ number_format($fMean, 1) }}%. 
                A total of {{ count($assessmentsData) }} learning domains were evaluated with {{ $stats->total_assessed }} individual learner assessments processed.
            </p>
        </div>
    </div>
</body>
</html>
