<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Client;
use App\Models\Loan;
use App\Models\Branch;
use App\Models\Organization;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Csv;
use Carbon\Carbon;

class CrbReportController extends Controller
{
    use CrbReportSheetMethods;

    public const PREVIEW_SHEET_TYPES = ['contract', 'individual', 'company'];

    private const SHEET_TYPES = [
        'contract' => ['method' => 'createContractSheet', 'label' => 'Contract'],
        'individual' => ['method' => 'createIndividualSheet', 'label' => 'Individual'],
        'subject-relation' => ['method' => 'createSubjectRelationSheet', 'label' => 'Subject_Relation'],
        'company' => ['method' => 'createCompanySheet', 'label' => 'Company'],
    ];

    /**
     * Display the CRB report form and preview data.
     */
    public function index(Request $request)
    {
        $organizationId = auth()->user()->organization_id;

        $branches = Branch::where('organization_id', $organizationId)
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        $clients = Client::where('organization_id', $organizationId)
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();

        $filters = [
            'branch_id' => $request->get('branch_id'),
            'client_id' => $request->get('client_id'),
            'start_date' => $request->get('start_date'),
            'end_date' => $request->get('end_date'),
        ];

        $sheetType = $request->get('sheet', 'contract');
        if (!in_array($sheetType, self::PREVIEW_SHEET_TYPES, true)) {
            $sheetType = 'contract';
        }

        $preview = null;
        $dateError = null;

        if ($filters['start_date'] && $filters['end_date'] && $filters['start_date'] > $filters['end_date']) {
            $dateError = 'Start date must be before end date.';
        } else {
            $data = $this->getCrbData(
                $organizationId,
                $filters['branch_id'],
                $filters['client_id'],
                $filters['start_date'],
                $filters['end_date']
            );
            $preview = $this->buildCrbPreview($sheetType, $data);
        }

        return view('reports.crb', compact('branches', 'clients', 'filters', 'sheetType', 'preview', 'dateError'));
    }

    /**
     * Generate and download a single CRB CSV report.
     */
    public function export(Request $request)
    {
        $sheetType = $request->get('sheet');

        if (!$sheetType || !array_key_exists($sheetType, self::SHEET_TYPES)) {
            return back()->withErrors(['sheet' => 'Please select a valid sheet to download.']);
        }

        $organizationId = auth()->user()->organization_id;
        $branchId = $request->get('branch_id');
        $clientId = $request->get('client_id');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        if ($startDate && $endDate && $startDate > $endDate) {
            return back()->withErrors(['date_range' => 'Start date must be before end date.']);
        }

        $data = $this->getCrbData($organizationId, $branchId, $clientId, $startDate, $endDate);

        $sheetConfig = self::SHEET_TYPES[$sheetType];
        $spreadsheet = new Spreadsheet();
        $this->{$sheetConfig['method']}($spreadsheet, $data);

        $filename = 'CRB_' . $sheetConfig['label'] . '_' . now()->format('Y-m-d_H-i-s') . '.csv';
        $writer = new Csv($spreadsheet);
        $writer->setDelimiter(',');
        $writer->setEnclosure('"');
        $writer->setLineEnding("\r\n");
        $writer->setSheetIndex(0);
        $writer->setUseBom(true);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * Get CRB data based on filters.
     */
    private function getCrbData($organizationId, $branchId = null, $clientId = null, $startDate = null, $endDate = null)
    {
        $loansQuery = Loan::with(['client', 'loanProduct', 'branch', 'schedules', 'pledgedCollateral'])
            ->where('organization_id', $organizationId)
            ->whereIn('status', $this->getCrbContractStatuses())
            ->whereNotNull('disbursement_date');

        if ($branchId) {
            $loansQuery->where('branch_id', $branchId);
        }

        if ($clientId) {
            $loansQuery->where('client_id', $clientId);
        }

        if ($startDate) {
            $loansQuery->where('disbursement_date', '>=', Carbon::parse($startDate)->startOfDay());
        }

        if ($endDate) {
            $loansQuery->where('disbursement_date', '<=', Carbon::parse($endDate)->endOfDay());
        }

        $loans = $loansQuery->orderByDesc('disbursement_date')->get();
        $contractLoans = $this->getCrbContractLoans($loans);

        $clientIds = $contractLoans->pluck('client_id')->filter()->unique();

        $clientsQuery = Client::where('organization_id', $organizationId);

        if ($clientIds->isNotEmpty()) {
            $clientsQuery->whereIn('id', $clientIds);
        } elseif ($clientId || $branchId) {
            // Filters were applied but no reportable loans matched.
            $clientsQuery->whereRaw('1 = 0');
        }

        if ($clientId) {
            $clientsQuery->where('id', $clientId);
        }

        $clients = $clientsQuery->orderBy('first_name')->orderBy('last_name')->get();

        $organization = Organization::find($organizationId);

        return [
            'loans' => $loans,
            'contract_loans' => $contractLoans,
            'clients' => $clients,
            'organization' => $organization,
            'filters' => [
                'branch_id' => $branchId,
                'client_id' => $clientId,
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
        ];
    }
}
