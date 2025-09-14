<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Balance Sheet - {{ $balanceSheetData['organization']->name }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
        }
        .company-name {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .report-title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .report-date {
            font-size: 14px;
            color: #666;
        }
        .balance-sheet {
            display: table;
            width: 100%;
            border-collapse: collapse;
        }
        .balance-sheet-row {
            display: table-row;
        }
        .balance-sheet-cell {
            display: table-cell;
            padding: 8px 12px;
            border-bottom: 1px solid #ddd;
            vertical-align: top;
        }
        .balance-sheet-cell.left {
            width: 60%;
            text-align: left;
        }
        .balance-sheet-cell.right {
            width: 40%;
            text-align: right;
        }
        .section-header {
            background-color: #f5f5f5;
            font-weight: bold;
            font-size: 14px;
            padding: 10px 12px;
            border-bottom: 2px solid #333;
        }
        .subsection-header {
            font-weight: bold;
            padding-left: 20px;
            background-color: #f9f9f9;
        }
        .account-item {
            padding-left: 40px;
        }
        .total-line {
            font-weight: bold;
            border-top: 2px solid #333;
            border-bottom: 2px solid #333;
            background-color: #f0f0f0;
        }
        .grand-total {
            font-weight: bold;
            font-size: 14px;
            border-top: 3px solid #333;
            border-bottom: 3px solid #333;
            background-color: #e0e0e0;
        }
        .balance-verification {
            margin-top: 30px;
            padding: 20px;
            border: 2px solid #333;
            text-align: center;
            background-color: #f9f9f9;
        }
        .balanced {
            color: #28a745;
            font-weight: bold;
        }
        .not-balanced {
            color: #dc3545;
            font-weight: bold;
        }
        .amount {
            font-family: 'Courier New', monospace;
        }
        @media print {
            body { margin: 0; padding: 15px; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="company-name">{{ $balanceSheetData['organization']->name }}</div>
        @if($balanceSheetData['branch'])
            <div style="font-size: 16px; margin-bottom: 5px;">{{ $balanceSheetData['branch']->name }}</div>
        @endif
        <div class="report-title">Balance Sheet</div>
        <div class="report-date">As of {{ $balanceSheetData['as_of_date']->format('F d, Y') }}</div>
    </div>

    <!-- Balance Sheet Content -->
    <div class="balance-sheet">
        <!-- Assets Section -->
        <div class="balance-sheet-row">
            <div class="balance-sheet-cell left section-header">ASSETS</div>
            <div class="balance-sheet-cell right section-header"></div>
        </div>

        <!-- Current Assets -->
        @if(count($balanceSheetData['assets']['current_assets']) > 0)
            <div class="balance-sheet-row">
                <div class="balance-sheet-cell left subsection-header">Current Assets</div>
                <div class="balance-sheet-cell right"></div>
            </div>
            @foreach($balanceSheetData['assets']['current_assets'] as $asset)
                <div class="balance-sheet-row">
                    <div class="balance-sheet-cell left account-item">{{ $asset['type'] }}</div>
                    <div class="balance-sheet-cell right amount">{{ number_format($asset['total'], 2) }}</div>
                </div>
            @endforeach
            <div class="balance-sheet-row total-line">
                <div class="balance-sheet-cell left">Total Current Assets</div>
                <div class="balance-sheet-cell right amount">{{ number_format($balanceSheetData['assets']['current_assets_total'], 2) }}</div>
            </div>
        @endif

        <!-- Fixed Assets -->
        @if(count($balanceSheetData['assets']['fixed_assets']) > 0)
            <div class="balance-sheet-row">
                <div class="balance-sheet-cell left subsection-header">Fixed Assets</div>
                <div class="balance-sheet-cell right"></div>
            </div>
            @foreach($balanceSheetData['assets']['fixed_assets'] as $asset)
                <div class="balance-sheet-row">
                    <div class="balance-sheet-cell left account-item">{{ $asset['type'] }}</div>
                    <div class="balance-sheet-cell right amount">{{ number_format($asset['total'], 2) }}</div>
                </div>
            @endforeach
            <div class="balance-sheet-row total-line">
                <div class="balance-sheet-cell left">Total Fixed Assets</div>
                <div class="balance-sheet-cell right amount">{{ number_format($balanceSheetData['assets']['fixed_assets_total'], 2) }}</div>
            </div>
        @endif

        <!-- Other Assets -->
        @if(count($balanceSheetData['assets']['other_assets']) > 0)
            <div class="balance-sheet-row">
                <div class="balance-sheet-cell left subsection-header">Other Assets</div>
                <div class="balance-sheet-cell right"></div>
            </div>
            @foreach($balanceSheetData['assets']['other_assets'] as $asset)
                <div class="balance-sheet-row">
                    <div class="balance-sheet-cell left account-item">{{ $asset['type'] }}</div>
                    <div class="balance-sheet-cell right amount">{{ number_format($asset['total'], 2) }}</div>
                </div>
            @endforeach
            <div class="balance-sheet-row total-line">
                <div class="balance-sheet-cell left">Total Other Assets</div>
                <div class="balance-sheet-cell right amount">{{ number_format($balanceSheetData['assets']['other_assets_total'], 2) }}</div>
            </div>
        @endif

        <!-- Total Assets -->
        <div class="balance-sheet-row grand-total">
            <div class="balance-sheet-cell left">TOTAL ASSETS</div>
            <div class="balance-sheet-cell right amount">{{ number_format($balanceSheetData['totals']['total_assets'], 2) }}</div>
        </div>

        <!-- Liabilities & Equity Section -->
        <div class="balance-sheet-row">
            <div class="balance-sheet-cell left section-header">LIABILITIES & EQUITY</div>
            <div class="balance-sheet-cell right section-header"></div>
        </div>

        <!-- Current Liabilities -->
        @if(count($balanceSheetData['liabilities']['current_liabilities']) > 0)
            <div class="balance-sheet-row">
                <div class="balance-sheet-cell left subsection-header">Current Liabilities</div>
                <div class="balance-sheet-cell right"></div>
            </div>
            @foreach($balanceSheetData['liabilities']['current_liabilities'] as $liability)
                <div class="balance-sheet-row">
                    <div class="balance-sheet-cell left account-item">{{ $liability['type'] }}</div>
                    <div class="balance-sheet-cell right amount">{{ number_format($liability['total'], 2) }}</div>
                </div>
            @endforeach
            <div class="balance-sheet-row total-line">
                <div class="balance-sheet-cell left">Total Current Liabilities</div>
                <div class="balance-sheet-cell right amount">{{ number_format($balanceSheetData['liabilities']['current_liabilities_total'], 2) }}</div>
            </div>
        @endif

        <!-- Long-term Liabilities -->
        @if(count($balanceSheetData['liabilities']['long_term_liabilities']) > 0)
            <div class="balance-sheet-row">
                <div class="balance-sheet-cell left subsection-header">Long-term Liabilities</div>
                <div class="balance-sheet-cell right"></div>
            </div>
            @foreach($balanceSheetData['liabilities']['long_term_liabilities'] as $liability)
                <div class="balance-sheet-row">
                    <div class="balance-sheet-cell left account-item">{{ $liability['type'] }}</div>
                    <div class="balance-sheet-cell right amount">{{ number_format($liability['total'], 2) }}</div>
                </div>
            @endforeach
            <div class="balance-sheet-row total-line">
                <div class="balance-sheet-cell left">Total Long-term Liabilities</div>
                <div class="balance-sheet-cell right amount">{{ number_format($balanceSheetData['liabilities']['long_term_liabilities_total'], 2) }}</div>
            </div>
        @endif

        <!-- Total Liabilities -->
        <div class="balance-sheet-row total-line">
            <div class="balance-sheet-cell left">TOTAL LIABILITIES</div>
            <div class="balance-sheet-cell right amount">{{ number_format($balanceSheetData['totals']['total_liabilities'], 2) }}</div>
        </div>

        <!-- Owner Equity -->
        @if(count($balanceSheetData['equity']['owner_equity']) > 0)
            <div class="balance-sheet-row">
                <div class="balance-sheet-cell left subsection-header">Owner Equity</div>
                <div class="balance-sheet-cell right"></div>
            </div>
            @foreach($balanceSheetData['equity']['owner_equity'] as $equity)
                <div class="balance-sheet-row">
                    <div class="balance-sheet-cell left account-item">{{ $equity['type'] }}</div>
                    <div class="balance-sheet-cell right amount">{{ number_format($equity['total'], 2) }}</div>
                </div>
            @endforeach
            <div class="balance-sheet-row total-line">
                <div class="balance-sheet-cell left">Total Owner Equity</div>
                <div class="balance-sheet-cell right amount">{{ number_format($balanceSheetData['equity']['owner_equity_total'], 2) }}</div>
            </div>
        @endif

        <!-- Retained Earnings -->
        @if(count($balanceSheetData['equity']['retained_earnings']) > 0)
            <div class="balance-sheet-row">
                <div class="balance-sheet-cell left subsection-header">Retained Earnings</div>
                <div class="balance-sheet-cell right"></div>
            </div>
            @foreach($balanceSheetData['equity']['retained_earnings'] as $equity)
                <div class="balance-sheet-row">
                    <div class="balance-sheet-cell left account-item">{{ $equity['type'] }}</div>
                    <div class="balance-sheet-cell right amount">{{ number_format($equity['total'], 2) }}</div>
                </div>
            @endforeach
            <div class="balance-sheet-row total-line">
                <div class="balance-sheet-cell left">Total Retained Earnings</div>
                <div class="balance-sheet-cell right amount">{{ number_format($balanceSheetData['equity']['retained_earnings_total'], 2) }}</div>
            </div>
        @endif

        <!-- Total Equity -->
        <div class="balance-sheet-row total-line">
            <div class="balance-sheet-cell left">TOTAL EQUITY</div>
            <div class="balance-sheet-cell right amount">{{ number_format($balanceSheetData['totals']['total_equity'], 2) }}</div>
        </div>

        <!-- Total Liabilities & Equity -->
        <div class="balance-sheet-row grand-total">
            <div class="balance-sheet-cell left">TOTAL LIABILITIES & EQUITY</div>
            <div class="balance-sheet-cell right amount">{{ number_format($balanceSheetData['totals']['total_liabilities_and_equity'], 2) }}</div>
        </div>
    </div>

    <!-- Balance Verification -->
    <div class="balance-verification">
        @if($balanceSheetData['totals']['is_balanced'])
            <div class="balanced">
                ✓ Balance Sheet is Balanced
            </div>
        @else
            <div class="not-balanced">
                ✗ Balance Sheet is Not Balanced
            </div>
        @endif
        
        <div style="margin-top: 10px; font-size: 11px;">
            <div>Assets: {{ number_format($balanceSheetData['totals']['total_assets'], 2) }}</div>
            <div>Liabilities & Equity: {{ number_format($balanceSheetData['totals']['total_liabilities_and_equity'], 2) }}</div>
            @if(!$balanceSheetData['totals']['is_balanced'])
                <div style="color: #dc3545; font-weight: bold;">
                    Difference: {{ number_format($balanceSheetData['totals']['total_assets'] - $balanceSheetData['totals']['total_liabilities_and_equity'], 2) }}
                </div>
            @endif
        </div>
    </div>

    <!-- Print Instructions -->
    <div class="no-print" style="margin-top: 30px; padding: 20px; background-color: #f8f9fa; border: 1px solid #dee2e6; border-radius: 5px;">
        <h4>Print Instructions:</h4>
        <p>To print this balance sheet:</p>
        <ol>
            <li>Press <strong>Ctrl+P</strong> (Windows) or <strong>Cmd+P</strong> (Mac)</li>
            <li>Select your printer</li>
            <li>Choose "More settings" and set margins to "Minimum"</li>
            <li>Click "Print"</li>
        </ol>
        <p><strong>Note:</strong> This view is optimized for printing and will automatically format for A4 paper size.</p>
    </div>

    <script>
        // Auto-print when page loads (optional)
        // window.onload = function() { window.print(); }
    </script>
</body>
</html>
