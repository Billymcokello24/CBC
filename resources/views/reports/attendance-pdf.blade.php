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
        .chart-container { margin-bottom: 25px; padding: 15px; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; }
        .chart-title { font-size: 10px; font-weight: bold; margin-bottom: 12px; text-transform: uppercase; color: #64748b; text-align: center; }
        .bar-wrapper { height: 140px; overflow: hidden; position: relative; padding-top: 20px; }
        .bar-group { float: left; width: 33%; height: 100%; position: relative; text-align: center; }
        .bar-item { position: absolute; bottom: 30px; left: 25%; right: 25%; border-radius: 4px 4px 0 0; min-width: 15px; }
        .bar-label { position: absolute; bottom: 10px; width: 100%; font-size: 7px; font-weight: 800; color: #64748b; }
        .bar-value { position: absolute; top: -15px; width: 100%; font-size: 8px; font-weight: bold; color: #0f172a; }
        
        .progress-bar-bg { background-color: #f1f5f9; height: 12px; border-radius: 6px; overflow: hidden; margin: 8px 0; }
        .progress-bar-fill { height: 100%; border-radius: 6px; }

        .status-badge {
            padding: 2px 6px;
            border-radius: 9999px;
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .status-active { background-color: #dcfce7; color: #166534; }
        .status-warning { background-color: #fef9c3; color: #854d0e; }
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
        Generated on {{ date('d M Y, H:i') }} &bull; Attendance Analytics Report
    </div>

    <div class="doc-title-block">
        <div class="doc-title">Institutional Attendance Analytics</div>
        <div style="font-size: 9px; color: #64748b; margin-top: 5px;">
            Target Node: {{ $classTitle }} &bull; Systematic Presence Tracking
        </div>
    </div>

    <div style="margin-bottom: 25px; overflow: hidden;">
        <div style="width: 24%; float: left; background: #f1f5f9; border-radius: 8px; padding: 10px; border-left: 3px solid {{ $color }};">
            <div style="font-size: 8px; text-transform: uppercase; font-weight: bold; color: #64748b;">Global Attendance</div>
            <div style="font-size: 16px; font-weight: 800; color: #0f172a;">{{ $stats->avg_rate }}%</div>
        </div>
        <div style="width: 24%; float: left; margin-left: 1%; background: #f1f5f9; border-radius: 8px; padding: 10px; border-left: 3px solid #3b82f6;">
            <div style="font-size: 8px; text-transform: uppercase; font-weight: bold; color: #64748b;">Male Rate</div>
            <div style="font-size: 16px; font-weight: 800; color: #1e3a8a;">{{ $stats->male_rate }}%</div>
        </div>
        <div style="width: 24%; float: left; margin-left: 1%; background: #f1f5f9; border-radius: 8px; padding: 10px; border-left: 3px solid #ec4899;">
            <div style="font-size: 8px; text-transform: uppercase; font-weight: bold; color: #64748b;">Female Rate</div>
            <div style="font-size: 16px; font-weight: 800; color: #831843;">{{ $stats->female_rate }}%</div>
        </div>
        <div style="width: 24%; float: left; margin-left: 1%; background: #f1f5f9; border-radius: 8px; padding: 10px; border-left: 3px solid #10b981;">
            <div style="font-size: 8px; text-transform: uppercase; font-weight: bold; color: #64748b;">Learners</div>
            <div style="font-size: 16px; font-weight: 800; color: #065f46;">{{ $stats->total }}</div>
        </div>
    </div>

    <!-- Attendance Records Table -->
    <table class="data-table">
        <thead>
            <tr>
                <th width="15%">Admission</th>
                <th width="35%">Learner Name</th>
                <th width="10%">Gender</th>
                <th width="10%" style="text-align: center;">Present</th>
                <th width="10%" style="text-align: center;">Total</th>
                <th width="10%" style="text-align: center;">Rate</th>
                <th width="10%" style="text-align: center;">Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($students as $student)
                @php
                    $statusClass = $student->att_rate >= 90 ? 'status-active' : ($student->att_rate >= 75 ? 'status-warning' : 'status-inactive');
                    $statusText = $student->att_rate >= 90 ? 'Excellent' : ($student->att_rate >= 75 ? 'Steady' : 'Support');
                @<ctrl95>php
                <tr>
                    <td style="font-weight: bold;">{{ $student->admission_number }}</td>
                    <td>{{ $student->full_name }}</td>
                    <td>{{ $student->gender }}</td>
                    <td align="center">{{ $student->att_present }}</td>
                    <td align="center">{{ $student->att_total }}</td>
                    <td align="center" style="font-weight: bold;">{{ $student->att_rate }}%</td>
                    <td align="center">
                        <span class="status-badge {{ $statusClass }}">{{ $statusText }}</span>
                    </td>
                </tr>
            @endforeach
            @if($students->isEmpty())
                <tr>
                    <td colspan="7" align="center" style="padding: 20px;">No operational records found.</td>
                </tr>
            @endif
        </tbody>
        <tfoot>
            <tr style="background-color: #f1f5f9; font-weight: bold;">
                <td colspan="3">INSTITUTIONAL ATTENDANCE SUMMARY (TALLY)</td>
                <td align="center">{{ $students->sum('att_present') }}</td>
                <td align="center">{{ $students->sum('att_total') }}</td>
                <td align="center">{{ $stats->avg_rate }}%</td>
                <td></td>
            </tr>
        </tfoot>
    </table>

    <div style="page-break-before: always;"></div>

    <!-- Vivid Analysis Section -->
    <div style="margin-top: 30px;">
        <h2 style="font-size: 14px; text-transform: uppercase; border-bottom: 2px solid {{ $color }}; padding-bottom: 5px; color: {{ $color }};">Institutional Presence Analysis</h2>
        
        <div style="margin-top: 20px; overflow: hidden;">
            <!-- Attendance Yield by Gender Chart -->
            <div class="chart-container" style="width: 48%; float: left;">
                <div class="chart-title">Attendance Yield by Gender Demographic</div>
                <div style="padding: 10px;">
                    <div style="margin-bottom: 25px;">
                        <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom: 5px;">
                            <tr>
                                <td style="font-size: 9px; font-weight: bold; color: #1e3a8a;">MALE ATTENDANCE</td>
                                <td align="right" style="font-size: 9px; font-weight: bold; color: #1e3a8a;">{{ $stats->male_rate }}%</td>
                            </tr>
                        </table>
                        <div style="background-color: #f1f5f9; height: 15px; border-radius: 4px; overflow: hidden;">
                            <div style="width: {{ $stats->male_rate }}%; height: 100%; background-color: #3b82f6 !important;">&nbsp;</div>
                        </div>
                    </div>

                    <div>
                        <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom: 5px;">
                            <tr>
                                <td style="font-size: 9px; font-weight: bold; color: #831843;">FEMALE ATTENDANCE</td>
                                <td align="right" style="font-size: 9px; font-weight: bold; color: #831843;">{{ $stats->female_rate }}%</td>
                            </tr>
                        </table>
                        <div style="background-color: #f1f5f9; height: 15px; border-radius: 4px; overflow: hidden;">
                            <div style="width: {{ $stats->female_rate }}%; height: 100%; background-color: #ec4899 !important;">&nbsp;</div>
                        </div>
                    </div>

                    <div style="margin-top: 25px; padding: 12px; background: {{ $lightBg }}; border-radius: 8px; border: 1px solid #e2e8f0;">
                        <h4 style="font-size: 9px; margin: 0 0 5px 0;">Gender Participation Tally</h4>
                        <table width="100%" style="font-size: 8px;">
                            <tr>
                                <td width="50%">Male Participants: <strong>{{ $stats->male }}</strong></td>
                                <td width="50%">Female Participants: <strong>{{ $stats->female }}</strong></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Presence Threshold Analysis Chart -->
            <div class="chart-container" style="width: 48%; float: right;">
                <div class="chart-title">Presence Threshold Analytics (Comparative)</div>
                <div style="padding: 10px;">
                    @php 
                        $comparative = [
                            (object)['label' => 'Current Mean', 'val' => $stats->avg_rate, 'color' => $color],
                            (object)['label' => 'Standard Target', 'val' => 95, 'color' => '#10b981'],
                            (object)['label' => 'Critical Threshold', 'val' => 75, 'color' => '#f59e0b']
                        ];
                    @endphp
                    
                    @foreach($comparative as $c)
                    <div style="margin-bottom: 12px;">
                        <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom: 3px;">
                            <tr>
                                <td style="font-size: 8px; font-weight: bold;">{{ $c->label }}</td>
                                <td align="right" style="font-size: 8px; font-weight: bold;">{{ $c->val }}%</td>
                            </tr>
                        </table>
                        <div style="background-color: #f1f5f9; height: 10px; border-radius: 3px; overflow: hidden;">
                            <div style="width: {{ $c->val }}%; height: 100%; background-color: {{ $c->color }} !important;">&nbsp;</div>
                        </div>
                    </div>
                    @endforeach
                    
                    <div style="margin-top: 15px; border-top: 1px dashed #e2e8f0; padding-top: 10px; font-size: 8px; color: #64748b;">
                        * This analysis compares the institutional current mean against pedagogical standard targets.
                    </div>
                </div>
            </div>
        </div>

        <div style="margin-top: 20px; padding: 15px; border: 1px solid #e2e8f0; border-radius: 8px; background-color: #fafafa;">
            <h3 style="font-size: 11px; margin: 0 0 10px 0; color: #1e293b;">Attendance Efficiency Statement</h3>
            <p style="font-size: 9px; color: #475569; margin: 0;">
                The systematic presence tracking indicates a global attendance rate of {{ $stats->avg_rate }}%. 
                Male learners achieve {{ $stats->male_rate }}% while female learners maintain {{ $stats->female_rate }}%. 
                Total institutional presence tally stands at {{ $students->sum('att_present') }} sessions out of a total possible {{ $students->sum('att_total') }} sessions.
            </p>
        </div>
    </div>
</body>
</html>
