<?php

namespace App\Http\Controllers;

use App\Models\Webhook;
use App\Models\WebhookLog;
use Illuminate\Http\Request;

class WebhookController extends Controller
{
    public function index(Request $request)
    {
        $webhooks = Webhook::where('created_by', $request->user()->id)
            ->withCount('logs')
            ->latest()
            ->get();

        return view('webhooks.index', compact('webhooks'));
    }

    public function create()
    {
        $availableEvents = [
            'run.started' => __('app.webhook_event_run_started'),
            'run.completed' => __('app.webhook_event_run_completed'),
            'run.paused' => __('app.webhook_event_run_paused'),
            'run.cancelled' => __('app.webhook_event_run_cancelled'),
            'run.looped_back' => __('app.webhook_event_run_looped'),
            'process.activated' => __('app.webhook_event_process_activated'),
            'process.archived' => __('app.webhook_event_process_archived'),
            'assignment.created' => __('app.webhook_event_assignment_created'),
        ];

        return view('webhooks.create', compact('availableEvents'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'url' => ['required', 'url', 'max:2048'],
            'secret' => ['nullable', 'string', 'max:255'],
            'events' => ['required', 'array', 'min:1'],
            'events.*' => ['string'],
        ]);

        Webhook::create([
            ...$validated,
            'created_by' => $request->user()->id,
        ]);

        return redirect()->route('webhooks.index')->with('success', __('app.webhook_created'));
    }

    public function edit(Webhook $webhook)
    {
        $this->authorizeWebhook($webhook);

        $availableEvents = [
            'run.started' => __('app.webhook_event_run_started'),
            'run.completed' => __('app.webhook_event_run_completed'),
            'run.paused' => __('app.webhook_event_run_paused'),
            'run.cancelled' => __('app.webhook_event_run_cancelled'),
            'run.looped_back' => __('app.webhook_event_run_looped'),
            'process.activated' => __('app.webhook_event_process_activated'),
            'process.archived' => __('app.webhook_event_process_archived'),
            'assignment.created' => __('app.webhook_event_assignment_created'),
        ];

        return view('webhooks.edit', compact('webhook', 'availableEvents'));
    }

    public function update(Request $request, Webhook $webhook)
    {
        $this->authorizeWebhook($webhook);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'url' => ['required', 'url', 'max:2048'],
            'secret' => ['nullable', 'string', 'max:255'],
            'events' => ['required', 'array', 'min:1'],
            'events.*' => ['string'],
            'is_active' => ['boolean'],
        ]);

        $webhook->update($validated);

        return redirect()->route('webhooks.index')->with('success', __('app.webhook_updated'));
    }

    public function destroy(Webhook $webhook)
    {
        $this->authorizeWebhook($webhook);
        $webhook->delete();

        return redirect()->route('webhooks.index')->with('success', __('app.webhook_deleted'));
    }

    public function logs(Webhook $webhook)
    {
        $this->authorizeWebhook($webhook);

        $logs = $webhook->logs()->latest()->paginate(25);

        return view('webhooks.logs', compact('webhook', 'logs'));
    }

    public function toggle(Webhook $webhook)
    {
        $this->authorizeWebhook($webhook);
        $webhook->update(['is_active' => !$webhook->is_active]);

        return back()->with('success', $webhook->is_active ? __('app.webhook_enabled') : __('app.webhook_disabled'));
    }

    protected function authorizeWebhook(Webhook $webhook): void
    {
        if ($webhook->created_by !== request()->user()->id && !request()->user()->isAdmin()) {
            abort(403);
        }
    }
}
