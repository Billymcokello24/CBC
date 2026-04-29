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
            <img class="page-header-logo" src="{{ public_path('storage/' . $school->logo) }}" />
        @endif
        <div class="page-header-name">{{ $school?->name ?? 'CBC School System' }}</div>
    </div>

    <div class="footer">
        Generated on {{ date('d M Y, H:i') }} &bull; Institutional Financial Report
    </div>

    <div class="doc-title-block">
        <div class="doc-title">Fee Collection Summary</div>
        <div style="font-size: 9px; color: #64748b; margin-top: 5px;">
            Institutional Billing & Payments Ledger
        </div>
    </div>

    <div class="summary-box">
        <p><strong>Total Billed:</strong> Kes {{ number_format($totalBilled, 2) }} &nbsp;&nbsp;|&nbsp;&nbsp; <strong>Total Collected:</strong> Kes {{ number_format($totalPaid, 2) }} &nbsp;&nbsp;|&nbsp;&nbsp; <strong>Balance:</strong> Kes {{ number_format(max(0, $totalBilled - $totalPaid), 2) }}</p>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th width="30%">Student Name</th>
                <th width="15%">Admission #</th>
                <th width="15%" style="text-align: right;">Billed (Kes)</th>
                <th width="15%" style="text-align: right;">Paid (Kes)</th>
                <th width="15%" style="text-align: right;">Balance (Kes)</th>
                <th width="10%" style="text-align: right;">Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($records as $r)
                <tr>
                    <td style="font-weight: bold;">{{ $r->student }}</td>
                    <td>{{ $r->adm }}</td>
                    <td align="right">{{ number_format($r->billed, 2) }}</td>
                    <td align="right" style="color: #166534;">{{ number_format($r->paid, 2) }}</td>
                    <td align="right" style="color: #991b1b;">{{ number_format($r->balance, 2) }}</td>
                    <td align="right"><span class="badge {{ $r->status === 'Cleared' ? 'status-cleared' : 'status-pending' }}">{{ strtoupper($r->status) }}</span></td>
                </tr>
            @endforeach
            @if(empty($records))
                <tr>
                    <td colspan="6" align="center" style="padding: 20px;">No financial records found.</td>
                </tr>
            @endif
        </tbody>
    </table>
</body>
</html>
