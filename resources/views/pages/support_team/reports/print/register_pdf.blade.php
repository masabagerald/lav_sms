<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #222; }
        .head { text-align: center; border-bottom: 2px solid #16305c; padding-bottom: 6px; margin-bottom: 12px; }
        .head h2 { margin: 0; font-size: 17px; color: #16305c; text-transform: uppercase; }
        .meta { font-size: 10px; color: #555; margin-top: 3px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #bbb; padding: 4px 5px; text-align: left; }
        th { background: #eef2f8; font-size: 9px; text-transform: uppercase; letter-spacing: .04em; }
        tr:nth-child(even) td { background: #fafbfd; }
        .foot { margin-top: 14px; font-size: 9px; color: #666; display: flex; justify-content: space-between; }
    </style>
</head>
<body>
    <div class="head">
        <h2>{{ $school }}</h2>
        <div class="meta"><b>{{ $title }}</b> · {{ $period }}</div>
    </div>

    <table>
        <thead><tr>
            <th style="width:4%">#</th><th style="width:11%">Adm No</th><th style="width:22%">Student</th>
            <th style="width:7%">Gender</th><th style="width:9%">DOB</th><th style="width:13%">Class / Stream</th>
            <th style="width:18%">Guardian</th><th style="width:16%">Contact</th>
        </tr></thead>
        <tbody>
            @forelse($rows as $r)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $r['adm_no'] ?? $r->adm_no }}</td>
                    <td>{{ $r['student'] ?? $r->student }}</td>
                    <td>{{ ucfirst((string) ($r['gender'] ?? $r->gender)) ?: '—' }}</td>
                    <td>{{ ($r['dob'] ?? $r->dob) ?: '—' }}</td>
                    <td>{{ trim(($r['class_name'] ?? '') . ' ' . ($r['section_name'] ?? '')) ?: '—' }}</td>
                    <td>{{ ($r['guardian_name'] ?? $r->guardian_name) ?: '—' }}</td>
                    <td>{{ ($r['contact'] ?? $r->contact) ?: '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="8" style="text-align:center;">No students found.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="foot">
        <span>Generated {{ $generated }} by {{ $by }}</span>
        <span>Total students: {{ count($rows) }} · Page numbers handled by PDF viewer</span>
    </div>
</body>
</html>
