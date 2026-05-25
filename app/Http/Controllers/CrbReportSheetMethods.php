<?php

namespace App\Http\Controllers;

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Carbon\Carbon;

trait CrbReportSheetMethods
{
    /**
     * Create Contract sheet (CRB format).
     */
    private function createContractSheet($spreadsheet, $data)
    {
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Contract');

        $headers = [
            'Reporting Date',
            'Contract code',
            'Customer Code',
            'Branch',
            'Contract Status',
            'Phase of Contract',
            'Transfer Status',
            'Type of Contract',
            'Purpose of Financing',
            'Interest Rate',
            'Total Amount',
            'Total Taken Amount',
            'Installment Amount',
            'Number of Installments',
            'Number of Outstanding Installments',
            'Outstanding Amount',
            'Past Due Amount',
            'Past Due Days',
            'Number of Due Installments',
            'Additional Fees Sum',
            'Additional Fees Paid',
            'Date of Last Payment',
            'Total Monthly Payment',
            'Payment Periodicity',
            'Credit Usage in Last 30 Days',
            'Start Date',
            'Expected End Date',
            'Real End Date',
            'Negative Status of the Contract',
            'Collateral Type',
            'Collateral Value',
            'Role of Customer',
            'Currency of Contract',
        ];

        $lastColumn = Coordinate::stringFromColumnIndex(count($headers));

        foreach ($headers as $index => $header) {
            $column = Coordinate::stringFromColumnIndex($index + 1);
            $sheet->setCellValue($column . '1', $header);
        }

        $this->styleHeaders($sheet, 'A1:' . $lastColumn . '1');

        $reportingDate = now()->format('Y-m-d');
        $row = 2;

        $loans = $data['contract_loans'] ?? $this->getLatestActiveContractsPerCustomer($data['loans']);

        foreach ($loans as $loan) {
            $values = $this->mapContractCrbRow($loan, $reportingDate);

            foreach ($values as $index => $value) {
                $column = Coordinate::stringFromColumnIndex($index + 1);
                $sheet->setCellValue($column . $row, $value);
            }

            $row++;
        }

        $this->autoSizeColumns($sheet, 'A', $lastColumn);
    }

    /**
     * Map a loan record to CRB Contract sheet columns.
     */
    private function mapContractCrbRow($loan, string $reportingDate): array
    {
        $meta = is_array($loan->metadata) ? $loan->metadata : [];
        $stats = $this->computeContractScheduleStats($loan);

        $additionalFeesSum = (float) ($loan->processing_fee ?? 0)
            + (float) ($loan->insurance_fee ?? 0)
            + (float) ($loan->late_fee ?? 0)
            + (float) ($loan->penalty_fee ?? 0)
            + (float) ($loan->other_fees ?? 0);

        $outstandingAmount = $loan->outstanding_balance ?? $loan->calculated_outstanding_amount ?? $stats['outstanding_amount'];

        return [
            $reportingDate,
            $this->crbValue($loan->loan_number),
            $this->crbValue($loan->client?->client_number),
            $this->crbValue($loan->branch?->name),
            $loan->status ? ucfirst(str_replace('_', ' ', $loan->status)) : '',
            $this->crbContractPhase($loan, $meta),
            $this->crbMetadata($meta, 'transfer_status'),
            $this->crbValue($loan->loanProduct?->name),
            $this->crbValue($loan->loan_purpose),
            $loan->interest_rate !== null ? $loan->interest_rate : '',
            $loan->total_amount !== null ? $loan->total_amount : '',
            $loan->approved_amount !== null ? $loan->approved_amount : ($loan->loan_amount ?? ''),
            $loan->monthly_payment !== null ? $loan->monthly_payment : '',
            $stats['number_of_installments'],
            $stats['outstanding_installments'],
            $outstandingAmount !== null && $outstandingAmount !== '' ? $outstandingAmount : '',
            $stats['past_due_amount'] !== '' ? $stats['past_due_amount'] : ($loan->overdue_amount ?? ''),
            $stats['past_due_days'] !== '' ? $stats['past_due_days'] : ($loan->overdue_days ?? ''),
            $stats['due_installments'],
            $additionalFeesSum > 0 ? $additionalFeesSum : '',
            $stats['additional_fees_paid'] !== '' ? $stats['additional_fees_paid'] : '',
            $stats['last_payment_date'],
            $loan->monthly_payment !== null ? $loan->monthly_payment : '',
            $this->crbPaymentPeriodicity($loan->repayment_frequency),
            $this->crbMetadata($meta, 'credit_usage_last_30_days'),
            $this->crbFormatDate($loan->disbursement_date),
            $this->crbFormatDate($loan->maturity_date),
            $this->crbFormatDate($loan->closure_date),
            $this->crbContractNegativeStatus($loan),
            $this->crbCollateralType($loan),
            $loan->collateral_value !== null ? $loan->collateral_value : '',
            $this->crbMetadata($meta, 'role_of_customer', 'Borrower'),
            $this->crbMetadata($meta, 'currency', 'TZS'),
        ];
    }

    private function computeContractScheduleStats($loan): array
    {
        $schedules = $loan->relationLoaded('schedules')
            ? $loan->schedules
            : $loan->schedules()->get();

        $today = today();

        $numberOfInstallments = $schedules->count() > 0
            ? $schedules->count()
            : ($loan->total_payments ?? '');

        if ($schedules->isEmpty()) {
            $outstandingInstallments = max(0, (int) ($loan->total_payments ?? 0) - (int) ($loan->payments_made ?? 0));

            return [
                'number_of_installments' => $numberOfInstallments,
                'outstanding_installments' => $outstandingInstallments > 0 ? $outstandingInstallments : '',
                'outstanding_amount' => $loan->outstanding_balance ?? '',
                'past_due_amount' => $loan->overdue_amount ?? '',
                'past_due_days' => $loan->overdue_days ?? '',
                'due_installments' => '',
                'additional_fees_paid' => '',
                'last_payment_date' => '',
            ];
        }

        $isOutstanding = function ($schedule) {
            if ($schedule->status === 'paid') {
                return false;
            }

            $remaining = $schedule->remaining_total ?? $schedule->outstanding_amount ?? 0;

            return (float) $remaining > 0.01;
        };

        $outstandingInstallments = $schedules->filter($isOutstanding)->count();

        $pastDueSchedules = $schedules->filter(
            fn ($s) => $s->due_date->lt($today) && $s->status !== 'paid'
        );

        $pastDueAmount = $pastDueSchedules->sum(
            fn ($s) => (float) ($s->remaining_total ?? $s->outstanding_amount ?? 0)
        );

        $pastDueDays = $pastDueSchedules->isNotEmpty()
            ? $pastDueSchedules->max(fn ($s) => $s->due_date->diffInDays($today))
            : '';

        $dueInstallments = $schedules->filter(
            fn ($s) => $s->due_date->lte($today) && $s->status !== 'paid'
        )->count();

        $lastPaidSchedule = $schedules->whereNotNull('paid_date')->sortByDesc('paid_date')->first();
        $lastPaymentDate = $lastPaidSchedule
            ? $this->crbFormatDate($lastPaidSchedule->paid_date)
            : '';

        $additionalFeesPaid = $schedules->sum(
            fn ($s) => (float) ($s->late_fee ?? 0) + (float) ($s->penalty_fee ?? 0)
        );

        $outstandingFromSchedules = $schedules->sum(
            fn ($s) => (float) ($s->remaining_total ?? $s->outstanding_amount ?? 0)
        );

        return [
            'number_of_installments' => $numberOfInstallments,
            'outstanding_installments' => $outstandingInstallments > 0 ? $outstandingInstallments : '',
            'outstanding_amount' => $outstandingFromSchedules > 0 ? $outstandingFromSchedules : '',
            'past_due_amount' => $pastDueAmount > 0 ? $pastDueAmount : '',
            'past_due_days' => $pastDueDays !== '' ? $pastDueDays : '',
            'due_installments' => $dueInstallments > 0 ? $dueInstallments : '',
            'additional_fees_paid' => $additionalFeesPaid > 0 ? $additionalFeesPaid : '',
            'last_payment_date' => $lastPaymentDate,
        ];
    }

    private function crbContractPhase($loan, array $meta): string
    {
        $fromMeta = $this->crbMetadata($meta, 'phase_of_contract');
        if ($fromMeta !== '') {
            return $fromMeta;
        }

        return match ($loan->status) {
            'pending', 'under_review', 'assessed' => 'Application',
            'approved' => 'Approved',
            'disbursed', 'active' => 'Performing',
            'overdue' => 'Arrears',
            'completed' => 'Closed',
            'written_off' => 'Written Off',
            'cancelled', 'rejected' => 'Terminated',
            default => '',
        };
    }

    private function crbContractNegativeStatus($loan): string
    {
        return match ($loan->status) {
            'overdue' => 'Overdue',
            'written_off' => 'Written Off',
            'cancelled' => 'Cancelled',
            default => '',
        };
    }

    private function crbCollateralType($loan): string
    {
        if ($loan->collateral_description) {
            return $loan->collateral_description;
        }

        if ($loan->requires_collateral) {
            return 'Collateral Required';
        }

        return '';
    }

    private function crbPaymentPeriodicity(?string $frequency): string
    {
        if (!$frequency) {
            return '';
        }

        return match ($frequency) {
            'daily' => 'Daily',
            'weekly' => 'Weekly',
            'monthly' => 'Monthly',
            'quarterly' => 'Quarterly',
            default => ucfirst($frequency),
        };
    }

    /**
     * One active contract per customer: the latest by disbursement/created date.
     * Individual and Contract reports join on Customer Code (client_number).
     */
    private function getLatestActiveContractsPerCustomer($loans)
    {
        $activeStatuses = ['active', 'disbursed'];

        return collect($loans)
            ->whereIn('status', $activeStatuses)
            ->filter(fn ($loan) => $loan->client_id)
            ->groupBy('client_id')
            ->map(function ($clientLoans) {
                return $clientLoans->sortByDesc(function ($loan) {
                    $disbursed = $loan->disbursement_date
                        ? Carbon::parse($loan->disbursement_date)->timestamp
                        : 0;
                    $created = $loan->created_at
                        ? Carbon::parse($loan->created_at)->timestamp
                        : 0;

                    return sprintf('%020d-%020d-%010d', $disbursed, $created, $loan->id);
                })->first();
            })
            ->filter()
            ->values();
    }

    /**
     * Create Individual sheet (CRB format).
     */
    private function createIndividualSheet($spreadsheet, $data)
    {
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Individual');

        $headers = [
            'Customer Code',
            'Present Surname',
            'Birth Surname',
            'First Name',
            'Middle Names',
            'Full Name',
            'Number of Spouse',
            'Number of Childrens',
            'Classification of Individual',
            'Gender',
            'Date of Birth',
            'Country of Birth',
            'Marital Status',
            'Fate Status',
            'Social status',
            'Residency',
            'Citizenship',
            'Nationality',
            'Employment',
            'Employer Name',
            'Education',
            'Business Name',
            'Income Available',
            'Monthly Expenses',
            'Negative Status of an Individual',
            'Tax Identification Number',
            'National ID',
            'Passport Number',
            'Passport Issuer Country',
            'Driving License Number',
            "Voter's ID",
            'Foreign Unique ID',
            'Custom ID Number 1',
            'Custom ID Number 2',
            'Main address',
            'Street',
            'Number of Building',
            'Postal Code',
            'Region',
            'District',
            'Country',
            'Mobile Phone',
            'Fixed line',
            'E-mail',
            'Web Page',
        ];

        $lastColumn = Coordinate::stringFromColumnIndex(count($headers));

        foreach ($headers as $index => $header) {
            $column = Coordinate::stringFromColumnIndex($index + 1);
            $sheet->setCellValue($column . '1', $header);
        }

        $this->styleHeaders($sheet, 'A1:' . $lastColumn . '1');

        $row = 2;
        $contractLoans = $data['contract_loans'] ?? $this->getLatestActiveContractsPerCustomer($data['loans']);
        $linkedClientIds = $contractLoans->pluck('client_id')->unique();
        $linkedCustomerCodes = $contractLoans
            ->map(fn ($loan) => $loan->client?->client_number)
            ->filter()
            ->unique();

        $clients = $data['clients']
            ->where('client_type', 'individual')
            ->filter(function ($client) use ($linkedClientIds, $linkedCustomerCodes) {
                return $linkedClientIds->contains($client->id)
                    || $linkedCustomerCodes->contains($client->client_number);
            });

        foreach ($clients as $client) {
            $values = $this->mapIndividualCrbRow($client);

            foreach ($values as $index => $value) {
                $column = Coordinate::stringFromColumnIndex($index + 1);
                $sheet->setCellValue($column . $row, $value);
            }

            $row++;
        }

        $this->autoSizeColumns($sheet, 'A', $lastColumn);
    }

    /**
     * Map a client record to CRB Individual sheet columns.
     */
    private function mapIndividualCrbRow($client): array
    {
        $meta = is_array($client->metadata) ? $client->metadata : [];
        $country = $this->crbValue($client->country);

        return [
            $this->crbValue($client->client_number),
            $this->crbValue($client->last_name),
            $this->crbMetadata($meta, 'birth_surname'),
            $this->crbValue($client->first_name),
            $this->crbValue($client->middle_name),
            $this->crbValue(trim(implode(' ', array_filter([$client->first_name, $client->middle_name, $client->last_name])))),
            $this->crbMetadata($meta, 'number_of_spouse'),
            $this->crbMetadata($meta, 'number_of_children'),
            $this->crbClassification($client->client_type),
            $client->gender ? ucfirst($client->gender) : '',
            $this->crbFormatDate($client->date_of_birth),
            $this->crbMetadata($meta, 'country_of_birth'),
            $client->marital_status ? ucfirst(str_replace('_', ' ', $client->marital_status)) : '',
            $this->crbMetadata($meta, 'fate_status'),
            $this->crbMetadata($meta, 'social_status'),
            $this->crbMetadata($meta, 'residency', $country),
            $this->crbMetadata($meta, 'citizenship', $country),
            $this->crbMetadata($meta, 'nationality', $country),
            $this->crbValue($client->occupation ?: $client->income_source),
            $this->crbValue($client->employer_name),
            $this->crbMetadata($meta, 'education'),
            $this->crbValue($client->business_name),
            $client->monthly_income !== null ? $client->monthly_income : '',
            $this->crbMetadata($meta, 'monthly_expenses'),
            $client->status === 'blacklisted' ? ucfirst($client->status) : '',
            $this->crbMetadata($meta, 'tax_identification_number'),
            $this->crbValue($client->national_id),
            $this->crbValue($client->passport_number),
            $this->crbMetadata($meta, 'passport_issuer_country'),
            $this->crbMetadata($meta, 'driving_license_number'),
            $this->crbMetadata($meta, 'voters_id'),
            $this->crbMetadata($meta, 'foreign_unique_id'),
            $this->crbMetadata($meta, 'custom_id_number_1'),
            $this->crbMetadata($meta, 'custom_id_number_2'),
            $this->crbValue($client->physical_address),
            $this->crbMetadata($meta, 'street'),
            $this->crbMetadata($meta, 'number_of_building'),
            $this->crbValue($client->postal_code),
            $this->crbValue($client->region),
            $this->crbValue($client->city),
            $country,
            $this->crbValue($client->phone_number),
            $this->crbValue($client->secondary_phone),
            $this->crbValue($client->email),
            $this->crbMetadata($meta, 'web_page'),
        ];
    }

    private function crbFormatDate($value): string
    {
        if (empty($value)) {
            return '';
        }

        return Carbon::parse($value)->format('Y-m-d');
    }

    private function crbValue($value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        return (string) $value;
    }

    private function crbMetadata(array $meta, string $key, string $fallback = ''): string
    {
        if (!empty($meta[$key])) {
            return (string) $meta[$key];
        }

        return $fallback;
    }

    private function crbClassification(?string $clientType): string
    {
        return match ($clientType) {
            'individual' => 'Individual',
            'business' => 'Business',
            'group' => 'Group',
            default => '',
        };
    }

    /**
     * Create Subject Relation sheet.
     */
    private function createSubjectRelationSheet($spreadsheet, $data)
    {
        $sheet = $spreadsheet->getActiveSheet();
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
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Company');

        $headers = [
            'Customer Code',
            'Company Name',
            'Trade Name',
            'Legal Form',
            'Establishment Date',
            'Registration Country',
            'Industry Sector',
            'Registration Number',
            'Tax Identification Number',
            'Street',
            'Number of Building',
            'Postal Code',
            'Region',
            'District',
            'Country',
            'Mobile Phone',
            'Fixed Line',
            'E-mail',
            'Web Page',
        ];

        $lastColumn = Coordinate::stringFromColumnIndex(count($headers));

        foreach ($headers as $index => $header) {
            $column = Coordinate::stringFromColumnIndex($index + 1);
            $sheet->setCellValue($column . '1', $header);
        }

        $this->styleHeaders($sheet, 'A1:' . $lastColumn . '1');

        $row = 2;
        $contractLoans = $data['contract_loans'] ?? $this->getLatestActiveContractsPerCustomer($data['loans']);
        $linkedClientIds = $contractLoans->pluck('client_id')->unique();
        $linkedCustomerCodes = $contractLoans
            ->map(fn ($loan) => $loan->client?->client_number)
            ->filter()
            ->unique();

        $clients = $data['clients']
            ->whereIn('client_type', ['business', 'group'])
            ->filter(function ($client) use ($linkedClientIds, $linkedCustomerCodes) {
                return $linkedClientIds->contains($client->id)
                    || $linkedCustomerCodes->contains($client->client_number);
            });

        foreach ($clients as $client) {
            $values = $this->mapCompanyCrbRow($client);

            foreach ($values as $index => $value) {
                $column = Coordinate::stringFromColumnIndex($index + 1);
                $sheet->setCellValue($column . $row, $value);
            }

            $row++;
        }

        $this->autoSizeColumns($sheet, 'A', $lastColumn);
    }

    /**
     * Map a business/group client record to CRB Company sheet columns.
     */
    private function mapCompanyCrbRow($client): array
    {
        $meta = is_array($client->metadata) ? $client->metadata : [];
        $country = $this->crbValue($client->country);

        return [
            $this->crbValue($client->client_number),
            $this->crbValue($client->business_name),
            $this->crbMetadata($meta, 'trade_name'),
            $this->crbLegalForm($client->business_type, $meta),
            $this->crbFormatDate($this->crbMetadata($meta, 'establishment_date') ?: $client->created_at),
            $this->crbMetadata($meta, 'registration_country', $country),
            $this->crbMetadata($meta, 'industry_sector', $this->crbValue($client->business_description ?: $client->occupation)),
            $this->crbValue($client->business_registration_number),
            $this->crbMetadata($meta, 'tax_identification_number'),
            $this->crbMetadata($meta, 'street', $this->crbValue($client->physical_address)),
            $this->crbMetadata($meta, 'number_of_building'),
            $this->crbValue($client->postal_code),
            $this->crbValue($client->region),
            $this->crbValue($client->city),
            $country,
            $this->crbValue($client->phone_number),
            $this->crbValue($client->secondary_phone),
            $this->crbValue($client->email),
            $this->crbMetadata($meta, 'web_page'),
        ];
    }

    private function crbLegalForm(?string $businessType, array $meta): string
    {
        $fromMeta = $this->crbMetadata($meta, 'legal_form');
        if ($fromMeta !== '') {
            return $fromMeta;
        }

        if (empty($businessType)) {
            return '';
        }

        return ucfirst(str_replace('_', ' ', $businessType));
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
        $startCol = Coordinate::columnIndexFromString($startColumn);
        $endCol = Coordinate::columnIndexFromString($endColumn);

        for ($col = $startCol; $col <= $endCol; $col++) {
            $sheet->getColumnDimensionByColumn($col)->setAutoSize(true);
        }
    }
}

