# Accounting System Guide

## Overview

This document provides a comprehensive guide to the accounting system implemented in the Madaba MFI system. The system follows proper double-entry bookkeeping principles and ensures accurate financial tracking for microfinance operations.

## Core Accounting Principles

### 1. Double-Entry Bookkeeping
Every financial transaction affects at least two accounts, with total debits always equaling total credits.

### 2. Accounting Equation
**Assets = Liabilities + Equity**

This fundamental equation must always balance in the system.

### 3. Account Categories

#### Assets (Debit Balance)
- **Cash and Cash Equivalents**: Physical cash, petty cash
- **Bank Accounts**: Bank deposits and checking accounts
- **Loan Portfolio**: Outstanding loan amounts and receivables
- **Accounts Receivable**: Amounts owed by customers
- **Investments**: Investment accounts and securities
- **Fixed Assets**: Equipment, furniture, vehicles, buildings
- **Prepaid Expenses**: Expenses paid in advance

#### Liabilities (Credit Balance)
- **Customer Deposits**: Customer savings deposits and balances
- **Accounts Payable**: Amounts owed to suppliers and vendors
- **Accrued Expenses**: Expenses incurred but not yet paid
- **Loan Payable**: Borrowings from external sources
- **Deferred Revenue**: Revenue received but not yet earned

#### Equity (Credit Balance)
- **Owner Capital**: Owner contributions and capital investments
- **Retained Earnings**: Accumulated profits from previous periods
- **Current Year Earnings**: Current year profit or loss

#### Income/Revenue (Credit Balance)
- **Interest Income**: Interest earned on loans and investments
- **Fee Income**: Processing fees, late fees, and other charges
- **Investment Income**: Dividends and capital gains
- **Other Income**: Miscellaneous income sources

#### Expenses (Debit Balance)
- **Operating Expenses**: General operating expenses
- **Personnel Expenses**: Salaries, wages, and benefits
- **Administrative Expenses**: Administrative and overhead costs
- **Bad Debt Expense**: Loan write-offs and bad debt provisions
- **Interest Expense**: Interest paid on borrowings
- **Depreciation Expense**: Depreciation of fixed assets

## Money Flow Architecture

### Entry Points (Capital Injection)
1. **Account Recharge**: Money comes into main organization accounts (Equity)
2. **Customer Deposits**: Money from customers (Liability)
3. **Investment Income**: Returns from investments (Income)

### Exit Points (Money Outflow)
1. **Loan Disbursements**: Money goes out to borrowers (Asset creation)
2. **Expense Payments**: Operational expenses (Expense)
3. **Branch Distribution**: Money distributed to branches (Asset transfer)

### Flow Process
```
Capital Entry → Main Accounts (Equity) → Branch Distribution → Loan Disbursements
                ↓
            Customer Deposits (Liability) → Loan Portfolio (Asset)
                ↓
            Loan Repayments → Collection Accounts → Interest Income
```

## Transaction Processing

### 1. Loan Disbursement
**Double Entry:**
- **Debit**: Loan Portfolio (Asset increases)
- **Credit**: Customer Deposits (Liability decreases - money going out)

**Accounting Impact:**
- Assets increase (new loan receivable)
- Liabilities decrease (money paid out)
- Net effect: No change to equity

### 2. Loan Repayment
**Double Entry:**
- **Debit**: Collection Account (Cash received - Asset increases)
- **Credit**: Loan Portfolio (Principal portion - Asset decreases)
- **Credit**: Interest Income (Interest portion - Income increases)

**Accounting Impact:**
- Cash assets increase
- Loan portfolio assets decrease
- Income increases
- Net effect: Equity increases

### 3. Expense Payment
**Double Entry:**
- **Debit**: Expense Account (Expense increases)
- **Credit**: Payment Account (Asset decreases - money going out)

**Accounting Impact:**
- Expenses increase
- Assets decrease
- Net effect: Equity decreases

### 4. Fund Transfer
**Double Entry:**
- **Debit**: Destination Account (Asset/Liability increases)
- **Credit**: Source Account (Asset/Liability decreases)

**Accounting Impact:**
- No net change to total assets/liabilities
- Internal transfer only

### 5. Account Recharge (Capital Injection)
**Double Entry:**
- **Credit**: Main Account (Equity increases)
- **Debit**: Distribution to Branches (Equity decreases)

**Accounting Impact:**
- Equity increases (capital injection)
- Internal distribution to branches

## Key Services

### AccountingService
Central service for all accounting operations:
- `recordLoanDisbursement()`: Records loan disbursement entries
- `recordLoanRepayment()`: Records loan repayment entries
- `recordExpensePayment()`: Records expense payment entries
- `recordFundTransfer()`: Records fund transfer entries
- `recordAccountRecharge()`: Records account recharge entries
- `recordLoanWriteOff()`: Records loan write-off entries
- `calculateAccountBalance()`: Calculates accurate account balances
- `getFinancialPosition()`: Gets financial position summary
- `validateAccountingEquation()`: Validates accounting equation balance

## Validation and Maintenance

### Validation Commands
```bash
# Validate accounting system
php artisan accounting:validate

# Fix accounting issues
php artisan accounting:fix

# Dry run to see what would be fixed
php artisan accounting:fix --dry-run
```

### Validation Checks
1. **Accounting Equation**: Assets = Liabilities + Equity
2. **Account Balances**: Consistency between ledger and account records
3. **General Ledger**: No duplicate transaction IDs, valid references
4. **Orphaned Transactions**: No transactions referencing deleted records
5. **Loan Accounting**: Proper account configurations and balances

## Financial Position Calculation

### Balance Sheet Structure
```
ASSETS
├── Current Assets
│   ├── Cash and Cash Equivalents
│   ├── Bank Accounts
│   └── Accounts Receivable
├── Fixed Assets
│   ├── Equipment
│   ├── Vehicles
│   └── Buildings
└── Other Assets
    └── Investments

LIABILITIES
├── Current Liabilities
│   ├── Customer Deposits
│   ├── Accounts Payable
│   └── Accrued Expenses
└── Long-term Liabilities
    └── Loan Payable

EQUITY
├── Owner Capital
├── Retained Earnings
└── Current Year Earnings
```

### Key Financial Ratios
1. **Liquidity Ratio**: Current Assets / Current Liabilities
2. **Asset Quality**: Performing Loans / Total Loan Portfolio
3. **Capital Adequacy**: Equity / Total Assets
4. **Profitability**: Net Income / Total Assets

## Best Practices

### 1. Transaction Processing
- Always use the AccountingService for financial transactions
- Ensure proper debit/credit logic based on account types
- Maintain transaction audit trails
- Validate account balances after each transaction

### 2. Account Management
- Use proper account types and categories
- Maintain parent-child account relationships
- Regular balance reconciliation
- Proper account mapping for loan products

### 3. Error Handling
- Validate accounting equation after major operations
- Check for orphaned transactions regularly
- Monitor account balance consistency
- Maintain proper error logging

### 4. Reporting
- Generate balance sheets regularly
- Monitor key financial ratios
- Track loan portfolio performance
- Analyze expense patterns

## Troubleshooting

### Common Issues
1. **Unbalanced Equation**: Check for missing or incorrect ledger entries
2. **Negative Balances**: Verify transaction logic and account types
3. **Missing Transactions**: Ensure all operations use AccountingService
4. **Inconsistent Balances**: Recalculate from general ledger

### Resolution Steps
1. Run validation command to identify issues
2. Use fix command to resolve common problems
3. Manual review for complex issues
4. Verify accounting equation balance
5. Test transaction processing

## Security Considerations

### Access Control
- Restrict accounting operations to authorized users
- Maintain audit trails for all financial transactions
- Regular review of accounting entries
- Segregation of duties between operations and accounting

### Data Integrity
- Regular backups of financial data
- Validation of all financial inputs
- Consistent transaction processing
- Error handling and logging

This accounting system ensures accurate financial tracking, proper audit trails, and compliance with accounting standards for microfinance operations.
