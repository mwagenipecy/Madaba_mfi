<?php

namespace App\Http\Controllers;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Carbon\Carbon;

trait CrbReportSheetMethods
{
    /**
     * Create Contract sheet.
     */
    private function createContractSheet($spreadsheet, $data)
    {
        $sheet = $spreadsheet->createSheet(0);
        $sheet->setTitle('Contract');

        // Headers
        $headers = [
            'A1' => 'Contract ID',
            'B1' => 'Client Name',
            'C1' => 'Client ID',
            'D1' => 'Loan Product',
            'E1' => 'Principal Amount',
            'F1' => 'Interest Rate',
            'G1' => 'Term (Months)',
            'H1' => 'Disbursement Date',
            'I1' => 'Maturity Date',
            'J1' => 'Status',
            'K1' => 'Outstanding Balance',
            'L1' => 'Branch',
            'M1' => 'Created Date'
        ];

        foreach ($headers as $cell => $value) {
            $sheet->setCellValue($cell, $value);
        }

        // Style headers
        $this->styleHeaders($sheet, 'A1:M1');

        // Data rows
        $row = 2;
        foreach ($data['loans'] as $loan) {
            $sheet->setCellValue('A' . $row, $loan->id);
            $sheet->setCellValue('B' . $row, $loan->client->first_name . ' ' . $loan->client->last_name);
            $sheet->setCellValue('C' . $row, $loan->client->id);
            $sheet->setCellValue('D' . $row, $loan->loanProduct->name ?? 'N/A');
            $sheet->setCellValue('E' . $row, $loan->principal_amount);
            $sheet->setCellValue('F' . $row, $loan->interest_rate . '%');
            $sheet->setCellValue('G' . $row, $loan->term_months);
            $sheet->setCellValue('H' . $row, $loan->disbursement_date ? Carbon::parse($loan->disbursement_date)->format('Y-m-d') : 'N/A');
            $sheet->setCellValue('I' . $row, $loan->maturity_date ? Carbon::parse($loan->maturity_date)->format('Y-m-d') : 'N/A');
            $sheet->setCellValue('J' . $row, ucfirst($loan->status));
            $sheet->setCellValue('K' . $row, $loan->outstanding_balance ?? 0);
            $sheet->setCellValue('L' . $row, $loan->branch->name ?? 'N/A');
            $sheet->setCellValue('M' . $row, $loan->created_at->format('Y-m-d H:i:s'));
            $row++;
        }

        // Auto-size columns
        $this->autoSizeColumns($sheet, 'A', 'M');
    }

    /**
     * Create Individual sheet.
     */
    private function createIndividualSheet($spreadsheet, $data)
    {
        $sheet = $spreadsheet->createSheet(1);
        $sheet->setTitle('Individual');

        // Headers
        $headers = [
            'A1' => 'Client ID',
            'B1' => 'First Name',
            'C1' => 'Last Name',
            'D1' => 'ID Number',
            'E1' => 'Phone',
            'F1' => 'Email',
            'G1' => 'Date of Birth',
            'H1' => 'Gender',
            'I1' => 'Address',
            'J1' => 'City',
            'K1' => 'State',
            'L1' => 'Postal Code',
            'M1' => 'Country',
            'N1' => 'Registration Date',
            'O1' => 'Total Loans',
            'P1' => 'Active Loans',
            'Q1' => 'Total Outstanding'
        ];

        foreach ($headers as $cell => $value) {
            $sheet->setCellValue($cell, $value);
        }

        // Style headers
        $this->styleHeaders($sheet, 'A1:Q1');

        // Data rows
        $row = 2;
        foreach ($data['clients'] as $client) {
            $totalLoans = $client->loans()->count();
            $activeLoans = $client->loans()->whereIn('status', ['active', 'disbursed'])->count();
            $totalOutstanding = $client->loans()->sum('outstanding_balance');

            $sheet->setCellValue('A' . $row, $client->id);
            $sheet->setCellValue('B' . $row, $client->first_name);
            $sheet->setCellValue('C' . $row, $client->last_name);
            $sheet->setCellValue('D' . $row, $client->id_number);
            $sheet->setCellValue('E' . $row, $client->phone);
            $sheet->setCellValue('F' . $row, $client->email);
            $sheet->setCellValue('G' . $row, $client->date_of_birth ? Carbon::parse($client->date_of_birth)->format('Y-m-d') : 'N/A');
            $sheet->setCellValue('H' . $row, ucfirst($client->gender ?? 'N/A'));
            $sheet->setCellValue('I' . $row, $client->address);
            $sheet->setCellValue('J' . $row, $client->city);
            $sheet->setCellValue('K' . $row, $client->state);
            $sheet->setCellValue('L' . $row, $client->postal_code);
            $sheet->setCellValue('M' . $row, $client->country);
            $sheet->setCellValue('N' . $row, $client->created_at->format('Y-m-d'));
            $sheet->setCellValue('O' . $row, $totalLoans);
            $sheet->setCellValue('P' . $row, $activeLoans);
            $sheet->setCellValue('Q' . $row, $totalOutstanding);
            $row++;
        }

        // Auto-size columns
        $this->autoSizeColumns($sheet, 'A', 'Q');
    }

    /**
     * Create Subject Relation sheet.
     */
    private function createSubjectRelationSheet($spreadsheet, $data)
    {
        $sheet = $spreadsheet->createSheet(2);
        $sheet->setTitle('Subject Relation');

        // Headers
        $headers = [
            'A1' => 'Client ID',
            'B1' => 'Client Name',
            'C1' => 'Relation Type',
            'D1' => 'Related Client ID',
            'E1' => 'Related Client Name',
            'F1' => 'Relation Status',
            'G1' => 'Created Date',
            'H1' => 'Notes'
        ];

        foreach ($headers as $cell => $value) {
            $sheet->setCellValue($cell, $value);
        }

        // Style headers
        $this->styleHeaders($sheet, 'A1:H1');

        // Data rows - For now, we'll create sample data as we don't have a relations table
        $row = 2;
        foreach ($data['clients'] as $client) {
            // Sample relation data - in a real system, this would come from a relations table
            $relations = [
                ['type' => 'Guarantor', 'status' => 'Active'],
                ['type' => 'Co-signer', 'status' => 'Active'],
                ['type' => 'Reference', 'status' => 'Active'],
            ];

            foreach ($relations as $relation) {
                $sheet->setCellValue('A' . $row, $client->id);
                $sheet->setCellValue('B' . $row, $client->first_name . ' ' . $client->last_name);
                $sheet->setCellValue('C' . $row, $relation['type']);
                $sheet->setCellValue('D' . $row, 'N/A'); // Related client ID
                $sheet->setCellValue('E' . $row, 'N/A'); // Related client name
                $sheet->setCellValue('F' . $row, $relation['status']);
                $sheet->setCellValue('G' . $row, $client->created_at->format('Y-m-d'));
                $sheet->setCellValue('H' . $row, 'Sample relation data');
                $row++;
            }
        }

        // Auto-size columns
        $this->autoSizeColumns($sheet, 'A', 'H');
    }

    /**
     * Create Company sheet.
     */
    private function createCompanySheet($spreadsheet, $data)
    {
        $sheet = $spreadsheet->createSheet(3);
        $sheet->setTitle('Company');

        // Headers
        $headers = [
            'A1' => 'Organization ID',
            'B1' => 'Organization Name',
            'C1' => 'Registration Number',
            'D1' => 'Address',
            'E1' => 'City',
            'F1' => 'State',
            'G1' => 'Country',
            'H1' => 'Phone',
            'I1' => 'Email',
            'J1' => 'Website',
            'K1' => 'Total Branches',
            'L1' => 'Total Clients',
            'M1' => 'Total Loans',
            'N1' => 'Total Outstanding',
            'O1' => 'Registration Date'
        ];

        foreach ($headers as $cell => $value) {
            $sheet->setCellValue($cell, $value);
        }

        // Style headers
        $this->styleHeaders($sheet, 'A1:O1');

        // Data rows
        $row = 2;
        $organization = $data['organization'];
        
        $totalBranches = \App\Models\Branch::where('organization_id', $organization->id)->count();
        $totalClients = $data['clients']->count();
        $totalLoans = $data['loans']->count();
        $totalOutstanding = $data['loans']->sum('outstanding_balance');

        $sheet->setCellValue('A' . $row, $organization->id);
        $sheet->setCellValue('B' . $row, $organization->name);
        $sheet->setCellValue('C' . $row, $organization->registration_number ?? 'N/A');
        $sheet->setCellValue('D' . $row, $organization->address ?? 'N/A');
        $sheet->setCellValue('E' . $row, $organization->city ?? 'N/A');
        $sheet->setCellValue('F' . $row, $organization->state ?? 'N/A');
        $sheet->setCellValue('G' . $row, $organization->country ?? 'N/A');
        $sheet->setCellValue('H' . $row, $organization->phone ?? 'N/A');
        $sheet->setCellValue('I' . $row, $organization->email ?? 'N/A');
        $sheet->setCellValue('J' . $row, $organization->website ?? 'N/A');
        $sheet->setCellValue('K' . $row, $totalBranches);
        $sheet->setCellValue('L' . $row, $totalClients);
        $sheet->setCellValue('M' . $row, $totalLoans);
        $sheet->setCellValue('N' . $row, $totalOutstanding);
        $sheet->setCellValue('O' . $row, $organization->created_at->format('Y-m-d'));

        // Auto-size columns
        $this->autoSizeColumns($sheet, 'A', 'O');
    }

    /**
     * Style headers with background color and bold text.
     */
    private function styleHeaders($sheet, $range)
    {
        $sheet->getStyle($range)->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF']
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '008000']
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000']
                ]
            ]
        ]);
    }

    /**
     * Auto-size columns.
     */
    private function autoSizeColumns($sheet, $startColumn, $endColumn)
    {
        $startCol = ord($startColumn) - ord('A');
        $endCol = ord($endColumn) - ord('A');
        
        for ($col = $startCol; $col <= $endCol; $col++) {
            $sheet->getColumnDimensionByColumn($col + 1)->setAutoSize(true);
        }
    }
}

