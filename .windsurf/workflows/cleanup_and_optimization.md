---
description: Application cleanup and optimization procedures
---

# Cleanup and Optimization Workflow

This workflow provides procedures for maintaining and optimizing the SPRIN application.

## 🧹 Regular Cleanup Tasks

### Weekly Cleanup
```bash
# Clean test results
cd /opt/lampp/htdocs/sprint/tests
rm -rf test-results/ playwright-report/ screenshots/

# Clean logs (keep last 7 days)
find /opt/lampp/htdocs/sprint/logs/ -name "*.log" -mtime +7 -delete

# Clean temporary files
find /opt/lampp/htdocs/sprint/ -name "*.tmp" -delete
find /opt/lampp/htdocs/sprint/ -name "*.cache" -delete
```

### Monthly Cleanup
```bash
# Clean old backups (keep last 3 months)
find /opt/lampp/htdocs/sprint/backups/ -name "*.sql" -mtime +90 -delete
find /opt/lampp/htdocs/sprint/backups/ -name "*.zip" -mtime +90 -delete

# Optimize database
/opt/lampp/bin/mysql -u root -proot bagops -e "OPTIMIZE TABLE personil, bagian, unsur, jabatan, pangkat;"

# Clean npm cache
cd /opt/lampp/htdocs/sprint/tests && npm cache clean --force
```

## ⚡ Performance Optimization

### Database Optimization
```bash
# Analyze table performance
/opt/lampp/bin/mysql -u root -proot bagops -e "ANALYZE TABLE personil, bagian, unsur, jabatan, pangkat;"

# Check table sizes
/opt/lampp/bin/mysql -u root -proot bagops -e "SHOW TABLE STATUS;"

# Optimize large tables
/opt/lampp/bin/mysql -u root -proot bagops -e "OPTIMIZE TABLE personil;"
```

### File System Optimization
```bash
# Check disk usage
du -sh /opt/lampp/htdocs/sprint/*

# Find large files
find /opt/lampp/htdocs/sprint/ -type f -size +10M -exec ls -lh {} \;

# Clean node_modules (if needed)
rm -rf /opt/lampp/htdocs/sprint/tests/node_modules
cd /opt/lampp/htdocs/sprint/tests && npm install
```

### Cache Optimization
```bash
# Clear PHP cache (if using OPcache)
sudo /opt/lampp/bin/php -r "if(function_exists('opcache_reset')) opcache_reset();"

# Clear browser cache files
find /opt/lampp/htdocs/sprint/public/ -name "*.min.*" -delete
```

## 🔍 Code Quality Checks

### PHP Code Analysis
```bash
# Check syntax errors
find /opt/lampp/htdocs/sprint/ -name "*.php" -exec php -l {} \;

# Check for deprecated functions
grep -r "mysql_" /opt/lampp/htdocs/sprint/api/ || echo "No deprecated mysql_ functions found"

# Check for security issues
grep -r "eval(" /opt/lampp/htdocs/sprint/ || echo "No eval() found"
grep -r "exec(" /opt/lampp/htdocs/sprint/ || echo "No exec() found"
```

### JavaScript Code Analysis
```bash
# Check for console.log statements
grep -r "console.log" /opt/lampp/htdocs/sprint/public/assets/js/ || echo "No console.log found"

# Check for unused variables
cd /opt/lampp/htdocs/sprint/tests && npm run lint 2>/dev/null || echo "Linting not configured"
```

### Database Integrity
```bash
# Check for orphaned records
/opt/lampp/bin/mysql -u root -proot bagops -e "
SELECT COUNT(*) as orphaned_personil 
FROM personil p 
LEFT JOIN bagian b ON p.id_bagian = b.id 
WHERE p.id_bagian > 0 AND b.id IS NULL;
"

# Check for duplicate records
/opt/lampp/bin/mysql -u root -proot bagops -e "
SELECT nrp, COUNT(*) as count 
FROM personil 
GROUP BY nrp 
HAVING COUNT(*) > 1;
"
```

## 📊 Performance Monitoring

### Response Time Testing
```bash
# Test API response times
time curl -s "http://localhost/sprint/api/personil_list.php?per_page=10" > /dev/null

# Test page load times
time curl -s "http://localhost/sprint/login.php" > /dev/null
```

### Memory Usage Monitoring
```bash
# Check PHP memory usage
php -r "echo 'Memory limit: ' . ini_get('memory_limit') . PHP_EOL;"
php -r "echo 'Current memory: ' . memory_get_usage(true) . ' bytes' . PHP_EOL;"

# Check system memory
free -h
```

### Database Performance
```bash
# Check slow queries
/opt/lampp/bin/mysql -u root -proot bagops -e "SHOW VARIABLES LIKE 'slow_query_log';"

# Monitor database connections
/opt/lampp/bin/mysql -u root -proot bagops -e "SHOW PROCESSLIST;"
```

## 🛡️ Security Maintenance

### Security Audit
```bash
# Check file permissions
find /opt/lampp/htdocs/sprint/ -type f -perm /o+w -exec ls -la {} \;

# Check for exposed credentials
grep -r "password" /opt/lampp/htdocs/sprint/config/ || echo "No hardcoded passwords found"

# Check for SQL injection vulnerabilities
grep -r "\$_GET\[" /opt/lampp/htdocs/sprint/api/ | head -5
```

### Update Security
```bash
# Update PHP dependencies (if using composer)
cd /opt/lampp/htdocs/sprint && composer update --no-dev

# Update JavaScript dependencies
cd /opt/lampp/htdocs/sprint/tests && npm update

# Check for security advisories
npm audit
```

## 🔄 Automation Scripts

### Create Cleanup Script
```bash
# Create automated cleanup script
cat > /opt/lampp/htdocs/spring/scripts/cleanup.sh << 'EOF'
#!/bin/bash
echo "Starting SPRIN cleanup..."

# Clean test results
rm -rf /opt/lampp/htdocs/sprint/tests/test-results/
rm -rf /opt/lampp/htdocs/sprint/tests/playwright-report/

# Clean old logs
find /opt/lampp/htdocs/sprint/logs/ -name "*.log" -mtime +7 -delete

# Optimize database
/opt/lampp/bin/mysql -u root -proot bagops -e "OPTIMIZE TABLE personil, bagian, unsur, jabatan, pangkat;"

echo "Cleanup completed!"
EOF

chmod +x /opt/lampp/htdocs/spring/scripts/cleanup.sh
```

### Setup Cron Job
```bash
# Add weekly cleanup to crontab
(crontab -l 2>/dev/null; echo "0 2 * * 0 /opt/lampp/htdocs/spring/scripts/cleanup.sh") | crontab -
```

## 📈 Optimization Recommendations

### Database Optimization
1. Add indexes to frequently queried columns
2. Partition large tables if needed
3. Use prepared statements consistently
4. Implement connection pooling

### Application Optimization
1. Enable output buffering
2. Use caching for static content
3. Optimize images and assets
4. Implement lazy loading

### Server Optimization
1. Enable Gzip compression
2. Use CDN for static assets
3. Implement HTTP/2
4. Optimize PHP-FPM settings

## 🎯 Best Practices

1. **Regular Schedule**: Perform cleanup weekly
2. **Monitor Performance**: Track response times and resource usage
3. **Backup First**: Always backup before major optimizations
4. **Test Changes**: Verify changes don't break functionality
5. **Document Changes**: Keep record of optimizations performed

## 📞 Emergency Procedures

### Performance Issues
1. Check system resources: `top`, `df -h`
2. Restart services: `sudo /opt/lampp/lampp restart`
3. Clear caches: PHP, browser, database
4. Check logs for errors

### Security Issues
1. Scan for malware: `clamscan`
2. Check file integrity
3. Review access logs
4. Update passwords and keys

## 📝 Maintenance Log

Keep a record of all maintenance activities:

```markdown
## 2026-04-02
- Removed redundant test files
- Optimized database tables
- Updated .windsurf configuration
- Cleaned up documentation structure

## Next Week
- [ ] Review security settings
- [ ] Update dependencies
- [ ] Performance testing
- [ ] Backup verification
```
