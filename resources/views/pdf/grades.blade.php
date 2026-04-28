<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        @php
            $color = $themeColor ?? '#1e40af';
        @endphp

        @page { margin: 120px 30px 60px 30px; }
        * { box-sizing: border-box; }
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 10px; color: #1e293b; line-height: 1.5; }

        .page-header { position: fixed; top: -100px; left: 0; right: 0; height: 90px; text-align: center; border-bottom: 2px solid {{ $color }}; padding-bottom: 8px; }
        .page-header-logo { height: 45px; margin-bottom: 5px; }
        .page-header-name { font-size: 16px; font-weight: bold; color: #0f172a; text-transform: uppercase; letter-spacing: 1px; }

        .footer { position: fixed; bottom: -40px; left: 0; right: 0; text-align: center; font-size: 8px; color: #94a3b8; border-top: 1px solid #e2e8f0; padding-top: 5px; }

        .doc-title-block { text-align: center; margin-bottom: 20px; }
        .doc-title { font-size: 14px; font-weight: bold; color: {{ $color }}; text-transform: uppercase; }

        table.data-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table.data-table th { background-color: {{ $color }}; color: #ffffff; padding: 8px; text-align: left; text-transform: uppercase; font-size: 8px; }
        table.data-table td { border: 1px solid #e2e8f0; padding: 8px; vertical-align: middle; }
        table.data-table tr:nth-child(even) td { background-color: #f8fafc; }
    </style>
</head>
<body>
    <div class="page-header">
        @if($school?->logo)
            <img class="page-header-logo" src="{{ storage_path('app/public/' . $school->logo) }}" />
        @endif
        <div class="page-header-name">{{ $school?->name ?? 'CBC School System' }}</div>
    </div>

    <div class="footer">Generated on {{ date('d M Y, H:i') }} &bull; Grade Levels Registry</div>

    <div class="doc-title-block">
        <div class="doc-title">Grade Levels Directory</div>
        <div style="font-size: 9px; color: #64748b; margin-top: 5px;">Total Records: {{ $items->count() }}</div>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th width="15%">Level Order</th>
                <th width="25%">Grade Name</th>
                <th width="15%">Grade Code</th>
                <th width="20%">Category</th>
                <th width="15%">Classes</th>
                <th width="10%">Learners</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $item)
            <tr>
                <td style="text-align: center;">{{ $item->level_order }}</td>
                <td style="font-weight: bold;">{{ $item->name }}</td>
                <td>{{ $item->code }}</td>
                <td>{{ $item->category }}</td>
                <td>{{ $item->classes_count }} Classes</td>
                <td>{{ $item->learners_count }} Students</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
