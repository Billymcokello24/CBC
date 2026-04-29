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

        .badge {
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .b-ee { background-color: #dcfce7; color: #166534; }
        .b-me { background-color: #dbeafe; color: #1e40af; }
        .b-ae { background-color: #fef08a; color: #854d0e; }
        .b-be { background-color: #fee2e2; color: #991b1b; }
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
        Generated on {{ date('d M Y, H:i') }} &bull; Competency Mastery Report
    </div>

    <div class="doc-title-block">
        <div class="doc-title">Competency Mastery Analysis</div>
        <div style="font-size: 9px; color: #64748b; margin-top: 5px;">
            Institutional Wide Competency Ratings | Assessed Traits: {{ count($competencyData) }}
        </div>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th width="30%">Competency Name</th>
                <th width="20%">Subject Area</th>
                <th width="10%" style="text-align: center;">EE</th>
                <th width="10%" style="text-align: center;">ME</th>
                <th width="10%" style="text-align: center;">AE</th>
                <th width="10%" style="text-align: center;">BE</th>
                <th width="10%" style="text-align: center;">Total Evaluated</th>
            </tr>
        </thead>
        <tbody>
            @foreach($competencyData as $c)
                <tr>
                    <td style="font-weight: bold;">{{ $c->competency }}</td>
                    <td>{{ $c->subject }}</td>
                    <td align="center"><span class="badge b-ee">{{ $c->ee }}</span></td>
                    <td align="center"><span class="badge b-me">{{ $c->me }}</span></td>
                    <td align="center"><span class="badge b-ae">{{ $c->ae }}</span></td>
                    <td align="center"><span class="badge b-be">{{ $c->be }}</span></td>
                    <td align="center" style="font-weight: bold;">{{ $c->total }}</td>
                </tr>
            @endforeach
            @if(empty($competencyData))
                <tr>
                    <td colspan="7" align="center" style="padding: 20px;">No competency data found.</td>
                </tr>
            @endif
        </tbody>
    </table>
</body>
</html>
