<?php

namespace App\Http\Controllers;

use App\Models\ProcessRun;
use App\Models\RunLog;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ExportController extends Controller
{
    public function auditCsv(Request $request): Response
    {
        $query = RunLog::with(['run.process', 'user'])->latest();

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        $logs = $query->get();

        $csv = $this->generateCsv([
            __('app.timestamp'),
            __('app.performed_by'),
            __('app.process'),
            __('app.actions'),
            __('app.details'),
        ], $logs->map(function ($log) {
            return [
                $log->created_at->format('Y-m-d H:i:s'),
                $log->user->name,
                $log->run?->process?->localizedName() ?? '-',
                $log->action,
                $log->details ? json_encode($log->details) : '',
            ];
        })->toArray());

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="audit-log-' . now()->format('Y-m-d') . '.csv"',
        ]);
    }

    public function runSummaryCsv(ProcessRun $run): Response
    {
        $run->load(['process', 'responses.step', 'responses.option', 'logs.user']);

        $csv = $this->generateCsv([
            __('app.step'),
            __('app.question'),
            'Answer',
            __('app.loop_iteration'),
            __('app.timestamp'),
        ], $run->responses->sortBy('created_at')->map(function ($response) {
            return [
                $response->step->order + 1,
                $response->step->localizedQuestion(),
                $response->option ? $response->option->localizedLabel() : $response->response_text,
                $response->loop_iteration,
                $response->responded_at->format('Y-m-d H:i:s'),
            ];
        })->toArray());

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="run-' . $run->id . '-summary.csv"',
        ]);
    }

    public function auditPdf(Request $request)
    {
        $query = RunLog::with(['run.process', 'user'])->latest();

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        $logs = $query->limit(500)->get();

        $html = view('exports.audit-pdf', compact('logs'))->render();

        return response($html, 200, [
            'Content-Type' => 'text/html',
            'Content-Disposition' => 'attachment; filename="audit-log-' . now()->format('Y-m-d') . '.html"',
        ]);
    }

    public function runSummaryPdf(ProcessRun $run)
    {
        $run->load(['process', 'starter', 'responses.step', 'responses.option', 'logs.user']);

        $html = view('exports.run-summary-pdf', compact('run'))->render();

        return response($html, 200, [
            'Content-Type' => 'text/html',
            'Content-Disposition' => 'attachment; filename="run-' . $run->id . '-summary.html"',
        ]);
    }

    protected function generateCsv(array $headers, array $rows): string
    {
        $output = fopen('php://temp', 'r+');
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF)); // UTF-8 BOM

        fputcsv($output, $headers);
        foreach ($rows as $row) {
            fputcsv($output, $row);
        }

        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return $csv;
    }
}
