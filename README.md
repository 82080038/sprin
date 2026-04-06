# SPRIN - Sistem Personil Polres Samosir

A comprehensive personnel management system for POLRES Samosir, built with PHP, MySQL, and modern JavaScript technologies.

## 🚀 Features

### Core Functionality
- **Personnel Management**: Complete CRUD operations for personnel data
- **Organizational Structure**: Management of unsur, bagian, and jabatan hierarchies
- **User Management**: Role-based access control and authentication
- **Calendar Integration**: Schedule management and shift planning
- **Backup System**: Automated database backup and recovery
- **Reporting**: Comprehensive reporting and analytics

### Technical Features
- **Modern Architecture**: Unified API gateway with consistent response format
- **Frontend Framework**: SPRIN Core JavaScript framework for consistent UI
- **Mobile Responsive**: Fully responsive design for all devices
- **Performance Optimized**: Fast loading times and efficient data handling
- **Comprehensive Testing**: E2E testing with Puppeteer and API integration tests

## 📋 System Requirements

### Server Requirements
- **PHP**: 8.0 or higher
- **Database**: MySQL/MariaDB 5.7+
- **Web Server**: Apache (included in XAMPP)
- **Node.js**: 16+ (for testing)

### Client Requirements
- **Browser**: Chrome 90+, Firefox 88+, Safari 14+
- **Screen Resolution**: 1024x768 minimum
- **JavaScript**: Enabled

## 🛠️ Installation

### Quick Setup
```bash
# Clone the repository
git clone <repository-url>
cd sprin

# Start XAMPP services
sudo /opt/lampp/bin/lampp start

# Create database
mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS bagops;"

# Import database structure
mysql -u root -p bagops < database/bagops.sql

# Set permissions
chmod -R 755 /opt/lampp/htdocs/sprin/
mkdir -p /opt/lampp/htdocs/sprin/{backups,logs,cache}
chmod -R 777 /opt/lampp/htdocs/sprin/{backups,logs,cache}

# Install testing dependencies
cd tests
npm install
npm run puppeteer-install
```

### Configuration
1. **Database Configuration**: Edit `/core/config.php` with your database credentials
2. **Application Settings**: Configure application settings in `/core/config.php`
3. **File Permissions**: Ensure proper permissions for writable directories

## 🧪 Testing

### Running Tests
```bash
# Navigate to tests directory
cd /opt/lampp/htdocs/sprin/tests

# Quick API test
node simple-api-test.js

# Full test suite
npm run test:all

# Specific test suites
npm run test:auth      # Authentication tests
npm run test:unsur     # Unsur management tests
npm run test:bagian    # Bagian management tests
npm run test:jabatan   # Jabatan management tests
npm run test:performance # Performance tests
npm run test:mobile    # Mobile responsiveness tests
```

### Test Coverage
- **API Integration**: All API endpoints tested
- **Frontend Functionality**: UI interactions and workflows
- **Performance**: Load times and responsiveness
- **Mobile**: Responsive design and touch interactions
- **Authentication**: Login/logout flows and session management

## 📊 Architecture

### Backend Architecture
```
├── api/
│   ├── unified-api.php      # Unified API gateway
│   ├── unsur_api.php        # Unsur management API
│   ├── bagian_api.php       # Bagian management API
│   ├── jabatan_api.php      # Jabatan management API
│   └── calendar_api_public.php # Calendar API
├── core/
│   ├── config.php           # Application configuration
│   ├── Database.php         # Database connection
│   ├── SessionManager.php   # Session management
│   └── auth_helper.php      # Authentication helpers
└── pages/
    ├── unsur.php            # Unsur management page
    ├── bagian.php           # Bagian management page
    ├── jabatan.php          # Jabatan management page
    └── personil.php         # Personnel management page
```

### Frontend Architecture
```
├── public/assets/js/
│   ├── sprin-core.js       # Core framework
│   └── modules/
│       ├── unsur-module.js  # Unsur management
│       ├── bagian-module.js # Bagian management
│       └── jabatan-module.js # Jabatan management
└── includes/
    ├── global-loader.php    # Loading states
    └── components/          # UI components
```

### API Response Format
```json
{
    "success": true,
    "message": "Operation successful",
    "timestamp": "2026-04-06 22:00:00",
    "data": {
        // Response data
    }
}
```

## 🔧 API Documentation

### Unified API Gateway
The unified API gateway provides a single endpoint for all CRUD operations:

```bash
# Base URL
http://localhost/sprin/api/unified-api.php

# Examples
GET  /api/unified-api.php?resource=stats&action=dashboard
GET  /api/unified-api.php?resource=unsur&action=get_all
POST /api/unified-api.php?resource=unsur&action=create
PUT  /api/unified-api.php?resource=unsur&action=update
DELETE /api/unified-api.php?resource=unsur&action=delete
```

### Resources
- **unsur**: Unsur management operations
- **bagian**: Bagian management operations
- **jabatan**: Jabatan management operations
- **personil**: Personnel data operations
- **stats**: Statistics and analytics

## 📱 Mobile Support

The application is fully responsive and works on all mobile devices:

- **Touch Optimized**: Buttons and inputs sized for touch interaction
- **Responsive Layout**: Adapts to all screen sizes
- **Mobile Navigation**: Hamburger menu for small screens
- **Performance**: Optimized for mobile networks

## 🔒 Security

### Security Features
- **Authentication**: Secure login system with session management
- **Input Validation**: All inputs validated and sanitized
- **SQL Injection Protection**: Prepared statements for all queries
- **XSS Protection**: Output escaping and CSP headers
- **CSRF Protection**: Token-based CSRF protection

### Security Best Practices
1. Change default passwords
2. Use HTTPS in production
3. Regular security updates
4. Monitor access logs
5. Implement rate limiting

## 📈 Performance

### Optimization Features
- **Database Caching**: Query result caching
- **API Response Caching**: Cached API responses
- **Asset Optimization**: Minified CSS and JavaScript
- **Lazy Loading**: Load data on demand
- **Compression**: Gzip compression enabled

### Performance Metrics
- **Page Load Time**: < 3 seconds
- **API Response Time**: < 500ms
- **Mobile Performance**: Optimized for mobile networks
- **Database Queries**: Optimized with proper indexing

## 🔄 Backup and Recovery

### Automated Backup
```bash
# Manual backup
mysqldump -u root -p bagops > backups/bagops_backup_$(date +%Y%m%d_%H%M%S).sql

# Restore backup
mysql -u root -p bagops < backups/bagops_backup_latest.sql
```

### Backup Features
- **Automated Backups**: Scheduled database backups
- **File Backups**: Configuration and file backups
- **Recovery Tools**: Easy recovery procedures
- **Backup Validation**: Backup integrity checks

## 🐛 Troubleshooting

### Common Issues
1. **Database Connection**: Check XAMPP MySQL service
2. **File Permissions**: Ensure proper directory permissions
3. **API Issues**: Check unified API gateway
4. **Frontend Issues**: Verify SPRIN Core Framework loading

### Error Logs
- **PHP Error Log**: `/opt/lampp/logs/php_error_log`
- **Apache Error Log**: `/opt/lampp/logs/apache/error_log`
- **MySQL Error Log**: `/opt/lampp/logs/mysql/error_log`
- **Application Log**: `/opt/lampp/htdocs/sprin/logs/error.log`

## 📚 Documentation

### Development Documentation
- **Development Workflow**: `.windsurf/workflows/development-workflow.md`
- **API Documentation**: Inline API documentation
- **Testing Guide**: Comprehensive testing procedures

### User Documentation
- **User Manual**: Step-by-step user guide
- **Admin Guide**: Administrative procedures
- **Troubleshooting**: Common issues and solutions

## 🚀 Deployment

### Production Deployment
1. **Environment Setup**: Configure production environment
2. **Database Setup**: Configure production database
3. **Security Configuration**: Implement security measures
4. **Performance Optimization**: Enable caching and optimization
5. **Testing**: Run comprehensive test suite
6. **Monitoring**: Set up monitoring and logging

### Deployment Checklist
- [ ] Database configured and tested
- [ ] Security measures implemented
- [ ] Performance optimizations applied
- [ ] Backup systems configured
- [ ] Monitoring set up
- [ ] Documentation updated

## 🤝 Contributing

### Development Workflow
1. **Setup Development Environment**: Follow development workflow
2. **Create Feature Branch**: Branch for new features
3. **Write Tests**: Comprehensive test coverage
4. **Code Review**: Peer review process
5. **Integration Testing**: Full integration testing
6. **Deployment**: Deploy to production

### Code Standards
- **PHP**: PSR-12 coding standards
- **JavaScript**: ESLint configuration
- **Database**: Proper naming conventions
- **Documentation**: Comprehensive inline documentation

## 📄 License

This project is licensed under the MIT License - see the LICENSE file for details.

## 📞 Support

### Support Channels
- **Documentation**: Comprehensive documentation
- **Issue Tracker**: GitHub issues
- **Email Support**: Development team support
- **Community**: User community and forums

### Contact Information
- **Development Team**: sprin-dev@polressamosir.com
- **Support**: support@polressamosir.com
- **Issues**: GitHub Issues

## 🏆 Acknowledgments

- **Development Team**: SPRIN Development Team
- **Testing Team**: Quality Assurance Team
- **User Feedback**: POLRES Samosir Personnel
- **Contributors**: Open Source Community

---

**Version**: 2.0.0  
**Last Updated**: April 2026  
**Status**: Production Ready

For detailed information, see the [Development Workflow](.windsurf/workflows/development-workflow.md) and [API Documentation](api/README.md).
