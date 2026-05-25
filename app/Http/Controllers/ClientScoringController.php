<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Services\ClientScoringService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class ClientScoringController extends Controller
{
    public function index(Request $request, ClientScoringService $scoringService)
    {
        $organizationId = auth()->user()->organization_id;
        if (!$organizationId) {
            return redirect()->route('dashboard')->with('error', 'You must be assigned to an organization to access scoring.');
        }

        $clients = Client::query()
            ->where('organization_id', $organizationId)
            ->where('status', '!=', 'disabled')
            ->orderBy('first_name')
            ->get(['id', 'client_number', 'first_name', 'last_name', 'middle_name', 'business_name', 'client_type', 'phone_number']);

        $report = null;
        $selectedClient = null;

        if ($request->filled('client_id')) {
            $selectedClient = Client::query()
                ->where('id', $request->client_id)
                ->where('organization_id', $organizationId)
                ->firstOrFail();

            $report = $scoringService->report($selectedClient);
        }

        return view('scoring.index', compact('clients', 'report', 'selectedClient'));
    }

    public function pdf(Client $client, ClientScoringService $scoringService)
    {
        $organizationId = auth()->user()->organization_id;
        if (!$organizationId || $client->organization_id !== $organizationId) {
            abort(403, 'Client does not belong to your organization.');
        }

        $report = $scoringService->report($client);

        $organization = auth()->user()->organization;

        $pdf = Pdf::loadView('scoring.pdf', [
            'report' => $report,
            'client' => $client,
            'organization' => $organization,
        ])->setPaper('a4', 'portrait');

        $filename = 'credit-score-' . ($client->client_number ?? $client->id) . '-' . now()->format('Y-m-d') . '.pdf';

        return $pdf->download($filename);
    }
}
