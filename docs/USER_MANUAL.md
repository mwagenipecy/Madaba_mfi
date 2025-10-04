# Microfinance Management System - User Manual

## Table of Contents
1. [System Overview](#system-overview)
2. [Getting Started](#getting-started)
3. [User Roles & Permissions](#user-roles--permissions)
4. [Organization Management](#organization-management)
5. [Account Management](#account-management)
6. [Branch Management](#branch-management)
7. [Client Management](#client-management)
8. [Loan Product Management](#loan-product-management)
9. [Loan Application Process](#loan-application-process)
10. [Loan Disbursement](#loan-disbursement)
11. [Loan Repayments](#loan-repayments)
12. [Reports & Analytics](#reports--analytics)
13. [System Administration](#system-administration)
14. [Troubleshooting](#troubleshooting)

---

## System Overview

The Microfinance Management System is a comprehensive platform designed to manage all aspects of microfinance operations including:

- **Organization & Branch Management**: Multi-tenant architecture supporting multiple organizations and branches
- **Client Management**: Complete client lifecycle from registration to loan servicing
- **Loan Management**: End-to-end loan processing from application to completion
- **Accounting System**: Double-entry bookkeeping with real-time balance tracking
- **Reporting**: Comprehensive financial and operational reports
- **User Management**: Role-based access control with multiple permission levels

### Key Features
- ✅ Multi-organization support
- ✅ Branch-based operations
- ✅ Real-time accounting
- ✅ Automated loan calculations
- ✅ Comprehensive reporting
- ✅ Role-based security
- ✅ Mobile-responsive interface

---

## Getting Started

### System Requirements
- **Web Browser**: Chrome 90+, Firefox 88+, Safari 14+, Edge 90+
- **Internet Connection**: Stable internet connection required
- **Device**: Desktop, tablet, or mobile device

### First-Time Login
1. Navigate to your organization's login URL
2. Enter your credentials provided by your system administrator
3. Complete the password setup if prompted
4. Review and accept the terms of service

### Dashboard Overview
Upon login, you'll see the main dashboard displaying:
- **Key Performance Indicators (KPIs)**
- **Recent Activities**
- **Pending Tasks**
- **Quick Action Buttons**
- **System Notifications**

---

## User Roles & Permissions

### Role Hierarchy

#### 1. Super Admin
- **Access**: System-wide access across all organizations
- **Permissions**:
  - Create/manage organizations
  - System configuration
  - User management across organizations
  - System monitoring and maintenance

#### 2. Admin (Organization Level)
- **Access**: Full access within assigned organization
- **Permissions**:
  - Manage organization settings
  - Create/manage branches
  - User management within organization
  - View all reports and analytics
  - Approve high-value loans

#### 3. Manager
- **Access**: Branch-level management
- **Permissions**:
  - Manage branch operations
  - Supervise loan officers
  - Approve loans within limits
  - Generate branch reports

#### 4. Loan Officer
- **Access**: Client-facing operations
- **Permissions**:
  - Register new clients
  - Process loan applications
  - Collect loan repayments
  - Update client information
  - Generate client reports

#### 5. Accountant
- **Access**: Financial operations
- **Permissions**:
  - Manage accounts and transactions
  - Process account recharges
  - Reconcile accounts
  - Generate financial reports

#### 6. Cashier
- **Access**: Cash operations
- **Permissions**:
  - Process cash transactions
  - Handle loan disbursements
  - Collect loan repayments
  - Maintain cash register

#### 7. Field Agent
- **Access**: Mobile operations
- **Permissions**:
  - Client registration in field
  - Loan application collection
  - Payment collection
  - Basic client updates

---

## Organization Management

### Creating an Organization
1. Navigate to **Admin** → **Organizations** → **Create New**
2. Fill in organization details:
   - **Organization Name**: Full legal name
   - **Registration Number**: Official registration number
   - **License Number**: Operating license number
   - **Contact Information**: Address, phone, email
   - **Authorized Capital**: Initial capital amount
   - **Incorporation Date**: Date of establishment
3. Upload organization logo (optional)
4. Set organization status to "Active"
5. Save and submit for approval

### Organization Settings
Access via **Settings** → **Organization Settings**:
- **General Information**: Update basic details
- **Contact Information**: Modify address and contact details
- **Financial Settings**: Configure currency, fiscal year
- **Operational Settings**: Set working hours, holidays
- **Security Settings**: Configure password policies, session timeouts

---

## Account Management

### Account Types
The system supports five main account categories:

#### 1. Asset Accounts
- **Cash Accounts**: Physical cash and bank accounts
- **Loan Portfolio**: Outstanding loan amounts
- **Fixed Assets**: Equipment, furniture, vehicles
- **Accounts Receivable**: Money owed by clients

#### 2. Liability Accounts
- **Accounts Payable**: Money owed to suppliers
- **Accrued Expenses**: Unpaid expenses
- **Client Deposits**: Money held for clients

#### 3. Equity Accounts
- **Owner's Capital**: Initial investment
- **Retained Earnings**: Accumulated profits
- **Reserves**: Set-aside funds

#### 4. Income Accounts
- **Interest Income**: Revenue from loans
- **Service Fees**: Processing and other fees
- **Other Income**: Miscellaneous revenue

#### 5. Expense Accounts
- **Operating Expenses**: Daily operational costs
- **Administrative Expenses**: Office and management costs
- **Interest Expenses**: Cost of borrowed funds

### Creating Accounts
1. Navigate to **Accounts** → **Create Account**
2. Select account type and category
3. Enter account details:
   - **Account Name**: Descriptive name
   - **Account Number**: Unique identifier
   - **Opening Balance**: Initial amount
   - **Currency**: Account currency
4. Set account status to "Active"
5. Save account

### Account Recharge
To add funds to an account:
1. Go to **Accounts** → **Recharge Account**
2. Select target account
3. Enter recharge amount and method
4. Provide reference number and description
5. Submit for approval
6. Complete transaction once approved

---

## Branch Management

### Creating a Branch
1. Navigate to **Admin** → **Branches** → **Create Branch**
2. Fill in branch details:
   - **Branch Name**: Official branch name
   - **Branch Code**: Unique identifier
   - **Address**: Complete physical address
   - **Manager**: Assign branch manager
   - **Contact Information**: Phone and email
3. Set branch status to "Active"
4. Save branch

### Branch Operations
Each branch can:
- Manage its own client portfolio
- Process loans within approved limits
- Maintain separate cash accounts
- Generate branch-specific reports
- Operate independently with central oversight

---

## Client Management

### Client Registration
1. Navigate to **Clients** → **Register New Client**
2. Fill in personal information:
   - **Full Name**: First and last name
   - **Date of Birth**: For age verification
   - **Gender**: Male/Female/Other
   - **Marital Status**: Single/Married/Divorced/Widowed
   - **National ID**: Government-issued identification
   - **Phone Number**: Primary contact number
   - **Email Address**: For communications
   - **Address**: Complete residential address
3. Add employment information:
   - **Occupation**: Current job or business
   - **Monthly Income**: Average monthly earnings
   - **Employer**: Company or business name
4. Upload required documents:
   - National ID copy
   - Proof of income
   - Address verification
5. Submit for approval

### Client Profile Management
- **Personal Information**: Update contact details and address
- **Employment Information**: Modify job and income details
- **Document Management**: Upload and update required documents
- **Loan History**: View all past and current loans
- **Payment History**: Track all transactions
- **Credit Score**: Monitor client's creditworthiness

### Client Search and Filtering
Use the search functionality to find clients by:
- Client name or ID
- Phone number
- National ID
- Branch location
- Loan status
- Registration date

---

## Loan Product Management

### Creating Loan Products
1. Navigate to **Loan Products** → **Create Product**
2. Define product parameters:
   - **Product Name**: Descriptive name
   - **Product Code**: Unique identifier
   - **Description**: Detailed product information
   - **Interest Rate**: Annual percentage rate
   - **Interest Calculation**: Flat or Reducing balance
   - **Loan Amount Range**: Minimum and maximum limits
   - **Loan Tenure**: Minimum and maximum duration
   - **Repayment Frequency**: Daily/Weekly/Monthly/Quarterly
   - **Processing Fees**: Upfront charges
   - **Late Fees**: Penalty for overdue payments
3. Set eligibility criteria:
   - Age requirements
   - Income thresholds
   - Credit score minimums
   - Required documents
4. Configure accounting accounts:
   - Disbursement account
   - Collection account
   - Interest revenue account
   - Principal tracking account
5. Set product status to "Active"
6. Save product

### Loan Product Categories
- **Business Loans**: For income-generating activities
- **Personal Loans**: For personal expenses
- **Emergency Loans**: Quick access to funds
- **Agricultural Loans**: For farming activities
- **Group Loans**: For community-based lending

---

## Loan Application Process

### Step 1: Client Selection
1. Navigate to **Loans** → **New Application**
2. Search and select existing client
3. Verify client information and eligibility
4. Review client's loan history and credit score

### Step 2: Loan Details
1. Select appropriate loan product
2. Enter loan amount (within product limits)
3. Choose loan tenure
4. Review interest rate and fees
5. Calculate monthly payment amount
6. Set disbursement date

### Step 3: Document Collection
Upload required documents:
- **Application Form**: Completed loan application
- **Income Proof**: Recent salary slips or business records
- **Bank Statements**: Recent account statements
- **Collateral Documents**: If applicable
- **Guarantor Information**: If required

### Step 4: Assessment and Approval
1. **Credit Assessment**: Review client's creditworthiness
2. **Income Verification**: Confirm repayment capacity
3. **Collateral Evaluation**: Assess security (if applicable)
4. **Loan Committee Review**: Internal approval process
5. **Final Approval**: Authorized personnel approval

### Step 5: Loan Documentation
1. Generate loan agreement
2. Prepare disbursement schedule
3. Create payment reminders
4. Set up automatic notifications

---

## Loan Disbursement

### Pre-Disbursement Checklist
- [ ] Loan approved by authorized personnel
- [ ] All required documents collected
- [ ] Client information verified
- [ ] Collateral registered (if applicable)
- [ ] Insurance coverage confirmed (if required)

### Disbursement Process
1. Navigate to **Loans** → **Pending Disbursements**
2. Select approved loan
3. Verify disbursement amount
4. Choose disbursement method:
   - **Cash**: Direct cash payment
   - **Bank Transfer**: Electronic transfer
   - **Cheque**: Physical cheque payment
5. Process disbursement transaction
6. Update loan status to "Disbursed"
7. Generate disbursement receipt
8. Send confirmation to client

### Post-Disbursement Activities
- Create loan schedule
- Set up payment reminders
- Update client records
- Process accounting entries
- Send welcome communication

---

## Loan Repayments

### Payment Collection Methods

#### 1. Cash Payments
1. Navigate to **Repayments** → **Collect Payment**
2. Search for client or loan
3. Verify payment amount
4. Accept cash payment
5. Issue receipt
6. Update loan schedule

#### 2. Bank Transfers
1. Monitor incoming transfers
2. Match payments to loans
3. Verify transfer details
4. Process payment allocation
5. Send confirmation to client

#### 3. Mobile Money
1. Receive mobile money payments
2. Verify transaction details
3. Allocate to appropriate loan
4. Update payment records

### Payment Allocation
When processing payments, the system automatically allocates funds in this order:
1. **Late Fees**: Outstanding penalty charges
2. **Interest**: Accrued interest payments
3. **Principal**: Loan principal amount

### Partial Payments
- Record partial payments accurately
- Update loan schedule accordingly
- Maintain payment history
- Send updated statements to clients

### Overdue Management
- **Day 1-30**: Send gentle reminders
- **Day 31-60**: Increase follow-up frequency
- **Day 61-90**: Formal demand letters
- **Day 90+**: Consider legal action

---

## Reports & Analytics

### Financial Reports

#### 1. Balance Sheet
- **Assets**: Cash, loans, fixed assets
- **Liabilities**: Payables, accrued expenses
- **Equity**: Capital, retained earnings
- **Period**: Monthly, quarterly, annually

#### 2. Income Statement
- **Revenue**: Interest income, fees
- **Expenses**: Operating, administrative
- **Net Profit**: Bottom line results
- **Trends**: Period-over-period analysis

#### 3. Cash Flow Statement
- **Operating Activities**: Core business cash flows
- **Investing Activities**: Asset purchases/sales
- **Financing Activities**: Capital changes

### Operational Reports

#### 1. Loan Portfolio Report
- Total outstanding loans
- Loan distribution by product
- Performance by branch
- Risk assessment metrics

#### 2. Client Reports
- Client demographics
- Registration trends
- Geographic distribution
- Credit score analysis

#### 3. Collection Reports
- Payment collection rates
- Overdue loan analysis
- Recovery performance
- Collection efficiency metrics

### Custom Reports
- Create custom reports using filters
- Export data in multiple formats
- Schedule automated report generation
- Share reports with stakeholders

---

## System Administration

### User Management
1. **Create Users**: Add new system users
2. **Assign Roles**: Set appropriate permissions
3. **Manage Access**: Control system access
4. **Monitor Activity**: Track user actions
5. **Reset Passwords**: Help users regain access

### System Configuration
- **Organization Settings**: Configure organizational parameters
- **Loan Settings**: Set global loan parameters
- **Accounting Settings**: Configure chart of accounts
- **Notification Settings**: Set up automated communications
- **Security Settings**: Configure access controls

### Data Management
- **Backup**: Regular data backups
- **Restore**: Data recovery procedures
- **Archive**: Historical data management
- **Export**: Data export capabilities
- **Import**: Bulk data import tools

---

## Troubleshooting

### Common Issues

#### Login Problems
**Issue**: Cannot log in to system
**Solutions**:
1. Verify username and password
2. Check internet connection
3. Clear browser cache
4. Contact system administrator
5. Reset password if needed

#### Payment Processing Errors
**Issue**: Payment not recorded
**Solutions**:
1. Verify payment amount
2. Check account balances
3. Review transaction logs
4. Contact support team
5. Process manual correction

#### Report Generation Issues
**Issue**: Reports not generating
**Solutions**:
1. Check date ranges
2. Verify user permissions
3. Clear browser cache
4. Try different browser
5. Contact technical support

#### Performance Issues
**Issue**: System running slowly
**Solutions**:
1. Close unnecessary browser tabs
2. Check internet connection
3. Clear browser cache
4. Restart browser
5. Contact technical support

### Error Messages

#### "Access Denied"
- Check user permissions
- Verify role assignments
- Contact administrator

#### "Insufficient Funds"
- Verify account balances
- Check pending transactions
- Process account recharge

#### "Loan Limit Exceeded"
- Review loan product limits
- Check client eligibility
- Consider product upgrade

### Support Contacts
- **Technical Support**: support@yourmfi.com
- **Phone Support**: +255 XXX XXX XXX
- **Help Desk**: Available 24/7
- **Training**: training@yourmfi.com

---

## Best Practices

### Security
- Use strong passwords
- Log out when finished
- Don't share login credentials
- Report suspicious activity
- Keep software updated

### Data Accuracy
- Double-check all entries
- Verify client information
- Maintain document integrity
- Regular data validation
- Backup important data

### Customer Service
- Be professional and courteous
- Listen to client concerns
- Provide clear explanations
- Follow up on commitments
- Maintain confidentiality

### System Usage
- Regular system updates
- Proper training for users
- Consistent data entry
- Regular report reviews
- Performance monitoring

---

## Appendices

### Appendix A: Keyboard Shortcuts
- **Ctrl + S**: Save current form
- **Ctrl + N**: New record
- **Ctrl + F**: Search/Find
- **Ctrl + P**: Print current page
- **Esc**: Cancel current operation

### Appendix B: File Formats
- **PDF**: Reports and documents
- **Excel**: Data exports
- **CSV**: Bulk data imports
- **JPG/PNG**: Document images

### Appendix C: System Limits
- **Maximum file size**: 10MB per upload
- **Session timeout**: 30 minutes of inactivity
- **Password requirements**: 8+ characters, mixed case, numbers
- **Loan amount limits**: Varies by product and user role

---

*This user manual is regularly updated. For the latest version, please visit the system help section or contact your system administrator.*

**Version**: 1.0  
**Last Updated**: October 2025  
**Next Review**: January 2026
