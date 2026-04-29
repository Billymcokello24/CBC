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
        .chart-title { font-size: 10px; font-weight: bold; margin-bottom: 15px; text-transform: uppercase; color: #64748b; text-align: center; }
        .bar-wrapper { height: 160px; overflow: hidden; position: relative; padding-top: 20px; }
        .bar-group { float: left; width: 33%; height: 100%; position: relative; text-align: center; }
        .bar-item { position: absolute; bottom: 30px; left: 25%; right: 25%; border-radius: 4px 4px 0 0; min-width: 15px; }
        .bar-label { position: absolute; bottom: 10px; width: 100%; font-size: 7px; font-weight: 800; color: #64748b; }
        .bar-value { position: absolute; top: -15px; width: 100%; font-size: 8px; font-weight: bold; color: #0f172a; }
        
        .progress-circle-container { text-align: center; padding: 10px; }
        .progress-bar-bg { background-color: #f1f5f9; height: 10px; border-radius: 5px; overflow: hidden; margin: 10px 0; }
        .progress-bar-fill { height: 100%; border-radius: 5px; }

        .gender-box { float: left; width: 48%; padding: 15px; border-radius: 8px; border: 1px solid #e2e8f0; }

        .status-badge {
            padding: 2px 6px;
            border-radius: 9999px;
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .status-active { background-color: #dcfce7; color: #166534; }
        .status-inactive { background-color: #fee2e2; color: #991b1b; }
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
        Generated on {{ date('d M Y, H:i') }} &bull; Enrollment Distribution Report
    </div>

    <div class="doc-title-block">
        <div class="doc-title">Enrollment Distribution & Analytics</div>
        <div style="font-size: 9px; color: #64748b; margin-top: 5px;">
            Institutional capacity assessment and demographic breakdown
        </div>
    </div>

    <div style="margin-bottom: 25px; overflow: hidden;">
        <div style="width: 24%; float: left; background: #f1f5f9; border-radius: 8px; padding: 10px; border-left: 3px solid {{ $color }};">
            <div style="font-size: 8px; text-transform: uppercase; font-weight: bold; color: #64748b;">Total Learners</div>
            <div style="font-size: 16px; font-weight: 800; color: #0f172a;">{{ $stats->total }}</div>
        </div>
        <div style="width: 24%; float: left; margin-left: 1%; background: #f1f5f9; border-radius: 8px; padding: 10px; border-left: 3px solid #3b82f6;">
            <div style="font-size: 8px; text-transform: uppercase; font-weight: bold; color: #64748b;">Male Population</div>
            <div style="font-size: 16px; font-weight: 800; color: #1e3a8a;">{{ $stats->male }}</div>
        </div>
        <div style="width: 24%; float: left; margin-left: 1%; background: #f1f5f9; border-radius: 8px; padding: 10px; border-left: 3px solid #ec4899;">
            <div style="font-size: 8px; text-transform: uppercase; font-weight: bold; color: #64748b;">Female Population</div>
            <div style="font-size: 16px; font-weight: 800; color: #831843;">{{ $stats->female }}</div>
        </div>
        <div style="width: 24%; float: left; margin-left: 1%; background: #f1f5f9; border-radius: 8px; padding: 10px; border-left: 3px solid #10b981;">
            <div style="font-size: 8px; text-transform: uppercase; font-weight: bold; color: #64748b;">Total Classes</div>
            <div style="font-size: 16px; font-weight: 800; color: #065f46;">{{ count($classesData) }}</div>
        </div>
    </div>

    <!-- General Records Table -->
    <table class="data-table">
        <thead>
            <tr>
                <th width="25%">Learning Node (Class)</th>
                <th width="20%">Grade Level</th>
                <th width="15%" style="text-align: center;">Male</th>
                <th width="15%" style="text-align: center;">Female</th>
                <th width="10%" style="text-align: center;">Total</th>
                <th width="15%" style="text-align: center;">Capacity Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($classesData as $c)
                <tr>
                    <td style="font-weight: bold;">{{ $c->class }}</td>
                    <td>{{ $c->grade }}</td>
                    <td align="center">{{ $c->male }}</td>
                    <td align="center">{{ $c->female }}</td>
                    <td align="center" style="font-weight: bold;">{{ $c->total }}</td>
                    <td align="center">
                        <span class="status-badge {{ $c->status === 'Optimal' ? 'status-active' : 'status-inactive' }}">
                            {{ $c->status }}
                        </span>
                    </td>
                </tr>
            @endforeach
            @if(empty($classesData))
                <tr>
                    <td colspan="6" align="center" style="padding: 20px;">No records found for the current filter.</td>
                </tr>
            @endif
        </tbody>
        <tfoot>
            <tr style="background-color: #f1f5f9; font-weight: bold;">
                <td colspan="2">TOTAL INSTITUTIONAL TALLY</td>
                <td align="center">{{ $stats->male }}</td>
                <td align="center">{{ $stats->female }}</td>
                <td align="center">{{ $stats->total }}</td>
                <td></td>
            </tr>
        </tfoot>
    </table>

    <div style="page-break-before: always;"></div>

    <!-- Vivid Analysis Section -->
    <div style="margin-top: 30px;">
        <h2 style="font-size: 14px; text-transform: uppercase; border-bottom: 2px solid {{ $color }}; padding-bottom: 5px; color: {{ $color }};">Institutional Demographic Analysis</h2>
        
        <div style="margin-top: 20px; overflow: hidden;">
            <!-- Gender Parity Chart -->
            <div class="chart-container" style="width: 48%; float: left;">
                <div class="chart-title">Gender Distribution & Parity</div>
                <div style="padding: 10px;">
                    @php
                        $total = $stats->total ?: 1;
                        $mPercent = round(($stats->male / $total) * 100);
                        $fPercent = round(($stats->female / $total) * 100);
                    @endphp
                    
                    <div style="margin-bottom: 25px;">
                        <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom: 5px;">
                            <tr>
                                <td style="font-size: 9px; font-weight: bold; color: #1e3a8a;">MALE LEARNERS</td>
                                <td align="right" style="font-size: 9px; font-weight: bold; color: #1e3a8a;">{{ $stats->male }} ({{ $mPercent }}%)</td>
                            </tr>
                        </table>
                        <div style="background-color: #f1f5f9; height: 15px; border-radius: 4px; overflow: hidden;">
                            <div style="width: {{ $mPercent }}%; height: 100%; background-color: #3b82f6 !important;">&nbsp;</div>
                        </div>
                    </div>

                    <div>
                        <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom: 5px;">
                            <tr>
                                <td style="font-size: 9px; font-weight: bold; color: #831843;">FEMALE LEARNERS</td>
                                <td align="right" style="font-size: 9px; font-weight: bold; color: #831843;">{{ $stats->female }} ({{ $fPercent }}%)</td>
                            </tr>
                        </table>
                        <div style="background-color: #f1f5f9; height: 15px; border-radius: 4px; overflow: hidden;">
                            <div style="width: {{ $fPercent }}%; height: 100%; background-color: #ec4899 !important;">&nbsp;</div>
                        </div>
                    </div>

                    <div style="margin-top: 20px; padding: 10px; background: {{ $lightBg }}; border-radius: 6px; font-size: 8px; color: #475569;">
                        <strong>INSIGHT:</strong> The institution exhibits a gender parity ratio of {{ $stats->male }}:{{ $stats->female }}. 
                        @if(abs($mPercent - $fPercent) < 5)
                            Enrollment is excellently balanced across demographics.
                        @else
                            There is a noticeable demographic skew that may require strategic enrollment focus.
                        @endif
                    </div>
                </div>
            </div>

            <!-- Enrollment Density Chart -->
            <div class="chart-container" style="width: 48%; float: right;">
                <div class="chart-title">Class Enrollment Density (Top 5)</div>
                <div style="padding: 10px;">
                    @php 
                        $topClasses = collect($classesData)->sortByDesc('total')->take(5);
                        $maxEnrollment = $topClasses->max('total') ?: 1;
                    @endphp
                    
                    @foreach($topClasses as $c)
                    <div style="margin-bottom: 12px;">
                        <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom: 3px;">
                            <tr>
                                <td style="font-size: 8px; font-weight: bold;">{{ $c->class }}</td>
                                <td align="right" style="font-size: 8px; font-weight: bold;">{{ $c->total }}</td>
                            </tr>
                        </table>
                        @php $width = ($c->total / $maxEnrollment) * 100; @endphp
                        <div style="background-color: #f1f5f9; height: 10px; border-radius: 3px; overflow: hidden;">
                            <div style="width: {{ $width }}%; height: 100%; background-color: {{ $color }} !important;">&nbsp;</div>
                        </div>
                    </div>
                    @endforeach
                    
                    <div style="margin-top: 15px; border-top: 1px dashed #e2e8f0; padding-top: 10px; font-size: 8px; color: #64748b;">
                        * This visualization represents the learning nodes with the highest population density.
                    </div>
                </div>
            </div>
        </div>

        <div style="margin-top: 20px; padding: 15px; border: 1px solid #e2e8f0; border-radius: 8px;">
            <h3 style="font-size: 11px; margin: 0 0 10px 0; color: #1e293b;">Executive Summary & Tallies</h3>
            <table width="100%" style="font-size: 9px;">
                <tr>
                    <td width="25%"><strong>Total Institutional Tally:</strong></td>
                    <td width="25%">{{ $stats->total }} Learners</td>
                    <td width="25%"><strong>Average Learners / Class:</strong></td>
                    <td width="25%">{{ count($classesData) > 0 ? round($stats->total / count($classesData), 1) : 0 }}</td>
                </tr>
                <tr>
                    <td><strong>Overall Male Tally:</strong></td>
                    <td>{{ $stats->male }}</td>
                    <td><strong>Overall Female Tally:</strong></td>
                    <td>{{ $stats->female }}</td>
                </tr>
            </table>
        </div>
    </div>
</body>
</html>
