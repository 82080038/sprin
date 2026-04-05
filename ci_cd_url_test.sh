#!/bin/bash
# Automated URL Testing for CI/CD
# Tests all URLs and links in the SPRIN application

echo "🚀 Starting Automated URL Testing..."

# Set base directory
BASE_DIR="/opt/lampp/htdocs/sprint"
cd "$BASE_DIR"

# Test 1: PHP Syntax Check
echo "📋 Checking PHP Syntax..."
find . -name "*.php" -exec php -l {} \; | grep -v "No syntax errors"
if [ $? -eq 0 ]; then
    echo "✅ PHP Syntax Check Passed"
else
    echo "❌ PHP Syntax Check Failed"
    exit 1
fi

# Test 2: API Endpoints
echo "🌐 Testing API Endpoints..."
api_endpoints=(
    "http://localhost/sprint/api/personil.php"
    "http://localhost/sprint/api/bagian.php"
    "http://localhost/sprint/api/unsur.php"
    "http://localhost/sprint/api/health_check_new.php"
    "http://localhost/sprint/api/performance_metrics.php"
)

api_passed=0
api_total=${#api_endpoints[@]}

for endpoint in "${api_endpoints[@]}"; do
    status_code=$(curl -s -o /dev/null -w "%{http_code}" "$endpoint")
    if [ "$status_code" = "200" ]; then
        ((api_passed++))
        echo "✅ $endpoint - $status_code"
    else
        echo "❌ $endpoint - $status_code"
    fi
done

echo "API Tests: $api_passed/$api_total passed"
if [ $api_passed -eq $api_total ]; then
    echo "✅ API Tests Passed"
else
    echo "❌ API Tests Failed"
    exit 1
fi

# Test 3: Main Pages
echo "📄 Testing Main Pages..."
pages=(
    "http://localhost/sprint/"
    "http://localhost/sprint/login.php"
    "http://localhost/sprint/pages/main.php"
    "http://localhost/sprint/pages/personil.php"
)

pages_passed=0
pages_total=${#pages[@]}

for page in "${pages[@]}"; do
    status_code=$(curl -s -o /dev/null -w "%{http_code}" "$page")
    if [[ "$status_code" =~ ^[23] ]]; then
        ((pages_passed++))
        echo "✅ $page - $status_code"
    else
        echo "❌ $page - $status_code"
    fi
done

echo "Page Tests: $pages_passed/$pages_total passed"
if [ $pages_passed -eq $pages_total ]; then
    echo "✅ Page Tests Passed"
else
    echo "❌ Page Tests Failed"
    exit 1
fi

echo "🎉 All Automated Tests Passed!"
echo "✅ Application is ready for deployment"
