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

        @page { margin: 120px 30px 60px 30px; }
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
        .page-header-logo { height: 45px; margin-bottom: 5px; }
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

        .doc-title-block { text-align: center; margin-bottom: 20px; }
        .doc-title { font-size: 14px; font-weight: bold; color: {{ $color }}; text-transform: uppercase; }

        .summary-banner {
            background-color: {{ $lightBg }};
            border: 1px solid {{ $color }}20;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
            text-align: center;
        }
        .summary-val { font-size: 18px; font-weight: bold; color: {{ $color }}; }
        .summary-label { font-size: 8px; color: #64748b; text-transform: uppercase; letter-spacing: 1px; }

        table.data-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table.data-table th {
            background-color: {{ $color }};
            color: #ffffff;
            padding: 10px 15px;
            text-align: left;
            text-transform: uppercase;
            font-size: 8px;
            letter-spacing: 0.5px;
        }
        table.data-table td { border: 1px solid #e2e8f0; padding: 12px 15px; vertical-align: middle; }
        table.data-table tr:nth-child(even) td { background-color: #f8fafc; }
        
        .role-indicator {
            display: inline-block;
            width: 8px;
            height: 8px;
            border-radius: 2px;
            background-color: {{ $color }};
            margin-right: 8px;
        }
        .count-badge {
            background-color: {{ $color }}15;
            color: {{ $color }};
            padding: 2px 8px;
            border-radius: 4px;
            font-weight: bold;
            font-size: 9px;
            border: 0.5px solid {{ $color }}30;
        }
    </style>
</head>
<body>
    <div class="page-header">
        @if($school?->logo)
            <img class="page-header-logo" src="{{ storage_path('app/public/' . $school->logo) }}" />
        @endif
        <div class="page-header-name">{{ $school?->name ?? 'Institutional Portal' }}</div>
    </div>

    <div class="footer">Generated on {{ date('d M Y, H:i') }} &bull; Institutional Human Capital Summary &bull; Confidential</div>

    <div class="doc-title-block">
        <div class="doc-title">Staffing Structure & Distribution Report</div>
        <div style="font-size: 9px; color: #64748b; margin-top: 5px;">Functional Group Analysis & Operational Capacity</div>
    </div>

    <div class="summary-banner">
        <div class="summary-label">Total Institutional Personnel</div>
        <div class="summary-val">{{ number_format($totalStaff) }} Registered Members</div>
        <div style="font-size: 8px; color: #64748b; margin-top: 4px;">Synchronized across {{ count($roles) }} functional categories</div>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th width="50%">Operational Functional Group</th>
                <th width="25%" style="text-align: center;">Personnel Count</th>
                <th width="25%" style="text-align: center;">% Composition</th>
            </tr>
        </thead>
        <tbody>
            @foreach($roles as $role)
            <tr>
                <td style="font-weight: bold; color: #0f172a;">
                    <div class="role-indicator"></div>
                    {{ $role['display_name'] }}
                </td>
                <td style="text-align: center;">
                    <span class="count-badge">{{ $role['count'] }}</span>
                </td>
                <td style="text-align: center; color: #64748b; font-weight: bold;">
                    {{ $totalStaff > 0 ? round(($role['count'] / $totalStaff) * 100, 1) : 0 }}%
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div style="margin-top: 30px; padding: 15px; background-color: #f8fafc; border-radius: 8px; border: 1px solid #e2e8f0;">
        <div style="font-size: 8px; font-weight: bold; color: #64748b; text-transform: uppercase; margin-bottom: 8px;">Authenticity Statement</div>
        <div style="font-size: 9px; color: #475569; line-height: 1.6;">
            This report provides a high-level overview of the human resource distribution within the institution. 
            Functional groups are mapped to specific system permissions and academic responsibilities. 
            This structural data is generated in real-time from the institutional registry.
        </div>
    </div>
</body>
</html>
