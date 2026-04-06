---
description: Complete development workflow for SPRIN application with comprehensive testing
---

# SPRIN Application Development Workflow

## Overview
This workflow covers the complete development process for the SPRIN (Sistem Personil Polres Samosir) application, including comprehensive testing, integration fixes, and quality assurance.

## Prerequisites
- PHP 8.0+
- MySQL/MariaDB
- XAMPP installed
- Node.js 16+ (for testing)
- Sudo access for system operations

## Development Steps

### 1. Environment Setup
```bash
# Check XAMPP status
sudo /opt/lampp/bin/lampp status

# Start XAMPP services
sudo /opt/lampp/bin/lampp start

# Verify services are running
sudo /opt/lampp/bin/lampp status
```

### 2. Database Setup
```bash
# Create database
mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS bagops;"

# Import database structure
mysql -u root -p bagops < /opt/lampp/htdocs/sprin/database/bagops.sql

# Verify database
mysql -u root -p -e "USE bagops; SHOW TABLES;"
```

### 3. Application Configuration
```bash
# Set proper permissions
chmod -R 755 /opt/lampp/htdocs/sprin/
chmod -R 777 /opt/lampp/htdocs/sprin/backups/
chmod -R 777 /opt/lampp/htdocs/sprin/logs/
chmod -R 777 /opt/lampp/htdocs/sprin/cache/

# Create necessary directories if they don't exist
mkdir -p /opt/lampp/htdocs/sprin/backups
mkdir -p /opt/lampp/htdocs/sprin/logs
mkdir -p /opt/lampp/htdocs/sprin/cache
mkdir -p /opt/lampp/htdocs/sprin/tests/screenshots
```

### 4. Testing Infrastructure Setup
```bash
# Navigate to tests directory
cd /opt/lampp/htdocs/sprin/tests

# Install Node.js dependencies
npm install

# Install Puppeteer (for headless testing)
npm run puppeteer-install

# Verify test setup
npm run test:api
```

### 5. Application Testing
```bash
# Access application
# http://localhost/sprin

# Default credentials
# Username: bagops
# Password: admin123

# Run comprehensive tests
cd /opt/lampp/htdocs/sprin/tests
npm run test:all

# Run specific test suites
npm run test:auth      # Authentication tests
npm run test:unsur     # Unsur management tests
npm run test:bagian    # Bagian management tests
npm run test:jabatan   # Jabatan management tests
npm run test:performance # Performance tests
npm run test:mobile    # Mobile responsiveness tests
```

### 6. Python Integration Setup
```bash
# Create Python directory
mkdir -p /opt/lampp/htdocs/sprin/python

# Install Python dependencies
pip3 install mysql-connector-python flask pandas numpy

# Set up Python environment
cd /opt/lampp/htdocs/sprin/python
python3 -m venv venv
source venv/bin/activate
```

## Integration Fixes Applied

### 1. F2E (Frontend-to-Backend) Integration
- ✅ Created unified SPRIN Core Framework (`/public/assets/js/sprin-core.js`)
- ✅ Implemented centralized API client with consistent interface
- ✅ Unified notification system (window.SPRINT)
- ✅ Standardized API response format across all endpoints
- ✅ Created modular JavaScript architecture

### 2. E2E (End-to-End) Integration
- ✅ Centralized state management with SPRINStateManager
- ✅ Event-driven communication via SPRINEventBus
- ✅ Unified API gateway (`/api/unified-api.php`)
- ✅ Consistent modal and form handling
- ✅ Global loading states and error handling

### 3. API Architecture
- ✅ Unified API Gateway - Single endpoint for all CRUD operations
- ✅ Consistent response format: `{success, message, data, timestamp}`
- ✅ Proper HTTP status codes and error handling
- ✅ Individual API endpoints for backward compatibility
- ✅ Performance optimization with concurrent request handling

### 4. Testing Infrastructure
- ✅ Puppeteer-based E2E testing
- ✅ API integration testing
- ✅ Performance testing
- ✅ Mobile responsiveness testing
- ✅ Authentication flow testing
- ✅ Data integrity validation

## Development Commands

### Database Operations
```bash
# Backup database
mysqldump -u root -p bagops > backups/bagops_backup_$(date +%Y%m%d_%H%M%S).sql

# Restore database
mysql -u root -p bagops < backups/bagops_backup_latest.sql

# Check database status
mysql -u root -p -e "SHOW DATABASES;"
```

### XAMPP Management
```bash
# Start XAMPP
sudo /opt/lampp/bin/lampp start

# Stop XAMPP
sudo /opt/lampp/bin/lampp stop

# Restart XAMPP
sudo /opt/lampp/bin/lampp restart

# Check status
sudo /opt/lampp/bin/lampp status
```

### Testing Commands
```bash
# Quick API test
cd /opt/lampp/htdocs/sprin/tests
node simple-api-test.js

# Full test suite
npm run test:all

# Generate test report
npm run report

# Run tests with coverage
npm run test:coverage

# Run performance tests
npm run test:performance

# Run mobile tests
npm run test:mobile
```

### Application Testing
```bash
# Test PHP configuration
php -v

# Test database connection
php -r "try { new PDO('mysql:host=localhost;dbname=bagops', 'root', ''); echo 'Database OK'; } catch(Exception \$e) { echo 'DB Error: ' . \$e->getMessage(); }"

# Check error logs
tail -f /opt/lampp/logs/php_error_log
tail -f /opt/lampp/htdocs/sprin/logs/error.log
```

## API Testing Results

### Unified API Gateway
```bash
# Test dashboard statistics
curl "http://localhost/sprin/api/unified-api.php?resource=stats&action=dashboard"

# Test unsur operations
curl "http://localhost/sprin/api/unified-api.php?resource=unsur&action=get_all"

# Test bagian operations
curl "http://localhost/sprin/api/unified-api.php?resource=bagian&action=get_all"

# Test jabatan operations
curl "http://localhost/sprin/api/unified-api.php?resource=jabatan&action=get_all"
```

### Current Data Status
- **Total Personil**: 208
- **Active Personil**: 208
- **Total Unsur**: 6
- **Total Bagian**: 29
- **Total Jabatan**: 109

## Troubleshooting

### Common Issues
1. **Database Connection Error**
   - Check XAMPP MySQL service status
   - Verify database credentials in config.php
   - Ensure database exists

2. **API Integration Issues**
   - Check unified API gateway: `/api/unified-api.php`
   - Verify API response format consistency
   - Check for authentication redirects

3. **Frontend Integration Issues**
   - Verify SPRIN Core Framework loading
   - Check window.SPRINT availability
   - Ensure API client configuration

4. **Testing Issues**
   - Install Node.js dependencies: `npm install`
   - Check Puppeteer installation: `npm run puppeteer-install`
   - Verify application accessibility: `curl http://localhost/sprin`

### Error Log Locations
- PHP Error Log: `/opt/lampp/logs/php_error_log`
- Apache Error Log: `/opt/lampp/logs/apache/error_log`
- MySQL Error Log: `/opt/lampp/logs/mysql/error_log`
- Application Log: `/opt/lampp/htdocs/sprin/logs/error.log`
- Test Screenshots: `/opt/lampp/htdocs/sprin/tests/screenshots`

## Development Best Practices

1. **Always backup database before making changes**
2. **Test in development environment first**
3. **Run comprehensive tests before deployment**
4. **Check error logs regularly**
5. **Use version control for code changes**
6. **Document any configuration changes**
7. **Verify API integration after changes**
8. **Test mobile responsiveness regularly**

## Security Considerations

1. **Change default passwords**
2. **Restrict database access**
3. **Enable HTTPS in production**
4. **Regular security updates**
5. **Monitor access logs**
6. **Validate API inputs**
7. **Implement rate limiting**

## Performance Optimization

1. **Enable database caching**
2. **Optimize database queries**
3. **Use CDN for static assets**
4. **Enable PHP OPcache**
5. **Regular database maintenance**
6. **Implement API response caching**
7. **Optimize JavaScript bundle size**

## Deployment Checklist

### Pre-Deployment
- [ ] Database configured and tested
- [ ] XAMPP services running
- [ ] File permissions set correctly
- [ ] Error logging configured
- [ ] Security measures implemented
- [ ] Backup systems in place
- [ ] Performance optimizations applied

### Testing
- [ ] All API endpoints tested
- [ ] Frontend integration verified
- [ ] Mobile responsiveness confirmed
- [ ] Performance benchmarks met
- [ ] Security tests passed
- [ ] Data integrity validated

### Documentation
- [ ] API documentation updated
- [ ] User documentation current
- [ ] Development workflow updated
- [ ] Deployment guide prepared

## Emergency Procedures

### Database Recovery
```bash
# Stop application
sudo /opt/lampp/bin/lampp stop

# Restore from backup
mysql -u root -p bagops < backups/bagops_backup_latest.sql

# Restart services
sudo /opt/lampp/bin/lampp start

# Verify application
curl http://localhost/sprin
```

### System Recovery
```bash
# Full XAMPP restart
sudo /opt/lampp/bin/lampp restart

# Check system resources
df -h
free -m
top

# Clear caches
rm -rf /opt/lampp/htdocs/sprin/cache/*

# Run health check
cd /opt/lampp/htdocs/sprin/tests
node simple-api-test.js
```

### API Recovery
```bash
# Test unified API gateway
curl "http://localhost/sprin/api/unified-api.php?resource=stats&action=dashboard"

# Test individual APIs
curl "http://localhost/sprin/api/unsur_api.php?action=get_all_unsur"
curl "http://localhost/sprin/api/bagian_api.php?action=get_all_bagian"
curl "http://localhost/sprin/api/jabatan_api.php?action=get_all_jabatan"
```

## Quality Assurance

### Automated Testing
```bash
# Run full test suite
cd /opt/lampp/htdocs/sprin/tests
npm run test:all

# Generate coverage report
npm run test:coverage

# Performance benchmarking
npm run test:performance

# Mobile testing
npm run test:mobile
```

### Manual Testing Checklist
- [ ] Login/logout functionality
- [ ] CRUD operations for all modules
- [ ] Modal dialogs and forms
- [ ] Navigation between pages
- [ ] Error handling and user feedback
- [ ] Mobile responsiveness
- [ ] Performance under load

---

**Note**: This workflow includes comprehensive testing and integration fixes. Always run the full test suite before deploying to production.
