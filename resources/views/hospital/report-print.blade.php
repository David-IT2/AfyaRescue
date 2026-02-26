<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>AfyaRescue Report – {{ date('Y-m-d') }}</title>
    <style>
        body { font-family: system-ui, sans-serif; font-size: 12px; color: #1e293b; padding: 1rem; }
        h1 { font-size: 1.25rem; margin-bottom: 0.5rem; }
        .meta { color: #64748b; margin-bottom: 1rem; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #e2e8f0; padding: 6px 8px; text-align: left; }
        th { background: #f1f5f9; font-weight: 600; }
        .stats { margin-bottom: 1rem; }
        .stats span { margin-right: 1rem; }
        @media print { body { padding: 0; } .no-print { display: none !important; } }
    </style>
</head>
<body>
    <div class="no-print" style="margin-bottom: 1rem;">
        <button type="button" onclick="window.print()" style="padding: 8px 16px; background: #dc2626; color: #fff; border: none; border-radius: 6px; cursor: pointer;">Print / Save as PDF</button>
        <a href="{{ route('hospital.dashboard') }}" style="margin-left: 8px; color: #64748b;">Back to dashboard</a>
    </div>
    <h1>AfyaRescue – Emergencies Report</h1>
    <p class="meta">Generated {{ now()->format('Y-m-d H:i') }}</p>
    @if(!empty($stats))
    <div class="stats">
        <span>Avg. assignment: {{ $stats['avg_assignment_min'] !== null ? $stats['avg_assignment_min'] . ' min' : '—' }}</span>
        <span>Avg. en route: {{ $stats['avg_enroute_min'] !== null ? $stats['avg_enroute_min'] . ' min' : '—' }}</span>
        @foreach($stats['by_severity'] ?? [] as $cat => $count)
            <span>{{ $cat }}: {{ $count }}</span>
        @endforeach
    </div>
    @endif
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Status</th>
                <th>Severity</th>
                <th>Patient</th>
                <th>Phone</th>
                <th>Hospital</th>
                <th>Address</th>
                <th>Requested</th>
                <th>Closed</th>
            </tr>
        </thead>
        <tbody>
            @foreach($emergencies as $e)
            <tr>
                <td>{{ $e->id }}</td>
                <td>{{ $e->status }}</td>
                <td>{{ $e->severity_category ?? $e->severity_label ?? '—' }}</td>
                <td>{{ $e->patient->name ?? '—' }}</td>
                <td>{{ $e->patient->phone ?? '—' }}</td>
                <td>{{ $e->hospital->name ?? '—' }}</td>
                <td>{{ $e->address_text ? \Illuminate\Support\Str::limit($e->address_text, 40) : '—' }}</td>
                <td>{{ $e->requested_at?->format('Y-m-d H:i') }}</td>
                <td>{{ $e->closed_at?->format('Y-m-d H:i') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
