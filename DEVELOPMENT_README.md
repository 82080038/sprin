# 🚀 SPRIN v1.2.0 - Development Version

## ⚠️ IMPORTANT NOTICE
**This is a DEVELOPMENT version, not production-ready!**

## 📋 Current Status

### 🎯 Development Phase
- **Version**: v1.2.0 Development
- **Status**: Active Development
- **Stability**: Testing Phase
- **Production**: ❌ NOT READY

### 🚧 What's Working
- ✅ Basic CRUD operations
- ✅ UI consistency improvements
- ✅ Modal standardization
- ✅ Auto-generation features
- ✅ Database structure

### ⚠️ What's Testing
- 🔄 Multi-user system stability
- 🔄 Backup system reliability
- 🔄 Performance optimization
- 🔄 Security validation
- 🔄 Error handling completeness

## 🧪 For Testing Only

### 📋 Requirements for Testers
- **PHP 8.0+**
- **MySQL 5.7+ / MariaDB 10.3+**
- **Apache Web Server**
- **Development environment** (NOT production)

### ⚡ Test Installation

```bash
# 1. Clone Development Version
git clone https://github.com/82080038/sprin.git
cd sprin

# 2. Database Setup (Testing)
mysql -u root -p
CREATE DATABASE bagops_test;
mysql -u root -p bagops_test < database/bagops.sql

# 3. Run Migrations (Testing)
mysql -u root -p bagops_test < database/migrations/create_users_table.sql
mysql -u root -p bagops_test < database/migrations/create_backup_tables.sql

# 4. Configure for Development
cp core/config.php.example core/config.php
# Edit for TEST database

# 5. Test Environment Setup
mkdir -p backups logs cache
chmod 755 backups logs cache

# 6. Access Test Application
# http://localhost/sprint
# Login: bagops / admin123
```

## 🚫 NOT FOR PRODUCTION USE

### ⚠️ Development Warnings
- **Security**: Not fully hardened
- **Performance**: Not optimized
- **Backup**: System in testing
- **Multi-user**: Features in development
- **Error Handling**: Still being improved

### 📝 Known Issues
- Some edge cases in error handling
- Performance issues with large datasets
- Backup system needs more testing
- Multi-user session management needs validation

## 🧪 Testing Guidelines

### ✅ Safe to Test
- Basic CRUD operations
- UI/UX features
- Modal functionality
- Form validations
- Export features

### ⚠️ Test with Caution
- Multi-user features
- Backup/restore operations
- User management
- Report generation
- Calendar integration

### 🚫 Do NOT Test in Production
- Any security features
- Real personnel data
- Production database
- Live systems

## 📊 Development Progress

### 🎯 Completed Features (v1.2.0)
- [x] Modal consistency
- [x] Auto-generation features
- [x] Card-based layouts
- [x] Basic CRUD operations
- [x] Export functionality

### 🔄 In Progress
- [ ] Performance optimization
- [ ] Security hardening
- [ ] Error handling improvements
- [ ] Multi-user testing
- [ ] Backup system validation

### 📋 Planned for v1.3.0
- [ ] Production security audit
- [ ] Performance optimization
- [ ] Advanced error handling
- [ ] Production deployment guide
- [ ] Stability testing

## 🔧 Development Configuration

### 📁 Development Structure
```
📁 sprin/
├── 📄 README.md               # Main documentation
├── 📄 DEVELOPMENT_README.md   # This file
├── 📁 pages/                  # Application pages
├── 📁 api/                    # API endpoints
├── 📁 core/                   # Core system files
├── 📁 database/               # Database files
├── 📁 tests/                  # Test files
└── 📁 docs/                   # Documentation
```

### 🧪 Testing Environment
- Use separate test database
- Enable debug mode
- Monitor error logs
- Test with sample data only

## 📚 Documentation

- **[Complete Documentation](docs/README.md)**
- **[Development Summary](docs/DEVELOPMENT_SUMMARY.md)**
- **[Folder Structure](docs/STRUKTUR_FOLDER.md)**

## 🆘 Development Support

### 📋 Default Test Credentials
- **Username**: `bagops`
- **Password**: `admin123`

### 🐛 Reporting Issues
- Create issues in GitHub
- Describe development environment
- Include error logs
- Provide steps to reproduce

## 📅 Development Timeline

### 🎯 Current Sprint: v1.2.0 Development
- **Focus**: UI/UX improvements
- **Status**: Testing phase
- **Next**: Stability improvements

### 📅 Planned Releases
- **v1.2.1**: Bug fixes and stability
- **v1.3.0**: Performance optimization
- **v1.4.0**: Production readiness

---

**⚠️ DEVELOPMENT VERSION - FOR TESTING ONLY**
**🚫 NOT PRODUCTION READY - DO NOT DEPLOY TO LIVE SYSTEMS**

For production deployment, wait for official stable release.
