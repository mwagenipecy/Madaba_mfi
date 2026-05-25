<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Client;
use App\Models\Collateral;
use App\Models\Loan;
use App\Services\ClientScoringService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class CollateralsController extends Controller
{
    private function organizationId(): int
    {
        $id = auth()->user()->organization_id;
        abort_unless($id, 403, 'You must be assigned to an organization.');

        return (int) $id;
    }

    public function index(Request $request)
    {
        $organizationId = $this->organizationId();

        $query = Collateral::query()
            ->with(['client', 'loan', 'branch'])
            ->where('organization_id', $organizationId)
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('client_id')) {
            $query->where('client_id', $request->client_id);
        }

        $collaterals = $query->paginate(20)->withQueryString();

        $stats = [
            'total' => Collateral::where('organization_id', $organizationId)->count(),
            'available' => Collateral::where('organization_id', $organizationId)->where('status', 'available')->count(),
            'pledged' => Collateral::where('organization_id', $organizationId)->where('status', 'pledged')->count(),
            'total_value' => Collateral::where('organization_id', $organizationId)->sum('estimated_value'),
        ];

        $clients = Client::where('organization_id', $organizationId)
            ->enabled()
            ->orderBy('first_name')
            ->get(['id', 'first_name', 'last_name', 'business_name', 'client_type']);

        return view('collaterals.index', compact('collaterals', 'stats', 'clients'));
    }

    public function create()
    {
        $organizationId = $this->organizationId();

        $clients = Client::where('organization_id', $organizationId)
            ->enabled()
            ->orderBy('first_name')
            ->get();

        $branches = Branch::where('organization_id', $organizationId)
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        return view('collaterals.create', compact('clients', 'branches'));
    }

    public function store(Request $request)
    {
        $organizationId = $this->organizationId();

        $validated = $request->validate([
            'client_id' => ['required', Rule::exists('clients', 'id')->where('organization_id', $organizationId)],
            'branch_id' => ['nullable', Rule::exists('branches', 'id')->where('organization_id', $organizationId)],
            'loan_id' => ['nullable', Rule::exists('loans', 'id')->where('organization_id', $organizationId)],
            'type' => ['required', Rule::in(array_keys(Collateral::TYPES))],
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'estimated_value' => 'required|numeric|min:0.01',
            'lending_ratio' => 'nullable|numeric|min:1|max:100',
            'location' => 'nullable|string|max:255',
            'identifier' => 'nullable|string|max:255',
            'document' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        $path = null;
        if ($request->hasFile('document')) {
            $path = $request->file('document')->store('collaterals', 'public');
        }

        $collateral = Collateral::create([
            'organization_id' => $organizationId,
            'branch_id' => $validated['branch_id'] ?? null,
            'client_id' => $validated['client_id'],
            'created_by' => auth()->id(),
            'type' => $validated['type'],
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'estimated_value' => $validated['estimated_value'],
            'lending_ratio' => $validated['lending_ratio'] ?? 70,
            'location' => $validated['location'] ?? null,
            'identifier' => $validated['identifier'] ?? null,
            'document_path' => $path,
            'status' => 'available',
        ]);

        if ($request->boolean('attach_to_loan') && $request->filled('loan_id')) {
            $loan = Loan::query()
                ->with(['client', 'loanProduct'])
                ->where('organization_id', $organizationId)
                ->where('id', $request->loan_id)
                ->first();

            if (!$loan) {
                return redirect()->back()
                    ->withErrors(['loan_id' => 'Selected loan was not found.'])
                    ->withInput();
            }

            try {
                $this->attachCollateralToLoan($collateral, $loan);
            } catch (\InvalidArgumentException $e) {
                $collateral->delete();

                return redirect()->back()
                    ->withErrors(['loan_id' => $e->getMessage()])
                    ->withInput();
            }

            return redirect()->route('loans.show', $loan)
                ->with('success', 'Collateral registered and attached to loan ' . $loan->loan_number . '.');
        }

        return redirect()->route('collaterals.show', $collateral)
            ->with('success', 'Collateral registered successfully.');
    }

    public function show(Collateral $collateral)
    {
        $this->authorizeCollateral($collateral);

        $collateral->load(['client', 'loan', 'branch', 'creator']);

        return view('collaterals.show', compact('collateral'));
    }

    public function edit(Collateral $collateral)
    {
        $this->authorizeCollateral($collateral);

        if ($collateral->status === 'pledged') {
            return redirect()->route('collaterals.show', $collateral)
                ->with('error', 'Pledged collateral cannot be edited. Release it from the loan first.');
        }

        $organizationId = $this->organizationId();

        $clients = Client::where('organization_id', $organizationId)->enabled()->orderBy('first_name')->get();
        $branches = Branch::where('organization_id', $organizationId)->where('status', 'active')->orderBy('name')->get();

        return view('collaterals.edit', compact('collateral', 'clients', 'branches'));
    }

    public function update(Request $request, Collateral $collateral)
    {
        $this->authorizeCollateral($collateral);

        if ($collateral->status === 'pledged') {
            return redirect()->route('collaterals.show', $collateral)
                ->with('error', 'Pledged collateral cannot be edited.');
        }

        $organizationId = $this->organizationId();

        $validated = $request->validate([
            'client_id' => ['required', Rule::exists('clients', 'id')->where('organization_id', $organizationId)],
            'branch_id' => ['nullable', Rule::exists('branches', 'id')->where('organization_id', $organizationId)],
            'type' => ['required', Rule::in(array_keys(Collateral::TYPES))],
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'estimated_value' => 'required|numeric|min:0.01',
            'lending_ratio' => 'nullable|numeric|min:1|max:100',
            'location' => 'nullable|string|max:255',
            'identifier' => 'nullable|string|max:255',
            'document' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        if ($request->hasFile('document')) {
            if ($collateral->document_path) {
                Storage::disk('public')->delete($collateral->document_path);
            }
            $validated['document_path'] = $request->file('document')->store('collaterals', 'public');
        }

        $collateral->update([
            'branch_id' => $validated['branch_id'] ?? null,
            'client_id' => $validated['client_id'],
            'type' => $validated['type'],
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'estimated_value' => $validated['estimated_value'],
            'lending_ratio' => $validated['lending_ratio'] ?? 70,
            'location' => $validated['location'] ?? null,
            'identifier' => $validated['identifier'] ?? null,
            'document_path' => $validated['document_path'] ?? $collateral->document_path,
        ]);

        return redirect()->route('collaterals.show', $collateral)
            ->with('success', 'Collateral updated successfully.');
    }

    public function release(Collateral $collateral)
    {
        $this->authorizeCollateral($collateral);

        if ($collateral->status !== 'pledged') {
            return back()->with('error', 'Only pledged collateral can be released.');
        }

        $collateral->releaseFromLoan();

        return back()->with('success', 'Collateral released and is available for use again.');
    }

    public function clientAvailable(Client $client)
    {
        $organizationId = $this->organizationId();

        abort_unless((int) $client->organization_id === $organizationId, 404);

        $items = Collateral::query()
            ->where('organization_id', $organizationId)
            ->where('client_id', $client->id)
            ->where('status', 'available')
            ->whereNull('loan_id')
            ->orderBy('title')
            ->get()
            ->map(fn (Collateral $c) => [
                'id' => $c->id,
                'uuid' => $c->uuid,
                'reference_number' => $c->reference_number,
                'title' => $c->title,
                'type' => $c->typeLabel(),
                'estimated_value' => (float) $c->estimated_value,
                'lending_ratio' => (float) $c->lending_ratio,
                'lending_capacity' => $c->lendingCapacity(),
                'location' => $c->location,
            ]);

        return response()->json($items);
    }

    public function clientLoans(string $client)
    {
        $organizationId = $this->organizationId();

        $clientModel = Client::query()
            ->where('organization_id', $organizationId)
            ->where(function ($query) use ($client) {
                $query->where('uuid', $client);
                if (is_numeric($client)) {
                    $query->orWhere('id', (int) $client);
                }
            })
            ->firstOrFail();

        $loans = Loan::query()
            ->where('organization_id', $organizationId)
            ->where('client_id', $clientModel->id)
            ->whereIn('status', ['pending', 'under_review', 'assessed'])
            ->whereDoesntHave('pledgedCollateral')
            ->orderByDesc('created_at')
            ->get(['id', 'loan_number', 'loan_amount', 'status']);

        return response()->json($loans->map(fn (Loan $loan) => [
            'id' => $loan->id,
            'loan_number' => $loan->loan_number,
            'loan_amount' => (float) $loan->loan_amount,
            'status' => $loan->status,
            'status_label' => ucfirst(str_replace('_', ' ', $loan->status)),
        ]));
    }

    private function attachCollateralToLoan(Collateral $collateral, Loan $loan): void
    {
        if ((int) $collateral->client_id !== (int) $loan->client_id) {
            throw new \InvalidArgumentException('The loan must belong to the same client as the collateral.');
        }

        if (!in_array($loan->status, ['pending', 'under_review', 'assessed'], true)) {
            throw new \InvalidArgumentException('Collateral can only be attached to loans that are pending, under review, or assessed.');
        }

        if ($loan->pledgedCollateral()->exists()) {
            throw new \InvalidArgumentException('This loan already has collateral attached.');
        }

        if (!$collateral->isAvailable()) {
            throw new \InvalidArgumentException('This collateral is not available for pledging.');
        }

        $collateral->pledgeToLoan($loan);

        $metadata = is_array($loan->metadata) ? $loan->metadata : [];
        $metadata['collateral_id'] = $collateral->id;

        $scoring = app(ClientScoringService::class)->score(
            $loan->client,
            $loan->loanProduct,
            (float) $loan->loan_amount,
            [
                'tenure_months' => (float) $loan->loan_tenure_months,
                'interest_rate' => (float) $loan->interest_rate,
                'repayment_frequency' => $loan->repayment_frequency ?? 'monthly',
                'interest_calculation_method' => $loan->interest_calculation_method ?? 'flat',
            ],
            $collateral->fresh()
        );
        $metadata['client_scoring'] = $scoring;

        $loan->update([
            'requires_collateral' => true,
            'collateral_description' => $collateral->title . ($collateral->description ? ' — ' . $collateral->description : ''),
            'collateral_value' => $collateral->estimated_value,
            'collateral_location' => $collateral->location,
            'metadata' => $metadata,
        ]);
    }

    private function authorizeCollateral(Collateral $collateral): void
    {
        abort_unless((int) $collateral->organization_id === $this->organizationId(), 404);
    }
}
