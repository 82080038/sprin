# SPRIN Application Development Rules

## 🚫 Forbidden Actions

### Security Rules
- **NEVER** hardcode database credentials in code
- **NEVER** use direct SQL queries without prepared statements
- **NEVER** expose sensitive information in error messages
- **NEVER** skip authentication checks on protected pages
- **NEVER** trust user input without validation

### Code Quality Rules
- **NEVER** place `declare(strict_types=1)` after any other code
- **NEVER** use deprecated PHP functions
- **NEVER** ignore PHP error messages in development
- **NEVER** commit code with syntax errors
- **NEVER** use `SELECT *` in production queries

### Architecture Rules
- **NEVER** bypass SessionManager for session operations
- **NEVER** create API endpoints without authentication
- **NEVER** mix business logic with presentation
- **NEVER** use global variables for application state
- **NEVER** ignore established naming conventions

## ✅ Required Actions

### Authentication Requirements
- **ALWAYS** use `AuthHelper::validateSession()` for authentication
- **ALWAYS** start sessions with `SessionManager::start()`
- **ALWAYS** redirect to login.php for unauthenticated users
- **ALWAYS** implement proper session timeout handling
- **ALWAYS** use HTTPS for authentication forms

### Database Requirements
- **ALWAYS** use PDO with prepared statements
- **ALWAYS** implement proper error handling for database operations
- **ALWAYS** use parameterized queries for user input
- **ALWAYS** close database connections properly
- **ALWAYS** implement transaction rollback on errors

### Error Handling Requirements
- **ALWAYS** use try-catch blocks for database operations
- **ALWAYS** implement proper logging for errors
- **ALWAYS** provide user-friendly error messages
- **ALWAYS** handle exceptions gracefully
- **ALWAYS** implement proper HTTP status codes

### API Requirements
- **ALWAYS** validate authentication for API endpoints
- **ALWAYS** return proper JSON responses
- **ALWAYS** implement proper HTTP methods (GET, POST, PUT, DELETE)
- **ALWAYS** set proper CORS headers
- **ALWAYS** validate input parameters

### Frontend Requirements
- **ALWAYS** include proper doctype and meta tags
- **ALWAYS** use responsive design principles
- **ALWAYS** implement proper form validation
- **ALWAYS** use semantic HTML elements
- **ALWAYS** optimize images and assets

## 🎯 Code Standards

### PHP Standards
- Use PSR-12 coding standards
- Declare strict types for all files
- Use proper namespace and class organization
- Implement proper documentation comments
- Use meaningful variable and function names

### Database Standards
- Use lowercase table and column names
- Implement proper indexing strategies
- Use foreign key constraints
- Implement proper data types
- Use consistent naming conventions

### API Standards
- Use RESTful design principles
- Implement proper HTTP status codes
- Use consistent response formats
- Implement proper versioning
- Document API endpoints

### Frontend Standards
- Use Bootstrap 5 framework
- Implement responsive design
- Use semantic HTML5
- Optimize for performance
- Ensure accessibility compliance

## 🔍 Quality Assurance

### Code Review Checklist
- [ ] Authentication properly implemented
- [ ] Database queries use prepared statements
- [ ] Error handling implemented
- [ ] Input validation performed
- [ ] Code follows established patterns
- [ ] Documentation is complete
- [ ] Performance considerations addressed
- [ ] Security best practices followed

### Testing Requirements
- [ ] All functionality tested manually
- [ ] API endpoints tested with curl
- [ ] Responsive design verified
- [ ] Error scenarios tested
- [ ] Performance benchmarks met
- [ ] Security vulnerabilities checked

### Documentation Requirements
- [ ] Function and class documentation
- [ ] API endpoint documentation
- [ ] Database schema documentation
- [ ] Configuration documentation
- [ ] Deployment instructions

## 🚨 Critical Issues

### Immediate Attention Required
- Security vulnerabilities
- Database connection failures
- Authentication bypasses
- Data corruption issues
- Performance degradation

### High Priority Issues
- Broken functionality
- API endpoint failures
- UI/UX problems
- Error handling gaps
- Code quality issues

### Medium Priority Issues
- Performance optimizations
- Code refactoring needs
- Documentation updates
- Testing improvements
- Feature enhancements

## 📋 Development Guidelines

### New Feature Development
1. Analyze requirements and existing patterns
2. Design database schema changes if needed
3. Implement backend logic following established patterns
4. Create/update API endpoints
5. Implement frontend interface
6. Test functionality thoroughly
7. Update documentation
8. Perform code review

### Bug Fixing Process
1. Reproduce the issue consistently
2. Identify root cause
3. Implement fix following best practices
4. Test fix thoroughly
5. Verify no regressions
6. Update documentation if needed
7. Perform code review

### Performance Optimization
1. Identify performance bottlenecks
2. Analyze database queries
3. Optimize code execution
4. Implement caching strategies
5. Test performance improvements
6. Monitor ongoing performance

## 🔒 Security Guidelines

### Input Validation
- Validate all user input
- Sanitize data before processing
- Use parameterized queries
- Implement CSRF protection
- Validate file uploads

### Session Management
- Use secure session handling
- Implement proper timeout
- Regenerate session IDs
- Use secure cookie settings
- Destroy sessions properly

### Data Protection
- Encrypt sensitive data
- Implement proper access controls
- Use secure communication
- Backup data regularly
- Monitor data access

---

*These rules must be followed for all SPRIN application development activities.*
