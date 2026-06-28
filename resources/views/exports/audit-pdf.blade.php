<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ __('app.audit_log') }} - {{ now()->format('Y-m-d') }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; margin: 20px; }
        h1 { color: #1f2937; font-size: 18px; border-bottom: 2px solid #4f46e5; padding-bottom: 8px; }
        table { width: 100%; border-collapse: collapse; margin-top: 16px; }
        th { background: #4f46e5; color: white; padding: 8px 12px; text-align: left; font-size: 11px; }
        td { padding: 6px 12px; border-bottom: 1px solid #e5e7eb; font-size: 11px; }
        tr:nth-child(even) { background: #f9fafb; }
        .footer { margin-top: 20px; font-size: 10px; color: #6b7280; border-top: 1px solid #e5e7eb; padding-top: 8px; }
    </style>
</head>
<body>
    <h1>{{ __('app.app_name') }} - {{ __('app.audit_log') }}</h1>
    <p>{{ __('app.export') }}: {{ now()->format('Y-m-d H:i:s') }}</p>

    <table>
        <thead>
            <tr>
                <th>{{ __('app.timestamp') }}</th>
                <th>{{ __('app.performed_by') }}</th>
                <th>{{ __('app.process') }}</th>
                <th>{{ __('app.actions') }}</th>
                <th>{{ __('app.details') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($logs as $log)
                <tr>
                    <td>{{ $log->created_at->format('Y-m-d H:i') }}</td>
                    <td>{{ $log->user->name }}</td>
                    <td>{{ $log->run?->process?->localizedName() ?? '-' }}</td>
                    <td>{{ $log->action }}</td>
                    <td>{{ $log->details ? implode(', ', array_map(fn($k, $v) => "$k: $v", array_keys($log->details), $log->details)) : '' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        {{ __('app.app_name') }} &mdash; {{ __('app.audit_trail') }} &mdash; {{ now()->format('Y-m-d H:i:s') }}
    </div>
</body>
</html>
