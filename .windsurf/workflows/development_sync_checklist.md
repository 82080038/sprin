# Development Synchronization Checklist

## Pre-Development Checklist

### 1. Read Windsurf Documentation
- [ ] Read `.windsurf/memories/api_reference.md`
- [ ] Read `.windsurf/memories/app_overview.md`
- [ ] Read `.windsurf/memories/database_schema.md`
- [ ] Read `.windsurf/memories/integration_guide.md`
- [ ] Read `.windsurf/rules/php_coding_standards.md`

### 2. Verify Active Workspace
- [ ] Check workspace path: `/opt/lampp/htdocs/sprint`
- [ ] Verify database connection available
- [ ] Confirm PHP version compatible (8.2+)

### 3. Load Project Context
- [ ] Review current file structure
- [ ] Identify modified files since last session
- [ ] Check for any pending changes

---

## During Development Checklist

### When Modifying PHP/API Files
- [ ] Use Database singleton: `Database::getInstance()`
- [ ] Implement AuthHelper for authentication
- [ ] Set proper headers: `Content-Type: application/json; charset=UTF-8`
- [ ] Use standardized response format:
  ```php
  [
      'success' => true|false,
      'message' => 'string',
      'data' => object|array,
      'timestamp' => date('c')
  ]
  ```
- [ ] Add environment-based error handling
- [ ] **UPDATE**: `.windsurf/memories/api_reference.md` if adding new endpoints

### When Modifying Frontend Files
- [ ] Verify Bootstrap 5.3.0 loaded
- [ ] Verify Font Awesome 6.4.2 loaded
- [ ] Use CSS variables: `--primary-color`, `--secondary-color`, etc.
- [ ] Implement proper error handling in JavaScript
- [ ] **UPDATE**: `.windsurf/memories/integration_guide.md` if changing flows

### When Modifying Database
- [ ] Follow foreign key naming conventions
- [ ] Add `is_deleted` for soft delete
- [ ] Include `created_at` and `updated_at` timestamps
- [ ] **UPDATE**: `.windsurf/memories/database_schema.md`

### When Adding New Features
- [ ] Follow MVC pattern
- [ ] Use existing helper functions
- [ ] Add to appropriate menu/navigation
- [ ] Create API endpoint if needed
- [ ] **UPDATE**: All relevant documentation

---

## Post-Development Checklist

### Code Verification
- [ ] No hardcoded credentials
- [ ] All SQL uses prepared statements
- [ ] Output properly escaped (XSS prevention)
- [ ] Error handling implemented
- [ ] Input validation complete
- [ ] No debug code (var_dump, echo debug)

### Documentation Update
- [ ] Update `.windsurf/memories/api_reference.md` if API changed
- [ ] Update `.windsurf/memories/app_overview.md` if architecture changed
- [ ] Update `.windsurf/memories/database_schema.md` if schema changed
- [ ] Update `.windsurf/memories/integration_guide.md` if flows changed
- [ ] Update `.windsurf/rules/php_coding_standards.md` if standards changed

### Testing Verification
- [ ] Test in development environment
- [ ] Verify no PHP errors/warnings
- [ ] Check browser console for JS errors
- [ ] Validate API responses format
- [ ] Test authentication flows
- [ ] Test on mobile (responsive)

### Final Review
- [ ] Compare code with documentation
- [ ] Ensure all standards followed
- [ ] Verify documentation matches actual implementation
- [ ] No inconsistencies between code and .windsurf/

---

## Weekly Maintenance Checklist

- [ ] Review all modified files
- [ ] Sync documentation with code changes
- [ ] Update integration guide if workflows changed
- [ ] Verify library versions still current
- [ ] Check for deprecated patterns
- [ ] Update skills/workflows if needed

---

## Emergency Sync Checklist (When Inconsistency Found)

1. **Identify Source of Truth**
   - Check which is more recent: code or documentation
   - Review git history if available
   - Determine which should be updated

2. **Sync Process**
   - Update code to match standards, OR
   - Update documentation to match code
   - Never leave both inconsistent

3. **Verification**
   - Read both code and documentation
   - Verify they describe the same thing
   - Test actual implementation
