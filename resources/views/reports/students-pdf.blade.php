<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Students List</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 12px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #4a90e2;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            color: #4a90e2;
            font-size: 24px;
        }
        .header p {
            margin: 5px 0 0;
            color: #777;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            text-align: left;
            padding: 10px;
            border-bottom: 1px solid #eee;
        }
        th {
            background-color: #f8f9fa;
            color: #4a90e2;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 10px;
        }
        .status-badge {
            padding: 3px 8px;
            border-radius: 10px;
            font-size: 10px;
            display: inline-block;
        }
        .status-active { background-color: #e6f4ea; color: #1e7e34; }
        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: center;
            font-size: 10px;
            color: #777;
            border-top: 1px solid #eee;
            padding-top: 5px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Students List</h1>
        <p>Generated on {{ now()->format('F j, Y, g:i a') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Adm. No</th>
                <th>Name</th>
                <th>Gender</th>
                <th>Class</th>
                <th>Age</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($students as $student)
                <tr>
                    <td>{{ $student->admission_number }}</td>
                    <td>{{ $student->full_name }}</td>
                    <td>{{ ucfirst($student->gender) }}</td>
                    <td>{{ $student->currentClass?->name ?? 'N/A' }}</td>
                    <td>{{ $student->age }}</td>
                    <td>
                        <span class="status-badge {{ $student->status === 'active' ? 'status-active' : '' }}">
                            {{ ucfirst($student->status) }}
                        </span>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        &copy; {{ date('Y') }} CBC Management System. Page <span class="pagenum"></span>
    </div>
</body>
</html>
