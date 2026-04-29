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
        .bar-wrapper { height: 120px; overflow: hidden; position: relative; padding-top: 20px; }
        .bar-group { float: left; width: 33%; height: 100%; position: relative; text-align: center; }
        .bar-item { position: absolute; bottom: 30px; left: 25%; right: 25%; border-radius: 4px 4px 0 0; min-width: 15px; }
        .bar-label { position: absolute; bottom: 10px; width: 100%; font-size: 7px; font-weight: 800; color: #64748b; }
        .bar-value { position: absolute; top: -15px; width: 100%; font-size: 8px; font-weight: bold; color: #0f172a; }
        
        .progress-bar-bg { background-color: #f1f5f9; height: 10px; border-radius: 5px; overflow: hidden; margin: 8px 0; }
        .progress-bar-fill { height: 100%; border-radius: 5px; }

        .badge {
            padding: 2px 6px;
            border-radius: 9999px;
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .status-active { background-color: #dcfce7; color: #166534; }
        .status-suspended { background-color: #fee2e2; color: #991b1b; }
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
        Generated on {{ date('d M Y, H:i') }} &bull; Learner Profile Report
    </div>

    <div class="doc-title-block">
        <div class="doc-title">Student Directory & Demographic Analytics</div>
        <div style="font-size: 9px; color: #64748b; margin-top: 5px;">
            Institutional Enrollment Ledger &bull; Records Quality: High
        </div>
    </div>

    <div style="margin-bottom: 25px; overflow: hidden;">
        <div style="width: 24%; float: left; background: #f1f5f9; border-radius: 8px; padding: 10px; border-left: 3px solid {{ $color }};">
            <div style="font-size: 8px; text-transform: uppercase; font-weight: bold; color: #64748b;">Grand Total</div>
            <div style="font-size: 16px; font-weight: 800; color: #0f172a;">{{ $stats->total }}</div>
        </div>
        <div style="width: 24%; float: left; margin-left: 1%; background: #f1f5f9; border-radius: 8px; padding: 10px; border-left: 3px solid #3b82f6;">
            <div style="font-size: 8px; text-transform: uppercase; font-weight: bold; color: #64748b;">Male Learners</div>
            <div style="font-size: 16px; font-weight: 800; color: #1e3a8a;">{{ $stats->male }}</div>
        </div>
        <div style="width: 24%; float: left; margin-left: 1%; background: #f1f5f9; border-radius: 8px; padding: 10px; border-left: 3px solid #ec4899;">
            <div style="font-size: 8px; text-transform: uppercase; font-weight: bold; color: #64748b;">Female Learners</div>
            <div style="font-size: 16px; font-weight: 800; color: #831843;">{{ $stats->female }}</div>
        </div>
        <div style="width: 24%; float: left; margin-left: 1%; background: #f1f5f9; border-radius: 8px; padding: 10px; border-left: 3px solid #10b981;">
            <div style="font-size: 8px; text-transform: uppercase; font-weight: bold; color: #64748b;">Active Status</div>
            <div style="font-size: 16px; font-weight: 800; color: #065f46;">{{ $stats->active }}</div>
        </div>
    </div>

    <!-- Learner Directory Table -->
    <table class="data-table">
        <thead>
            <tr>
                <th width="35%">Learner Identity (Full Name)</th>
                <th width="15%">Admission #</th>
                <th width="15%">Gender</th>
                <th width="20%">Current Node (Class)</th>
                <th width="15%" style="text-align: right;">Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($learners as $l)
                <tr>
                    <td style="font-weight: bold;">{{ $l->full_name }}</td>
                    <td align="center">{{ $l->admission_number }}</td>
                    <td align="center" style="font-weight: bold;">{{ strtolower($l->gender) === 'male' ? 'Male' : 'Female' }}</td>
                    <td>{{ optional($l->currentClass)->name ?? 'Unassigned' }}</td>
                    <td align="right"><span class="badge {{ $l->status === 'active' ? 'status-active' : 'status-inactive' }}">{{ strtoupper($l->status) }}</span></td>
                </tr>
            @endforeach
            @if($learners->isEmpty())
                <tr>
                    <td colspan="5" align="center" style="padding: 20px;">Student matrix currently null.</td>
                </tr>
            @endif
        </tbody>
        <tfoot>
            <tr style="background-color: #f1f5f9; font-weight: bold;">
                <td colspan="2">INSTITUTIONAL POPULATION TALLY</td>
                <td align="center">{{ $stats->male }} Male &bull; {{ $stats->female }} Female</td>
                <td></td>
                <td align="right">{{ $stats->total }} Total</td>
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
                <div class="chart-title">Global Demographic Distribution</div>
                <div style="padding: 10px;">
                    @php
                        $total = $stats->total ?: 1;
                        $mP = round(($stats->male / $total) * 100);
                        $fP = round(($stats->female / $total) * 100);
                    @endphp
                    
                    <div style="margin-bottom: 25px;">
                        <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom: 5px;">
                            <tr>
                                <td style="font-size: 9px; font-weight: bold; color: #1e3a8a;">MALE LEARNERS</td>
                                <td align="right" style="font-size: 9px; font-weight: bold; color: #1e3a8a;">{{ $stats->male }} ({{ $mP }}%)</td>
                            </tr>
                        </table>
                        <div style="background-color: #f1f5f9; height: 15px; border-radius: 4px; overflow: hidden;">
                            <div style="width: {{ $mP }}%; height: 100%; background-color: #3b82f6 !important;">&nbsp;</div>
                        </div>
                    </div>

                    <div>
                        <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom: 5px;">
                            <tr>
                                <td style="font-size: 9px; font-weight: bold; color: #831843;">FEMALE LEARNERS</td>
                                <td align="right" style="font-size: 9px; font-weight: bold; color: #831843;">{{ $stats->female }} ({{ $fP }}%)</td>
                            </tr>
                        </table>
                        <div style="background-color: #f1f5f9; height: 15px; border-radius: 4px; overflow: hidden;">
                            <div style="width: {{ $fP }}%; height: 100%; background-color: #ec4899 !important;">&nbsp;</div>
                        </div>
                    </div>

                    <div style="margin-top: 25px; padding: 12px; background: {{ $lightBg }}; border-radius: 8px; border: 1px solid #e2e8f0; font-size: 8px; color: #475569;">
                        <strong>INSIGHT:</strong> The institutional demographic balance stands at <strong>{{ $mP }}% Male</strong> to <strong>{{ $fP }}% Female</strong>. 
                        This distribution reflects the current pedagogical capacity and enrollment trends of the school.
                    </div>
                </div>
            </div>

            <!-- Growth/Status Bar Chart -->
            <div class="chart-container" style="width: 48%; float: right;">
                <div class="chart-title">Status & Growth Metrics</div>
                <div style="padding: 10px;">
                    @php 
                        $metrics = [
                            (object)['label' => 'Total Enrolled', 'val' => $stats->total, 'color' => $color],
                            (object)['label' => 'Active Status', 'val' => $stats->active, 'color' => '#10b981'],
                            (object)['label' => 'Male Count', 'val' => $stats->male, 'color' => '#3b82f6'],
                            (object)['label' => 'Female Count', 'val' => $stats->female, 'color' => '#ec4899']
                        ];
                        $maxMetric = collect($metrics)->max('val') ?: 1;
                    @endphp
                    
                    @foreach($metrics as $m)
                    <div style="margin-bottom: 12px;">
                        <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom: 3px;">
                            <tr>
                                <td style="font-size: 8px; font-weight: bold;">{{ $m->label }}</td>
                                <td align="right" style="font-size: 8px; font-weight: bold;">{{ $m->val }}</td>
                            </tr>
                        </table>
                        <div style="background-color: #f1f5f9; height: 10px; border-radius: 3px; overflow: hidden;">
                            <div style="width: {{ ($m->val / $maxMetric) * 100 }}%; height: 100%; background-color: {{ $m->color }} !important;">&nbsp;</div>
                        </div>
                    </div>
                    @endforeach
                    
                    <div style="margin-top: 15px; border-top: 1px dashed #e2e8f0; padding-top: 10px; font-size: 8px; color: #64748b;">
                        * This represents a comparative snapshot of total enrollment versus operational status.
                    </div>
                </div>
            </div>
        </div>

        <div style="margin-top: 20px; padding: 15px; border: 1px solid #e2e8f0; border-radius: 8px; background-color: #fafafa;">
            <h3 style="font-size: 11px; margin: 0 0 10px 0; color: #1e293b;">Executive Demographic Summary</h3>
            <p style="font-size: 9px; color: #475569; margin: 0;">
                The audited student directory comprises a total of <strong>{{ $stats->total }}</strong> learners. 
                Operational audit confirms <strong>{{ $stats->active }}</strong> active records. 
                The institutional demographic tally stands at <strong>{{ $stats->male }} male</strong> and <strong>{{ $stats->female }} female</strong> learners, 
                distributed across various grade levels and learning nodes.
            </p>
        </div>
    </div>
</body>
</html>
