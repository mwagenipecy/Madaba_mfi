<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Client;
use App\Models\Loan;
use App\Models\Branch;
use App\Models\Organization;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;
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

    /**
     * Generate and download CRB Excel report.
     */
    public function export(Request $request)
    {
        $organizationId = auth()->user()->organization_id;
        $branchId = $request->get('branch_id');
        $clientId = $request->get('client_id');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        // Validate dates
        if ($startDate && $endDate && $startDate > $endDate) {
            return back()->withErrors(['date_range' => 'Start date must be before end date.']);
        }

        // Get data based on filters
        $data = $this->getCrbData($organizationId, $branchId, $clientId, $startDate, $endDate);

        // Generate Excel file
        $spreadsheet = $this->generateExcelReport($data, $startDate, $endDate);

        // Set headers for download
        $filename = 'CRB_Report_' . now()->format('Y-m-d_H-i-s') . '.xlsx';
        
        $writer = new Xlsx($spreadsheet);
        
        return response()->streamDownload(function() use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * Get CRB data based on filters.
     */
    private function getCrbData($organizationId, $branchId = null, $clientId = null, $startDate = null, $endDate = null)
    {
        // Base query for loans
        $loansQuery = Loan::with(['client', 'loanProduct', 'branch'])
            ->where('organization_id', $organizationId);

        // Apply filters
        if ($branchId) {
            $loansQuery->where('branch_id', $branchId);
        }

        if ($clientId) {
            $loansQuery->where('client_id', $clientId);
        }

        if ($startDate) {
            $loansQuery->where('created_at', '>=', Carbon::parse($startDate)->startOfDay());
        }

        if ($endDate) {
            $loansQuery->where('created_at', '<=', Carbon::parse($endDate)->endOfDay());
        }

        $loans = $loansQuery->get();

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

    /**
     * Generate Excel report with multiple sheets.
     */
    private function generateExcelReport($data, $startDate = null, $endDate = null)
    {
        $spreadsheet = new Spreadsheet();

        // Create sheets
        $this->createContractSheet($spreadsheet, $data);
        $this->createIndividualSheet($spreadsheet, $data);
        $this->createSubjectRelationSheet($spreadsheet, $data);
        $this->createCompanySheet($spreadsheet, $data);

        // Set active sheet to first one
        $spreadsheet->setActiveSheetIndex(0);

        return $spreadsheet;
    }
}
