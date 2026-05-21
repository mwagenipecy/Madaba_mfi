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
    /**
     * Display the CRB report form.
     */
    public function index(Request $request)
    {
        $organizationId = auth()->user()->organization_id;
        
        // Get branches for filter
        $branches = Branch::where('organization_id', $organizationId)
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        // Get clients for filter
        $clients = Client::where('organization_id', $organizationId)
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();

        return view('reports.crb', compact('branches', 'clients'));
    }

    private const SHEET_TYPES = [
        'contract' => ['method' => 'createContractSheet', 'label' => 'Contract'],
        'individual' => ['method' => 'createIndividualSheet', 'label' => 'Individual'],
        'subject-relation' => ['method' => 'createSubjectRelationSheet', 'label' => 'Subject_Relation'],
        'company' => ['method' => 'createCompanySheet', 'label' => 'Company'],
    ];

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
        // Base query for loans
        $loansQuery = Loan::with(['client', 'loanProduct', 'branch', 'schedules'])
            ->where('organization_id', $organizationId);

        // Apply filters
        if ($branchId) {
            $loansQuery->where('branch_id', $branchId);
        }

        if ($clientId) {
            $loansQuery->where('client_id', $clientId);
        }

        if ($startDate) {
            $loansQuery->where(function ($query) use ($startDate) {
                $query->where('disbursement_date', '>=', Carbon::parse($startDate)->startOfDay())
                    ->orWhere(function ($q) use ($startDate) {
                        $q->whereNull('disbursement_date')
                            ->where('created_at', '>=', Carbon::parse($startDate)->startOfDay());
                    });
            });
        }

        if ($endDate) {
            $loansQuery->where(function ($query) use ($endDate) {
                $query->where('disbursement_date', '<=', Carbon::parse($endDate)->endOfDay())
                    ->orWhere(function ($q) use ($endDate) {
                        $q->whereNull('disbursement_date')
                            ->where('created_at', '<=', Carbon::parse($endDate)->endOfDay());
                    });
            });
        }

        $loans = $loansQuery->get();
        $contractLoans = $this->getLatestActiveContractsPerCustomer($loans);

        // Get clients data
        $clientsQuery = Client::where('organization_id', $organizationId);
        
        if ($branchId) {
            $clientsQuery->whereHas('loans', function($query) use ($branchId) {
                $query->where('branch_id', $branchId);
            });
        }

        if ($clientId) {
            $clientsQuery->where('id', $clientId);
        }

        $clients = $clientsQuery->get();

        // Get organization data
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
            ]
        ];
    }

}
