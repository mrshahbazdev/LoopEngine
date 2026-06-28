<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ __('app.run_summary') }} #{{ $run->id }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; margin: 20px; }
        h1 { color: #1f2937; font-size: 18px; border-bottom: 2px solid #4f46e5; padding-bottom: 8px; }
        h2 { color: #374151; font-size: 14px; margin-top: 20px; }
        .info-grid { display: flex; gap: 20px; margin: 12px 0; }
        .info-item { background: #f3f4f6; padding: 8px 12px; border-radius: 4px; }
        .info-label { font-size: 10px; color: #6b7280; text-transform: uppercase; }
        .info-value { font-size: 14px; font-weight: bold; color: #1f2937; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th { background: #4f46e5; color: white; padding: 8px 12px; text-align: left; font-size: 11px; }
        td { padding: 6px 12px; border-bottom: 1px solid #e5e7eb; font-size: 11px; }
        tr:nth-child(even) { background: #f9fafb; }
        .badge { display: inline-block; padding: 2px 8px; border-radius: 10px; font-size: 10px; font-weight: bold; }
        .badge-completed { background: #d1fae5; color: #065f46; }
        .badge-loop { background: #e0e7ff; color: #3730a3; }
        .footer { margin-top: 20px; font-size: 10px; color: #6b7280; border-top: 1px solid #e5e7eb; padding-top: 8px; }
    </style>
</head>
<body>
    <h1>{{ __('app.app_name') }} - {{ __('app.run_summary') }}</h1>

    <div class="info-grid">
        <div class="info-item">
            <div class="info-label">{{ __('app.process') }}</div>
            <div class="info-value">{{ $run->process->localizedName() }}</div>
        </div>
        <div class="info-item">
            <div class="info-label">{{ __('app.status') }}</div>
            <div class="info-value"><span class="badge badge-completed">{{ $run->status }}</span></div>
        </div>
        <div class="info-item">
            <div class="info-label">{{ __('app.loop_count') }}</div>
            <div class="info-value">{{ $run->loop_count }}</div>
        </div>
        <div class="info-item">
            <div class="info-label">{{ __('app.started_at') }}</div>
            <div class="info-value">{{ $run->started_at->format('Y-m-d H:i') }}</div>
        </div>
        @if($run->completed_at)
        <div class="info-item">
            <div class="info-label">{{ __('app.completed_at') }}</div>
            <div class="info-value">{{ $run->completed_at->format('Y-m-d H:i') }}</div>
        </div>
        @endif
    </div>

    <h2>{{ __('app.options') }} / Responses</h2>
    <table>
        <thead>
            <tr>
                <th>{{ __('app.step') }}</th>
                <th>{{ __('app.question') }}</th>
                <th>Answer</th>
                <th>{{ __('app.loop_iteration') }}</th>
                <th>{{ __('app.timestamp') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($run->responses->sortBy('created_at') as $response)
                <tr>
                    <td>{{ $response->step->order + 1 }}</td>
                    <td>{{ $response->step->localizedQuestion() }}</td>
                    <td>{{ $response->option ? $response->option->localizedLabel() : $response->response_text }}</td>
                    <td>
                        @if($response->loop_iteration > 1)
                            <span class="badge badge-loop">{{ $response->loop_iteration }}</span>
                        @else
                            {{ $response->loop_iteration }}
                        @endif
                    </td>
                    <td>{{ $response->responded_at->format('Y-m-d H:i') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <h2>{{ __('app.audit_trail') }}</h2>
    <table>
        <thead>
            <tr>
                <th>{{ __('app.timestamp') }}</th>
                <th>{{ __('app.performed_by') }}</th>
                <th>{{ __('app.actions') }}</th>
                <th>{{ __('app.details') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($run->logs->sortBy('created_at') as $log)
                <tr>
                    <td>{{ $log->created_at->format('Y-m-d H:i') }}</td>
                    <td>{{ $log->user->name }}</td>
                    <td>{{ $log->action }}</td>
                    <td>{{ $log->details ? implode(', ', array_map(fn($k, $v) => "$k: $v", array_keys($log->details), $log->details)) : '' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        {{ __('app.app_name') }} &mdash; Run #{{ $run->id }} &mdash; {{ now()->format('Y-m-d H:i:s') }}
    </div>
</body>
</html>
