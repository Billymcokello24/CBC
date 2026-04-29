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
        
        .summary-box { 
            background-color: {{ $lightBg }}; 
            padding: 12px; 
            border-radius: 4px; 
            margin-bottom: 15px; 
            border-left: 4px solid {{ $color }};
        }
        .summary-box p { margin: 0; font-size: 11px; }

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
        .status-cleared { background-color: #dcfce7; color: #166534; }
        .status-pending { background-color: #fee2e2; color: #991b1b; }
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
        Generated on {{ date('d M Y, H:i') }} &bull; Institutional Financial Report
    </div>

    <div class="doc-title-block">
        <div class="doc-title">Institutional Financial Performance Ledger</div>
        <div style="font-size: 9px; color: #64748b; margin-top: 5px;">
            Audited Billing & Revenue Matrix &bull; Collection Rate: {{ $summary->collection_rate }}%
        </div>
    </div>

    <div style="margin-bottom: 25px; overflow: hidden;">
        <div style="width: 24%; float: left; background: #f1f5f9; border-radius: 8px; padding: 10px; border-left: 3px solid {{ $color }};">
            <div style="font-size: 8px; text-transform: uppercase; font-weight: bold; color: #64748b;">Total Revenue</div>
            <div style="font-size: 14px; font-weight: 800; color: #0f172a;">KES {{ number_format($summary->totalBilled) }}</div>
        </div>
        <div style="width: 24%; float: left; margin-left: 1%; background: #f1f5f9; border-radius: 8px; padding: 10px; border-left: 3px solid #10b981;">
            <div style="font-size: 8px; text-transform: uppercase; font-weight: bold; color: #64748b;">Total Collected</div>
            <div style="font-size: 14px; font-weight: 800; color: #065f46;">KES {{ number_format($summary->totalPaid) }}</div>
        </div>
        <div style="width: 24%; float: left; margin-left: 1%; background: #f1f5f9; border-radius: 8px; padding: 10px; border-left: 3px solid #3b82f6;">
            <div style="font-size: 8px; text-transform: uppercase; font-weight: bold; color: #64748b;">Male Yield</div>
            <div style="font-size: 14px; font-weight: 800; color: #1e3a8a;">KES {{ number_format($summary->malePaid) }}</div>
        </div>
        <div style="width: 24%; float: left; margin-left: 1%; background: #f1f5f9; border-radius: 8px; padding: 10px; border-left: 3px solid #ec4899;">
            <div style="font-size: 8px; text-transform: uppercase; font-weight: bold; color: #64748b;">Female Yield</div>
            <div style="font-size: 14px; font-weight: 800; color: #831843;">KES {{ number_format($summary->femalePaid) }}</div>
        </div>
    </div>

    <!-- Financial Ledger Table -->
    <table class="data-table">
        <thead>
            <tr>
                <th width="25%">Learner Identity</th>
                <th width="10%">Adm #</th>
                <th width="10%">Gender</th>
                <th width="15%" style="text-align: right;">Billed (Kes)</th>
                <th width="15%" style="text-align: right;">Paid (Kes)</th>
                <th width="15%" style="text-align: right;">Balance (Kes)</th>
                <th width="10%" style="text-align: center;">Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($records as $r)
                <tr>
                    <td style="font-weight: bold;">{{ $r->student }}</td>
                    <td align="center">{{ $r->adm }}</td>
                    <td align="center">{{ $r->gender }}</td>
                    <td align="right">{{ number_format($r->billed, 2) }}</td>
                    <td align="right" style="color: #166534; font-weight: bold;">{{ number_format($r->paid, 2) }}</td>
                    <td align="right" style="color: #991b1b;">{{ number_format($r->balance, 2) }}</td>
                    <td align="center"><span class="badge {{ $r->status === 'Cleared' ? 'status-cleared' : 'status-pending' }}">{{ strtoupper($r->status) }}</span></td>
                </tr>
            @endforeach
            @if(empty($records))
                <tr>
                    <td colspan="7" align="center" style="padding: 20px;">Financial record matrix is currently null.</td>
                </tr>
            @endif
        </tbody>
        <tfoot>
            <tr style="background-color: #f1f5f9; font-weight: bold;">
                <td colspan="3">INSTITUTIONAL FINANCIAL TALLY</td>
                <td align="right">KES {{ number_format($summary->totalBilled, 2) }}</td>
                <td align="right">KES {{ number_format($summary->totalPaid, 2) }}</td>
                <td align="right">KES {{ number_format($summary->totalBilled - $summary->totalPaid, 2) }}</td>
                <td></td>
            </tr>
        </tfoot>
    </table>

    <div style="page-break-before: always;"></div>

    <!-- Vivid Analysis Section -->
    <div style="margin-top: 30px;">
        <h2 style="font-size: 14px; text-transform: uppercase; border-bottom: 2px solid {{ $color }}; padding-bottom: 5px; color: {{ $color }};">Institutional Revenue Analysis</h2>
        
        <div style="margin-top: 20px; overflow: hidden;">
            <!-- Revenue Extraction Chart -->
            <div class="chart-container" style="width: 48%; float: left;">
                <div class="chart-title">Revenue Extraction Efficiency</div>
                <div style="padding: 10px;">
                    <div style="margin-bottom: 25px;">
                        <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom: 5px;">
                            <tr>
                                <td style="font-size: 9px; font-weight: bold; color: #166534;">COLLECTION RATE</td>
                                <td align="right" style="font-size: 9px; font-weight: bold; color: #166534;">{{ $summary->collection_rate }}%</td>
                            </tr>
                        </table>
                        <div style="background-color: #f1f5f9; height: 15px; border-radius: 4px; overflow: hidden;">
                            <div style="width: {{ $summary->collection_rate }}%; height: 100%; background-color: #10b981 !important;">&nbsp;</div>
                        </div>
                    </div>

                    <div>
                        <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom: 5px;">
                            <tr>
                                <td style="font-size: 9px; font-weight: bold; color: #991b1b;">OUTSTANDING LIABILITIES</td>
                                <td align="right" style="font-size: 9px; font-weight: bold; color: #991b1b;">{{ number_format(100 - $summary->collection_rate, 1) }}%</td>
                            </tr>
                        </table>
                        <div style="background-color: #f1f5f9; height: 15px; border-radius: 4px; overflow: hidden;">
                            <div style="width: {{ 100 - $summary->collection_rate }}%; height: 100%; background-color: #ef4444 !important;">&nbsp;</div>
                        </div>
                    </div>

                    <div style="margin-top: 25px; padding: 12px; background: {{ $lightBg }}; border-radius: 8px; border: 1px solid #e2e8f0; font-size: 8px; color: #475569;">
                        <strong>INSIGHT:</strong> The current collection efficiency of <strong>{{ $summary->collection_rate }}%</strong> indicates 
                        @if($summary->collection_rate >= 90)
                            exceptional financial health and liquidity.
                        @elseif($summary->collection_rate >= 70)
                            stable revenue cycles with moderate arrears.
                        @else
                            a critical requirement for debt recovery focus.
                        @endif
                    </div>
                </div>
            </div>

            <!-- Demographic Yield Chart -->
            <div class="chart-container" style="width: 48%; float: right;">
                <div class="chart-title">Demographic Yield Parity (Collection Rate)</div>
                <div style="padding: 10px;">
                    @php 
                        $mRate = $summary->maleBilled > 0 ? round(($summary->malePaid / $summary->maleBilled) * 100) : 0;
                        $fRate = $summary->femaleBilled > 0 ? round(($summary->femalePaid / $summary->femaleBilled) * 100) : 0;
                    @endphp
                    
                    <div style="margin-bottom: 20px;">
                        <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom: 3px;">
                            <tr>
                                <td style="font-size: 8px; font-weight: bold; color: #1e3a8a;">MALE REVENUE YIELD</td>
                                <td align="right" style="font-size: 8px; font-weight: bold;">{{ $mRate }}%</td>
                            </tr>
                        </table>
                        <div style="background-color: #f1f5f9; height: 10px; border-radius: 3px; overflow: hidden;">
                            <div style="width: {{ $mRate }}%; height: 100%; background-color: #3b82f6 !important;">&nbsp;</div>
                        </div>
                    </div>

                    <div style="margin-bottom: 20px;">
                        <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom: 3px;">
                            <tr>
                                <td style="font-size: 8px; font-weight: bold; color: #831843;">FEMALE REVENUE YIELD</td>
                                <td align="right" style="font-size: 8px; font-weight: bold;">{{ $fRate }}%</td>
                            </tr>
                        </table>
                        <div style="background-color: #f1f5f9; height: 10px; border-radius: 3px; overflow: hidden;">
                            <div style="width: {{ $fRate }}%; height: 100%; background-color: #ec4899 !important;">&nbsp;</div>
                        </div>
                    </div>
                    
                    <div style="margin-top: 15px; border-top: 1px dashed #e2e8f0; padding-top: 10px; font-size: 8px; color: #64748b;">
                        * This metric compares the percentage of billed revenue successfully collected per demographic.
                    </div>
                </div>
            </div>
        </div>

        <div style="margin-top: 20px; padding: 15px; border: 1px solid #e2e8f0; border-radius: 8px; background-color: #fafafa;">
            <h3 style="font-size: 11px; margin: 0 0 10px 0; color: #1e293b;">Executive Financial Statement</h3>
            <p style="font-size: 9px; color: #475569; margin: 0;">
                The audited institutional extract reveals a gross billed revenue of KES {{ number_format($summary->totalBilled) }} for the current period. 
                Successful collections amount to KES {{ number_format($summary->totalPaid) }}, representing a {{ $summary->collection_rate }}% yield. 
                Demographic contributions are balanced at KES {{ number_format($summary->malePaid) }} (Male) and KES {{ number_format($summary->femalePaid) }} (Female).
            </p>
        </div>
    </div>
</body>
</html>
