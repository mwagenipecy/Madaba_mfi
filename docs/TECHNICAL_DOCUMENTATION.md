# Microfinance Management System - Technical Documentation

## Table of Contents
1. [System Architecture](#system-architecture)
2. [Technology Stack](#technology-stack)
3. [Database Schema](#database-schema)
4. [API Documentation](#api-documentation)
5. [Security Implementation](#security-implementation)
6. [Performance Optimization](#performance-optimization)
7. [Deployment Guide](#deployment-guide)
8. [Testing Framework](#testing-framework)
9. [Monitoring & Logging](#monitoring--logging)
10. [Troubleshooting Guide](#troubleshooting-guide)

---

## System Architecture

### Overview
The Microfinance Management System is built using Laravel 12.28.1 with PHP 8.4.4, following a modular, multi-tenant architecture designed for scalability and maintainability.

### Architecture Components

```
┌─────────────────────────────────────────────────────────────┐
│                    Presentation Layer                        │
├─────────────────────────────────────────────────────────────┤
│  Web Interface (Blade Templates)  │  API Endpoints (REST)   │
├─────────────────────────────────────────────────────────────┤
│                    Application Layer                         │
├─────────────────────────────────────────────────────────────┤
│  Controllers  │  Services  │  Livewire Components  │  Jobs  │
├─────────────────────────────────────────────────────────────┤
│                     Domain Layer                            │
├─────────────────────────────────────────────────────────────┤
│  Models  │  Repositories  │  Business Logic  │  Validators  │
├─────────────────────────────────────────────────────────────┤
│                   Infrastructure Layer                       │
├─────────────────────────────────────────────────────────────┤
│  Database (MySQL)  │  File Storage  │  Cache (Redis)  │  Queue │
└─────────────────────────────────────────────────────────────┘
```

### Key Design Patterns
- **Repository Pattern**: Data access abstraction
- **Service Layer**: Business logic encapsulation
- **Observer Pattern**: Event handling
- **Factory Pattern**: Object creation
- **Strategy Pattern**: Algorithm selection

---

## Technology Stack

### Backend Technologies
- **Framework**: Laravel 12.28.1
- **PHP Version**: 8.4.4
- **Database**: MySQL 8.0+
- **Cache**: Redis 6.0+
- **Queue**: Redis/Database
- **Search**: Laravel Scout (Elasticsearch)

### Frontend Technologies
- **CSS Framework**: Tailwind CSS 3.0+
- **JavaScript**: Alpine.js 3.0+
- **Build Tool**: Vite
- **Icons**: Heroicons
- **Charts**: Chart.js

### Development Tools
- **Version Control**: Git
- **Package Manager**: Composer (PHP), NPM (JavaScript)
- **Testing**: PHPUnit, Pest
- **Code Quality**: PHP CS Fixer, ESLint
- **Documentation**: Markdown

### Third-Party Integrations
- **Authentication**: Laravel Fortify
- **Team Management**: Laravel Jetstream
- **Email**: Laravel Mail
- **File Storage**: Laravel Filesystem
- **Payments**: Custom payment gateway integration

---

## Database Schema

### Core Tables

#### Organizations
```sql
CREATE TABLE organizations (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    registration_number VARCHAR(255) UNIQUE,
    license_number VARCHAR(255) UNIQUE,
    type ENUM('microfinance_bank', 'cooperative_society', 'ngo', 'credit_union', 'other'),
    email VARCHAR(255) UNIQUE NOT NULL,
    phone VARCHAR(255) NOT NULL,
    address TEXT NOT NULL,
    city VARCHAR(255) NOT NULL,
    state VARCHAR(255) NOT NULL,
    country VARCHAR(255) DEFAULT 'Tanzania',
    postal_code VARCHAR(255),
    authorized_capital DECIMAL(15,2),
    incorporation_date DATE,
    regulatory_info JSON,
    logo_path VARCHAR(255),
    status ENUM('active', 'inactive', 'suspended', 'pending_approval') DEFAULT 'pending_approval',
    description TEXT,
    settings JSON,
    approved_at TIMESTAMP NULL,
    approved_by BIGINT UNSIGNED NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    
    INDEX idx_organizations_status_type (status, type),
    INDEX idx_organizations_slug (slug),
    FOREIGN KEY (approved_by) REFERENCES users(id)
);
```

#### Users
```sql
CREATE TABLE users (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    organization_id BIGINT UNSIGNED NULL,
    branch_id BIGINT UNSIGNED NULL,
    first_name VARCHAR(255) NOT NULL,
    last_name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    email_verified_at TIMESTAMP NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(255),
    role ENUM('super_admin', 'admin', 'manager', 'loan_officer', 'accountant', 'cashier', 'field_agent') DEFAULT 'admin',
    status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
    employee_id VARCHAR(255),
    permissions JSON,
    profile_photo_path VARCHAR(255),
    last_login_at TIMESTAMP NULL,
    two_factor_secret TEXT,
    two_factor_recovery_codes TEXT,
    two_factor_confirmed_at TIMESTAMP NULL,
    remember_token VARCHAR(100),
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    
    INDEX idx_users_org_status (organization_id, status),
    INDEX idx_users_org_role (organization_id, role),
    FOREIGN KEY (organization_id) REFERENCES organizations(id),
    FOREIGN KEY (branch_id) REFERENCES branches(id)
);
```

#### Accounts
```sql
CREATE TABLE accounts (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    account_number VARCHAR(255) UNIQUE NOT NULL,
    account_type_id BIGINT UNSIGNED NOT NULL,
    organization_id BIGINT UNSIGNED NOT NULL,
    branch_id BIGINT UNSIGNED NULL,
    parent_account_id BIGINT UNSIGNED NULL,
    opening_balance DECIMAL(15,2) DEFAULT 0.00,
    current_balance DECIMAL(15,2) DEFAULT 0.00,
    currency VARCHAR(3) DEFAULT 'TZS',
    is_active BOOLEAN DEFAULT TRUE,
    last_transaction_date TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    
    INDEX idx_accounts_org_type (organization_id, account_type_id),
    INDEX idx_accounts_number (account_number),
    FOREIGN KEY (account_type_id) REFERENCES account_types(id),
    FOREIGN KEY (organization_id) REFERENCES organizations(id),
    FOREIGN KEY (branch_id) REFERENCES branches(id),
    FOREIGN KEY (parent_account_id) REFERENCES accounts(id)
);
```

#### Loans
```sql
CREATE TABLE loans (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    loan_number VARCHAR(255) UNIQUE NOT NULL,
    client_id BIGINT UNSIGNED NOT NULL,
    loan_product_id BIGINT UNSIGNED NOT NULL,
    organization_id BIGINT UNSIGNED NOT NULL,
    branch_id BIGINT UNSIGNED NULL,
    loan_officer_id BIGINT UNSIGNED NULL,
    loan_amount DECIMAL(15,2) NOT NULL,
    approved_amount DECIMAL(15,2) NULL,
    interest_rate DECIMAL(5,2) NOT NULL,
    interest_calculation_method ENUM('flat', 'reducing') DEFAULT 'flat',
    loan_tenure_months INTEGER NOT NULL,
    repayment_frequency ENUM('daily', 'weekly', 'monthly', 'quarterly') DEFAULT 'monthly',
    application_date DATE NOT NULL,
    approval_date DATE NULL,
    disbursement_date DATE NULL,
    first_payment_date DATE NULL,
    maturity_date DATE NULL,
    total_interest DECIMAL(15,2) NULL,
    total_amount DECIMAL(15,2) NULL,
    monthly_payment DECIMAL(15,2) NULL,
    processing_fee DECIMAL(15,2) DEFAULT 0.00,
    insurance_fee DECIMAL(15,2) DEFAULT 0.00,
    late_fee DECIMAL(15,2) DEFAULT 0.00,
    penalty_fee DECIMAL(15,2) DEFAULT 0.00,
    other_fees DECIMAL(15,2) DEFAULT 0.00,
    status ENUM('pending', 'under_review', 'approved', 'rejected', 'disbursed', 'active', 'overdue', 'completed', 'written_off', 'cancelled') DEFAULT 'pending',
    approval_status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    approved_by BIGINT UNSIGNED NULL,
    rejected_by BIGINT UNSIGNED NULL,
    approval_notes TEXT NULL,
    rejection_reason TEXT NULL,
    paid_amount DECIMAL(15,2) DEFAULT 0.00,
    outstanding_balance DECIMAL(15,2) NULL,
    overdue_amount DECIMAL(15,2) DEFAULT 0.00,
    overdue_days INTEGER DEFAULT 0,
    payments_made INTEGER DEFAULT 0,
    total_payments INTEGER NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    
    INDEX idx_loans_client (client_id),
    INDEX idx_loans_product (loan_product_id),
    INDEX idx_loans_org_branch_status (organization_id, branch_id, status),
    INDEX idx_loans_org_status_amount (organization_id, status, approved_amount),
    FOREIGN KEY (client_id) REFERENCES clients(id),
    FOREIGN KEY (loan_product_id) REFERENCES loan_products(id),
    FOREIGN KEY (organization_id) REFERENCES organizations(id),
    FOREIGN KEY (branch_id) REFERENCES branches(id),
    FOREIGN KEY (loan_officer_id) REFERENCES users(id)
);
```

### Performance Indexes

#### Loan Transactions
```sql
-- Optimized indexes for reporting queries
CREATE INDEX idx_loan_transactions_org_type_status_date ON loan_transactions (organization_id, transaction_type, status, transaction_date);
CREATE INDEX idx_loan_transactions_branch_type_status_date ON loan_transactions (branch_id, transaction_type, status, transaction_date);
CREATE INDEX idx_loan_transactions_type_status_date ON loan_transactions (transaction_type, status, transaction_date);
```

#### Loan Schedules
```sql
CREATE INDEX idx_loan_schedules_status_paid_date ON loan_schedules (status, paid_date);
CREATE INDEX idx_loan_schedules_loan_status_paid_date ON loan_schedules (loan_id, status, paid_date);
```

---

## API Documentation

### Authentication
All API endpoints require authentication using Laravel Sanctum tokens.

```http
Authorization: Bearer {token}
Content-Type: application/json
```

### Core Endpoints

#### Organizations
```http
GET    /api/organizations              # List organizations
POST   /api/organizations              # Create organization
GET    /api/organizations/{id}         # Get organization
PUT    /api/organizations/{id}         # Update organization
DELETE /api/organizations/{id}         # Delete organization
```

#### Loans
```http
GET    /api/loans                      # List loans
POST   /api/loans                      # Create loan
GET    /api/loans/{id}                 # Get loan
PUT    /api/loans/{id}                 # Update loan
DELETE /api/loans/{id}                 # Delete loan
POST   /api/loans/{id}/disburse        # Disburse loan
POST   /api/loans/{id}/repay           # Process repayment
```

#### Reports
```http
GET    /api/reports/balance-sheet      # Balance sheet
GET    /api/reports/income-statement   # Income statement
GET    /api/reports/loan-collections   # Loan collections
GET    /api/reports/portfolio-summary  # Portfolio summary
```

### Response Format
```json
{
    "success": true,
    "data": {
        // Response data
    },
    "message": "Operation completed successfully",
    "meta": {
        "pagination": {
            "current_page": 1,
            "per_page": 15,
            "total": 100,
            "last_page": 7
        }
    }
}
```

### Error Response
```json
{
    "success": false,
    "message": "Validation failed",
    "errors": {
        "field_name": ["Error message"]
    }
}
```

---

## Security Implementation

### Authentication & Authorization
- **Multi-factor Authentication**: TOTP-based 2FA
- **Role-based Access Control**: Granular permissions
- **Session Management**: Secure session handling
- **Password Policies**: Strong password requirements

### Data Protection
- **Encryption**: Data encryption at rest and in transit
- **Input Validation**: Comprehensive input sanitization
- **SQL Injection Prevention**: Parameterized queries
- **XSS Protection**: Output encoding
- **CSRF Protection**: Token-based protection

### Security Headers
```php
// Security middleware configuration
'headers' => [
    'X-Content-Type-Options' => 'nosniff',
    'X-Frame-Options' => 'DENY',
    'X-XSS-Protection' => '1; mode=block',
    'Strict-Transport-Security' => 'max-age=31536000; includeSubDomains',
    'Content-Security-Policy' => "default-src 'self'"
]
```

### Audit Logging
- **User Actions**: Track all user activities
- **Data Changes**: Log all data modifications
- **Security Events**: Monitor security-related events
- **System Events**: Track system operations

---

## Performance Optimization

### Database Optimization
- **Indexing Strategy**: Comprehensive index coverage
- **Query Optimization**: Efficient query patterns
- **Connection Pooling**: Database connection management
- **Read Replicas**: Distribute read operations

### Caching Strategy
```php
// Redis caching configuration
'cache' => [
    'default' => 'redis',
    'stores' => [
        'redis' => [
            'driver' => 'redis',
            'connection' => 'cache',
            'lock_connection' => 'default',
        ],
    ],
]
```

### Application Optimization
- **Lazy Loading**: Optimize model relationships
- **Eager Loading**: Prevent N+1 queries
- **Query Scoping**: Filter data efficiently
- **Pagination**: Limit result sets

### Frontend Optimization
- **Asset Minification**: Compress CSS/JS files
- **Image Optimization**: Optimize image assets
- **CDN Integration**: Distribute static assets
- **Browser Caching**: Leverage browser cache

---

## Deployment Guide

### Environment Setup

#### Production Environment
```bash
# Install dependencies
composer install --optimize-autoloader --no-dev
npm ci && npm run build

# Configure environment
cp .env.example .env
# Edit .env with production values

# Generate application key
php artisan key:generate

# Run migrations
php artisan migrate --force

# Cache configuration
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Set permissions
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

#### Docker Deployment
```dockerfile
FROM php:8.4-fpm-alpine

# Install system dependencies
RUN apk add --no-cache \
    nginx \
    mysql-client \
    redis \
    supervisor

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql

# Copy application code
COPY . /var/www/html
WORKDIR /var/www/html

# Install Composer dependencies
RUN composer install --optimize-autoloader --no-dev

# Build frontend assets
RUN npm ci && npm run build

# Configure Nginx
COPY docker/nginx.conf /etc/nginx/nginx.conf

# Start services
CMD ["supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
```

### Load Balancer Configuration
```nginx
upstream backend {
    server app1:9000;
    server app2:9000;
    server app3:9000;
}

server {
    listen 80;
    server_name yourdomain.com;
    
    location / {
        proxy_pass http://backend;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
    }
}
```

---

## Testing Framework

### Unit Testing
```php
// Example unit test
class LoanCalculationTest extends TestCase
{
    public function test_monthly_payment_calculation()
    {
        $loan = new Loan([
            'loan_amount' => 100000,
            'interest_rate' => 24.0,
            'loan_tenure_months' => 12
        ]);
        
        $payment = $loan->calculateMonthlyPayment();
        
        $this->assertEquals(9456.02, round($payment, 2));
    }
}
```

### Integration Testing
```php
// Example integration test
class LoanDisbursementTest extends TestCase
{
    public function test_loan_disbursement_process()
    {
        $loan = Loan::factory()->create(['status' => 'approved']);
        
        $response = $this->postJson("/api/loans/{$loan->id}/disburse");
        
        $response->assertStatus(200);
        $this->assertDatabaseHas('loans', [
            'id' => $loan->id,
            'status' => 'disbursed'
        ]);
    }
}
```

### System Testing
The system includes a comprehensive test suite that validates:
- Organization setup and configuration
- Account creation and management
- Loan application and approval process
- Disbursement and repayment processing
- Double-entry bookkeeping accuracy
- Report generation and accuracy

### Running Tests
```bash
# Run all tests
php artisan test

# Run specific test suite
php artisan test --testsuite=Unit
php artisan test --testsuite=Feature

# Run with coverage
php artisan test --coverage
```

---

## Monitoring & Logging

### Application Monitoring
- **Performance Metrics**: Response times, throughput
- **Error Tracking**: Exception monitoring
- **User Analytics**: Usage patterns
- **System Health**: Resource utilization

### Logging Configuration
```php
// Logging configuration
'logging' => [
    'default' => 'stack',
    'channels' => [
        'stack' => [
            'driver' => 'stack',
            'channels' => ['single', 'slack'],
            'ignore_exceptions' => false,
        ],
        'single' => [
            'driver' => 'single',
            'path' => storage_path('logs/laravel.log'),
            'level' => 'debug',
        ],
        'slack' => [
            'driver' => 'slack',
            'url' => env('LOG_SLACK_WEBHOOK_URL'),
            'username' => 'Laravel Log',
            'emoji' => ':boom:',
            'level' => 'critical',
        ],
    ],
]
```

### Health Checks
```php
// Health check endpoints
Route::get('/health', function () {
    return response()->json([
        'status' => 'healthy',
        'timestamp' => now(),
        'database' => DB::connection()->getPdo() ? 'connected' : 'disconnected',
        'redis' => Redis::ping() ? 'connected' : 'disconnected',
    ]);
});
```

---

## Troubleshooting Guide

### Common Issues

#### Database Connection Issues
```bash
# Check database connectivity
php artisan tinker
>>> DB::connection()->getPdo()

# Test specific connection
php artisan migrate:status
```

#### Cache Issues
```bash
# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Restart queue workers
php artisan queue:restart
```

#### Performance Issues
```bash
# Check slow queries
php artisan telescope:install

# Monitor memory usage
php artisan horizon:status

# Check queue status
php artisan queue:work --verbose
```

### Debug Mode
```php
// Enable debug mode
APP_DEBUG=true
LOG_LEVEL=debug

// Check logs
tail -f storage/logs/laravel.log
```

### Database Maintenance
```sql
-- Check table sizes
SELECT 
    table_name,
    ROUND(((data_length + index_length) / 1024 / 1024), 2) AS 'Size (MB)'
FROM information_schema.TABLES 
WHERE table_schema = 'your_database_name'
ORDER BY (data_length + index_length) DESC;

-- Optimize tables
OPTIMIZE TABLE loans, loan_transactions, general_ledger;

-- Check for slow queries
SHOW PROCESSLIST;
```

### Backup and Recovery
```bash
# Database backup
mysqldump -u username -p database_name > backup.sql

# Application backup
tar -czf app_backup.tar.gz /path/to/application

# Restore database
mysql -u username -p database_name < backup.sql
```

---

## Development Guidelines

### Code Standards
- **PSR-12**: PHP coding standards
- **Laravel Conventions**: Framework-specific guidelines
- **Documentation**: Comprehensive code documentation
- **Testing**: Test-driven development

### Git Workflow
```bash
# Feature development
git checkout -b feature/new-feature
# Make changes
git commit -m "Add new feature"
git push origin feature/new-feature

# Create pull request for code review
```

### Code Review Checklist
- [ ] Code follows PSR-12 standards
- [ ] Tests are included and passing
- [ ] Documentation is updated
- [ ] Security considerations addressed
- [ ] Performance impact evaluated
- [ ] Database migrations included if needed

---

*This technical documentation is maintained alongside the codebase. For the latest version, please refer to the repository documentation or contact the development team.*

**Version**: 1.0  
**Last Updated**: October 2025  
**Maintained By**: Development Team
