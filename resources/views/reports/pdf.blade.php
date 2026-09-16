<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ isset($person) ? 'Performance Report - '.$person->name : 'Ticket Report' }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 10px; color: #1e293b; background: #fff; }
        .header { background: #1e40af; color: #fff; padding: 18px 20px; margin-bottom: 16px; }
        .header h1 { font-size: 16px; font-weight: bold; letter-spacing: 0.5px; }
        .header p { font-size: 9px; margin-top: 4px; opacity: 0.85; }
        .meta { padding: 0 20px 12px; font-size: 9px; color: #64748b; border-bottom: 1px solid #e2e8f0; margin-bottom: 12px; }
        .meta span { margin-right: 16px; }
        .stats-grid { display: table; width: calc(100% - 40px); margin: 0 20px 16px; border-collapse: separate; border-spacing: 6px; }
        .stat-cell { display: table-cell; text-align: center; background: #f8fafc; border: 1px solid #e2e8f0; padding: 10px 4px; border-radius: 6px; width: 12.5%; }
        .stat-cell .num { font-size: 16px; font-weight: bold; color: #1e40af; }
        .stat-cell .lbl { font-size: 7.5px; color: #64748b; text-transform: uppercase; letter-spacing: 0.4px; margin-top: 2px; font-weight: bold; }
        table.tickets { width: calc(100% - 40px); margin: 0 20px; border-collapse: collapse; }
        table.tickets th { background: #1e293b; color: #fff; padding: 6px 8px; text-align: left; font-size: 8px; text-transform: uppercase; letter-spacing: 0.4px; }
        table.tickets td { padding: 5px 8px; border-bottom: 1px solid #f1f5f9; font-size: 9px; vertical-align: top; }
        table.tickets tr:nth-child(even) td { background: #f8fafc; }
        .badge { display: inline-block; padding: 1px 6px; border-radius: 20px; font-size: 8px; font-weight: bold; }
        .badge-open { background: #fef3c7; color: #92400e; }
        .badge-in_progress { background: #dbeafe; color: #1e40af; }
        .badge-resolved { background: #d1fae5; color: #065f46; }
        .badge-closed { background: #f1f5f9; color: #475569; }
        .badge-low { background: #f1f5f9; color: #475569; }
        .badge-medium { background: #fef3c7; color: #92400e; }
        .badge-high { background: #ffedd5; color: #9a3412; }
        .badge-critical { background: #fee2e2; color: #991b1b; }
        .footer { margin-top: 20px; padding: 8px 20px; border-top: 1px solid #e2e8f0; font-size: 8px; color: #94a3b8; text-align: center; }
        .person-card { margin: 0 20px 14px; padding: 12px 16px; background: #f1f5f9; border-radius: 8px; border: 1px solid #cbd5e1; }
        .person-card h2 { font-size: 14px; color: #0f172a; font-weight: bold; }
        .person-card p { font-size: 9px; color: #475569; margin-top: 2px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>VISION Technologies Limited &mdash; {{ isset($person) ? 'Single Person Performance Report' : 'Ticket Report' }}</h1>
        <p>Generated: {{ now()->format('d M Y, H:i') }} &nbsp;&bull;&nbsp; Filters: {{ $filters }}</p>
    </div>

    @if(isset($person))
    <div class="person-card">
        <h2>{{ $person->name }} <span style="font-size:10px;color:#4f46e5;">({{ strtoupper(str_replace('_',' ',$person->role)) }})</span></h2>
        <p>Email: {{ $person->email }} &nbsp;&bull;&nbsp; Phone: {{ $person->phone ?? 'N/A' }} &nbsp;&bull;&nbsp; Resolution Rate: <strong>{{ $personStats['rate'] ?? 0 }}%</strong> &nbsp;&bull;&nbsp; Avg Time: <strong>{{ $personStats['avg_resolution_time'] ?? 'N/A' }}</strong></p>
    </div>

    <div class="stats-grid">
        <div class="stat-cell"><div class="num" style="color:#8b5cf6;">{{ $personStats['created'] ?? 0 }}</div><div class="lbl">Created</div></div>
        <div class="stat-cell"><div class="num" style="color:#6366f1;">{{ $personStats['assigned'] ?? 0 }}</div><div class="lbl">Assigned</div></div>
        <div class="stat-cell"><div class="num" style="color:#ec4899;">{{ $personStats['transferred'] ?? 0 }}</div><div class="lbl">Transferred</div></div>
        <div class="stat-cell"><div class="num" style="color:#0ea5e9;">{{ $personStats['comments'] ?? 0 }}</div><div class="lbl">Comments</div></div>
        <div class="stat-cell"><div class="num" style="color:#f59e0b;">{{ $personStats['reactions'] ?? 0 }}</div><div class="lbl">Reactions</div></div>
        <div class="stat-cell"><div class="num" style="color:#10b981;">{{ $personStats['resolved'] ?? 0 }}</div><div class="lbl">Resolved</div></div>
        <div class="stat-cell"><div class="num" style="color:#3b82f6;">{{ $personStats['in_progress'] ?? 0 }}</div><div class="lbl">In Progress</div></div>
        <div class="stat-cell"><div class="num" style="color:#f97316;">{{ $personStats['pending'] ?? 0 }}</div><div class="lbl">Pending</div></div>
    </div>
    @else
    <div class="meta">
        <span>Total: <strong>{{ $stats['total'] }}</strong></span>
        <span>Open: <strong>{{ $stats['open'] }}</strong></span>
        <span>In Progress: <strong>{{ $stats['in_progress'] }}</strong></span>
        <span>Resolved: <strong>{{ $stats['resolved'] }}</strong></span>
        <span>Closed: <strong>{{ $stats['closed'] }}</strong></span>
    </div>
    @endif

    <table class="tickets">
        <thead>
            <tr>
                <th>#ID</th>
                <th>Title</th>
                <th>Category</th>
                <th>Priority</th>
                <th>Status</th>
                <th>Created By</th>
                <th>Assigned To</th>
                <th>Created At</th>
                <th>Resolved At</th>
            </tr>
        </thead>
        <tbody>
            @forelse($tickets as $ticket)
            <tr>
                <td>#{{ $ticket->id }}</td>
                <td>{{ $ticket->title }}</td>
                <td>{{ ucfirst(str_replace('_', ' ', $ticket->category)) }}</td>
                <td><span class="badge badge-{{ $ticket->priority }}">{{ ucfirst($ticket->priority) }}</span></td>
                <td><span class="badge badge-{{ $ticket->status }}">{{ ucfirst(str_replace('_', ' ', $ticket->status)) }}</span></td>
                <td>{{ $ticket->creator->name ?? 'N/A' }}</td>
                <td>{{ $ticket->assignee->name ?? '—' }}</td>
                <td>{{ $ticket->created_at->format('d M Y') }}</td>
                <td>{{ $ticket->status === 'resolved' ? $ticket->updated_at->format('d M Y') : '—' }}</td>
            </tr>
            @empty
            <tr><td colspan="9" style="text-align:center;padding:20px;color:#94a3b8;">No tickets found for this period.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">VISION Technologies Limited &bull; Confidential &bull; {{ now()->format('Y') }}</div>
</body>
</html>
