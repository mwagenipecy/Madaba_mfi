<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Credit Score Report - {{ $report['client']['display_name'] }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1f2937; line-height: 1.4; }
        h1 { font-size: 18px; margin: 0 0 4px; color: #111827; }
        h2 { font-size: 13px; margin: 18px 0 8px; color: #374151; border-bottom: 1px solid #e5e7eb; padding-bottom: 4px; }
        .header { margin-bottom: 16px; }
        .meta { color: #6b7280; font-size: 10px; }
        .score-box { border: 2px solid #16a34a; border-radius: 8px; padding: 12px; display: inline-block; text-align: center; min-width: 90px; }
        .score-box.critical { border-color: #dc2626; }
        .score-box.poor { border-color: #ea580c; }
        .score-box.fair { border-color: #ca8a04; }
        .score-box.good { border-color: #2563eb; }
        .score-num { font-size: 28px; font-weight: bold; }
        table { width: 100%; border-collapse: collapse; margin-top: 6px; }
        th, td { border: 1px solid #e5e7eb; padding: 5px 6px; text-align: left; vertical-align: top; }
        th { background: #f9fafb; font-size: 9px; text-transform: uppercase; color: #6b7280; }
        .danger { background: #fef2f2; color: #991b1b; padding: 8px; border: 1px solid #fecaca; margin-top: 8px; }
        .info-grid { width: 100%; }
        .info-grid td { border: none; padding: 3px 8px 3px 0; width: 50%; }
        .label { color: #6b7280; }
        ul { margin: 4px 0; padding-left: 16px; }
        .badge { display: inline-block; padding: 2px 8px; border-radius: 10px; font-size: 9px; font-weight: bold; }
        .badge-pass { background: #dcfce7; color: #166534; }
        .badge-fail { background: #fee2e2; color: #991b1b; }
        .footer { margin-top: 24px; font-size: 9px; color: #9ca3af; border-top: 1px solid #e5e7eb; padding-top: 8px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Client Credit Score Report</h1>
        <p class="meta">{{ $organization->name ?? 'Organization' }} &middot; Generated {{ now()->format('d M Y H:i') }}</p>
    </div>

    <table style="border:none; margin-bottom: 12px;">
        <tr style="border:none;">
            <td style="border:none; width: 120px; vertical-align: top;">
                <div class="score-box {{ $report['band'] }}">
                    <div class="score-num">{{ $report['score'] }}</div>
                    <div>/100</div>
                </div>
            </td>
            <td style="border:none; vertical-align: top;">
                <strong style="font-size: 14px;">{{ $report['client']['display_name'] }}</strong><br>
                {{ $report['client']['client_number'] }} &middot; {{ ucfirst($report['client']['client_type']) }}<br>
                Band: <strong>{{ $report['band_label'] }}</strong>
                <span class="badge {{ $report['passes'] ? 'badge-pass' : 'badge-fail' }}">{{ $report['passes'] ? 'Eligible' : 'Not Eligible' }}</span><br>
                Phone: {{ $report['client']['phone_number'] ?? '—' }}
            </td>
        </tr>
    </table>

    <h2>Score Summary</h2>
    <table class="info-grid">
        <tr>
            <td><span class="label">Total loans:</span> {{ $report['history']['total_loans'] }}</td>
            <td><span class="label">Open contracts:</span> {{ count($report['loans']['open']) }}</td>
        </tr>
        <tr>
            <td><span class="label">Closed contracts:</span> {{ count($report['loans']['closed']) }}</td>
            <td><span class="label">Max arrears days:</span> {{ $report['history']['max_overdue_days'] }}</td>
        </tr>
        <tr>
            <td><span class="label">Overdue loans:</span> {{ $report['history']['overdue_loans'] }}</td>
            <td><span class="label">Written off:</span> {{ $report['history']['written_off_loans'] }}</td>
        </tr>
        <tr>
            <td><span class="label">Total borrowed:</span> TZS {{ number_format($report['history']['total_borrowed'], 2) }}</td>
            <td><span class="label">Total repaid:</span> TZS {{ number_format($report['history']['total_repaid'], 2) }}</td>
        </tr>
    </table>

    <h2>Score Factors</h2>
    <table>
        <thead><tr><th>Factor</th><th>Score</th></tr></thead>
        <tbody>
            <tr><td>Repayment History</td><td>{{ $report['factors']['repayment_history'] }}/100</td></tr>
            <tr><td>Arrears Record</td><td>{{ $report['factors']['arrears'] }}/100</td></tr>
            <tr><td>Profile &amp; KYC</td><td>{{ $report['factors']['profile'] }}/100</td></tr>
            <tr><td>Portfolio Behavior</td><td>{{ $report['factors']['portfolio'] }}/100</td></tr>
        </tbody>
    </table>

    <h2>Why This Score?</h2>
    <ul>
        @foreach($report['score_explanation'] as $line)
            <li>{{ $line }}</li>
        @endforeach
    </ul>

    @if(count($report['negative_contributors']) > 0)
        <h2>Loans That Hurt the Score</h2>
        <table>
            <thead>
                <tr>
                    <th>Loan</th>
                    <th>Product</th>
                    <th>Status</th>
                    <th>Arrears Days</th>
                    <th>Impact</th>
                    <th>Reason</th>
                </tr>
            </thead>
            <tbody>
                @foreach($report['negative_contributors'] as $loan)
                    <tr>
                        <td>{{ $loan['loan_number'] }}</td>
                        <td>{{ $loan['product_name'] }}</td>
                        <td>{{ ucfirst($loan['status']) }}</td>
                        <td>{{ $loan['overdue_days'] }}</td>
                        <td>{{ $loan['score_impact'] }}</td>
                        <td>{{ implode(' ', $loan['impact_reasons']) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <h2>KYC &amp; Profile</h2>
    <table class="info-grid">
        <tr>
            <td><span class="label">KYC status:</span> {{ ucfirst($report['kyc']['status'] ?? 'N/A') }}</td>
            <td><span class="label">Verified:</span> {{ $report['kyc']['verified'] ? 'Yes' : 'No' }}</td>
        </tr>
        <tr>
            <td><span class="label">Verification date:</span> {{ $report['kyc']['verification_date'] ?? '—' }}</td>
            <td><span class="label">Verified by:</span> {{ $report['kyc']['verified_by'] ?? '—' }}</td>
        </tr>
        <tr>
            <td><span class="label">National ID:</span> {{ $report['kyc']['national_id'] ?? '—' }}</td>
            <td><span class="label">Passport:</span> {{ $report['kyc']['passport_number'] ?? '—' }}</td>
        </tr>
        <tr>
            <td><span class="label">Date of birth:</span> {{ $report['kyc']['date_of_birth'] ?? '—' }}</td>
            <td><span class="label">Gender:</span> {{ ucfirst($report['kyc']['gender'] ?? '—') }}</td>
        </tr>
        <tr>
            <td colspan="2"><span class="label">Address:</span> {{ trim(($report['kyc']['physical_address'] ?? '') . ', ' . ($report['kyc']['city'] ?? '') . ' ' . ($report['kyc']['region'] ?? '')) ?: '—' }}</td>
        </tr>
        <tr>
            <td><span class="label">Business reg. no.:</span> {{ $report['kyc']['business_registration_number'] ?? '—' }}</td>
            <td><span class="label">Client status:</span> {{ ucfirst($report['client']['status']) }}</td>
        </tr>
        @if($report['kyc']['kyc_notes'])
        <tr>
            <td colspan="2"><span class="label">KYC notes:</span> {{ $report['kyc']['kyc_notes'] }}</td>
        </tr>
        @endif
    </table>

    @if(count($report['kyc']['documents']) > 0)
        <h2>KYC Documents</h2>
        <table>
            <thead><tr><th>Type</th><th>Name</th><th>Status</th><th>Uploaded</th></tr></thead>
            <tbody>
                @foreach($report['kyc']['documents'] as $doc)
                    <tr>
                        <td>{{ ucfirst(str_replace('_', ' ', $doc['type'])) }}</td>
                        <td>{{ $doc['name'] }}</td>
                        <td>{{ ucfirst($doc['status']) }}</td>
                        <td>{{ $doc['uploaded_at'] ?? '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <h2>Score Trend ({{ $report['score_trend']['year'] }})</h2>
    <p class="meta">Trend: {{ ucfirst($report['score_trend']['trend_direction']) }} ({{ $report['score_trend']['trend_delta'] >= 0 ? '+' : '' }}{{ $report['score_trend']['trend_delta'] }} points year-to-date)</p>
    <table>
        <thead><tr><th>Month</th><th>Score</th><th>Band</th><th>Change</th></tr></thead>
        <tbody>
            @foreach($report['score_trend']['months'] as $month)
                <tr>
                    <td>{{ $month['label_full'] ?? $month['label'] }}</td>
                    <td>{{ $month['score'] ?? '—' }}</td>
                    <td>{{ $month['band_label'] ?? '—' }}</td>
                    <td>
                        @if($month['change'] !== null)
                            {{ $month['change'] >= 0 ? '+' : '' }}{{ $month['change'] }}
                        @else
                            —
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <h2>Payment Trend ({{ $report['payment_trend']['year'] }})</h2>
    <p class="meta">Total payments: TZS {{ number_format($report['payment_trend']['total'], 2) }}</p>
    <table>
        <thead><tr><th>Month</th><th>Amount Paid (TZS)</th></tr></thead>
        <tbody>
            @foreach($report['payment_trend']['months'] as $month)
                <tr>
                    <td>{{ $month['label_full'] }}</td>
                    <td>{{ number_format($month['amount'], 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    @if(count($report['products_taken']) > 0)
        <h2>Loan Products Taken</h2>
        <table>
            <thead><tr><th>Product</th><th>Code</th><th>Times</th><th>Total Amount</th><th>Last Taken</th></tr></thead>
            <tbody>
                @foreach($report['products_taken'] as $product)
                    <tr>
                        <td>{{ $product['product_name'] }}</td>
                        <td>{{ $product['product_code'] }}</td>
                        <td>{{ $product['times_taken'] }}</td>
                        <td>TZS {{ number_format($product['total_amount'], 2) }}</td>
                        <td>{{ $product['last_taken'] ?? '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    @if(count($report['loans']['open']) > 0)
        <h2>Open Contracts</h2>
        <table>
            <thead><tr><th>Loan</th><th>Product</th><th>Amount</th><th>Outstanding</th><th>Arrears Days</th><th>Status</th></tr></thead>
            <tbody>
                @foreach($report['loans']['open'] as $loan)
                    <tr>
                        <td>{{ $loan['loan_number'] }}</td>
                        <td>{{ $loan['product_name'] }}</td>
                        <td>TZS {{ number_format($loan['loan_amount'], 2) }}</td>
                        <td>TZS {{ number_format($loan['outstanding_balance'], 2) }}</td>
                        <td>{{ $loan['overdue_days'] }}</td>
                        <td>{{ ucfirst($loan['status']) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    @if(count($report['loans']['previous_completed']) > 0)
        <h2>Previous Contracts — Completed</h2>
        <table>
            <thead><tr><th>Loan</th><th>Product</th><th>Amount</th><th>Repaid</th><th>Arrears Days</th><th>Applied</th><th>Closed</th></tr></thead>
            <tbody>
                @foreach($report['loans']['previous_completed'] as $loan)
                    <tr>
                        <td>{{ $loan['loan_number'] }}</td>
                        <td>{{ $loan['product_name'] }}</td>
                        <td>TZS {{ number_format($loan['loan_amount'], 2) }}</td>
                        <td>TZS {{ number_format($loan['paid_amount'], 2) }}</td>
                        <td>{{ $loan['overdue_days'] }}</td>
                        <td>{{ $loan['application_date'] ?? '—' }}</td>
                        <td>{{ $loan['closure_date'] ?? '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    @if(count($report['loans']['previous_other']) > 0)
        <h2>Previous Contracts — Written Off / Rejected</h2>
        <table>
            <thead><tr><th>Loan</th><th>Product</th><th>Amount</th><th>Arrears Days</th><th>Status</th><th>Closed</th></tr></thead>
            <tbody>
                @foreach($report['loans']['previous_other'] as $loan)
                    <tr>
                        <td>{{ $loan['loan_number'] }}</td>
                        <td>{{ $loan['product_name'] }}</td>
                        <td>TZS {{ number_format($loan['loan_amount'], 2) }}</td>
                        <td>{{ $loan['overdue_days'] }}</td>
                        <td>{{ ucfirst($loan['status']) }}</td>
                        <td>{{ $loan['closure_date'] ?? $loan['write_off_date'] ?? '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <div class="footer">
        Confidential credit assessment report. Score calculated at {{ $report['scored_at'] }}.
    </div>
</body>
</html>
