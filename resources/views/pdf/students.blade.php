<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Student Enrollment Registry</title>
    <style>
        @page {
            margin: 0cm 0cm;
        }
        body {
            font-family: 'Helvetica', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #ffffff;
            color: #1e293b;
            line-height: 1.5;
        }
        .top-accent {
            height: 8px;
            background-color: {{ $themeColor ?? '#1e40af' }};
            width: 100%;
        }
        .container {
            padding: 1cm 1.2cm;
        }
        .header {
            margin-bottom: 30px;
            border-bottom: 1px solid #f1f5f9;
            padding-bottom: 20px;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
        }
        .logo-box {
            width: 60px;
            height: 60px;
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            text-align: center;
            vertical-align: middle;
        }
        .logo-img {
            max-width: 45px;
            max-height: 45px;
            display: block;
            margin: 0 auto;
        }
        .institution-details {
            padding-left: 15px;
            vertical-align: middle;
        }
        .institution-name {
            font-size: 18px;
            font-weight: 900;
            color: #0f172a;
            text-transform: uppercase;
        }
        .report-meta {
            font-size: 10px;
            color: #64748b;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .doc-title-section {
            margin-bottom: 25px;
        }
        .doc-title {
            font-size: 16px;
            font-weight: 800;
            color: {{ $themeColor ?? '#1e40af' }};
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .doc-subtitle {
            font-size: 10px;
            color: #94a3b8;
            font-weight: 600;
        }
        table.data-table {
            width: 100%;
            border-collapse: collapse;
        }
        table.data-table th {
            background-color: #f8fafc;
            color: #64748b;
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            padding: 12px 10px;
            border-bottom: 2px solid #e2e8f0;
            text-align: left;
        }
        table.data-table td {
            padding: 12px 10px;
            font-size: 10px;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
        }
        .status-pill {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 9999px;
            font-size: 8px;
            font-weight: 800;
            text-transform: uppercase;
        }
        .status-active { background-color: #dcfce7; color: #166534; }
        .status-inactive { background-color: #fee2e2; color: #991b1b; }
        .footer {
            position: fixed;
            bottom: 0cm;
            left: 0cm;
            right: 0cm;
            height: 1.5cm;
            padding: 0.4cm 1.2cm;
            font-size: 8px;
            color: #94a3b8;
            border-top: 1px solid #f1f5f9;
        }
        .footer-table {
            width: 100%;
        }
    </style>
</head>
<body>
    <div class="top-accent"></div>
    <div class="container">
        <div class="header">
            <table class="header-table">
                <tr>
                    <td class="logo-box">
                        @if($school && $school->logo)
                            <img src="{{ public_path('storage/' . $school->logo) }}" class="logo-img">
                        @else
                            <div style="font-size: 20px; font-weight: 900; color: {{ $themeColor ?? '#1e40af' }}; opacity: 0.3;">
                                {{ substr($school->name ?? 'S', 0, 1) }}
                            </div>
                        @endif
                    </td>
                    <td class="institution-details">
                        <div class="institution-name">{{ $school->name ?? 'Operational Portal' }}</div>
                        <div class="report-meta">Institutional Enrollment Matrix</div>
                    </td>
                    <td style="text-align: right; vertical-align: middle;">
                        <div style="font-size: 14px; font-weight: 800; color: #1e293b;">{{ date('d-m-Y') }}</div>
                        <div style="font-size: 8px; color: #94a3b8; text-transform: uppercase;">Doc Ref: {{ strtoupper(uniqid('LRN-')) }}</div>
                    </td>
                </tr>
            </table>
        </div>

        <div class="doc-title-section">
            <div class="doc-title">Learner Enrollment Registry</div>
            <div class="doc-subtitle">Official census of enrolled learners across all academic levels. Total Records: {{ $items->count() }}</div>
        </div>

        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 15%;">Reg / UPI #</th>
                    <th style="width: 30%;">Learner Full Name</th>
                    <th style="width: 15%;">Grade Level</th>
                    <th style="width: 12%;">Stream</th>
                    <th style="width: 13%;">Gender</th>
                    <th style="width: 15%; text-align: center;">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $item)
                    <tr>
                        <td style="font-weight: 700;">{{ $item->upi_number ?? $item->admission_number }}</td>
                        <td style="font-weight: 600;">{{ $item->first_name }} {{ $item->middle_name }} {{ $item->last_name }}</td>
                        <td>{{ $item->currentClass?->gradeLevel?->name ?? '—' }}</td>
                        <td>{{ $item->currentClass?->stream?->name ?? '—' }}</td>
                        <td style="text-transform: capitalize;">{{ $item->gender }}</td>
                        <td style="text-align: center;">
                            <span class="status-pill {{ $item->status === 'active' ? 'status-active' : 'status-inactive' }}">
                                {{ $item->status }}
                            </span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="footer">
        <table class="footer-table">
            <tr>
                <td>&copy; {{ date('Y') }} {{ $school->name ?? 'Learning Portal' }}. Official Educational Record.</td>
                <td style="text-align: right;">Generated on {{ date('M d, Y H:i') }} • Page 1</td>
            </tr>
        </table>
    </div>
</body>
</html>
