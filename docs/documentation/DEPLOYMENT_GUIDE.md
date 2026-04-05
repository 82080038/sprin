# SPRIN Deployment Guide

## Overview

This guide provides step-by-step instructions for deploying the SPRIN (Sistem Personil & Jadwal) application in various environments.

## System Requirements

### Minimum Requirements
- **PHP**: 8.2 or higher
- **Database**: MySQL 5.7+ or MariaDB 10.2+
- **Web Server**: Apache 2.4+ or Nginx 1.18+
- **Memory**: 512MB RAM minimum
- **Storage**: 1GB disk space minimum
- **OS**: Linux (Ubuntu 20.04+, CentOS 7+) or Windows Server 2016+

### Recommended Requirements
- **PHP**: 8.2+
- **Database**: MySQL 8.0+ or MariaDB 10.5+
- **Web Server**: Apache 2.4+ with mod_rewrite
- **Memory**: 2GB RAM
- **Storage**: 5GB disk space
- **OS**: Ubuntu 22.04 LTS

### PHP Extensions Required
```bash
php-mysql
php-curl
php-json
php-mbstring
php-xml
php-zip
php-gd
php-session
```

## Development Deployment (XAMPP)

### Prerequisites
- XAMPP installed
- Command line access
- Administrative privileges

### Installation Steps

#### 1. Download and Install XAMPP
```bash
# For Linux
wget https://www.apachefriends.org/xampp-files/8.2.12/xampp-linux-x64-8.2.12-installer.run
chmod +x xampp-linux-x64-8.2.12-installer.run
sudo ./xampp-linux-x64-8.2.12-installer.run

# For Windows
# Download from https://www.apachefriends.org
```

#### 2. Start XAMPP Services
```bash
sudo /opt/lampp/xampp start
```

#### 3. Deploy Application
```bash
# Copy application files
sudo cp -r /path/to/sprin /opt/lampp/htdocs/

# Set permissions
sudo chown -R nobody:nogroup /opt/lampp/htdocs/sprin
sudo chmod -R 755 /opt/lampp/htdocs/sprin
```

#### 4. Configure Database
```bash
# Access phpMyAdmin
http://localhost/phpmyadmin

# Create database
CREATE DATABASE bagops CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

# Import schema
mysql -u root -proot bagops < /opt/lampp/htdocs/sprin/database/bagops.sql
```

#### 5. Configure Application
```bash
# Edit configuration
nano /opt/lampp/htdocs/sprin/core/config.php

# Update database credentials
define('DB_HOST', 'localhost');
define('DB_NAME', 'bagops');
define('DB_USER', 'root');
define('DB_PASS', 'root');
```

#### 6. Test Deployment
```bash
# Access application
http://localhost/sprint

# Login credentials
Username: bagops
Password: admin123
```

## Production Deployment (Linux)

### Prerequisites
- Linux server with SSH access
- Apache/Nginx installed
- MySQL/MariaDB installed
- Domain name configured

### Installation Steps

#### 1. Server Preparation
```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install required packages
sudo apt install apache2 mysql-server php8.2 php8.2-mysql php8.2-curl php8.2-json php8.2-mbstring php8.2-xml php8.2-zip php8.2-gd -y

# Enable Apache modules
sudo a2enmod rewrite
sudo a2enmod ssl
sudo systemctl restart apache2
```

#### 2. Database Setup
```bash
# Secure MySQL
sudo mysql_secure_installation

# Create database and user
mysql -u root -p
```
```sql
CREATE DATABASE bagops CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'sprin_user'@'localhost' IDENTIFIED BY 'secure_password';
GRANT ALL PRIVILEGES ON bagops.* TO 'sprin_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

#### 3. Application Deployment
```bash
# Create application directory
sudo mkdir -p /var/www/sprin
sudo chown $USER:$USER /var/www/sprin

# Deploy application files
cp -r /path/to/sprin/* /var/www/sprin/

# Set permissions
sudo chown -R www-data:www-data /var/www/sprin
sudo find /var/www/sprin -type d -exec chmod 755 {} \;
sudo find /var/www/sprin -type f -exec chmod 644 {} \;
```

#### 4. Apache Configuration
```bash
# Create virtual host
sudo nano /etc/apache2/sites-available/sprin.conf
```

```apache
<VirtualHost *:80>
    ServerName your-domain.com
    ServerAdmin admin@your-domain.com
    DocumentRoot /var/www/sprin
    
    <Directory /var/www/sprin>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/sprin_error.log
    CustomLog ${APACHE_LOG_DIR}/sprin_access.log combined
</VirtualHost>
```

```bash
# Enable site
sudo a2ensite sprin.conf
sudo a2dissite 000-default.conf
sudo systemctl reload apache2
```

#### 5. SSL Configuration (Optional)
```bash
# Install Certbot
sudo apt install certbot python3-certbot-apache -y

# Obtain SSL certificate
sudo certbot --apache -d your-domain.com

# Set up auto-renewal
sudo crontab -e
# Add: 0 12 * * * /usr/bin/certbot renew --quiet
```

#### 6. Application Configuration
```bash
# Update production configuration
nano /var/www/sprin/core/config.php
```

```php
// Production settings
define('ENVIRONMENT', 'production');
define('DEBUG_MODE', false);
define('DB_HOST', 'localhost');
define('DB_NAME', 'bagops');
define('DB_USER', 'sprin_user');
define('DB_PASS', 'secure_password');
define('BASE_URL', 'https://your-domain.com');
```

#### 7. Security Hardening
```bash
# Hide PHP version
sudo nano /etc/php/8.2/apache2/php.ini
# Set: expose_php = Off

# Disable directory listing
sudo nano /var/www/sprin/.htaccess
# Add: Options -Indexes

# Set up firewall
sudo ufw allow 22/tcp
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw enable
```

## Docker Deployment

### Dockerfile
```dockerfile
FROM php:8.2-apache

# Install dependencies
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip \
    && docker-php-ext-install pdo pdo_mysql mysqli zip gd

# Enable Apache modules
RUN a2enmod rewrite

# Copy application
COPY . /var/www/html/

# Set permissions
RUN chown -R www-data:www-data /var/www/html/

# Expose port
EXPOSE 80

CMD ["apache2-foreground"]
```

### Docker Compose
```yaml
version: '3.8'

services:
  app:
    build: .
    ports:
      - "8080:80"
    depends_on:
      - db
    environment:
      - DB_HOST=db
      - DB_NAME=bagops
      - DB_USER=sprin_user
      - DB_PASS=secure_password
    volumes:
      - ./core/config.php:/var/www/html/core/config.php

  db:
    image: mysql:8.0
    environment:
      MYSQL_ROOT_PASSWORD: root_password
      MYSQL_DATABASE: bagops
      MYSQL_USER: sprin_user
      MYSQL_PASSWORD: secure_password
    volumes:
      - mysql_data:/var/lib/mysql
      - ./database/bagops.sql:/docker-entrypoint-initdb.d/init.sql

volumes:
  mysql_data:
```

### Docker Commands
```bash
# Build and run
docker-compose up -d

# View logs
docker-compose logs -f

# Stop containers
docker-compose down

# Remove volumes
docker-compose down -v
```

## Configuration Management

### Environment Variables
```bash
# .env file
DB_HOST=localhost
DB_NAME=bagops
DB_USER=sprin_user
DB_PASS=secure_password
BASE_URL=https://your-domain.com
ENVIRONMENT=production
DEBUG_MODE=false
```

### Configuration Files
```php
// config.php
<?php
// Load environment variables
$env_file = __DIR__ . '/../.env';
if (file_exists($env_file)) {
    $lines = file($env_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '#') === 0) continue;
        if (strpos($line, '=') === false) continue;
        list($key, $value) = explode('=', $line, 2);
        $_ENV[trim($key)] = trim($value);
    }
}

// Database configuration
define('DB_HOST', $_ENV['DB_HOST'] ?? 'localhost');
define('DB_NAME', $_ENV['DB_NAME'] ?? 'bagops');
define('DB_USER', $_ENV['DB_USER'] ?? 'root');
define('DB_PASS', $_ENV['DB_PASS'] ?? 'root');

// Application configuration
define('BASE_URL', $_ENV['BASE_URL'] ?? 'http://localhost');
define('ENVIRONMENT', $_ENV['ENVIRONMENT'] ?? 'development');
define('DEBUG_MODE', filter_var($_ENV['DEBUG_MODE'] ?? true, FILTER_VALIDATE_BOOLEAN));
```

## Database Management

### Backup Scripts
```bash
#!/bin/bash
# backup.sh

DB_NAME="bagops"
DB_USER="sprin_user"
DB_PASS="secure_password"
BACKUP_DIR="/var/backups/sprin"
DATE=$(date +%Y%m%d_%H%M%S)

# Create backup directory
mkdir -p $BACKUP_DIR

# Create backup
mysqldump -u $DB_USER -p$DB_PASS $DB_NAME > $BACKUP_DIR/bagops_$DATE.sql

# Compress backup
gzip $BACKUP_DIR/bagops_$DATE.sql

# Remove old backups (keep last 7 days)
find $BACKUP_DIR -name "bagops_*.sql.gz" -mtime +7 -delete

echo "Backup completed: bagops_$DATE.sql.gz"
```

### Restore Scripts
```bash
#!/bin/bash
# restore.sh

DB_NAME="bagops"
DB_USER="sprin_user"
DB_PASS="secure_password"
BACKUP_FILE=$1

if [ -z "$BACKUP_FILE" ]; then
    echo "Usage: $0 <backup_file>"
    exit 1
fi

# Restore database
mysql -u $DB_USER -p$DB_PASS $DB_NAME < $BACKUP_FILE

echo "Database restored from: $BACKUP_FILE"
```

## Monitoring and Logging

### Application Logging
```php
// Add to config.php
if (ENVIRONMENT !== 'development') {
    ini_set('log_errors', 1);
    ini_set('error_log', __DIR__ . '/../logs/application.log');
    
    // Custom error handler
    set_error_handler(function($severity, $message, $file, $line) {
        $log_message = sprintf(
            "[%s] %s: %s in %s on line %d",
            date('Y-m-d H:i:s'),
            $severity,
            $message,
            $file,
            $line
        );
        error_log($log_message);
        return true;
    });
}
```

### Log Rotation
```bash
# /etc/logrotate.d/sprin
/var/www/sprin/logs/*.log {
    daily
    missingok
    rotate 30
    compress
    delaycompress
    notifempty
    create 644 www-data www-data
    postrotate
        systemctl reload apache2
    endscript
}
```

## Performance Optimization

### Apache Optimization
```apache
# .htaccess
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType text/css "access plus 1 month"
    ExpiresByType application/javascript "access plus 1 month"
    ExpiresByType image/png "access plus 1 month"
    ExpiresByType image/jpg "access plus 1 month"
    ExpiresByType image/gif "access plus 1 month"
    ExpiresByType image/ico "access plus 1 month"
</IfModule>

<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/plain
    AddOutputFilterByType DEFLATE text/html
    AddOutputFilterByType DEFLATE text/xml
    AddOutputFilterByType DEFLATE text/css
    AddOutputFilterByType DEFLATE application/xml
    AddOutputFilterByType DEFLATE application/xhtml+xml
    AddOutputFilterByType DEFLATE application/rss+xml
    AddOutputFilterByType DEFLATE application/javascript
    AddOutputFilterByType DEFLATE application/x-javascript
</IfModule>
```

### PHP Optimization
```ini
; php.ini
memory_limit = 256M
max_execution_time = 300
upload_max_filesize = 10M
post_max_size = 10M
max_input_vars = 3000
```

## Security Checklist

### Application Security
- [ ] Update all dependencies
- [ ] Enable HTTPS
- [ ] Configure firewall
- [ ] Set up intrusion detection
- [ ] Regular security audits
- [ ] Backup encryption
- [ ] Access control lists

### Server Security
- [ ] Disable unused services
- [ ] Regular system updates
- [ ] Fail2ban configuration
- [ ] Log monitoring
- [ ] User access controls
- [ ] File permissions audit

## Troubleshooting

### Common Issues

#### 1. Database Connection Error
```bash
# Check MySQL status
sudo systemctl status mysql

# Check credentials
mysql -u sprin_user -p bagops

# Check configuration
grep DB_ /var/www/sprin/core/config.php
```

#### 2. Permission Denied
```bash
# Check file permissions
ls -la /var/www/sprin/

# Fix permissions
sudo chown -R www-data:www-data /var/www/sprin/
sudo chmod -R 755 /var/www/sprin/
```

#### 3. Apache Configuration
```bash
# Check Apache status
sudo systemctl status apache2

# Test configuration
sudo apache2ctl configtest

# Check logs
sudo tail -f /var/log/apache2/error.log
```

#### 4. PHP Errors
```bash
# Check PHP error log
sudo tail -f /var/log/php_errors.log

# Enable error reporting
sudo nano /etc/php/8.2/apache2/php.ini
# Set: display_errors = On (development only)
```

## Maintenance

### Regular Tasks
1. **Daily**: Check application logs
2. **Weekly**: Update system packages
3. **Monthly**: Security updates
4. **Quarterly**: Performance review
5. **Annually**: Security audit

### Backup Schedule
1. **Database**: Daily automated backups
2. **Files**: Weekly full backups
3. **Configuration**: Monthly backup
4. **Off-site**: Quarterly off-site backup

---

## Support

### Documentation
- [Application Documentation](README.md)
- [API Documentation](docs/API.md)
- [Testing Guide](README_TESTING.md)
- [Database Schema](database/README.md)

### Contact
- **Technical Support**: support@your-domain.com
- **Security Issues**: security@your-domain.com
- **General Questions**: info@your-domain.com

---

**Last Updated**: April 2, 2026  
**Version**: 1.1.0  
**Environment**: Production Ready
