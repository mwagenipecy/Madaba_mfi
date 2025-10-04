# Microfinance Management System - System Administration Guide

## Table of Contents
1. [System Overview](#system-overview)
2. [Initial System Setup](#initial-system-setup)
3. [User Management](#user-management)
4. [Organization Configuration](#organization-configuration)
5. [System Configuration](#system-configuration)
6. [Security Management](#security-management)
7. [Data Management](#data-management)
8. [Backup & Recovery](#backup--recovery)
9. [Monitoring & Maintenance](#monitoring--maintenance)
10. [Troubleshooting](#troubleshooting)

---

## System Overview

The Microfinance Management System is a comprehensive platform designed to manage microfinance operations across multiple organizations and branches. This guide provides detailed instructions for system administrators to configure, maintain, and troubleshoot the system.

### System Architecture
- **Multi-tenant**: Supports multiple organizations
- **Role-based**: Granular permission system
- **Scalable**: Designed for growth
- **Secure**: Enterprise-grade security
- **Auditable**: Complete audit trail

### Key Components
- **Web Interface**: Browser-based application
- **Database**: MySQL backend
- **Cache**: Redis for performance
- **Queue**: Background job processing
- **File Storage**: Document management
- **Email**: Automated notifications

---

## Initial System Setup

### Prerequisites
- **Server**: Ubuntu 20.04+ or CentOS 8+
- **PHP**: Version 8.4.4+
- **Database**: MySQL 8.0+
- **Web Server**: Nginx or Apache
- **Cache**: Redis 6.0+
- **SSL Certificate**: For HTTPS

### Installation Steps

#### 1. Server Preparation
```bash
# Update system packages
sudo apt update && sudo apt upgrade -y

# Install required packages
sudo apt install -y nginx mysql-server redis-server php8.4-fpm php8.4-mysql php8.4-redis php8.4-xml php8.4-curl php8.4-zip php8.4-mbstring php8.4-gd

# Install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Install Node.js and NPM
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt-get install -y nodejs
```

#### 2. Database Setup
```sql
-- Create database and user
CREATE DATABASE microfinance_system;
CREATE USER 'mf_user'@'localhost' IDENTIFIED BY 'secure_password';
GRANT ALL PRIVILEGES ON microfinance_system.* TO 'mf_user'@'localhost';
FLUSH PRIVILEGES;
```

#### 3. Application Deployment
```bash
# Clone repository
git clone https://github.com/your-org/microfinance-system.git
cd microfinance-system

# Install PHP dependencies
composer install --optimize-autoloader --no-dev

# Install Node.js dependencies
npm ci && npm run build

# Configure environment
cp .env.example .env
nano .env  # Configure database and other settings

# Generate application key
php artisan key:generate

# Run database migrations
php artisan migrate --force

# Create storage links
php artisan storage:link

# Set permissions
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 755 storage bootstrap/cache
```

#### 4. Web Server Configuration

**Nginx Configuration:**
```nginx
server {
    listen 80;
    listen 443 ssl http2;
    server_name yourdomain.com;
    root /path/to/microfinance-system/public;

    # SSL Configuration
    ssl_certificate /path/to/ssl/cert.pem;
    ssl_certificate_key /path/to/ssl/private.key;

    # Security headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header X-Content-Type-Options "nosniff" always;

    # Main application
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # PHP processing
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.4-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    # Static files
    location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }

    # Deny access to sensitive files
    location ~ /\. {
        deny all;
    }
}
```

#### 5. System Services
```bash
# Enable and start services
sudo systemctl enable nginx mysql redis-server php8.4-fpm
sudo systemctl start nginx mysql redis-server php8.4-fpm

# Configure queue workers
sudo nano /etc/systemd/system/microfinance-queue.service
```

**Queue Service Configuration:**
```ini
[Unit]
Description=Microfinance Queue Worker
After=network.target

[Service]
User=www-data
Group=www-data
WorkingDirectory=/path/to/microfinance-system
ExecStart=/usr/bin/php artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
Restart=always
RestartSec=5

[Install]
WantedBy=multi-user.target
```

```bash
# Enable queue service
sudo systemctl enable microfinance-queue
sudo systemctl start microfinance-queue
```

---

## User Management

### Creating Super Admin
```bash
# Create super admin user
php artisan tinker

# In tinker:
$user = new App\Models\User();
$user->first_name = 'Super';
$user->last_name = 'Admin';
$user->email = 'admin@system.com';
$user->password = Hash::make('secure_password');
$user->role = 'super_admin';
$user->status = 'active';
$user->email_verified_at = now();
$user->save();
```

### User Role Management

#### Role Hierarchy
1. **Super Admin**: System-wide access
2. **Admin**: Organization-level access
3. **Manager**: Branch-level access
4. **Loan Officer**: Client-facing operations
5. **Accountant**: Financial operations
6. **Cashier**: Cash operations
7. **Field Agent**: Mobile operations

#### Permission Management
```php
// Example permission configuration
$permissions = [
    'users.create' => ['super_admin', 'admin'],
    'users.update' => ['super_admin', 'admin'],
    'users.delete' => ['super_admin'],
    'loans.approve' => ['super_admin', 'admin', 'manager'],
    'loans.disburse' => ['super_admin', 'admin', 'manager', 'cashier'],
    'reports.view' => ['super_admin', 'admin', 'manager'],
    'accounts.manage' => ['super_admin', 'admin', 'accountant'],
];
```

### User Creation Process
1. **Navigate** to Admin → Users → Create User
2. **Fill Details**:
   - Personal information
   - Contact details
   - Role assignment
   - Organization/Branch assignment
3. **Set Permissions**: Configure specific permissions
4. **Send Invitation**: Email invitation with temporary password
5. **Activate**: User completes setup process

---

## Organization Configuration

### Creating Organizations
1. **Navigate** to Admin → Organizations → Create
2. **Fill Details**:
   - Organization name and legal details
   - Registration and license numbers
   - Contact information
   - Address details
   - Authorized capital
3. **Configure Settings**:
   - Currency settings
   - Fiscal year
   - Business rules
   - Notification preferences
4. **Set Status**: Activate organization

### Organization Settings
```php
// Organization configuration options
$settings = [
    'currency' => 'TZS',
    'fiscal_year_start' => '01-01',
    'business_hours' => [
        'start' => '08:00',
        'end' => '17:00',
        'timezone' => 'Africa/Dar_es_Salaam'
    ],
    'loan_settings' => [
        'max_loan_amount' => 10000000,
        'default_interest_rate' => 24.0,
        'grace_period_days' => 7
    ],
    'notification_settings' => [
        'email_notifications' => true,
        'sms_notifications' => false,
        'push_notifications' => true
    ]
];
```

### Branch Configuration
1. **Create Branches**: Add branch locations
2. **Assign Managers**: Set branch administrators
3. **Configure Accounts**: Set up branch-specific accounts
4. **Set Limits**: Configure operational limits
5. **Enable Features**: Activate branch-specific features

---

## System Configuration

### Environment Configuration
```bash
# Key environment variables
APP_NAME="Microfinance Management System"
APP_ENV=production
APP_KEY=base64:your-generated-key
APP_DEBUG=false
APP_URL=https://yourdomain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=microfinance_system
DB_USERNAME=mf_user
DB_PASSWORD=secure_password

CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
```

### Application Configuration
```php
// config/app.php - Key settings
'timezone' => 'Africa/Dar_es_Salaam',
'locale' => 'en',
'fallback_locale' => 'en',
'faker_locale' => 'en_US',

// config/database.php - Database settings
'connections' => [
    'mysql' => [
        'driver' => 'mysql',
        'host' => env('DB_HOST', '127.0.0.1'),
        'port' => env('DB_PORT', '3306'),
        'database' => env('DB_DATABASE', 'forge'),
        'username' => env('DB_USERNAME', 'forge'),
        'password' => env('DB_PASSWORD', ''),
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
        'prefix' => '',
        'strict' => true,
        'engine' => null,
    ],
],
```

### Cache Configuration
```php
// config/cache.php
'stores' => [
    'redis' => [
        'driver' => 'redis',
        'connection' => 'cache',
        'lock_connection' => 'default',
    ],
],

// config/database.php - Redis configuration
'redis' => [
    'client' => env('REDIS_CLIENT', 'phpredis'),
    'options' => [
        'cluster' => env('REDIS_CLUSTER', 'redis'),
        'prefix' => env('REDIS_PREFIX', Str::slug(env('APP_NAME', 'laravel'), '_').'_database_'),
    ],
    'default' => [
        'url' => env('REDIS_URL'),
        'host' => env('REDIS_HOST', '127.0.0.1'),
        'password' => env('REDIS_PASSWORD'),
        'port' => env('REDIS_PORT', '6379'),
        'database' => env('REDIS_DB', '0'),
    ],
    'cache' => [
        'url' => env('REDIS_URL'),
        'host' => env('REDIS_HOST', '127.0.0.1'),
        'password' => env('REDIS_PASSWORD'),
        'port' => env('REDIS_PORT', '6379'),
        'database' => env('REDIS_CACHE_DB', '1'),
    ],
],
```

---

## Security Management

### Authentication Security
```php
// config/fortify.php - Authentication settings
'features' => [
    Features::registration(),
    Features::resetPasswords(),
    Features::emailVerification(),
    Features::updateProfileInformation(),
    Features::updatePasswords(),
    Features::twoFactorAuthentication([
        'confirm' => true,
        'confirmPassword' => true,
    ]),
],

// Password requirements
'passwords' => [
    'users' => [
        'provider' => 'users',
        'table' => 'password_reset_tokens',
        'expire' => 60,
        'throttle' => 60,
    ],
],
```

### Session Security
```php
// config/session.php
'lifetime' => 120,  // 2 hours
'expire_on_close' => false,
'encrypt' => false,
'files' => storage_path('framework/sessions'),
'connection' => env('SESSION_CONNECTION'),
'table' => 'sessions',
'store' => env('SESSION_STORE'),
'lottery' => [2, 100],
'cookie' => env('SESSION_COOKIE', Str::slug(env('APP_NAME', 'laravel'), '_').'_session'),
'path' => '/',
'domain' => env('SESSION_DOMAIN'),
'secure' => env('SESSION_SECURE_COOKIE'),
'http_only' => true,
'same_site' => 'lax',
```

### Data Encryption
```bash
# Generate encryption keys
php artisan key:generate
php artisan passport:keys

# Encrypt sensitive data
php artisan tinker
>>> Crypt::encrypt('sensitive_data');
```

### Security Headers
```php
// Middleware for security headers
class SecurityHeadersMiddleware
{
    public function handle($request, Closure $next)
    {
        $response = $next($request);
        
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        $response->headers->set('Content-Security-Policy', "default-src 'self'");
        
        return $response;
    }
}
```

---

## Data Management

### Database Maintenance
```sql
-- Regular maintenance tasks
OPTIMIZE TABLE loans, loan_transactions, general_ledger, users, clients;

-- Check table sizes
SELECT 
    table_name,
    ROUND(((data_length + index_length) / 1024 / 1024), 2) AS 'Size (MB)'
FROM information_schema.TABLES 
WHERE table_schema = 'microfinance_system'
ORDER BY (data_length + index_length) DESC;

-- Monitor slow queries
SHOW PROCESSLIST;
SHOW FULL PROCESSLIST;
```

### Data Archival
```bash
# Archive old data
php artisan data:archive --older-than=2-years

# Archive completed loans
php artisan loans:archive --status=completed --older-than=5-years

# Archive old transactions
php artisan transactions:archive --older-than=3-years
```

### Data Validation
```bash
# Validate data integrity
php artisan data:validate

# Check for orphaned records
php artisan data:check-orphans

# Validate accounting balances
php artisan accounting:validate-balances
```

---

## Backup & Recovery

### Automated Backup Script
```bash
#!/bin/bash
# /usr/local/bin/backup-microfinance.sh

# Configuration
DB_NAME="microfinance_system"
DB_USER="mf_user"
DB_PASS="secure_password"
BACKUP_DIR="/backups/microfinance"
APP_DIR="/path/to/microfinance-system"
DATE=$(date +%Y%m%d_%H%M%S)

# Create backup directory
mkdir -p $BACKUP_DIR

# Database backup
mysqldump -u$DB_USER -p$DB_PASS $DB_NAME | gzip > $BACKUP_DIR/db_backup_$DATE.sql.gz

# Application files backup
tar -czf $BACKUP_DIR/app_backup_$DATE.tar.gz $APP_DIR --exclude='node_modules' --exclude='vendor' --exclude='storage/logs'

# Keep only last 30 days of backups
find $BACKUP_DIR -name "*.gz" -mtime +30 -delete

echo "Backup completed: $DATE"
```

### Recovery Procedures
```bash
# Database recovery
gunzip -c /backups/microfinance/db_backup_20251003_120000.sql.gz | mysql -u$DB_USER -p$DB_PASS $DB_NAME

# Application recovery
tar -xzf /backups/microfinance/app_backup_20251003_120000.tar.gz -C /

# Restore from specific point
php artisan backup:restore --file=backup_20251003_120000.zip
```

### Disaster Recovery Plan
1. **Immediate Response**: Assess damage and scope
2. **System Recovery**: Restore from latest backup
3. **Data Validation**: Verify data integrity
4. **Service Restoration**: Bring services back online
5. **Post-Recovery**: Monitor system stability
6. **Documentation**: Record incident and lessons learned

---

## Monitoring & Maintenance

### System Monitoring
```bash
# Install monitoring tools
sudo apt install htop iotop nethogs

# Monitor system resources
htop                    # CPU and memory usage
iotop                   # Disk I/O monitoring
nethogs                 # Network usage by process
df -h                   # Disk space usage
free -h                 # Memory usage
```

### Application Monitoring
```php
// Health check endpoint
Route::get('/health', function () {
    $checks = [
        'database' => DB::connection()->getPdo() ? 'ok' : 'error',
        'redis' => Redis::ping() ? 'ok' : 'error',
        'storage' => is_writable(storage_path()) ? 'ok' : 'error',
        'queue' => Queue::size() < 1000 ? 'ok' : 'warning',
    ];
    
    $status = in_array('error', $checks) ? 'unhealthy' : 'healthy';
    
    return response()->json([
        'status' => $status,
        'checks' => $checks,
        'timestamp' => now(),
    ]);
});
```

### Log Monitoring
```bash
# Monitor application logs
tail -f /path/to/microfinance-system/storage/logs/laravel.log

# Monitor web server logs
tail -f /var/log/nginx/access.log
tail -f /var/log/nginx/error.log

# Monitor system logs
tail -f /var/log/syslog
```

### Performance Monitoring
```bash
# Database performance
mysqladmin -u$DB_USER -p$DB_PASS processlist
mysqladmin -u$DB_USER -p$DB_PASS status

# Application performance
php artisan horizon:status
php artisan queue:monitor redis:default

# Cache performance
redis-cli info stats
redis-cli monitor
```

### Regular Maintenance Tasks
```bash
# Daily tasks (cron job)
0 2 * * * /usr/local/bin/backup-microfinance.sh
0 3 * * * php /path/to/microfinance-system/artisan queue:prune-failed
0 4 * * * php /path/to/microfinance-system/artisan cache:clear

# Weekly tasks
0 1 * * 0 php /path/to/microfinance-system/artisan data:archive
0 2 * * 0 php /path/to/microfinance-system/artisan optimize:clear

# Monthly tasks
0 1 1 * * php /path/to/microfinance-system/artisan reports:generate-monthly
0 2 1 * * mysql -u$DB_USER -p$DB_PASS -e "OPTIMIZE TABLE loans, loan_transactions, general_ledger;"
```

---

## Troubleshooting

### Common Issues

#### 1. Database Connection Issues
```bash
# Check database status
sudo systemctl status mysql
sudo systemctl restart mysql

# Test connection
mysql -u$DB_USER -p$DB_PASS -e "SELECT 1"

# Check Laravel database connection
php artisan tinker
>>> DB::connection()->getPdo()
```

#### 2. Application Not Loading
```bash
# Check web server status
sudo systemctl status nginx
sudo systemctl restart nginx

# Check PHP-FPM status
sudo systemctl status php8.4-fpm
sudo systemctl restart php8.4-fpm

# Check file permissions
ls -la /path/to/microfinance-system/storage
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 755 storage bootstrap/cache
```

#### 3. Queue Workers Not Processing
```bash
# Check queue worker status
sudo systemctl status microfinance-queue
sudo systemctl restart microfinance-queue

# Check Redis connection
redis-cli ping

# Monitor queue
php artisan queue:monitor redis:default
```

#### 4. Cache Issues
```bash
# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Restart Redis
sudo systemctl restart redis-server
```

#### 5. Email Not Sending
```bash
# Test email configuration
php artisan tinker
>>> Mail::raw('Test email', function($message) { $message->to('test@example.com')->subject('Test'); });

# Check mail logs
tail -f /var/log/mail.log
```

### Performance Issues

#### Slow Database Queries
```sql
-- Enable slow query log
SET GLOBAL slow_query_log = 'ON';
SET GLOBAL long_query_time = 2;

-- Check slow queries
SHOW PROCESSLIST;
SHOW FULL PROCESSLIST;

-- Analyze query performance
EXPLAIN SELECT * FROM loans WHERE status = 'active';
```

#### High Memory Usage
```bash
# Monitor memory usage
free -h
ps aux --sort=-%mem | head

# Check PHP memory limit
php -i | grep memory_limit

# Optimize PHP-FPM
sudo nano /etc/php/8.4/fpm/pool.d/www.conf
# Adjust pm.max_children, pm.start_servers, pm.min_spare_servers
```

### Security Issues

#### Unauthorized Access
```bash
# Check authentication logs
tail -f /var/log/auth.log

# Review failed login attempts
grep "Failed password" /var/log/auth.log

# Check application logs for suspicious activity
grep "Unauthorized" /path/to/microfinance-system/storage/logs/laravel.log
```

#### Data Breach Response
1. **Immediate**: Isolate affected systems
2. **Assessment**: Determine scope of breach
3. **Containment**: Prevent further access
4. **Investigation**: Analyze attack vectors
5. **Recovery**: Restore systems securely
6. **Notification**: Inform stakeholders
7. **Documentation**: Record incident details

### Emergency Procedures

#### System Downtime
1. **Assessment**: Determine cause and scope
2. **Communication**: Notify users and stakeholders
3. **Recovery**: Restore services quickly
4. **Monitoring**: Watch for recurring issues
5. **Post-Mortem**: Analyze and improve

#### Data Corruption
1. **Stop Services**: Prevent further corruption
2. **Assessment**: Determine extent of damage
3. **Recovery**: Restore from clean backup
4. **Validation**: Verify data integrity
5. **Resume Services**: Bring system back online

---

## Contact Information

### Technical Support
- **Email**: support@yourmfi.com
- **Phone**: +255 XXX XXX XXX
- **Emergency**: +255 XXX XXX XXX
- **Hours**: 24/7 for critical issues

### Documentation
- **User Manual**: /docs/USER_MANUAL.md
- **Technical Docs**: /docs/TECHNICAL_DOCUMENTATION.md
- **API Reference**: /docs/API_DOCUMENTATION.md

### Training
- **New User Training**: Available on request
- **Administrator Training**: Quarterly sessions
- **Advanced Features**: As needed

---

*This administration guide is regularly updated. For the latest version, please refer to the system documentation or contact the technical support team.*

**Version**: 1.0  
**Last Updated**: October 2025  
**Next Review**: January 2026
