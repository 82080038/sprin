---
description: Application health check and monitoring
---

# Application Health Check Workflow

This workflow provides comprehensive health monitoring for the SPRIN application.

## 🚀 Quick Health Check

### 1. System Health Check
```bash
# Navigate to tests directory
cd /opt/lampp/htdocs/sprint/tests

# Run comprehensive health check
npm run test:health
```

### 2. API Testing
```bash
# Run API public access tests
npm run test:public

# Run API authentication tests (requires login)
npm run test:auth
```

### 3. E2E Testing
```bash
# Run login tests
npm run test:login

# Run dashboard tests
npm run test:dashboard

# Run personil management tests
npm run test:personil

# Run all E2E tests
npm run test:e2e
```

### 4. Full Test Suite
```bash
# Run all tests
npm run test:all
```

## 📊 Health Check Components

### Database Health
- Connection status
- Query performance
- Table integrity
- Index optimization

### API Endpoints
- Response times
- Error rates
- Authentication status
- Data validation

### Frontend Integration
- F2E client functionality
- JavaScript error monitoring
- Asset loading performance
- User interface responsiveness

### System Resources
- Memory usage
- Disk space
- Log file sizes
- Cache efficiency

## 🔧 Troubleshooting

### Common Issues

#### API 401 Errors
```bash
# Check authentication helper
curl -s -X POST "http://localhost/sprint/api/health_check.php" | python3 -m json.tool
```

#### Database Connection Issues
```bash
# Check MySQL service
sudo /opt/lampp/lampp status

# Restart if needed
sudo /opt/lampp/lampp restart
```

#### Test Failures
```bash
# Install test dependencies
cd tests && npm install

# Clear test cache
npm run test:clean
```

## 📈 Performance Monitoring

### Response Time Targets
- Database queries: < 100ms
- API endpoints: < 500ms
- Page loads: < 2s

### Resource Limits
- Memory usage: < 512MB
- Disk space: > 1GB free
- Log files: < 100MB total

## 🚨 Alert Thresholds

### Critical Alerts
- Health check status: ERROR
- Database connection: FAILED
- API response time: > 5s

### Warning Alerts
- Memory usage: > 80%
- Disk space: < 10%
- Error rate: > 5%

## 📝 Reporting

### Daily Health Report
```bash
# Generate daily report
npm run test:health > health_report_$(date +%Y%m%d).json
```

### Weekly Performance Summary
```bash
# Generate performance summary
npm run test:all > performance_report_$(date +%Y%m%d).json
```

## 🔄 Automated Monitoring

### Cron Jobs
```bash
# Add to crontab for daily health checks
0 8 * * * cd /opt/lampp/htdocs/sprint/tests && npm run test:health
```

### Log Monitoring
```bash
# Monitor error logs
tail -f /opt/lampp/htdocs/sprint/logs/error.log

# Monitor API activity
tail -f /opt/lampp/htdocs/sprint/logs/api_activity.log
```

## 🎯 Best Practices

1. **Daily Health Checks**: Run health checks every morning
2. **Weekly Full Tests**: Run complete test suite weekly
3. **Monthly Performance**: Review performance metrics monthly
4. **Immediate Response**: Address critical alerts immediately
5. **Documentation**: Document all issues and resolutions

## 📞 Support

For health check issues:
1. Check this workflow first
2. Review logs in `/logs/` directory
3. Run individual test components
4. Check system resources
5. Contact system administrator if needed
